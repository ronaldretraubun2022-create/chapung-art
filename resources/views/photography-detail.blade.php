@extends('layouts.public')

@php
    $mainImage = $photo->thumbnail ?: $photo->og_image;
    $description = str(strip_tags($photo->excerpt ?: $photo->description ?: __('chapung.pages.detail.photography_description')))->limit(160);
    $price = $photo->price ? 'Rp '.number_format((float) $photo->price, 0, ',', '.') : __('chapung.marketplace.by_request');
    $whatsapp = preg_replace('/\D+/', '', site_setting('whatsapp', (string) config('chapung.contact_whatsapp'))) ?: (string) config('chapung.contact_whatsapp');
    $message = rawurlencode('Halo Chapung Art, saya tertarik dengan photography: '.$photo->title.' - '.route('photography.show', $photo->slug));
    $productSignal = str(collect([$photo->license, $photo->category?->name, $photo->title])->filter()->implode(' '))->lower();
    $isDigitalProduct = $productSignal->contains(['digital', 'download', 'file']);
    $productType = $isDigitalProduct ? __('chapung.photography_product.digital') : __('chapung.photography_product.physical_print');
    $deliveryType = $isDigitalProduct ? __('chapung.photography_product.secure_digital_delivery') : __('chapung.photography_product.manual_shipping');
    $productAttributes = collect([
        __('chapung.photography_product.product_type') => $productType,
        __('chapung.photography_product.delivery_type') => $deliveryType,
        __('chapung.photography_product.location') => $photo->location ?: $photo->province,
        __('chapung.photography_product.camera') => $photo->camera,
        __('chapung.photography_product.lens') => $photo->lens,
        __('chapung.photography_product.taken_at') => optional($photo->taken_at)->format('d M Y'),
        __('chapung.photography_product.license') => $photo->license,
        __('chapung.pages.cart.stock') => $photo->stock,
    ])->filter(fn ($value) => filled($value));
@endphp

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('photography.show', $photo, [
        'title' => $photo->title.' | '.site_setting('site_name', 'Chapung Art'),
        'description' => (string) $description,
        'og_image' => $mainImage ? asset('storage/'.$mainImage) : asset('images/og-image.jpg'),
        'canonical_url' => route('photography.show', $photo->slug),
    ])])
@endsection

@section('content')
    <section class="px-4 py-10 sm:px-6 lg:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1.1fr_.9fr]">
            <div>
                <div class="group overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 p-3">
                    @include('partials.public.image', [
                        'path' => $mainImage,
                        'alt' => $photo->title,
                        'ratio' => 'aspect-[4/3]',
                        'label' => __('chapung.types.photography'),
                        'width' => 1200,
                        'height' => 900,
                        'loading' => 'eager',
                        'fetchPriority' => 'high',
                    ])
                </div>
                @if ($photo->mediaItems->count())
                    <div class="mt-4 grid grid-cols-3 gap-3">
                        @foreach ($photo->mediaItems->take(6) as $media)
                            @include('partials.public.image', ['path' => $media->file_path, 'alt' => $media->alt_text ?: $photo->title, 'ratio' => 'aspect-square', 'label' => __('chapung.product_detail.gallery_image')])
                        @endforeach
                    </div>
                @endif
            </div>

            <article class="lg:sticky lg:top-28 lg:self-start">
                <div class="flex flex-wrap gap-2">
                    <span class="ca-badge {{ $isDigitalProduct ? 'ca-badge-gold' : 'ca-badge-muted' }}">{{ $productType }}</span>
                    <span class="ca-badge ca-badge-muted">{{ $photo->category?->name ?: __('chapung.types.photography') }}</span>
                </div>
                <h1 class="mt-4 text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">{{ $photo->title }}</h1>
                <p class="mt-5 text-lg text-zinc-300">{{ $photo->artist_display_name ?: __('chapung.home.artist_fallback') }}</p>
                <p class="mt-6 text-3xl font-black text-yellow-500">{{ $price }}</p>

                <div class="mt-8 grid gap-3 border-y border-zinc-800 py-6 text-sm text-zinc-400 sm:grid-cols-2">
                    @foreach ($productAttributes as $label => $value)
                        <div class="rounded-md border border-zinc-800 bg-black/35 p-4">
                            <span class="block text-[10px] font-black uppercase tracking-[0.16em] text-zinc-500">{{ $label }}</span>
                            <strong class="mt-2 block text-white">{{ $value }}</strong>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 rounded-lg border border-yellow-600/25 bg-yellow-600/10 p-4 text-sm leading-6 text-zinc-300">
                    {{ $isDigitalProduct ? __('chapung.photography_product.digital_note') : __('chapung.photography_product.physical_note') }}
                </div>

                <div class="prose prose-invert mt-8 max-w-none prose-p:leading-8 prose-a:text-yellow-500">
                    {!! $photo->description ?: '<p>'.e(__('chapung.photography_product.description_empty')).'</p>' !!}
                </div>

                <a href="https://wa.me/{{ $whatsapp }}?text={{ $message }}" target="_blank" rel="noopener" class="mt-8 inline-flex w-full justify-center rounded-md bg-yellow-600 px-6 py-4 text-center text-xs font-black uppercase tracking-[0.2em] text-black hover:bg-yellow-500">{{ __('chapung.pages.detail.inquire_photography') }}</a>
            </article>
        </div>
    </section>

    @if ($relatedPhotographies->count())
        <section class="border-t border-zinc-800 px-4 py-14 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <h2 class="text-2xl font-black uppercase tracking-tight text-white">{{ __('chapung.pages.detail.related_photography') }}</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($relatedPhotographies as $related)
                        @include('partials.public.photography-card', ['photo' => $related])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
