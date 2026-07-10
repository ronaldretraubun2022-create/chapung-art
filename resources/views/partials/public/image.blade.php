@php
    use App\Services\ImageUploadService;

    $path = $path ?? null;
    $alt = $alt ?? 'Chapung Art';
    $ratio = $ratio ?? 'aspect-[4/5]';
    $label = $label ?? 'Chapung Art';
    $width = $width ?? 800;
    $height = $height ?? 1000;
    $loading = $loading ?? 'lazy';
    $fetchPriority = $fetchPriority ?? 'auto';
    $alt = ImageUploadService::altText($alt, $label);
    $normalizedPath = ImageUploadService::normalizePath($path);
    $imageUrl = $normalizedPath ? asset('storage/'.$normalizedPath) : ImageUploadService::fallbackUrl();
@endphp

<div class="relative {{ $ratio }} overflow-hidden rounded-md bg-zinc-900">
    <img src="{{ $imageUrl }}" alt="{{ $alt }}" width="{{ $width }}" height="{{ $height }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="{{ $loading }}" decoding="async" fetchpriority="{{ $fetchPriority }}">
    <span class="absolute bottom-3 right-3 rounded-md bg-black/70 px-2 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-white backdrop-blur">Papua Selatan</span>
</div>
