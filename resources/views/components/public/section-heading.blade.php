@props([
    'eyebrow' => null,
    'title' => null,
    'description' => null,
    'align' => 'left',
    'level' => 'h2',
])

@php
    $alignment = $align === 'center' ? 'text-center mx-auto items-center' : 'text-left items-start';
    $descriptionClass = $align === 'center' ? 'mx-auto' : '';
@endphp

<div {{ $attributes->merge(['class' => 'flex max-w-3xl flex-col gap-3 '.$alignment]) }}>
    @if ($eyebrow)
        <p class="ca-eyebrow">{{ $eyebrow }}</p>
    @endif

    @if ($title)
        @if ($level === 'h1')
            <h1 class="ca-heading-lg">{{ $title }}</h1>
        @elseif ($level === 'h3')
            <h3 class="ca-heading-lg">{{ $title }}</h3>
        @else
            <h2 class="ca-heading-lg">{{ $title }}</h2>
        @endif
    @endif

    @if ($description)
        <p class="ca-copy max-w-2xl {{ $descriptionClass }}">{{ $description }}</p>
    @endif
</div>
