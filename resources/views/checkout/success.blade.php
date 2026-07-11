@extends('layouts.public')

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('checkout.success', fallback: [
        'title' => 'Order '.$order->order_number.' | '.site_setting('site_name', 'Chapung Art'),
        'description' => __('chapung.pages.order.success_description'),
        'canonical_url' => route('checkout.success', $order->order_number),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.18),transparent_30rem),#050505] px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-4xl text-center">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Order Created</p>
            <h1 class="mt-4 text-4xl font-black uppercase tracking-tight text-white sm:text-6xl">{{ $order->order_number }}</h1>
            <p class="mt-5 text-sm leading-7 text-zinc-400">{{ __('chapung.pages.order.success_message') }}</p>
        </div>
    </section>

    <section class="px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="mx-auto grid max-w-5xl gap-8 lg:grid-cols-[1fr_320px]">
            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
                <h2 class="text-lg font-black uppercase tracking-tight text-white">{{ __('chapung.pages.order.items') }}</h2>
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

            <aside class="h-fit rounded-lg border border-zinc-800 bg-zinc-950 p-5">
                <h2 class="text-lg font-black uppercase tracking-tight text-white">Summary</h2>
                <div class="mt-5 space-y-3 border-y border-zinc-800 py-5 text-sm">
                    <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.order.status') }}</span><strong class="text-white">{{ ucfirst($order->status) }}</strong></div>
                    <div class="flex justify-between gap-4 text-zinc-400"><span>Payment</span><strong class="text-white">{{ ucfirst($order->payment_status) }}</strong></div>
                    <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.subtotal') }}</span><strong class="text-white">Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</strong></div>
                    <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.discount') }}</span><strong class="text-white">- Rp {{ number_format((float) $order->discount_total, 0, ',', '.') }}</strong></div>
                    <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.shipping_estimate') }}</span><strong class="text-white">Rp {{ number_format((float) $order->shipping_total, 0, ',', '.') }}</strong></div>
                    <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.total') }}</span><strong class="text-yellow-500">Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</strong></div>
                </div>
                @if ($order->notes)
                    <div class="mt-5 rounded-md border border-zinc-800 bg-black/40 p-4 text-xs leading-6 text-zinc-400 whitespace-pre-line">{{ $order->notes }}</div>
                @endif
                <a href="{{ route('gallery') }}" class="mt-5 inline-flex w-full justify-center rounded-md bg-yellow-600 px-5 py-4 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">Back to Gallery</a>
                @auth
                    <a href="{{ route('orders.show', $order) }}" class="mt-3 inline-flex w-full justify-center rounded-md border border-zinc-700 px-5 py-4 text-xs font-black uppercase tracking-[0.18em] text-zinc-200 hover:border-yellow-600 hover:text-yellow-500">{{ __('chapung.orders.detail') }}</a>
                    <a href="{{ route('invoice.show', $order) }}" class="mt-3 inline-flex w-full justify-center rounded-md border border-yellow-600/60 px-5 py-4 text-xs font-black uppercase tracking-[0.18em] text-yellow-500 hover:bg-yellow-600 hover:text-black">{{ __('chapung.pages.order.view_invoice') }}</a>
                @endauth

                <div class="mt-5">
                    @include('partials.public.payment-information')
                </div>
            </aside>
        </div>
    </section>
@endsection
