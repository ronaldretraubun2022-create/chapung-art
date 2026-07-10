@extends('layouts.public')

@php
    $mainImage = $post->display_image ?: $post->og_image;
    $description = str(strip_tags($post->excerpt ?: $post->content ?: 'Berita seni budaya Papua Selatan dari Chapung Art.'))->limit(160);
@endphp

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('news.show', $post, [
        'title' => $post->title.' | '.site_setting('site_name', 'Chapung Art'),
        'description' => (string) $description,
        'og_image' => $mainImage ? asset('storage/'.$mainImage) : asset('images/og-image.jpg'),
        'canonical_url' => route('news.show', $post->slug),
    ])])
@endsection

@section('content')
    <article>
        <header class="border-b border-zinc-800 px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
            <div class="mx-auto max-w-4xl">
                <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ $post->category?->name ?: 'News' }}</p>
                <h1 class="mt-4 text-4xl font-black uppercase leading-tight tracking-tight text-white sm:text-6xl">{{ $post->title }}</h1>
                <div class="mt-6 flex flex-wrap items-center gap-3 text-xs uppercase tracking-[0.18em] text-zinc-500">
                    <span>{{ $post->author?->name ?: $post->author_name ?: 'Chapung Art' }}</span>
                    <span class="h-1 w-1 rounded-full bg-yellow-600"></span>
                    <time>{{ optional($post->published_at ?: $post->created_at)->format('d M Y') }}</time>
                    @if ($post->reading_time)
                        <span class="h-1 w-1 rounded-full bg-yellow-600"></span>
                        <span>{{ $post->reading_time }} min read</span>
                    @endif
                </div>
                @if ($post->excerpt)
                    <p class="mt-8 text-xl leading-9 text-zinc-300">{{ $post->excerpt }}</p>
                @endif
            </div>
        </header>

        <section class="px-4 py-10 sm:px-6 lg:px-8">
            <div class="group mx-auto max-w-6xl overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 p-3">
                @include('partials.public.image', ['path' => $mainImage, 'alt' => $post->title, 'ratio' => 'aspect-[16/9]', 'label' => 'News'])
            </div>
        </section>

        <section class="px-4 pb-16 sm:px-6 lg:px-8">
            <div class="prose prose-invert prose-lg mx-auto max-w-3xl prose-p:leading-8 prose-a:text-yellow-500 prose-headings:font-black prose-headings:uppercase prose-headings:text-white">
                {!! $post->content ?: '<p>Konten berita belum tersedia.</p>' !!}
            </div>

            @if ($post->tags->count())
                <div class="mx-auto mt-10 flex max-w-3xl flex-wrap gap-2">
                    @foreach ($post->tags as $tag)
                        <span class="rounded-md border border-zinc-800 px-3 py-2 text-xs font-bold uppercase tracking-[0.16em] text-zinc-300">{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif
        </section>
    </article>

    @if ($relatedPosts->count())
        <section class="border-t border-zinc-800 px-4 py-14 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <h2 class="text-2xl font-black uppercase tracking-tight text-white">Related News</h2>
                <div class="mt-6 grid gap-6 md:grid-cols-3">
                    @foreach ($relatedPosts as $related)
                        @include('partials.public.post-card', ['post' => $related])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
