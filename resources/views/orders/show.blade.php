@extends('layouts.public')

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('orders.show', fallback: [
        'title' => $order->order_number.' | '.site_setting('site_name', 'Chapung Art'),
        'description' => __('chapung.orders.detail_description'),
        'canonical_url' => route('orders.show', $order),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-zinc-950 px-4 py-14 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[1fr_auto] lg:items-end">
            <div>
                <a href="{{ route('orders.index') }}" class="text-xs font-black uppercase tracking-[0.18em] text-yellow-500 hover:text-yellow-400">{{ __('chapung.orders.back_to_orders') }}</a>
                <h1 class="mt-4 text-4xl font-black uppercase tracking-tight text-white sm:text-6xl">{{ $order->order_number }}</h1>
                <p class="mt-4 text-sm leading-7 text-zinc-400">{{ __('chapung.orders.detail_description') }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('invoice.show', $order) }}" class="inline-flex items-center justify-center rounded-md bg-yellow-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">{{ __('chapung.pages.order.view_invoice') }}</a>
                <a href="{{ route('invoice.download', $order) }}" class="inline-flex items-center justify-center rounded-md border border-yellow-600/60 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-yellow-500 hover:bg-yellow-600 hover:text-black">{{ __('chapung.pages.invoice.download_pdf') }}</a>
            </div>
        </div>
    </section>

    <section class="px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[1fr_380px]">
            <div class="space-y-8">
                <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.orders.status_timeline') }}</p>
                            <h2 class="mt-2 text-xl font-black uppercase tracking-tight text-white">{{ $order->statusLabel() }}</h2>
                        </div>
                        <span class="rounded-full bg-yellow-600/10 px-4 py-2 text-xs font-black uppercase tracking-[0.14em] text-yellow-500">{{ $order->paymentStatusLabel() }}</span>
                    </div>
                    <div class="mt-5 h-2 overflow-hidden rounded-full bg-zinc-800" aria-label="{{ __('chapung.orders.progress') }}">
                        <div class="h-full rounded-full bg-yellow-600" style="width: {{ $order->progressPercentage() }}%"></div>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($order->statusHistories as $history)
                            <article class="rounded-md border border-zinc-800 bg-black/40 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="font-black text-white">{{ $history->status_to ? str($history->status_to)->headline() : $order->statusLabel() }} / {{ $history->payment_status_to ? str($history->payment_status_to)->headline() : $order->paymentStatusLabel() }}</p>
                                        <p class="mt-1 text-xs font-bold uppercase tracking-[0.12em] text-zinc-500">{{ $history->source }} / {{ $history->created_at?->format('d M Y H:i') }}</p>
                                    </div>
                                    @if ($history->changedBy)
                                        <span class="text-xs font-bold text-zinc-500">{{ $history->changedBy->name }}</span>
                                    @endif
                                </div>
                                @if ($history->note)
                                    <p class="mt-3 text-sm leading-6 text-zinc-400">{{ $history->note }}</p>
                                @endif
                            </article>
                        @empty
                            <p class="rounded-md border border-zinc-800 bg-black/40 p-4 text-sm text-zinc-400">{{ __('chapung.orders.history_empty') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5 sm:p-6">
                    <h2 class="text-xl font-black uppercase tracking-tight text-white">{{ __('chapung.pages.order.items') }}</h2>
                    <div class="mt-5 divide-y divide-zinc-800">
                        @foreach ($order->items as $item)
                            <div class="flex justify-between gap-4 py-4 text-sm">
                                <div>
                                    <p class="font-black uppercase tracking-tight text-white">{{ $item->title }}</p>
                                    <p class="mt-1 text-zinc-500">Qty {{ $item->quantity }} x Rp {{ number_format((float) $item->price, 0, ',', '.') }}</p>
                                </div>
                                <strong class="shrink-0 text-yellow-500">Rp {{ number_format((float) $item->total, 0, ',', '.') }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
                    <h2 class="text-lg font-black uppercase tracking-tight text-white">{{ __('chapung.pages.cart.summary') }}</h2>
                    <div class="mt-5 space-y-3 border-y border-zinc-800 py-5 text-sm">
                        <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.order.status') }}</span><strong class="text-white">{{ $order->statusLabel() }}</strong></div>
                        <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.orders.payment') }}</span><strong class="text-white">{{ $order->paymentStatusLabel() }}</strong></div>
                        <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.subtotal') }}</span><strong class="text-white">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</strong></div>
                        <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.discount') }}</span><strong class="text-white">- Rp {{ number_format((float) $order->discount_total, 0, ',', '.') }}</strong></div>
                        <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.shipping_estimate') }}</span><strong class="text-white">Rp {{ number_format((float) $order->shipping_total, 0, ',', '.') }}</strong></div>
                        <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.total') }}</span><strong class="text-yellow-500">Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</strong></div>
                    </div>
                    @if ($order->notes)
                        <div class="mt-5 rounded-md border border-zinc-800 bg-black/40 p-4 text-xs leading-6 text-zinc-400 whitespace-pre-line">{{ $order->notes }}</div>
                    @endif
                </div>

                <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
                    <h2 class="text-lg font-black uppercase tracking-tight text-white">{{ __('chapung.orders.payments') }}</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($order->payments as $payment)
                            <div class="rounded-md border border-zinc-800 bg-black/40 p-4 text-sm text-zinc-400">
                                <div class="flex justify-between gap-3"><span>{{ str($payment->payment_method)->headline() }}</span><strong class="text-yellow-500">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</strong></div>
                                <p class="mt-2 text-xs font-black uppercase tracking-[0.12em] text-zinc-500">{{ str($payment->status)->headline() }} / {{ optional($payment->paid_at)->format('d M Y H:i') ?: '-' }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500">{{ __('chapung.orders.payments_empty') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
                    <h2 class="text-lg font-black uppercase tracking-tight text-white">{{ __('chapung.orders.shipments') }}</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($order->shipments as $shipment)
                            <div class="rounded-md border border-zinc-800 bg-black/40 p-4 text-sm text-zinc-400">
                                <div class="flex justify-between gap-3"><span>{{ $shipment->courier ?: __('chapung.orders.courier_pending') }}</span><strong class="text-white">{{ str($shipment->status)->headline() }}</strong></div>
                                <p class="mt-2 text-xs text-zinc-500">{{ $shipment->tracking_number ?: __('chapung.orders.tracking_pending') }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500">{{ __('chapung.orders.shipments_empty') }}</p>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
    </section>
@endsection
