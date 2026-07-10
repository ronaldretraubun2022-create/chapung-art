<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $siteName = site_setting('site_name', 'Chapung Art');
        $metaThumbnail = $post->thumbnail ? urldecode($post->thumbnail) : null;
        $metaThumbnail = $metaThumbnail ? str_replace('%2F', '/', $metaThumbnail) : null;
        $metaThumbnail = $metaThumbnail ? ltrim(preg_replace('#^/?storage/#', '', $metaThumbnail), '/') : null;
        $ogImage = $metaThumbnail ? asset('storage/' . $metaThumbnail) : asset('images/og-image.jpg');
        $seoDescription = str(strip_tags($post->excerpt ?: $post->content ?: 'Media budaya Papua Selatan dari Chapung Art Merauke.'))->limit(160);
    @endphp
    @include('partials.seo-meta', ['seo' => seo_meta('media.show', $post, [
        'title' => $post->title.' | '.$siteName.' Media',
        'description' => (string) $seoDescription,
        'og_image' => $ogImage,
        'canonical_url' => route('media.show', $post->slug),
    ])])

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-black text-white">
    <nav class="sticky top-0 z-50 border-b border-zinc-800 bg-black/85 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
            <a href="{{ route('home') }}" class="text-2xl font-black tracking-[8px]">{{ strtoupper($siteName) }}</a>
            <div class="flex items-center gap-6 text-xs font-semibold uppercase tracking-[3px] text-zinc-300">
                <a href="{{ route('home') }}" class="hover:text-white">Beranda</a>
                <a href="{{ route('media.index') }}" class="text-amber-500">Media</a>
            </div>
        </div>
    </nav>

    <main>
        <article>
            <header class="px-6 py-16 md:py-24">
                <div class="mx-auto max-w-5xl">
                    <p class="mb-5 text-sm uppercase tracking-[6px] text-amber-500">Chapung Art Media</p>
                    <h1 class="text-4xl font-black leading-tight md:text-7xl">{{ $post->title }}</h1>

                    <div class="mt-8 flex flex-wrap items-center gap-4 text-sm uppercase tracking-[2px] text-zinc-500">
                        <span>{{ $post->author_name ?: 'Chapung Art' }}</span>
                        <span class="h-1 w-1 rounded-full bg-amber-500"></span>
                        <time>{{ optional($post->published_at)->format('d M Y H:i') ?: optional($post->created_at)->format('d M Y H:i') }}</time>
                    </div>

                    @if ($post->excerpt)
                        <p class="mt-8 max-w-3xl text-xl leading-relaxed text-zinc-300">{{ $post->excerpt }}</p>
                    @endif
                </div>
            </header>

            <section class="px-6">
                @php
                    $thumbnail = $post->thumbnail ? urldecode($post->thumbnail) : null;
                    $thumbnail = $thumbnail ? str_replace('%2F', '/', $thumbnail) : null;
                    $thumbnail = $thumbnail ? ltrim(preg_replace('#^/?storage/#', '', $thumbnail), '/') : null;
                @endphp
                <div class="relative mx-auto max-w-7xl overflow-hidden rounded-3xl border border-zinc-800 bg-zinc-950">
                    <img
                        src="{{ $thumbnail ? asset('storage/' . $thumbnail) : 'https://placehold.co/1200x700/111111/FFFFFF?text=Chapung+Art+Media' }}"
                        alt="{{ $post->title }}"
                        class="w-full h-full object-cover"
                        loading="lazy"
                        onerror="this.onerror=null;this.src='https://placehold.co/1200x700/111111/FFFFFF?text=Chapung+Art+Media';"
                    >
                    <span class="absolute bottom-4 right-4 z-10 rounded-full bg-black/70 px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-white backdrop-blur">Chapung Art © Papua Selatan</span>
                </div>
            </section>

            <section class="px-6 py-16 md:py-24">
                <div class="prose prose-invert prose-lg mx-auto max-w-4xl prose-p:leading-relaxed prose-a:text-amber-400 prose-strong:text-white prose-headings:font-black prose-headings:text-white">
                    {!! $post->content ?: '<p>Konten berita belum tersedia.</p>' !!}
                </div>
            </section>
        </article>
    </main>
</body>
</html>
