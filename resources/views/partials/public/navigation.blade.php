@php
    $siteName = site_setting('site_name', 'Chapung Art');
    $siteLogo = site_setting('logo');
    $siteLogoUrl = filled($siteLogo) ? asset('storage/'.$siteLogo) : null;
    $cartCount = app(\App\Services\CartService::class)->count();
    $favoriteCount = auth()->check() ? app(\App\Services\FavoriteService::class)->count(auth()->user()) : 0;
    $categoryUrl = route('artworks.index').'#catalog-filters';
    $collectionsUrl = route('home').'#collections';
    $adminUrl = null;

    if (auth()->check() && class_exists(\Filament\Facades\Filament::class)) {
        try {
            $panel = \Filament\Facades\Filament::getPanel('admin');
            $adminUrl = auth()->user()->canAccessPanel($panel) ? $panel->getUrl() : null;
        } catch (\Throwable) {
            $adminUrl = null;
        }
    }

    $navItems = collect([
        ['label' => __('chapung.nav.home'), 'url' => route('home'), 'active' => ['home'], 'icon' => 'heroicon-o-home'],
        ['label' => __('chapung.nav.artwork'), 'url' => route('artworks.index'), 'active' => ['artworks.*', 'gallery', 'artwork.*'], 'icon' => 'heroicon-o-photo'],
        ['label' => __('chapung.nav.artists'), 'url' => route('artists.index'), 'active' => ['artists.*'], 'icon' => 'heroicon-o-user-group'],
        ['label' => __('chapung.nav.categories'), 'url' => $categoryUrl, 'active' => ['gallery', 'artworks.*'], 'icon' => 'heroicon-o-squares-2x2'],
        ['label' => __('chapung.nav.collections'), 'url' => $collectionsUrl, 'active' => ['collections.*'], 'icon' => 'heroicon-o-rectangle-stack'],
        \Illuminate\Support\Facades\Route::has('news.index') ? ['label' => __('chapung.nav.news_event'), 'url' => route('news.index'), 'active' => ['news.*', 'media.*'], 'icon' => 'heroicon-o-newspaper'] : null,
    ])->filter()->values();
@endphp

