@php
    $siteName = site_setting('site_name', 'Chapung Art');
    $siteDescription = site_setting('site_description', __('chapung.home.hero_subtitle'));
    $siteLogo = site_setting('logo');
    $siteLogoUrl = filled($siteLogo) ? asset('storage/'.$siteLogo) : null;
    $siteEmail = site_setting('email', (string) config('chapung.emails.info'));
    $sitePhone = site_setting('phone', (string) config('chapung.contact_phone'));
    $siteWhatsapp = site_setting('whatsapp', (string) config('chapung.contact_whatsapp'));
    $siteWhatsappNumber = preg_replace('/\D+/', '', $siteWhatsapp) ?: (string) config('chapung.contact_whatsapp');
    $siteWhatsappUrl = 'https://wa.me/'.$siteWhatsappNumber;
    $siteAddress = site_setting('address', (string) config('chapung.address'));
    $categoryUrl = route('artworks.index').'#catalog-filters';
    $footerCategories = \App\Support\PerformanceCache::activeCategories('artwork')->take(8);
    $footerCollections = \App\Support\PerformanceCache::activeCollections()->take(6);
    $footerItems = collect([
        ['label' => __('chapung.nav.home'), 'url' => route('home'), 'icon' => 'heroicon-o-home'],
        ['label' => __('chapung.nav.artwork'), 'url' => route('artworks.index'), 'icon' => 'heroicon-o-photo'],
        ['label' => __('chapung.nav.artists'), 'url' => route('artists.index'), 'icon' => 'heroicon-o-user-group'],
        ['label' => __('chapung.nav.categories'), 'url' => $categoryUrl, 'icon' => 'heroicon-o-squares-2x2'],
        ['label' => __('chapung.nav.collections'), 'url' => route('home').'#collections', 'icon' => 'heroicon-o-rectangle-stack'],
        \Illuminate\Support\Facades\Route::has('news.index') ? ['label' => __('chapung.nav.news_event'), 'url' => route('news.index'), 'icon' => 'heroicon-o-newspaper'] : null,
        ['label' => __('chapung.nav.photography'), 'url' => route('photography.index'), 'icon' => 'heroicon-o-camera'],
        ['label' => __('chapung.nav.about'), 'url' => route('about'), 'icon' => 'heroicon-o-information-circle'],
        ['label' => __('chapung.nav.contact'), 'url' => route('contact'), 'icon' => 'heroicon-o-envelope'],
    ])->filter()->values();
    $socialLinks = collect([
        ['label' => 'Instagram', 'url' => site_setting('instagram')],
        ['label' => 'Facebook', 'url' => site_setting('facebook')],
        ['label' => 'TikTok', 'url' => site_setting('tiktok')],
        ['label' => 'YouTube', 'url' => site_setting('youtube')],
    ])->filter(fn (array $item): bool => filled($item['url']) && str($item['url'])->startsWith(['http://', 'https://']))->values();
@endphp

