@props([
    'as' => 'div',
    'size' => '7xl',
])

@php
    $maxWidth = [
        'md' => 'max-w-5xl',
        'lg' => 'max-w-6xl',
        '7xl' => 'max-w-7xl',
        'full' => 'max-w-none',
    ][$size] ?? 'max-w-7xl';
@endphp

@if ($as === 'section')
    <section {{ $attributes->merge(['class' => 'mx-auto w-full '.$maxWidth.' px-4 sm:px-6 lg:px-8']) }}>{{ $slot }}</section>
@elseif ($as === 'main')
    <main {{ $attributes->merge(['class' => 'mx-auto w-full '.$maxWidth.' px-4 sm:px-6 lg:px-8']) }}>{{ $slot }}</main>
@else
    <div {{ $attributes->merge(['class' => 'mx-auto w-full '.$maxWidth.' px-4 sm:px-6 lg:px-8']) }}>{{ $slot }}</div>
@endif
