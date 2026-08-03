<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{address: string, name: string}  $from
     */
    public function __construct(
        public readonly string $subjectText,
        public readonly string $bodyText,
        public readonly ?string $trackingUrl,
        array $from,
    ) {
        $this->from($from['address'], $from['name']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectText);
    }

    public function content(): Content
    {
        return new Content(text: 'emails.notification-message');
    }
}
