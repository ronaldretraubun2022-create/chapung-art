<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Berita - Chapung Art</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-black text-white">
    <nav class="sticky top-0 z-50 border-b border-zinc-800 bg-black/85 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
            <a href="{{ route('home') }}" class="text-2xl font-black tracking-[8px]">CHAPUNG ART</a>
            <div class="flex items-center gap-6 text-xs font-semibold uppercase tracking-[3px] text-zinc-300">
                <a href="{{ route('home') }}" class="hover:text-white">Beranda</a>
                <a href="{{ route('gallery') }}" class="hover:text-white">Artwork</a>
                <a href="{{ route('photography.index') }}" class="hover:text-white">Photography</a>
                <a href="{{ route('media.index') }}" class="text-amber-500">Media</a>
            </div>
        </div>
    </nav>

    <main>
        <section class="border-b border-zinc-800 px-6 py-24">
            <div class="mx-auto max-w-7xl">
                <p class="mb-4 text-sm uppercase tracking-[6px] text-amber-500">Newsroom</p>
                <h1 class="max-w-4xl text-5xl font-black leading-tight md:text-7xl">Media Berita Chapung Art</h1>
                <p class="mt-6 max-w-3xl text-lg leading-relaxed text-zinc-400">
                    Cerita seni, budaya, pameran, dan ruang kreatif Papua Selatan dalam format media visual yang elegan.
                </p>
            </div>
        </section>

        <section class="px-6 py-20">
            <div class="mx-auto grid max-w-7xl grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($posts as $post)
                    @php
                        $thumbnail = $post->thumbnail ? urldecode($post->thumbnail) : null;
                        $thumbnail = $thumbnail ? str_replace('%2F', '/', $thumbnail) : null;
                        $thumbnail = $thumbnail ? ltrim(preg_replace('#^/?storage/#', '', $thumbnail), '/') : null;
                    @endphp
                    <article class="group overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-950 shadow-2xl shadow-black/30">
                        <a href="{{ route('media.show', $post->slug) }}" class="block">
                            <div class="relative aspect-[16/10] overflow-hidden bg-zinc-900">
                                <img
                                    src="{{ $thumbnail ? asset('storage/' . $thumbnail) : 'https://placehold.co/1200x700/111111/FFFFFF?text=Chapung+Art+Media' }}"
                                    alt="{{ $post->title }}"
                                    class="w-full h-full object-cover transition duration-500 group-hover:scale-105"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src='https://placehold.co/1200x700/111111/FFFFFF?text=Chapung+Art+Media';"
                                >
                                <span class="absolute bottom-4 right-4 z-10 rounded-full bg-black/70 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-white backdrop-blur">Chapung Art © Papua Selatan</span>
                                <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/80 to-transparent"></div>
                            </div>

                            <div class="space-y-5 p-6">
                                <div class="flex flex-wrap items-center gap-3 text-xs uppercase tracking-[2px] text-zinc-500">
                                    <span>{{ $post->author_name ?: 'Chapung Art' }}</span>
                                    <span class="h-1 w-1 rounded-full bg-amber-500"></span>
                                    <time>{{ optional($post->published_at)->format('d M Y') ?: optional($post->created_at)->format('d M Y') }}</time>
                                </div>

                                <div>
                                    <h2 class="text-2xl font-black leading-tight text-white">{{ $post->title }}</h2>
                                    <p class="mt-4 line-clamp-3 text-sm leading-relaxed text-zinc-400">
                                        {{ $post->excerpt ?: str(strip_tags($post->content))->limit(150) }}
                                    </p>
                                </div>

                                <span class="inline-flex border-b border-amber-500 pb-1 text-xs font-black uppercase tracking-[3px] text-amber-500">
                                    Baca Berita
                                </span>
                            </div>
                        </a>
                    </article>
                @empty
                    <div class="col-span-full rounded-2xl border border-zinc-800 bg-zinc-950 p-10 text-center text-zinc-400">
                        Belum ada media berita published.
                    </div>
                @endforelse
            </div>
        </section>
    </main>
</body>
</html>
