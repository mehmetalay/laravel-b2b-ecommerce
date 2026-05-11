<?php

namespace App\Application\Mail\Services;

use App\Mail\PaymentMail;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Mail;

class PaymentMailDispatchService
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function sendPaymentMail(Payment $payment, ?string $recipientEmail = null, bool $markAsSent = true): bool
    {
        try {
            Mail::send(new PaymentMail($payment, $recipientEmail));

            if (count(Mail::failures()) > 0) {
                return false;
            }

            if ($markAsSent && $recipientEmail === null) {
                $this->paymentService->updateRaw($payment->id, [
                    'email_sent' => 1,
                    'email_sent_at' => now(),
                ]);
            }

            return true;
        } catch (\Throwable $e) {
            logException($e, 'PaymentMailDispatchService::sendPaymentMail', true);
            return false;
        }
    }

    public function sendPendingPayments(int $limit = 100, bool $dryRun = false): array
    {
        $fifteenMinutesAgo = now()->subMinutes(15);

        $payments = Payment::query()
            ->where('email_sent', 0)
            ->where('status', 'SUCCESS')
            ->where(function ($query) use ($fifteenMinutesAgo) {
                $query->where('created_at', '<', $fifteenMinutesAgo)
                    ->orWhere('updated_at', '<', $fifteenMinutesAgo);
            })
            ->orderBy('id')
            ->limit(max(1, $limit))
            ->get();

        if ($dryRun) {
            return [
                'processed' => $payments->count(),
                'sent' => 0,
                'failed' => 0,
                'dry_run' => true,
            ];
        }

        $sent = 0;
        $failed = 0;

        foreach ($payments as $payment) {
            if ($this->sendPaymentMail($payment)) {
                $sent++;
                continue;
            }

            $failed++;
        }

        return [
            'processed' => $payments->count(),
            'sent' => $sent,
            'failed' => $failed,
            'dry_run' => false,
        ];
    }
}

