<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentNotificationReceived extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly Payment $payment)
    {
    }

    public function envelope(): Envelope
    {
        $orderNumber = $this->payment->order?->order_number ?: '#'.$this->payment->order_id;

        return new Envelope(
            from: new Address((string) config('mail.from.address'), (string) config('mail.from.name')),
            subject: '[Chapung Art Finance] Payment '.$orderNumber,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.payment-notification-received',
        );
    }
}
