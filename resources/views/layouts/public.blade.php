@php
    $siteName = site_setting('site_name', 'Chapung Art');
    $siteDescription = site_setting('site_description', __('chapung.home.hero_subtitle'));
    $siteLogo = site_setting('logo');
    $siteFavicon = site_setting('favicon');
    $siteEmail = site_setting('email', (string) config('chapung.emails.info'));
    $siteWhatsapp = site_setting('whatsapp', (string) config('chapung.contact_whatsapp'));
    $siteWhatsappNumber = preg_replace('/\D+/', '', $siteWhatsapp) ?: (string) config('chapung.contact_whatsapp');
    $siteWhatsappUrl = 'https://wa.me/'.$siteWhatsappNumber;
    $siteAddress = site_setting('address', (string) config('chapung.address'));
    $siteLogoUrl = filled($siteLogo) ? asset('storage/'.$siteLogo) : null;
    $siteFaviconUrl = filled($siteFavicon) ? asset('storage/'.$siteFavicon) : null;
    $cartCount = app(\App\Services\CartService::class)->count();
    $favoriteCount = auth()->check() ? app(\App\Services\FavoriteService::class)->count(auth()->user()) : 0;
    $navItems = [
        ['label' => __('chapung.nav.home'), 'route' => 'home', 'icon' => 'heroicon-o-home'],
        ['label' => __('chapung.nav.artwork'), 'route' => 'gallery', 'icon' => 'heroicon-o-photo'],
        ['label' => __('chapung.nav.photography'), 'route' => 'photography.index', 'icon' => 'heroicon-o-camera'],
        ['label' => __('chapung.nav.artists'), 'route' => 'artists.index', 'icon' => 'heroicon-o-user-group'],
        ['label' => __('chapung.nav.news'), 'route' => 'news.index', 'icon' => 'heroicon-o-newspaper'],
        ['label' => __('chapung.nav.about'), 'route' => 'about', 'icon' => 'heroicon-o-information-circle'],
        ['label' => __('chapung.nav.contact'), 'route' => 'contact', 'icon' => 'heroicon-o-envelope'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @hasSection('seo')
        @yield('seo')
    @else
        @include('partials.seo-meta', ['seo' => seo_meta(fallback: [
            'title' => $siteName,
            'description' => $siteDescription,
            'canonical_url' => url()->current(),
        ])])
    @endif
    @if ($siteFaviconUrl)
        <link rel="icon" href="{{ $siteFaviconUrl }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-black text-white antialiased selection:bg-yellow-600 selection:text-black">
    <nav class="sticky top-0 z-50 border-b border-zinc-800 bg-black/90 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                @if ($siteLogoUrl)
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" width="40" height="40" class="h-10 w-10 rounded-md object-cover" loading="lazy" decoding="async">
                @else
                    <span class="grid h-10 w-10 place-items-center rounded-md border border-yellow-600/50 bg-zinc-950 text-yellow-500" aria-hidden="true">
                        <x-heroicon-o-sparkles class="h-5 w-5" />
                    </span>
                @endif
                <span class="min-w-0">
                    <span class="block truncate text-lg font-black uppercase tracking-[0.22em] text-white sm:text-xl">{{ $siteName }}</span>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-[0.24em] text-yellow-600">
                        <x-heroicon-o-map-pin class="h-3 w-3" aria-hidden="true" />
                        {{ __('chapung.brand.region') }}
                    </span>
                </span>
            </a>

            <div class="hidden items-center gap-5 text-xs font-black uppercase tracking-[0.18em] text-zinc-300 lg:flex">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" class="inline-flex items-center gap-1.5 {{ request()->routeIs($item['route']) ? 'text-yellow-500' : 'hover:text-yellow-500' }}">
                        <x-dynamic-component :component="$item['icon']" class="h-4 w-4" aria-hidden="true" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>

            <div class="hidden items-center gap-3 sm:flex">
                @include('partials.public.global-search', ['id' => 'global-search-desktop', 'class' => 'w-64'])
                <a href="{{ route('cart.index') }}" class="relative inline-flex items-center gap-1.5 rounded-md border border-zinc-800 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-zinc-200 hover:border-yellow-600 hover:text-yellow-500">
                    <x-heroicon-o-shopping-bag class="h-4 w-4" aria-hidden="true" />
                    <span>{{ __('chapung.nav.cart') }}</span>
                    @if ($cartCount > 0)
                        <span class="absolute -right-2 -top-2 grid h-5 min-w-5 place-items-center rounded-full bg-yellow-600 px-1 text-[10px] leading-none text-black">{{ $cartCount }}</span>
                    @endif
                </a>
                <a href="{{ auth()->check() ? route('favorites.index') : route('login') }}" class="relative inline-flex items-center gap-1.5 rounded-md border border-zinc-800 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-zinc-200 hover:border-yellow-600 hover:text-yellow-500" aria-label="{{ __('chapung.favorites.title') }}" data-favorite-nav>
                    <x-heroicon-o-heart class="h-4 w-4" aria-hidden="true" />
                    <span>{{ __('chapung.nav.favorites') }}</span>
                    <span class="absolute -right-2 -top-2 grid h-5 min-w-5 place-items-center rounded-full bg-yellow-600 px-1 text-[10px] leading-none text-black {{ $favoriteCount > 0 ? '' : 'hidden' }}" data-favorite-count data-favorite-count-format="plain">{{ $favoriteCount }}</span>
                </a>
                @include('partials.language-switcher')
                <a href="{{ $siteWhatsappUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-md bg-yellow-600 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-black hover:bg-yellow-500">
                    <x-heroicon-o-chat-bubble-left-right class="h-4 w-4" aria-hidden="true" />
                    <span>{{ __('chapung.nav.whatsapp') }}</span>
                </a>
            </div>
        </div>

        <div class="flex gap-5 overflow-x-auto border-t border-zinc-900 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-zinc-300 lg:hidden">
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}" class="inline-flex shrink-0 items-center gap-1.5 {{ request()->routeIs($item['route']) ? 'text-yellow-500' : '' }}">
                    <x-dynamic-component :component="$item['icon']" class="h-4 w-4" aria-hidden="true" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
            <a href="{{ route('cart.index') }}" class="inline-flex shrink-0 items-center gap-1.5 {{ request()->routeIs('cart.*') ? 'text-yellow-500' : '' }}">
                <x-heroicon-o-shopping-bag class="h-4 w-4" aria-hidden="true" />
                <span>{{ __('chapung.nav.cart') }}</span>
                @if ($cartCount > 0)
                    ({{ $cartCount }})
                @endif
            </a>
            <a href="{{ auth()->check() ? route('favorites.index') : route('login') }}" class="inline-flex shrink-0 items-center gap-1.5 {{ request()->routeIs('favorites.*') ? 'text-yellow-500' : '' }}" data-favorite-nav>
                <x-heroicon-o-heart class="h-4 w-4" aria-hidden="true" />
                <span>{{ __('chapung.nav.favorites') }}</span>
                <span class="{{ $favoriteCount > 0 ? '' : 'hidden' }}" data-favorite-count data-favorite-count-format="paren">({{ $favoriteCount }})</span>
            </a>
            @foreach (config('locales.available', ['id', 'en']) as $locale)
                <a href="{{ route('language.switch', $locale) }}" class="shrink-0 {{ app()->getLocale() === $locale ? 'text-yellow-500' : '' }}">
                    {{ config('locales.flags.'.$locale) }} {{ config('locales.labels.'.$locale) }}
                </a>
            @endforeach
        </div>
        <div class="border-t border-zinc-900 px-4 py-3 sm:hidden">
            @include('partials.public.global-search', ['id' => 'global-search-mobile', 'class' => 'w-full'])
        </div>
    </nav>

    @if (session('toast'))
        @php($toast = session('toast'))
        <div class="fixed right-4 top-24 z-50 max-w-sm rounded-md border border-yellow-600/50 bg-zinc-950 px-4 py-3 text-sm font-bold text-white shadow-2xl shadow-black/40" role="status" aria-live="polite">
            {{ $toast['message'] ?? __('chapung.common.cart_updated') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="fixed right-4 top-24 z-50 max-w-sm rounded-md border border-red-700/60 bg-zinc-950 px-4 py-3 text-sm font-bold text-red-200 shadow-2xl shadow-black/40" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-zinc-800 bg-zinc-950 px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1.15fr_.85fr_.75fr]">
            <div>
                <div class="flex items-center gap-3">
                    @if ($siteLogoUrl)
                        <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" width="44" height="44" class="h-11 w-11 rounded-md object-cover" loading="lazy" decoding="async">
                    @else
                        <span class="grid h-11 w-11 place-items-center rounded-md border border-yellow-600/50 bg-black text-yellow-500" aria-hidden="true">
                            <x-heroicon-o-sparkles class="h-5 w-5" />
                        </span>
                    @endif
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-[0.22em] text-white">{{ $siteName }}</h2>
                        <p class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-[0.22em] text-yellow-600">
                            <x-heroicon-o-map-pin class="h-3 w-3" aria-hidden="true" />
                            {{ __('chapung.brand.region') }}
                        </p>
                    </div>
                </div>
                <p class="mt-5 max-w-2xl text-sm leading-7 text-zinc-400">{{ $siteDescription }}</p>
            </div>

            <nav aria-label="Footer navigation" class="grid grid-cols-2 gap-3 text-xs font-black uppercase tracking-[0.16em] text-zinc-400 sm:grid-cols-3 lg:grid-cols-2">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" class="inline-flex items-center gap-1.5 hover:text-yellow-500">
                        <x-dynamic-component :component="$item['icon']" class="h-4 w-4" aria-hidden="true" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="text-sm leading-7 text-zinc-400 lg:text-right">
                <p class="font-bold text-white">{{ $siteAddress }}</p>
                <a href="mailto:{{ $siteEmail }}" class="text-yellow-500 hover:text-yellow-400">{{ $siteEmail }}</a>
                <div class="mt-5">
                    <a href="{{ $siteWhatsappUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-md border border-yellow-600/70 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-yellow-500 hover:bg-yellow-600 hover:text-black">
                        <x-heroicon-o-chat-bubble-left-right class="h-4 w-4" aria-hidden="true" />
                        <span>{{ __('chapung.nav.whatsapp') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="mx-auto mt-10 flex max-w-7xl flex-col gap-3 border-t border-zinc-800 pt-6 text-xs uppercase tracking-[0.16em] text-zinc-600 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} {{ $siteName }}</p>
            <p>{{ __('chapung.brand.footer_tagline') }}</p>
        </div>
    </footer>
</body>
</html>
