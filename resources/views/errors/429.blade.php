@php
    $locale = session('locale', config('locales.default', 'id'));

    if (in_array($locale, config('locales.available', ['id', 'en']), true)) {
        app()->setLocale($locale);
    }
@endphp

@include('errors.layout', [
    'code' => 429,
    'title' => __('chapung.errors.429_title'),
    'message' => __('chapung.errors.429_message'),
])
