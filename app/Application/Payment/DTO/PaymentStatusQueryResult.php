<?php

namespace App\Application\Payment\DTO;

class PaymentStatusQueryResult
{
    public function __construct(
        public bool $supported,
        public string $status,
        public ?string $message = null,
        public ?string $providerReference = null,
        public ?string $authCode = null,
        public ?string $rrn = null,
        public array $rawPayload = []
    ) {}

    public static function unsupported(string $message = 'provider_status_check_not_supported', array $rawPayload = []): self
    {
        return new self(
            supported: false,
            status: 'unknown',
            message: $message,
            providerReference: null,
            authCode: null,
            rrn: null,
            rawPayload: $rawPayload
        );
    }
}
