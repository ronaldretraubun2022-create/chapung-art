<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Chapung Art</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-black text-white">
    <nav class="sticky top-0 z-50 border-b border-zinc-800 bg-black/85 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
            <a href="{{ route('home') }}" class="text-2xl font-black tracking-[8px]">CHAPUNG ART</a>
            <div class="flex items-center gap-6 text-xs font-semibold uppercase tracking-[3px] text-zinc-300">
                <a href="{{ route('home') }}" class="hover:text-white">Beranda</a>
                <a href="{{ route('gallery') }}" class="text-amber-500">Gallery</a>
            </div>
        </div>
    </nav>

    <main>
        <section class="border-b border-zinc-800 px-6 py-24">
            <div class="mx-auto max-w-7xl">
                <p class="mb-4 text-sm uppercase tracking-[6px] text-amber-500">Curated Marketplace</p>
                <h1 class="max-w-4xl text-5xl font-black leading-tight md:text-7xl">Gallery Karya Chapung Art</h1>
                <p class="mt-6 max-w-3xl text-lg leading-relaxed text-zinc-400">
                    Koleksi karya seni rupa dan visual dari Papua Selatan, dikurasi untuk kolektor, ruang publik, dan penikmat budaya.
                </p>
            </div>
        </section>

        <section class="px-6 py-20">
            <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
                @forelse ($artworks as $artwork)
                    <article class="group overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950 shadow-2xl shadow-black/30">
                        <div class="relative aspect-[4/5] overflow-hidden bg-zinc-900">
                            <img
                                src="{{ $artwork->thumbnail
                                    ? asset('storage/' . urldecode(str_replace('%2F', '/', $artwork->thumbnail)))
                                    : 'https://placehold.co/600x800/111111/FFFFFF?text=Chapung+Art'
                                }}"
                                alt="{{ $artwork->title }}"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                loading="lazy"
                            >
                            <span class="absolute bottom-4 right-4 z-10 rounded-full bg-black/70 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-white backdrop-blur">Chapung Art © Papua Selatan</span>
                            @if ($artwork->is_featured)
                                <span class="absolute left-4 top-4 rounded-full bg-amber-500 px-3 py-1 text-xs font-black uppercase tracking-[2px] text-black">Featured</span>
                            @endif
                        </div>
                        <div class="space-y-4 p-5">
                            <div>
                                <h2 class="text-xl font-black leading-tight">{{ $artwork->title }}</h2>
                                <p class="mt-2 text-sm uppercase tracking-[2px] text-zinc-500">{{ $artwork->artist_name ?: 'Chapung Art' }}</p>
                            </div>
                            <div class="flex items-center justify-between gap-4 text-sm">
                                <p class="font-bold text-amber-400">
                                    {{ $artwork->price ? 'Rp ' . number_format((float) $artwork->price, 0, ',', '.') : 'Harga tersedia atas permintaan' }}
                                </p>
                                <span class="rounded-full border border-zinc-700 px-3 py-1 text-xs uppercase tracking-[2px] text-zinc-300">{{ $artwork->status }}</span>
                            </div>
                            <a href="{{ route('artwork.show', $artwork->slug) }}" class="block border border-white px-4 py-3 text-center text-xs font-bold uppercase tracking-[3px] transition hover:bg-white hover:text-black">
                                Detail
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-2xl border border-zinc-800 bg-zinc-950 p-10 text-center text-zinc-400">
                        Belum ada artwork yang tersedia.
                    </div>
                @endforelse
            </div>
        </section>
    </main>
</body>
</html>
