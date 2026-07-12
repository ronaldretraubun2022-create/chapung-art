@php
    use App\Services\ImageUploadService;
    use Illuminate\Support\Facades\Storage;

    $collection = $collection ?? null;
    $bannerPath = $collection ? trim((string) $collection->getRawOriginal('banner_image')) : trim((string) ($bannerPath ?? ''));
    $coverPath = $collection ? trim((string) $collection->getRawOriginal('cover_image')) : trim((string) ($coverPath ?? ''));
    $path = $bannerPath !== '' ? $bannerPath : $coverPath;
    $normalizedPath = ImageUploadService::normalizePath($path);
    $hasCover = filled($normalizedPath) && Storage::disk('public')->exists($normalizedPath);
    $alt = ImageUploadService::altText($alt ?? $collection?->name ?? null, __('chapung.types.collection'));
    $ratio = $ratio ?? 'aspect-[16/11]';
    $width = $width ?? 960;
    $height = $height ?? 660;
    $loading = $loading ?? 'lazy';
    $sizes = $sizes ?? '(min-width: 1280px) 25vw, (min-width: 768px) 50vw, 100vw';
@endphp

<div
    class="relative {{ $ratio }} overflow-hidden rounded-md bg-[radial-gradient(circle_at_top_right,rgba(200,155,60,.12),transparent_18rem),#111113]"
    data-collection-cover-state="{{ $hasCover ? 'image' : 'placeholder' }}"
    @if (! $hasCover) aria-hidden="true" @endif
>
    @if ($hasCover)
        <img
            src="{{ asset('storage/'.$normalizedPath) }}"
            alt="{{ $alt }}"
            width="{{ $width }}"
            height="{{ $height }}"
            sizes="{{ $sizes }}"
            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            loading="{{ $loading }}"
            decoding="async"
            onerror="this.remove()"
        >
    @endif
</div>
