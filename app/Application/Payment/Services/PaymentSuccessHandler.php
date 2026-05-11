<?php

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\PaymentFlowResult;
use App\Application\Payment\Enums\PaymentFlowType;
use App\Models\Payment;
use App\Models\PaymentLink;
use App\Services\PaymentService;

class PaymentSuccessHandler
{
    public function __construct(
        private PaymentService $paymentService,
        private PaymentSuccessEffectHandler $paymentSuccessEffectHandler,
        private PaymentLinkNotificationService $paymentLinkNotificationService,
        private PaymentLinkLifecycleService $paymentLinkLifecycleService
    ) {}

    public function handle(
        PaymentFlowType $flowType,
        Payment|PaymentLink $model,
        string $bankIntegrationName,
        array $payload,
        bool $isAuthenticated = false
    ): PaymentFlowResult {
        if ($flowType === PaymentFlowType::PAYMENT) {
            $message = 'Odeme Islemi Basariyla Gerceklesti!';
            $transition = $this->paymentService->applySuccessTransition($model, $payload);
            $payment = $transition['payment'];
            $statusTransitioned = (bool) ($transition['transitioned'] ?? false);
            $message .= $this->paymentSuccessEffectHandler->handle($payment, $statusTransitioned);

            if (!$statusTransitioned) {
                logSession(
                    "payment callback duplicate bildirimi. paymentId {$payment->id}",
                    ['payment_id' => $payment->id],
                    'info',
                    'payment_logs'
                );
            }

            logSession("{$bankIntegrationName} paymentId: {$payment->id} odeme basarili.", null, 'info', 'payment_logs');

            return PaymentFlowResult::postMessage('success', $message, route('index'));
        }

        $message = 'Odeme Islemi Basariyla Gerceklesti!';
        $hasPaymentId = (int) ($payload['paymentId'] ?? 0) > 0;
        $linkedPayment = $this->paymentLinkLifecycleService->handleSuccess($model, $payload);

        $shouldSendNotification = !$hasPaymentId || $linkedPayment !== null;
        if ($shouldSendNotification) {
            $message .= $this->paymentLinkNotificationService->sendPaymentSuccessNotification($model);
        }

        $url = $isAuthenticated ? route('index') : route('login.form');

        logSession("{$bankIntegrationName} paymentLinkId: {$model->id} odeme basarili.", null, 'info', 'payment_logs');

        return PaymentFlowResult::postMessage('success', $message, $url);
    }
}
