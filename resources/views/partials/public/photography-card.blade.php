<article class="group rounded-lg border border-zinc-800 bg-zinc-950 p-3 shadow-xl shadow-black/20 transition hover:border-yellow-600/70">
    <a href="{{ route('photography.show', $photo->slug) }}" class="block">
        @include('partials.public.image', ['path' => $photo->thumbnail, 'alt' => $photo->title, 'label' => 'Photography'])
        <div class="p-2 pt-4">
            <h3 class="text-lg font-black uppercase tracking-tight text-white">{{ $photo->title }}</h3>
            <p class="mt-2 text-sm text-zinc-400">{{ $photo->artist_display_name ?: 'Chapung Art' }}</p>
            <div class="mt-4 grid gap-2 text-sm text-zinc-400">
                <div class="flex justify-between gap-3"><span>Location</span><strong class="text-right text-white">{{ $photo->location ?: $photo->province ?: '-' }}</strong></div>
                <div class="flex justify-between gap-3"><span>License</span><strong class="text-right text-yellow-500">{{ $photo->license ?: 'By request' }}</strong></div>
            </div>
        </div>
    </a>
</article>
