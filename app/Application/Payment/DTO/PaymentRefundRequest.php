<?php

namespace App\Application\Payment\DTO;

class PaymentRefundRequest
{
    public function __construct(
        public int $bankIntegrationId,
        public ?string $bankCode,
        public object $bankIntegrationInformation,
        public string $oid,
        public float $amount,
        public ?string $providerReference = null
    ) {}

    public function toProviderPayload(): array
    {
        return [
            'bank_code' => $this->bankCode,
            'bank_integration_information' => $this->bankIntegrationInformation,
            'oid' => $this->oid,
            'provider_reference' => $this->providerReference,
            'amount' => $this->amount,
        ];
    }
}
