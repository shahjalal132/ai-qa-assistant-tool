<?php

namespace App\Services;

/**
 * Outcome of fetching a report page URL (HTML expected).
 */
readonly class PageFetchOutcome
{
    public function __construct(
        public PageFetchClassification $classification,
        public int $status,
        public string $body,
        public ?string $contentType,
        public ?string $transportMessage = null,
    ) {}

    public function summaryForMessage(): string
    {
        if ($this->classification === PageFetchClassification::TransportError) {
            return 'transport error'.($this->transportMessage !== null && $this->transportMessage !== '' ? ': '.$this->transportMessage : '');
        }

        $type = $this->contentType ?? 'unknown';

        return match ($this->classification) {
            PageFetchClassification::Ok => 'HTTP '.$this->status.' OK',
            PageFetchClassification::HttpError => 'HTTP '.$this->status.' (Content-Type: '.$type.')',
            PageFetchClassification::NonHtml => 'HTTP '.$this->status.' non-HTML body (Content-Type: '.$type.')',
            PageFetchClassification::ErrorPage => 'HTTP '.$this->status.' error-style HTML page',
        };
    }
}
