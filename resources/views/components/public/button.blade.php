@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'full' => false,
])

@php
    $variantClass = [
        'primary' => 'ca-button-primary',
        'secondary' => 'ca-button-secondary',
        'ghost' => 'ca-button-ghost',
    ][$variant] ?? 'ca-button-primary';
    $widthClass = $full ? 'w-full' : '';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'ca-button '.$variantClass.' '.$widthClass]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'ca-button '.$variantClass.' '.$widthClass]) }}>{{ $slot }}</button>
@endif
