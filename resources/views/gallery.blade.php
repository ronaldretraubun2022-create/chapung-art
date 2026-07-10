@extends('layouts.public')

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('gallery', fallback: [
        'title' => 'Artwork Gallery | '.site_setting('site_name', 'Chapung Art'),
        'description' => 'Koleksi artwork Papua Selatan dari Chapung Art: lukisan, ukiran, dan karya visual terkurasi.',
        'canonical_url' => route('gallery'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.18),transparent_30rem),#050505] px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Curated Marketplace</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">Artwork Gallery</h1>
            <p class="mt-6 max-w-3xl text-base leading-8 text-zinc-300 sm:text-lg">Temukan karya seni Papua Selatan yang dikurasi untuk kolektor, ruang publik, dan dokumentasi budaya.</p>
        </div>
    </section>

    <section class="border-b border-zinc-800 px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('gallery') }}" class="mx-auto grid max-w-7xl gap-3 md:grid-cols-2 xl:grid-cols-[1.3fr_.9fr_.9fr_.9fr_.9fr_.8fr_auto]">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search artwork, artist, material" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white placeholder:text-zinc-500 focus:border-yellow-600 focus:ring-yellow-600">
            <select name="category" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="artist" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                <option value="">All artists</option>
                @foreach ($artists as $artist)
                    <option value="{{ $artist->id }}" @selected(($filters['artist'] ?? '') == $artist->id)>{{ $artist->name }}</option>
                @endforeach
            </select>
            <select name="collection" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                <option value="">All collections</option>
                @foreach ($collections as $collection)
                    <option value="{{ $collection->id }}" @selected(($filters['collection'] ?? '') == $collection->id)>{{ $collection->name }}</option>
                @endforeach
            </select>
            <select name="tag" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                <option value="">All tags</option>
                @foreach ($tags as $tag)
                    <option value="{{ $tag->id }}" @selected(($filters['tag'] ?? '') == $tag->id)>{{ $tag->name }}</option>
                @endforeach
            </select>
            <select name="sort" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest' || ($filters['sort'] ?? '') === '')>Newest</option>
                <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Oldest</option>
                <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Price low</option>
                <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Price high</option>
            </select>
            <div class="flex items-center gap-3 rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white">
                <input id="gallery-featured" name="featured" value="1" type="checkbox" @checked(($filters['featured'] ?? false) || ($filters['sort'] ?? '') === 'featured') class="rounded border-zinc-700 bg-black text-yellow-600 focus:ring-yellow-600">
                <label for="gallery-featured" class="text-xs font-black uppercase tracking-[0.14em] text-zinc-300">Featured</label>
            </div>
            <div class="flex gap-3 md:col-span-2 xl:col-span-1">
                <button class="flex-1 rounded-md bg-yellow-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">Filter</button>
                <a href="{{ route('gallery') }}" class="rounded-md border border-zinc-800 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-zinc-300 hover:border-yellow-600 hover:text-yellow-500">Reset</a>
            </div>
        </form>
    </section>

    <section class="px-4 py-14 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            @if ($artworks->count())
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($artworks as $artwork)
                        @include('partials.public.artwork-card', ['artwork' => $artwork])
                    @endforeach
                </div>
                <div class="mt-10">{{ $artworks->links() }}</div>
            @else
                @include('partials.public.empty-state', ['label' => 'Artwork', 'title' => 'Artwork tidak ditemukan'])
            @endif
        </div>
    </section>
@endsection
