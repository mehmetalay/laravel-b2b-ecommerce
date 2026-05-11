<?php

namespace App\Application\Payment\Services;

use App\Models\Payment;
use App\Models\PaymentLink;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;

class PaymentLinkLifecycleService
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function resolveLinkedPayment(PaymentLink $paymentLink, array $payload): ?Payment
    {
        $paymentId = (int) ($payload['paymentId'] ?? 0);
        if ($paymentId <= 0) {
            return null;
        }

        $payment = Payment::query()->find($paymentId);
        if (!$payment) {
            logSession('payment-link lifecycle warning: payment not found', [
                'payment_link_id' => $paymentLink->id,
                'payment_id' => $paymentId,
            ], 'warning', 'payment_logs');
            return null;
        }

        if ((int) ($payment->payment_link_id ?? 0) !== (int) $paymentLink->id) {
            logSession('payment-link lifecycle warning: payment_link_id mismatch', [
                'payment_link_id' => $paymentLink->id,
                'payment_id' => $payment->id,
                'payment_payment_link_id' => $payment->payment_link_id,
            ], 'warning', 'payment_logs');
            return null;
        }

        return $payment;
    }

    public function markLinkPaid(PaymentLink $paymentLink, Payment $payment): void
    {
        $update = [
            'is_paid' => 1,
            'payment_date' => now(),
        ];

        if ((int) ($paymentLink->current_payment_id ?? 0) !== (int) $payment->id) {
            $update['current_payment_id'] = $payment->id;
        }

        if ((int) ($paymentLink->paid_payment_id ?? 0) <= 0) {
            $update['paid_payment_id'] = $payment->id;
        }

        $paymentLink->update($update);
    }

    public function handleSuccess(PaymentLink $paymentLink, array $payload): ?Payment
    {
        $paymentId = (int) ($payload['paymentId'] ?? 0);

        if ($paymentId <= 0) {
            DB::transaction(function () use ($paymentLink) {
                $lockedPaymentLink = PaymentLink::query()
                    ->whereKey($paymentLink->id)
                    ->lockForUpdate()
                    ->first();

                if (!$lockedPaymentLink) {
                    return;
                }

                $lockedPaymentLink->update([
                    'is_paid' => 1,
                    'payment_date' => now(),
                ]);
            }, 3);

            return null;
        }

        return DB::transaction(function () use ($paymentLink, $payload, $paymentId) {
            $lockedPaymentLink = PaymentLink::query()
                ->whereKey($paymentLink->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedPaymentLink) {
                return null;
            }

            $payment = Payment::query()
                ->whereKey($paymentId)
                ->lockForUpdate()
                ->first();

            if (!$payment) {
                logSession('payment-link lifecycle warning: payment not found during success transition', [
                    'payment_link_id' => $lockedPaymentLink->id,
                    'payment_id' => $paymentId,
                ], 'warning', 'payment_logs');
                return null;
            }

            if ((int) ($payment->payment_link_id ?? 0) !== (int) $lockedPaymentLink->id) {
                logSession('payment-link lifecycle warning: payment_link_id mismatch during success transition', [
                    'payment_link_id' => $lockedPaymentLink->id,
                    'payment_id' => $payment->id,
                    'payment_payment_link_id' => $payment->payment_link_id,
                ], 'warning', 'payment_logs');
                return null;
            }

            $transition = $this->paymentService->applySuccessTransition($payment, $payload);
            $updatedPayment = $transition['payment'];

            $this->markLinkPaid($lockedPaymentLink, $updatedPayment);

            return $updatedPayment;
        }, 3);
    }

    public function handleFailure(PaymentLink $paymentLink, string $message, array $payload): ?Payment
    {
        $paymentId = (int) ($payload['paymentId'] ?? 0);
        if ($paymentId <= 0) {
            return null;
        }

        return DB::transaction(function () use ($paymentLink, $paymentId, $message, $payload) {
            $lockedPaymentLink = PaymentLink::query()
                ->whereKey($paymentLink->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedPaymentLink) {
                return null;
            }

            $payment = Payment::query()
                ->whereKey($paymentId)
                ->lockForUpdate()
                ->first();

            if (!$payment) {
                logSession('payment-link lifecycle warning: payment not found during failure transition', [
                    'payment_link_id' => $lockedPaymentLink->id,
                    'payment_id' => $paymentId,
                ], 'warning', 'payment_logs');
                return null;
            }

            if ((int) ($payment->payment_link_id ?? 0) !== (int) $lockedPaymentLink->id) {
                logSession('payment-link lifecycle warning: payment_link_id mismatch during failure transition', [
                    'payment_link_id' => $lockedPaymentLink->id,
                    'payment_id' => $payment->id,
                    'payment_payment_link_id' => $payment->payment_link_id,
                ], 'warning', 'payment_logs');
                return null;
            }

            $transition = $this->paymentService->applyFailureTransition($payment, array_merge($payload, [
                'failure_reason' => $message,
            ]));

            return $transition['payment'];
        }, 3);
    }
}
