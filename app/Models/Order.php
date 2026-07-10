<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
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
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (blank($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
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
}
