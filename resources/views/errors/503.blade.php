@php
    $locale = session('locale', config('locales.default', 'id'));

    if (in_array($locale, config('locales.available', ['id', 'en']), true)) {
        app()->setLocale($locale);
    }
@endphp

@include('errors.layout', [
    'code' => 503,
    'title' => __('chapung.errors.503_title'),
    'message' => __('chapung.errors.503_message'),
])
