<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $gymName,
        public readonly string $registrationUrl,
        public readonly string $city = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Join {$this->gymName} â€” Complete Your Registration",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-link',
        );
    }
}

