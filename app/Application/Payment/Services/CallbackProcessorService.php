<?php

namespace App\Application\Payment\Services;

use App\Application\Payment\DTO\CallbackProcessorResult;
use App\Application\Payment\Mappers\PaymentPayloadMapper;
use App\Application\Payment\Enums\PaymentFlowType;
use App\Models\Payment;
use App\Models\PaymentLink;

class CallbackProcessorService
{
    public function __construct(
        private PaymentOrchestrationService $paymentOrchestrationService,
        private PaymentPayloadMapper $paymentPayloadMapper,
        private PaymentCallbackIdempotencyService $paymentCallbackIdempotencyService
    ) {}

    public function process(PaymentFlowType $flowType, Payment|PaymentLink $model, array $data): CallbackProcessorResult
    {
        $this->logDuplicateGuardHint($flowType, $model);
        $bankIntegrationName = (string) $model->bankIntegration->full_name;
        $providerReference = $this->resolveProviderReference($data);
        $idempotencyRecord = null;

        if ($providerReference !== null) {
            $reservation = $this->paymentCallbackIdempotencyService->beginOrGetDuplicate(
                $flowType,
                $model,
                $providerReference
            );

            if (!$reservation['acquired']) {
                $record = $reservation['record'];
                logSession(
                    "{$flowType->value} callback duplicate detected. modelId {$model->id}",
                    [
                        $flowType->contextIdKey() => $model->id,
                        'provider_reference' => $providerReference,
                    ],
                    'info',
                    'payment_logs'
                );

                return new CallbackProcessorResult(
                    success: (bool) ($record->result_success ?? false),
                    message: (string) ($record->result_message ?? 'Odeme durumunuz kisa sure icinde kontrol edilecektir.'),
                    bankIntegrationName: $bankIntegrationName,
                    resolvedPayload: (array) ($record->resolved_payload ?? $data),
                    skipProcessing: true
                );
            }

            $idempotencyRecord = $reservation['record'];
        }

        $callbackRequest = $this->paymentPayloadMapper->toCallbackRequestFromModel($model, $data);
        $callbackResult = $this->paymentOrchestrationService->resolveCallback($callbackRequest);

        $providerFields = $this->paymentPayloadMapper->extractProviderFields(
            array_merge($data, $callbackResult->payload)
        );
        $resolvedPayload = array_merge($data, $callbackResult->payload, $providerFields);
        $resolvedProviderReference = $this->resolveProviderReference($resolvedPayload);

        if ($idempotencyRecord === null && $resolvedProviderReference !== null) {
            $reservation = $this->paymentCallbackIdempotencyService->beginOrGetDuplicate(
                $flowType,
                $model,
                $resolvedProviderReference
            );

            if (!$reservation['acquired']) {
                $record = $reservation['record'];
                logSession(
                    "{$flowType->value} callback duplicate detected after resolve. modelId {$model->id}",
                    [
                        $flowType->contextIdKey() => $model->id,
                        'provider_reference' => $resolvedProviderReference,
                    ],
                    'info',
                    'payment_logs'
                );

                return new CallbackProcessorResult(
                    success: (bool) ($record->result_success ?? false),
                    message: (string) ($record->result_message ?? 'Odeme durumunuz kisa sure icinde kontrol edilecektir.'),
                    bankIntegrationName: $bankIntegrationName,
                    resolvedPayload: (array) ($record->resolved_payload ?? $resolvedPayload),
                    skipProcessing: true
                );
            }

            $idempotencyRecord = $reservation['record'];
        }

        if ($idempotencyRecord !== null) {
            $this->paymentCallbackIdempotencyService->complete(
                $idempotencyRecord,
                (bool) $callbackResult->success,
                (string) ($callbackResult->message ?? ''),
                $resolvedPayload
            );
        }

        return new CallbackProcessorResult(
            success: $callbackResult->success,
            message: (string) ($callbackResult->message ?? ''),
            bankIntegrationName: $bankIntegrationName,
            resolvedPayload: $resolvedPayload
        );
    }

    private function resolveProviderReference(array $payload): ?string
    {
        $providerFields = $this->paymentPayloadMapper->extractProviderFields($payload);
        $reference = trim((string) ($providerFields['provider_reference'] ?? ''));

        return $reference !== '' ? $reference : null;
    }

    private function logDuplicateGuardHint(PaymentFlowType $flowType, Payment|PaymentLink $model): void
    {
        if ($flowType === PaymentFlowType::PAYMENT) {
            $status = strtolower((string) $model->status);
            $refundStatus = strtolower((string) ($model->refund_status ?? ''));

            if ($status === 'success' || $status === 'failed' || $refundStatus !== '') {
                logSession(
                    "payment callback duplicate guard hint. paymentId {$model->id}",
                    [
                        'payment_id' => $model->id,
                        'status' => $model->status,
                        'refund_status' => $model->refund_status,
                    ],
                    'info',
                    'payment_logs'
                );
            }

            return;
        }

        if ((int) $model->is_paid === 1) {
            logSession(
                "paymentLink callback duplicate guard hint. paymentLinkId {$model->id}",
                [
                    'payment_link_id' => $model->id,
                    'is_paid' => $model->is_paid,
                ],
                'info',
                'payment_logs'
            );
        }
    }
}
