<?php

namespace App\Application\Payment\Services;

use App\Application\Mail\Services\PaymentMailDispatchService;
use App\Models\Payment;
use App\Services\EtaBankService;

class PaymentEffectService
{
    public function __construct(
        private EtaBankService $etaBankService,
        private PaymentMailDispatchService $paymentMailDispatchService
    ) {}

    public function runSuccessEffects(Payment $payment, bool $statusTransitioned): string
    {
        if (!$statusTransitioned) {
            logSession(
                "paymentId {$payment->id} duplicate success callback alindi, yan etkiler tekrar calistirilmadi.",
                null,
                'info',
                'payment_logs'
            );

            return '';
        }

        try {
            $this->etaBankService->sendPosTransaction($payment->id);
        } catch (\Throwable $th) {
            logSession("Eta Bank Service hatasi | PaymentID: {$payment->id}", null, 'error', 'payment_logs');
        }

        try {
            if ((int) ($payment->email_sent ?? 0) !== 0) {
                return '';
            }

            if ($this->paymentMailDispatchService->sendPaymentMail($payment)) {
                logSession(
                    "Mail gönderimi başarılı. paymentId {$payment->id}",
                    'info',
                    'payment_logs'
                );

                return optional($payment->user)->receipt_enabled
                    ? 'Tahsilat makbuzu e-posta adresinize gonderilmistir.'
                    : '';
            }

            logSession("Mail gönderimi başarısız. paymentId {$payment->id}", null, 'info', 'payment_logs');
        } catch (\Throwable $th) {
            logSession("Mail gönderimi sırasında hata. paymentId {$payment->id}", null, 'info', 'payment_logs');
        }

        return '';
    }
}
