<?php

namespace App\Services;

use App\Models\Prompt;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    public function __construct(
        private string $model = 'gemini-2.0-flash',
    ) {}

    /**
     * Strip heavy / noisy HTML before sending to the model.
     */
    public function stripHtmlForTokens(string $html): string
    {
        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? '';
        $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html) ?? '';
        $html = preg_replace('#<nav\b[^>]*>.*?</nav>#is', '', $html) ?? '';

        return trim(strip_tags($html));
    }

    /**
     * Call Gemini with JSON output. Requires a non-empty API key in settings (`gemini_api_key`).
     *
     * @param  array<string, mixed>  $schema  Gemini response_schema (JSON Schema object)
     * @return array<string, mixed> Decoded JSON object from the model response
     */
    public function analyze(Prompt $prompt, string $enContent, string $cyContent, array $schema): array
    {
        $apiKey = Setting::getValue('gemini_api_key', '') ?? '';
        if ($apiKey === '') {
            throw new RuntimeException('No gemini_api_key in settings. Save a key in Settings or enable QA_USE_DUMMY_AI.');
        }

        $instruction = $prompt->system_instruction;
        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => "English page content:\n{$enContent}\n\nWelsh page content:\n{$cyContent}\n\nInstruction:\n{$instruction}"],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => $schema,
            ],
        ];

        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
            $this->model
        );

        $response = Http::timeout(90)
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->post($url, $body);

        if (! $response->successful()) {
            throw new RuntimeException('Gemini API error: HTTP '.$response->status().' '.$response->body());
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (! is_string($text) || $text === '') {
            throw new RuntimeException('Gemini returned an empty response.');
        }

        $decoded = json_decode($text, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('Gemini response was not valid JSON.');
        }

        return $decoded;
    }

    /**
     * Same contract as {@see analyze()} for smoke tests: short, valid NHS-style JSON.
     * Uses EN/CY content lengths only in reasons for quick sanity checks.
     */
    public function dummyAnalyze(Prompt $prompt, string $enContent, string $cyContent, array $schema): array
    {
        $enLen = strlen($enContent);
        $cyLen = strlen($cyContent);

        return [
            'content_match' => [
                'pass' => true,
                'reason' => "Dummy: compared stripped text lengths en={$enLen} cy={$cyLen} for prompt #{$prompt->getKey()}.",
            ],
            'h1_match' => ['pass' => true, 'reason' => 'Dummy: not evaluated.'],
            'format_match' => ['pass' => true, 'reason' => 'Dummy: not evaluated.'],
            'author_match' => ['pass' => true, 'reason' => 'Dummy: not evaluated.'],
            'nhsuk_tag_match' => ['pass' => true, 'reason' => 'Dummy: not evaluated.'],
            'report_download_match' => ['pass' => true, 'reason' => 'Dummy: not evaluated.'],
            'welsh_doc_language' => ['pass' => true, 'reason' => 'Dummy: not evaluated.'],
            'alt_text_check' => ['pass' => true, 'reason' => 'Dummy: not evaluated.'],
            'broken_links' => 'Dummy: none checked.',
        ];
    }
}
