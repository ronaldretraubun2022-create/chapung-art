@extends('layouts.public')

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('cart.index', fallback: [
        'title' => __('chapung.pages.cart.title').' | '.site_setting('site_name', 'Chapung Art'),
        'description' => __('chapung.pages.cart.description'),
        'canonical_url' => route('cart.index'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.16),transparent_30rem),#050505] px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <div class="mx-auto flex max-w-7xl flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('chapung.pages.cart.marketplace_label') }}</p>
                <h1 class="mt-4 text-4xl font-black uppercase tracking-tight text-white sm:text-6xl">{{ __('chapung.pages.cart.title') }}</h1>
                <p class="mt-5 max-w-2xl text-sm leading-7 text-zinc-400">{{ __('chapung.pages.cart.intro') }}</p>
            </div>
            <a href="{{ route('artworks.index') }}" class="inline-flex w-fit items-center gap-2 rounded-md border border-zinc-800 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-zinc-200 hover:border-yellow-600 hover:text-yellow-500">
                <x-heroicon-o-arrow-left class="h-4 w-4" aria-hidden="true" />
                {{ __('chapung.pages.cart.continue_shopping') }}
            </a>
        </div>
    </section>

    <section class="px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[minmax(0,1fr)_390px]">
            <div class="space-y-4">
                @forelse ($cart['items'] as $item)
                    <article class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 shadow-xl shadow-black/20">
                        <div class="grid gap-4 p-4 sm:grid-cols-[150px_1fr] lg:p-5">
                            <a href="{{ route('artwork.show', $item['slug']) }}" class="group block">
                                @include('partials.public.image', ['path' => $item['thumbnail'], 'alt' => $item['title'], 'ratio' => 'aspect-[4/5] sm:aspect-square', 'label' => __('chapung.types.artwork'), 'width' => 360, 'height' => 360])
                            </a>

                            <div class="grid gap-5 xl:grid-cols-[1fr_270px]">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-yellow-600">{{ __('chapung.pages.cart.item_detail') }}</p>
                                    <a href="{{ route('artwork.show', $item['slug']) }}" class="mt-2 block text-xl font-black uppercase tracking-tight text-white hover:text-yellow-500">{{ $item['title'] }}</a>
                                    <p class="mt-2 text-sm text-zinc-400">{{ $item['artist_name'] ?: __('chapung.home.artist_fallback') }}</p>

                                    <div class="mt-5 grid gap-3 text-sm sm:grid-cols-3">
                                        <div class="rounded-md border border-zinc-800 bg-black/40 p-3">
                                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-zinc-500">{{ __('chapung.pages.cart.price') }}</p>
                                            <p class="mt-1 font-black text-yellow-500">Rp {{ number_format((float) $item['price'], 0, ',', '.') }}</p>
                                        </div>
                                        <div class="rounded-md border border-zinc-800 bg-black/40 p-3">
                                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-zinc-500">{{ __('chapung.pages.cart.stock') }}</p>
                                            <p class="mt-1 font-black text-white">{{ number_format((int) $item['stock']) }}</p>
                                        </div>
                                        <div class="rounded-md border border-zinc-800 bg-black/40 p-3">
                                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-zinc-500">{{ __('chapung.pages.cart.line_total') }}</p>
                                            <p class="mt-1 font-black text-white">Rp {{ number_format((float) $item['line_total'], 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col justify-between gap-4 rounded-lg border border-zinc-800 bg-black/35 p-4">
                                    <form method="POST" action="{{ route('cart.update', $item['artwork_id']) }}" class="space-y-3">
                                        @csrf
                                        @method('PATCH')
                                        <label for="quantity-{{ $item['artwork_id'] }}" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.pages.cart.quantity') }}</label>
                                        <div class="grid grid-cols-[1fr_auto] gap-2">
                                            <input id="quantity-{{ $item['artwork_id'] }}" name="quantity" type="number" min="1" max="{{ $item['stock'] }}" value="{{ $item['quantity'] }}" class="h-12 rounded-md border border-zinc-800 bg-black px-3 text-sm font-bold text-white focus:border-yellow-600 focus:ring-yellow-600">
                                            <button type="submit" class="inline-flex h-12 items-center justify-center gap-1.5 rounded-md border border-yellow-600/60 px-4 text-xs font-black uppercase tracking-[0.14em] text-yellow-500 hover:bg-yellow-600 hover:text-black">
                                                <x-heroicon-o-arrow-path class="h-4 w-4" aria-hidden="true" />
                                                {{ __('chapung.pages.cart.update') }}
                                            </button>
                                        </div>
                                        @error('quantity') <p class="text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                                    </form>

                                    <form method="POST" action="{{ route('cart.destroy', $item['artwork_id']) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-zinc-800 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-zinc-500 hover:border-red-500 hover:text-red-400">
                                            <x-heroicon-o-trash class="h-4 w-4" aria-hidden="true" />
                                            {{ __('chapung.pages.cart.remove_item') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-8 text-center">
                        <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-600">{{ __('chapung.pages.cart.empty_label') }}</p>
                        <h2 class="mt-4 text-2xl font-black uppercase tracking-tight text-white">{{ __('chapung.pages.cart.empty_title') }}</h2>
                        <p class="mt-3 text-sm leading-7 text-zinc-400">{{ __('chapung.pages.cart.empty_description') }}</p>
                        <a href="{{ route('artworks.index') }}" class="mt-6 inline-flex rounded-md bg-yellow-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">{{ __('chapung.pages.cart.browse') }}</a>
                    </div>
                @endforelse
            </div>

            <aside class="h-fit space-y-4 lg:sticky lg:top-28">
                <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5 shadow-xl shadow-black/20">
                    <h2 class="text-lg font-black uppercase tracking-tight text-white">{{ __('chapung.pages.cart.summary') }}</h2>
                    <div class="mt-5 space-y-3 border-y border-zinc-800 py-5 text-sm">
                        <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.items') }}</span><strong class="text-white">{{ number_format($cart['count']) }}</strong></div>
                        <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.subtotal') }}</span><strong class="text-white">Rp {{ number_format((float) $cart['subtotal'], 0, ',', '.') }}</strong></div>
                        <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.discount') }}</span><strong class="text-white">- Rp {{ number_format((float) $cart['discount_total'], 0, ',', '.') }}</strong></div>
                        <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.shipping_estimate') }}</span><strong class="text-white">Rp {{ number_format((float) $cart['shipping_estimate'], 0, ',', '.') }}</strong></div>
                        <div class="flex justify-between gap-4 border-t border-zinc-800 pt-4 text-base"><span class="font-black text-white">{{ __('chapung.pages.cart.estimated_total') }}</span><strong class="text-xl text-yellow-500">Rp {{ number_format((float) $cart['estimated_total'], 0, ',', '.') }}</strong></div>
                    </div>

                    @if ($cart['coupon_code'])
                        <div class="mt-4 flex items-center justify-between gap-3 rounded-md border border-yellow-600/40 bg-yellow-600/10 px-3 py-2 text-xs text-yellow-500">
                            <span class="font-black uppercase tracking-[0.14em]">{{ $cart['coupon_code'] }} / {{ $cart['coupon_label'] }}</span>
                            <form method="POST" action="{{ route('cart.coupon.remove') }}">
                                @csrf
                                @method('DELETE')
                                <button class="font-black uppercase hover:text-yellow-300">{{ __('chapung.pages.cart.remove') }}</button>
                            </form>
                        </div>
                    @endif

                    @if ($cart['shipping_label'])
                        <div class="mt-3 flex items-center justify-between gap-3 rounded-md border border-zinc-800 bg-black/40 px-3 py-2 text-xs text-zinc-300">
                            <span class="font-bold">{{ $cart['shipping_label'] }}</span>
                            <form method="POST" action="{{ route('cart.shipping.remove') }}">
                                @csrf
                                @method('DELETE')
                                <button class="font-black uppercase text-zinc-500 hover:text-red-400">{{ __('chapung.pages.cart.remove') }}</button>
                            </form>
                        </div>
                    @endif

                    @if ($cart['items'] !== [])
                        <a href="{{ route('checkout.create') }}" class="mt-5 inline-flex w-full justify-center rounded-md bg-yellow-600 px-5 py-4 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">{{ __('chapung.pages.cart.checkout') }}</a>
                    @else
                        <a href="{{ route('artworks.index') }}" class="mt-5 inline-flex w-full justify-center rounded-md bg-yellow-600 px-5 py-4 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">{{ __('chapung.pages.cart.browse') }}</a>
                    @endif
                    <p class="mt-4 text-xs leading-6 text-zinc-500">{{ __('chapung.pages.cart.note') }}</p>
                </div>

                <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
                    <h2 class="text-sm font-black uppercase tracking-[0.18em] text-white">{{ __('chapung.pages.cart.shipping_estimate') }}</h2>
                    <form method="POST" action="{{ route('cart.shipping.estimate') }}" class="mt-4 space-y-3">
                        @csrf
                        <label for="shipping_area" class="sr-only">{{ __('chapung.pages.cart.shipping_area') }}</label>
                        <select id="shipping_area" name="shipping_area" required class="w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                            <option value="">{{ __('chapung.pages.cart.select_shipping_area') }}</option>
                            @foreach ($cart['shipping_options'] as $area => $option)
                                <option value="{{ $area }}" @selected(($cart['shipping_area'] ?? null) === $area)>{{ $option['label'] }} / Rp {{ number_format((float) $option['amount'], 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                        @error('shipping_area') <p class="text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-yellow-600/60 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-yellow-500 hover:bg-yellow-600 hover:text-black">
                            <x-heroicon-o-truck class="h-4 w-4" aria-hidden="true" />
                            {{ __('chapung.pages.cart.estimate_shipping') }}
                        </button>
                    </form>
                    <p class="mt-3 text-xs leading-6 text-zinc-500">{{ __('chapung.pages.cart.shipping_note') }}</p>
                </div>

                <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
                    <h2 class="text-sm font-black uppercase tracking-[0.18em] text-white">{{ __('chapung.pages.cart.coupon') }}</h2>
                    <form method="POST" action="{{ route('cart.coupon.apply') }}" class="mt-4 space-y-3">
                        @csrf
                        <label for="coupon_code" class="sr-only">{{ __('chapung.pages.cart.coupon_code') }}</label>
                        <input id="coupon_code" name="coupon_code" value="{{ old('coupon_code', $cart['coupon_code']) }}" placeholder="CHAPUNG10" maxlength="32" class="w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm font-black uppercase tracking-[0.14em] text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                        @error('coupon_code') <p class="text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-yellow-600/60 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-yellow-500 hover:bg-yellow-600 hover:text-black">
                            <x-heroicon-o-ticket class="h-4 w-4" aria-hidden="true" />
                            {{ __('chapung.pages.cart.apply_coupon') }}
                        </button>
                    </form>
                    <p class="mt-3 text-xs leading-6 text-zinc-500">{{ __('chapung.pages.cart.coupon_hint') }}</p>
                </div>

                <div class="rounded-lg border border-zinc-800 bg-black p-5 text-xs leading-6 text-zinc-500">
                    <div class="flex gap-3">
                        <x-heroicon-o-shield-check class="mt-0.5 h-5 w-5 shrink-0 text-yellow-600" aria-hidden="true" />
                        <p>{{ __('chapung.pages.cart.secure_note') }}</p>
                    </div>
                </div>
            </aside>
        </div>
    </section>
@endsection
