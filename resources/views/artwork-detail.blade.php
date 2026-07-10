@extends('layouts.public')

@php
    $mainImage = $artwork->thumbnail ?: $artwork->og_image;
    $description = str(strip_tags($artwork->excerpt ?: $artwork->description ?: 'Karya seni Papua Selatan dari Chapung Art.'))->limit(160);
    $price = $artwork->price ? 'Rp '.number_format((float) $artwork->price, 0, ',', '.') : 'By request';
    $canAddToCart = $artwork->status === 'available' && (int) ($artwork->stock ?? 0) > 0;
    $whatsapp = preg_replace('/\D+/', '', site_setting('whatsapp', '6281234567890')) ?: '6281234567890';
    $message = rawurlencode('Halo Chapung Art, saya tertarik dengan artwork: '.$artwork->title.' - '.route('artwork.show', $artwork->slug));
@endphp

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('artwork.show', $artwork, [
        'title' => $artwork->title.' | '.site_setting('site_name', 'Chapung Art'),
        'description' => (string) $description,
        'og_image' => $mainImage ? asset('storage/'.$mainImage) : asset('images/og-image.jpg'),
        'canonical_url' => route('artwork.show', $artwork->slug),
    ])])
@endsection

@section('content')
    <section class="px-4 py-10 sm:px-6 lg:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1.1fr_.9fr]">
            <div>
                <div class="group overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 p-3">
                    @include('partials.public.image', ['path' => $mainImage, 'alt' => $artwork->title, 'ratio' => 'aspect-[4/3]', 'label' => 'Artwork'])
                </div>

                @if ($artwork->mediaItems->count())
                    <div class="mt-4 grid grid-cols-3 gap-3">
                        @foreach ($artwork->mediaItems->take(6) as $media)
                            @include('partials.public.image', ['path' => $media->file_path, 'alt' => $media->alt_text ?: $artwork->title, 'ratio' => 'aspect-square', 'label' => 'Gallery'])
                        @endforeach
                    </div>
                @endif
            </div>

            <article class="lg:sticky lg:top-28 lg:self-start">
                <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ $artwork->category?->name ?: 'Artwork' }}</p>
                <h1 class="mt-4 text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">{{ $artwork->title }}</h1>
                <p class="mt-5 text-lg text-zinc-300">{{ $artwork->artist_display_name ?: 'Chapung Art' }}</p>
                <p class="mt-6 text-3xl font-black text-yellow-500">{{ $price }}</p>

                <div class="mt-8 grid gap-3 border-y border-zinc-800 py-6 text-sm text-zinc-400 sm:grid-cols-2">
                    <div><span class="block text-zinc-500">Material</span><strong class="text-white">{{ $artwork->material ?: $artwork->medium ?: '-' }}</strong></div>
                    <div><span class="block text-zinc-500">Technique</span><strong class="text-white">{{ $artwork->technique ?: '-' }}</strong></div>
                    <div><span class="block text-zinc-500">Dimension</span><strong class="text-white">{{ $artwork->width && $artwork->height ? $artwork->width.' x '.$artwork->height.' cm' : ($artwork->size ?: '-') }}</strong></div>
                    <div><span class="block text-zinc-500">Stock</span><strong class="text-white">{{ $artwork->stock ?? 1 }}</strong></div>
                    <div><span class="block text-zinc-500">Condition</span><strong class="text-white">{{ $artwork->condition ?: '-' }}</strong></div>
                    <div><span class="block text-zinc-500">Location</span><strong class="text-white">{{ $artwork->location ?: '-' }}</strong></div>
                </div>

                <div class="prose prose-invert mt-8 max-w-none prose-p:leading-8 prose-a:text-yellow-500">
                    {!! $artwork->description ?: '<p>Deskripsi artwork belum tersedia.</p>' !!}
                </div>

                <div class="mt-8 grid gap-3 sm:grid-cols-[1fr_auto]">
                    <form method="POST" action="{{ route('cart.store') }}" class="grid gap-3 sm:grid-cols-[110px_1fr]">
                        @csrf
                        <input type="hidden" name="artwork_id" value="{{ $artwork->id }}">
                        <label for="quantity" class="sr-only">Quantity</label>
                        <input id="quantity" name="quantity" type="number" min="1" max="{{ max(1, (int) ($artwork->stock ?? 1)) }}" value="1" @disabled(! $canAddToCart) class="h-12 rounded-md border border-zinc-800 bg-zinc-950 px-4 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50 focus:border-yellow-600 focus:ring-yellow-600">
                        <button type="submit" @disabled(! $canAddToCart) class="h-12 rounded-md bg-yellow-600 px-5 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500 disabled:cursor-not-allowed disabled:bg-zinc-800 disabled:text-zinc-500">Add to Cart</button>
                    </form>
                    <a href="https://wa.me/{{ $whatsapp }}?text={{ $message }}" target="_blank" rel="noopener" class="inline-flex h-12 justify-center rounded-md border border-yellow-600/60 px-5 py-4 text-center text-xs font-black uppercase tracking-[0.18em] text-yellow-500 hover:bg-yellow-600 hover:text-black">Inquire</a>
                </div>
            </article>
        </div>
    </section>

    @if ($relatedArtworks->count())
        <section class="border-t border-zinc-800 px-4 py-14 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <h2 class="text-2xl font-black uppercase tracking-tight text-white">Related Artwork</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($relatedArtworks as $related)
                        @include('partials.public.artwork-card', ['artwork' => $related])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
