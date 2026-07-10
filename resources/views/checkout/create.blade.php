@extends('layouts.public')

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('checkout.create', fallback: [
        'title' => 'Checkout | '.site_setting('site_name', 'Chapung Art'),
        'description' => 'Checkout artwork Chapung Art.',
        'canonical_url' => route('checkout.create'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.16),transparent_30rem),#050505] px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Marketplace</p>
            <h1 class="mt-4 text-4xl font-black uppercase tracking-tight text-white sm:text-6xl">Checkout</h1>
            <p class="mt-5 max-w-2xl text-sm leading-7 text-zinc-400">Lengkapi data pemesan untuk membuat order artwork.</p>
        </div>
    </section>

    <section class="px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[1fr_380px]">
            <form method="POST" action="{{ route('checkout.store') }}" class="space-y-6 rounded-lg border border-zinc-800 bg-zinc-950 p-5" id="checkout-form">
                @csrf
                <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">

                <div>
                    <h2 class="text-lg font-black uppercase tracking-tight text-white">Customer Detail</h2>
                    <p class="mt-2 text-sm leading-6 text-zinc-500">Data ini dipakai tim Chapung Art untuk konfirmasi order dan pengiriman.</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="customer_name" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">Name</label>
                        <input id="customer_name" name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" required autocomplete="name" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                        @error('customer_name') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="customer_email" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">Email</label>
                        <input id="customer_email" name="customer_email" type="email" value="{{ old('customer_email', auth()->user()?->email) }}" required autocomplete="email" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                        @error('customer_email') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="customer_phone" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">Phone</label>
                        <input id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required autocomplete="tel" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                        @error('customer_phone') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="customer_whatsapp" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">WhatsApp</label>
                        <input id="customer_whatsapp" name="customer_whatsapp" value="{{ old('customer_whatsapp') }}" autocomplete="tel" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                        @error('customer_whatsapp') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="province" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">Province</label>
                        <input id="province" name="province" value="{{ old('province', 'Papua Selatan') }}" required class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                        @error('province') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="city" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">City</label>
                        <input id="city" name="city" value="{{ old('city') }}" required class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                        @error('city') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="address" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">Address</label>
                    <textarea id="address" name="address" rows="4" required class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">{{ old('address') }}</textarea>
                    @error('address') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="notes" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">Notes</label>
                    <textarea id="notes" name="notes" rows="4" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">{{ old('notes') }}</textarea>
                    @error('notes') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                </div>

                @error('cart') <p class="text-sm font-bold text-red-400">{{ $message }}</p> @enderror
                @error('checkout_token') <p class="text-sm font-bold text-red-400">{{ $message }}</p> @enderror
                @error('quantity') <p class="text-sm font-bold text-red-400">{{ $message }}</p> @enderror

                <button type="submit" class="w-full rounded-md bg-yellow-600 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">Place Order</button>
            </form>

            <aside class="h-fit rounded-lg border border-zinc-800 bg-zinc-950 p-5 lg:sticky lg:top-28">
                <h2 class="text-lg font-black uppercase tracking-tight text-white">Order Summary</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($cart['items'] as $item)
                        <div class="flex gap-3 border-b border-zinc-800 pb-4">
                            <div class="w-20 shrink-0">
                                @include('partials.public.image', ['path' => $item['thumbnail'], 'alt' => $item['title'], 'ratio' => 'aspect-square', 'label' => 'Artwork', 'width' => 160, 'height' => 160])
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-black uppercase tracking-tight text-white">{{ $item['title'] }}</p>
                                <p class="mt-1 text-xs text-zinc-500">Qty {{ $item['quantity'] }}</p>
                                <p class="mt-2 text-sm font-bold text-yellow-500">Rp {{ number_format((float) $item['line_total'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 space-y-3 border-t border-zinc-800 pt-5 text-sm">
                    <div class="flex justify-between gap-4 text-zinc-400"><span>Items</span><strong class="text-white">{{ number_format($cart['count']) }}</strong></div>
                    <div class="flex justify-between gap-4 text-zinc-400"><span>Subtotal</span><strong class="text-white">Rp {{ number_format((float) $cart['subtotal'], 0, ',', '.') }}</strong></div>
                    <div class="flex justify-between gap-4 text-zinc-400"><span>Total</span><strong class="text-yellow-500">Rp {{ number_format((float) $cart['total'], 0, ',', '.') }}</strong></div>
                </div>
            </aside>
        </div>
    </section>
@endsection
