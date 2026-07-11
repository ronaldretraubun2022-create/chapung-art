@props([
    'as' => 'div',
    'muted' => false,
])

@if ($as === 'article')
    <article {{ $attributes->merge(['class' => $muted ? 'ca-surface-muted' : 'ca-surface']) }}>{{ $slot }}</article>
@elseif ($as === 'section')
    <section {{ $attributes->merge(['class' => $muted ? 'ca-surface-muted' : 'ca-surface']) }}>{{ $slot }}</section>
@else
    <div {{ $attributes->merge(['class' => $muted ? 'ca-surface-muted' : 'ca-surface']) }}>{{ $slot }}</div>
@endif
