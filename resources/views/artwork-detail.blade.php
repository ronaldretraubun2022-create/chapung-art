<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $ogImage = $artwork->thumbnail
            ? asset('storage/' . urldecode(str_replace('%2F', '/', $artwork->thumbnail)))
            : asset('images/og-image.jpg');
        $seoDescription = str(strip_tags($artwork->excerpt ?: $artwork->description ?: 'Karya seni Papua Selatan dari Chapung Art Merauke.'))->limit(160);
    @endphp
    <title>{{ $artwork->title }} | Chapung Art Merauke</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta property="og:title" content="{{ $artwork->title }} | Chapung Art Merauke">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ route('artwork.show', $artwork->slug) }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $artwork->title }} | Chapung Art Merauke">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-black text-white">
    @php
        $artworkPrice = $artwork->price
            ? 'Rp ' . number_format((float) $artwork->price, 0, ',', '.')
            : 'Harga tersedia atas permintaan';
        $artworkLink = route('artwork.show', $artwork->slug);
        $whatsappText = rawurlencode(
            "Halo Chapung Art, saya tertarik dengan karya: {$artwork->title}\nHarga: {$artworkPrice}\nLink karya: {$artworkLink}"
        );
    @endphp

    <nav class="sticky top-0 z-50 border-b border-zinc-800 bg-black/85 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
            <a href="{{ route('home') }}" class="text-2xl font-black tracking-[8px]">CHAPUNG ART</a>
            <div class="flex items-center gap-6 text-xs font-semibold uppercase tracking-[3px] text-zinc-300">
                <a href="{{ route('home') }}" class="hover:text-white">Beranda</a>
                <a href="{{ route('gallery') }}" class="hover:text-white">Gallery</a>
            </div>
        </div>
    </nav>

    <main class="px-6 py-16">
        <section class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1.15fr_0.85fr] lg:items-start">
            <div class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-950">
                <div class="relative">
                    <img
                    src="{{ $artwork->thumbnail
                        ? asset('storage/' . urldecode(str_replace('%2F', '/', $artwork->thumbnail)))
                        : 'https://placehold.co/600x800/111111/FFFFFF?text=Chapung+Art'
                    }}"
                    alt="{{ $artwork->title }}"
                    class="h-full max-h-[780px] w-full object-cover"
                    loading="lazy"
                    >
                    <span class="absolute bottom-4 right-4 z-10 rounded-full bg-black/70 px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-white backdrop-blur">Chapung Art © Papua Selatan</span>
                </div>
            </div>

            <div class="lg:sticky lg:top-28">
                @if ($artwork->is_featured)
                    <span class="mb-5 inline-flex rounded-full bg-amber-500 px-4 py-2 text-xs font-black uppercase tracking-[3px] text-black">Featured Artwork</span>
                @endif

                <p class="mb-4 text-sm uppercase tracking-[6px] text-amber-500">{{ $artwork->artist_name ?: 'Chapung Art' }}</p>
                <h1 class="text-5xl font-black leading-tight md:text-7xl">{{ $artwork->title }}</h1>

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <p class="text-3xl font-black text-amber-400">
                        {{ $artwork->price ? 'Rp ' . number_format((float) $artwork->price, 0, ',', '.') : 'Harga tersedia atas permintaan' }}
                    </p>
                    <span class="rounded-full border border-zinc-700 px-4 py-2 text-xs font-bold uppercase tracking-[3px] text-zinc-300">{{ $artwork->status }}</span>
                </div>

                <div class="mt-10 space-y-4 border-y border-zinc-800 py-8 text-zinc-300">
                    <div class="flex justify-between gap-6">
                        <span class="text-zinc-500">Media</span>
                        <strong class="text-right text-white">{{ $artwork->medium ?: '-' }}</strong>
                    </div>
                    <div class="flex justify-between gap-6">
                        <span class="text-zinc-500">Ukuran</span>
                        <strong class="text-right text-white">{{ $artwork->size ?: '-' }}</strong>
                    </div>
                    <div class="flex justify-between gap-6">
                        <span class="text-zinc-500">Tahun</span>
                        <strong class="text-right text-white">{{ $artwork->year ?: '-' }}</strong>
                    </div>
                </div>

                <div class="prose prose-invert mt-10 max-w-none text-zinc-300 prose-p:leading-relaxed prose-a:text-amber-400">
                    {!! $artwork->description ?: '<p>Deskripsi karya belum tersedia.</p>' !!}
                </div>

                <a href="https://wa.me/6281392269774?text={{ $whatsappText }}" target="_blank" rel="noopener" class="mt-10 flex w-full items-center justify-center gap-3 rounded-xl bg-yellow-600 px-8 py-4 text-center text-sm font-black uppercase tracking-[4px] text-black transition hover:bg-yellow-500">
                    <x-heroicon-o-chat-bubble-left-right class="h-5 w-5" />
                    <span>Hubungi Kolektor / Beli Karya</span>
                </a>
            </div>
        </section>
    </main>
</body>
</html>
