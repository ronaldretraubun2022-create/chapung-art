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
            Stat::make(__('admin.stats.pending_orders'), number_format(Order::query()->where('status', 'pending')->count()))
                ->description(__('admin.stats.pending_orders_description'))
                ->color('gray'),

            Stat::make(__('admin.stats.paid_orders'), number_format(Order::query()->where('payment_status', 'paid')->count()))
                ->description(__('admin.stats.paid_orders_description'))
                ->color('success'),

            Stat::make(__('admin.stats.in_fulfillment'), number_format(Order::query()->whereIn('status', ['confirmed', 'processing', 'shipped'])->count()))
                ->description(__('admin.stats.in_fulfillment_description'))
                ->color('warning'),

            Stat::make(__('admin.stats.paid_revenue'), 'Rp '.number_format((float) Order::query()->where('payment_status', 'paid')->sum('grand_total'), 0, ',', '.'))
                ->description(__('admin.stats.paid_revenue_description'))
                ->color('info'),
        ];
    }
}
