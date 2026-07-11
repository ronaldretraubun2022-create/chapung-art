<form action="{{ route('search.index') }}" method="GET" class="relative {{ $class ?? '' }}" data-global-search data-search-url="{{ route('search.live') }}">
    <label for="{{ $id ?? 'global-search' }}" class="sr-only">{{ __('chapung.pages.search.aria') }}</label>
    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-3.5 h-4 w-4 text-zinc-500" aria-hidden="true" />
    <input
        id="{{ $id ?? 'global-search' }}"
        name="q"
        type="search"
        value="{{ request('q') }}"
        autocomplete="off"
        placeholder="{{ __('chapung.pages.search.button') }}"
        class="ca-field w-full bg-chapung-charcoal py-3 pl-10 pr-4"
        data-search-input
        aria-autocomplete="list"
        aria-expanded="false"
    >
    <div class="absolute left-0 right-0 top-[calc(100%+.5rem)] z-[60] hidden overflow-hidden rounded-chapung-lg border border-chapung-line bg-chapung-charcoal shadow-chapung-soft" data-search-panel>
        <div class="border-b border-chapung-line px-4 py-3 text-xs font-black uppercase tracking-[0.18em] text-zinc-500" data-search-status>{{ __('chapung.pages.search.type_to_search') }}</div>
        <div class="max-h-[28rem] overflow-y-auto" data-search-results></div>
        <div class="border-t border-chapung-line p-3">
            <x-public.button type="submit" full>{{ __('chapung.pages.search.view_all_results') }}</x-public.button>
        </div>
    </div>
</form>
