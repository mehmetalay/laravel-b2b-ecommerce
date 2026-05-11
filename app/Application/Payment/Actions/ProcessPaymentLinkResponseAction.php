<?php

namespace App\Application\Payment\Actions;

use App\Application\Payment\DTO\PaymentFlowResult;
use App\Application\Payment\Enums\PaymentFlowType;

class ProcessPaymentLinkResponseAction
{
    public function __construct(
        private HandlePaymentCallbackAction $handlePaymentCallbackAction
    ) {}

    public function execute(array $data, bool $isAuthenticated): PaymentFlowResult
    {
        return $this->handlePaymentCallbackAction->execute(PaymentFlowType::PAYMENT_LINK, $data, $isAuthenticated);
    }
}
