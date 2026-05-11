<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;

class OrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $recipientOverride;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order, $recipientOverride = null)
    {
        $this->order = $order;
        $this->recipientOverride = $recipientOverride;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->getMailRecipients())
            ->subject(config('app.name') . ' | Yeni Sipariş')
            ->view('emails.order')
            ->with([
                'order' => $this->order,
            ]);
    }

    private function getMailRecipients()
    {
        if ($this->recipientOverride) {
            $emails = trim($this->recipientOverride);
        } else {
            $emails = trim(str_replace(' ', '', additional_setting('order_emails')));

            if (optional($this->order->plasiyer)->email) {
                $emails .= ',' . trim($this->order->plasiyer->email);
            }

            if (optional($this->order->user)->email) {
                $emails .= ',' . trim($this->order->user->email);
            }
        }

        $mailTo = array_filter(
            explode(',', $emails),
            fn($email) => filter_var(trim($email), FILTER_VALIDATE_EMAIL)
        );

        Log::info('OrderMail orderId: ' . $this->order->id . ' giden mail adresleri: ' . implode(', ', $mailTo));

        return $mailTo;
    }
}
