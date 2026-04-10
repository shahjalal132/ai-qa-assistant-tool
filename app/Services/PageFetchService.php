<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PageFetchService
{
    public function __construct(
        private int $timeoutSeconds = 60,
    ) {}

    public function fetch(string $url): PageFetchOutcome
    {
        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->withHeaders(['User-Agent' => 'AI-QA-Tool/1.0'])
                ->get($url);
        } catch (ConnectionException $e) {
            return new PageFetchOutcome(
                PageFetchClassification::TransportError,
                0,
                '',
                null,
                $e->getMessage(),
            );
        } catch (\Throwable $e) {
            return new PageFetchOutcome(
                PageFetchClassification::TransportError,
                0,
                '',
                null,
                $e->getMessage(),
            );
        }

        $status = $response->status();
        $body = $response->body();
        $contentType = $response->header('Content-Type');

        if (! $response->successful()) {
            if ($this->isHtmlContentType($contentType) && strlen(trim(strip_tags($body))) > 20) {
                if ($this->looksLikeErrorPageHtml($body)) {
                    return new PageFetchOutcome(PageFetchClassification::ErrorPage, $status, $body, $contentType);
                }

                return new PageFetchOutcome(PageFetchClassification::HttpError, $status, $body, $contentType);
            }

            return new PageFetchOutcome(PageFetchClassification::HttpError, $status, $body, $contentType);
        }

        if (! $this->isHtmlContentType($contentType)) {
            return new PageFetchOutcome(PageFetchClassification::NonHtml, $status, $body, $contentType);
        }

        if ($this->looksLikeErrorPageHtml($body)) {
            return new PageFetchOutcome(PageFetchClassification::ErrorPage, $status, $body, $contentType);
        }

        return new PageFetchOutcome(PageFetchClassification::Ok, $status, $body, $contentType);
    }

    public function isHardFailure(PageFetchOutcome $outcome): bool
    {
        if ($outcome->classification === PageFetchClassification::TransportError) {
            return true;
        }

        if ($outcome->classification !== PageFetchClassification::HttpError) {
            return false;
        }

        return trim($outcome->body) === '';
    }

    public function isSoftUnusable(PageFetchOutcome $outcome): bool
    {
        return in_array($outcome->classification, [
            PageFetchClassification::NonHtml,
            PageFetchClassification::ErrorPage,
        ], true)
            || (
                $outcome->classification === PageFetchClassification::HttpError
                && trim($outcome->body) !== ''
            );
    }

    public function isOk(PageFetchOutcome $outcome): bool
    {
        return $outcome->classification === PageFetchClassification::Ok;
    }

    private function isHtmlContentType(?string $contentType): bool
    {
        if ($contentType === null || $contentType === '') {
            return false;
        }

        $lower = strtolower($contentType);

        return Str::contains($lower, 'text/html')
            || Str::contains($lower, 'application/xhtml+xml');
    }

    /**
     * Conservative: short pages with not-found signals in title or h1.
     */
    public function looksLikeErrorPageHtml(string $html): bool
    {
        $plainLen = strlen(trim(strip_tags($html)));
        if ($plainLen > 12000) {
            return false;
        }

        if (preg_match('/<title[^>]*>[^<]*(page\s+not\s+found|not\s+found|\b404\b)[^<]*<\/title>/i', $html) === 1) {
            return true;
        }

        if (preg_match('/<h1[^>]*>[^<]*(page\s+not\s+found|not\s+found)[^<]*<\/h1>/i', $html) === 1) {
            return true;
        }

        return false;
    }
}
