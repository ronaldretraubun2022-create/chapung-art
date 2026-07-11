@php
    use App\Services\ImageUploadService;

    $location = collect([$artist->origin_area ?? null, $artist->city ?? null, $artist->province ?? null])
        ->filter()
        ->unique()
        ->implode(', ');
    $summary = $artist->specialization ?: ($location ?: __('chapung.artist_store.location_fallback'));
@endphp

<article class="ca-surface group overflow-hidden p-3 transition hover:border-chapung-gold focus-within:border-chapung-gold">
    <a href="{{ route('artists.show', $artist->slug) }}" class="block rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-chapung-gold">
        @if ($artist->photo)
            @include('partials.public.image', [
                'path' => $artist->photo,
                'alt' => $artist->name,
                'label' => __('chapung.types.artist'),
                'ratio' => 'aspect-[4/5]',
                'width' => 720,
                'height' => 900,
            ])
        @else
            <div class="grid aspect-[4/5] place-items-center rounded-md border border-chapung-line bg-[radial-gradient(circle_at_top,rgba(200,155,60,.2),transparent_18rem),#111113] text-5xl font-black uppercase text-chapung-gold">
                {{ str($artist->name)->substr(0, 2)->upper() }}
            </div>
        @endif

        <div class="p-2 pt-4">
            <div class="flex flex-wrap items-center gap-2">
                @if ($artist->is_featured ?? false)
                    <span class="ca-badge ca-badge-gold">{{ __('chapung.home.featured_artist') }}</span>
                @endif
                @if ($location)
                    <span class="ca-badge ca-badge-muted">{{ $location }}</span>
                @endif
            </div>
            <h2 class="mt-4 text-xl font-black uppercase leading-tight text-white transition group-hover:text-chapung-gold">{{ $artist->name }}</h2>
            <p class="mt-2 line-clamp-2 text-sm leading-6 text-zinc-400">{{ $summary }}</p>
            <p class="mt-4 text-xs font-black uppercase tracking-[0.16em] text-chapung-gold">
                {{ number_format((int) ($artist->artworks_count ?? 0)) }} {{ __('chapung.pages.artists.artworks') }} / {{ number_format((int) ($artist->photographies_count ?? 0)) }} {{ __('chapung.pages.artists.photos') }}
            </p>
        </div>
    </a>
</article>
