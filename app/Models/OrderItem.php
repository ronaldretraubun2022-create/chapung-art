<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_type',
        'product_id',
        'title',
        'price',
        'quantity',
        'total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (OrderItem $item): void {
            $item->quantity = max(1, (int) ($item->quantity ?: 1));
            $item->price = (float) ($item->price ?? 0);
            $item->total = $item->price * $item->quantity;
        });

        static::saved(fn (OrderItem $item): ?bool => $item->order?->recalculateTotals());
        static::deleted(fn (OrderItem $item): ?bool => $item->order?->recalculateTotals());
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(ArtworkReview::class);
    }
}
