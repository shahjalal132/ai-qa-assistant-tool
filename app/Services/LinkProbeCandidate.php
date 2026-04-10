<?php

namespace App\Services;

readonly class LinkProbeCandidate
{
    public function __construct(
        public string $url,
        public bool $isCritical,
    ) {}
}
