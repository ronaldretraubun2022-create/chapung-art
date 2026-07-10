<x-filament-panels::page>
    @php
        $summary = $this->getOverviewSummary();
        $orders = $this->getRecentOrders();
        $statuses = $this->getOrderStatusSummary();
    @endphp

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Start Date</span>
                <input type="date" wire:model.live="startDate" class="w-full rounded-lg border-gray-300 bg-white text-sm text-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
            </label>

            <label class="space-y-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">End Date</span>
                <input type="date" wire:model.live="endDate" class="w-full rounded-lg border-gray-300 bg-white text-sm text-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase text-gray-500">Artwork</p>
                <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_artwork']) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase text-gray-500">Photography</p>
                <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_photography']) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase text-gray-500">Posts</p>
                <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_posts']) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase text-gray-500">Orders</p>
                <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_orders']) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase text-gray-500">Revenue</p>
                <p class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase text-gray-500">Visitors</p>
                <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_visitors']) }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 xl:col-span-2">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Recent Orders</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800">
                            <tr>
                                <th class="py-3 pr-4">Order</th>
                                <th class="py-3 pr-4">Customer</th>
                                <th class="py-3 pr-4">Status</th>
                                <th class="py-3 pr-4 text-right">Grand Total</th>
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
                                <tr><td colspan="4" class="py-6 text-center text-gray-500">No order data for this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Order Status</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($statuses as $status => $total)
                        <div class="flex items-center justify-between rounded-md bg-gray-50 px-3 py-2 dark:bg-gray-800">
                            <span class="text-sm text-gray-700 dark:text-gray-200">{{ ucfirst($status) }}</span>
                            <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ number_format($total) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No status data for this period.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
