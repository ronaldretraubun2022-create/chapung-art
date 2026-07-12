@php
    $context = $context ?? 'public';
    $currentLocale = app()->getLocale();
    $availableLocales = config('locales.available', ['id', 'en']);
    $labels = config('locales.labels', []);
    $shortLabels = config('locales.short_labels', []);
    $wrapperClass = $context === 'admin'
        ? 'flex items-center gap-1 rounded-lg border border-gray-200 bg-white/80 p-1 shadow-sm dark:border-white/10 dark:bg-white/5'
        : 'flex shrink-0 items-center gap-1 rounded-chapung border border-chapung-line bg-black/25 p-1';
    $linkBaseClass = $context === 'admin'
        ? 'inline-flex min-h-9 items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-semibold transition sm:px-3'
        : 'inline-flex min-h-9 items-center gap-1 rounded-md px-2.5 py-1.5 text-[11px] font-black uppercase tracking-[0.12em] transition sm:px-3';
@endphp

<nav class="{{ $wrapperClass }}" aria-label="{{ __('chapung.language.label') }}">
    @foreach ($availableLocales as $locale)
        @php
            $isActive = $currentLocale === $locale;
            $activeClass = $context === 'admin'
                ? 'bg-primary-500 text-white shadow-sm dark:bg-primary-400 dark:text-gray-950'
                : 'bg-chapung-gold text-black shadow-sm';
            $inactiveClass = $context === 'admin'
                ? 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10'
                : 'text-zinc-300 hover:bg-white/10 hover:text-chapung-gold';
        @endphp
        <a
            href="{{ route('language.switch', $locale) }}"
            class="{{ $linkBaseClass }} {{ $isActive ? $activeClass : $inactiveClass }}"
            hreflang="{{ $locale }}"
            aria-current="{{ $isActive ? 'page' : 'false' }}"
            aria-label="{{ __('admin.language.switch_to', ['language' => $labels[$locale] ?? strtoupper($locale)]) }}"
        >
            <span aria-hidden="true">{{ $shortLabels[$locale] ?? strtoupper($locale) }}</span>
            <span class="hidden sm:inline">{{ $labels[$locale] ?? strtoupper($locale) }}</span>
            @if ($isActive)
                <span class="hidden rounded bg-black/10 px-1.5 py-0.5 text-[10px] font-bold uppercase leading-none sm:inline dark:bg-white/20">
                    {{ __('chapung.language.active') }}
                </span>
            @endif
        </a>
    @endforeach
</nav>
