@extends('layouts.public')

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('artists.index', fallback: [
        'title' => __('chapung.pages.artists.title').' | '.site_setting('site_name', 'Chapung Art'),
        'description' => __('chapung.pages.artists.description'),
        'canonical_url' => route('artists.index'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.18),transparent_30rem),#050505] px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('chapung.pages.artists.eyebrow') }}</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">{{ __('chapung.pages.artists.title') }}</h1>
            <p class="mt-6 max-w-3xl text-base leading-8 text-zinc-300 sm:text-lg">{{ __('chapung.pages.artists.description') }}</p>
        </div>
    </section>

    <section class="border-b border-zinc-800 px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('artists.index') }}" class="mx-auto grid max-w-7xl gap-3 md:grid-cols-[1.4fr_.8fr_auto]">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('chapung.filters.search_artist') }}" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white placeholder:text-zinc-500 focus:border-yellow-600 focus:ring-yellow-600">
            <select name="sort" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                <option value="">{{ __('chapung.filters.name') }}</option>
                <option value="featured" @selected(($filters['sort'] ?? '') === 'featured')>{{ __('chapung.filters.featured') }}</option>
            </select>
            <button class="rounded-md bg-yellow-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">{{ __('chapung.filters.filter') }}</button>
        </form>
    </section>

    <section class="px-4 py-14 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            @if ($artists->count())
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($artists as $artist)
                        @include('partials.public.artist-card', ['artist' => $artist])
                    @endforeach
                </div>
                <div class="mt-10">{{ $artists->links() }}</div>
            @else
                @include('partials.public.empty-state', ['label' => __('chapung.types.artists'), 'title' => __('chapung.pages.artists.empty')])
            @endif
        </div>
    </section>
@endsection
