@php
    $locale = session('locale', config('locales.default', 'id'));

    if (in_array($locale, config('locales.available', ['id', 'en']), true)) {
        app()->setLocale($locale);
    }
@endphp

@include('errors.layout', [
    'code' => 419,
    'title' => __('chapung.errors.419_title'),
    'message' => __('chapung.errors.419_message'),
])
