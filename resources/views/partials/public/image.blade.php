@php
    $path = $path ?? null;
    $alt = $alt ?? 'Chapung Art';
    $ratio = $ratio ?? 'aspect-[4/5]';
    $label = $label ?? 'Chapung Art';
    $normalizedPath = filled($path) ? ltrim(str_replace('%2F', '/', urldecode((string) $path)), '/') : null;
    $normalizedPath = $normalizedPath ? ltrim(preg_replace('#^storage/#', '', $normalizedPath), '/') : null;
    $imageUrl = $normalizedPath ? asset('storage/'.$normalizedPath) : null;
@endphp

<div class="relative {{ $ratio }} overflow-hidden rounded-md bg-zinc-900">
    @if ($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $alt }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
    @else
        <div class="grid h-full w-full place-items-center bg-[radial-gradient(circle_at_top,rgba(202,138,4,.22),transparent_18rem),#101010] p-6 text-center">
            <span class="text-xs font-black uppercase tracking-[0.26em] text-yellow-600">{{ $label }}</span>
        </div>
    @endif
    <span class="absolute bottom-3 right-3 rounded-md bg-black/70 px-2 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-white backdrop-blur">Papua Selatan</span>
</div>
