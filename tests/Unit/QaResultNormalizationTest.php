<?php

use App\Services\GeminiService;

test('normalizeQaResultAgainstSchema fills missing keys from schema', function () {
    $gemini = app(GeminiService::class);
    $schema = [
        'properties' => [
            'broken_links' => ['type' => 'string'],
            'alt_text_check' => [
                'type' => 'object',
                'properties' => [
                    'pass' => ['type' => 'boolean'],
                    'reason' => ['type' => 'string'],
                ],
            ],
        ],
    ];

    $out = $gemini->normalizeQaResultAgainstSchema([], $schema);

    expect($out)->toHaveKeys(['broken_links', 'alt_text_check'])
        ->and($out['broken_links'])->toBeString()->not->toBe('')
        ->and($out['alt_text_check'])->toBeArray()
        ->and($out['alt_text_check']['pass'])->toBeTrue()
        ->and($out['alt_text_check']['reason'])->toBeString()->not->toBe('');
});

test('normalizeQaResultAgainstSchema keeps complete model output', function () {
    $gemini = app(GeminiService::class);
    $schema = [
        'properties' => [
            'broken_links' => ['type' => 'string'],
            'content_match' => [
                'type' => 'object',
                'properties' => [
                    'pass' => ['type' => 'boolean'],
                    'reason' => ['type' => 'string'],
                ],
            ],
        ],
    ];
    $data = [
        'broken_links' => 'No broken links found.',
        'content_match' => ['pass' => false, 'reason' => 'Mismatch in section two.'],
    ];

    $out = $gemini->normalizeQaResultAgainstSchema($data, $schema);

    expect($out)->toBe($data);
});

test('normalizeQaResultAgainstSchema returns data unchanged when schema has no properties', function () {
    $gemini = app(GeminiService::class);
    $data = ['custom' => 'x'];

    expect($gemini->normalizeQaResultAgainstSchema($data, []))->toBe($data)
        ->and($gemini->normalizeQaResultAgainstSchema($data, ['type' => 'object']))->toBe($data);
});
