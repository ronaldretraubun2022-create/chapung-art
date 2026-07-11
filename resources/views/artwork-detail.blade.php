@extends('layouts.public')

@php
    use App\Services\ImageUploadService;

    $mainImage = $artwork->thumbnail ?: $artwork->og_image;
    $publicUrl = route('artwork.show', $artwork->slug);
    $galleryImages = collect([[
        'path' => $mainImage,
        'alt' => $artwork->title,
        'title' => __('chapung.product_detail.main_image'),
    ]])
        ->merge($artwork->mediaItems->map(fn ($media) => [
            'path' => $media->file_path,
            'alt' => $media->alt_text ?: $media->title ?: $artwork->title,
            'title' => $media->title ?: __('chapung.product_detail.gallery_image'),
        ]))
        ->filter(fn ($image) => filled($image['path']))
        ->unique('path')
        ->take(7)
        ->map(function ($image) {
            $normalizedPath = ImageUploadService::normalizePath($image['path']);

            return [
                ...$image,
                'url' => $normalizedPath ? asset('storage/'.$normalizedPath) : ImageUploadService::fallbackUrl(),
                'is_fallback' => ! $normalizedPath,
            ];
        })
        ->values();
    $galleryImages = $galleryImages->isNotEmpty() ? $galleryImages : collect([[
        'path' => null,
        'alt' => $artwork->title,
        'title' => __('chapung.product_detail.main_image'),
        'url' => ImageUploadService::fallbackUrl(),
        'is_fallback' => true,
    ]]);
    $selectedImage = $galleryImages->first();
    $hasFallbackOnly = $galleryImages->count() === 1 && ($selectedImage['is_fallback'] ?? false);
    $description = str(strip_tags($artwork->excerpt ?: $artwork->description ?: __('chapung.pages.detail.artwork_description')))->limit(160);
    $priceValue = filled($artwork->price) ? (float) $artwork->price : null;
    $price = $priceValue ? 'Rp '.number_format($priceValue, 0, ',', '.') : __('chapung.marketplace.by_request');
    $discountPercent = $priceValue && $artwork->is_featured ? 12 : 0;
    $oldPrice = $discountPercent > 0 ? round($priceValue / (1 - ($discountPercent / 100)), -3) : null;
    $status = (string) ($artwork->status ?? 'available');
    $stockCount = max(0, (int) ($artwork->stock ?? 0));
    $canAddToCart = $status === 'available' && $stockCount > 0;
    $isSold = $status === 'sold';
    $statusLabel = match ($status) {
        'available' => __('chapung.home.status_available'),
        'sold' => __('chapung.home.status_sold'),
        'reserved' => __('chapung.product_detail.status_reserved'),
        default => str($status)->headline()->toString(),
    };
    $approvedReviews = $artwork->relationLoaded('approvedReviews') ? $artwork->approvedReviews : collect();
    $reviewCount = (int) ($artwork->approved_reviews_count ?? $approvedReviews->count());
    $rating = $artwork->approved_reviews_avg_rating !== null
        ? (float) $artwork->approved_reviews_avg_rating
        : ($approvedReviews->isNotEmpty() ? (float) $approvedReviews->avg('rating') : 0);
    $whatsapp = preg_replace('/\D+/', '', site_setting('whatsapp', (string) config('chapung.contact_whatsapp'))) ?: (string) config('chapung.contact_whatsapp');
    $message = rawurlencode('Halo Chapung Art, saya tertarik dengan artwork: '.$artwork->title.' - '.$publicUrl);
    $shareMessage = rawurlencode($artwork->title.' - '.$publicUrl);
    $facebookShareUrl = 'https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($publicUrl);
    $artist = $artwork->artist;
    $artistLocation = collect([$artist?->city, $artist?->province, $artist?->country])->filter()->implode(', ');
    $specifications = collect([
        __('chapung.product_detail.specs.medium') => $artwork->medium,
        __('chapung.product_detail.specs.material') => $artwork->material,
        __('chapung.product_detail.specs.technique') => $artwork->technique,
        __('chapung.product_detail.specs.dimension') => $artwork->width && $artwork->height ? $artwork->width.' x '.$artwork->height.($artwork->depth ? ' x '.$artwork->depth : '').' cm' : $artwork->size,
        __('chapung.product_detail.specs.year') => $artwork->year,
        __('chapung.product_detail.specs.frame') => $artwork->frame,
        __('chapung.product_detail.specs.condition') => $artwork->condition,
        __('chapung.product_detail.specs.location') => $artwork->location,
        __('chapung.product_detail.specs.certificate') => $artwork->certificate_number,
        __('chapung.product_detail.specs.license') => $artwork->license,
    ])->filter(fn ($value) => filled($value));
    $variants = collect([
        __('chapung.product_detail.variants.format') => $artwork->medium ?: $artwork->category?->name,
        __('chapung.product_detail.variants.material') => $artwork->material,
        __('chapung.product_detail.variants.frame') => $artwork->frame,
        __('chapung.product_detail.variants.size') => $artwork->size ?: ($artwork->width && $artwork->height ? $artwork->width.' x '.$artwork->height.' cm' : null),
    ])->filter(fn ($value) => filled($value));
