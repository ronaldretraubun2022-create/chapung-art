@extends('layouts.public')

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('about', fallback: [
        'title' => 'About | '.site_setting('site_name', 'Chapung Art'),
        'description' => 'Tentang Chapung Art, platform seni, fotografi budaya, dan arsip kreatif Papua Selatan.',
        'canonical_url' => route('about'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.18),transparent_30rem),#050505] px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">About</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">Chapung Art</h1>
            <p class="mt-6 max-w-3xl text-lg leading-8 text-zinc-300">{{ site_setting('site_description', 'Galeri seni, fotografi budaya, dan media kreatif Papua Selatan.') }}</p>
        </div>
    </section>

    <section class="border-b border-zinc-800 px-4 py-14 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-6 md:grid-cols-3">
            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6"><p class="text-3xl font-black text-yellow-500">{{ number_format($artistCount) }}</p><p class="mt-2 text-sm uppercase tracking-[0.18em] text-zinc-400">Artists</p></div>
            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6"><p class="text-3xl font-black text-yellow-500">{{ number_format($artworkCount) }}</p><p class="mt-2 text-sm uppercase tracking-[0.18em] text-zinc-400">Artworks</p></div>
            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6"><p class="text-3xl font-black text-yellow-500">{{ number_format($photographyCount) }}</p><p class="mt-2 text-sm uppercase tracking-[0.18em] text-zinc-400">Photography</p></div>
        </div>
    </section>

    <section class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[.8fr_1.2fr]">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Mission</p>
                <h2 class="mt-3 text-3xl font-black uppercase tracking-tight text-white sm:text-5xl">Ruang seni dan budaya yang hidup.</h2>
            </div>
            <div class="space-y-5 text-base leading-8 text-zinc-300">
                <p>Chapung Art menghadirkan karya seni, fotografi budaya, profil kreator, dan berita seni budaya dari Papua Selatan dalam satu ekosistem digital yang rapi dan dapat diakses publik.</p>
                <p>Platform ini dirancang untuk mendukung seniman, fotografer, jurnalis budaya, kolektor, komunitas, dan mitra kreatif melalui kurasi visual yang profesional.</p>
                <p>Setiap halaman publik dibangun agar cepat, responsif, SEO-ready, dan tetap menonjolkan karakter premium gallery.</p>
            </div>
        </div>
    </section>
@endsection
