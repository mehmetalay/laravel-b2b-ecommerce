<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use App\Models\DealerApplication;
use Illuminate\Queue\SerializesModels;

class DealerApplicationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $dealer_application;
    public $documents;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(DealerApplication $dealer_application)
    {
        $this->dealer_application = $dealer_application;
        $this->documents = $dealer_application->documents;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->to($this->getMailRecipients())
            ->subject(config('app.name') . ' | Yeni Bayi Talebi')
            ->view('emails.dealer-application')
            ->with([
                'dealer_application' => $this->dealer_application
            ]);

        foreach ($this->documents as $document) {
            $path = storage_path('app/' . $document->path);
            $email->attach($path, [
                'as' => basename($path),
                'mime' => mime_content_type($path),
            ]);
        }

        return $email;
    }

    private function getMailRecipients()
    {
        $emails = trim(str_replace(' ', '', additional_setting('dealer_application_mails')));

        $mailTo = array_filter(explode(',', $emails), function($email) {
            return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        });

        Log::info('DealerApplicationMail dealerApplicationId: ' . $this->dealer_application->id . ' giden mail adresleri: ' . implode(', ', $mailTo));

        return $mailTo;
    }
}
