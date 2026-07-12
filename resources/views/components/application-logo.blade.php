@props([
    'variant' => 'normal',
    'width' => 108,
    'height' => 135,
])

<x-brand-logo
    :variant="$variant"
    :width="$width"
    :height="$height"
    {{ $attributes }}
/>
