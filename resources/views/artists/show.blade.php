@extends('layouts.public')

@php
    use App\Services\ImageUploadService;

    $coverImage = ImageUploadService::normalizePath($coverImage ?? null);
    $profilePhoto = ImageUploadService::normalizePath($artist->photo);
    $coverUrl = $coverImage ? asset('storage/'.$coverImage) : ImageUploadService::fallbackUrl();
    $profileUrl = $profilePhoto ? asset('storage/'.$profilePhoto) : null;
    $seoDescription = (string) str(strip_tags($artist->bio ?: $artist->specialization ?: 'Profil kreator Chapung Art Papua Selatan.'))->limit(160);
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
            <img src="{{ $coverUrl }}" alt="{{ ImageUploadService::altText($artist->name, 'Artist cover') }}" width="1600" height="900" class="h-full w-full object-cover opacity-45" loading="eager" decoding="async" fetchpriority="high">
            <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-black/70 to-black"></div>
        </div>

        <div class="relative mx-auto grid min-h-[34rem] max-w-7xl content-end gap-8 px-4 pb-10 pt-28 sm:px-6 lg:grid-cols-[18rem_1fr] lg:px-8 lg:pb-14 lg:pt-36">
            <div class="w-44 overflow-hidden rounded-lg border border-zinc-700 bg-zinc-950 p-2 shadow-2xl shadow-black/40 sm:w-56 lg:w-full">
                @if ($profileUrl)
                    <img src="{{ $profileUrl }}" alt="{{ ImageUploadService::altText($artist->name, 'Artist portrait') }}" width="800" height="1000" class="aspect-[4/5] w-full rounded-md object-cover" loading="eager" decoding="async">
                @else
                    <div class="grid aspect-[4/5] w-full place-items-center rounded-md bg-[radial-gradient(circle_at_top,rgba(202,138,4,.22),transparent_20rem),#101010] text-5xl font-black text-yellow-600">{{ str($artist->name)->substr(0, 2)->upper() }}</div>
                @endif
            </div>

            <article class="max-w-4xl self-end">
                <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-500">{{ $artist->origin_area ?: 'Chapung Art Artist' }}</p>
                <h1 class="mt-4 text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl lg:text-7xl">{{ $artist->name }}</h1>
                <p class="mt-5 max-w-3xl text-base leading-8 text-zinc-200 sm:text-xl">{{ $artist->specialization ?: 'Seni rupa, fotografi budaya, dan cerita visual Papua Selatan.' }}</p>

                <dl class="mt-8 grid gap-3 text-sm text-zinc-300 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="border-l border-yellow-600/70 pl-4">
                        <dt class="text-xs uppercase tracking-[0.16em] text-zinc-500">Artwork</dt>
                        <dd class="mt-1 text-lg font-black text-white">{{ number_format($artist->artworks_count) }}</dd>
                    </div>
                    <div class="border-l border-yellow-600/70 pl-4">
                        <dt class="text-xs uppercase tracking-[0.16em] text-zinc-500">Photography</dt>
                        <dd class="mt-1 text-lg font-black text-white">{{ number_format($artist->photographies_count) }}</dd>
                    </div>
                    <div class="border-l border-yellow-600/70 pl-4">
                        <dt class="text-xs uppercase tracking-[0.16em] text-zinc-500">City</dt>
                        <dd class="mt-1 font-bold text-white">{{ $artist->city ?: '-' }}</dd>
                    </div>
                    <div class="border-l border-yellow-600/70 pl-4">
                        <dt class="text-xs uppercase tracking-[0.16em] text-zinc-500">Province</dt>
                        <dd class="mt-1 font-bold text-white">{{ $artist->province ?: '-' }}</dd>
                    </div>
                </dl>
            </article>
        </div>
    </section>

    <section class="border-b border-zinc-800 px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1fr_22rem]">
            <article>
                <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-600">Biography</p>
                <div class="prose prose-invert mt-5 max-w-none prose-p:leading-8 prose-a:text-yellow-500">
                    {!! $artist->bio ?: '<p>Bio artist belum tersedia.</p>' !!}
                </div>
            </article>

            <aside class="space-y-6 border-t border-zinc-800 pt-8 lg:border-l lg:border-t-0 lg:pl-8 lg:pt-0">
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-zinc-500">Origin</p>
                    <p class="mt-2 font-bold text-white">{{ $artist->origin_area ?: $artist->city ?: 'Papua Selatan' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-zinc-500">Education</p>
                    <p class="mt-2 text-zinc-300">{{ $artist->education ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-zinc-500">Website</p>
                    @if ($artist->website)
                        <a href="{{ $artist->website }}" rel="nofollow noopener" target="_blank" class="mt-2 inline-flex text-sm font-bold text-yellow-500 hover:text-yellow-400">{{ $artist->website }}</a>
                    @else
                        <p class="mt-2 text-zinc-300">-</p>
                    @endif
                </div>
            </aside>
        </div>
    </section>

    <section class="px-4 py-14 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-16">
            <div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-600">Selected Work</p>
                        <h2 class="mt-2 text-2xl font-black uppercase tracking-tight text-white">Artwork</h2>
                    </div>
                    <p class="text-sm text-zinc-500">{{ number_format($artworks->total()) }} karya</p>
                </div>

                @if ($artworks->count())
                    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($artworks as $artwork)
                            @include('partials.public.artwork-card', ['artwork' => $artwork])
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $artworks->links() }}</div>
                @else
                    <div class="mt-6">@include('partials.public.empty-state', ['label' => 'Artwork', 'title' => 'Belum ada artwork'])</div>
                @endif
            </div>

            <div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-600">Curated Series</p>
                        <h2 class="mt-2 text-2xl font-black uppercase tracking-tight text-white">Collections</h2>
                    </div>
                    <p class="text-sm text-zinc-500">{{ number_format($collections->total()) }} collection</p>
                </div>

                @if ($collections->count())
                    <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($collections as $collection)
                            @php($collectionImage = $collection->banner_image ?: $collection->cover_image)
                            <article class="group rounded-lg border border-zinc-800 bg-zinc-950 p-3 transition hover:border-yellow-600/70">
                                <a href="{{ route('collections.show', $collection->slug) }}" class="block">
                                    @include('partials.public.image', [
                                        'path' => $collectionImage,
                                        'alt' => $collection->name,
                                        'label' => 'Collection',
                                        'ratio' => 'aspect-[16/10]',
                                        'width' => 960,
                                        'height' => 600,
                                    ])
                                    <div class="p-2 pt-4">
                                        <h3 class="text-lg font-black uppercase tracking-tight text-white">{{ $collection->name }}</h3>
                                        <p class="mt-2 line-clamp-2 text-sm leading-6 text-zinc-400">{{ $collection->description ?: 'Kurasi karya Chapung Art.' }}</p>
                                        <p class="mt-4 text-xs font-black uppercase tracking-[0.16em] text-yellow-600">{{ $collection->artworks_count }} artwork</p>
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $collections->links() }}</div>
                @else
                    <div class="mt-6">@include('partials.public.empty-state', ['label' => 'Collections', 'title' => 'Belum ada collection untuk artist ini'])</div>
                @endif
            </div>

            <div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.28em] text-yellow-600">Visual Archive</p>
                        <h2 class="mt-2 text-2xl font-black uppercase tracking-tight text-white">Photography</h2>
                    </div>
                    <p class="text-sm text-zinc-500">{{ number_format($photographies->total()) }} foto</p>
                </div>

                @if ($photographies->count())
                    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($photographies as $photo)
                            @include('partials.public.photography-card', ['photo' => $photo])
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $photographies->links() }}</div>
                @else
                    <div class="mt-6">@include('partials.public.empty-state', ['label' => 'Photography', 'title' => 'Belum ada photography'])</div>
                @endif
            </div>
        </div>
    </section>
@endsection
