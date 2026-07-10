@extends('layouts.public')

@php
    $homepageSections = $homepageSections ?? collect();
    $hero = $homepageSections->get('hero');
    $heroTitle = $hero?->title ?: site_setting('site_name', 'Chapung Art');
    $heroSubtitle = $hero?->subtitle ?: 'Premium gallery for Papua art, culture, and visual archives.';
    $heroContent = $hero?->content ?: site_setting('site_description', 'Galeri seni, fotografi budaya, dan media kreatif Papua Selatan.');
    $heroImage = $hero?->image;
@endphp

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('home', fallback: [
        'title' => $heroTitle.' | '.site_setting('site_name', 'Chapung Art'),
        'description' => $heroContent,
        'og_image' => $heroImage ? asset('storage/'.$heroImage) : asset('images/og-image.jpg'),
        'canonical_url' => route('home'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-cover bg-center px-4 py-20 sm:px-6 lg:px-8 lg:py-28" @if ($heroImage) style="background-image: linear-gradient(180deg, rgba(0,0,0,.68), rgba(0,0,0,.95)), url('{{ asset('storage/'.$heroImage) }}');" @else style="background-image: radial-gradient(circle at top right, rgba(202,138,4,.24), transparent 32rem), linear-gradient(180deg,#050505,#000);" @endif>
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.34em] text-yellow-600">Merauke / Papua Selatan</p>
            <h1 class="mt-5 max-w-5xl text-5xl font-black uppercase leading-none tracking-tight text-white sm:text-7xl">{{ $heroTitle }}</h1>
            <p class="mt-6 max-w-3xl text-xl font-semibold leading-9 text-zinc-200">{{ $heroSubtitle }}</p>
            <p class="mt-5 max-w-3xl text-base leading-8 text-zinc-300">{{ $heroContent }}</p>
            <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('gallery') }}" class="rounded-md bg-yellow-600 px-6 py-4 text-center text-xs font-black uppercase tracking-[0.2em] text-black hover:bg-yellow-500">Explore Artwork</a>
                <a href="{{ route('photography.index') }}" class="rounded-md border border-yellow-600/60 px-6 py-4 text-center text-xs font-black uppercase tracking-[0.2em] text-yellow-500 hover:bg-yellow-600 hover:text-black">View Photography</a>
                <a href="{{ route('news.index') }}" class="rounded-md border border-zinc-700 px-6 py-4 text-center text-xs font-black uppercase tracking-[0.2em] text-white hover:border-yellow-600 hover:text-yellow-500">Read News</a>
            </div>
        </div>
    </section>

    <section class="border-b border-zinc-800 px-4 py-14 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-6 md:grid-cols-4">
            @foreach ([['Artwork', route('gallery')], ['Photography', route('photography.index')], ['Artists', route('artists.index')], ['News', route('news.index')]] as [$label, $url])
                <a href="{{ $url }}" class="rounded-lg border border-zinc-800 bg-zinc-950 p-6 hover:border-yellow-600/70">
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Explore</p>
                    <h2 class="mt-3 text-2xl font-black uppercase tracking-tight text-white">{{ $label }}</h2>
                </a>
            @endforeach
        </div>
    </section>

    <section class="border-b border-zinc-800 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Featured Artwork</p>
                    <h2 class="mt-3 text-3xl font-black uppercase tracking-tight text-white sm:text-5xl">Curated Works</h2>
                </div>
                <a href="{{ route('gallery') }}" class="text-xs font-black uppercase tracking-[0.2em] text-yellow-500 hover:text-yellow-400">View all</a>
            </div>
            @if (collect($featuredArtworks ?? [])->count())
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredArtworks as $artwork)
                        @include('partials.public.artwork-card', ['artwork' => $artwork])
                    @endforeach
                </div>
            @else
                @include('partials.public.empty-state', ['label' => 'Artwork', 'title' => 'Belum ada featured artwork'])
            @endif
        </div>
    </section>

    <section class="border-b border-zinc-800 bg-zinc-950 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Photography</p>
                    <h2 class="mt-3 text-3xl font-black uppercase tracking-tight text-white sm:text-5xl">Visual Archive</h2>
                </div>
                <a href="{{ route('photography.index') }}" class="text-xs font-black uppercase tracking-[0.2em] text-yellow-500 hover:text-yellow-400">View all</a>
            </div>
            @if (collect($featuredPhotographies ?? [])->count())
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredPhotographies as $photo)
                        @include('partials.public.photography-card', ['photo' => $photo])
                    @endforeach
                </div>
            @else
                @include('partials.public.empty-state', ['label' => 'Photography', 'title' => 'Belum ada featured photography'])
            @endif
        </div>
    </section>

    <section class="border-b border-zinc-800 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-2">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Artists</p>
                <h2 class="mt-3 text-3xl font-black uppercase tracking-tight text-white sm:text-5xl">Creative Profiles</h2>
                <div class="mt-7 grid gap-4">
                    @forelse ($featuredArtists ?? [] as $artist)
                        <a href="{{ route('artists.show', $artist->slug) }}" class="rounded-lg border border-zinc-800 bg-zinc-950 p-5 hover:border-yellow-600/70">
                            <h3 class="font-black uppercase tracking-tight text-white">{{ $artist->name }}</h3>
                            <p class="mt-2 text-sm text-zinc-400">{{ $artist->specialization ?: $artist->origin_area }}</p>
                        </a>
                    @empty
                        @include('partials.public.empty-state', ['label' => 'Artists', 'title' => 'Belum ada featured artist'])
                    @endforelse
                </div>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Collections</p>
                <h2 class="mt-3 text-3xl font-black uppercase tracking-tight text-white sm:text-5xl">Curated Themes</h2>
                <div class="mt-7 grid gap-4">
                    @forelse ($featuredCollections ?? [] as $collection)
                        <a href="{{ route('collections.show', $collection->slug) }}" class="rounded-lg border border-zinc-800 bg-zinc-950 p-5 hover:border-yellow-600/70">
                            <h3 class="font-black uppercase tracking-tight text-white">{{ $collection->name }}</h3>
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-zinc-400">{{ $collection->description }}</p>
                        </a>
                    @empty
                        @include('partials.public.empty-state', ['label' => 'Collections', 'title' => 'Belum ada featured collection'])
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">News</p>
                    <h2 class="mt-3 text-3xl font-black uppercase tracking-tight text-white sm:text-5xl">Latest Stories</h2>
                </div>
                <a href="{{ route('news.index') }}" class="text-xs font-black uppercase tracking-[0.2em] text-yellow-500 hover:text-yellow-400">Read all</a>
            </div>
            @if (collect($latestPosts ?? [])->count())
                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($latestPosts as $post)
                        @include('partials.public.post-card', ['post' => $post])
                    @endforeach
                </div>
            @else
                @include('partials.public.empty-state', ['label' => 'News', 'title' => 'Belum ada berita published'])
            @endif
        </div>
    </section>
@endsection
