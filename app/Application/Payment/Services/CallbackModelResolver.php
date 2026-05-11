<?php

namespace App\Application\Payment\Services;

use App\Application\Payment\Enums\PaymentFlowType;
use App\Models\Payment;
use App\Models\PaymentLink;

class CallbackModelResolver
{
    public function resolve(PaymentFlowType $flowType, array $data): Payment|PaymentLink|null
    {
        return match ($flowType) {
            PaymentFlowType::PAYMENT => Payment::find($this->resolveModelId($flowType, $data)),
            PaymentFlowType::PAYMENT_LINK => PaymentLink::find($this->resolveModelId($flowType, $data)),
        };
    }

    public function resolveModelId(PaymentFlowType $flowType, array $data): int
    {
        return (int) ($data[$flowType->requestIdKey()] ?? 0);
    }
}
