<article class="group rounded-lg border border-zinc-800 bg-zinc-950 p-3 shadow-xl shadow-black/20 transition hover:border-yellow-600/70">
    <a href="{{ route('artwork.show', $artwork->slug) }}" class="block">
        @include('partials.public.image', ['path' => $artwork->thumbnail, 'alt' => $artwork->title, 'label' => 'Artwork'])
        <div class="p-2 pt-4">
            <div class="flex items-start justify-between gap-3">
                <h3 class="text-lg font-black uppercase tracking-tight text-white">{{ $artwork->title }}</h3>
                @if ($artwork->is_featured)
                    <span class="rounded-md bg-yellow-600 px-2 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-black">Featured</span>
                @endif
            </div>
            <p class="mt-2 text-sm text-zinc-400">{{ $artwork->artist_display_name ?: 'Chapung Art' }}</p>
            <div class="mt-4 flex items-center justify-between gap-3 text-sm">
                <span class="font-bold text-yellow-500">{{ $artwork->price ? 'Rp '.number_format((float) $artwork->price, 0, ',', '.') : 'By request' }}</span>
                <span class="text-xs uppercase tracking-[0.16em] text-zinc-500">{{ $artwork->category?->name ?: $artwork->status }}</span>
            </div>
        </div>
    </a>
</article>
