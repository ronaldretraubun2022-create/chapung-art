<?php

namespace App\Services;

use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImageUploadService
{
    /**
     * @return array<int, string>
     */
    public static function allowedMimeTypes(): array
    {
        return config('image-upload.allowed_mime_types', ['image/jpeg', 'image/png', 'image/webp']);
    }

    /**
     * @return array<string, string>
     */
    public static function extensionMap(): array
    {
        return config('image-upload.extensions', [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ]);
    }

    public static function maxKilobytes(): int
    {
        return max(1, (int) config('image-upload.max_kb', 4096));
    }

    public static function thumbnailWidth(): int
    {
        return max(1, (int) config('image-upload.thumbnail_width', 480));
    }

    public static function thumbnailHeight(): int
    {
        return max(1, (int) config('image-upload.thumbnail_height', 480));
    }

    public static function fallbackUrl(): string
    {
        return asset(config('image-upload.fallback_public_path', 'images/og-image.jpg'));
    }

    public static function normalizePath(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $path = ltrim(str_replace('%2F', '/', urldecode((string) $path)), '/');

        return ltrim((string) preg_replace('#^storage/#', '', $path), '/');
    }

    public static function thumbnailPath(string $path): string
    {
        $path = self::normalizePath($path) ?: $path;
        $directory = trim(pathinfo($path, PATHINFO_DIRNAME), '.\\/');
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $thumbnail = 'thumbnails/'.$filename.'.'.$extension;

        return $directory === '' ? $thumbnail : $directory.'/'.$thumbnail;
    }

    public static function altText(?string $value = null, ?string $fallback = null): string
    {
        $alt = trim(strip_tags((string) ($value ?: $fallback ?: 'Chapung Art')));

        return Str::limit($alt, 120, '');
    }

    public static function configureFilamentUpload(
        FileUpload $upload,
        string $directory,
        string $disk = 'public',
        string $visibility = 'public',
    ): FileUpload {
        $service = app(self::class);

        return $upload
            ->image()
            ->acceptedFileTypes(self::allowedMimeTypes())
            ->maxSize(self::maxKilobytes())
            ->disk($disk)
            ->directory($directory)
            ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file): string => $service->uniqueFileName($file))
            ->saveUploadedFileUsing(fn (BaseFileUpload $component, TemporaryUploadedFile $file): ?string => $service->storeFilamentUpload($component, $file))
            ->deleteUploadedFileUsing(function (string $file) use ($disk, $service): void {
                $service->delete($file, $disk);
            })
            ->visibility($visibility);
    }

    /**
     * @return array<int, string|\Illuminate\Contracts\Validation\ValidationRule>
     */
    public static function rules(bool $nullable = true): array
    {
        return array_filter([
            $nullable ? 'nullable' : 'required',
            'file',
            'mimetypes:'.implode(',', self::allowedMimeTypes()),
            'max:'.self::maxKilobytes(),
            function (string $attribute, mixed $value, callable $fail): void {
                if (! $value instanceof UploadedFile) {
                    return;
                }

                try {
                    app(self::class)->assertSafeImage($value);
                } catch (ValidationException $exception) {
                    $fail($exception->validator->errors()->first('file') ?: 'The uploaded image is invalid.');
                }
            },
        ]);
    }

    public function store(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $this->assertSafeImage($file);

        $path = $file->storeAs(trim($directory, '/'), $this->uniqueFileName($file), $disk);
        $this->makeThumbnail($path, $disk);

        return $path;
    }

    public function storeFilamentUpload(BaseFileUpload $component, TemporaryUploadedFile $file): ?string
    {
        $this->assertSafeImage($file);

        if (! $file->exists()) {
            return null;
        }

        $path = $file->storeAs(
            $component->getDirectory(),
            $component->getUploadedFileNameForStorage($file),
            $component->getDiskName(),
        );

        if ($component->getVisibility() === 'public') {
            rescue(fn () => $component->getDisk()->setVisibility($path, 'public'), report: false);
        }

        $this->makeThumbnail($path, $component->getDiskName());

        return $path;
    }

    public function replace(?string $oldPath, UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $newPath = $this->store($file, $directory, $disk);
        $this->delete($oldPath, $disk);

        return $newPath;
    }

    public function delete(?string $path, string $disk = 'public'): void
    {
        $path = self::normalizePath($path);

        if (! $path) {
            return;
        }

        Storage::disk($disk)->delete([$path, self::thumbnailPath($path)]);
    }

    public function uniqueFileName(UploadedFile $file): string
    {
        $mimeType = $this->detectedMimeType($file) ?: $file->getMimeType();
        $extension = self::extensionMap()[$mimeType] ?? self::extensionMap()[$file->getMimeType()] ?? null;

        if (! $extension) {
            throw ValidationException::withMessages([
                'file' => 'Only JPEG, PNG, and WebP images are allowed.',
            ]);
        }

        return Str::uuid().'.'.$extension;
    }

    public function assertSafeImage(UploadedFile $file): void
    {
        Validator::make(['file' => $file], [
            'file' => [
                'required',
                'file',
                'mimetypes:'.implode(',', self::allowedMimeTypes()),
                'max:'.self::maxKilobytes(),
            ],
        ])->validate();

        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw ValidationException::withMessages([
                'file' => 'Only JPEG, PNG, and WebP images are allowed.',
            ]);
        }

        $mimeType = $this->detectedMimeType($file);

        if (! in_array($mimeType, self::allowedMimeTypes(), true)) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file MIME type is not allowed.',
            ]);
        }

        if (@getimagesize($file->getRealPath()) === false || $this->containsExecutableSignature($file)) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded image is invalid.',
            ]);
        }
    }

    public function makeThumbnail(string $path, string $disk = 'public'): ?string
    {
        $storage = Storage::disk($disk);

        if (! method_exists($storage, 'path') || ! $storage->exists($path)) {
            return null;
        }

        $sourcePath = $storage->path($path);
        $thumbnailPath = self::thumbnailPath($path);
        $targetPath = $storage->path($thumbnailPath);

        if (! is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0755, true);
        }

        if (! extension_loaded('gd')) {
            copy($sourcePath, $targetPath);

            return $thumbnailPath;
        }

        [$width, $height, $type] = getimagesize($sourcePath) ?: [0, 0, 0];

        if ($width < 1 || $height < 1) {
            return null;
        }

        $ratio = min(self::thumbnailWidth() / $width, self::thumbnailHeight() / $height, 1);
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));
        $source = $this->createImageResource($sourcePath, $type);

        if (! $source) {
            copy($sourcePath, $targetPath);

            return $thumbnailPath;
        }

        $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        $this->saveImageResource($thumbnail, $targetPath, $type);

        imagedestroy($source);
        imagedestroy($thumbnail);

        return $thumbnailPath;
    }

    private function detectedMimeType(UploadedFile $file): ?string
    {
        $path = $file->getRealPath();

        return $path ? (new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: null : null;
    }

    private function containsExecutableSignature(UploadedFile $file): bool
    {
        $handle = fopen($file->getRealPath(), 'rb');

        if (! $handle) {
            return true;
        }

        $contents = fread($handle, 8192) ?: '';
        fclose($handle);

        $lower = strtolower($contents);

        return str_starts_with($contents, 'MZ')
            || str_contains($lower, '<?php')
            || str_contains($lower, '<script')
            || preg_match('/^#!\s*\/.*\b(sh|bash|python|perl|php)\b/i', $contents) === 1;
    }

    private function createImageResource(string $path, int $type): mixed
    {
        return match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : null,
            default => null,
        };
    }

    private function saveImageResource(mixed $image, string $path, int $type): void
    {
        match ($type) {
            IMAGETYPE_JPEG => imagejpeg($image, $path, 82),
            IMAGETYPE_PNG => imagepng($image, $path, 7),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($image, $path, 82) : imagejpeg($image, $path, 82),
            default => imagejpeg($image, $path, 82),
        };
    }
}
