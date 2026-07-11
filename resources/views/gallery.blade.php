@extends('layouts.public')

@php
    $siteName = site_setting('site_name', 'Chapung Art');
    $siteLogo = site_setting('logo');
    $siteLogoUrl = filled($siteLogo) ? asset('storage/'.$siteLogo) : null;
    $marketplaceRoute = route('artworks.index');
    $cartCount = app(\App\Services\CartService::class)->count();
    $favoriteCount = auth()->check() ? app(\App\Services\FavoriteService::class)->count(auth()->user()) : 0;
    $activeFilters = collect($filters ?? [])->filter(fn ($value) => filled($value));
    $filterUrl = fn (array $overrides = []) => route('artworks.index', collect(request()->query())
        ->merge($overrides)
        ->reject(fn ($value, $key) => $key === 'page' || blank($value))
        ->all());
    $typeFilters = [
        'painting' => __('chapung.marketplace.types.painting'),
        'photography' => __('chapung.marketplace.types.photography'),
        'digital' => __('chapung.marketplace.types.digital'),
        'craft' => __('chapung.marketplace.types.craft'),
        'papua' => __('chapung.marketplace.types.papua'),
    ];
    $secondaryNav = [
        ['label' => __('chapung.marketplace.secondary.painting'), 'url' => $filterUrl(['type' => 'painting'])],
        ['label' => __('chapung.marketplace.secondary.photography'), 'url' => $filterUrl(['type' => 'photography'])],
        ['label' => __('chapung.marketplace.secondary.digital'), 'url' => $filterUrl(['type' => 'digital'])],
        ['label' => __('chapung.marketplace.secondary.craft'), 'url' => $filterUrl(['type' => 'craft'])],
        ['label' => __('chapung.marketplace.secondary.papua'), 'url' => $filterUrl(['type' => 'papua'])],
        ['label' => __('chapung.marketplace.secondary.artists'), 'url' => route('artists.index')],
        ['label' => __('chapung.marketplace.secondary.news'), 'url' => route('news.index')],
    ];