<div x-data="{ mobileMenuOpen: false, searchOpen: false }" @keydown.escape.window="mobileMenuOpen = false; searchOpen = false">
    <header class="sticky top-0 z-50 border-b border-chapung-line bg-chapung-black/92 backdrop-blur-xl">
        <x-public.container class="flex min-h-[4.75rem] items-center justify-between gap-4 py-3">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3" aria-label="{{ $siteName }}">
                @if ($siteLogoUrl)
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" width="42" height="42" class="h-10 w-10 rounded-md object-cover sm:h-11 sm:w-11" loading="lazy" decoding="async">
                @else
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-chapung border border-chapung-gold/50 bg-chapung-charcoal text-chapung-gold sm:h-11 sm:w-11" aria-hidden="true">
                        <x-heroicon-o-sparkles class="h-5 w-5" />
                    </span>
                @endif
                <span class="min-w-0">
                    <span class="block truncate text-base font-black uppercase tracking-[0.18em] text-white sm:text-lg">{{ $siteName }}</span>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-[0.22em] text-chapung-gold">
                        <x-heroicon-o-map-pin class="h-3 w-3" aria-hidden="true" />{{ __('chapung.brand.region') }}
                    </span>
                </span>
            </a>

            <nav class="hidden items-center gap-4 text-xs font-black uppercase tracking-[0.15em] text-zinc-300 xl:flex" aria-label="{{ __('chapung.nav.primary') }}">
                @foreach ($navItems as $item)
                    <a href="{{ $item['url'] }}" class="inline-flex items-center gap-1.5 transition {{ request()->routeIs(...$item['active']) ? 'text-chapung-gold' : 'hover:text-chapung-gold' }}">
                        <x-dynamic-component :component="$item['icon']" class="h-4 w-4" aria-hidden="true" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center justify-end gap-2">
                <button type="button" class="grid h-11 w-11 place-items-center rounded-chapung border border-chapung-line bg-black/30 text-zinc-100 transition hover:border-chapung-gold hover:text-chapung-gold" aria-label="{{ __('chapung.pages.search.aria') }}" :aria-expanded="searchOpen.toString()" aria-controls="site-search-dialog" @click="searchOpen = true; $nextTick(() => $refs.searchInput?.focus())">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5" aria-hidden="true" />
                </button>
                <button type="button" class="grid h-11 w-11 place-items-center rounded-chapung border border-chapung-line bg-black/30 text-zinc-100 transition hover:border-chapung-gold hover:text-chapung-gold" data-theme-toggle data-label-light="{{ __('chapung.theme.switch_to_light') }}" data-label-dark="{{ __('chapung.theme.switch_to_dark') }}" aria-label="{{ __('chapung.theme.switch_to_light') }}" aria-pressed="false">
                    <x-heroicon-o-sun class="h-5 w-5" data-theme-icon-light aria-hidden="true" />
                    <x-heroicon-o-moon class="hidden h-5 w-5" data-theme-icon-dark aria-hidden="true" />
                </button>
                <a href="{{ route('cart.index') }}" class="relative grid h-11 w-11 place-items-center rounded-chapung border border-chapung-line bg-black/30 text-zinc-100 transition hover:border-chapung-gold hover:text-chapung-gold" aria-label="{{ __('chapung.nav.cart') }}">
                    <x-heroicon-o-shopping-bag class="h-5 w-5" aria-hidden="true" />
                    <span class="absolute -right-1.5 -top-1.5 grid h-5 min-w-5 place-items-center rounded-full bg-chapung-gold px-1 text-[10px] font-black leading-none text-black {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
                    <span class="sr-only">({{ $cartCount }})</span>
                </a>
                <a href="{{ auth()->check() ? route('favorites.index') : route('login') }}" class="relative hidden h-11 w-11 place-items-center rounded-chapung border border-chapung-line bg-black/30 text-zinc-100 transition hover:border-chapung-gold hover:text-chapung-gold sm:grid" aria-label="{{ __('chapung.favorites.title') }}" data-favorite-nav>
                    <x-heroicon-o-heart class="h-5 w-5" aria-hidden="true" />
                    <span class="absolute -right-1.5 -top-1.5 grid h-5 min-w-5 place-items-center rounded-full bg-chapung-gold px-1 text-[10px] font-black leading-none text-black {{ $favoriteCount > 0 ? '' : 'hidden' }}" data-favorite-count data-favorite-count-format="plain">{{ $favoriteCount }}</span>
                </a>

                <div class="hidden items-center gap-2 lg:flex">
                    @include('partials.language-switcher')
                    @if ($adminUrl)
                        <a href="{{ $adminUrl }}" class="ca-button ca-button-secondary px-4"><x-heroicon-o-shield-check class="h-4 w-4" aria-hidden="true" /><span>{{ __('chapung.nav.admin') }}</span></a>
                    @elseif (auth()->check())
                        <a href="{{ route('dashboard') }}" class="ca-button ca-button-secondary px-4"><x-heroicon-o-user-circle class="h-4 w-4" aria-hidden="true" /><span>{{ __('chapung.common.dashboard') }}</span></a>
                    @elseif (\Illuminate\Support\Facades\Route::has('login'))
                        <a href="{{ route('login') }}" class="ca-button ca-button-secondary px-4"><x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4" aria-hidden="true" /><span>{{ __('chapung.nav.login') }}</span></a>
                    @endif
                </div>

                <button type="button" class="grid h-11 w-11 place-items-center rounded-chapung border border-chapung-line bg-black/30 text-zinc-100 transition hover:border-chapung-gold hover:text-chapung-gold xl:hidden" aria-label="{{ __('chapung.nav.open_menu') }}" :aria-expanded="mobileMenuOpen.toString()" aria-controls="mobile-navigation" @click="mobileMenuOpen = true; $nextTick(() => $refs.mobileFirstLink?.focus())">
                    <x-heroicon-o-bars-3 class="h-6 w-6" aria-hidden="true" />
                </button>
            </div>
        </x-public.container>
    </header>

    <div x-cloak x-show="mobileMenuOpen" x-transition.opacity class="fixed inset-0 z-[70] bg-black/70 backdrop-blur-sm xl:hidden" @click="mobileMenuOpen = false"></div>
    <aside id="mobile-navigation" x-cloak x-show="mobileMenuOpen" x-transition class="fixed bottom-0 right-0 top-0 z-[80] flex w-full max-w-sm flex-col border-l border-chapung-line bg-chapung-ink shadow-chapung-soft xl:hidden" role="dialog" aria-modal="true" aria-label="{{ __('chapung.nav.mobile_menu') }}">
        <div class="flex items-center justify-between gap-4 border-b border-chapung-line px-5 py-4">
            <p class="text-xs font-black uppercase tracking-[0.22em] text-chapung-gold">{{ __('chapung.nav.mobile_menu') }}</p>
            <button type="button" class="grid h-10 w-10 place-items-center rounded-chapung border border-chapung-line text-zinc-100 hover:border-chapung-gold hover:text-chapung-gold" aria-label="{{ __('chapung.nav.close_menu') }}" @click="mobileMenuOpen = false"><x-heroicon-o-x-mark class="h-5 w-5" aria-hidden="true" /></button>
        </div>
        <div class="flex-1 overflow-y-auto px-5 py-5">
            <nav class="grid gap-2" aria-label="{{ __('chapung.nav.primary') }}">
                @foreach ($navItems as $item)
                    <a href="{{ $item['url'] }}" @if ($loop->first) x-ref="mobileFirstLink" @endif class="flex items-center justify-between rounded-chapung border border-chapung-line bg-black/25 px-4 py-3 text-sm font-black uppercase tracking-[0.14em] transition {{ request()->routeIs(...$item['active']) ? 'border-chapung-gold text-chapung-gold' : 'text-zinc-200 hover:border-chapung-gold hover:text-chapung-gold' }}">
                        <span class="inline-flex items-center gap-2"><x-dynamic-component :component="$item['icon']" class="h-5 w-5" aria-hidden="true" />{{ $item['label'] }}</span>
                        <x-heroicon-o-chevron-right class="h-4 w-4" aria-hidden="true" />
                    </a>
                @endforeach
            </nav>
            <div class="mt-5 grid gap-2 border-t border-chapung-line pt-5">
                <a href="{{ route('cart.index') }}" class="flex items-center justify-between rounded-chapung border border-chapung-line bg-black/25 px-4 py-3 text-sm font-black uppercase tracking-[0.14em] text-zinc-200 hover:border-chapung-gold hover:text-chapung-gold"><span class="inline-flex items-center gap-2"><x-heroicon-o-shopping-bag class="h-5 w-5" aria-hidden="true" />{{ __('chapung.nav.cart') }}</span><span class="rounded-full bg-chapung-gold px-2 py-1 text-[10px] text-black">{{ $cartCount }}</span></a>
                <a href="{{ auth()->check() ? route('favorites.index') : route('login') }}" class="flex items-center justify-between rounded-chapung border border-chapung-line bg-black/25 px-4 py-3 text-sm font-black uppercase tracking-[0.14em] text-zinc-200 hover:border-chapung-gold hover:text-chapung-gold" data-favorite-nav><span class="inline-flex items-center gap-2"><x-heroicon-o-heart class="h-5 w-5" aria-hidden="true" />{{ __('chapung.nav.favorites') }}</span><span class="rounded-full bg-chapung-gold px-2 py-1 text-[10px] text-black {{ $favoriteCount > 0 ? '' : 'hidden' }}" data-favorite-count data-favorite-count-format="plain">{{ $favoriteCount }}</span></a>
            </div>
            <div class="mt-5 flex flex-wrap gap-2 border-t border-chapung-line pt-5">
                @include('partials.language-switcher')
                <button type="button" class="inline-flex min-h-11 items-center gap-2 rounded-full border border-chapung-line px-3 py-2 text-xs font-black uppercase tracking-[0.14em] text-zinc-300 hover:border-chapung-gold hover:text-chapung-gold" data-theme-toggle data-label-light="{{ __('chapung.theme.switch_to_light') }}" data-label-dark="{{ __('chapung.theme.switch_to_dark') }}" aria-label="{{ __('chapung.theme.switch_to_light') }}" aria-pressed="false">
                    <x-heroicon-o-sun class="h-4 w-4" data-theme-icon-light aria-hidden="true" />
                    <x-heroicon-o-moon class="hidden h-4 w-4" data-theme-icon-dark aria-hidden="true" />
                    <span data-theme-label>{{ __('chapung.theme.switch_to_light') }}</span>
                </button>
            </div>
        </div>
        <div class="border-t border-chapung-line p-5">
            @if ($adminUrl)
                <a href="{{ $adminUrl }}" class="ca-button ca-button-primary w-full"><x-heroicon-o-shield-check class="h-4 w-4" aria-hidden="true" /><span>{{ __('chapung.nav.admin') }}</span></a>
            @elseif (auth()->check())
                <a href="{{ route('dashboard') }}" class="ca-button ca-button-primary w-full"><x-heroicon-o-user-circle class="h-4 w-4" aria-hidden="true" /><span>{{ __('chapung.common.dashboard') }}</span></a>
            @elseif (\Illuminate\Support\Facades\Route::has('login'))
                <a href="{{ route('login') }}" class="ca-button ca-button-primary w-full"><x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4" aria-hidden="true" /><span>{{ __('chapung.nav.login') }}</span></a>
            @endif
        </div>
    </aside>

    <div id="site-search-dialog" x-cloak x-show="searchOpen" x-transition.opacity class="fixed inset-0 z-[90] grid place-items-start bg-black/80 px-4 py-24 backdrop-blur-md sm:place-items-center sm:py-8" role="dialog" aria-modal="true" aria-label="{{ __('chapung.pages.search.aria') }}" @click.self="searchOpen = false">
        <div class="w-full max-w-2xl rounded-chapung-lg border border-chapung-line bg-chapung-ink p-4 shadow-chapung-soft sm:p-5">
            <div class="mb-4 flex items-center justify-between gap-4"><p class="text-xs font-black uppercase tracking-[0.22em] text-chapung-gold">{{ __('chapung.pages.search.eyebrow') }}</p><button type="button" class="grid h-10 w-10 place-items-center rounded-chapung border border-chapung-line text-zinc-100 hover:border-chapung-gold hover:text-chapung-gold" aria-label="{{ __('chapung.pages.search.close') }}" @click="searchOpen = false"><x-heroicon-o-x-mark class="h-5 w-5" aria-hidden="true" /></button></div>
            @include('partials.public.global-search', ['id' => 'global-search-modal', 'class' => 'w-full', 'inputRef' => 'searchInput', 'panelMode' => 'static'])
        </div>
    </div>
</div>
