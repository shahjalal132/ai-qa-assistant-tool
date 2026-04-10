<?php

use App\Services\PageFetchClassification;
use App\Services\PageFetchService;
use Illuminate\Support\Facades\Http;

test('empty 404 body is hard failure', function () {
    Http::fake(['*' => Http::response('', 404)]);

    $s = new PageFetchService;
    $o = $s->fetch('https://example.com/r');

    expect($o->classification)->toBe(PageFetchClassification::HttpError)
        ->and($s->isHardFailure($o))->toBeTrue()
        ->and($s->isSoftUnusable($o))->toBeFalse();
});

test('200 html error page is soft unusable not hard failure', function () {
    $html = '<html><head><title>Page not found</title></head><body><h1>Page not found</h1></body></html>';
    Http::fake(['*' => Http::response($html, 200, ['Content-Type' => 'text/html'])]);

    $s = new PageFetchService;
    $o = $s->fetch('https://example.com/missing');

    expect($o->classification)->toBe(PageFetchClassification::ErrorPage)
        ->and($s->isHardFailure($o))->toBeFalse()
        ->and($s->isSoftUnusable($o))->toBeTrue();
});

test('200 non html is soft unusable', function () {
    Http::fake(['*' => Http::response('%PDF-1.4', 200, ['Content-Type' => 'application/pdf'])]);

    $s = new PageFetchService;
    $o = $s->fetch('https://example.com/doc');

    expect($o->classification)->toBe(PageFetchClassification::NonHtml)
        ->and($s->isSoftUnusable($o))->toBeTrue();
});

test('successful html report page is ok', function () {
    $html = '<html><body><main><h1>Annual report</h1><p>Hello</p></main></body></html>';
    Http::fake(['*' => Http::response($html, 200, ['Content-Type' => 'text/html; charset=utf-8'])]);

    $s = new PageFetchService;
    $o = $s->fetch('https://example.com/report');

    expect($o->classification)->toBe(PageFetchClassification::Ok)
        ->and($s->isOk($o))->toBeTrue();
});

test('looksLikeErrorPageHtml is false for long normal pages mentioning 404 in body', function () {
    $s = new PageFetchService;
    $long = '<html><head><title>Reports</title></head><body><main><h1>Reports</h1>'
        .str_repeat('<p>Discussion of 404 errors in healthcare systems.</p>', 400)
        .'</main></body></html>';

    expect($s->looksLikeErrorPageHtml($long))->toBeFalse();
});
