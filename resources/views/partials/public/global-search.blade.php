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
        class="w-full rounded-md border border-zinc-800 bg-zinc-950 py-3 pl-10 pr-4 text-sm text-white placeholder:text-zinc-500 focus:border-yellow-600 focus:ring-yellow-600"
        data-search-input
        aria-autocomplete="list"
        aria-expanded="false"
    >
    <div class="absolute left-0 right-0 top-[calc(100%+.5rem)] z-[60] hidden overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 shadow-2xl shadow-black/50" data-search-panel>
        <div class="border-b border-zinc-800 px-4 py-3 text-xs font-black uppercase tracking-[0.18em] text-zinc-500" data-search-status>{{ __('chapung.pages.search.type_to_search') }}</div>
        <div class="max-h-[28rem] overflow-y-auto" data-search-results></div>
        <div class="border-t border-zinc-800 p-3">
            <button type="submit" class="w-full rounded-md bg-yellow-600 px-4 py-3 text-xs font-black uppercase tracking-[0.16em] text-black hover:bg-yellow-500">{{ __('chapung.pages.search.view_all_results') }}</button>
        </div>
    </div>
</form>
