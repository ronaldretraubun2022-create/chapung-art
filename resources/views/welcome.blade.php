@extends('layouts.public')

@php
    use App\Services\ImageUploadService;
    use Illuminate\Support\Collection;

    $homepageSections = $homepageSections instanceof Collection ? $homepageSections : collect();
    $featuredArtworks = $featuredArtworks instanceof Collection ? $featuredArtworks : collect();
    $featuredArtists = $featuredArtists instanceof Collection ? $featuredArtists : collect();
    $featuredCollections = $featuredCollections instanceof Collection ? $featuredCollections : collect();
    $featuredPhotographies = $featuredPhotographies instanceof Collection ? $featuredPhotographies : collect();
    $latestPosts = $latestPosts instanceof Collection ? $latestPosts : collect();

    $hero = $homepageSections->get('hero');
    $heroTitle = $hero?->title ?: __('chapung.home.hero_title');
    $heroSubtitle = $hero?->subtitle ?: __('chapung.home.hero_subtitle');
    $heroContent = $hero?->content ?: __('chapung.home.hero_content');
    $heroImage = ImageUploadService::normalizePath($hero?->image);
    $heroImageUrl = $heroImage ? asset('storage/'.$heroImage) : asset('images/og-image.jpg');
    $heroArtwork = $featuredArtworks->first();
    $heroArtworkImage = ImageUploadService::normalizePath($heroArtwork?->thumbnail);
    $totalFeaturedContent = $featuredArtworks->count() + $featuredPhotographies->count() + $featuredCollections->count();
