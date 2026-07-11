@extends('layouts.public')

@php
    use App\Services\ImageUploadService;
    use Illuminate\Support\Collection;

    $homepageSections = $homepageSections instanceof Collection ? $homepageSections : collect();
    $featuredArtworks = $featuredArtworks instanceof Collection ? $featuredArtworks : collect();
    $latestArtworks = $latestArtworks instanceof Collection ? $latestArtworks : collect();
    $availableArtworks = $availableArtworks instanceof Collection ? $availableArtworks : collect();
    $artworkCategories = $artworkCategories instanceof Collection ? $artworkCategories : collect();
    $featuredArtists = $featuredArtists instanceof Collection ? $featuredArtists : collect();
    $featuredCollections = $featuredCollections instanceof Collection ? $featuredCollections : collect();
    $featuredPhotographies = $featuredPhotographies instanceof Collection ? $featuredPhotographies : collect();
    $digitalArtworks = $digitalArtworks instanceof Collection ? $digitalArtworks : collect();
    $soldArtworks = $soldArtworks instanceof Collection ? $soldArtworks : collect();
    $storyArtists = $storyArtists instanceof Collection ? $storyArtists : collect();
    $latestPosts = $latestPosts instanceof Collection ? $latestPosts : collect();

    $hero = $homepageSections->get('hero');
    $heroArtwork = $featuredArtworks->first() ?: $availableArtworks->first() ?: $latestArtworks->first();
    $heroTitle = $heroArtwork?->title ?: ($hero?->title ?: __('chapung.home.hero_title'));
    $heroSubtitle = $heroArtwork
        ? ($heroArtwork->artist_display_name ?: __('chapung.home.artist_fallback'))
        : ($hero?->subtitle ?: __('chapung.home.hero_subtitle'));
    $heroContent = $hero?->content ?: __('chapung.home.hero_content');
    $heroImage = ImageUploadService::normalizePath($heroArtwork?->thumbnail) ?: ImageUploadService::normalizePath($hero?->image);
    $heroImageUrl = $heroImage ? asset('storage/'.$heroImage) : ImageUploadService::fallbackUrl();
    $heroPrice = filled($heroArtwork?->price) ? (float) $heroArtwork->price : null;
    $heroAvailable = $heroArtwork && $heroArtwork->status === 'available' && (int) ($heroArtwork->stock ?? 0) > 0;
    $supportingUpdates = $latestPosts->take(3);
    $homepageItemCount = $latestArtworks->count() + $availableArtworks->count() + $featuredArtists->count() + $featuredCollections->count();
@endphp

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('home', fallback: [
        'title' => __('chapung.home.marketplace_title').' | '.site_setting('site_name', 'Chapung Art'),
        'description' => (string) str(strip_tags($heroContent))->limit(160),
        'og_image' => $heroImageUrl,
        'canonical_url' => route('home'),
        'schema_json' => [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => site_setting('site_name', 'Chapung Art'),
            'description' => (string) str(strip_tags($heroContent))->limit(160),
            'url' => route('home'),
        ],
    ])])
@endsection

