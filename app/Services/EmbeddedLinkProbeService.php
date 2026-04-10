<?php

namespace App\Services;

use DOMElement;
use DOMXPath;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EmbeddedLinkProbeService
{
    private const USER_AGENT = 'AI-QA-Tool/1.0';

    private const MACHINE_BLOCK_MAX_FAILURE_LINES = 50;

    public function __construct(
        private int $probeTimeoutSeconds = 20,
    ) {}

    /**
     * @return list<LinkProbeResult>
     */
    public function extractAndProbe(string $strippedHtml, string $pageUrl): array
    {
        $candidates = $this->extractHttpsCandidates($strippedHtml, $pageUrl);

        return $this->probeCandidates($candidates);
    }

    /**
     * @param  list<LinkProbeCandidate>  $candidates
     * @return list<LinkProbeResult>
     */
    public function probeCandidates(array $candidates): array
    {
        if ($candidates === []) {
            return [];
        }

        $concurrency = max(1, min(25, (int) config('qa.link_probe_concurrency', 10)));
        $chunks = array_chunk($candidates, $concurrency);
        $results = [];

        foreach ($chunks as $chunk) {
            $responses = Http::timeout($this->probeTimeoutSeconds)
                ->withHeaders(['User-Agent' => self::USER_AGENT])
                ->pool(function (Pool $pool) use ($chunk): void {
                    foreach ($chunk as $i => $c) {
                        $pool->as((string) $i)->head($c->url);
                    }
                }, $concurrency);

            foreach ($chunk as $i => $c) {
                $results[] = $this->responseToResult($c, $responses[(string) $i] ?? null);
            }
        }

        return $results;
    }

    /**
     * @param  list<LinkProbeResult>  $english
     * @param  list<LinkProbeResult>  $welsh
     */
    public function formatMachineVerifiedBlock(array $english, array $welsh): string
    {
        $parts = ['MACHINE_VERIFIED (unauthenticated HEAD from QA worker; no session cookies):'];

        foreach (['English' => $english, 'Welsh' => $welsh] as $label => $list) {
            if ($list === []) {
                continue;
            }
            $n = count($list);
            $bad = array_values(array_filter($list, fn (LinkProbeResult $r) => ! $r->isOk()));
            $badCritical = array_values(array_filter($bad, fn (LinkProbeResult $r) => $r->isCritical));
            $bc = count($bad);
            $bcc = count($badCritical);

            if ($bc === 0) {
                $parts[] = "{$label}: {$n} HTTPS links checked via HEAD; all returned reachable (2xx).";

                continue;
            }

            $parts[] = "{$label}: {$n} links checked, {$bc} unreachable ({$bcc} critical).";

            usort($bad, function (LinkProbeResult $a, LinkProbeResult $b): int {
                if ($a->isCritical !== $b->isCritical) {
                    return $a->isCritical ? -1 : 1;
                }

                return strcmp($a->url, $b->url);
            });

            $slice = array_slice($bad, 0, self::MACHINE_BLOCK_MAX_FAILURE_LINES);
            foreach ($slice as $r) {
                $parts[] = $r->lineForMachineBlock();
            }

            if ($bc > count($slice)) {
                $parts[] = '… and '.($bc - count($slice)).' more unreachable (see stored link probe data / CSV export).';
            }
        }

        if (count($parts) === 1) {
            return '';
        }

        $parts[] = 'Trust unreachable lines for reachability; the model cannot open binaries. Still run HTML-only checks on the content below.';

        return implode("\n", $parts);
    }

    /**
     * @param  list<LinkProbeResult>  $all
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function mergeDownloadFailuresIntoResult(array $data, array $all): array
    {
        $criticalFailures = array_values(array_filter(
            $all,
            fn (LinkProbeResult $r) => $r->isCritical && ! $r->isOk(),
        ));

        if ($criticalFailures === []) {
            return $data;
        }

        $desc = implode('; ', array_map(
            fn (LinkProbeResult $r) => $r->url.' → HTTP '.$r->status.' ('.$r->label.')',
            $criticalFailures,
        ));

        $data['report_download_match'] = [
            'pass' => false,
            'reason' => 'Machine-verified critical download/upload URL check (unauthenticated): '.$desc,
        ];

        $suffix = ' Machine-verified critical download URLs: '.$desc;
        $bl = $data['broken_links'] ?? '';
        if (is_string($bl) && trim($bl) !== '') {
            $data['broken_links'] = rtrim($bl).$suffix;
        } else {
            $data['broken_links'] = trim($suffix);
        }

        return $data;
    }

    /**
     * @return list<LinkProbeCandidate>
     */
    private function extractHttpsCandidates(string $html, string $pageUrl): array
    {
        $max = max(1, (int) config('qa.link_probe_max_per_page', 80));
        $allowHttp = (bool) config('qa.link_probe_allow_insecure_http', false);

        $useErrors = libxml_use_internal_errors(true);
        try {
            $dom = new \DOMDocument;
            $loaded = @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
            if (! $loaded) {
                return [];
            }

            $xpath = new DOMXPath($dom);
            $nodes = $xpath->query('//a[@href]');
            if ($nodes === false) {
                return [];
            }

            /** @var array<string, LinkProbeCandidate> $byUrl */
            $byUrl = [];

            /** @var DOMElement $node */
            foreach ($nodes as $node) {
                $href = $node->getAttribute('href');
                $absolute = $this->resolveUrl($href, $pageUrl);
                if ($absolute === null) {
                    continue;
                }

                $lower = strtolower($absolute);
                if (str_starts_with($lower, 'https://')) {
                    // ok
                } elseif ($allowHttp && str_starts_with($lower, 'http://')) {
                    // ok
                } else {
                    continue;
                }

                $isCritical = $this->isCriticalAnchor($node, $absolute);

                $existing = $byUrl[$absolute] ?? null;
                if ($existing !== null) {
                    if ($isCritical && ! $existing->isCritical) {
                        $byUrl[$absolute] = new LinkProbeCandidate($absolute, true);
                    }

                    continue;
                }

                $byUrl[$absolute] = new LinkProbeCandidate($absolute, $isCritical);
            }

            $list = array_values($byUrl);
            usort($list, function (LinkProbeCandidate $a, LinkProbeCandidate $b): int {
                if ($a->isCritical !== $b->isCritical) {
                    return $a->isCritical ? -1 : 1;
                }
                $ap = str_contains(strtolower($a->url), '/app/uploads/') ? 0 : 1;
                $bp = str_contains(strtolower($b->url), '/app/uploads/') ? 0 : 1;
                if ($ap !== $bp) {
                    return $ap - $bp;
                }

                return strcmp($a->url, $b->url);
            });

            if (count($list) > $max) {
                $list = array_slice($list, 0, $max);
            }

            return $list;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($useErrors);
        }
    }

    private function isCriticalAnchor(DOMElement $node, string $absoluteUrl): bool
    {
        $class = ' '.$node->getAttribute('class').' ';
        if (str_contains($class, ' nhsuk-button ')) {
            return true;
        }

        if (str_contains(strtolower($absoluteUrl), '/app/uploads/')) {
            return true;
        }

        return false;
    }

    private function resolveUrl(string $href, string $base): ?string
    {
        $href = trim($href);
        if ($href === '' || str_starts_with($href, 'javascript:') || str_starts_with($href, 'mailto:') || str_starts_with($href, '#')) {
            return null;
        }

        if (str_starts_with($href, '//')) {
            $scheme = parse_url($base, PHP_URL_SCHEME) ?: 'https';

            return $scheme.':'.$href;
        }

        if (preg_match('#^https?://#i', $href) === 1) {
            return $href;
        }

        $baseParts = parse_url($base);
        if (! is_array($baseParts) || ! isset($baseParts['scheme'], $baseParts['host'])) {
            return null;
        }

        $scheme = $baseParts['scheme'];
        $host = $baseParts['host'];
        $port = isset($baseParts['port']) ? ':'.$baseParts['port'] : '';
        $path = $baseParts['path'] ?? '/';
        if (! str_ends_with($path, '/')) {
            $path = Str::beforeLast($path, '/');
            if ($path === '') {
                $path = '/';
            }
        }

        if (str_starts_with($href, '/')) {
            return $scheme.'://'.$host.$port.$href;
        }

        return rtrim($scheme.'://'.$host.$port.$path, '/').'/'.$href;
    }

    private function responseToResult(LinkProbeCandidate $c, mixed $response): LinkProbeResult
    {
        if ($response instanceof \Throwable) {
            return new LinkProbeResult($c->url, 0, 'transport_error', $c->isCritical);
        }

        if (! $response instanceof Response) {
            return new LinkProbeResult($c->url, 0, 'transport_error', $c->isCritical);
        }

        return new LinkProbeResult(
            $c->url,
            $response->status(),
            $this->labelForStatus($response->status()),
            $c->isCritical,
        );
    }

    private function labelForStatus(int $status): string
    {
        if ($status >= 200 && $status < 300) {
            return 'reachable';
        }
        if ($status === 404) {
            return 'not_found';
        }
        if ($status === 401 || $status === 403) {
            return 'forbidden_or_private';
        }
        if ($status >= 300 && $status < 400) {
            return 'redirect';
        }

        return 'http_error';
    }
}
