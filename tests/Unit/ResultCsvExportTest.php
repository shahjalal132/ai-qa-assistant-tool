<?php

use App\Http\Controllers\ResultController;

function invokeFormattedExportCell(ResultController $controller, string $key, mixed $value): string
{
    $m = new ReflectionMethod(ResultController::class, 'formatFormattedExportCell');
    $m->setAccessible(true);

    return $m->invoke($controller, $key, $value);
}

function invokeFormattedCsvHeaders(ResultController $controller): array
{
    $m = new ReflectionMethod(ResultController::class, 'formattedCsvHeaders');
    $m->setAccessible(true);

    return $m->invoke($controller);
}

test('formatFormattedExportCell returns PASS when pass is true', function () {
    $c = new ResultController;
    expect(invokeFormattedExportCell($c, 'h1_match', [
        'pass' => true,
        'reason' => 'Headings align.',
    ]))->toBe('PASS');
});

test('formatFormattedExportCell returns FAIL with reason when pass is false', function () {
    $c = new ResultController;
    expect(invokeFormattedExportCell($c, 'content_match', [
        'pass' => false,
        'reason' => 'Section A differs.',
    ]))->toBe('Section A differs.');
});

test('formatFormattedExportCell trims object check reason on failure', function () {
    $c = new ResultController;
    expect(invokeFormattedExportCell($c, 'format_match', [
        'pass' => false,
        'reason' => "  spaced  \n",
    ]))->toBe('spaced');
});

test('formatFormattedExportCell returns PASS when broken_links reports no broken links', function () {
    $c = new ResultController;
    expect(invokeFormattedExportCell($c, 'broken_links', '  No broken links found.  '))->toBe('PASS');
});

test('formatFormattedExportCell returns broken_links details when broken links exist', function () {
    $c = new ResultController;
    expect(invokeFormattedExportCell($c, 'broken_links', '  One link is unreachable: https://example.com  '))
        ->toBe('One link is unreachable: https://example.com');
});

test('formatFormattedExportCell returns missing placeholder for null broken_links', function () {
    $c = new ResultController;
    $out = invokeFormattedExportCell($c, 'broken_links', null);
    expect($out)->toBeString()->not->toBe('');
});

test('formatFormattedExportCell returns missing placeholder for invalid object check', function () {
    $c = new ResultController;
    $out = invokeFormattedExportCell($c, 'author_match', ['pass' => true]);
    expect($out)->toBeString()->not->toBe('PASS');
});

test('formattedCsvHeaders has fourteen columns including inaccessible links', function () {
    $c = new ResultController;
    $headers = invokeFormattedCsvHeaders($c);
    expect($headers)->toHaveCount(14)
        ->and($headers[0])->toBeString()
        ->and($headers[1])->toBeString();
});
