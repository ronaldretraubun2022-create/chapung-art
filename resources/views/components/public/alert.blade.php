@props([
    'variant' => 'info',
])

@php
    $variantClass = [
        'info' => 'border-chapung-gold/40 bg-chapung-gold/10 text-chapung-gold-soft',
        'success' => 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300',
        'danger' => 'border-red-500/40 bg-red-500/10 text-red-300',
        'muted' => 'border-chapung-line bg-black/40 text-zinc-300',
    ][$variant] ?? 'border-chapung-gold/40 bg-chapung-gold/10 text-chapung-gold-soft';
@endphp

<div {{ $attributes->merge(['class' => 'ca-alert '.$variantClass]) }}>{{ $slot }}</div>
