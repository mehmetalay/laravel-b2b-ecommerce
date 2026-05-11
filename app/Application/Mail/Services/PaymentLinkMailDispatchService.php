<?php

namespace App\Application\Mail\Services;

use App\Mail\PaymentLinkMail;
use App\Mail\PaymentLinkMailPaymentSuccess;
use App\Models\PaymentLink;
use Illuminate\Support\Facades\Mail;

class PaymentLinkMailDispatchService
{
    public function sendPaymentLinkMail(PaymentLink $paymentLink, bool $markAsSent = true): bool
    {
        try {
            Mail::send(new PaymentLinkMail($paymentLink));

            if (count(Mail::failures()) > 0) {
                logSession('PaymentLinkMailDispatchService::sendPaymentLinkMail mail gönderimi başarısız.', ['paymentLinkId' => $paymentLink->id], 'error');
                Mail::raw('paymentLink mail gönderimi başarısız oldu.', function ($message) {
                    $message->to(config('services.notifications.error_mail'))->subject(config('app.name') . ' Hata');
                });
                return false;
            }

            if ($markAsSent) {
                $paymentLink->update([
                    'email_sent' => 1,
                ]);
            }

            return true;
        } catch (\Throwable $e) {
            logException($e, 'PaymentLinkMailDispatchService::sendPaymentLinkMail', true);
            return false;
        }
    }

    public function sendPaymentSuccessMail(PaymentLink $paymentLink, bool $markAsSent = true): bool
    {
        try {
            Mail::send(new PaymentLinkMailPaymentSuccess($paymentLink));

            if (count(Mail::failures()) > 0) {
                logSession('PaymentLinkMailDispatchService::sendPaymentSuccessMail mail gönderimi başarısız.', ['paymentLinkId' => $paymentLink->id], 'error');
                Mail::raw('paymentLinkPaymentSuccess mail gönderimi başarısız oldu.', function ($message) {
                    $message->to(config('services.notifications.error_mail'))->subject(config('app.name') . ' Hata');
                });
                return false;
            }

            if ($markAsSent) {
                $paymentLink->update([
                    'paid_email_sent' => 1,
                ]);
            }

            return true;
        } catch (\Throwable $e) {
            logException($e, 'PaymentLinkMailDispatchService::sendPaymentSuccessMail', true);
            return false;
        }
    }

    public function sendPendingPaymentLinks(int $limit = 100, bool $dryRun = false): array
    {
        $items = PaymentLink::query()
            ->where('email_sent', 0)
            ->whereNotNull('email')
            ->where('is_paid', 0)
            ->where('status', 1)
            ->orderBy('id')
            ->limit(max(1, $limit))
            ->get();

        if ($dryRun) {
            return [
                'processed' => $items->count(),
                'sent' => 0,
                'failed' => 0,
                'dry_run' => true,
            ];
        }

        $sent = 0;
        $failed = 0;

        foreach ($items as $item) {
            if ($this->sendPaymentLinkMail($item)) {
                $sent++;
                continue;
            }

            $failed++;
        }

        return [
            'processed' => $items->count(),
            'sent' => $sent,
            'failed' => $failed,
            'dry_run' => false,
        ];
    }

    public function sendPendingPaymentLinkSuccesses(int $limit = 100, bool $dryRun = false): array
    {
        $fifteenMinutesAgo = now()->subMinutes(15);

        $items = PaymentLink::query()
            ->where('paid_email_sent', 0)
            ->where('is_paid', 1)
            ->where(function ($query) use ($fifteenMinutesAgo) {
                $query->where('created_at', '<', $fifteenMinutesAgo)
                    ->orWhere('updated_at', '<', $fifteenMinutesAgo);
            })
            ->orderBy('id')
            ->limit(max(1, $limit))
            ->get();

        if ($dryRun) {
            return [
                'processed' => $items->count(),
                'sent' => 0,
                'failed' => 0,
                'dry_run' => true,
            ];
        }

        $sent = 0;
        $failed = 0;

        foreach ($items as $item) {
            if ($this->sendPaymentSuccessMail($item)) {
                $sent++;
                continue;
            }

            $failed++;
        }

        return [
            'processed' => $items->count(),
            'sent' => $sent,
            'failed' => $failed,
            'dry_run' => false,
        ];
    }
}

