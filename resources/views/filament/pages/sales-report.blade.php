<x-filament-panels::page>
    @php
        $summary = $this->getOverviewSummary();
        $orders = $this->getRecentOrders();
        $statuses = $this->getOrderStatusSummary();
    @endphp

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('admin.reports.start_date') }}</span>
                <input type="date" wire:model.live="startDate" class="w-full rounded-lg border-gray-300 bg-white text-sm text-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
            </label>

            <label class="space-y-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('admin.reports.end_date') }}</span>
                <input type="date" wire:model.live="endDate" class="w-full rounded-lg border-gray-300 bg-white text-sm text-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase text-gray-500">{{ __('admin.reports.artwork') }}</p>
                <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_artwork']) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase text-gray-500">{{ __('admin.reports.photography') }}</p>
                <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_photography']) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase text-gray-500">{{ __('admin.reports.posts') }}</p>
                <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_posts']) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase text-gray-500">{{ __('admin.reports.orders') }}</p>
                <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_orders']) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase text-gray-500">{{ __('admin.reports.revenue') }}</p>
                <p class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase text-gray-500">{{ __('admin.reports.visitors') }}</p>
                <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_visitors']) }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 xl:col-span-2">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ __('admin.reports.recent_orders') }}</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800">
                            <tr>
                                <th class="py-3 pr-4">{{ __('admin.reports.order') }}</th>
                                <th class="py-3 pr-4">{{ __('admin.reports.customer') }}</th>
                                <th class="py-3 pr-4">{{ __('admin.reports.status') }}</th>
                                <th class="py-3 pr-4 text-right">{{ __('admin.reports.grand_total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($orders as $order)
                                <tr>
                                    <td class="py-3 pr-4 font-medium text-gray-950 dark:text-white">{{ $order->order_number }}</td>
                                    <td class="py-3 pr-4 text-gray-600 dark:text-gray-300">{{ $order->customer_name }}</td>
                                    <td class="py-3 pr-4 text-gray-600 dark:text-gray-300">{{ ucfirst($order->status) }}</td>
                                    <td class="py-3 pr-4 text-right text-gray-950 dark:text-white">Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-6 text-center text-gray-500">{{ __('admin.reports.empty_orders') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ __('admin.reports.order_status') }}</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($statuses as $status => $total)
                        <div class="flex items-center justify-between rounded-md bg-gray-50 px-3 py-2 dark:bg-gray-800">
                            <span class="text-sm text-gray-700 dark:text-gray-200">{{ ucfirst($status) }}</span>
                            <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ number_format($total) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">{{ __('admin.reports.empty_status') }}</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
