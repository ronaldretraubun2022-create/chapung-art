@extends('layouts.public')

@php
    $query = $payload['query'] ?? '';
    $total = $payload['total'] ?? 0;
    $groups = $payload['groups'] ?? collect();
    $title = filled($query) ? 'Search: '.$query : 'Search';
@endphp

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('search.index', fallback: [
        'title' => $title.' | '.site_setting('site_name', 'Chapung Art'),
        'description' => 'Cari artwork, artist, photography, news, dan collection di Chapung Art.',
        'robots' => 'noindex, follow',
        'canonical_url' => route('search.index'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.16),transparent_30rem),#050505] px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Global Search</p>
            <h1 class="mt-4 text-4xl font-black uppercase leading-none text-white sm:text-6xl">Search Chapung Art</h1>
            <form action="{{ route('search.index') }}" method="GET" class="mt-8 grid max-w-3xl gap-3 sm:grid-cols-[1fr_auto]">
                <label for="search-page-input" class="sr-only">Search</label>
                <input id="search-page-input" name="q" value="{{ $query }}" type="search" autocomplete="off" placeholder="Search artwork, artist, photography, news, collection" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-4 text-sm text-white placeholder:text-zinc-500 focus:border-yellow-600 focus:ring-yellow-600">
                <button class="rounded-md bg-yellow-600 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">Search</button>
            </form>
            @if (filled($query))
                <p class="mt-5 text-sm text-zinc-400">{{ $total }} results for <span class="font-bold text-white">{{ $query }}</span></p>
            @endif
        </div>
    </section>

    <section class="px-4 py-14 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-12">
            @if (blank($query))
                @include('partials.public.empty-state', ['label' => 'Search', 'title' => 'Masukkan kata kunci pencarian'])
            @elseif ($total < 1)
                @include('partials.public.empty-state', ['label' => 'Search', 'title' => 'Tidak ada hasil ditemukan'])
            @else
                @foreach ($groups as $key => $group)
                    @if ($group['items']->count())
                        <section>
                            <div class="mb-5 flex items-end justify-between gap-4">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ $group['label'] }}</p>
                                    <h2 class="mt-2 text-2xl font-black uppercase text-white">{{ $group['items']->count() }} results</h2>
                                </div>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                @foreach ($group['items'] as $item)
                                    <article class="group rounded-lg border border-zinc-800 bg-zinc-950 p-3 transition hover:border-yellow-600/70">
                                        <a href="{{ $item['url'] }}" class="grid gap-4 sm:grid-cols-[7rem_1fr]">
                                            <div class="overflow-hidden rounded-md bg-zinc-900">
                                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" width="320" height="240" class="aspect-[4/3] h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async">
                                            </div>
                                            <div class="min-w-0 p-1">
                                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-yellow-600">{{ $group['label'] }}</p>
                                                <h3 class="mt-2 text-lg font-black uppercase text-white">{{ $item['title'] }}</h3>
                                                <p class="mt-2 text-sm text-zinc-400">{{ $item['subtitle'] }}</p>
                                                @if (filled($item['excerpt']))
                                                    <p class="mt-3 line-clamp-2 text-sm leading-6 text-zinc-500">{{ $item['excerpt'] }}</p>
                                                @endif
                                            </div>
                                        </a>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach
            @endif
        </div>
    </section>
@endsection
