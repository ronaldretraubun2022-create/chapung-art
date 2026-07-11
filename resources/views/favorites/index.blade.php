@extends('layouts.public')

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('favorites.index', fallback: [
        'title' => __('chapung.favorites.title').' | '.site_setting('site_name', 'Chapung Art'),
        'description' => __('chapung.favorites.description'),
        'canonical_url' => route('favorites.index'),
        'robots' => 'noindex, nofollow',
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.16),transparent_30rem),#050505] px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('chapung.favorites.eyebrow') }}</p>
                <h1 class="mt-4 text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">{{ __('chapung.favorites.title') }}</h1>
                <p class="mt-5 max-w-3xl text-base leading-8 text-zinc-300">{{ __('chapung.favorites.description') }}</p>
            </div>
            <a href="{{ route('artworks.index') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-yellow-600/70 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-yellow-500 hover:bg-yellow-600 hover:text-black">
                <x-heroicon-o-photo class="h-4 w-4" aria-hidden="true" />
                {{ __('chapung.favorites.browse') }}
            </a>
        </div>
    </section>

    <section class="px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl font-black uppercase tracking-tight text-white">{{ __('chapung.favorites.saved_artworks') }}</h2>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('chapung.favorites.count', ['count' => number_format($artworks->total())]) }}</p>
                </div>
            </div>

            @if ($artworks->count())
                <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 xl:grid-cols-4">
                    @foreach ($artworks as $artwork)
                        @include('partials.public.artwork-card', ['artwork' => $artwork])
                    @endforeach
                </div>
                <div class="mt-10">{{ $artworks->links() }}</div>
            @else
                @include('partials.public.empty-state', [
                    'label' => __('chapung.favorites.title'),
                    'title' => __('chapung.favorites.empty_title'),
                    'description' => __('chapung.favorites.empty_description'),
                ])
            @endif
        </div>
    </section>
@endsection
