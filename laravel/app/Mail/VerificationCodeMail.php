<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public ?User $user = null;

    public function __construct(
        string|User $recipient,
        public string $code
    ) {
        if ($recipient instanceof User) {
            $this->user = $recipient;
            $this->name = $recipient->name;
        } else {
            $this->name = $recipient;
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Purita's Beauty Lounge Verification Code",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verification-code',
            with: [
                'name' => $this->name,
                'code' => $this->code,
                'user' => $this->user,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
