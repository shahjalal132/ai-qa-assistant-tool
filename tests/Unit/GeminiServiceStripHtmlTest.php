<?php

use App\Services\GeminiService;

test('stripHtmlForTokens preserves HTML structure in main content', function () {
    $gemini = new GeminiService;
    $html = <<<'HTML'
<html><body>
<div id="cookie-banner">Your choice regarding cookies. Accept cookies</div>
<header>Site header navigation</header>
<main class="phw-report-page-content">
  <h1 class="nhsuk-heading-xl">Annual report</h1>
  <p class="nhsuk-body">Surveillance of uptake in Wales.</p>
  <img src="chart.jpg" alt="Chart data">
  <span class="nhsuk-tag">National</span>
</main>
<footer>Privacy policy and terms</footer>
</body></html>
HTML;

    $result = $gemini->stripHtmlForTokens($html);

    // Should preserve structure (main element without classes, as we clean root attrs)
    // Check for tag starts (handles different attribute orders and spacing)
    expect($result)->toContain('<main')
        ->and($result)->toContain('<h1')
        ->and($result)->toContain('Annual report')
        ->and($result)->toContain('<p')
        ->and($result)->toContain('Surveillance of uptake in Wales')
        ->and($result)->toContain('<img')
        ->and($result)->toContain('alt="Chart data"')
        ->and($result)->toContain('src="chart.jpg"')
        ->and($result)->toContain('class="nhsuk-tag"');

    // Should remove chrome and noise (but preserve nhsuk-* classes)
    expect($result)->not->toContain('<header>')
        ->and($result)->not->toContain('Accept cookies')
        ->and($result)->not->toContain('<footer>')
        ->and($result)->not->toContain('Privacy policy')
        ->and($result)->not->toContain('phw-report-page-content')
        // nhsuk-heading-xl IS preserved (it's an nhsuk-* class)
        ->and($result)->toContain('nhsuk-heading-xl');
});

test('stripHtmlForTokens removes scripts and styles', function () {
    $gemini = new GeminiService;
    $html = <<<'HTML'
<html><head><style>body{color:red}</style></head>
<body>
<script>alert('test');</script>
<main><h1>Report</h1><p>Content here.</p></main>
</body></html>
HTML;

    $result = $gemini->stripHtmlForTokens($html);

    expect($result)->toContain('<main>')
        ->and($result)->toContain('<h1>Report</h1>')
        ->and($result)->not->toContain('<script')
        ->and($result)->not->toContain('alert')
        ->and($result)->not->toContain('<style')
        ->and($result)->not->toContain('color:red');
});

test('stripHtmlForTokens preserves href on links and cleans non-nhsuk classes', function () {
    $gemini = new GeminiService;
    $html = <<<'HTML'
<html><body>
<main>
  <h1>Links</h1>
  <p><a href="https://example.com/page" class="some-button-class">Click here</a></p>
</main>
</body></html>
HTML;

    $result = $gemini->stripHtmlForTokens($html);

    expect($result)->toContain('<a href="https://example.com/page">')
        ->and($result)->not->toContain('some-button-class')
        ->and($result)->toContain('Click here');
});

test('stripHtmlForTokens cleans non-nhsuk classes', function () {
    $gemini = new GeminiService;
    $html = <<<'HTML'
<html><body>
<main class="phw-report-page-content custom-class">
  <h1 class="nhsuk-heading-xl other-class">Title</h1>
  <p class="random-class nhsuk-body">Text</p>
  <span class="nhsuk-tag nhsuk-tag--red">Tag</span>
</main>
</body></html>
HTML;

    $result = $gemini->stripHtmlForTokens($html);

    // Should preserve nhsuk-* classes
    expect($result)->toContain('class="nhsuk-tag nhsuk-tag--red"');
    expect($result)->toContain('class="nhsuk-body"');

    // Should preserve nhsuk-* classes on h1
    expect($result)->toContain('class="nhsuk-heading-xl"');

    // Should remove non-nhsuk classes from elements
    expect($result)->not->toContain('phw-report-page-content');
    expect($result)->not->toContain('custom-class');
    expect($result)->not->toContain('other-class');
    expect($result)->not->toContain('random-class');

    // Verify structure is preserved
    expect($result)->toContain('<h1');
    expect($result)->toContain('Title');
});

test('stripHtmlForTokens applies max length from config', function () {
    config(['qa.max_stripped_content_length' => 40]);

    $gemini = new GeminiService;
    $html = '<main>'.str_repeat('word ', 50).'</main>';

    $result = $gemini->stripHtmlForTokens($html);

    expect(mb_strlen($result))->toBeLessThanOrEqual(45);
});

test('stripHtmlForTokens removes header footer nav aside and form', function () {
    $gemini = new GeminiService;
    $html = <<<'HTML'
<html><body>
<nav>Menu items</nav>
<header>Logo and nav</header>
<form><input name="search"></form>
<aside>Sidebar content</aside>
<main><h1>Main Content</h1><p>Text here.</p></main>
<footer>Copyright 2025</footer>
</body></html>
HTML;

    $result = $gemini->stripHtmlForTokens($html);

    expect($result)->toContain('<main>')
        ->and($result)->toContain('<h1>Main Content</h1>')
        ->and($result)->not->toContain('<nav>')
        ->and($result)->not->toContain('Menu items')
        ->and($result)->not->toContain('<form>')
        ->and($result)->not->toContain('<input')
        ->and($result)->not->toContain('<aside>');
});

test('stripHtmlForTokens preserves headings and lists', function () {
    $gemini = new GeminiService;
    $html = <<<'HTML'
<html><body>
<main>
  <h1>Main Title</h1>
  <h2>Subsection</h2>
  <p>Description</p>
  <ul>
    <li>Item one</li>
    <li>Item two</li>
  </ul>
  <ol>
    <li>First</li>
    <li>Second</li>
  </ol>
</main>
</body></html>
HTML;

    $result = $gemini->stripHtmlForTokens($html);

    expect($result)->toContain('<h1>Main Title</h1>')
        ->and($result)->toContain('<h2>Subsection</h2>')
        ->and($result)->toContain('<ul>')
        ->and($result)->toContain('<ol>')
        ->and($result)->toContain('<li>Item one</li>')
        ->and($result)->toContain('<li>First</li>');
});
