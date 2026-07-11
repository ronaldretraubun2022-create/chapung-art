@props([
    'variant' => 'gold',
])

@php
    $variantClass = [
        'gold' => 'ca-badge-gold',
        'muted' => 'ca-badge-muted',
        'danger' => 'border border-red-500/40 bg-red-500/10 text-red-300',
        'success' => 'border border-emerald-500/40 bg-emerald-500/10 text-emerald-300',
    ][$variant] ?? 'ca-badge-gold';
@endphp

<span {{ $attributes->merge(['class' => 'ca-badge '.$variantClass]) }}>{{ $slot }}</span>
