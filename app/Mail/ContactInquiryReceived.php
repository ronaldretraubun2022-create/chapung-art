<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactInquiryReceived extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param array{name: string, email: string, department: string, subject: string, message: string} $payload
     */
    public function __construct(
        public readonly array $payload,
        public readonly string $departmentLabel,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->payload['email'], $this->payload['name'])],
            subject: '[Chapung Art] '.$this->departmentLabel.' - '.$this->payload['subject'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.contact-inquiry-received',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