@endphp

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('gallery', fallback: [
        'title' => __('chapung.pages.gallery.title').' | '.$siteName,
        'description' => __('chapung.pages.gallery.description'),
        'canonical_url' => request()->routeIs('artworks.index') ? route('artworks.index') : route('gallery'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-black/95 px-4 py-4 sm:px-6 lg:sticky lg:top-[73px] lg:z-40 lg:px-8 lg:backdrop-blur-xl">
        <div class="mx-auto grid max-w-7xl gap-4 lg:grid-cols-[auto_auto_minmax(280px,1fr)_auto] lg:items-center">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                @if ($siteLogoUrl)
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" width="44" height="44" class="h-11 w-11 rounded-md object-cover" loading="lazy" decoding="async">
                @else
                    <span class="grid h-11 w-11 place-items-center rounded-md border border-yellow-600/60 bg-zinc-950 text-yellow-500" aria-hidden="true">
                        <x-heroicon-o-sparkles class="h-5 w-5" />
                    </span>
                @endif
                <span class="min-w-0">
                    <span class="block truncate text-lg font-black uppercase tracking-[0.2em] text-white">{{ $siteName }}</span>
                    <span class="block truncate text-[10px] font-black uppercase tracking-[0.22em] text-yellow-600">{{ __('chapung.marketplace.header_label') }}</span>
                </span>
            </a>

            <a href="#catalog-filters" class="inline-flex items-center justify-center gap-2 rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-zinc-200 transition hover:border-yellow-600 hover:text-yellow-500">
                <x-heroicon-o-squares-2x2 class="h-4 w-4" aria-hidden="true" />
                <span>{{ __('chapung.marketplace.categories') }}</span>
            </a>

            <form method="GET" action="{{ $marketplaceRoute }}" data-marketplace-search class="relative">
                <label for="marketplace-search" class="sr-only">{{ __('chapung.marketplace.search_label') }}</label>
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-zinc-500" aria-hidden="true" />
                <input id="marketplace-search" name="q" value="{{ $filters['q'] ?? '' }}" autocomplete="off" placeholder="{{ __('chapung.marketplace.search_placeholder') }}" class="h-14 w-full rounded-full border border-zinc-800 bg-zinc-950 px-12 py-4 text-base font-bold text-white placeholder:text-zinc-500 focus:border-yellow-600 focus:ring-yellow-600">
                @foreach (request()->except(['q', 'page']) as $key => $value)
                    @if (filled($value))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
            </form>

            <div class="flex items-center justify-between gap-2 lg:justify-end">
                @include('partials.language-switcher')
                @if (Route::has('login'))
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="grid h-11 w-11 place-items-center rounded-full border border-zinc-800 text-zinc-200 transition hover:border-yellow-600 hover:text-yellow-500" aria-label="{{ auth()->check() ? __('chapung.common.dashboard') : __('chapung.marketplace.login') }}">
                        <x-heroicon-o-user-circle class="h-5 w-5" aria-hidden="true" />
                    </a>
                @endif
                <a href="{{ auth()->check() ? route('favorites.index') : route('login') }}" class="relative grid h-11 w-11 place-items-center rounded-full border border-zinc-800 text-zinc-200 transition hover:border-yellow-600 hover:text-yellow-500" aria-label="{{ __('chapung.favorites.title') }}" data-favorite-nav>
                    <x-heroicon-o-heart class="h-5 w-5" aria-hidden="true" />
                    <span class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-yellow-600 px-1 text-[10px] font-black text-black {{ $favoriteCount > 0 ? '' : 'hidden' }}" data-favorite-count data-favorite-count-format="plain">{{ $favoriteCount }}</span>
                </a>
                <a href="{{ route('cart.index') }}" class="relative grid h-11 w-11 place-items-center rounded-full border border-zinc-800 text-zinc-200 transition hover:border-yellow-600 hover:text-yellow-500" aria-label="{{ __('chapung.nav.cart') }}">
                    <x-heroicon-o-shopping-bag class="h-5 w-5" aria-hidden="true" />
                    @if ($cartCount > 0)
                        <span class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-yellow-600 px-1 text-[10px] font-black text-black">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </section>

    <nav class="border-b border-zinc-800 bg-zinc-950 px-4 sm:px-6 lg:px-8" aria-label="{{ __('chapung.marketplace.secondary_label') }}">
        <div class="mx-auto flex max-w-7xl gap-4 overflow-x-auto py-3 text-xs font-black uppercase tracking-[0.16em] text-zinc-400">
            @foreach ($secondaryNav as $item)
                <a href="{{ $item['url'] }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-2 transition hover:bg-zinc-900 hover:text-yellow-500">
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.18),transparent_30rem),#050505] px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('chapung.pages.gallery.eyebrow') }}</p>
                <h1 class="mt-4 max-w-4xl text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">{{ __('chapung.pages.gallery.title') }}</h1>
                <p class="mt-5 max-w-3xl text-base leading-8 text-zinc-300 sm:text-lg">{{ __('chapung.pages.gallery.description') }}</p>
            </div>
            <div class="rounded-lg border border-zinc-800 bg-black/50 p-5 text-left lg:text-right">
                <p class="text-3xl font-black text-yellow-500">{{ number_format($artworks->total()) }}</p>
                <p class="mt-1 text-xs font-black uppercase tracking-[0.18em] text-zinc-400">{{ __('chapung.marketplace.product_count') }}</p>
            </div>
        </div>
    </section>

    <section id="catalog-filters" class="border-b border-zinc-800 bg-black px-4 py-6 sm:px-6 lg:px-8">
        <form method="GET" action="{{ $marketplaceRoute }}" data-marketplace-filter class="mx-auto max-w-7xl space-y-5">
            @if (filled($filters['type'] ?? null))
                <input type="hidden" name="type" value="{{ $filters['type'] }}">
            @endif

            <div class="flex gap-2 overflow-x-auto pb-1" aria-label="{{ __('chapung.marketplace.filter_chips') }}">
                @foreach ($typeFilters as $value => $label)
                    <a href="{{ $filterUrl(['type' => (($filters['type'] ?? '') === $value ? null : $value)]) }}" class="inline-flex shrink-0 items-center gap-2 rounded-full border px-4 py-2 text-xs font-black uppercase tracking-[0.14em] transition {{ ($filters['type'] ?? '') === $value ? 'border-yellow-600 bg-yellow-600 text-black' : 'border-zinc-800 bg-zinc-950 text-zinc-300 hover:border-yellow-600 hover:text-yellow-500' }}">
                        {{ $label }}
                    </a>
                @endforeach
                <label class="inline-flex shrink-0 items-center gap-2 rounded-full border border-zinc-800 bg-zinc-950 px-4 py-2 text-xs font-black uppercase tracking-[0.14em] text-zinc-300">
                    <input name="stock" value="1" type="checkbox" @checked((bool) ($filters['stock'] ?? false)) class="rounded border-zinc-700 bg-black text-yellow-600 focus:ring-yellow-600">
                    {{ __('chapung.marketplace.filters.stock') }}
                </label>
                <label class="inline-flex shrink-0 items-center gap-2 rounded-full border border-zinc-800 bg-zinc-950 px-4 py-2 text-xs font-black uppercase tracking-[0.14em] text-zinc-300">
                    <input name="limited" value="1" type="checkbox" @checked((bool) ($filters['limited'] ?? false)) class="rounded border-zinc-700 bg-black text-yellow-600 focus:ring-yellow-600">
                    {{ __('chapung.marketplace.filters.limited') }}
                </label>
                <label class="inline-flex shrink-0 items-center gap-2 rounded-full border border-zinc-800 bg-zinc-950 px-4 py-2 text-xs font-black uppercase tracking-[0.14em] text-zinc-300">
                    <input name="downloadable" value="1" type="checkbox" @checked((bool) ($filters['downloadable'] ?? false)) class="rounded border-zinc-700 bg-black text-yellow-600 focus:ring-yellow-600">
                    {{ __('chapung.marketplace.filters.downloadable') }}
                </label>
                <label class="inline-flex shrink-0 items-center gap-2 rounded-full border border-zinc-800 bg-zinc-950 px-4 py-2 text-xs font-black uppercase tracking-[0.14em] text-zinc-300">
                    <input name="customizable" value="1" type="checkbox" @checked((bool) ($filters['customizable'] ?? false)) class="rounded border-zinc-700 bg-black text-yellow-600 focus:ring-yellow-600">
                    {{ __('chapung.marketplace.filters.customizable') }}
                </label>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1.1fr_.9fr_.9fr_.8fr_.8fr_.8fr_.8fr_auto]">
                <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('chapung.filters.search_artwork') }}" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white placeholder:text-zinc-500 focus:border-yellow-600 focus:ring-yellow-600">
                <select name="category" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                    <option value="">{{ __('chapung.filters.all_categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="artist" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                    <option value="">{{ __('chapung.filters.all_artists') }}</option>
                    @foreach ($artists as $artist)
                        <option value="{{ $artist->id }}" @selected(($filters['artist'] ?? '') == $artist->id)>{{ $artist->name }}</option>
                    @endforeach
                </select>
                <input name="price_min" value="{{ $filters['price_min'] ?? '' }}" inputmode="numeric" placeholder="{{ __('chapung.marketplace.filters.price_min') }}" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white placeholder:text-zinc-500 focus:border-yellow-600 focus:ring-yellow-600">
                <input name="price_max" value="{{ $filters['price_max'] ?? '' }}" inputmode="numeric" placeholder="{{ __('chapung.marketplace.filters.price_max') }}" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white placeholder:text-zinc-500 focus:border-yellow-600 focus:ring-yellow-600">
                <select name="location" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                    <option value="">{{ __('chapung.marketplace.filters.location') }}</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location }}" @selected(($filters['location'] ?? '') === $location)>{{ $location }}</option>
                    @endforeach
                </select>
                <select name="rating" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                    <option value="">{{ __('chapung.marketplace.filters.rating') }}</option>
                    @for ($rating = 5; $rating >= 1; $rating--)
                        <option value="{{ $rating }}" @selected(($filters['rating'] ?? '') == $rating)> {{ $rating }}+ </option>
                    @endfor
                </select>
                <div class="flex gap-3 md:col-span-2 xl:col-span-1">
                    <button class="flex-1 rounded-md bg-yellow-600 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">{{ __('chapung.filters.filter') }}</button>
                    <a href="{{ route('artworks.index') }}" class="rounded-md border border-zinc-800 px-5 py-3 text-xs font-black uppercase tracking-[0.18em] text-zinc-300 hover:border-yellow-600 hover:text-yellow-500">{{ __('chapung.filters.reset') }}</a>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-[1fr_1fr_1fr_auto]">
                <select name="collection" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                    <option value="">{{ __('chapung.filters.all_collections') }}</option>
                    @foreach ($collections as $collection)
                        <option value="{{ $collection->id }}" @selected(($filters['collection'] ?? '') == $collection->id)>{{ $collection->name }}</option>
                    @endforeach
                </select>
                <select name="tag" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                    <option value="">{{ __('chapung.filters.all_tags') }}</option>
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}" @selected(($filters['tag'] ?? '') == $tag->id)>{{ $tag->name }}</option>
                    @endforeach
                </select>
                <select name="sort" class="rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                    <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest' || ($filters['sort'] ?? '') === '')>{{ __('chapung.filters.newest') }}</option>
                    <option value="popular" @selected(($filters['sort'] ?? '') === 'popular')>{{ __('chapung.marketplace.sort.popular') }}</option>
                    <option value="rating" @selected(($filters['sort'] ?? '') === 'rating')>{{ __('chapung.marketplace.sort.rating') }}</option>
                    <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>{{ __('chapung.filters.oldest') }}</option>
                    <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('chapung.filters.price_low') }}</option>
                    <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('chapung.filters.price_high') }}</option>
                </select>
                <label class="flex items-center gap-3 rounded-md border border-zinc-800 bg-zinc-950 px-4 py-3 text-sm text-white">
                    <input name="featured" value="1" type="checkbox" @checked(($filters['featured'] ?? false) || ($filters['sort'] ?? '') === 'featured') class="rounded border-zinc-700 bg-black text-yellow-600 focus:ring-yellow-600">
                    <span class="text-xs font-black uppercase tracking-[0.14em] text-zinc-300">{{ __('chapung.filters.featured') }}</span>
                </label>
            </div>

            @if ($activeFilters->isNotEmpty())
                <div class="flex flex-wrap gap-2 pt-1 text-xs font-bold text-zinc-400">
                    <span class="py-2 uppercase tracking-[0.14em] text-zinc-500">{{ __('chapung.marketplace.active_filters') }}</span>
                    @foreach ($activeFilters as $key => $value)
                        <span class="rounded-full border border-yellow-600/40 bg-yellow-600/10 px-3 py-2 text-yellow-500">{{ str_replace('_', ' ', (string) $key) }}: {{ is_bool($value) ? '1' : $value }}</span>
                    @endforeach
                </div>
            @endif
        </form>
    </section>

    <section class="px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-black uppercase tracking-tight text-white">{{ __('chapung.marketplace.results_title') }}</h2>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('chapung.marketplace.results_count', ['count' => number_format($artworks->total())]) }}</p>
                </div>
            </div>

            <div data-catalog-skeleton class="hidden grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 xl:grid-cols-4" aria-hidden="true">
                @for ($index = 0; $index < 8; $index++)
                    <div class="animate-pulse rounded-lg border border-zinc-800 bg-zinc-950 p-2">
                        <div class="aspect-[4/5] rounded-md bg-zinc-900"></div>
                        <div class="mt-4 h-4 rounded bg-zinc-900"></div>
                        <div class="mt-2 h-3 w-2/3 rounded bg-zinc-900"></div>
                        <div class="mt-4 h-8 rounded bg-zinc-900"></div>
                    </div>
                @endfor
            </div>

            <div data-catalog-results>
                @if ($artworks->count())
                    <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 xl:grid-cols-4">
                        @foreach ($artworks as $artwork)
                            @include('partials.public.artwork-card', ['artwork' => $artwork])
                        @endforeach
                    </div>
                    <div class="mt-10">{{ $artworks->links() }}</div>
                @else
                    @include('partials.public.empty-state', ['label' => __('chapung.types.artwork'), 'title' => __('chapung.pages.gallery.empty')])
                @endif
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const showSkeleton = () => {
                document.querySelector('[data-catalog-skeleton]')?.classList.remove('hidden');
                document.querySelector('[data-catalog-skeleton]')?.classList.add('grid');
                document.querySelector('[data-catalog-results]')?.classList.add('opacity-40', 'pointer-events-none');
            };

            document.querySelectorAll('[data-marketplace-search]').forEach((form) => {
                const input = form.querySelector('input[name="q"]');
                let timer;

                if (! input) {
                    return;
                }

                input.addEventListener('input', () => {
                    window.clearTimeout(timer);
                    timer = window.setTimeout(() => {
                        showSkeleton();
                        form.requestSubmit();
                    }, 450);
                });

                form.addEventListener('submit', showSkeleton);
            });

            document.querySelectorAll('[data-marketplace-filter]').forEach((form) => {
                form.addEventListener('submit', showSkeleton);
                form.querySelectorAll('select, input[type="checkbox"]').forEach((field) => {
                    field.addEventListener('change', () => {
                        showSkeleton();
                        form.requestSubmit();
                    });
                });
            });
        });
    </script>
@endsection
