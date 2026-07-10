<article class="group overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 shadow-xl shadow-black/20 transition hover:border-yellow-600/70">
    <a href="{{ route('news.show', $post->slug) }}" class="block">
        @include('partials.public.image', ['path' => $post->display_image, 'alt' => $post->title, 'ratio' => 'aspect-[16/10]', 'label' => 'News'])
        <div class="p-5">
            <div class="flex flex-wrap items-center gap-3 text-xs uppercase tracking-[0.16em] text-zinc-500">
                <span>{{ $post->category?->name ?: 'News' }}</span>
                <span class="h-1 w-1 rounded-full bg-yellow-600"></span>
                <time>{{ optional($post->published_at ?: $post->created_at)->format('d M Y') }}</time>
            </div>
            <h3 class="mt-4 text-xl font-black uppercase tracking-tight text-white">{{ $post->title }}</h3>
            <p class="mt-3 line-clamp-3 text-sm leading-6 text-zinc-400">{{ $post->excerpt ?: str(strip_tags($post->content))->limit(150) }}</p>
            <span class="mt-5 inline-flex border-b border-yellow-600 pb-1 text-xs font-black uppercase tracking-[0.2em] text-yellow-500">Read</span>
        </div>
    </a>
</article>
