<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment_link;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($payment_link)
    {
        $this->payment_link = $payment_link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->getMailRecipients())
            ->view('emails.payment-link')
            ->with([
                'payment_link' => $this->payment_link
            ])
            ->subject(config('app.name') . ' | Ödeme Linki');
    }

    public function getMailRecipients()
    {
        $emails = $this->payment_link->email;

        $mailTo = array_filter(explode(',', $emails), function($email) {
            return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        });

        Log::info('PaymentLinkMail paymentLinkId: ' . $this->payment_link->id . ' giden mail adresleri: ' . implode(', ', $mailTo));

        return $mailTo;
    }
}
