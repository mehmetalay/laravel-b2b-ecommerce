<?php

namespace App\Application\Payment\DTO;

class PaymentCallbackResult
{
    public function __construct(
        public bool $success,
        public ?string $message = null,
        public array $payload = []
    ) {}

    public static function success(array $payload = []): self
    {
        return new self(true, null, $payload);
    }

    public static function failure(string $message, array $payload = []): self
    {
        return new self(false, $message, $payload);
    }
}