@endphp

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('artwork.show', $artwork, [
        'title' => $artwork->title.' | '.site_setting('site_name', 'Chapung Art'),
        'description' => (string) $description,
        'og_image' => $mainImage ? asset('storage/'.ImageUploadService::normalizePath($mainImage)) : asset('images/og-image.jpg'),
        'canonical_url' => route('artwork.show', $artwork->slug),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-zinc-950 px-4 py-4 sm:px-6 lg:px-8">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-2 text-xs font-bold text-zinc-500">
            <a href="{{ route('artworks.index') }}" class="inline-flex items-center gap-1 text-zinc-300 hover:text-yellow-500">
                <x-heroicon-o-arrow-left class="h-4 w-4" aria-hidden="true" />
                {{ __('chapung.product_detail.back_to_catalog') }}
            </a>
            <span>/</span>
            <span>{{ $artwork->category?->name ?: __('chapung.types.artwork') }}</span>
            @if ($artwork->collection)
                <span>/</span>
                <span>{{ $artwork->collection->name }}</span>
            @endif
        </div>
    </section>

    <section class="px-4 py-8 pb-28 sm:px-6 lg:px-8 lg:py-12" data-artwork-detail>
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[minmax(0,1.08fr)_minmax(360px,.92fr)] xl:gap-12" x-data="{ images: @js($galleryImages), selected: @js($selectedImage), zoomOpen: false, loading: true }">
            <div class="space-y-4">
                <div class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 p-2 shadow-2xl shadow-black/30">
                    <button type="button" class="group relative block w-full overflow-hidden rounded-md bg-zinc-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-600" x-on:click="zoomOpen = true" aria-label="{{ __('chapung.product_detail.zoom_open') }}" data-artwork-zoom-trigger>
                        <div class="ca-skeleton absolute inset-0 rounded-none" x-show="loading" aria-hidden="true"></div>
                        <img :src="selected.url" :alt="selected.alt" width="1200" height="1000" class="aspect-[4/5] h-full w-full object-cover transition duration-500 group-hover:scale-[1.03] lg:aspect-[5/4]" loading="eager" decoding="async" fetchpriority="high" x-on:load="loading = false" x-on:error="$event.target.src='{{ ImageUploadService::fallbackUrl() }}'; loading = false">
                        <span class="absolute bottom-3 left-3 inline-flex items-center gap-2 rounded-md bg-black/75 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-white backdrop-blur">
                            <x-heroicon-o-magnifying-glass-plus class="h-4 w-4" aria-hidden="true" />
                            {{ __('chapung.product_detail.zoom_open') }}
                        </span>
                        @if ($isSold)
                            <span class="absolute right-3 top-3 rounded-md bg-red-800 px-3 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-white shadow-lg shadow-black/40">{{ __('chapung.home.status_sold') }}</span>
                        @endif
                    </button>
                </div>

                @if ($hasFallbackOnly)
                    <div class="rounded-lg border border-zinc-800 bg-black/40 p-4 text-sm font-bold text-zinc-400" role="status">
                        {{ __('chapung.product_detail.fallback_image') }}
                    </div>
                @endif

                <div class="grid grid-cols-4 gap-3 sm:grid-cols-6" aria-label="{{ __('chapung.product_detail.gallery') }}" data-artwork-gallery>
                    @foreach ($galleryImages as $image)
                        <button type="button" x-on:click="selected = images[{{ $loop->index }}]; loading = true" class="group rounded-lg border border-zinc-800 bg-zinc-950 p-1 transition hover:border-yellow-600 focus-visible:border-yellow-600" :class="selected.url === images[{{ $loop->index }}].url ? 'border-yellow-600' : ''" aria-label="{{ $image['title'] }}">
                            @include('partials.public.image', [
                                'path' => $image['path'],
                                'alt' => $image['alt'],
                                'ratio' => 'aspect-square',
                                'label' => __('chapung.product_detail.gallery_image'),
                                'width' => 240,
                                'height' => 240,
                            ])
                        </button>
                    @endforeach
                </div>

                <div x-show="zoomOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[80] flex items-center justify-center bg-black/90 p-4" role="dialog" aria-modal="true" aria-label="{{ __('chapung.product_detail.zoom_open') }}" x-on:keydown.escape.window="zoomOpen = false" data-artwork-zoom-modal>
                    <button type="button" class="absolute right-4 top-4 inline-flex h-11 w-11 items-center justify-center rounded-md border border-white/15 bg-black text-white hover:border-yellow-600 hover:text-yellow-500" x-on:click="zoomOpen = false" aria-label="{{ __('chapung.product_detail.zoom_close') }}">
                        <x-heroicon-o-x-mark class="h-5 w-5" aria-hidden="true" />
                    </button>
                    <img :src="selected.url" :alt="selected.alt" class="max-h-[86vh] max-w-[94vw] rounded-lg object-contain shadow-2xl shadow-black" width="1600" height="1200" x-on:error="$event.target.src='{{ ImageUploadService::fallbackUrl() }}'">
                </div>
            </div>

            <article class="lg:sticky lg:top-28 lg:self-start">
                <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-5 shadow-2xl shadow-black/30 sm:p-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-yellow-600 px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-black">
                            {{ $artwork->is_featured ? __('chapung.home.official_badge') : __('chapung.marketplace.badges.ready') }}
                        </span>
                        <span class="rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] {{ $isSold ? 'border-red-800 bg-red-950/60 text-red-200' : 'border-zinc-800 text-zinc-400' }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    @if (! $canAddToCart)
                        <div class="mt-5 rounded-lg border {{ $isSold ? 'border-red-800/70 bg-red-950/30' : 'border-yellow-600/40 bg-yellow-600/10' }} p-4" role="status">
                            <p class="text-sm font-black uppercase tracking-[0.16em] {{ $isSold ? 'text-red-200' : 'text-yellow-500' }}">
                                {{ $isSold ? __('chapung.product_detail.sold_title') : __('chapung.product_detail.unavailable_title') }}
                            </p>
                            <p class="mt-2 text-sm leading-6 text-zinc-400">
                                {{ $isSold ? __('chapung.product_detail.sold_note') : __('chapung.product_detail.unavailable_note') }}
                            </p>
                        </div>
                    @endif

                    <h1 class="mt-5 text-3xl font-black uppercase leading-tight tracking-tight text-white sm:text-5xl">{{ $artwork->title }}</h1>

                    <div class="mt-4 flex flex-wrap items-center gap-3 text-sm text-zinc-400">
                        <span>{{ __('chapung.product_detail.by_artist') }} <strong class="text-white">{{ $artwork->artist_display_name ?: __('chapung.home.artist_fallback') }}</strong></span>
                        <span class="hidden text-zinc-700 sm:inline">/</span>
                        <span>{{ $artwork->category?->name ?: __('chapung.types.artwork') }}</span>
                    </div>

                    <div class="mt-4 flex items-center gap-2 text-sm text-zinc-400" aria-label="{{ __('chapung.marketplace.rating') }}">
                        <span class="inline-flex items-center text-yellow-500" aria-hidden="true">
                            @for ($star = 1; $star <= 5; $star++)
                                <x-heroicon-s-star class="h-4 w-4" />
                            @endfor
                        </span>
                        <strong class="text-white">{{ number_format($rating, 1) }}</strong>
                        <span>({{ number_format($reviewCount) }} {{ __('chapung.product_detail.reviews') }})</span>
                    </div>

                    <div class="mt-6 border-y border-zinc-800 py-5">
                        <div class="flex flex-wrap items-end gap-3">
                            <p class="text-3xl font-black text-yellow-500 sm:text-4xl">{{ $price }}</p>
                            @if ($oldPrice)
                                <p class="pb-1 text-sm font-bold text-zinc-500 line-through">Rp {{ number_format($oldPrice, 0, ',', '.') }}</p>
                                <span class="mb-1 rounded-md bg-red-700 px-2 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-white">-{{ $discountPercent }}%</span>
                            @endif
                        </div>
                        <p class="mt-2 text-sm text-zinc-500">{{ __('chapung.product_detail.price_note') }}</p>
                    </div>

                    @if ($variants->isNotEmpty())
                        <div class="mt-6 space-y-4">
                            <h2 class="text-sm font-black uppercase tracking-[0.18em] text-white">{{ __('chapung.product_detail.variation') }}</h2>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach ($variants as $label => $value)
                                    <div class="rounded-lg border border-zinc-800 bg-black/40 p-4">
                                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-zinc-500">{{ $label }}</p>
                                        <p class="mt-2 text-sm font-black text-white">{{ $value }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 grid gap-3 rounded-lg border border-zinc-800 bg-black/40 p-4 text-sm sm:grid-cols-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-zinc-500">{{ __('chapung.pages.cart.stock') }}</p>
                            <p class="mt-1 font-black text-white">{{ $isSold ? $statusLabel : $stockCount }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-zinc-500">{{ __('chapung.product_detail.location') }}</p>
                            <p class="mt-1 font-black text-white">{{ $artwork->location ?: __('chapung.brand.region') }}</p>
                        </div>
                        @if ($artwork->certificate_number)
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-zinc-500">{{ __('chapung.product_detail.certificate') }}</p>
                                <p class="mt-1 font-black text-white">{{ $artwork->certificate_number }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-[1fr_auto]">
                        <form method="POST" action="{{ route('cart.store') }}" class="grid gap-3 sm:grid-cols-[112px_1fr]">
                            @csrf
                            <input type="hidden" name="artwork_id" value="{{ $artwork->id }}">
                            <label for="quantity" class="sr-only">{{ __('chapung.pages.cart.quantity') }}</label>
                            <input id="quantity" name="quantity" type="number" min="1" max="{{ max(1, (int) ($artwork->stock ?? 1)) }}" value="1" @disabled(! $canAddToCart) class="h-14 rounded-md border border-zinc-800 bg-black px-4 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50 focus:border-yellow-600 focus:ring-yellow-600">
                            <button type="submit" @disabled(! $canAddToCart) class="inline-flex h-14 items-center justify-center gap-2 rounded-md bg-yellow-600 px-5 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500 disabled:cursor-not-allowed disabled:bg-zinc-800 disabled:text-zinc-500">
                                <x-heroicon-o-shopping-bag class="h-4 w-4" aria-hidden="true" />
                                {{ __('chapung.pages.detail.add_to_cart') }}
                            </button>
                        </form>

                        <a href="https://wa.me/{{ $whatsapp }}?text={{ $message }}" target="_blank" rel="noopener" class="inline-flex h-14 items-center justify-center gap-2 rounded-md border border-yellow-600/60 px-5 text-center text-xs font-black uppercase tracking-[0.18em] text-yellow-500 hover:bg-yellow-600 hover:text-black">
                            <x-heroicon-o-chat-bubble-left-right class="h-4 w-4" aria-hidden="true" />
                            {{ __('chapung.pages.detail.inquire') }}
                        </a>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @if ($canAddToCart)
                            <a href="{{ route('checkout.create') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-zinc-800 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-zinc-200 hover:border-yellow-600 hover:text-yellow-500">
                                <x-heroicon-o-credit-card class="h-4 w-4" aria-hidden="true" />
                                {{ __('chapung.product_detail.buy_cta') }}
                            </a>
                        @else
                            <span class="inline-flex cursor-not-allowed items-center justify-center gap-2 rounded-md border border-zinc-800 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-zinc-500">
                                <x-heroicon-o-credit-card class="h-4 w-4" aria-hidden="true" />
                                {{ $statusLabel }}
                            </span>
                        @endif
                        @include('partials.public.favorite-button', [
                            'artwork' => $artwork,
                            'showLabel' => true,
                            'class' => 'inline-flex w-full items-center justify-center gap-2 rounded-md border border-zinc-800 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-zinc-200 hover:border-yellow-600 hover:text-yellow-500',
                            'iconClass' => 'h-4 w-4',
                        ])
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2" aria-label="{{ __('chapung.product_detail.share_title') }}">
                        <a href="https://wa.me/?text={{ $shareMessage }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-md border border-zinc-800 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-zinc-200 hover:border-yellow-600 hover:text-yellow-500">
                            <x-heroicon-o-share class="h-4 w-4" aria-hidden="true" />
                            {{ __('chapung.product_detail.share_whatsapp') }}
                        </a>
                        <a href="{{ $facebookShareUrl }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-md border border-zinc-800 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-zinc-200 hover:border-yellow-600 hover:text-yellow-500">
                            <x-heroicon-o-share class="h-4 w-4" aria-hidden="true" />
                            {{ __('chapung.product_detail.share_facebook') }}
                        </a>
                    </div>

                    @if ($hasDigitalDownload ?? false)
                        <div class="mt-5 rounded-lg border border-yellow-600/30 bg-yellow-600/10 p-4">
                            <div class="flex items-start gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md bg-yellow-600 text-black">
                                    <x-heroicon-o-arrow-down-tray class="h-5 w-5" aria-hidden="true" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-yellow-500">{{ __('chapung.digital_download.title') }}</p>
                                    <p class="mt-2 text-sm leading-6 text-zinc-300">{{ __('chapung.digital_download.description') }}</p>

                                    @auth
                                        @if ($canDownloadDigital ?? false)
                                            <a href="{{ route('artwork.download', $artwork->slug) }}" class="mt-4 inline-flex items-center justify-center gap-2 rounded-md bg-yellow-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">
                                                <x-heroicon-o-lock-open class="h-4 w-4" aria-hidden="true" />
                                                {{ __('chapung.digital_download.download') }}
                                            </a>
                                        @else
                                            <p class="mt-4 rounded-md border border-zinc-800 bg-black/40 p-3 text-sm font-bold text-zinc-400">{{ __('chapung.digital_download.purchase_required') }}</p>
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}" class="mt-4 inline-flex items-center justify-center gap-2 rounded-md border border-yellow-600/60 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-yellow-500 hover:bg-yellow-600 hover:text-black">
                                            <x-heroicon-o-lock-closed class="h-4 w-4" aria-hidden="true" />
                                            {{ __('chapung.digital_download.login_required') }}
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </article>
        </div>
    </section>

    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-zinc-800 bg-black/95 px-4 py-3 shadow-2xl shadow-black backdrop-blur lg:hidden" data-mobile-sticky-purchase>
        <div class="mx-auto flex max-w-7xl items-center gap-3">
            <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-bold text-zinc-400">{{ $artwork->title }}</p>
                <p class="mt-1 text-base font-black text-yellow-500">{{ $price }}</p>
            </div>

            @if ($canAddToCart)
                <form method="POST" action="{{ route('cart.store') }}" class="flex shrink-0 items-center gap-2">
                    @csrf
                    <input type="hidden" name="artwork_id" value="{{ $artwork->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-yellow-600 px-4 text-[11px] font-black uppercase tracking-[0.14em] text-black hover:bg-yellow-500">
                        <x-heroicon-o-shopping-bag class="h-4 w-4" aria-hidden="true" />
                        {{ __('chapung.pages.detail.add_to_cart') }}
                    </button>
                </form>
                <a href="{{ route('checkout.create') }}" class="inline-flex h-11 shrink-0 items-center justify-center rounded-md border border-zinc-700 px-3 text-[11px] font-black uppercase tracking-[0.14em] text-zinc-100 hover:border-yellow-600 hover:text-yellow-500">
                    {{ __('chapung.pages.cart.checkout') }}
                </a>
            @else
                <span class="inline-flex h-11 shrink-0 cursor-not-allowed items-center justify-center rounded-md border border-zinc-800 px-4 text-[11px] font-black uppercase tracking-[0.14em] text-zinc-500">
                    {{ $statusLabel }}
                </span>
            @endif
        </div>
    </div>

    <section class="border-t border-zinc-800 px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[.9fr_1.1fr]">
            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.product_detail.description') }}</p>
                <div class="prose prose-invert mt-5 max-w-none prose-p:leading-8 prose-a:text-yellow-500">
                    {!! $artwork->description ?: '<p>'.e(__('chapung.pages.detail.artwork_description')).'</p>' !!}
                </div>

                @if ($artwork->tags->count())
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach ($artwork->tags as $tag)
                            <span class="rounded-full border border-zinc-800 px-3 py-2 text-xs font-black uppercase tracking-[0.12em] text-zinc-400">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.product_detail.specifications') }}</p>
                <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                    @forelse ($specifications as $label => $value)
                        <div class="rounded-md border border-zinc-800 bg-black/40 p-4">
                            <dt class="text-[10px] font-black uppercase tracking-[0.16em] text-zinc-500">{{ $label }}</dt>
                            <dd class="mt-2 text-sm font-bold text-white">{{ $value }}</dd>
                        </div>
                    @empty
                        <div class="rounded-md border border-zinc-800 bg-black/40 p-4 text-sm text-zinc-400">{{ __('chapung.product_detail.spec_empty') }}</div>
                    @endforelse
                </dl>
            </div>
        </div>
    </section>

    <section class="border-t border-zinc-800 px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[.95fr_1.05fr]">
            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.reviews.title') }}</p>
                <div class="mt-5 flex flex-wrap items-end gap-4">
                    <p class="text-5xl font-black text-white">{{ number_format($rating, 1) }}</p>
                    <div>
                        <div class="flex items-center text-yellow-500" aria-hidden="true">
                            @for ($star = 1; $star <= 5; $star++)
                                <x-heroicon-s-star class="h-5 w-5" />
                            @endfor
                        </div>
                        <p class="mt-2 text-sm text-zinc-400">{{ __('chapung.reviews.summary', ['count' => number_format($reviewCount)]) }}</p>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse ($approvedReviews as $review)
                        <article class="rounded-md border border-zinc-800 bg-black/40 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="font-black text-white">{{ $review->title ?: __('chapung.reviews.default_title') }}</p>
                                    <p class="mt-1 text-xs font-bold text-zinc-500">
                                        {{ $review->reviewer_name ?: $review->user?->name ?: __('chapung.reviews.collector') }}
                                        @if ($review->is_verified_purchase)
                                            <span class="ml-2 rounded-full bg-emerald-500/10 px-2 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-emerald-400">{{ __('chapung.reviews.verified_purchase') }}</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-1 text-yellow-500" aria-label="{{ __('chapung.reviews.rating_value', ['rating' => $review->rating]) }}">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <x-heroicon-s-star class="h-4 w-4 {{ $star <= $review->rating ? 'text-yellow-500' : 'text-zinc-700' }}" />
                                    @endfor
                                </div>
                            </div>
                            <p class="mt-4 text-sm leading-7 text-zinc-300">{{ $review->body }}</p>
                        </article>
                    @empty
                        @include('partials.public.empty-state', ['label' => __('chapung.reviews.title'), 'title' => __('chapung.reviews.empty')])
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.reviews.write_title') }}</p>
                <h2 class="mt-3 text-2xl font-black uppercase tracking-tight text-white">{{ __('chapung.reviews.write_heading') }}</h2>
                <p class="mt-3 text-sm leading-6 text-zinc-500">{{ __('chapung.reviews.moderation_note') }}</p>

                @if (session('status'))
                    <div class="mt-5 rounded-md border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm font-bold text-emerald-300">{{ session('status') }}</div>
                @endif

                @error('review')
                    <div class="mt-5 rounded-md border border-red-500/30 bg-red-500/10 p-4 text-sm font-bold text-red-300">{{ $message }}</div>
                @enderror

                @auth
                    @if ($canReview)
                        <form method="POST" action="{{ route('artwork.reviews.store', $artwork->slug) }}" class="mt-6 space-y-5">
                            @csrf
                            <div>
                                <label for="rating" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.reviews.rating') }}</label>
                                <select id="rating" name="rating" required class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm font-bold text-white focus:border-yellow-600 focus:ring-yellow-600">
                                    @for ($star = 5; $star >= 1; $star--)
                                        <option value="{{ $star }}" @selected((int) old('rating', 5) === $star)>{{ __('chapung.reviews.star_option', ['count' => $star]) }}</option>
                                    @endfor
                                </select>
                                @error('rating') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="review-title" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.reviews.form_title') }}</label>
                                <input id="review-title" name="title" value="{{ old('title') }}" maxlength="120" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm font-bold text-white focus:border-yellow-600 focus:ring-yellow-600" placeholder="{{ __('chapung.reviews.form_title_placeholder') }}">
                                @error('title') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="review-body" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.reviews.body') }}</label>
                                <textarea id="review-body" name="body" rows="5" required minlength="10" maxlength="2000" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm font-bold text-white focus:border-yellow-600 focus:ring-yellow-600" placeholder="{{ __('chapung.reviews.body_placeholder') }}">{{ old('body') }}</textarea>
                                @error('body') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-yellow-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">
                                <x-heroicon-o-star class="h-4 w-4" aria-hidden="true" />
                                {{ __('chapung.reviews.submit') }}
                            </button>
                        </form>
                    @elseif ($hasReviewed)
                        <div class="mt-6 rounded-md border border-yellow-600/30 bg-yellow-600/10 p-4 text-sm font-bold text-yellow-300">{{ __('chapung.reviews.already_submitted') }}</div>
                    @else
                        <div class="mt-6 rounded-md border border-zinc-800 bg-black/40 p-4 text-sm leading-6 text-zinc-400">{{ __('chapung.reviews.purchase_required') }}</div>
                    @endif
                @else
                    <div class="mt-6 rounded-md border border-zinc-800 bg-black/40 p-4 text-sm leading-6 text-zinc-400">
                        {{ __('chapung.reviews.login_required') }}
                        <a href="{{ route('login') }}" class="font-black text-yellow-500 hover:text-yellow-400">{{ __('chapung.marketplace.login') }}</a>
                    </div>
                @endauth
            </div>
        </div>
    </section>

    <section class="border-t border-zinc-800 px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl rounded-lg border border-zinc-800 bg-zinc-950 p-6">
            <div class="grid gap-6 md:grid-cols-[auto_1fr_auto] md:items-center">
                <div class="group h-24 w-24 overflow-hidden rounded-lg border border-zinc-800 bg-black">
                    @include('partials.public.image', [
                        'path' => $artist?->photo,
                        'alt' => $artist?->name ?: $artwork->artist_display_name,
                        'ratio' => 'aspect-square',
                        'label' => __('chapung.types.artist'),
                        'width' => 240,
                        'height' => 240,
                    ])
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.product_detail.artist_profile') }}</p>
                    <h2 class="mt-3 text-2xl font-black uppercase text-white">{{ $artist?->name ?: $artwork->artist_display_name ?: __('chapung.home.artist_fallback') }}</h2>
                    <p class="mt-2 text-sm leading-7 text-zinc-400">{{ $artistLocation ?: $artist?->origin_area ?: __('chapung.brand.region') }}</p>
                    @if ($artist?->specialization)
                        <p class="mt-2 text-sm font-bold text-zinc-300">{{ $artist->specialization }}</p>
                    @endif
                    @if ($artist?->bio)
                        <div class="mt-3 max-w-3xl text-sm leading-7 text-zinc-400" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                            {!! $artist->bio !!}
                        </div>
                    @endif
                    <div class="mt-4 flex flex-wrap gap-4 text-xs font-black uppercase tracking-[0.14em] text-zinc-500">
                        <span>{{ number_format((int) ($artist?->artworks_count ?? 0)) }} {{ __('chapung.types.artworks') }}</span>
                        <span>{{ number_format((int) ($artist?->photographies_count ?? 0)) }} {{ __('chapung.types.photography') }}</span>
                    </div>
                </div>
                @if ($artist)
                    <a href="{{ route('artists.show', $artist->slug) }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-yellow-600/70 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-yellow-500 hover:bg-yellow-600 hover:text-black">
                        <x-heroicon-o-user-circle class="h-4 w-4" aria-hidden="true" />
                        {{ __('chapung.product_detail.view_artist') }}
                    </a>
                @endif
            </div>
        </div>
    </section>

    @if ($relatedArtworks->count())
        <section class="border-t border-zinc-800 px-4 py-14 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.product_detail.related_label') }}</p>
                        <h2 class="mt-3 text-2xl font-black uppercase tracking-tight text-white">{{ __('chapung.pages.detail.related_artwork') }}</h2>
                    </div>
                    <a href="{{ route('artworks.index') }}" class="text-xs font-black uppercase tracking-[0.18em] text-yellow-500 hover:text-yellow-400">{{ __('chapung.home.view_all') }}</a>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 xl:grid-cols-4">
                    @foreach ($relatedArtworks as $related)
                        @include('partials.public.artwork-card', ['artwork' => $related])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
