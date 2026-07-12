@extends('layouts.public')

@php
    use App\Services\ImageUploadService;
    use Illuminate\Support\Facades\Storage;

    $coverImage = ImageUploadService::normalizePath($coverImage ?? null);
    $profilePhoto = ImageUploadService::normalizePath($artist->photo);
    $coverUrl = $coverImage ? asset('storage/'.$coverImage) : ImageUploadService::fallbackUrl();
    $profileUrl = ($profilePhoto && Storage::disk('public')->exists($profilePhoto)) ? asset('storage/'.$profilePhoto) : null;
    $seoDescription = (string) str(strip_tags($artist->bio ?: $artist->specialization ?: __('chapung.artist_store.bio_empty')))->limit(160);
    $location = collect([$artist->origin_area, $artist->city, $artist->province, $artist->country])->filter()->unique()->implode(', ') ?: __('chapung.artist_store.location_fallback');
    $rating = (float) ($storefrontStats['rating'] ?? 0);
    $reviewCount = (int) ($storefrontStats['review_count'] ?? 0);
    $whatsapp = preg_replace('/\D+/', '', site_setting('whatsapp', (string) config('chapung.contact_whatsapp'))) ?: (string) config('chapung.contact_whatsapp');
    $message = rawurlencode('Halo Chapung Art, saya ingin bertanya tentang toko seniman '.$artist->name.' - '.route('artists.show', $artist->slug));
    $socialLinks = collect([
        ['label' => 'Instagram', 'url' => $artist->instagram],
        ['label' => 'Facebook', 'url' => $artist->facebook],
        ['label' => 'Website', 'url' => $artist->website],
    ])->filter(fn ($link) => filled($link['url']))
        ->map(function (array $link): array {
            $url = trim((string) $link['url']);

            if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
                $url = 'https://'.ltrim($url, '/');
            }

            return [...$link, 'url' => $url];
        });
@endphp

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('artists.show', fallback: [
        'title' => $artist->name.' | '.site_setting('site_name', 'Chapung Art'),
        'description' => $seoDescription,
        'og_image' => $coverUrl,
        'canonical_url' => route('artists.show', $artist->slug),
        'schema_json' => [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $artist->name,
            'description' => $seoDescription,
            'image' => $profileUrl ?: $coverUrl,
            'url' => route('artists.show', $artist->slug),
            'homeLocation' => array_filter([
                '@type' => 'Place',
                'name' => $artist->origin_area ?: $artist->city ?: $artist->province,
                'address' => array_filter([
                    '@type' => 'PostalAddress',
                    'addressLocality' => $artist->city,
                    'addressRegion' => $artist->province,
                    'addressCountry' => $artist->country,
                ]),
            ]),
        ],
    ])])
@endsection

