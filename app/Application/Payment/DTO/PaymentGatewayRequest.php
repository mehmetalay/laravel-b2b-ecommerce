<?php

namespace App\Application\Payment\DTO;

class PaymentGatewayRequest
{
    public function __construct(
        public int $bankIntegrationId,
        public ?string $bankCode,
        public object $bankIntegrationInformation,
        public float $amount,
        public string $oid,
        public int $installment,
        public string $creditCardName,
        public string $creditCardNumber,
        public string $creditCardExpMonth,
        public string $creditCardExpYear,
        public string $cvc,
        public string $okUrl,
        public string $failUrl,
        public ?int $paymentId = null,
        public ?int $paymentLinkId = null
    ) {}

    public function toProviderPayload(): array
    {
        $payload = [
            'bank_code' => $this->bankCode,
            'bank_integration_information' => $this->bankIntegrationInformation,
            'amount' => $this->amount,
            'oid' => $this->oid,
            'installment' => $this->installment,
            'credit_card_name' => $this->creditCardName,
            'credit_card_number' => $this->creditCardNumber,
            'credit_card_exp_date_month' => $this->creditCardExpMonth,
            'credit_card_exp_date_year' => $this->creditCardExpYear,
            'cvc' => $this->cvc,
            'ok_url' => $this->okUrl,
            'fail_url' => $this->failUrl,
        ];

        if ($this->paymentId !== null) {
            $payload['payment_id'] = $this->paymentId;
        }

        if ($this->paymentLinkId !== null) {
            $payload['payment_link_id'] = $this->paymentLinkId;
        }

        return $payload;
    }
}
