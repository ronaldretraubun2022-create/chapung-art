<?php

namespace App\Models;

use App\Models\Concerns\ManagesImageUploads;
use App\Services\InternalNotificationMailService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use ManagesImageUploads;

    protected array $imageUploads = [
        'proof_image' => 'local',
    ];

    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'status',
        'paid_at',
        'proof_image',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (Payment $payment): void {
            if ($payment->status === 'paid' && $payment->order) {
                $payment->order->forceFill(['payment_status' => 'paid'])->saveQuietly();
            }

            if ($payment->wasRecentlyCreated) {
                AdminNotification::create([
                    'title' => 'Payment baru',
                    'message' => 'Payment '.number_format((float) $payment->amount, 0, ',', '.').' dibuat untuk order '.($payment->order?->order_number ?: '#'.$payment->order_id).'.',
                    'type' => 'payment',
                    'url' => url('/admin/payments/'.$payment->id.'/edit'),
                ]);

                app(InternalNotificationMailService::class)->notifyFinance($payment);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
