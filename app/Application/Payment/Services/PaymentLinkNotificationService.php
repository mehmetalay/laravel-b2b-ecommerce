<?php

namespace App\Application\Payment\Services;

use App\Application\Mail\Services\PaymentLinkMailDispatchService;
use App\Models\PaymentLink;

class PaymentLinkNotificationService
{
    public function __construct(
        private PaymentLinkMailDispatchService $paymentLinkMailDispatchService
    ) {}

    public function sendPaymentSuccessNotification(PaymentLink $paymentLink): string
    {
        try {
            if ($this->paymentLinkMailDispatchService->sendPaymentSuccessMail($paymentLink)) {
                logSession(
                    "Mail gönderimi başarılı. paymentLinkId {$paymentLink->id}",
                    'info',
                    'payment_logs'
                );

                return optional($paymentLink->user)->receipt_enabled
                    ? 'Tahsilat makbuzu e-posta adresinize gonderilmistir.'
                    : '';
            }

            logSession("Mail gönderimi başarısız. paymentLinkId {$paymentLink->id}", null, 'info', 'payment_logs');
        } catch (\Throwable $th) {
            logSession("Mail gönderimi sırasında hata. paymentLinkId {$paymentLink->id}", null, 'info', 'payment_logs');
        }

        return '';
    }
}
