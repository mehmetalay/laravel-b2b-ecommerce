<?php

namespace App\Application\Payment\Services;

use App\Models\Payment;
use App\Models\PaymentLink;
use Illuminate\Support\Facades\DB;

class PaymentLinkPaymentBindingService
{
    public function bindFromCallback(PaymentLink $paymentLink, array $callbackData, bool $markPaidPayment = false): void
    {
        $paymentId = (int) ($callbackData['paymentId'] ?? 0);
        if ($paymentId <= 0) {
            return;
        }

        try {
            DB::transaction(function () use ($paymentLink, $paymentId, $markPaidPayment) {
                $lockedPaymentLink = PaymentLink::query()
                    ->whereKey($paymentLink->id)
                    ->lockForUpdate()
                    ->first();

                if (!$lockedPaymentLink) {
                    return;
                }

                $payment = Payment::query()
                    ->whereKey($paymentId)
                    ->lockForUpdate()
                    ->first();

                if (!$payment) {
                    logSession('payment-link callback binding skipped: payment not found', [
                        'payment_link_id' => $lockedPaymentLink->id,
                        'payment_id' => $paymentId,
                    ], 'warning', 'payment_logs');
                    return;
                }

                if ((int) ($payment->payment_link_id ?? 0) !== (int) $lockedPaymentLink->id) {
                    logSession('payment-link callback binding skipped: payment does not belong to link', [
                        'payment_link_id' => $lockedPaymentLink->id,
                        'payment_id' => $payment->id,
                        'payment_payment_link_id' => $payment->payment_link_id,
                    ], 'warning', 'payment_logs');
                    return;
                }

                $update = [];
                if ((int) ($lockedPaymentLink->current_payment_id ?? 0) !== (int) $payment->id) {
                    $update['current_payment_id'] = $payment->id;
                }

                if ($markPaidPayment && (int) ($lockedPaymentLink->paid_payment_id ?? 0) !== (int) $payment->id) {
                    $update['paid_payment_id'] = $payment->id;
                }

                if (!empty($update)) {
                    $lockedPaymentLink->update($update);
                }
            }, 3);
        } catch (\Throwable $e) {
            logException($e, 'PaymentLinkPaymentBindingService::bindFromCallback', true);
        }
    }
}
