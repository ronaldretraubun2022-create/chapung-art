@props([
    'label' => __('chapung.common.empty'),
    'title' => __('chapung.common.empty_title'),
    'description' => __('chapung.common.empty_description'),
])

<div {{ $attributes->merge(['class' => 'rounded-chapung-lg border border-dashed border-chapung-line bg-chapung-charcoal/70 p-8 text-center sm:p-10']) }}>
    <p class="ca-eyebrow">{{ $label }}</p>
    <h3 class="mt-3 text-2xl font-black uppercase tracking-tight text-white">{{ $title }}</h3>
    <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-zinc-400">{{ $description }}</p>
    {{ $slot }}
</div>
