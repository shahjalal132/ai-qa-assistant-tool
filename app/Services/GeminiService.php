<?php

namespace App\Services;

use App\Models\Prompt;
use App\Models\Setting;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GeminiService
{
    public function __construct(
        private string $model = 'gemini-2.0-flash',
    ) {}

    /**
     * Strip heavy / noisy HTML before sending to the model.
     * Preserves semantic HTML structure while removing noise, scripts, and chrome.
     */
    public function stripHtmlForTokens(string $html): string
    {
        // Remove noise elements completely
        $html = $this->removeNoiseElements($html);

        // Extract main content as structured HTML (not plain text)
        $structuredHtml = $this->extractStructuredMainContent($html);
        if ($structuredHtml === '') {
            // Fallback: use full body but clean it up
            $structuredHtml = $this->cleanAndExtractBody($html);
        }

        // Clean up attributes on remaining elements
        $structuredHtml = $this->cleanAttributes($structuredHtml);

        // Normalize whitespace
        $structuredHtml = $this->normalizeHtmlWhitespace($structuredHtml);

        // Apply length limit if configured
        $max = (int) config('qa.max_stripped_content_length', 65535);
        if ($max > 0 && strlen($structuredHtml) > $max) {
            $structuredHtml = Str::limit($structuredHtml, $max, '…', preserveWords: false);
        }

        return $structuredHtml;
    }

    /**
     * Remove scripts, styles, and other noise elements.
     */
    private function removeNoiseElements(string $html): string
    {
        // Remove script tags and their content
        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? '';

        // Remove style tags and their content
        $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html) ?? '';

        // Remove meta, link tags (single tags)
        $html = preg_replace('#<(meta|link)\b[^>]*>#is', '', $html) ?? '';

        // Remove HTML comments
        $html = preg_replace('#<!--.*?-->#s', '', $html) ?? '';

        // Remove chrome elements: nav, header, footer, aside, form
        foreach (['nav', 'header', 'footer', 'aside', 'form'] as $tag) {
            $html = preg_replace('#<'.$tag.'\b[^>]*>.*?</'.$tag.'>#is', '', $html) ?? '';
        }

        // Remove noscript tags
        $html = preg_replace('#<noscript\b[^>]*>.*?</noscript>#is', '', $html) ?? '';

        return $html;
    }

    /**
     * Extract main content area as structured HTML preserving tags.
     */
    private function extractStructuredMainContent(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $useErrors = libxml_use_internal_errors(true);
        try {
            $dom = new \DOMDocument;
            $loaded = @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
            if (! $loaded) {
                return '';
            }

            $xpath = new DOMXPath($dom);

            // Try to find main content area (main, role=main, or article)
            foreach ([
                '//main',
                '//*[translate(@role, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="main"]',
                '//article',
            ] as $expression) {
                $nodes = $xpath->query($expression);
                if ($nodes === false || $nodes->length === 0) {
                    continue;
                }

                foreach ($nodes as $node) {
                    $content = $this->domNodeToCleanHtml($node);
                    if ($content !== '') {
                        return $content;
                    }
                }
            }

            return '';
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($useErrors);
        }
    }

    /**
     * Extract and clean the body content as fallback.
     */
    private function cleanAndExtractBody(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $useErrors = libxml_use_internal_errors(true);
        try {
            $dom = new \DOMDocument;
            $loaded = @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
            if (! $loaded) {
                return '';
            }

            $body = $dom->getElementsByTagName('body')->item(0);
            if (! $body) {
                return '';
            }

            return $this->domNodeToCleanHtml($body);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($useErrors);
        }
    }

    /**
     * Convert a DOM node to cleaned HTML string.
     */
    private function domNodeToCleanHtml(\DOMNode $node): string
    {
        if ($node instanceof \DOMElement) {
            $cleanNode = $this->cleanDomElement($node);

            return $this->domElementToHtml($cleanNode);
        }

        return '';
    }

    /**
     * Create a cleaned copy of a DOM element preserving only essential tags and attributes.
     */
    private function cleanDomElement(\DOMElement $element): \DOMElement
    {
        // Tags to preserve
        $allowedTags = [
            'main', 'article', 'section', 'div',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'p', 'span',
            'ul', 'ol', 'li',
            'table', 'thead', 'tbody', 'tr', 'td', 'th',
            'strong', 'b', 'em', 'i', 'u', 'small',
            'a', 'img', 'br', 'hr',
            'dl', 'dt', 'dd',
            'blockquote', 'pre', 'code',
        ];

        $doc = new \DOMDocument;

        // Create a fresh element with the same tag name (no attributes)
        $root = $doc->createElement($element->tagName);

        // Add allowed attributes from the original
        $this->cleanElementAttributes($element, $root);

        $this->copyChildrenPreservingStructure($element, $root, $doc, $allowedTags);

        return $root;
    }

    /**
     * Copy only whitelisted attributes from source element to target element.
     */
    private function cleanElementAttributes(\DOMElement $source, \DOMElement $target): void
    {
        $tag = strtolower($source->tagName);

        foreach ($source->attributes as $attr) {
            $name = strtolower($attr->name);
            $value = $attr->value;

            // Always keep href on links
            if ($name === 'href' && $tag === 'a' && $value !== '') {
                $target->setAttribute('href', $value);
                continue;
            }

            // Always keep src and alt on images
            if ($tag === 'img' && in_array($name, ['src', 'alt'], true)) {
                $target->setAttribute($name, $value);
                continue;
            }

            // Keep id only on h1-h6 (for heading identification)
            if ($name === 'id' && in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)) {
                $target->setAttribute('id', $value);
                continue;
            }

            // Keep class only if it contains nhsuk- prefix
            if ($name === 'class') {
                $nhsukClasses = array_filter(
                    explode(' ', $value),
                    fn ($cls) => str_starts_with($cls, 'nhsuk-')
                );
                if (! empty($nhsukClasses)) {
                    $target->setAttribute('class', implode(' ', $nhsukClasses));
                }
                continue;
            }
        }
    }

    /**
     * Recursively copy children while filtering tags and cleaning attributes.
     */
    private function copyChildrenPreservingStructure(\DOMElement $source, \DOMElement $target, \DOMDocument $doc, array $allowedTags): void
    {
        foreach ($source->childNodes as $child) {
            if ($child instanceof \DOMText) {
                $text = trim($child->textContent);
                if ($text !== '') {
                    $target->appendChild($doc->createTextNode($text));
                }
            } elseif ($child instanceof \DOMElement) {
                $tag = strtolower($child->tagName);

                // Skip if not an allowed tag
                if (! in_array($tag, $allowedTags, true)) {
                    // Still recurse into children to extract any text or nested allowed tags
                    $this->copyChildrenPreservingStructure($child, $target, $doc, $allowedTags);
                    continue;
                }

                // Create new element with cleaned attributes
                $newChild = $doc->createElement($tag);
                $this->cleanElementAttributes($child, $newChild);
                $target->appendChild($newChild);

                // Recurse into children
                $this->copyChildrenPreservingStructure($child, $newChild, $doc, $allowedTags);
            }
        }
    }

    /**
     * Convert DOM element to HTML string.
     */
    private function domElementToHtml(\DOMElement $element): string
    {
        $doc = new \DOMDocument;
        $imported = $doc->importNode($element, true);
        $doc->appendChild($imported);

        $html = $doc->saveHTML();
        if ($html === false) {
            return '';
        }

        // Remove the XML encoding we added
        $html = preg_replace('#^<\?xml[^?]*\?>
?#', '', $html);

        return $html ?? '';
    }

    /**
     * Clean attributes on all HTML elements using regex for remaining elements.
     */
    private function cleanAttributes(string $html): string
    {
        // This is a secondary pass to clean any remaining unwanted attributes
        // that might have come from regex operations before DOM processing

        // Remove inline styles
        $html = preg_replace('#\s+style="[^"]*"#i', '', $html) ?? $html;

        // Remove data-* attributes
        $html = preg_replace('#\s+data-[a-z0-9-]+="[^"]*"#i', '', $html) ?? $html;

        // Remove on* event handlers
        $html = preg_replace('#\s+on[a-z]+="[^"]*"#i', '', $html) ?? $html;

        // Remove aria-* attributes (can be noise for QA)
        $html = preg_replace('#\s+aria-[a-z]+="[^"]*"#i', '', $html) ?? $html;

        // Remove role attributes except on specific semantic elements
        // Note: We already cleaned via DOM, this is extra cleanup

        return $html;
    }

    /**
     * Normalize whitespace in HTML (preserve structure but clean up spacing).
     */
    private function normalizeHtmlWhitespace(string $html): string
    {
        // Collapse multiple spaces within tags
        $html = preg_replace('/\s+/u', ' ', $html) ?? $html;

        // Trim whitespace around tags while preserving tag structure
        $html = preg_replace('/>\s+</u', '><', $html) ?? $html;

        return trim($html);
    }

    /**
     * Legacy method: Extract plain text fallback (not used for main flow anymore).
     */
    private function extractPreferredPlainText(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $useErrors = libxml_use_internal_errors(true);
        try {
            $dom = new \DOMDocument;
            $loaded = @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
            if (! $loaded) {
                return '';
            }

            $xpath = new DOMXPath($dom);
            foreach ([
                '//main',
                '//*[translate(@role, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="main"]',
                '//article',
            ] as $expression) {
                $nodes = $xpath->query($expression);
                if ($nodes === false) {
                    continue;
                }
                foreach ($nodes as $node) {
                    $chunk = $this->normalizeWhitespace($node->textContent ?? '');
                    if ($chunk !== '') {
                        return $chunk;
                    }
                }
            }

            $body = $dom->getElementsByTagName('body')->item(0);
            if ($body) {
                return $this->normalizeWhitespace($body->textContent ?? '');
            }

            return '';
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($useErrors);
        }
    }

    private function normalizeWhitespace(string $text): string
    {
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
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
