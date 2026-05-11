<?php

namespace App\Application\Mail\Services;

use App\Mail\OrderMail;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class OrderMailDispatchService
{
    public function sendOrderMail(Order $order, ?string $recipientEmail = null, bool $markAsSent = true): bool
    {
        try {
            Mail::send(new OrderMail($order, $recipientEmail));

            if (count(Mail::failures()) > 0) {
                logSession('OrderMailDispatchService::sendOrderMail mail gönderimi başarısız.', ['orderId' => $order->id], 'error');
                Mail::raw('order mail gönderimi başarısız oldu.', function ($message) {
                    $message->to(config('services.notifications.error_mail'))->subject(config('app.name') . ' Hata');
                });
                return false;
            }

            if ($markAsSent && $recipientEmail === null) {
                $order->update([
                    'email_sent' => 1,
                    'email_status' => 'sent',
                ]);
            }

            return true;
        } catch (\Throwable $e) {
            logException($e, 'OrderMailDispatchService::sendOrderMail', true);
            return false;
        }
    }

    public function sendPendingOrders(int $limit = 100, bool $dryRun = false): array
    {
        $fifteenMinutesAgo = Carbon::now()->subMinutes(15);

        $items = Order::query()
            ->where('email_sent', 0)
            ->whereNull('email_status')
            ->where('send_email', 1)
            ->where('order_status_id', 4)
            ->where('status', 'approved')
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
            if ($this->sendOrderMail($item)) {
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

