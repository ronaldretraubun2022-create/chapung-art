@extends('layouts.public')

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('artists.index', fallback: [
        'title' => 'Artists | '.site_setting('site_name', 'Chapung Art'),
        'description' => 'Profil seniman, fotografer, dan kreator budaya dalam ekosistem Chapung Art Papua Selatan.',
        'canonical_url' => route('artists.index'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.18),transparent_30rem),#050505] px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Creative Profiles</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">Artists</h1>
            <p class="mt-6 max-w-3xl text-base leading-8 text-zinc-300 sm:text-lg">Profil kreator yang membentuk lanskap seni, fotografi, dan dokumentasi budaya Papua Selatan.</p>
        </div>
    </section>

    <section class="border-b border-zinc-800 px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('artists.index') }}" class="mx-auto grid max-w-7xl gap-3 md:grid-cols-[1.4fr_.8fr_auto]">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search artist, origin, specialization" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white placeholder:text-zinc-500 focus:border-yellow-600 focus:ring-yellow-600">
            <select name="sort" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                <option value="">Name</option>
                <option value="featured" @selected(($filters['sort'] ?? '') === 'featured')>Featured</option>
            </select>
            <button class="rounded-md bg-yellow-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">Filter</button>
        </form>
    </section>

    <section class="px-4 py-14 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            @if ($artists->count())
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($artists as $artist)
                        <article class="group rounded-lg border border-zinc-800 bg-zinc-950 p-5 transition hover:border-yellow-600/70">
                            <a href="{{ route('artists.show', $artist->slug) }}" class="block">
                                <div class="flex gap-4">
                                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-md bg-zinc-900">
                                        @if ($artist->photo)
                                            <img src="{{ asset('storage/'.$artist->photo) }}" alt="{{ $artist->name }}" class="h-full w-full object-cover" loading="lazy">
                                        @else
                                            <div class="grid h-full w-full place-items-center text-sm font-black text-yellow-600">{{ str($artist->name)->substr(0, 2)->upper() }}</div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h2 class="text-xl font-black uppercase tracking-tight text-white">{{ $artist->name }}</h2>
                                        <p class="mt-2 text-sm text-zinc-400">{{ $artist->specialization ?: $artist->origin_area ?: $artist->city }}</p>
                                        <p class="mt-3 text-xs uppercase tracking-[0.16em] text-zinc-500">{{ $artist->artworks_count }} artworks / {{ $artist->photographies_count }} photos</p>
                                    </div>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
                <div class="mt-10">{{ $artists->links() }}</div>
            @else
                @include('partials.public.empty-state', ['label' => 'Artists', 'title' => 'Artist tidak ditemukan'])
            @endif
        </div>
    </section>
@endsection
