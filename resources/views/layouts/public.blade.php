@php
    $siteName = site_setting('site_name', 'Chapung Art');
    $siteDescription = site_setting('site_description', 'Galeri seni, fotografi budaya, dan media kreatif Papua Selatan.');
    $siteLogo = site_setting('logo');
    $siteFavicon = site_setting('favicon');
    $siteEmail = site_setting('email', 'info@chapungart.com');
    $siteWhatsapp = site_setting('whatsapp', '6281234567890');
    $siteWhatsappNumber = preg_replace('/\D+/', '', $siteWhatsapp) ?: '6281234567890';
    $siteWhatsappUrl = 'https://wa.me/'.$siteWhatsappNumber;
    $siteAddress = site_setting('address', 'Merauke, Papua Selatan');
    $siteLogoUrl = filled($siteLogo) ? asset('storage/'.$siteLogo) : null;
    $siteFaviconUrl = filled($siteFavicon) ? asset('storage/'.$siteFavicon) : null;
    $cartCount = app(\App\Services\CartService::class)->count();
    $navItems = [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'Artwork', 'route' => 'gallery'],
        ['label' => 'Photography', 'route' => 'photography.index'],
        ['label' => 'Artists', 'route' => 'artists.index'],
        ['label' => 'News', 'route' => 'news.index'],
        ['label' => 'About', 'route' => 'about'],
        ['label' => 'Contact', 'route' => 'contact'],
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
                    <span class="grid h-10 w-10 place-items-center rounded-md border border-yellow-600/50 bg-zinc-950 text-sm font-black text-yellow-500">CA</span>
                @endif
                <span class="min-w-0">
                    <span class="block truncate text-lg font-black uppercase tracking-[0.22em] text-white sm:text-xl">{{ $siteName }}</span>
                    <span class="block text-[10px] font-bold uppercase tracking-[0.24em] text-yellow-600">Papua Selatan</span>
                </span>
            </a>

            <div class="hidden items-center gap-5 text-xs font-black uppercase tracking-[0.18em] text-zinc-300 lg:flex">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['route']) ? 'text-yellow-500' : 'hover:text-yellow-500' }}">{{ $item['label'] }}</a>
                @endforeach
            </div>

            <div class="hidden items-center gap-3 sm:flex">
                @include('partials.public.global-search', ['id' => 'global-search-desktop', 'class' => 'w-64'])
                <a href="{{ route('cart.index') }}" class="relative rounded-md border border-zinc-800 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-zinc-200 hover:border-yellow-600 hover:text-yellow-500">
                    Cart
                    @if ($cartCount > 0)
                        <span class="absolute -right-2 -top-2 grid h-5 min-w-5 place-items-center rounded-full bg-yellow-600 px-1 text-[10px] leading-none text-black">{{ $cartCount }}</span>
                    @endif
                </a>
                <a href="{{ $siteWhatsappUrl }}" target="_blank" rel="noopener" class="rounded-md bg-yellow-600 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-black hover:bg-yellow-500">WhatsApp</a>
            </div>
        </div>

        <div class="flex gap-5 overflow-x-auto border-t border-zinc-900 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-zinc-300 lg:hidden">
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}" class="shrink-0 {{ request()->routeIs($item['route']) ? 'text-yellow-500' : '' }}">{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('cart.index') }}" class="shrink-0 {{ request()->routeIs('cart.*') ? 'text-yellow-500' : '' }}">
                Cart
                @if ($cartCount > 0)
                    ({{ $cartCount }})
                @endif
            </a>
        </div>
        <div class="border-t border-zinc-900 px-4 py-3 sm:hidden">
            @include('partials.public.global-search', ['id' => 'global-search-mobile', 'class' => 'w-full'])
        </div>
    </nav>

    @if (session('toast'))
        @php($toast = session('toast'))
        <div class="fixed right-4 top-24 z-50 max-w-sm rounded-md border border-yellow-600/50 bg-zinc-950 px-4 py-3 text-sm font-bold text-white shadow-2xl shadow-black/40" role="status" aria-live="polite">
            {{ $toast['message'] ?? 'Cart updated.' }}
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
                        <span class="grid h-11 w-11 place-items-center rounded-md border border-yellow-600/50 bg-black text-sm font-black text-yellow-500">CA</span>
                    @endif
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-[0.22em] text-white">{{ $siteName }}</h2>
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-yellow-600">Papua Selatan</p>
                    </div>
                </div>
                <p class="mt-5 max-w-2xl text-sm leading-7 text-zinc-400">{{ $siteDescription }}</p>
            </div>

            <nav aria-label="Footer navigation" class="grid grid-cols-2 gap-3 text-xs font-black uppercase tracking-[0.16em] text-zinc-400 sm:grid-cols-3 lg:grid-cols-2">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" class="hover:text-yellow-500">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="text-sm leading-7 text-zinc-400 lg:text-right">
                <p class="font-bold text-white">{{ $siteAddress }}</p>
                <a href="mailto:{{ $siteEmail }}" class="text-yellow-500 hover:text-yellow-400">{{ $siteEmail }}</a>
                <div class="mt-5">
                    <a href="{{ $siteWhatsappUrl }}" target="_blank" rel="noopener" class="inline-flex rounded-md border border-yellow-600/70 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-yellow-500 hover:bg-yellow-600 hover:text-black">WhatsApp</a>
                </div>
            </div>
        </div>

        <div class="mx-auto mt-10 flex max-w-7xl flex-col gap-3 border-t border-zinc-800 pt-6 text-xs uppercase tracking-[0.16em] text-zinc-600 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} {{ $siteName }}</p>
            <p>Premium gallery and cultural archive</p>
        </div>
    </footer>
</body>
</html>
