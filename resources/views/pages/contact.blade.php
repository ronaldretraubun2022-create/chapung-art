@extends('layouts.public')

@php
    $email = site_setting('email', 'info@chapungart.com');
    $whatsapp = preg_replace('/\D+/', '', site_setting('whatsapp', '6281234567890')) ?: '6281234567890';
    $address = site_setting('address', 'Merauke, Papua Selatan');
    $maps = site_setting('google_maps_url');
@endphp

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('contact', fallback: [
        'title' => 'Contact | '.site_setting('site_name', 'Chapung Art'),
        'description' => 'Hubungi Chapung Art untuk karya seni, fotografi budaya, kolaborasi, dan marketplace seni Papua Selatan.',
        'canonical_url' => route('contact'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(127,29,29,.2),transparent_30rem),#050505] px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Contact</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">Terhubung dengan Chapung Art</h1>
            <p class="mt-6 max-w-3xl text-lg leading-8 text-zinc-300">Untuk pembelian karya, arsip fotografi, publikasi budaya, dan kolaborasi kreatif.</p>
        </div>
    </section>

    <section class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-5 md:grid-cols-2">
            <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="rounded-lg border border-zinc-800 bg-zinc-950 p-6 hover:border-yellow-600/70">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">WhatsApp</p>
                <h2 class="mt-3 text-2xl font-black text-white">+{{ $whatsapp }}</h2>
            </a>
            <a href="mailto:{{ $email }}" class="rounded-lg border border-zinc-800 bg-zinc-950 p-6 hover:border-yellow-600/70">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Email</p>
                <h2 class="mt-3 text-2xl font-black text-white">{{ $email }}</h2>
            </a>
            <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Address</p>
                <h2 class="mt-3 text-2xl font-black text-white">{{ $address }}</h2>
            </div>
            @if ($maps)
                <a href="{{ $maps }}" target="_blank" rel="noopener" class="rounded-lg border border-zinc-800 bg-zinc-950 p-6 hover:border-yellow-600/70">
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Maps</p>
                    <h2 class="mt-3 text-2xl font-black text-white">Open location</h2>
                </a>
            @else
                <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6">
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Maps</p>
                    <h2 class="mt-3 text-2xl font-black text-white">Location URL belum diatur</h2>
                </div>
            @endif
        </div>
    </section>
@endsection
