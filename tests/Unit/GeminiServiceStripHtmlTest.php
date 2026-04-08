<?php

use App\Services\GeminiService;

test('stripHtmlForTokens prefers main content over cookie chrome', function () {
    $gemini = new GeminiService;
    $html = <<<'HTML'
<html><body>
<div id="cookie-banner">Your choice regarding cookies. Accept cookies</div>
<header>Site header navigation</header>
<main><h1>Annual report</h1><p>Surveillance of uptake in Wales.</p></main>
<footer>Privacy policy and terms</footer>
</body></html>
HTML;

    $text = $gemini->stripHtmlForTokens($html);

    expect($text)->toContain('Annual report')
        ->and($text)->toContain('Surveillance of uptake in Wales')
        ->and($text)->not->toContain('Accept cookies')
        ->and($text)->not->toContain('Privacy policy');
});

test('stripHtmlForTokens applies max length from config', function () {
    config(['qa.max_stripped_content_length' => 40]);

    $gemini = new GeminiService;
    $html = '<main>'.str_repeat('word ', 50).'</main>';

    $text = $gemini->stripHtmlForTokens($html);

    expect(mb_strlen($text))->toBeLessThanOrEqual(45);
});
