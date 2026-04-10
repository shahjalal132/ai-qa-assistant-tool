<?php

namespace App\Services;

readonly class LinkProbeResult
{
    public function __construct(
        public string $url,
        public int $status,
        public string $label,
        public bool $isCritical = false,
    ) {}

    public function isOk(): bool
    {
        return $this->label === 'reachable';
    }

    public function lineForMachineBlock(): string
    {
        $crit = $this->isCritical ? '[CRITICAL] ' : '';

        return '- '.$crit.$this->url.' → HTTP '.$this->status.' → '.$this->label;
    }
}
