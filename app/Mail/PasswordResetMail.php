<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user, $resetLink;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $resetLink)
    {
        $this->user = $user;
        $this->resetLink = $resetLink;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(config('app.name') . ' | Şifre Değiştirme Talebi')
            ->view('emails.password-reset')
            ->with([
                'user' => $this->user,
                'resetLink' => $this->resetLink,
            ]);
    }
}
