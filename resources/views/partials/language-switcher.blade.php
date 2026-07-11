@php
    $context = $context ?? 'public';
    $currentLocale = app()->getLocale();
    $availableLocales = config('locales.available', ['id', 'en']);
    $labels = config('locales.labels', []);
    $flags = config('locales.flags', []);
    $wrapperClass = $context === 'admin'
        ? 'flex items-center gap-1 px-2'
        : 'flex shrink-0 items-center gap-1';
    $linkBaseClass = $context === 'admin'
        ? 'rounded-md px-2.5 py-2 text-xs font-semibold transition'
        : 'rounded-md border px-3 py-2 text-[11px] font-black uppercase tracking-[0.14em] transition';
@endphp

<nav class="{{ $wrapperClass }}" aria-label="{{ __('chapung.language.label') }}">
    @foreach ($availableLocales as $locale)
        @php
            $isActive = $currentLocale === $locale;
            $activeClass = $context === 'admin'
                ? 'bg-primary-500 text-white dark:bg-primary-400 dark:text-gray-950'
                : 'border-yellow-600 bg-yellow-600 text-black';
            $inactiveClass = $context === 'admin'
                ? 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10'
                : 'border-zinc-800 text-zinc-300 hover:border-yellow-600 hover:text-yellow-500';
        @endphp
        <a
            href="{{ route('language.switch', $locale) }}"
            class="{{ $linkBaseClass }} {{ $isActive ? $activeClass : $inactiveClass }}"
            hreflang="{{ $locale }}"
            aria-current="{{ $isActive ? 'true' : 'false' }}"
        >
            <span aria-hidden="true">{{ $flags[$locale] ?? strtoupper($locale) }}</span>
            <span>{{ $labels[$locale] ?? strtoupper($locale) }}</span>
        </a>
    @endforeach
</nav>
