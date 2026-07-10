<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\InteractsWithReportFilters;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class SalesReport extends Page
{
    use InteractsWithReportFilters;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Sales Report';

    protected static ?string $title = 'Sales Report';

    protected static ?string $slug = 'reports/sales';

    protected string $view = 'filament.pages.sales-report';

    public function mount(): void
    {
        $this->initializeReportFilters();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_any report') ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action('exportCsv'),
        ];
    }

    /**
     * @return Collection<int, Order>
     */
    public function getRecentOrders(): Collection
    {
        return $this->applyDateFilter(Order::query())
            ->select(['id', 'order_number', 'customer_name', 'status', 'payment_status', 'grand_total', 'created_at'])
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * @return array<string, int>
     */
    public function getOrderStatusSummary(): array
    {
        return $this->applyDateFilter(Order::query())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total): int => (int) $total)
            ->all();
    }

    public function exportCsv(): StreamedResponse
    {
        $rows = $this->applyDateFilter(Order::query())
            ->select(['order_number', 'customer_name', 'status', 'payment_status', 'subtotal', 'discount_total', 'shipping_total', 'grand_total', 'created_at'])
            ->latest()
            ->get()
            ->map(fn (Order $order): array => [
                $order->order_number,
                $order->customer_name,
                $order->status,
                $order->payment_status,
                (float) $order->subtotal,
                (float) $order->discount_total,
                (float) $order->shipping_total,
                (float) $order->grand_total,
                optional($order->created_at)->format('Y-m-d H:i:s'),
            ])
            ->all();

        return $this->downloadCsv($this->reportFilename('sales-report'), [
            'Order Number',
            'Customer',
            'Status',
            'Payment Status',
            'Subtotal',
            'Discount',
            'Shipping',
            'Grand Total',
            'Created At',
        ], $rows);
    }
}
