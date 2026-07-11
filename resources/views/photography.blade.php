@extends('layouts.public')

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('photography.index', fallback: [
        'title' => __('chapung.pages.photography.title').' | '.site_setting('site_name', 'Chapung Art'),
        'description' => __('chapung.pages.photography.description'),
        'canonical_url' => route('photography.index'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(127,29,29,.22),transparent_30rem),#050505] px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('chapung.pages.photography.eyebrow') }}</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">{{ __('chapung.pages.photography.title') }}</h1>
            <p class="mt-6 max-w-3xl text-base leading-8 text-zinc-300 sm:text-lg">{{ __('chapung.pages.photography.description') }}</p>
        </div>
    </section>

    <section class="border-b border-zinc-800 px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('photography.index') }}" class="mx-auto grid max-w-7xl gap-3 md:grid-cols-[1.4fr_1fr_1fr_1fr_.8fr_auto]">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('chapung.filters.search_photo') }}" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white placeholder:text-zinc-500 focus:border-yellow-600 focus:ring-yellow-600">
            <select name="category" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                <option value="">{{ __('chapung.filters.all_categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="artist" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                <option value="">{{ __('chapung.filters.all_artists') }}</option>
                @foreach ($artists as $artist)
                    <option value="{{ $artist->id }}" @selected(($filters['artist'] ?? '') == $artist->id)>{{ $artist->name }}</option>
                @endforeach
            </select>
            <select name="collection" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                <option value="">{{ __('chapung.filters.all_collections') }}</option>
                @foreach ($collections as $collection)
                    <option value="{{ $collection->id }}" @selected(($filters['collection'] ?? '') == $collection->id)>{{ $collection->name }}</option>
                @endforeach
            </select>
            <select name="sort" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                <option value="">{{ __('chapung.filters.newest') }}</option>
                <option value="featured" @selected(($filters['sort'] ?? '') === 'featured')>{{ __('chapung.filters.featured') }}</option>
                <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('chapung.filters.price_low') }}</option>
                <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('chapung.filters.price_high') }}</option>
            </select>
            <button class="rounded-md bg-yellow-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">{{ __('chapung.filters.filter') }}</button>
        </form>
    </section>

    <section class="px-4 py-14 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            @if ($photographies->count())
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($photographies as $photo)
                        @include('partials.public.photography-card', ['photo' => $photo])
                    @endforeach
                </div>
                <div class="mt-10">{{ $photographies->links() }}</div>
            @else
                @include('partials.public.empty-state', ['label' => __('chapung.types.photography'), 'title' => __('chapung.pages.photography.empty')])
            @endif
        </div>
    </section>
@endsection
