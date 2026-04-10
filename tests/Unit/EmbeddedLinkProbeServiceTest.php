<?php

use App\Services\EmbeddedLinkProbeService;
use App\Services\LinkProbeResult;
use Illuminate\Support\Facades\Http;

test('extractAndProbe probes nhsuk-button href on same host', function () {
    $pageUrl = 'https://beta-phw.nhs.wales/reports/some-report/';
    $html = '<main><a class="nhsuk-button nhsuk-button--primary" href="/app/uploads/sites/2/2025/11/file.docx">Download</a></main>';

    Http::fake([
        'beta-phw.nhs.wales/app/uploads/*' => Http::response('', 403, ['Content-Type' => 'text/html']),
    ]);

    $svc = new EmbeddedLinkProbeService;
    $results = $svc->extractAndProbe($html, $pageUrl);

    expect($results)->toHaveCount(1)
        ->and($results[0]->url)->toBe('https://beta-phw.nhs.wales/app/uploads/sites/2/2025/11/file.docx')
        ->and($results[0]->status)->toBe(403)
        ->and($results[0]->label)->toBe('forbidden_or_private')
        ->and($results[0]->isCritical)->toBeTrue()
        ->and($results[0]->isOk())->toBeFalse();
});

test('mergeDownloadFailuresIntoResult overwrites report_download_match only for critical failures', function () {
    $svc = new EmbeddedLinkProbeService;
    $failures = [
        new LinkProbeResult('https://x.test/a.docx', 404, 'not_found', true),
    ];

    $data = [
        'report_download_match' => ['pass' => true, 'reason' => 'Model said ok.'],
        'broken_links' => 'No broken links found.',
    ];

    $out = $svc->mergeDownloadFailuresIntoResult($data, $failures);

    expect($out['report_download_match']['pass'])->toBeFalse()
        ->and($out['report_download_match']['reason'])->toContain('Machine-verified')
        ->and($out['broken_links'])->toContain('Machine-verified critical download URLs');
});

test('mergeDownloadFailuresIntoResult ignores non-critical failures', function () {
    $svc = new EmbeddedLinkProbeService;
    $failures = [
        new LinkProbeResult('https://ons.gov.uk/x', 403, 'forbidden_or_private', false),
    ];

    $data = [
        'report_download_match' => ['pass' => true, 'reason' => 'Model said ok.'],
        'broken_links' => 'No broken links found.',
    ];

    $out = $svc->mergeDownloadFailuresIntoResult($data, $failures);

    expect($out['report_download_match']['pass'])->toBeTrue()
        ->and($out['broken_links'])->toBe('No broken links found.');
});

test('extract respects link_probe_max_per_page config', function () {
    config(['qa.link_probe_max_per_page' => 2]);

    $pageUrl = 'https://example.com/r/';
    $html = '<main>'
        .'<a href="https://a.test/1">1</a>'
        .'<a href="https://a.test/2">2</a>'
        .'<a href="https://a.test/3">3</a>'
        .'</main>';

    Http::fake(['*' => Http::response('', 200)]);

    $svc = new EmbeddedLinkProbeService;
    $results = $svc->extractAndProbe($html, $pageUrl);

    expect($results)->toHaveCount(2);
});

test('formatMachineVerifiedBlock is empty when no probes', function () {
    $svc = new EmbeddedLinkProbeService;
    expect($svc->formatMachineVerifiedBlock([], []))->toBe('');
});
