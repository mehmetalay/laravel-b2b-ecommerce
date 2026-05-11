<?php

namespace App\Application\Payment\DTO;

class CallbackProcessorResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public string $bankIntegrationName,
        public array $resolvedPayload = [],
        public bool $skipProcessing = false
    ) {}
}
