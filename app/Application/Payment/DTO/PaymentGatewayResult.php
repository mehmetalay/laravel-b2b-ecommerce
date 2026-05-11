<?php

namespace App\Application\Payment\DTO;

class PaymentGatewayResult
{
    public function __construct(
        public bool $success,
        public ?string $message = null,
        public ?string $html = null,
        public array $payload = []
    ) {}

    public static function successHtml(string $html, array $payload = []): self
    {
        return new self(true, null, $html, $payload);
    }

    public static function successMessage(string $message, array $payload = []): self
    {
        return new self(true, $message, null, $payload);
    }

    public static function failure(string $message, array $payload = []): self
    {
        return new self(false, $message, null, $payload);
    }
}

