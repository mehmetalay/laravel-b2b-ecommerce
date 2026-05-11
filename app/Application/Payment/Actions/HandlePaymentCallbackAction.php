<?php

namespace App\Application\Payment\Actions;

use App\Application\Payment\DTO\PaymentFlowResult;
use App\Application\Payment\Services\CallbackModelResolver;
use App\Application\Payment\Services\CallbackProcessorService;
use App\Application\Payment\Services\CallbackSignatureValidator;
use App\Application\Payment\Services\PaymentFailureHandler;
use App\Application\Payment\Services\PaymentLinkPaymentBindingService;
use App\Application\Payment\Services\PaymentSensitiveDataMasker;
use App\Application\Payment\Services\PaymentSuccessHandler;
use App\Application\Payment\Enums\PaymentFlowType;
use App\Models\PaymentLink;

class HandlePaymentCallbackAction
{
    public function __construct(
        private CallbackModelResolver $callbackModelResolver,
        private CallbackSignatureValidator $callbackSignatureValidator,
        private CallbackProcessorService $callbackProcessorService,
        private PaymentSuccessHandler $paymentSuccessHandler,
        private PaymentFailureHandler $paymentFailureHandler,
        private PaymentSensitiveDataMasker $paymentSensitiveDataMasker,
        private PaymentLinkPaymentBindingService $paymentLinkPaymentBindingService
    ) {}

    public function execute(PaymentFlowType $flowType, array $data, bool $isAuthenticated = false): PaymentFlowResult
    {
        $model = $this->callbackModelResolver->resolve($flowType, $data);
        $modelId = $this->callbackModelResolver->resolveModelId($flowType, $data);
        $flowName = $flowType->value;
        $maskedData = $this->paymentSensitiveDataMasker->mask($data);

        logSession(
            "{$flowName} callback islemi basladi. id {$modelId} response",
            $maskedData,
            'info',
            'payment_logs'
        );

        if (!$model) {
            logSession(
                "{$flowName} callback model bulunamadi. id {$modelId}",
                $maskedData,
                'error',
                'payment_logs'
            );

            return PaymentFlowResult::postMessage('error', $flowType->notFoundMessage());
        }

        if (!$this->callbackSignatureValidator->isValid($flowType, $model, $data)) {
            logSession(
                "{$flowName} invalid signature. id {$modelId}",
                [
                    $flowType->contextIdKey() => $modelId,
                    'payload' => $maskedData,
                ],
                'error',
                'payment_logs'
            );

            return PaymentFlowResult::postMessage('error', 'Gecersiz callback imzasi.');
        }

        $callbackResult = $this->callbackProcessorService->process($flowType, $model, $data);
        if ($callbackResult->skipProcessing) {
            $this->bindPaymentLinkToPaymentFromCallback($flowType, $model, $data, false);

            return $this->buildDuplicateResponse(
                flowType: $flowType,
                success: $callbackResult->success,
                message: $callbackResult->message,
                isAuthenticated: $isAuthenticated
            );
        }

        $this->bindPaymentLinkToPaymentFromCallback($flowType, $model, $data, false);

        if ($callbackResult->success) {
            return $this->paymentSuccessHandler->handle(
                flowType: $flowType,
                model: $model,
                bankIntegrationName: $callbackResult->bankIntegrationName,
                payload: $callbackResult->resolvedPayload,
                isAuthenticated: $isAuthenticated
            );
        }

        return $this->paymentFailureHandler->handle(
            flowType: $flowType,
            model: $model,
            bankIntegrationName: $callbackResult->bankIntegrationName,
            message: $callbackResult->message ?: 'Odeme Islemi Basarisiz.',
            payload: $callbackResult->resolvedPayload
        );
    }

    private function buildDuplicateResponse(
        PaymentFlowType $flowType,
        bool $success,
        string $message,
        bool $isAuthenticated
    ): PaymentFlowResult {
        $resolvedMessage = $message !== ''
            ? $message
            : ($success ? 'Odeme Islemi Basariyla Gerceklesti!' : 'Odeme Islemi Basarisiz.');

        if (!$success) {
            return PaymentFlowResult::postMessage('error', $resolvedMessage);
        }

        $url = $flowType === PaymentFlowType::PAYMENT
            ? route('index')
            : ($isAuthenticated ? route('index') : route('login.form'));

        return PaymentFlowResult::postMessage('success', $resolvedMessage, $url);
    }

    private function bindPaymentLinkToPaymentFromCallback(
        PaymentFlowType $flowType,
        $model,
        array $callbackData,
        bool $markPaidPayment
    ): void {
        if ($flowType !== PaymentFlowType::PAYMENT_LINK || !$model instanceof PaymentLink) {
            return;
        }

        $this->paymentLinkPaymentBindingService->bindFromCallback($model, $callbackData, $markPaidPayment);
    }
}