<footer class="border-t border-chapung-line bg-chapung-ink py-12 sm:py-14">
    <x-public.container class="grid gap-10 lg:grid-cols-[1.2fr_.75fr_.8fr_.9fr]">
        <div>
            <div class="flex items-center gap-3">
                @if ($siteLogoUrl)
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" width="44" height="44" class="h-11 w-11 rounded-md object-cover" loading="lazy" decoding="async">
                @else
                    <span class="grid h-11 w-11 place-items-center rounded-chapung border border-chapung-gold/50 bg-black text-chapung-gold" aria-hidden="true"><x-heroicon-o-sparkles class="h-5 w-5" /></span>
                @endif
                <div>
                    <h2 class="text-lg font-black uppercase tracking-[0.18em] text-white sm:text-xl">{{ $siteName }}</h2>
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-chapung-gold">{{ __('chapung.brand.region') }}</p>
                </div>
            </div>
            <p class="mt-5 max-w-2xl text-sm leading-7 text-zinc-400">{{ $siteDescription }}</p>
            @if ($socialLinks->isNotEmpty())
                <div class="mt-5 flex flex-wrap gap-2" aria-label="{{ __('chapung.footer.social') }}">
                    @foreach ($socialLinks as $social)
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener nofollow" class="rounded-full border border-chapung-line px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-zinc-300 transition hover:border-chapung-gold hover:text-chapung-gold">{{ $social['label'] }}</a>
                    @endforeach
                </div>
            @endif
        </div>

        <nav aria-label="{{ __('chapung.footer.main_menu') }}" class="grid content-start gap-3 text-xs font-black uppercase tracking-[0.15em] text-zinc-400">
            <h3 class="mb-1 text-[10px] tracking-[0.24em] text-chapung-gold">{{ __('chapung.footer.main_menu') }}</h3>
            @foreach ($footerItems as $item)
                <a href="{{ $item['url'] }}" class="inline-flex items-center gap-1.5 hover:text-chapung-gold"><x-dynamic-component :component="$item['icon']" class="h-4 w-4" aria-hidden="true" /><span>{{ $item['label'] }}</span></a>
            @endforeach
        </nav>

        <nav aria-label="{{ __('chapung.footer.categories') }}" class="grid content-start gap-3 text-sm text-zinc-400">
            <h3 class="mb-1 text-[10px] font-black uppercase tracking-[0.24em] text-chapung-gold">{{ __('chapung.footer.categories') }}</h3>
            @forelse ($footerCategories as $category)
                <a href="{{ route('artworks.index', ['category' => $category->id]) }}" class="hover:text-chapung-gold">{{ $category->name }}</a>
            @empty
                <a href="{{ $categoryUrl }}" class="hover:text-chapung-gold">{{ __('chapung.nav.categories') }}</a>
            @endforelse
            @if ($footerCollections->isNotEmpty())
                <h3 class="mt-5 text-[10px] font-black uppercase tracking-[0.24em] text-chapung-gold">{{ __('chapung.nav.collections') }}</h3>
                @foreach ($footerCollections as $collection)
                    <a href="{{ route('collections.show', $collection->slug) }}" class="hover:text-chapung-gold">{{ $collection->name }}</a>
                @endforeach
            @endif
        </nav>

        <div class="text-sm leading-7 text-zinc-400 lg:text-right">
            <h3 class="mb-2 text-[10px] font-black uppercase tracking-[0.24em] text-chapung-gold">{{ __('chapung.footer.contact') }}</h3>
            <p class="whitespace-pre-line font-bold text-white">{{ $siteAddress }}</p>
            <div class="mt-4 grid gap-1">
                <a href="mailto:{{ $siteEmail }}" class="text-chapung-gold hover:text-chapung-gold-soft">{{ $siteEmail }}</a>
                <a href="tel:{{ preg_replace('/\D+/', '', $sitePhone) }}" class="text-zinc-300 hover:text-chapung-gold">{{ $sitePhone }}</a>
            </div>
            <div class="mt-5">
                <a href="{{ $siteWhatsappUrl }}" target="_blank" rel="noopener" class="ca-button ca-button-secondary border-chapung-gold/70 text-chapung-gold hover:bg-chapung-gold hover:text-black"><x-heroicon-o-chat-bubble-left-right class="h-4 w-4" aria-hidden="true" /><span>{{ __('chapung.nav.whatsapp') }}</span></a>
            </div>
        </div>
    </x-public.container>

    <x-public.container class="mt-10 flex flex-col gap-3 border-t border-chapung-line pt-6 text-xs uppercase tracking-[0.14em] text-zinc-600 sm:flex-row sm:items-center sm:justify-between">
        <p>&copy; {{ now()->year }} {{ $siteName }}. {{ __('chapung.footer.rights') }}</p>
        <p>{{ __('chapung.brand.footer_tagline') }}</p>
    </x-public.container>
</footer>
