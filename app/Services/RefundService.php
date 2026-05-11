<?php

namespace App\Services;

use App\Application\Payment\Actions\CancelPaymentAction;
use App\Application\Payment\Actions\RefundPaymentAction;
use App\Models\Payment;
use App\Models\PaymentLink;
use InvalidArgumentException;

class RefundService
{
    public function __construct(
        private CancelPaymentAction $cancelPaymentAction,
        private RefundPaymentAction $refundPaymentAction
    ) {}

    /**
     * @param Payment|PaymentLink $model
     * @param string $action 'cancel' veya 'refund'
     */
    public function process($model, string $action): array
    {
        try {
            logSession('RefundService islem baslatildi.', [
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'action' => $action,
                'bank_integration_id' => $model->bank_integration_id,
                'oid' => $model->oid,
                'amount' => $model->amount_paid,
            ], 'info', 'payment_logs');

            $gatewayResult = $this->resolveAction($action)->execute($model);

            $result = [
                'success' => $gatewayResult->success,
                'message' => $gatewayResult->message ?? ($gatewayResult->success ? 'Islem basarili.' : 'Islem basarisiz.'),
            ];

            logSession('RefundService islem sonucu.', [
                'model_id' => $model->id,
                'action' => $action,
                'result' => $result,
            ], 'info', 'payment_logs');

            return $result;
        } catch (\Throwable $e) {
            logSession('RefundService hata.', [
                'model_id' => $model->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ], 'error', 'payment_logs');

            return [
                'success' => false,
                'message' => 'Islem sirasinda bir hata olustu: ' . $e->getMessage(),
            ];
        }
    }

    private function resolveAction(string $action): CancelPaymentAction|RefundPaymentAction
    {
        return match ($action) {
            'cancel' => $this->cancelPaymentAction,
            'refund' => $this->refundPaymentAction,
            default => throw new InvalidArgumentException('Desteklenmeyen islem tipi: ' . $action),
        };
    }
}
