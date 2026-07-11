@extends('layouts.public')

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('orders.index', fallback: [
        'title' => __('chapung.orders.title').' | '.site_setting('site_name', 'Chapung Art'),
        'description' => __('chapung.orders.description'),
        'canonical_url' => route('orders.index'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-zinc-950 px-4 py-14 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('chapung.orders.eyebrow') }}</p>
            <h1 class="mt-4 text-4xl font-black uppercase tracking-tight text-white sm:text-6xl">{{ __('chapung.orders.title') }}</h1>
            <p class="mt-5 max-w-3xl text-sm leading-7 text-zinc-400">{{ __('chapung.orders.description') }}</p>
        </div>
    </section>

    <section class="px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="mx-auto max-w-7xl rounded-lg border border-zinc-800 bg-zinc-950 p-5 sm:p-6">
            <div class="divide-y divide-zinc-800">
                @forelse ($orders as $order)
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
                            <a href="{{ route('invoice.show', $order) }}" class="inline-flex items-center justify-center rounded-md bg-yellow-600 px-4 py-2 text-xs font-black uppercase tracking-[0.14em] text-black hover:bg-yellow-500">{{ __('chapung.pages.order.view_invoice') }}</a>
                        </div>
                    </article>
                @empty
                    @include('partials.public.empty-state', [
                        'label' => __('chapung.orders.eyebrow'),
                        'title' => __('chapung.orders.empty_title'),
                        'description' => __('chapung.orders.empty_description'),
                    ])
                @endforelse
            </div>

            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        </div>
    </section>
@endsection
