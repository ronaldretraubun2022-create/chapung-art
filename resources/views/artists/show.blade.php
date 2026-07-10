@extends('layouts.public')

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('artists.show', fallback: [
        'title' => $artist->name.' | '.site_setting('site_name', 'Chapung Art'),
        'description' => (string) str(strip_tags($artist->bio ?: $artist->specialization ?: 'Profil kreator Chapung Art Papua Selatan.'))->limit(160),
        'og_image' => $artist->photo ? asset('storage/'.$artist->photo) : asset('images/og-image.jpg'),
        'canonical_url' => route('artists.show', $artist->slug),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[.7fr_1.3fr]">
            <div class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 p-3">
                @if ($artist->photo)
                    <img src="{{ asset('storage/'.$artist->photo) }}" alt="{{ $artist->name }}" class="aspect-[4/5] w-full rounded-md object-cover" loading="lazy">
                @else
                    <div class="grid aspect-[4/5] place-items-center rounded-md bg-[radial-gradient(circle_at_top,rgba(202,138,4,.2),transparent_20rem),#101010] text-5xl font-black text-yellow-600">{{ str($artist->name)->substr(0, 2)->upper() }}</div>
                @endif
            </div>
            <article>
                <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ $artist->origin_area ?: 'Chapung Art Artist' }}</p>
                <h1 class="mt-4 text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">{{ $artist->name }}</h1>
                <p class="mt-5 text-xl text-zinc-300">{{ $artist->specialization ?: 'Seni dan budaya Papua Selatan' }}</p>
                <div class="mt-8 grid gap-3 border-y border-zinc-800 py-6 text-sm text-zinc-400 sm:grid-cols-2">
                    <div><span class="block text-zinc-500">City</span><strong class="text-white">{{ $artist->city ?: '-' }}</strong></div>
                    <div><span class="block text-zinc-500">Province</span><strong class="text-white">{{ $artist->province ?: '-' }}</strong></div>
                    <div><span class="block text-zinc-500">Education</span><strong class="text-white">{{ $artist->education ?: '-' }}</strong></div>
                    <div><span class="block text-zinc-500">Website</span><strong class="text-white">{{ $artist->website ?: '-' }}</strong></div>
                </div>
                <div class="prose prose-invert mt-8 max-w-none prose-p:leading-8 prose-a:text-yellow-500">
                    {!! $artist->bio ?: '<p>Bio artist belum tersedia.</p>' !!}
                </div>
            </article>
        </div>
    </section>

    <section class="px-4 py-14 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-14">
            <div>
                <h2 class="text-2xl font-black uppercase tracking-tight text-white">Artwork</h2>
                @if ($artworks->count())
                    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($artworks as $artwork)
                            @include('partials.public.artwork-card', ['artwork' => $artwork])
                        @endforeach
                    </div>
                @else
                    <div class="mt-6">@include('partials.public.empty-state', ['label' => 'Artwork', 'title' => 'Belum ada artwork'])</div>
                @endif
            </div>

            <div>
                <h2 class="text-2xl font-black uppercase tracking-tight text-white">Photography</h2>
                @if ($photographies->count())
                    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($photographies as $photo)
                            @include('partials.public.photography-card', ['photo' => $photo])
                        @endforeach
                    </div>
                @else
                    <div class="mt-6">@include('partials.public.empty-state', ['label' => 'Photography', 'title' => 'Belum ada photography'])</div>
                @endif
            </div>
        </div>
    </section>
@endsection
