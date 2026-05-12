<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use App\Services\StatementReportService;

class CustomerStatementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public $dealer, public $recipientOverride = null) {}

    public function build()
    {
        $statement = app(StatementReportService::class)->buildForDealer($this->dealer);
        $items = $statement['items']->all();
        $currency = $statement['currency'] ?? 'TL';

        $totals = [
            'debit' => $statement['debtTotal'],
            'credit' => $statement['receivableTotal'],
            'balance' => $statement['balance'],
            'currency' => $currency,
        ];

        $pdf = Pdf::loadView('pdf.customer-statement', [
            'dealer' => $this->dealer,
            'items' => $items,
            'totals' => $totals,
        ])->output();

        $mailBody = 'Müşteri ekstresi ektedir.<br><br>Güzel günler dileriz,<br><strong>' . general_info('company_official_name', config('app.name')) . '</strong>';

        return $this->to($this->getMailRecipients())
            ->subject(config('app.name') . ' | Müşteri Ekstresi')
            ->attachData($pdf, 'musteri-ekstresi-'.$this->dealer->id.'.pdf', [
                'mime' => 'application/pdf',
            ])
            ->html($mailBody);
    }

    private function getMailRecipients()
    {
        if ($this->recipientOverride) {
            $emails = trim($this->recipientOverride);
        } else {
            $emails = trim((string) ($this->dealer->email ?? ''));
        }

        $mailTo = array_filter(
            explode(',', $emails),
            fn($email) => filter_var(trim($email), FILTER_VALIDATE_EMAIL)
        );

        Log::info('CustomerStatementMail dealer code: ' . $this->dealer->code . ' giden mail adresleri: ' . implode(', ', $mailTo));

        return $mailTo;
    }
}
