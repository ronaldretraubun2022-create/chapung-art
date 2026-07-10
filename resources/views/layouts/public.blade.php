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
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" class="h-10 w-10 rounded-md object-cover" loading="lazy">
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

            <a href="{{ $siteWhatsappUrl }}" target="_blank" rel="noopener" class="hidden rounded-md bg-yellow-600 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-black hover:bg-yellow-500 sm:inline-flex">WhatsApp</a>
        </div>

        <div class="flex gap-5 overflow-x-auto border-t border-zinc-900 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-zinc-300 lg:hidden">
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}" class="shrink-0 {{ request()->routeIs($item['route']) ? 'text-yellow-500' : '' }}">{{ $item['label'] }}</a>
            @endforeach
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-zinc-800 bg-zinc-950 px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-8 md:grid-cols-[1.2fr_.8fr]">
            <div>
                <h2 class="text-2xl font-black uppercase tracking-[0.26em] text-white">{{ $siteName }}</h2>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-zinc-400">{{ $siteDescription }}</p>
            </div>
            <div class="text-sm leading-7 text-zinc-400 md:text-right">
                <p>{{ $siteAddress }}</p>
                <a href="mailto:{{ $siteEmail }}" class="text-yellow-500 hover:text-yellow-400">{{ $siteEmail }}</a>
                <p class="mt-4 text-xs uppercase tracking-[0.2em] text-zinc-600">Premium gallery and cultural archive</p>
            </div>
        </div>
    </footer>
</body>
</html>
