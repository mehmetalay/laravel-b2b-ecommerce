<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;

class PaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $recipientOverride;
    public $pdf;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Payment $payment, $recipientOverride = null)
    {
        $this->payment = $payment;
        $this->recipientOverride = $recipientOverride;
        $this->pdf = app(PaymentService::class)->generatePaymentReceiptPdf($payment, 'payment');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mailBody = config('app.name') . ' sitesinde ' . format_date_time($this->payment->created_at) . ' tarihinde gerçekleştirilen ödeme işlemine ait ' . $this->payment->id . ' numaralı Online Ödeme Dekontu ekte yer almaktadır.<br><br>Güzel günler dileriz,<br><strong>' . general_info('company_official_name', config('app.name')) . '</strong>';

        return $this->to($this->getMailRecipients())
            ->subject(config('app.name') . ' | Online Ödeme Dekontu')
            ->attachData($this->pdf, 'online-odeme-dekontu.pdf', [
                'mime' => 'application/pdf',
            ])
            ->html($mailBody);
    }

    private function getMailRecipients()
    {
        if ($this->recipientOverride) {
            $emails = trim($this->recipientOverride);
        } else {
            $emails = trim(str_replace(' ', '', additional_setting('payment_emails')));

            if (optional($this->payment->plasiyer)->email) {
                $emails .= ',' . trim($this->payment->plasiyer->email);
            }

            if (optional($this->payment->user)->receipt_enabled && optional($this->payment->user)->email) {
                $emails .= ',' . trim($this->payment->user->email);
            }
        }

        $mailTo = array_filter(
            explode(',', $emails),
            fn($email) => filter_var(trim($email), FILTER_VALIDATE_EMAIL)
        );

        Log::info('PaymentMail paymentId: ' . $this->payment->id . ' giden mail adresleri: ' . implode(', ', $mailTo));

        return $mailTo;
    }
}
