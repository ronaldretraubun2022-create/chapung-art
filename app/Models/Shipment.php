<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'courier',
        'tracking_number',
        'origin',
        'destination',
        'shipping_cost',
        'status',
        'shipped_at',
        'delivered_at',
        'notes',
    ];

    protected $casts = [
        'shipping_cost' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (Shipment $shipment): void {
            if ($shipment->status === 'shipped' && $shipment->order) {
                $order = $shipment->order;
                $previousStatus = $order->status;
                $order->forceFill(['status' => 'shipped'])->saveQuietly();

                if ($previousStatus !== 'shipped') {
                    $order->recordStatusHistory(
                        'Order dikirim melalui shipment #'.$shipment->id.'.',
                        'shipment',
                        statusFrom: $previousStatus,
                    );
                }
            }

            if ($shipment->status === 'delivered' && $shipment->order) {
                $order = $shipment->order;
                $previousStatus = $order->status;
                $order->forceFill(['status' => 'completed'])->saveQuietly();

                if ($previousStatus !== 'completed') {
                    $order->recordStatusHistory(
                        'Order selesai dari shipment #'.$shipment->id.'.',
                        'shipment',
                        statusFrom: $previousStatus,
                    );
                }
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
