@extends('layouts.public')

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('about', fallback: [
        'title' => __('chapung.pages.about.title').' | '.site_setting('site_name', 'Chapung Art'),
        'description' => __('chapung.pages.about.description'),
        'canonical_url' => route('about'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.18),transparent_30rem),#050505] px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('chapung.pages.about.title') }}</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">Chapung Art</h1>
            <p class="mt-6 max-w-3xl text-lg leading-8 text-zinc-300">{{ site_setting('site_description', __('chapung.home.hero_subtitle')) }}</p>
        </div>
    </section>

    <section class="border-b border-zinc-800 px-4 py-14 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-6 md:grid-cols-3">
            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6"><p class="text-3xl font-black text-yellow-500">{{ number_format($artistCount) }}</p><p class="mt-2 text-sm uppercase tracking-[0.18em] text-zinc-400">{{ __('chapung.types.artists') }}</p></div>
            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6"><p class="text-3xl font-black text-yellow-500">{{ number_format($artworkCount) }}</p><p class="mt-2 text-sm uppercase tracking-[0.18em] text-zinc-400">{{ __('chapung.types.artworks') }}</p></div>
            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6"><p class="text-3xl font-black text-yellow-500">{{ number_format($photographyCount) }}</p><p class="mt-2 text-sm uppercase tracking-[0.18em] text-zinc-400">{{ __('chapung.types.photography') }}</p></div>
        </div>
    </section>

    <section class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[.8fr_1.2fr]">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('chapung.pages.about.mission') }}</p>
                <h2 class="mt-3 text-3xl font-black uppercase tracking-tight text-white sm:text-5xl">{{ __('chapung.pages.about.mission_title') }}</h2>
            </div>
            <div class="space-y-5 text-base leading-8 text-zinc-300">
                <p>{{ __('chapung.pages.about.paragraph_one') }}</p>
                <p>{{ __('chapung.pages.about.paragraph_two') }}</p>
                <p>{{ __('chapung.pages.about.paragraph_three') }}</p>
            </div>
        </div>
    </section>
@endsection