@section('content')
    <section class="relative border-b border-zinc-800 bg-black">
        <div class="absolute inset-0">
            <img src="{{ $coverUrl }}" alt="{{ ImageUploadService::altText($artist->name, __('chapung.artist_store.label')) }}" width="1600" height="900" class="h-full w-full object-cover opacity-50" loading="eager" decoding="async" fetchpriority="high">
            <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/70 to-black"></div>
        </div>

        <div class="relative mx-auto grid min-h-[38rem] max-w-7xl content-end gap-8 px-4 pb-10 pt-28 sm:px-6 lg:grid-cols-[18rem_1fr] lg:px-8 lg:pb-14 lg:pt-36">
            <div class="w-44 overflow-hidden rounded-lg border border-zinc-700 bg-zinc-950 p-2 shadow-2xl shadow-black/40 sm:w-56 lg:w-full">
                @include('partials.public.artist-photo', [
                    'path' => $artist->photo,
                    'alt' => $artist->name,
                    'ratio' => 'aspect-[4/5] w-full',
                    'width' => 800,
                    'height' => 1000,
                ])
            </div>

            <article class="max-w-5xl self-end">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-yellow-600 px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-black">{{ __('chapung.artist_store.label') }}</span>
                    <span class="rounded-full border border-zinc-700 bg-black/40 px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-zinc-200">{{ __('chapung.artist_store.verified') }}</span>
                </div>
                <h1 class="mt-5 text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl lg:text-7xl">{{ $artist->name }}</h1>
                <p class="mt-4 max-w-3xl text-base leading-8 text-zinc-200 sm:text-xl">{{ $artist->specialization ?: __('chapung.home.artist_fallback') }}</p>
                <p class="mt-3 inline-flex items-center gap-2 text-sm font-bold text-zinc-300">
                    <x-heroicon-o-map-pin class="h-4 w-4 text-yellow-600" aria-hidden="true" />
                    {{ $location }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="https://wa.me/{{ $whatsapp }}?text={{ $message }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-md bg-yellow-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">
                        <x-heroicon-o-chat-bubble-left-right class="h-4 w-4" aria-hidden="true" />
                        {{ __('chapung.artist_store.contact') }}
                    </a>
                    <button type="button" class="inline-flex items-center justify-center gap-2 rounded-md border border-zinc-700 bg-black/40 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-zinc-200 hover:border-yellow-600 hover:text-yellow-500" aria-pressed="false">
                        <x-heroicon-o-heart class="h-4 w-4" aria-hidden="true" />
                        {{ __('chapung.artist_store.follow') }}
                    </button>
                    @if ($artist->website)
                        <a href="{{ $socialLinks->firstWhere('label', 'Website')['url'] ?? $artist->website }}" target="_blank" rel="nofollow noopener" class="inline-flex items-center justify-center gap-2 rounded-md border border-zinc-700 bg-black/40 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-zinc-200 hover:border-yellow-600 hover:text-yellow-500">
                            <x-heroicon-o-globe-alt class="h-4 w-4" aria-hidden="true" />
                            {{ __('chapung.artist_store.visit_website') }}
                        </a>
                    @endif
                </div>
            </article>
        </div>
    </section>

    <section class="border-b border-zinc-800 bg-zinc-950 px-4 py-6 sm:px-6 lg:px-8">
        <dl class="mx-auto grid max-w-7xl gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div class="rounded-lg border border-zinc-800 bg-black/40 p-4">
                <dt class="text-[10px] font-black uppercase tracking-[0.16em] text-zinc-500">{{ __('chapung.artist_store.available_artworks') }}</dt>
                <dd class="mt-2 text-2xl font-black text-white">{{ number_format((int) $storefrontStats['available_artworks']) }}</dd>
            </div>
            <div class="rounded-lg border border-zinc-800 bg-black/40 p-4">
                <dt class="text-[10px] font-black uppercase tracking-[0.16em] text-zinc-500">{{ __('chapung.artist_store.collector_rating') }}</dt>
                <dd class="mt-2 text-2xl font-black text-yellow-500">{{ number_format($rating, 1) }}</dd>
            </div>
            <div class="rounded-lg border border-zinc-800 bg-black/40 p-4">
                <dt class="text-[10px] font-black uppercase tracking-[0.16em] text-zinc-500">{{ __('chapung.artist_store.collector_reviews') }}</dt>
                <dd class="mt-2 text-2xl font-black text-white">{{ number_format($reviewCount) }}</dd>
            </div>
            <div class="rounded-lg border border-zinc-800 bg-black/40 p-4">
                <dt class="text-[10px] font-black uppercase tracking-[0.16em] text-zinc-500">{{ __('chapung.artist_store.views') }}</dt>
                <dd class="mt-2 text-2xl font-black text-white">{{ number_format((int) $storefrontStats['views_total']) }}</dd>
            </div>
            <div class="rounded-lg border border-zinc-800 bg-black/40 p-4">
                <dt class="text-[10px] font-black uppercase tracking-[0.16em] text-zinc-500">{{ __('chapung.artist_store.likes') }}</dt>
                <dd class="mt-2 text-2xl font-black text-white">{{ number_format((int) $storefrontStats['likes_total']) }}</dd>
            </div>
            <div class="rounded-lg border border-zinc-800 bg-black/40 p-4">
                <dt class="text-[10px] font-black uppercase tracking-[0.16em] text-zinc-500">{{ __('chapung.artist_store.average_price') }}</dt>
                <dd class="mt-2 text-xl font-black text-yellow-500">Rp {{ number_format((float) $storefrontStats['average_price'], 0, ',', '.') }}</dd>
            </div>
        </dl>
    </section>

    <section class="border-b border-zinc-800 px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[1fr_24rem]">
            <article class="rounded-lg border border-zinc-800 bg-zinc-950 p-6">
                <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-600">{{ __('chapung.artist_store.bio') }}</p>
                <div class="prose prose-invert mt-5 max-w-none prose-p:leading-8 prose-a:text-yellow-500">
                    {!! $artist->bio ?: '<p>'.e(__('chapung.artist_store.bio_empty')).'</p>' !!}
                </div>
            </article>

            <aside class="rounded-lg border border-zinc-800 bg-zinc-950 p-6">
                <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-600">{{ __('chapung.artist_store.store_info') }}</p>
                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500">{{ __('chapung.artist_store.origin') }}</dt>
                        <dd class="mt-1 font-bold text-white">{{ $location }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500">{{ __('chapung.artist_store.specialization') }}</dt>
                        <dd class="mt-1 text-zinc-300">{{ $artist->specialization ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500">{{ __('chapung.artist_store.education') }}</dt>
                        <dd class="mt-1 text-zinc-300">{{ $artist->education ?: '-' }}</dd>
                    </div>
                    @if ($artist->achievements)
                        <div>
                            <dt class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500">{{ __('chapung.artist_store.achievements') }}</dt>
                            <dd class="mt-1 whitespace-pre-line text-zinc-300">{{ $artist->achievements }}</dd>
                        </div>
                    @endif
                    @if ($artist->exhibitions)
                        <div>
                            <dt class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500">{{ __('chapung.artist_store.exhibitions') }}</dt>
                            <dd class="mt-1 whitespace-pre-line text-zinc-300">{{ $artist->exhibitions }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs font-black uppercase tracking-[0.16em] text-zinc-500">{{ __('chapung.artist_store.member_since') }}</dt>
                        <dd class="mt-1 text-zinc-300">{{ optional($artist->created_at)->format('Y') ?: '-' }}</dd>
                    </div>
                </dl>

                @if ($socialLinks->isNotEmpty())
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach ($socialLinks as $link)
                            <a href="{{ $link['url'] }}" target="_blank" rel="nofollow noopener" class="rounded-md border border-zinc-800 px-3 py-2 text-xs font-black uppercase tracking-[0.14em] text-zinc-300 hover:border-yellow-600 hover:text-yellow-500">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>
    </section>

    <section class="border-b border-zinc-800 px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-600">{{ __('chapung.artist_store.reviews_title') }}</p>
                    <h2 class="mt-2 text-2xl font-black uppercase tracking-tight text-white">{{ number_format($rating, 1) }} / 5.0</h2>
                </div>
                <p class="max-w-2xl text-sm leading-6 text-zinc-500">{{ __('chapung.artist_store.reviews_description') }}</p>
            </div>

            @if ($reviewSignals->count())
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    @foreach ($reviewSignals as $signal)
                        @php($reviewArtwork = $signal->artwork)
                        <article class="rounded-lg border border-zinc-800 bg-zinc-950 p-5">
                            <div class="flex items-center gap-1 text-yellow-500" aria-hidden="true">
                                @for ($star = 1; $star <= 5; $star++)
                                    <x-heroicon-s-star class="h-4 w-4 {{ $star <= $signal->rating ? 'text-yellow-500' : 'text-zinc-700' }}" />
                                @endfor
                            </div>
                            <h3 class="mt-4 text-lg font-black uppercase tracking-tight text-white">{{ $signal->title ?: ($reviewArtwork?->title ?? __('chapung.reviews.default_title')) }}</h3>
                            <p class="mt-2 text-sm leading-6 text-zinc-400">{{ str($signal->body)->limit(150) }}</p>
                            @if ($reviewArtwork)
                                <a href="{{ route('artwork.show', $reviewArtwork->slug) }}" class="mt-4 inline-flex text-xs font-black uppercase tracking-[0.16em] text-yellow-500 hover:text-yellow-400">{{ __('chapung.artist_store.view_store_item') }}</a>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <div class="mt-6">@include('partials.public.empty-state', ['label' => __('chapung.artist_store.collector_reviews'), 'title' => __('chapung.artist_store.review_empty')])</div>
            @endif
        </div>
    </section>

    <section class="px-4 py-14 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-16">
            <div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-600">{{ __('chapung.artist_store.selected_work') }}</p>
                        <h2 class="mt-2 text-2xl font-black uppercase tracking-tight text-white">{{ __('chapung.artist_store.all_artworks') }}</h2>
                    </div>
                    <p class="text-sm text-zinc-500">{{ __('chapung.artist_store.artwork_count', ['count' => number_format($artworks->total())]) }}</p>
                </div>

                @if ($artworks->count())
                    <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 xl:grid-cols-4">
                        @foreach ($artworks as $artwork)
                            @include('partials.public.artwork-card', ['artwork' => $artwork])
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $artworks->links() }}</div>
                @else
                    <div class="mt-6">@include('partials.public.empty-state', ['label' => __('chapung.types.artwork'), 'title' => __('chapung.common.empty_title')])</div>
                @endif
            </div>

            <div class="grid gap-8 lg:grid-cols-2">
                <section class="rounded-lg border border-zinc-800 bg-zinc-950/55 p-4 sm:p-5">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-600">{{ __('chapung.home.status_available') }}</p>
                            <h2 class="mt-2 text-2xl font-black uppercase tracking-tight text-white">{{ __('chapung.home.available_artworks') }}</h2>
                        </div>
                        <span class="text-sm text-zinc-500">{{ number_format($availableArtworksPreview->count()) }}</span>
                    </div>

                    @if ($availableArtworksPreview->count())
                        <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4">
                            @foreach ($availableArtworksPreview as $artwork)
                                @include('partials.public.artwork-card', ['artwork' => $artwork, 'badge' => __('chapung.home.status_available')])
                            @endforeach
                        </div>
                    @else
                        <div class="mt-6">@include('partials.public.empty-state', ['label' => __('chapung.home.status_available'), 'title' => __('chapung.home.empty_available_artwork')])</div>
                    @endif
                </section>

                <section class="rounded-lg border border-zinc-800 bg-zinc-950/55 p-4 sm:p-5">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-600">{{ __('chapung.home.status_sold') }}</p>
                            <h2 class="mt-2 text-2xl font-black uppercase tracking-tight text-white">{{ __('chapung.home.sold_artworks') }}</h2>
                        </div>
                        <span class="text-sm text-zinc-500">{{ number_format($soldArtworksPreview->count()) }}</span>
                    </div>

                    @if ($soldArtworksPreview->count())
                        <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4">
                            @foreach ($soldArtworksPreview as $artwork)
                                @include('partials.public.artwork-card', ['artwork' => $artwork, 'badge' => __('chapung.home.status_sold')])
                            @endforeach
                        </div>
                    @else
                        <div class="mt-6">@include('partials.public.empty-state', ['label' => __('chapung.home.status_sold'), 'title' => __('chapung.home.empty_sold_artwork')])</div>
                    @endif
                </section>
            </div>

            <div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-600">{{ __('chapung.artist_store.curated_series') }}</p>
                        <h2 class="mt-2 text-2xl font-black uppercase tracking-tight text-white">{{ __('chapung.types.collections') }}</h2>
                    </div>
                    <p class="text-sm text-zinc-500">{{ __('chapung.artist_store.collection_count', ['count' => number_format($collections->total())]) }}</p>
                </div>

                @if ($collections->count())
                    <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($collections as $collection)
                            <article class="group rounded-lg border border-zinc-800 bg-zinc-950 p-3 transition hover:border-yellow-600/70">
                                <a href="{{ route('collections.show', $collection->slug) }}" class="block">
                                    @include('partials.public.collection-cover', [
                                        'collection' => $collection,
                                        'ratio' => 'aspect-[16/10]',
                                        'width' => 960,
                                        'height' => 600,
                                    ])
                                    <div class="p-2 pt-4">
                                        <h3 class="text-lg font-black uppercase tracking-tight text-white">{{ $collection->name }}</h3>
                                        <p class="mt-2 text-sm leading-6 text-zinc-400" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $collection->description ?: __('chapung.home.collection_fallback') }}</p>
                                        <p class="mt-4 text-xs font-black uppercase tracking-[0.16em] text-yellow-600">{{ __('chapung.artist_store.artwork_count', ['count' => number_format($collection->artworks_count)]) }}</p>
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $collections->links() }}</div>
                @else
                    <div class="mt-6">@include('partials.public.empty-state', ['label' => __('chapung.types.collections'), 'title' => __('chapung.common.empty_title')])</div>
                @endif
            </div>

            <div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-600">{{ __('chapung.artist_store.visual_archive') }}</p>
                        <h2 class="mt-2 text-2xl font-black uppercase tracking-tight text-white">{{ __('chapung.types.photography') }}</h2>
                    </div>
                    <p class="text-sm text-zinc-500">{{ __('chapung.artist_store.photo_count', ['count' => number_format($photographies->total())]) }}</p>
                </div>

                @if ($photographies->count())
                    <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 xl:grid-cols-4">
                        @foreach ($photographies as $photo)
                            @include('partials.public.photography-card', ['photo' => $photo])
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $photographies->links() }}</div>
                @else
                    <div class="mt-6">@include('partials.public.empty-state', ['label' => __('chapung.types.photography'), 'title' => __('chapung.common.empty_title')])</div>
                @endif
            </div>
        </div>
    </section>
@endsection
