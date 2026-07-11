@php
    use App\Services\ImageUploadService;
    use App\Support\VideoEmbed;

    $type = strtolower((string) ($media->file_type ?? 'image'));
    $path = (string) ($media->file_path ?? '');
    $title = ImageUploadService::altText($media->title ?: $media->alt_text, __('chapung.types.news'));
    $isVideo = str_contains($type, 'video') || str_contains($path, 'youtube.com') || str_contains($path, 'youtu.be');
    $embedUrl = $isVideo ? VideoEmbed::youtubeNoCookieUrl($path) : null;
    $normalizedPath = ImageUploadService::normalizePath($path);
    $videoUrl = str($path)->startsWith(['http://', 'https://']) ? $path : ($normalizedPath ? asset('storage/'.$normalizedPath) : null);
@endphp

<figure class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 shadow-xl shadow-black/20">
    @if ($isVideo && $embedUrl)
        <div class="aspect-video bg-black">
            <iframe src="{{ $embedUrl }}" title="{{ $title }}" width="1280" height="720" class="h-full w-full" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
        </div>
    @elseif ($isVideo && $videoUrl)
        <video controls preload="metadata" class="aspect-video h-full w-full bg-black object-contain">
            <source src="{{ $videoUrl }}">
        </video>
    @else
        @include('partials.public.image', ['path' => $path, 'alt' => $title, 'ratio' => 'aspect-[16/10]', 'label' => __('chapung.types.news'), 'width' => 960, 'height' => 600])
    @endif

    @if ($media->title || $media->alt_text)
        <figcaption class="border-t border-zinc-800 px-4 py-3 text-xs leading-6 text-zinc-400">
            {{ $media->title ?: $media->alt_text }}
        </figcaption>
    @endif
</figure>
