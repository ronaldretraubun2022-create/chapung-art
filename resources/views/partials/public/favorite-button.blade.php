@php
    $isFavorited = (bool) ($artwork->is_favorited ?? false);
    $buttonLabel = $isFavorited ? __('chapung.favorites.remove') : __('chapung.favorites.add');
    $buttonClass = $class ?? 'grid h-9 w-9 place-items-center rounded-full border border-white/15 bg-black/70 text-white backdrop-blur transition hover:border-yellow-500 hover:text-yellow-500';
@endphp

@auth
    <form
        method="POST"
        action="{{ $isFavorited ? route('favorites.destroy', $artwork->slug) : route('favorites.store', $artwork->slug) }}"
        data-favorite-form
        data-artwork-slug="{{ $artwork->slug }}"
        data-store-url="{{ route('favorites.store', $artwork->slug) }}"
        data-destroy-url="{{ route('favorites.destroy', $artwork->slug) }}"
        data-favorited="{{ $isFavorited ? 'true' : 'false' }}"
        data-add-label="{{ __('chapung.favorites.add') }}"
        data-remove-label="{{ __('chapung.favorites.remove') }}"
        class="{{ $wrapperClass ?? '' }}"
    >
        @csrf
        <input type="hidden" name="_method" value="{{ $isFavorited ? 'DELETE' : 'POST' }}" data-favorite-method>
        <button type="submit" class="{{ $buttonClass }} {{ $isFavorited ? 'border-yellow-500 bg-yellow-600 text-black hover:text-black' : '' }}" aria-label="{{ $buttonLabel }}" aria-pressed="{{ $isFavorited ? 'true' : 'false' }}" data-favorite-button>
            <span data-favorite-icon>
                @if ($isFavorited)
                    <x-heroicon-s-heart class="{{ $iconClass ?? 'h-5 w-5' }}" aria-hidden="true" />
                @else
                    <x-heroicon-o-heart class="{{ $iconClass ?? 'h-5 w-5' }}" aria-hidden="true" />
                @endif
            </span>
            @isset($showLabel)
                <span data-favorite-label>{{ $buttonLabel }}</span>
            @endisset
        </button>
    </form>
@else
    <a href="{{ route('login') }}" class="{{ $buttonClass }} {{ $wrapperClass ?? '' }}" aria-label="{{ __('chapung.marketplace.favorite') }}" title="{{ __('chapung.favorites.login_required') }}">
        <x-heroicon-o-heart class="{{ $iconClass ?? 'h-5 w-5' }}" aria-hidden="true" />
        @isset($showLabel)
            <span>{{ __('chapung.favorites.add') }}</span>
        @endisset
    </a>
@endauth
