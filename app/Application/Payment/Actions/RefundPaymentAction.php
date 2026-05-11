<?php

namespace App\Application\Payment\Actions;

use App\Application\Payment\DTO\PaymentGatewayResult;
use App\Application\Payment\Mappers\PaymentPayloadMapper;
use App\Application\Payment\Services\PaymentOrchestrationService;
use App\Models\Payment;
use App\Models\PaymentLink;

class RefundPaymentAction
{
    public function __construct(
        private PaymentOrchestrationService $paymentOrchestrationService,
        private PaymentPayloadMapper $paymentPayloadMapper
    ) {}

    /**
     * @param Payment|PaymentLink $model
     */
    public function execute($model): PaymentGatewayResult
    {
        $refundRequest = $this->paymentPayloadMapper->toRefundRequest($model);

        return $this->paymentOrchestrationService->refund($refundRequest);
    }
}

