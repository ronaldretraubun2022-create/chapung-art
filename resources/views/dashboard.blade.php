<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.orders.dashboard_label') }}</p>
                <h2 class="mt-2 text-2xl font-black uppercase tracking-tight text-gray-950 dark:text-white">{{ __('chapung.common.dashboard') }}</h2>
            </div>
            <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center rounded-md border border-yellow-600/60 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-yellow-700 hover:bg-yellow-600 hover:text-black dark:text-yellow-500">
                {{ __('chapung.orders.view_all') }}
            </a>
        </div>
    </x-slot>

    <div class="bg-zinc-950 py-10 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-zinc-800 bg-black/40 p-5">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-zinc-500">{{ __('chapung.orders.total_orders') }}</p>
                    <p class="mt-3 text-3xl font-black text-white">{{ number_format((int) $summary['total']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-800 bg-black/40 p-5">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-zinc-500">{{ __('chapung.orders.active_orders') }}</p>
                    <p class="mt-3 text-3xl font-black text-white">{{ number_format((int) $summary['active']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-800 bg-black/40 p-5">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-zinc-500">{{ __('chapung.orders.paid_orders') }}</p>
                    <p class="mt-3 text-3xl font-black text-white">{{ number_format((int) $summary['paid']) }}</p>
                </div>
                <div class="rounded-lg border border-zinc-800 bg-black/40 p-5">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-zinc-500">{{ __('chapung.orders.total_spend') }}</p>
                    <p class="mt-3 text-2xl font-black text-yellow-500">Rp {{ number_format((float) $summary['grand_total'], 0, ',', '.') }}</p>
                </div>
            </div>

            <section class="mt-8 rounded-lg border border-zinc-800 bg-zinc-950 p-5 sm:p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.orders.recent_label') }}</p>
                        <h3 class="mt-2 text-xl font-black uppercase tracking-tight text-white">{{ __('chapung.orders.recent_orders') }}</h3>
                    </div>
                    <a href="{{ route('gallery') }}" class="text-xs font-black uppercase tracking-[0.18em] text-yellow-500 hover:text-yellow-400">{{ __('chapung.pages.cart.continue_shopping') }}</a>
                </div>

                <div class="mt-6 divide-y divide-zinc-800">
                    @forelse ($recentOrders as $order)
                        <article class="grid gap-4 py-5 md:grid-cols-[1fr_auto] md:items-center">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('orders.show', $order) }}" class="font-black uppercase tracking-tight text-white hover:text-yellow-500">{{ $order->order_number }}</a>
                                    <span class="rounded-full border border-zinc-700 px-3 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-zinc-400">{{ $order->statusLabel() }}</span>
                                    <span class="rounded-full bg-yellow-600/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-yellow-500">{{ $order->paymentStatusLabel() }}</span>
                                </div>
                                <p class="mt-2 text-sm text-zinc-500">{{ $order->items_count }} {{ __('chapung.pages.cart.items') }} / {{ $order->created_at?->format('d M Y H:i') }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 md:justify-end">
                                <strong class="text-yellow-500">Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</strong>
                                <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center justify-center rounded-md border border-zinc-700 px-4 py-2 text-xs font-black uppercase tracking-[0.14em] text-zinc-200 hover:border-yellow-600 hover:text-yellow-500">{{ __('chapung.orders.detail') }}</a>
                            </div>
                        </article>
                    @empty
                        @include('partials.public.empty-state', [
                            'label' => __('chapung.orders.recent_label'),
                            'title' => __('chapung.orders.empty_title'),
                            'description' => __('chapung.orders.empty_description'),
                        ])
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