@endphp

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('home', fallback: [
        'title' => $heroTitle.' | '.site_setting('site_name', 'Chapung Art'),
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
    <section class="relative min-h-[calc(100svh-7rem)] overflow-hidden border-b border-zinc-800 bg-black">
        <div class="absolute inset-0">
            <img src="{{ $heroImageUrl }}" alt="{{ ImageUploadService::altText($heroTitle, __('chapung.home.hero_title')) }}" width="1800" height="1200" class="h-full w-full object-cover opacity-55" loading="eager" decoding="async" fetchpriority="high">
            <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/72 to-black"></div>
        </div>

        <div class="relative mx-auto grid min-h-[calc(100svh-7rem)] max-w-7xl content-center gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1.1fr_.9fr] lg:px-8 lg:py-20">
            <article class="max-w-4xl">
                <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-500">{{ __('chapung.home.hero_region') }}</p>
                <h1 class="mt-5 text-5xl font-black uppercase leading-none text-white sm:text-7xl lg:text-8xl">{{ $heroTitle }}</h1>
                <p class="mt-6 max-w-3xl text-lg font-semibold leading-8 text-zinc-100 sm:text-2xl sm:leading-10">{{ $heroSubtitle }}</p>
                <p class="mt-5 max-w-2xl text-base leading-8 text-zinc-300">{{ $heroContent }}</p>

                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('gallery') }}" class="rounded-md bg-yellow-600 px-6 py-4 text-center text-xs font-black uppercase tracking-[0.18em] text-black transition hover:bg-yellow-500">{{ __('chapung.home.explore_artwork') }}</a>
                    <a href="{{ route('artists.index') }}" class="rounded-md border border-white/30 bg-black/30 px-6 py-4 text-center text-xs font-black uppercase tracking-[0.18em] text-white backdrop-blur transition hover:border-yellow-600 hover:text-yellow-500">{{ __('chapung.home.meet_artists') }}</a>
                </div>
            </article>

            <div class="grid gap-4 self-end lg:self-center">
                <div class="rounded-lg border border-white/15 bg-black/45 p-4 backdrop-blur">
                    @if ($heroArtwork)
                        @include('partials.public.image', [
                            'path' => $heroArtworkImage ?: $heroImage,
                            'alt' => $heroArtwork->title,
                            'label' => __('chapung.home.featured_label'),
                            'ratio' => 'aspect-[4/5]',
                            'width' => 960,
                            'height' => 1200,
                            'loading' => 'eager',
                            'fetchPriority' => 'high',
                        ])
                        <div class="pt-5">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-yellow-500">{{ __('chapung.home.featured_artwork') }}</p>
                            <h2 class="mt-2 text-2xl font-black uppercase text-white">{{ $heroArtwork->title }}</h2>
                            <p class="mt-2 text-sm text-zinc-300">{{ $heroArtwork->artist_display_name ?: 'Chapung Art' }}</p>
                        </div>
                    @else
                        @include('partials.public.image', [
                            'path' => $heroImage,
                            'alt' => $heroTitle,
                            'label' => __('chapung.home.hero_title'),
                            'ratio' => 'aspect-[4/5]',
                            'width' => 960,
                            'height' => 1200,
                            'loading' => 'eager',
                            'fetchPriority' => 'high',
                        ])
                    @endif
                </div>

                <dl class="grid grid-cols-3 gap-3">
                    <div class="rounded-lg border border-white/10 bg-black/45 p-4 backdrop-blur">
                        <dt class="text-[10px] font-black uppercase tracking-[0.18em] text-zinc-400">{{ __('chapung.home.artwork_count') }}</dt>
                        <dd class="mt-2 text-2xl font-black text-white">{{ $featuredArtworks->count() }}</dd>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-black/45 p-4 backdrop-blur">
                        <dt class="text-[10px] font-black uppercase tracking-[0.18em] text-zinc-400">{{ __('chapung.home.artists_count') }}</dt>
                        <dd class="mt-2 text-2xl font-black text-white">{{ $featuredArtists->count() }}</dd>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-black/45 p-4 backdrop-blur">
                        <dt class="text-[10px] font-black uppercase tracking-[0.18em] text-zinc-400">{{ __('chapung.home.stories_count') }}</dt>
                        <dd class="mt-2 text-2xl font-black text-white">{{ $latestPosts->count() }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    <section class="border-b border-zinc-800 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-6 lg:grid-cols-[.85fr_1.15fr] lg:items-end">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.3em] text-yellow-600">{{ __('chapung.home.featured_artwork') }}</p>
                    <h2 class="mt-3 text-3xl font-black uppercase text-white sm:text-5xl">{{ __('chapung.home.curated_works') }}</h2>
                </div>
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <p class="max-w-2xl text-sm leading-7 text-zinc-400">{{ __('chapung.home.featured_artwork_description') }}</p>
                    <a href="{{ route('gallery') }}" class="text-xs font-black uppercase tracking-[0.18em] text-yellow-500 hover:text-yellow-400">{{ __('chapung.home.view_all') }}</a>
                </div>
            </div>

            @if ($featuredArtworks->count())
                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredArtworks as $artwork)
                        @include('partials.public.artwork-card', ['artwork' => $artwork])
                    @endforeach
                </div>
            @else
                <div class="mt-8">@include('partials.public.empty-state', ['label' => __('chapung.home.artwork_count'), 'title' => __('chapung.home.empty_featured_artwork')])</div>
            @endif
        </div>
    </section>

    <section class="border-b border-zinc-800 bg-zinc-950 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.3em] text-yellow-600">{{ __('chapung.home.featured_artist') }}</p>
                    <h2 class="mt-3 text-3xl font-black uppercase text-white sm:text-5xl">{{ __('chapung.home.creative_profiles') }}</h2>
                </div>
                <a href="{{ route('artists.index') }}" class="text-xs font-black uppercase tracking-[0.18em] text-yellow-500 hover:text-yellow-400">{{ __('chapung.home.view_artists') }}</a>
            </div>

            @if ($featuredArtists->count())
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($featuredArtists as $artist)
                        <article class="group rounded-lg border border-zinc-800 bg-black p-4 transition hover:border-yellow-600/70">
                            <a href="{{ route('artists.show', $artist->slug) }}" class="block">
                                @include('partials.public.image', [
                                    'path' => $artist->photo,
                                    'alt' => $artist->name,
                                    'label' => __('chapung.types.artist'),
                                    'ratio' => 'aspect-[4/5]',
                                    'width' => 800,
                                    'height' => 1000,
                                ])
                                <div class="pt-5">
                                    <h3 class="text-xl font-black uppercase text-white">{{ $artist->name }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-zinc-400">{{ $artist->specialization ?: $artist->origin_area ?: __('chapung.home.artist_fallback') }}</p>
                                    <p class="mt-4 text-xs font-black uppercase tracking-[0.16em] text-yellow-600">{{ $artist->artworks_count }} artworks / {{ $artist->photographies_count }} photos</p>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                @include('partials.public.empty-state', ['label' => __('chapung.home.artists_count'), 'title' => __('chapung.home.empty_featured_artist')])
            @endif
        </div>
    </section>

    <section class="border-b border-zinc-800 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.3em] text-yellow-600">{{ __('chapung.home.collections') }}</p>
                    <h2 class="mt-3 text-3xl font-black uppercase text-white sm:text-5xl">{{ __('chapung.home.curated_themes') }}</h2>
                </div>
                <p class="max-w-xl text-sm leading-7 text-zinc-400 md:text-right">{{ __('chapung.home.collections_description') }}</p>
            </div>

            @if ($featuredCollections->count())
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($featuredCollections as $collection)
                        @php($collectionImage = $collection->banner_image ?: $collection->cover_image)
                        <article class="group rounded-lg border border-zinc-800 bg-zinc-950 p-3 transition hover:border-yellow-600/70">
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
                                    <h3 class="text-lg font-black uppercase text-white">{{ $collection->name }}</h3>
                                    <p class="mt-2 line-clamp-3 text-sm leading-6 text-zinc-400">{{ $collection->description ?: __('chapung.home.collection_fallback') }}</p>
                                    <p class="mt-4 text-xs font-black uppercase tracking-[0.16em] text-yellow-600">{{ $collection->artworks_count }} artworks / {{ $collection->photographies_count }} photos</p>
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

    <section class="border-b border-zinc-800 bg-zinc-950 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.3em] text-yellow-600">{{ __('chapung.home.photography') }}</p>
                    <h2 class="mt-3 text-3xl font-black uppercase text-white sm:text-5xl">{{ __('chapung.home.visual_archive') }}</h2>
                </div>
                <a href="{{ route('photography.index') }}" class="text-xs font-black uppercase tracking-[0.18em] text-yellow-500 hover:text-yellow-400">{{ __('chapung.home.view_photography') }}</a>
            </div>

            @if ($featuredPhotographies->count())
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredPhotographies as $photo)
                        @include('partials.public.photography-card', ['photo' => $photo])
                    @endforeach
                </div>
            @else
                @include('partials.public.empty-state', ['label' => __('chapung.home.photography'), 'title' => __('chapung.home.empty_featured_photography')])
            @endif
        </div>
    </section>

    <section class="border-b border-zinc-800 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.3em] text-yellow-600">{{ __('chapung.home.news') }}</p>
                    <h2 class="mt-3 text-3xl font-black uppercase text-white sm:text-5xl">{{ __('chapung.home.latest_stories') }}</h2>
                </div>
                <a href="{{ route('news.index') }}" class="text-xs font-black uppercase tracking-[0.18em] text-yellow-500 hover:text-yellow-400">{{ __('chapung.home.read_all') }}</a>
            </div>

            @if ($latestPosts->count())
                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($latestPosts as $post)
                        @include('partials.public.post-card', ['post' => $post])
                    @endforeach
                </div>
            @else
                @include('partials.public.empty-state', ['label' => __('chapung.home.news'), 'title' => __('chapung.home.empty_published_news')])
            @endif
        </div>
    </section>

    <section class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-8 border-y border-zinc-800 py-12 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.3em] text-yellow-600">Chapung Art</p>
                <h2 class="mt-3 max-w-4xl text-3xl font-black uppercase leading-tight text-white sm:text-5xl">{{ __('chapung.home.cta_title') }}</h2>
                <p class="mt-5 max-w-2xl text-sm leading-7 text-zinc-400">{{ __('chapung.home.cta_count', ['count' => $totalFeaturedContent]) }}</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                <a href="{{ route('gallery') }}" class="rounded-md bg-yellow-600 px-6 py-4 text-center text-xs font-black uppercase tracking-[0.18em] text-black transition hover:bg-yellow-500">{{ __('chapung.home.explore_gallery') }}</a>
                <a href="{{ route('contact') }}" class="rounded-md border border-zinc-700 px-6 py-4 text-center text-xs font-black uppercase tracking-[0.18em] text-white transition hover:border-yellow-600 hover:text-yellow-500">{{ __('chapung.home.contact') }}</a>
            </div>
        </div>
    </section>
@endsection
