<?php

namespace App\Mail;

use App\Models\PaymentLink;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;

class PaymentLinkMailPaymentSuccess extends Mailable
{
    use Queueable, SerializesModels;

    public $payment_link;
    public $pdf;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(PaymentLink $payment_link)
    {
        $this->payment_link = $payment_link;
        $this->pdf = app(PaymentService::class)->generatePaymentReceiptPdf($payment_link, 'paymentLink');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mailBody = config('app.name') . ' sitesinde ' . format_date_time($this->payment_link->created_at) . ' tarihinde gerçekleştirilen ödeme işlemine ait ' . $this->payment_link->id . ' numaralı Online Ödeme Linki Dekontu ekte yer almaktadır.<br><br>Güzel günler dileriz,<br><strong>' . general_info('company_official_name', 'ÖZDOĞAN HIRDAVAT SAN. TİC. LTD. ŞTİ.') . '</strong>';

        return $this->to($this->getMailRecipients())
            ->subject(config('app.name') . ' | Online Ödeme Linki Dekontu')
            ->attachData($this->pdf, 'online-odeme-linki-dekontu.pdf', [
                'mime' => 'application/pdf',
            ])
            ->html($mailBody);
    }

    private function getMailRecipients()
    {
        $emails = trim(str_replace(' ', '', additional_setting('payment_emails')));
        $emails .= isset($this->payment_link->plasiyer) && $this->payment_link->plasiyer->email != null ? ',' . trim($this->payment_link->plasiyer->email) : '';

        if ($this->payment_link->user->receipt_enabled) {
            $emails .= $this->payment_link->email != null ? ',' . trim($this->payment_link->email) : '';
        }

        $mailTo = array_filter(explode(',', $emails), function($email) {
            return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        });

        Log::info('PaymentLinkMail paymentId: ' . $this->payment_link->id . ' giden mail adresleri: ' . implode(', ', $mailTo));

        return $mailTo;
    }
}
