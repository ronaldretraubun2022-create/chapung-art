<?php

namespace App\Support;

use App\Services\ImageUploadService;

class UploadSecurity
{
    public const IMAGE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public const PDF_MIME_TYPES = ['application/pdf'];

    public const IMAGE_MAX_KB = 4096;

    public const PDF_MAX_KB = 8192;

    /**
     * @return array<int, string>
     */
    public static function imageMimeTypes(): array
    {
        return ImageUploadService::allowedMimeTypes();
    }

    public static function imageMaxKilobytes(): int
    {
        return ImageUploadService::maxKilobytes();
    }

    /**
     * @return array<int, string>
     */
    public static function imageRules(): array
    {
        return ImageUploadService::rules();
    }

    /**
     * @return array<int, string>
     */
    public static function pdfRules(): array
    {
        return [
            'nullable',
            'file',
            'mimetypes:'.implode(',', self::PDF_MIME_TYPES),
            'max:'.self::PDF_MAX_KB,
        ];
    }
}
