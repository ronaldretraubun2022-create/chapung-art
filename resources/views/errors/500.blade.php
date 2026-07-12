@php
    $locale = session('locale', config('locales.default', 'id'));

    if (in_array($locale, config('locales.available', ['id', 'en']), true)) {
        app()->setLocale($locale);
    }
@endphp

@include('errors.layout', [
    'code' => 500,
    'title' => __('chapung.errors.500_title'),
    'message' => __('chapung.errors.500_message'),
])
