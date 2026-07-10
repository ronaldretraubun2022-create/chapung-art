@extends('layouts.public')

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('cart.index', fallback: [
        'title' => 'Cart | '.site_setting('site_name', 'Chapung Art'),
        'description' => 'Cart pembelian artwork Chapung Art.',
        'canonical_url' => route('cart.index'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.16),transparent_30rem),#050505] px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Marketplace</p>
            <h1 class="mt-4 text-4xl font-black uppercase tracking-tight text-white sm:text-6xl">Cart</h1>
            <p class="mt-5 max-w-2xl text-sm leading-7 text-zinc-400">Kelola artwork pilihan sebelum melanjutkan pembelian.</p>
        </div>
    </section>

    <section class="px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[1fr_360px]">
            <div class="space-y-4">
                @forelse ($cart['items'] as $item)
                    <article class="grid gap-4 rounded-lg border border-zinc-800 bg-zinc-950 p-4 sm:grid-cols-[140px_1fr]">
                        <a href="{{ route('artwork.show', $item['slug']) }}" class="block">
                            @include('partials.public.image', ['path' => $item['thumbnail'], 'alt' => $item['title'], 'ratio' => 'aspect-square', 'label' => 'Artwork', 'width' => 320, 'height' => 320])
                        </a>

                        <div class="grid gap-5 md:grid-cols-[1fr_auto]">
                            <div>
                                <a href="{{ route('artwork.show', $item['slug']) }}" class="text-xl font-black uppercase tracking-tight text-white hover:text-yellow-500">{{ $item['title'] }}</a>
                                <p class="mt-2 text-sm text-zinc-400">{{ $item['artist_name'] ?: 'Chapung Art' }}</p>
                                <p class="mt-4 text-sm font-bold text-yellow-500">Rp {{ number_format((float) $item['price'], 0, ',', '.') }}</p>
                                <p class="mt-1 text-xs uppercase tracking-[0.16em] text-zinc-500">Stock {{ $item['stock'] }}</p>
                            </div>

                            <div class="flex flex-col gap-3 md:min-w-48 md:items-end">
                                <form method="POST" action="{{ route('cart.update', $item['artwork_id']) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label for="quantity-{{ $item['artwork_id'] }}" class="sr-only">Quantity</label>
                                    <input id="quantity-{{ $item['artwork_id'] }}" name="quantity" type="number" min="1" max="{{ $item['stock'] }}" value="{{ $item['quantity'] }}" class="h-11 w-20 rounded-md border border-zinc-800 bg-black px-3 text-sm font-bold text-white focus:border-yellow-600 focus:ring-yellow-600">
                                    <button type="submit" class="h-11 rounded-md border border-yellow-600/60 px-4 text-xs font-black uppercase tracking-[0.16em] text-yellow-500 hover:bg-yellow-600 hover:text-black">Update</button>
                                </form>

                                <form method="POST" action="{{ route('cart.destroy', $item['artwork_id']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500 hover:text-red-400">Remove</button>
                                </form>

                                <p class="text-right text-sm font-black text-white">Rp {{ number_format((float) $item['line_total'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-8 text-center">
                        <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-600">Empty Cart</p>
                        <h2 class="mt-4 text-2xl font-black uppercase tracking-tight text-white">Belum ada artwork di cart</h2>
                        <p class="mt-3 text-sm leading-7 text-zinc-400">Pilih artwork dari gallery untuk mulai menyusun pesanan.</p>
                        <a href="{{ route('gallery') }}" class="mt-6 inline-flex rounded-md bg-yellow-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">Browse Artwork</a>
                    </div>
                @endforelse
            </div>

            <aside class="h-fit rounded-lg border border-zinc-800 bg-zinc-950 p-5 lg:sticky lg:top-28">
                <h2 class="text-lg font-black uppercase tracking-tight text-white">Summary</h2>
                <div class="mt-5 space-y-3 border-y border-zinc-800 py-5 text-sm">
                    <div class="flex justify-between gap-4 text-zinc-400"><span>Items</span><strong class="text-white">{{ number_format($cart['count']) }}</strong></div>
                    <div class="flex justify-between gap-4 text-zinc-400"><span>Subtotal</span><strong class="text-white">Rp {{ number_format((float) $cart['subtotal'], 0, ',', '.') }}</strong></div>
                    <div class="flex justify-between gap-4 text-zinc-400"><span>Total</span><strong class="text-yellow-500">Rp {{ number_format((float) $cart['total'], 0, ',', '.') }}</strong></div>
                </div>
                @if ($cart['items'] !== [])
                    <a href="{{ route('checkout.create') }}" class="mt-5 inline-flex w-full justify-center rounded-md bg-yellow-600 px-5 py-4 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">Checkout</a>
                @else
                    <a href="{{ route('gallery') }}" class="mt-5 inline-flex w-full justify-center rounded-md bg-yellow-600 px-5 py-4 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">Browse Artwork</a>
                @endif
                <p class="mt-4 text-xs leading-6 text-zinc-500">Checkout final akan dikonfirmasi oleh tim Chapung Art sebelum pembayaran.</p>
            </aside>
        </div>
    </section>
@endsection
