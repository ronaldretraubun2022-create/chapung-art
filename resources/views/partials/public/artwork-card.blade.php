@php
    $price = filled($artwork->price) ? (float) $artwork->price : null;
    $loadedReviews = $artwork->relationLoaded('approvedReviews') ? $artwork->approvedReviews : collect();
    $reviewCount = (int) ($artwork->approved_reviews_count ?? $loadedReviews->count());
    $rating = $artwork->approved_reviews_avg_rating !== null
        ? (float) $artwork->approved_reviews_avg_rating
        : ($loadedReviews->isNotEmpty() ? (float) $loadedReviews->avg('rating') : null);
    $hasDiscount = $price && $artwork->is_featured;
    $discountPercent = $hasDiscount ? 12 : 0;
    $oldPrice = $hasDiscount ? round($price / (1 - ($discountPercent / 100)), -3) : null;
    $isAvailable = ($artwork->status ?? 'available') === 'available' && (int) ($artwork->stock ?? 0) > 0;
    $badge = $artwork->is_featured
        ? __('chapung.marketplace.badges.curated')
        : ($isAvailable ? __('chapung.marketplace.badges.ready') : __('chapung.marketplace.badges.unavailable'));
@endphp

<article class="ca-surface group relative flex h-full flex-col overflow-hidden transition duration-300 hover:-translate-y-0.5 hover:border-chapung-gold/80 hover:shadow-chapung-gold" data-favorite-card data-artwork-slug="{{ $artwork->slug }}">
    <div class="relative p-2 pb-0">
        <a href="{{ route('artwork.show', $artwork->slug) }}" class="block" aria-label="{{ __('chapung.marketplace.view_detail_for', ['title' => $artwork->title]) }}">
            @include('partials.public.image', [
                'path' => $artwork->thumbnail,
                'alt' => $artwork->title,
                'label' => __('chapung.types.artwork'),
                'ratio' => 'aspect-[4/5]',
                'width' => 640,
                'height' => 800,
            ])
        </a>

        @include('partials.public.favorite-button', [
            'artwork' => $artwork,
            'wrapperClass' => 'absolute right-4 top-4',
            'class' => 'grid h-9 w-9 place-items-center rounded-full border border-white/15 bg-black/70 text-white backdrop-blur transition hover:border-yellow-500 hover:text-yellow-500',
            'iconClass' => 'h-5 w-5',
        ])

        <x-public.badge class="absolute left-4 top-4 rounded-md shadow-lg shadow-black/30">
            {{ $badge }}
        </x-public.badge>

        @if ($discountPercent > 0)
            <span class="absolute bottom-3 left-4 rounded-md bg-red-700 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-white shadow-lg shadow-black/30">
                -{{ $discountPercent }}%
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-3 sm:p-4">
        <a href="{{ route('artwork.show', $artwork->slug) }}" class="min-h-11 text-sm font-black leading-snug text-white transition hover:text-chapung-gold sm:text-base" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
            {{ $artwork->title }}
        </a>

        <p class="mt-2 truncate text-xs font-bold text-zinc-400 sm:text-sm">
            {{ $artwork->artist_display_name ?: __('chapung.home.artist_fallback') }}
        </p>

        <div class="mt-3 flex items-center gap-1.5 text-xs text-zinc-400" aria-label="{{ __('chapung.marketplace.rating') }}">
            <span class="inline-flex items-center text-yellow-500" aria-hidden="true">
                @for ($star = 1; $star <= 5; $star++)
                    <x-heroicon-s-star class="h-3.5 w-3.5" />
                @endfor
            </span>
            <span class="font-black text-white">{{ $rating ? number_format($rating, 1) : '0.0' }}</span>
            <span>({{ number_format($reviewCount) }})</span>
        </div>

        <div class="mt-3 min-h-12">
            @if ($price)
                <p class="text-lg font-black text-chapung-gold sm:text-xl">Rp {{ number_format($price, 0, ',', '.') }}</p>
                @if ($oldPrice)
                    <p class="mt-1 text-xs text-zinc-500 line-through">Rp {{ number_format($oldPrice, 0, ',', '.') }}</p>
                @endif
            @else
                <p class="text-sm font-black uppercase tracking-[0.14em] text-chapung-gold">{{ __('chapung.marketplace.by_request') }}</p>
            @endif
        </div>

        <div class="mt-3 flex flex-wrap gap-2 text-[10px] font-black uppercase tracking-[0.12em] text-zinc-500">
            <span class="rounded-md border border-zinc-800 px-2 py-1">{{ $artwork->category?->name ?: __('chapung.types.artwork') }}</span>
            @if ($artwork->medium)
                <span class="rounded-md border border-zinc-800 px-2 py-1">{{ $artwork->medium }}</span>
            @endif
        </div>

        <div class="mt-auto grid gap-2 pt-4 sm:grid-cols-2">
            <a href="{{ route('artwork.show', $artwork->slug) }}" class="inline-flex items-center justify-center gap-1.5 rounded-chapung border border-chapung-line px-3 py-2.5 text-[11px] font-black uppercase tracking-[0.13em] text-zinc-200 transition hover:border-chapung-gold hover:text-chapung-gold">
                <x-heroicon-o-eye class="h-4 w-4" aria-hidden="true" />
                <span>{{ __('chapung.marketplace.view_detail') }}</span>
            </a>

            <form method="POST" action="{{ route('cart.store') }}">
                @csrf
                <input type="hidden" name="artwork_id" value="{{ $artwork->id }}">
                <input type="hidden" name="quantity" value="1">
                <button @disabled(! $isAvailable) class="inline-flex w-full items-center justify-center gap-1.5 rounded-chapung bg-chapung-gold px-3 py-2.5 text-[11px] font-black uppercase tracking-[0.13em] text-black transition hover:bg-chapung-gold-soft disabled:cursor-not-allowed disabled:bg-zinc-800 disabled:text-zinc-500">
                    <x-heroicon-o-shopping-bag class="h-4 w-4" aria-hidden="true" />
                    <span>{{ __('chapung.marketplace.add_to_cart') }}</span>
                </button>
            </form>
        </div>
    </div>
</article>
