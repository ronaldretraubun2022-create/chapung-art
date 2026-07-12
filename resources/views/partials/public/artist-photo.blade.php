@php
    use App\Services\ImageUploadService;
    use Illuminate\Support\Facades\Storage;

    $path = $path ?? null;
    $alt = ImageUploadService::altText($alt ?? null, __('chapung.types.artist'));
    $ratio = $ratio ?? 'aspect-[4/5]';
    $width = $width ?? 800;
    $height = $height ?? 1000;
    $loading = $loading ?? 'lazy';
    $sizes = $sizes ?? '(min-width: 1280px) 25vw, (min-width: 768px) 50vw, 100vw';
    $normalizedPath = ImageUploadService::normalizePath($path);
    $hasPhoto = filled($normalizedPath) && Storage::disk('public')->exists($normalizedPath);
@endphp

<div
    class="relative {{ $ratio }} overflow-hidden rounded-md bg-[radial-gradient(circle_at_top,rgba(200,155,60,.14),transparent_18rem),#111113]"
    data-artist-photo-state="{{ $hasPhoto ? 'image' : 'placeholder' }}"
    @if (! $hasPhoto) aria-hidden="true" @endif
>
    @if ($hasPhoto)
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
