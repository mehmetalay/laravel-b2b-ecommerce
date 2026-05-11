<?php

namespace App\Application\Payment\Services;

use App\Application\Payment\Enums\PaymentFlowType;
use App\Models\Payment;
use App\Models\PaymentCallbackIdempotency;
use App\Models\PaymentLink;
use Illuminate\Database\QueryException;

class PaymentCallbackIdempotencyService
{
    /**
     * @return array{acquired: bool, record: PaymentCallbackIdempotency}
     */
    public function beginOrGetDuplicate(
        PaymentFlowType $flowType,
        Payment|PaymentLink $model,
        string $providerReference
    ): array {
        $modelType = $flowType === PaymentFlowType::PAYMENT ? 'payment' : 'payment_link';

        try {
            $record = PaymentCallbackIdempotency::create([
                'flow_type' => $flowType->value,
                'model_type' => $modelType,
                'model_id' => $model->id,
                'bank_integration_id' => $model->bank_integration_id,
                'provider_reference' => $providerReference,
                'status' => 'processing',
            ]);

            return ['acquired' => true, 'record' => $record];
        } catch (QueryException $exception) {
            $record = PaymentCallbackIdempotency::query()
                ->where('flow_type', $flowType->value)
                ->where('model_type', $modelType)
                ->where('model_id', $model->id)
                ->where('provider_reference', $providerReference)
                ->firstOrFail();

            return ['acquired' => false, 'record' => $record];
        }
    }

    public function complete(
        PaymentCallbackIdempotency $record,
        bool $success,
        string $message,
        array $resolvedPayload
    ): void {
        $record->update([
            'status' => 'processed',
            'result_success' => $success,
            'result_message' => $message,
            'resolved_payload' => $resolvedPayload,
            'processed_at' => now(),
        ]);
    }
}
