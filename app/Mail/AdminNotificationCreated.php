<?php

namespace App\Mail;

use App\Models\AdminNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNotificationCreated extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly AdminNotification $notification)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address((string) config('mail.from.address'), (string) config('mail.from.name')),
            subject: '[Chapung Art Admin] '.$this->notification->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.admin-notification-created',
        );
    }
}
