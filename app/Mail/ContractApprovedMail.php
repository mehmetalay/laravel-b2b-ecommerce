<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Queue\SerializesModels;

class ContractApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $pdf = Pdf::loadView('pdf.contract', ['user' => $this->user]);

        return $this->subject(config('app.name') . ' | Sözleşme Onaylandı')
                ->view('emails.contract_approved')
                ->with([
                    'user' => $this->user,
                ])
                ->attachData($pdf->output(), 'sozlesme.pdf', [
                    'mime' => 'application/pdf',
                ]);
    }
}
