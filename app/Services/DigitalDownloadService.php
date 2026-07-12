<?php

namespace App\Services;

use App\Models\Artwork;
use App\Models\OrderItem;
use App\Models\User;
use Filament\Forms\Components\BaseFileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DigitalDownloadService
{
    /**
     * @return array<int, string>
     */
    public static function allowedMimeTypes(): array
    {
        return config('chapung.digital_download.allowed_mime_types', [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function extensionMap(): array
    {
        return config('chapung.digital_download.extensions', [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ]);
    }

    public static function maxKilobytes(): int
    {
        return max(1, (int) config('chapung.digital_download.max_kb', 51200));
    }

    public function hasDownload(Artwork $artwork): bool
    {
        return $artwork->digital_download_enabled && filled($artwork->digital_file_path);
    }

    public function canDownload(Artwork $artwork, ?User $user): bool
    {
        if (! $user || ! $this->hasDownload($artwork)) {
            return false;
        }

        if ($this->userCanManageArtwork($user, $artwork)) {
            return true;
        }

        return $this->downloadableOrderItem($artwork, $user) !== null;
    }

    public function downloadableOrderItem(Artwork $artwork, User $user): ?OrderItem
    {
        return OrderItem::query()
            ->where('product_type', 'artwork')
            ->where('product_id', $artwork->id)
            ->whereHas('order', function ($query) use ($user): void {
                $query->where('customer_email', $user->email)
                    ->where(function ($query): void {
                        $query->where('payment_status', 'paid')
                            ->orWhereIn('status', ['shipped', 'completed']);
                    });
            })
            ->latest('id')
            ->first();
    }

    public function fileExists(Artwork $artwork): bool
    {
        return $this->hasDownload($artwork)
            && Storage::disk('local')->exists((string) $artwork->digital_file_path);
    }

    public function downloadResponse(Artwork $artwork): StreamedResponse
    {
        return Storage::disk('local')->download(
            (string) $artwork->digital_file_path,
            $this->downloadFilename($artwork),
            [
                'Cache-Control' => 'no-store, private',
                'Pragma' => 'no-cache',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    public function downloadFilename(Artwork $artwork): string
    {
        $sourceName = $artwork->digital_file_name ?: $artwork->title ?: 'chapung-art-digital';
        $baseName = pathinfo($sourceName, PATHINFO_FILENAME) ?: $sourceName;
        $extension = strtolower(pathinfo((string) $artwork->digital_file_path, PATHINFO_EXTENSION));
        $extension = in_array($extension, array_values(self::extensionMap()), true) ? $extension : 'pdf';

        return (Str::slug($baseName) ?: 'chapung-art-digital').'.'.$extension;
    }

    public function uniqueFileName(UploadedFile $file): string
    {
        $mimeType = $this->detectedMimeType($file) ?: $file->getMimeType();
        $extension = self::extensionMap()[$mimeType] ?? self::extensionMap()[$file->getMimeType()] ?? null;

        if (! $extension) {
            throw ValidationException::withMessages([
                'file' => 'Only PDF, JPEG, PNG, and WebP files are allowed.',
            ]);
        }

        return Str::uuid().'.'.$extension;
    }

    public function storeFilamentUpload(BaseFileUpload $component, TemporaryUploadedFile $file): ?string
    {
        $this->assertSafeFile($file);

        if (! $file->exists()) {
            return null;
        }

        try {
            $path = $file->storeAs(
                $component->getDirectory(),
                $component->getUploadedFileNameForStorage($file),
                $component->getDiskName(),
            );
        } catch (Throwable) {
            $path = null;
        }

        if (! is_string($path) || $path === '') {
            Log::warning('Chapung Art digital upload failed.', [
                'event' => 'filament_digital_upload',
                'disk' => $component->getDiskName(),
                'directory' => $component->getDirectory(),
            ]);

            throw ValidationException::withMessages([
                'file' => 'The uploaded digital file could not be stored. Please try again.',
            ]);
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        Storage::disk('local')->delete(ltrim((string) $path, '/'));
    }

    public function assertSafeFile(UploadedFile $file): void
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

        if (! in_array($extension, array_values(self::extensionMap()), true)) {
            throw ValidationException::withMessages([
                'file' => 'Only PDF, JPEG, PNG, and WebP files are allowed.',
            ]);
        }

        $mimeType = $this->detectedMimeType($file);

        if (! in_array($mimeType, self::allowedMimeTypes(), true)) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file MIME type is not allowed.',
            ]);
        }

        if ($this->containsExecutableSignature($file) || ! $this->matchesExpectedFileSignature($file, (string) $mimeType)) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded digital file is invalid.',
            ]);
        }
    }

    private function userCanManageArtwork(User $user, Artwork $artwork): bool
    {
        try {
            return $user->isSuperAdmin()
                || $user->isLegacyAdminWithoutRoles()
                || Gate::forUser($user)->allows('view', $artwork);
        } catch (Throwable) {
            return false;
        }
    }

    private function detectedMimeType(UploadedFile $file): ?string
    {
        $path = $file->getRealPath();

        return $path ? (new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: null : null;
    }

    private function containsExecutableSignature(UploadedFile $file): bool
    {
        $handle = fopen((string) $file->getRealPath(), 'rb');

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

    private function matchesExpectedFileSignature(UploadedFile $file, string $mimeType): bool
    {
        $path = (string) $file->getRealPath();

        if (str_starts_with($mimeType, 'image/')) {
            return @getimagesize($path) !== false;
        }

        if ($mimeType === 'application/pdf') {
            $handle = fopen($path, 'rb');

            if (! $handle) {
                return false;
            }

            $contents = fread($handle, 16) ?: '';
            fclose($handle);

            return str_starts_with(ltrim($contents), '%PDF-');
        }

        return false;
    }
}
