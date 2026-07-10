<?php

namespace App\Services;

use App\Mail\AdminNotificationCreated;
use App\Mail\PaymentNotificationReceived;
use App\Models\AdminNotification;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class InternalNotificationMailService
{
    public function __construct(private readonly MailboxService $mailboxes)
    {
    }

    public function notifyAdmins(AdminNotification $notification): void
    {
        $recipients = $this->mailboxes->adminEmails();

        if ($recipients === []) {
            return;
        }

        $this->sendSafely(
            fn (): mixed => Mail::to($recipients)->send(new AdminNotificationCreated($notification)),
            'admin_notification',
            ['notification_id' => $notification->id, 'type' => $notification->type],
        );
    }

    public function notifyFinance(Payment $payment): void
    {
        $recipient = $this->mailboxes->addressFor('finance');

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $payment->loadMissing('order');

        $this->sendSafely(
            fn (): mixed => Mail::to($recipient)->send(new PaymentNotificationReceived($payment)),
            'payment_notification',
            ['payment_id' => $payment->id, 'order_id' => $payment->order_id],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function sendSafely(callable $send, string $event, array $context): void
    {
        try {
            $send();
        } catch (Throwable $exception) {
            Log::warning('Chapung Art internal mail failed.', [
                'event' => $event,
                ...$context,
                'exception' => $exception::class,
            ]);
        }
    }
}
