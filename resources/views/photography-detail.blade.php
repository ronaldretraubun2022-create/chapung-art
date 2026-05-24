<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $ogImage = $photo->thumbnail
            ? asset('storage/' . urldecode(str_replace('%2F', '/', $photo->thumbnail)))
            : asset('images/og-image.jpg');
        $seoDescription = str(strip_tags($photo->excerpt ?: $photo->description ?: 'Fotografi Papua Selatan dari Chapung Art Merauke.'))->limit(160);
    @endphp
    <title>{{ $photo->title }} | Chapung Art Photography</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta property="og:title" content="{{ $photo->title }} | Chapung Art Photography">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ route('photography.show', $photo->slug) }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $photo->title }} | Chapung Art Photography">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-black text-white">
    @php
        $photoPrice = $photo->price
            ? 'Rp ' . number_format((float) $photo->price, 0, ',', '.')
            : 'Harga tersedia atas permintaan';
        $photoLink = route('photography.show', $photo->slug);
        $whatsappText = rawurlencode(
            "Halo Chapung Art, saya tertarik dengan karya: {$photo->title}\nHarga: {$photoPrice}\nLink karya: {$photoLink}"
        );
    @endphp

    <nav class="sticky top-0 z-50 border-b border-zinc-800 bg-black/85 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
            <a href="{{ route('home') }}" class="text-2xl font-black tracking-[8px]">CHAPUNG ART</a>
            <div class="flex items-center gap-6 text-xs font-semibold uppercase tracking-[3px] text-zinc-300">
                <a href="{{ route('home') }}" class="hover:text-white">Beranda</a>
                <a href="{{ route('photography.index') }}" class="text-amber-500">Photography</a>
            </div>
        </div>
    </nav>

    <main class="px-6 py-16">
        <section class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1.15fr_0.85fr] lg:items-start">
            <div class="overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-950">
                <div class="relative">
                    <img
                        src="{{ $photo->thumbnail ? asset('storage/' . urldecode(str_replace('%2F', '/', $photo->thumbnail))) : 'https://placehold.co/600x800/111111/FFFFFF?text=Chapung+Photo' }}"
                        alt="{{ $photo->title }}"
                        class="h-full max-h-[780px] w-full object-cover"
                        loading="lazy"
                    >
                    <span class="absolute bottom-4 right-4 z-10 rounded-full bg-black/70 px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-white backdrop-blur">Chapung Art © Papua Selatan</span>
                </div>
            </div>

            <div class="lg:sticky lg:top-28">
                @if ($photo->is_featured)
                    <span class="mb-5 inline-flex rounded-full bg-yellow-600 px-4 py-2 text-xs font-black uppercase tracking-[3px] text-black">Featured Photography</span>
                @endif

                <p class="mb-4 text-sm uppercase tracking-[6px] text-amber-500">{{ $photo->photographer_name ?: 'Chapung Art' }}</p>
                <h1 class="text-5xl font-black leading-tight md:text-7xl">{{ $photo->title }}</h1>

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <p class="text-3xl font-black text-amber-400">{{ $photoPrice }}</p>
                    <span class="rounded-full border border-zinc-700 px-4 py-2 text-xs font-bold uppercase tracking-[3px] text-zinc-300">{{ $photo->status }}</span>
                </div>

                <div class="mt-10 space-y-4 border-y border-zinc-800 py-8 text-zinc-300">
                    <div class="flex justify-between gap-6">
                        <span class="text-zinc-500">Lokasi</span>
                        <strong class="text-right text-white">{{ $photo->location ?: '-' }}</strong>
                    </div>
                    <div class="flex justify-between gap-6">
                        <span class="text-zinc-500">Kamera</span>
                        <strong class="text-right text-white">{{ $photo->camera ?: '-' }}</strong>
                    </div>
                    <div class="flex justify-between gap-6">
                        <span class="text-zinc-500">Tahun</span>
                        <strong class="text-right text-white">{{ optional($photo->created_at)->format('Y') ?: '-' }}</strong>
                    </div>
                </div>

                <div class="prose prose-invert mt-10 max-w-none text-zinc-300 prose-p:leading-relaxed prose-a:text-amber-400">
                    {!! $photo->description ?: '<p>Deskripsi photography belum tersedia.</p>' !!}
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
