<?php

namespace App\Services;

/**
 * Deterministic QA JSON when pages cannot be audited normally.
 */
class QaSyntheticResultBuilder
{
    /**
     * @param  array{en: string, cy: string}  $summaries  Human-readable per-language fetch outcome
     * @return array<string, mixed>
     */
    public function buildAllChecksFail(array $summaries): array
    {
        $pair = 'English: '.$summaries['en'].'; Welsh: '.$summaries['cy'];

        $reason = static fn (string $detail): array => [
            'pass' => false,
            'reason' => $detail.' ('.$pair.')',
        ];

        return [
            'content_match' => $reason('Cannot compare page content'),
            'h1_match' => $reason('Cannot verify H1 against URL'),
            'format_match' => $reason('Cannot compare HTML structure'),
            'author_match' => $reason('Cannot compare authors'),
            'nhsuk_tag_match' => $reason('Cannot compare NHSUK tags'),
            'report_download_match' => $reason('Cannot verify report downloads from provided pages'),
            'welsh_doc_language' => $reason('Cannot verify Welsh document language'),
            'alt_text_check' => $reason('Cannot audit images'),
            'broken_links' => 'Body link auditing skipped or limited due to page fetch issues. '.$pair,
        ];
    }
}
