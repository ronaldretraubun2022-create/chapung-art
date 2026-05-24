<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photography - Chapung Art</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-black text-white">
    <nav class="sticky top-0 z-50 border-b border-zinc-800 bg-black/85 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
            <a href="{{ route('home') }}" class="text-2xl font-black tracking-[8px]">CHAPUNG ART</a>
            <div class="flex items-center gap-6 text-xs font-semibold uppercase tracking-[3px] text-zinc-300">
                <a href="{{ route('home') }}" class="hover:text-white">Beranda</a>
                <a href="{{ route('gallery') }}" class="hover:text-white">Artwork</a>
                <a href="{{ route('photography.index') }}" class="text-amber-500">Photography</a>
            </div>
        </div>
    </nav>

    <main>
        <section class="border-b border-zinc-800 px-6 py-24">
            <div class="mx-auto max-w-7xl">
                <p class="mb-4 text-sm uppercase tracking-[6px] text-amber-500">Visual Archive</p>
                <h1 class="max-w-4xl text-5xl font-black leading-tight md:text-7xl">Photography Chapung Art</h1>
                <p class="mt-6 max-w-3xl text-lg leading-relaxed text-zinc-400">
                    Arsip fotografi premium dari Papua Selatan, menampilkan ruang, wajah, budaya, dan lanskap visual Indonesia Timur.
                </p>
            </div>
        </section>

        <section class="px-6 py-20">
            <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
                @forelse ($photographies as $photo)
                    <article class="group overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950 shadow-2xl shadow-black/30">
                        <div class="relative aspect-[4/5] overflow-hidden bg-zinc-900">
                            <img
                                src="{{ $photo->thumbnail ? asset('storage/' . urldecode(str_replace('%2F', '/', $photo->thumbnail))) : 'https://placehold.co/600x800/111111/FFFFFF?text=Chapung+Photo' }}"
                                alt="{{ $photo->title }}"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                loading="lazy"
                            >
                            <span class="absolute bottom-4 right-4 z-10 rounded-full bg-black/70 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-white backdrop-blur">Chapung Art © Papua Selatan</span>
                        </div>

                        <div class="space-y-5 p-5">
                            <div>
                                <div class="mb-3 flex items-center justify-between gap-3">
                                    <span class="rounded-full border border-zinc-700 px-3 py-1 text-xs font-bold uppercase tracking-[2px] text-zinc-300">
                                        {{ $photo->status }}
                                    </span>
                                    <span class="text-xs uppercase tracking-[2px] text-zinc-500">
                                        {{ optional($photo->created_at)->format('Y') ?: '-' }}
                                    </span>
                                </div>

                                <h2 class="text-xl font-black leading-tight">{{ $photo->title }}</h2>
                                <p class="mt-2 text-sm uppercase tracking-[2px] text-zinc-500">
                                    {{ $photo->photographer_name ?: 'Chapung Art' }}
                                </p>
                            </div>

                            <div class="space-y-2 text-sm text-zinc-400">
                                <div class="flex justify-between gap-4">
                                    <span>Lokasi</span>
                                    <strong class="text-right font-semibold text-white">{{ $photo->location ?: '-' }}</strong>
                                </div>
                                <div class="flex justify-between gap-4">
                                    <span>Harga</span>
                                    <strong class="text-right font-semibold text-amber-400">
                                        {{ $photo->price ? 'Rp ' . number_format((float) $photo->price, 0, ',', '.') : 'Atas permintaan' }}
                                    </strong>
                                </div>
                            </div>

                            <a href="{{ route('photography.show', $photo->slug) }}" class="flex items-center justify-center rounded-xl bg-yellow-600 px-5 py-3 text-center text-xs font-black uppercase tracking-[3px] text-black transition hover:bg-yellow-500">
                                Detail / Beli
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-2xl border border-zinc-800 bg-zinc-950 p-10 text-center text-zinc-400">
                        Belum ada koleksi photography yang tersedia.
                    </div>
                @endforelse
            </div>
        </section>
    </main>
</body>
</html>
