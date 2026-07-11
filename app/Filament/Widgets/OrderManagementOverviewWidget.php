<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderManagementOverviewWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Pending Orders', number_format(Order::query()->where('status', 'pending')->count()))
                ->description('Order baru menunggu konfirmasi')
                ->color('gray'),

            Stat::make('Paid Orders', number_format(Order::query()->where('payment_status', 'paid')->count()))
                ->description('Pembayaran sudah diverifikasi')
                ->color('success'),

            Stat::make('In Fulfillment', number_format(Order::query()->whereIn('status', ['confirmed', 'processing', 'shipped'])->count()))
                ->description('Order sedang diproses atau dikirim')
                ->color('warning'),

            Stat::make('Paid Revenue', 'Rp '.number_format((float) Order::query()->where('payment_status', 'paid')->sum('grand_total'), 0, ',', '.'))
                ->description('Total nilai order paid')
                ->color('info'),
        ];
    }
}
