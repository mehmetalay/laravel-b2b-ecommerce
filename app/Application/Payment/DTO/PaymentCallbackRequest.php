<?php

namespace App\Application\Payment\DTO;

class PaymentCallbackRequest
{
    public function __construct(
        public int $bankIntegrationId,
        public ?string $bankCode,
        public object $bankIntegrationInformation,
        public array $payload,
        public string $oid,
        public float $amount,
        public string $okUrl,
        public string $failUrl
    ) {}
}
