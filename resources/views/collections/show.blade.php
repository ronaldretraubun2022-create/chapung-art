@extends('layouts.public')

@php
    $heroImage = $collection->banner_image ?: $collection->cover_image;
@endphp

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('collections.show', fallback: [
        'title' => $collection->name.' Collection | '.site_setting('site_name', 'Chapung Art'),
        'description' => (string) str(strip_tags($collection->description ?: 'Collection Chapung Art Papua Selatan.'))->limit(160),
        'og_image' => $heroImage ? asset('storage/'.$heroImage) : asset('images/og-image.jpg'),
        'canonical_url' => route('collections.show', $collection->slug),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-cover bg-center px-4 py-20 sm:px-6 lg:px-8" @if ($heroImage) style="background-image: linear-gradient(180deg, rgba(0,0,0,.72), rgba(0,0,0,.94)), url('{{ asset('storage/'.$heroImage) }}');" @endif>
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Collection</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">{{ $collection->name }}</h1>
            <p class="mt-6 max-w-3xl text-base leading-8 text-zinc-300 sm:text-lg">{{ $collection->description ?: 'Kurasi karya seni dan fotografi dari Chapung Art.' }}</p>
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
                    <div class="mt-8">{{ $artworks->links() }}</div>
                @else
                    <div class="mt-6">@include('partials.public.empty-state', ['label' => 'Artwork', 'title' => 'Belum ada artwork dalam collection ini'])</div>
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
                    <div class="mt-8">{{ $photographies->links() }}</div>
                @else
                    <div class="mt-6">@include('partials.public.empty-state', ['label' => 'Photography', 'title' => 'Belum ada photography dalam collection ini'])</div>
                @endif
            </div>
        </div>
    </section>
@endsection
