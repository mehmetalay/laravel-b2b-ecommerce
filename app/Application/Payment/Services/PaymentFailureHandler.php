<?php

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\PaymentFlowResult;
use App\Application\Payment\Enums\PaymentFlowType;
use App\Models\Payment;
use App\Models\PaymentLink;
use App\Services\PaymentService;

class PaymentFailureHandler
{
    public function __construct(
        private PaymentService $paymentService,
        private PaymentFailureEffectHandler $paymentFailureEffectHandler,
        private PaymentLinkLifecycleService $paymentLinkLifecycleService
    ) {}

    public function handle(
        PaymentFlowType $flowType,
        Payment|PaymentLink $model,
        string $bankIntegrationName,
        string $message,
        array $payload
    ): PaymentFlowResult {
        if ($flowType === PaymentFlowType::PAYMENT) {
            $transition = $this->paymentService->applyFailureTransition($model, array_merge($payload, [
                'failure_reason' => $message,
            ]));
            $payment = $transition['payment'];
            $statusTransitioned = (bool) ($transition['transitioned'] ?? false);

            $this->paymentFailureEffectHandler->handle($payment, $message, $payload);

            logSession("{$bankIntegrationName} paymentId: {$payment->id} odeme basarisiz: {$message}", null, 'info', 'payment_logs');

            return PaymentFlowResult::postMessage('error', $message);
        }

        $this->paymentLinkLifecycleService->handleFailure($model, $message, $payload);

        logSession("{$bankIntegrationName} paymentLinkId: {$model->id} odeme basarisiz: {$message}", null, 'info', 'payment_logs');

        return PaymentFlowResult::postMessage('error', $message);
    }
}
