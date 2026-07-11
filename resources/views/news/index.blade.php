@extends('layouts.public')

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('news.index', fallback: [
        'title' => __('chapung.home.news').' | '.site_setting('site_name', 'Chapung Art'),
        'description' => __('chapung.pages.detail.news_description'),
        'canonical_url' => route($indexRouteName ?? 'news.index'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.18),transparent_30rem),#050505] px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('chapung.pages.news.eyebrow') }}</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">{{ __('chapung.pages.news.title') }}</h1>
            <p class="mt-6 max-w-3xl text-base leading-8 text-zinc-300 sm:text-lg">{{ __('chapung.pages.news.description') }}</p>
        </div>
    </section>

    <section class="border-b border-zinc-800 px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route($indexRouteName ?? 'news.index') }}" class="mx-auto grid max-w-7xl gap-3 md:grid-cols-[1.4fr_1fr_.8fr_auto]">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('chapung.pages.search.placeholder') }}" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white placeholder:text-zinc-500 focus:border-yellow-600 focus:ring-yellow-600">
            <select name="category" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                <option value="">{{ __('chapung.pages.news.all_categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="sort" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                <option value="">{{ __('chapung.filters.newest') }}</option>
                <option value="popular" @selected(($filters['sort'] ?? '') === 'popular')>{{ __('chapung.pages.news.popular') }}</option>
            </select>
            <button class="rounded-md bg-yellow-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">{{ __('chapung.filters.filter') }}</button>
        </form>
    </section>

    <section class="px-4 py-14 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            @if ($posts->count())
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($posts as $post)
                        @include('partials.public.post-card', ['post' => $post, 'showRouteName' => $showRouteName ?? 'news.show'])
                    @endforeach
                </div>
                <div class="mt-10">{{ $posts->links() }}</div>
            @else
                @include('partials.public.empty-state', ['label' => __('chapung.types.news'), 'title' => __('chapung.pages.search.empty_results')])
            @endif
        </div>
    </section>
@endsection
