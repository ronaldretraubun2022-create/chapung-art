@php
    $siteName = site_setting('site_name', 'Chapung Art');
    $siteDescription = site_setting('site_description', __('chapung.home.hero_subtitle'));
    $siteFavicon = site_setting('favicon');
    $siteFaviconUrl = filled($siteFavicon) ? asset('storage/'.$siteFavicon) : null;
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-chapung-black text-white antialiased selection:bg-chapung-gold selection:text-black">
    @include('partials.public.navigation')

    @if (session('toast'))
        @php($toast = session('toast'))
        <x-public.alert class="fixed right-4 top-24 z-50 max-w-sm" role="status" aria-live="polite">
            {{ $toast['message'] ?? __('chapung.common.cart_updated') }}
        </x-public.alert>
    @endif

    @if ($errors->any())
        <x-public.alert variant="danger" class="fixed right-4 top-24 z-50 max-w-sm" role="alert">
            {{ $errors->first() }}
        </x-public.alert>
    @endif

    <main>
        @yield('content')
    </main>

    @include('partials.public.footer')
</body>
</html>
