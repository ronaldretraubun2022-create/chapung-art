@php
    $productSignal = str(collect([$photo->license ?? null, $photo->category?->name ?? null, $photo->title ?? null])->filter()->implode(' '))->lower();
    $isDigitalProduct = $productSignal->contains(['digital', 'download', 'file']);
@endphp

<article class="ca-surface group overflow-hidden p-3 transition hover:border-chapung-gold">
    <a href="{{ route('photography.show', $photo->slug) }}" class="block">
        @include('partials.public.image', ['path' => $photo->thumbnail, 'alt' => $photo->title, 'label' => __('chapung.types.photography')])
        <div class="p-2 pt-4">
            <div class="flex flex-wrap gap-2">
                <span class="ca-badge {{ $isDigitalProduct ? 'ca-badge-gold' : 'ca-badge-muted' }}">{{ $isDigitalProduct ? __('chapung.photography_product.digital') : __('chapung.photography_product.physical_print') }}</span>
                @if ($photo->status)
                    <span class="ca-badge ca-badge-muted">{{ str($photo->status)->headline() }}</span>
                @endif
            </div>
            <h3 class="mt-4 text-lg font-black uppercase tracking-tight text-white transition group-hover:text-chapung-gold">{{ $photo->title }}</h3>
            <p class="mt-2 text-sm text-zinc-400">{{ $photo->artist_display_name ?: __('chapung.home.artist_fallback') }}</p>
            <div class="mt-4 grid gap-2 text-sm text-zinc-400">
                <div class="flex justify-between gap-3"><span>{{ __('chapung.photography_product.location') }}</span><strong class="text-right text-white">{{ $photo->location ?: $photo->province ?: '-' }}</strong></div>
                <div class="flex justify-between gap-3"><span>{{ __('chapung.photography_product.license') }}</span><strong class="text-right text-chapung-gold">{{ $photo->license ?: __('chapung.marketplace.by_request') }}</strong></div>
            </div>
        </div>
    </a>
</article>
