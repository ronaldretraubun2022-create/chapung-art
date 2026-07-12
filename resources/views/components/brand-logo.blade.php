@props([
    'variant' => 'dark',
    'width' => 108,
    'height' => 135,
    'loading' => 'eager',
])

@php
    $sources = [
        'normal' => asset('images/brand/chapung-art-logo.svg'),
        'dark' => asset('images/brand/chapung-art-logo-dark.svg'),
        'icon' => asset('images/brand/chapung-art-icon.svg'),
    ];

    $source = $sources[$variant] ?? $sources['normal'];
@endphp

<img
    src="{{ $source }}"
    alt="Chapung Art"
    width="{{ $width }}"
    height="{{ $height }}"
    decoding="async"
    @if ($loading) loading="{{ $loading }}" @endif
    {{ $attributes->merge(['class' => 'block max-w-full object-contain']) }}
>
