<?php

namespace App\Application\Payment\DTO;

class PaymentStatusQueryRequest
{
    public function __construct(
        public int $paymentId,
        public int $bankIntegrationId,
        public ?string $bankCode,
        public object $bankIntegrationInformation,
        public string $oid,
        public float $amount,
        public ?string $providerReference = null,
        public ?string $createdAt = null
    ) {}

    public function toProviderPayload(): array
    {
        return [
            'payment_id' => $this->paymentId,
            'bank_integration_id' => $this->bankIntegrationId,
            'bank_code' => $this->bankCode,
            'bank_integration_information' => $this->bankIntegrationInformation,
            'oid' => $this->oid,
            'provider_reference' => $this->providerReference,
            'amount' => $this->amount,
            'created_at' => $this->createdAt,
        ];
    }
}
