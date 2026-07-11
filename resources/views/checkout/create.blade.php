@extends('layouts.public')

@php
    $selectedShipping = old('shipping_area', $cart['shipping_area'] ?? 'pickup');
    $selectedPayment = old('payment_method', array_key_first($paymentMethods) ?: 'bank_transfer');
@endphp

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('checkout.create', fallback: [
        'title' => __('chapung.pages.checkout.title').' | '.site_setting('site_name', 'Chapung Art'),
        'description' => __('chapung.pages.checkout.description'),
        'canonical_url' => route('checkout.create'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.16),transparent_30rem),#050505] px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <div class="mx-auto flex max-w-7xl flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('chapung.pages.checkout.marketplace_label') }}</p>
                <h1 class="mt-4 text-4xl font-black uppercase tracking-tight text-white sm:text-6xl">{{ __('chapung.pages.checkout.title') }}</h1>
                <p class="mt-5 max-w-2xl text-sm leading-7 text-zinc-400">{{ __('chapung.pages.checkout.intro') }}</p>
            </div>
            <a href="{{ route('cart.index') }}" class="inline-flex w-fit items-center gap-2 rounded-md border border-zinc-800 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-zinc-200 hover:border-yellow-600 hover:text-yellow-500">
                <x-heroicon-o-arrow-left class="h-4 w-4" aria-hidden="true" />
                {{ __('chapung.pages.checkout.back_to_cart') }}
            </a>
        </div>
    </section>

    <section class="px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[minmax(0,1fr)_410px]">
            <form method="POST" action="{{ route('checkout.store') }}" class="space-y-5" id="checkout-form">
                @csrf
                <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">

                <section class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-yellow-600 text-sm font-black text-black">1</span>
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-white">{{ __('chapung.pages.checkout.customer_detail') }}</h2>
                            <p class="mt-2 text-sm leading-6 text-zinc-500">{{ __('chapung.pages.checkout.customer_note') }}</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="customer_name" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.pages.checkout.name') }}</label>
                            <input id="customer_name" name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" required autocomplete="name" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                            @error('customer_name') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="customer_email" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">Email</label>
                            <input id="customer_email" name="customer_email" type="email" value="{{ old('customer_email', auth()->user()?->email) }}" required autocomplete="email" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                            @error('customer_email') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="customer_phone" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.pages.checkout.phone') }}</label>
                            <input id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required autocomplete="tel" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                            @error('customer_phone') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="customer_whatsapp" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">WhatsApp</label>
                            <input id="customer_whatsapp" name="customer_whatsapp" value="{{ old('customer_whatsapp') }}" autocomplete="tel" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                            @error('customer_whatsapp') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-yellow-600 text-sm font-black text-black">2</span>
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-white">{{ __('chapung.pages.checkout.shipping_address') }}</h2>
                            <p class="mt-2 text-sm leading-6 text-zinc-500">{{ __('chapung.pages.checkout.shipping_note') }}</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="province" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.pages.checkout.province') }}</label>
                            <input id="province" name="province" value="{{ old('province', 'Papua Selatan') }}" required class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                            @error('province') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="city" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.pages.checkout.city') }}</label>
                            <input id="city" name="city" value="{{ old('city') }}" required class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                            @error('city') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="district" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.pages.checkout.district') }}</label>
                            <input id="district" name="district" value="{{ old('district') }}" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                            @error('district') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="postal_code" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.pages.checkout.postal_code') }}</label>
                            <input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" inputmode="numeric" autocomplete="postal-code" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                            @error('postal_code') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="address" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.pages.checkout.address') }}</label>
                        <textarea id="address" name="address" rows="4" required class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">{{ old('address') }}</textarea>
                        @error('address') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>
                </section>

                <section class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-yellow-600 text-sm font-black text-black">3</span>
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-white">{{ __('chapung.pages.checkout.shipping_method') }}</h2>
                            <p class="mt-2 text-sm leading-6 text-zinc-500">{{ __('chapung.pages.cart.shipping_note') }}</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 md:grid-cols-2">
                        @foreach ($cart['shipping_options'] as $area => $option)
                            <label class="flex cursor-pointer gap-3 rounded-lg border border-zinc-800 bg-black/40 p-4 transition hover:border-yellow-600">
                                <input name="shipping_area" type="radio" value="{{ $area }}" @checked($selectedShipping === $area) class="mt-1 border-zinc-700 bg-black text-yellow-600 focus:ring-yellow-600">
                                <span>
                                    <span class="block font-black text-white">{{ $option['label'] }}</span>
                                    <span class="mt-1 block text-sm text-yellow-500">Rp {{ number_format((float) $option['amount'], 0, ',', '.') }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('shipping_area') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror

                    <div class="mt-4">
                        <label for="shipping_notes" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.pages.checkout.shipping_notes') }}</label>
                        <textarea id="shipping_notes" name="shipping_notes" rows="3" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">{{ old('shipping_notes') }}</textarea>
                        @error('shipping_notes') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>
                </section>

                <section class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
                    <div class="flex items-start gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-yellow-600 text-sm font-black text-black">4</span>
                        <div>
                            <h2 class="text-lg font-black uppercase tracking-tight text-white">{{ __('chapung.pages.checkout.payment_method') }}</h2>
                            <p class="mt-2 text-sm leading-6 text-zinc-500">{{ __('chapung.pages.checkout.payment_note') }}</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3">
                        @foreach ($paymentMethods as $method => $payment)
                            <label class="flex cursor-pointer gap-3 rounded-lg border border-zinc-800 bg-black/40 p-4 transition hover:border-yellow-600">
                                <input name="payment_method" type="radio" value="{{ $method }}" @checked($selectedPayment === $method) class="mt-1 border-zinc-700 bg-black text-yellow-600 focus:ring-yellow-600">
                                <span>
                                    <span class="block font-black text-white">{{ $payment['label'] }}</span>
                                    <span class="mt-1 block text-sm leading-6 text-zinc-500">{{ $payment['description'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('payment_method') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror

                    <div class="mt-5">
                        @include('partials.public.payment-information')
                    </div>
                </section>

                <section class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
                    <label for="notes" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.pages.checkout.notes') }}</label>
                    <textarea id="notes" name="notes" rows="4" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">{{ old('notes') }}</textarea>
                    @error('notes') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                </section>

                @error('cart') <p class="text-sm font-bold text-red-400">{{ $message }}</p> @enderror
                @error('checkout_token') <p class="text-sm font-bold text-red-400">{{ $message }}</p> @enderror
                @error('quantity') <p class="text-sm font-bold text-red-400">{{ $message }}</p> @enderror

                <button type="submit" class="w-full rounded-md bg-yellow-600 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">{{ __('chapung.pages.checkout.place_order') }}</button>
            </form>

            <aside class="h-fit rounded-lg border border-zinc-800 bg-zinc-950 p-5 lg:sticky lg:top-28">
                <h2 class="text-lg font-black uppercase tracking-tight text-white">{{ __('chapung.pages.checkout.order_summary') }}</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($cart['items'] as $item)
                        <div class="flex gap-3 border-b border-zinc-800 pb-4">
                            <div class="w-20 shrink-0">
                                @include('partials.public.image', ['path' => $item['thumbnail'], 'alt' => $item['title'], 'ratio' => 'aspect-square', 'label' => __('chapung.types.artwork'), 'width' => 160, 'height' => 160])
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-black uppercase tracking-tight text-white">{{ $item['title'] }}</p>
                                <p class="mt-1 text-xs text-zinc-500">{{ __('chapung.pages.checkout.qty') }} {{ $item['quantity'] }}</p>
                                <p class="mt-2 text-sm font-bold text-yellow-500">Rp {{ number_format((float) $item['line_total'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 space-y-3 border-t border-zinc-800 pt-5 text-sm">
                    <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.items') }}</span><strong class="text-white">{{ number_format($cart['count']) }}</strong></div>
                    <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.subtotal') }}</span><strong class="text-white">Rp {{ number_format((float) $cart['subtotal'], 0, ',', '.') }}</strong></div>
                    <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.discount') }}</span><strong class="text-white">- Rp {{ number_format((float) $cart['discount_total'], 0, ',', '.') }}</strong></div>
                    <div class="flex justify-between gap-4 text-zinc-400"><span>{{ __('chapung.pages.cart.shipping_estimate') }}</span><strong class="text-white">Rp {{ number_format((float) $cart['shipping_estimate'], 0, ',', '.') }}</strong></div>
                    <div class="flex justify-between gap-4 border-t border-zinc-800 pt-4"><span class="font-black text-white">{{ __('chapung.pages.cart.estimated_total') }}</span><strong class="text-xl text-yellow-500">Rp {{ number_format((float) $cart['estimated_total'], 0, ',', '.') }}</strong></div>
                </div>

                @if ($cart['coupon_code'] || $cart['shipping_label'])
                    <div class="mt-5 space-y-2 rounded-lg border border-zinc-800 bg-black/40 p-4 text-xs text-zinc-400">
                        @if ($cart['coupon_code'])
                            <p><span class="font-black uppercase text-yellow-500">{{ __('chapung.pages.cart.coupon') }}:</span> {{ $cart['coupon_code'] }} / {{ $cart['coupon_label'] }}</p>
                        @endif
                        @if ($cart['shipping_label'])
                            <p><span class="font-black uppercase text-yellow-500">{{ __('chapung.pages.cart.shipping_estimate') }}:</span> {{ $cart['shipping_label'] }}</p>
                        @endif
                    </div>
                @endif

                <p class="mt-5 text-xs leading-6 text-zinc-500">{{ __('chapung.pages.checkout.confirmation_note') }}</p>
            </aside>
        </div>
    </section>
@endsection
