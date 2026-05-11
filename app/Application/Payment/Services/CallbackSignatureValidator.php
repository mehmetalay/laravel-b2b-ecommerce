<?php

namespace App\Application\Payment\Services;

use App\Application\Payment\Enums\PaymentFlowType;
use App\Models\Payment;
use App\Models\PaymentLink;

class CallbackSignatureValidator
{
    public function __construct(
        private PaymentCallbackSecurityService $paymentCallbackSecurityService
    ) {}

    public function isValid(PaymentFlowType $flowType, Payment|PaymentLink $model, array $data): bool
    {
        return match ($flowType) {
            PaymentFlowType::PAYMENT => $this->paymentCallbackSecurityService->verifyPaymentSignature($model, $data),
            PaymentFlowType::PAYMENT_LINK => $this->paymentCallbackSecurityService->verifyPaymentLinkSignature($model, $data),
        };
    }
}
