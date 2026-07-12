<x-filament-panels::page>
    @php
        $summary = $this->getOverviewSummary();
        $contents = $this->getLatestContent();
        $statuses = $this->getStatusSummary();
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
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"><p class="text-xs font-medium uppercase text-gray-500">{{ __('admin.reports.artwork') }}</p><p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_artwork']) }}</p></div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"><p class="text-xs font-medium uppercase text-gray-500">{{ __('admin.reports.photography') }}</p><p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_photography']) }}</p></div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"><p class="text-xs font-medium uppercase text-gray-500">{{ __('admin.reports.posts') }}</p><p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_posts']) }}</p></div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"><p class="text-xs font-medium uppercase text-gray-500">{{ __('admin.reports.orders') }}</p><p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_orders']) }}</p></div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"><p class="text-xs font-medium uppercase text-gray-500">{{ __('admin.reports.revenue') }}</p><p class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</p></div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"><p class="text-xs font-medium uppercase text-gray-500">{{ __('admin.reports.visitors') }}</p><p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_visitors']) }}</p></div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 xl:col-span-2">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ __('admin.reports.latest_content') }}</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800">
                            <tr>
                                <th class="py-3 pr-4">{{ __('admin.reports.type') }}</th>
                                <th class="py-3 pr-4">{{ __('admin.reports.title') }}</th>
                                <th class="py-3 pr-4">{{ __('admin.reports.status') }}</th>
                                <th class="py-3 pr-4 text-right">{{ __('admin.reports.views') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($contents as $content)
                                <tr>
                                    <td class="py-3 pr-4 font-medium text-gray-950 dark:text-white">{{ $content['type'] }}</td>
                                    <td class="py-3 pr-4 text-gray-600 dark:text-gray-300">{{ $content['title'] }}</td>
                                    <td class="py-3 pr-4 text-gray-600 dark:text-gray-300">{{ ucfirst($content['status']) }}</td>
                                    <td class="py-3 pr-4 text-right text-gray-950 dark:text-white">{{ number_format($content['views']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-6 text-center text-gray-500">{{ __('admin.reports.empty_content') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ __('admin.reports.status_summary') }}</h2>
                <div class="mt-4 space-y-4">
                    @foreach ($statuses as $type => $items)
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $type }}</p>
                            <div class="mt-2 space-y-2">
                                @forelse ($items as $status => $total)
                                    <div class="flex items-center justify-between rounded-md bg-gray-50 px-3 py-2 dark:bg-gray-800">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">{{ ucfirst($status ?: __('admin.reports.unknown')) }}</span>
                                        <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ number_format($total) }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">{{ __('admin.reports.empty_data') }}</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
