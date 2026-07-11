<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Order extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid' => 'Unpaid',
        'paid' => 'Paid',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
    ];

    protected $fillable = [
        'order_number',
        'invoice_number',
        'invoiced_at',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'subtotal',
        'discount_total',
        'shipping_total',
        'grand_total',
        'status',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'invoiced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (blank($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }

            if (blank($order->invoice_number)) {
                $order->invoice_number = static::generateInvoiceNumber();
                $order->invoiced_at ??= now();
            }
        });

        static::saving(function (Order $order): void {
            $order->discount_total = (float) ($order->discount_total ?? 0);
            $order->shipping_total = (float) ($order->shipping_total ?? 0);
            $order->subtotal = (float) ($order->subtotal ?? 0);
            $order->grand_total = max(0, $order->subtotal - $order->discount_total + $order->shipping_total);
        });

        static::created(function (Order $order): void {
            AdminNotification::create([
                'title' => 'Order baru',
                'message' => "Order {$order->order_number} dibuat oleh {$order->customer_name}.",
                'type' => 'order',
                'url' => url('/admin/orders/'.$order->id.'/edit'),
            ]);

            $order->recordStatusHistory('Order dibuat.', 'checkout');
        });

        static::updated(function (Order $order): void {
            if (! $order->wasChanged(['status', 'payment_status'])) {
                return;
            }

            $order->recordStatusHistory('Status order diperbarui.', 'admin');
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items()->sum('total');

        $this->forceFill([
            'subtotal' => $subtotal,
            'grand_total' => max(0, $subtotal - (float) $this->discount_total + (float) $this->shipping_total),
        ])->saveQuietly();
    }

    public static function generateOrderNumber(): string
    {
        do {
            $number = 'CA-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    public function ensureInvoiceNumber(): void
    {
        if (filled($this->invoice_number)) {
            return;
        }

        $this->forceFill([
            'invoice_number' => static::generateInvoiceNumber(),
            'invoiced_at' => now(),
        ])->save();
    }

    public function canBeViewedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $this->loadMissing('customer');

        return $this->customer?->user_id === $user->id
            || strcasecmp((string) $this->customer_email, (string) $user->email) === 0;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? str($this->status)->headline()->toString();
    }

    public function paymentStatusLabel(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? str($this->payment_status)->headline()->toString();
    }

    public function progressPercentage(): int
    {
        return match ($this->status) {
            'confirmed' => 35,
            'processing' => 55,
            'shipped' => 80,
            'completed' => 100,
            'cancelled' => 100,
            default => 15,
        };
    }

    public function recordStatusHistory(
        ?string $note = null,
        string $source = 'system',
        ?User $changedBy = null,
        ?string $statusFrom = null,
        ?string $paymentStatusFrom = null,
    ): ?OrderStatusHistory
    {
        if (! Schema::hasTable('order_status_histories')) {
            return null;
        }

        $statusFrom ??= $this->wasRecentlyCreated ? null : $this->getOriginal('status');
        $paymentStatusFrom ??= $this->wasRecentlyCreated ? null : $this->getOriginal('payment_status');

        return $this->statusHistories()->create([
            'changed_by' => $changedBy?->id ?: auth()->id(),
            'status_from' => $statusFrom,
            'status_to' => $this->status,
            'payment_status_from' => $paymentStatusFrom,
            'payment_status_to' => $this->payment_status,
            'source' => $source,
            'note' => $note,
        ]);
    }

    public static function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('invoice_number', $number)->exists());

        return $number;
    }
}