@section('content')
    <section class="relative overflow-hidden border-b border-chapung-line bg-black">
        <div class="absolute inset-0">
            <img src="{{ $heroImageUrl }}" alt="{{ ImageUploadService::altText($heroTitle, __('chapung.types.artwork')) }}" width="1800" height="1200" class="h-full w-full object-cover opacity-55" loading="eager" decoding="async" fetchpriority="high" onerror="this.onerror=null;this.src='{{ ImageUploadService::fallbackUrl() }}'">
            <div class="absolute inset-0 bg-gradient-to-b from-black/45 via-black/78 to-black"></div>
        </div>

        <div class="ca-container relative grid min-h-[calc(100svh-7rem)] gap-8 py-12 sm:py-16 lg:grid-cols-[1.05fr_.95fr] lg:items-end lg:py-18">
            <div class="max-w-4xl pb-4 lg:pb-10">
                <p class="ca-eyebrow">{{ __('chapung.home.hero_marketplace') }}</p>
                <h1 class="mt-5 font-display text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl lg:text-7xl">{{ $heroTitle }}</h1>
                <p class="mt-5 max-w-2xl text-lg font-bold leading-8 text-zinc-100 sm:text-2xl sm:leading-9">{{ $heroSubtitle }}</p>
                <p class="mt-5 max-w-2xl text-sm leading-7 text-zinc-300 sm:text-base">{{ $heroContent }}</p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('artworks.index', ['stock' => 1]) }}" class="ca-button ca-button-primary">{{ __('chapung.home.available_artworks') }}</a>
                    <a href="{{ route('artists.index') }}" class="ca-button ca-button-secondary">{{ __('chapung.home.view_artists') }}</a>
                </div>
            </div>

            <aside class="ca-surface bg-black/55 p-3 backdrop-blur lg:mb-10">
                @if ($heroArtwork)
                    <a href="{{ route('artwork.show', $heroArtwork->slug) }}" class="block">
                        @include('partials.public.image', [
                            'path' => $heroArtwork->thumbnail,
                            'alt' => $heroArtwork->title,
                            'label' => __('chapung.types.artwork'),
                            'ratio' => 'aspect-[5/4] lg:aspect-[4/5]',
                            'width' => 980,
                            'height' => 1120,
                            'loading' => 'eager',
                            'fetchPriority' => 'high',
                        ])
                    </a>
                    <div class="p-3 sm:p-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="ca-badge ca-badge-gold">{{ __('chapung.home.official_badge') }}</span>
                            <span class="ca-badge ca-badge-muted">{{ $heroAvailable ? __('chapung.home.status_available') : __('chapung.home.status_sold') }}</span>
                        </div>
                        <h2 class="mt-4 text-2xl font-black uppercase leading-tight text-white">{{ $heroArtwork->title }}</h2>
                        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-zinc-400">
                            @if ($heroArtwork->artist?->slug)
                                <a href="{{ route('artists.show', $heroArtwork->artist->slug) }}" class="font-bold text-zinc-200 hover:text-chapung-gold">{{ $heroArtwork->artist_display_name }}</a>
                            @else
                                <span>{{ $heroArtwork->artist_display_name ?: __('chapung.home.artist_fallback') }}</span>
                            @endif
                            <span>{{ $heroArtwork->category?->name ?: __('chapung.types.artwork') }}</span>
                        </div>
                        <div class="mt-4 flex flex-wrap items-end justify-between gap-3">
                            <p class="text-2xl font-black text-chapung-gold">{{ $heroPrice ? 'Rp '.number_format($heroPrice, 0, ',', '.') : __('chapung.marketplace.by_request') }}</p>
                            <a href="{{ route('artwork.show', $heroArtwork->slug) }}" class="ca-button ca-button-secondary py-2.5">{{ __('chapung.home.view_artwork') }}</a>
                        </div>
                    </div>
                @else
                    <x-public.loading-skeleton :items="1" class="grid-cols-1" />
                    <div class="p-5">
                        @include('partials.public.empty-state', ['label' => __('chapung.types.artwork'), 'title' => __('chapung.home.empty_featured_artwork')])
                    </div>
                @endif
            </aside>
        </div>
    </section>

    <section class="ca-section">
        <div class="ca-container">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="ca-eyebrow">{{ __('chapung.home.latest_label') }}</p>
                    <h2 class="ca-heading-lg mt-3">{{ __('chapung.home.latest_artworks') }}</h2>
                </div>
                <p class="max-w-xl ca-copy md:text-right">{{ __('chapung.home.latest_artworks_desc') }}</p>
            </div>

            @if ($latestArtworks->count())
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($latestArtworks as $artwork)
                        @include('partials.public.artwork-card', ['artwork' => $artwork, 'badge' => __('chapung.home.official_badge')])
                    @endforeach
                </div>
            @else
                @include('partials.public.empty-state', ['label' => __('chapung.types.artwork'), 'title' => __('chapung.home.empty_latest_artwork')])
            @endif
        </div>
    </section>

    <section class="ca-section-soft">
        <div class="ca-container">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="ca-eyebrow">{{ __('chapung.home.available_label') }}</p>
                    <h2 class="ca-heading-lg mt-3">{{ __('chapung.home.available_artworks') }}</h2>
                </div>
                <a href="{{ route('artworks.index', ['stock' => 1]) }}" class="ca-button ca-button-ghost">{{ __('chapung.home.view_all') }}</a>
            </div>

            @if ($availableArtworks->count())
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($availableArtworks as $artwork)
                        @include('partials.public.artwork-card', ['artwork' => $artwork, 'badge' => __('chapung.home.status_available')])
                    @endforeach
                </div>
            @else
                @include('partials.public.empty-state', ['label' => __('chapung.home.status_available'), 'title' => __('chapung.home.empty_available_artwork')])
            @endif
        </div>
    </section>

    <section class="ca-section">
        <div class="ca-container">
            <div class="mb-8 grid gap-4 md:grid-cols-[.85fr_1.15fr] md:items-end">
                <div>
                    <p class="ca-eyebrow">{{ __('chapung.nav.categories') }}</p>
                    <h2 class="ca-heading-lg mt-3">{{ __('chapung.home.categories_title') }}</h2>
                </div>
                <p class="ca-copy md:text-right">{{ __('chapung.home.categories_desc') }}</p>
            </div>

            @if ($artworkCategories->count())
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($artworkCategories as $category)
                        <a href="{{ route('artworks.index', ['category' => $category->id]) }}" class="ca-surface-muted group p-5 transition hover:border-chapung-gold">
                            <span class="ca-badge ca-badge-muted">{{ number_format((int) $category->artworks_count) }} {{ __('chapung.types.artworks') }}</span>
                            <h3 class="mt-5 text-xl font-black uppercase text-white group-hover:text-chapung-gold">{{ $category->name }}</h3>
                            <p class="mt-3 line-clamp-2 text-sm leading-6 text-zinc-400">{{ $category->description ?: __('chapung.home.category_fallback') }}</p>
                        </a>
                    @endforeach
                </div>
            @else
                @include('partials.public.empty-state', ['label' => __('chapung.nav.categories'), 'title' => __('chapung.home.empty_category')])
            @endif
        </div>
    </section>

    <section class="ca-section-soft">
        <div class="ca-container">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="ca-eyebrow">{{ __('chapung.home.featured_artist') }}</p>
                    <h2 class="ca-heading-lg mt-3">{{ __('chapung.home.selected_artists') }}</h2>
                </div>
                <a href="{{ route('artists.index') }}" class="ca-button ca-button-ghost">{{ __('chapung.home.view_artists') }}</a>
            </div>

            @if ($featuredArtists->count())
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($featuredArtists as $artist)
                        <article class="ca-surface group overflow-hidden p-3 transition hover:border-chapung-gold">
                            <a href="{{ route('artists.show', $artist->slug) }}" class="block">
                                @include('partials.public.image', [
                                    'path' => $artist->photo,
                                    'alt' => $artist->name,
                                    'label' => __('chapung.types.artist'),
                                    'ratio' => 'aspect-[4/5]',
                                    'width' => 800,
                                    'height' => 1000,
                                ])
                                <div class="p-2 pt-4">
                                    <h3 class="text-xl font-black uppercase text-white group-hover:text-chapung-gold">{{ $artist->name }}</h3>
                                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-zinc-400">{{ $artist->specialization ?: $artist->origin_area ?: __('chapung.home.artist_fallback') }}</p>
                                    <p class="mt-4 text-xs font-black uppercase tracking-[0.16em] text-chapung-gold">{{ number_format((int) $artist->artworks_count) }} {{ __('chapung.types.artworks') }}</p>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                @include('partials.public.empty-state', ['label' => __('chapung.types.artist'), 'title' => __('chapung.home.empty_featured_artist')])
            @endif
        </div>
    </section>

    <section id="collections" class="ca-section">
        <div class="ca-container">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="ca-eyebrow">{{ __('chapung.home.collections') }}</p>
                    <h2 class="ca-heading-lg mt-3">{{ __('chapung.home.artwork_collections') }}</h2>
                </div>
                <p class="max-w-xl ca-copy md:text-right">{{ __('chapung.home.collections_description') }}</p>
            </div>

            @if ($featuredCollections->count())
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($featuredCollections as $collection)
                        @php($collectionImage = $collection->banner_image ?: $collection->cover_image)
                        <article class="ca-surface group overflow-hidden p-3 transition hover:border-chapung-gold">
                            <a href="{{ route('collections.show', $collection->slug) }}" class="block">
                                @include('partials.public.image', [
                                    'path' => $collectionImage,
                                    'alt' => $collection->name,
                                    'label' => __('chapung.types.collection'),
                                    'ratio' => 'aspect-[16/11]',
                                    'width' => 960,
                                    'height' => 660,
                                ])
                                <div class="p-2 pt-4">
                                    <h3 class="text-lg font-black uppercase text-white group-hover:text-chapung-gold">{{ $collection->name }}</h3>
                                    <p class="mt-2 line-clamp-3 text-sm leading-6 text-zinc-400">{{ $collection->description ?: __('chapung.home.collection_fallback') }}</p>
                                    <p class="mt-4 text-xs font-black uppercase tracking-[0.16em] text-chapung-gold">{{ number_format((int) $collection->artworks_count) }} {{ __('chapung.types.artworks') }}</p>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                @include('partials.public.empty-state', ['label' => __('chapung.home.collections'), 'title' => __('chapung.home.empty_featured_collection')])
            @endif
        </div>
    </section>

    <section class="ca-section-soft">
        <div class="ca-container">
            <div class="mb-8 grid gap-4 md:grid-cols-[.85fr_1.15fr] md:items-end">
                <div>
                    <p class="ca-eyebrow">{{ __('chapung.home.photography') }}</p>
                    <h2 class="ca-heading-lg mt-3">{{ __('chapung.home.photography_digital') }}</h2>
                </div>
                <p class="ca-copy md:text-right">{{ __('chapung.home.photography_digital_desc') }}</p>
            </div>

            @if ($featuredPhotographies->count() || $digitalArtworks->count())
                <div class="grid gap-6 xl:grid-cols-2">
                    <div>
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <h3 class="text-sm font-black uppercase tracking-[0.18em] text-white">{{ __('chapung.home.visual_archive') }}</h3>
                            <a href="{{ route('photography.index') }}" class="text-xs font-black uppercase tracking-[0.16em] text-chapung-gold hover:text-chapung-gold-soft">{{ __('chapung.home.view_photography') }}</a>
                        </div>
                        @if ($featuredPhotographies->count())
                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach ($featuredPhotographies->take(4) as $photo)
                                    @include('partials.public.photography-card', ['photo' => $photo])
                                @endforeach
                            </div>
                        @else
                            @include('partials.public.empty-state', ['label' => __('chapung.home.photography'), 'title' => __('chapung.home.empty_featured_photography')])
                        @endif
                    </div>

                    <div>
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <h3 class="text-sm font-black uppercase tracking-[0.18em] text-white">{{ __('chapung.home.digital_artwork') }}</h3>
                            <a href="{{ route('artworks.index', ['type' => 'digital']) }}" class="text-xs font-black uppercase tracking-[0.16em] text-chapung-gold hover:text-chapung-gold-soft">{{ __('chapung.home.view_all') }}</a>
                        </div>
                        @if ($digitalArtworks->count())
                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach ($digitalArtworks as $artwork)
                                    @include('partials.public.artwork-card', ['artwork' => $artwork, 'badge' => __('chapung.home.digital_artwork')])
                                @endforeach
                            </div>
                        @else
                            @include('partials.public.empty-state', ['label' => __('chapung.home.digital_artwork'), 'title' => __('chapung.home.empty_digital')])
                        @endif
                    </div>
                </div>
            @else
                @include('partials.public.empty-state', ['label' => __('chapung.home.photography_digital'), 'title' => __('chapung.home.empty_digital')])
            @endif
        </div>
    </section>

    <section class="ca-section">
        <div class="ca-container">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="ca-eyebrow">{{ __('chapung.home.sold_label') }}</p>
                    <h2 class="ca-heading-lg mt-3">{{ __('chapung.home.sold_artworks') }}</h2>
                </div>
                <p class="max-w-xl ca-copy md:text-right">{{ __('chapung.home.sold_artworks_desc') }}</p>
            </div>

            @if ($soldArtworks->count())
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($soldArtworks as $artwork)
                        @include('partials.public.artwork-card', ['artwork' => $artwork, 'badge' => __('chapung.home.status_sold')])
                    @endforeach
                </div>
            @else
                @include('partials.public.empty-state', ['label' => __('chapung.home.sold_label'), 'title' => __('chapung.home.empty_sold_artwork')])
            @endif
        </div>
    </section>

    <section class="ca-section-soft">
        <div class="ca-container">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="ca-eyebrow">{{ __('chapung.home.artist_stories') }}</p>
                    <h2 class="ca-heading-lg mt-3">{{ __('chapung.home.artist_stories_title') }}</h2>
                </div>
                <p class="max-w-xl ca-copy md:text-right">{{ __('chapung.home.artist_stories_desc') }}</p>
            </div>

            @if ($storyArtists->count())
                <div class="grid gap-4 lg:grid-cols-3">
                    @foreach ($storyArtists as $artist)
                        <article class="ca-surface grid gap-4 p-4 sm:grid-cols-[8rem_1fr]">
                            <a href="{{ route('artists.show', $artist->slug) }}" class="block">
                                @include('partials.public.image', [
                                    'path' => $artist->photo,
                                    'alt' => $artist->name,
                                    'label' => __('chapung.types.artist'),
                                    'ratio' => 'aspect-square sm:aspect-[4/5]',
                                    'width' => 420,
                                    'height' => 520,
                                ])
                            </a>
                            <div>
                                <h3 class="text-xl font-black uppercase text-white"><a href="{{ route('artists.show', $artist->slug) }}" class="hover:text-chapung-gold">{{ $artist->name }}</a></h3>
                                <p class="mt-2 text-xs font-black uppercase tracking-[0.16em] text-chapung-gold">{{ $artist->origin_area ?: $artist->city ?: __('chapung.brand.region') }}</p>
                                <p class="mt-3 line-clamp-4 text-sm leading-7 text-zinc-400">{{ str(strip_tags($artist->bio))->limit(190) }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                @include('partials.public.empty-state', ['label' => __('chapung.types.artist'), 'title' => __('chapung.home.empty_artist_story')])
            @endif
        </div>
    </section>

    <section class="ca-section">
        <div class="ca-container">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="ca-eyebrow">{{ __('chapung.home.news') }}</p>
                    <h2 class="ca-heading-lg mt-3">{{ __('chapung.home.supporting_updates') }}</h2>
                </div>
                <a href="{{ route('news.index') }}" class="ca-button ca-button-ghost">{{ __('chapung.home.read_all') }}</a>
            </div>

            @if ($supportingUpdates->count())
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach ($supportingUpdates as $post)
                        @include('partials.public.post-card', ['post' => $post])
                    @endforeach
                </div>
            @else
                @include('partials.public.empty-state', ['label' => __('chapung.home.news'), 'title' => __('chapung.home.empty_published_news')])
            @endif
        </div>
    </section>

    <section class="px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-6 border-y border-chapung-line py-10 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <p class="ca-eyebrow">Chapung Art</p>
                <h2 class="mt-3 max-w-4xl text-3xl font-black uppercase leading-tight text-white sm:text-5xl">{{ __('chapung.home.cta_title') }}</h2>
                <p class="mt-4 max-w-2xl ca-copy">{{ __('chapung.home.cta_count', ['count' => $homepageItemCount]) }}</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                <a href="{{ route('artworks.index') }}" class="ca-button ca-button-primary">{{ __('chapung.home.explore_gallery') }}</a>
                <a href="{{ route('contact') }}" class="ca-button ca-button-secondary">{{ __('chapung.home.contact') }}</a>
            </div>
        </div>
    </section>
@endsection
