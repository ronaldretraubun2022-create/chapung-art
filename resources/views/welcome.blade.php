@php
    $locale = app()->getLocale();
    $ogImage = asset('images/og-image.jpg');
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('home.hero_title') }} | {{ __('home.hero_subtitle') }}</title>
    <meta name="description" content="{{ __('home.about_text_1') }}">
    <meta property="og:title" content="{{ __('home.hero_title') }}">
    <meta property="og:description" content="{{ __('home.hero_subtitle') }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('home.hero_title') }}">
    <meta name="twitter:description" content="{{ __('home.hero_subtitle') }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-black text-white antialiased">
    <nav class="sticky top-0 z-50 border-b border-zinc-800 bg-black/85 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 lg:px-8">
            <a href="{{ route('home') }}" class="leading-tight">
                <span class="block text-xl font-black uppercase tracking-[0.28em] md:text-2xl">CHAPUNG ART</span>
                <span class="text-xs font-bold uppercase tracking-[0.28em] text-yellow-600">Merauke</span>
            </a>

            <div class="hidden items-center gap-7 text-xs font-black uppercase tracking-[0.22em] text-zinc-300 lg:flex">
                <a href="{{ route('home') }}" class="text-yellow-600">{{ __('home.nav_home') }}</a>
                <a href="{{ route('gallery') }}" class="hover:text-yellow-600">{{ __('home.nav_gallery') }}</a>
                <a href="{{ route('photography.index') }}" class="hover:text-yellow-600">{{ __('home.nav_photography') }}</a>
                <a href="{{ route('media.index') }}" class="hover:text-yellow-600">{{ __('home.nav_media') }}</a>
                <a href="#legalitas" class="hover:text-yellow-600">{{ __('home.nav_legal') }}</a>
                <a href="#contact" class="hover:text-yellow-600">Contact</a>
                <a href="{{ url('/admin') }}" class="hover:text-yellow-600">{{ __('home.nav_admin') }}</a>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-widest">
                    <a href="{{ route('lang.switch', 'id') }}" class="{{ $locale === 'id' ? 'text-yellow-600' : 'text-zinc-400 hover:text-yellow-600' }}">ID</a>
                    <span class="text-zinc-700">|</span>
                    <a href="{{ route('lang.switch', 'en') }}" class="{{ $locale === 'en' ? 'text-yellow-600' : 'text-zinc-400 hover:text-yellow-600' }}">EN</a>
                </div>
                <a href="https://wa.me/6281234567890" target="_blank" rel="noopener" class="rounded-xl bg-yellow-600 px-4 py-3 text-xs font-black uppercase tracking-widest text-black hover:bg-yellow-500">
                    {{ __('home.nav_whatsapp') }}
                </a>
            </div>
        </div>

        <div class="flex gap-5 overflow-x-auto border-t border-zinc-900 px-5 py-3 text-xs font-black uppercase tracking-[0.2em] text-zinc-300 lg:hidden">
            <a href="{{ route('home') }}" class="shrink-0 text-yellow-600">{{ __('home.nav_home') }}</a>
            <a href="{{ route('gallery') }}" class="shrink-0">{{ __('home.nav_gallery') }}</a>
            <a href="{{ route('photography.index') }}" class="shrink-0">{{ __('home.nav_photography') }}</a>
            <a href="{{ route('media.index') }}" class="shrink-0">{{ __('home.nav_media') }}</a>
            <a href="#legalitas" class="shrink-0">{{ __('home.nav_legal') }}</a>
            <a href="#contact" class="shrink-0">Contact</a>
            <a href="{{ url('/admin') }}" class="shrink-0">{{ __('home.nav_admin') }}</a>
        </div>
    </nav>

    <main>
        <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.22),transparent_34rem),linear-gradient(180deg,#050505,#09090b_65%,#000)] px-5 py-20 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <p class="mb-5 text-xs font-black uppercase tracking-[0.36em] text-yellow-600">
                    {{ __('home.location') }}
                </p>
                <h1 class="max-w-5xl text-5xl font-black uppercase leading-none tracking-tight md:text-7xl">
                    {{ __('home.hero_title') }}
                </h1>
                <p class="mt-6 max-w-3xl text-2xl font-semibold leading-9 text-zinc-200">
                    {{ __('home.hero_subtitle') }}
                </p>
                <div class="mt-10 flex flex-col gap-4 sm:flex-row">
                    <a href="{{ route('gallery') }}" class="rounded-xl bg-yellow-600 px-7 py-4 text-center text-sm font-black uppercase tracking-[0.2em] text-black hover:bg-yellow-500">{{ __('home.gallery_button') }}</a>
                    <a href="{{ route('media.index') }}" class="rounded-xl border border-yellow-600/50 px-7 py-4 text-center text-sm font-black uppercase tracking-[0.2em] text-yellow-600 hover:bg-yellow-600 hover:text-black">{{ __('home.media_button') }}</a>
                    <a href="https://wa.me/6281234567890" target="_blank" rel="noopener" class="rounded-xl border border-zinc-700 px-7 py-4 text-center text-sm font-black uppercase tracking-[0.2em] text-white hover:border-yellow-600 hover:text-yellow-600">{{ __('home.whatsapp_button') }}</a>
                </div>
            </div>
        </section>

        <section class="border-b border-zinc-800 px-5 py-16 lg:px-8">
            <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[.75fr_1.25fr]">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('home.about_title') }}</p>
                    <h2 class="mt-3 text-3xl font-black uppercase tracking-tight md:text-5xl">{{ __('home.about_heading') }}</h2>
                </div>
                <div class="space-y-5 text-base leading-8 text-zinc-300 md:text-lg">
                    <p>{{ __('home.about_text_1') }}</p>
                    <p>{{ __('home.about_text_2') }}</p>
                    <p>{{ __('home.about_text_3') }}</p>
                </div>
            </div>
        </section>

        <section id="legalitas" class="border-b border-zinc-800 bg-zinc-950 px-5 py-16 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="max-w-3xl">
                    <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('home.nav_legal') }}</p>
                    <h2 class="mt-3 text-3xl font-black uppercase tracking-tight md:text-5xl">Legalitas & Kepercayaan</h2>
                    <p class="mt-5 text-lg leading-8 text-zinc-300">
                        Chapung Art memiliki Sertifikat Pengukuhan Sanggar Seni dan Akta Notaris sebagai dasar legalitas resmi untuk ekosistem kreatif, seni, dan budaya Papua Selatan.
                    </p>
                </div>
            </div>
        </section>

        <section id="contact" class="border-b border-zinc-800 px-5 py-16 lg:px-8">
            <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[.8fr_1.2fr]">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Contact</p>
                    <h2 class="mt-3 text-3xl font-black uppercase tracking-tight md:text-5xl">Terhubung dengan Chapung Art.</h2>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <a href="https://wa.me/6281234567890" target="_blank" rel="noopener" class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5 hover:border-yellow-600">
                        <span class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">WhatsApp</span>
                        <p class="mt-3 font-bold">+62 812-3456-7890</p>
                    </a>
                    <a href="https://instagram.com/chapungart" target="_blank" rel="noopener" class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5 hover:border-yellow-600">
                        <span class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Instagram</span>
                        <p class="mt-3 font-bold">@chapungart</p>
                    </a>
                    <a href="mailto:info@chapungart.com" class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5 hover:border-yellow-600">
                        <span class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Email</span>
                        <p class="mt-3 font-bold">info@chapungart.com</p>
                    </a>
                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
                        <span class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Lokasi</span>
                        <p class="mt-3 font-bold">Merauke, Papua Selatan</p>
                    </div>
                </div>
                <div class="lg:col-start-2 flex flex-wrap gap-3">
                    <a href="https://instagram.com/chapungart" target="_blank" rel="noopener" class="rounded-xl border border-zinc-700 px-4 py-3 text-xs font-black uppercase tracking-[0.2em] hover:border-yellow-600 hover:text-yellow-600">Instagram</a>
                    <a href="https://facebook.com/chapungart" target="_blank" rel="noopener" class="rounded-xl border border-zinc-700 px-4 py-3 text-xs font-black uppercase tracking-[0.2em] hover:border-yellow-600 hover:text-yellow-600">Facebook</a>
                    <a href="https://youtube.com/@chapungart" target="_blank" rel="noopener" class="rounded-xl border border-zinc-700 px-4 py-3 text-xs font-black uppercase tracking-[0.2em] hover:border-yellow-600 hover:text-yellow-600">YouTube</a>
                    <a href="https://www.tiktok.com/@chapungart" target="_blank" rel="noopener" class="rounded-xl border border-zinc-700 px-4 py-3 text-xs font-black uppercase tracking-[0.2em] hover:border-yellow-600 hover:text-yellow-600">TikTok</a>
                </div>
            </div>
        </section>

        <section class="border-b border-zinc-800 bg-zinc-950 px-5 py-16 lg:px-8">
            <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[.9fr_1.1fr]">
                <article class="rounded-3xl border border-yellow-700/40 bg-black p-7">
                    <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('home.vision_title') }}</p>
                    <p class="mt-5 text-xl font-semibold leading-9 text-zinc-100">
                        {{ __('home.vision_text') }}
                    </p>
                </article>

                <article class="rounded-3xl border border-zinc-800 bg-black p-7">
                    <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('home.mission_title') }}</p>
                    <ul class="mt-5 grid gap-3 text-zinc-300">
                        @foreach (__('home.mission_items') as $mission)
                            <li>• {{ $mission }}</li>
                        @endforeach
                    </ul>
                </article>
            </div>
        </section>

        <section class="border-b border-zinc-800 px-5 py-16 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="mb-8 max-w-3xl">
                    <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('home.creative_focus') }}</p>
                    <h2 class="mt-3 text-3xl font-black uppercase tracking-tight md:text-5xl">{{ __('home.creative_focus_heading') }}</h2>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach (__('home.creative_focus_items') as $focus)
                        <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
                            <p class="font-black text-zinc-100">{{ $focus }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="px-5 py-16 lg:px-8">
            <div class="mx-auto max-w-7xl rounded-3xl border border-yellow-600/40 bg-[radial-gradient(circle_at_top_right,rgba(202,138,4,.22),transparent_28rem),#09090b] p-8 md:p-12">
                <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('home.quick_access') }}</p>
                <h2 class="mt-3 text-3xl font-black uppercase tracking-tight md:text-5xl">{{ __('home.quick_access_heading') }}</h2>
                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    <a href="{{ route('gallery') }}" class="rounded-xl bg-yellow-600 px-7 py-4 text-center text-sm font-black uppercase tracking-[0.2em] text-black hover:bg-yellow-500">{{ __('home.gallery_button') }}</a>
                    <a href="{{ route('media.index') }}" class="rounded-xl border border-yellow-600/50 px-7 py-4 text-center text-sm font-black uppercase tracking-[0.2em] text-yellow-600 hover:bg-yellow-600 hover:text-black">{{ __('home.media_button') }}</a>
                    <a href="https://wa.me/6281234567890" target="_blank" rel="noopener" class="rounded-xl border border-zinc-700 px-7 py-4 text-center text-sm font-black uppercase tracking-[0.2em] text-white hover:border-yellow-600 hover:text-yellow-600">{{ __('home.whatsapp_button') }}</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-zinc-800 bg-zinc-950 px-5 py-10 text-center lg:px-8">
        <h2 class="text-2xl font-black uppercase tracking-[0.3em] text-white">CHAPUNG ART</h2>
        <p class="mt-3 text-zinc-500">{{ __('home.footer_location') }}</p>
        <p class="mt-4 italic text-zinc-500">{{ __('home.footer_quote') }}</p>
    </footer>
</body>
</html>
