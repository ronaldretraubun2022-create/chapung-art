@props([
    'items' => 4,
])

<div {{ $attributes->merge(['class' => 'grid gap-4 sm:grid-cols-2 lg:grid-cols-4']) }} aria-hidden="true">
    @for ($index = 0; $index < $items; $index++)
        <div class="rounded-chapung-lg border border-chapung-line bg-chapung-charcoal p-2">
            <div class="ca-skeleton aspect-[4/5]"></div>
            <div class="ca-skeleton mt-4 h-4"></div>
            <div class="ca-skeleton mt-2 h-3 w-2/3"></div>
            <div class="ca-skeleton mt-4 h-9"></div>
        </div>
    @endfor
</div>
