<?php

return [
    'disk' => env('IMAGE_UPLOAD_DISK', 'public'),
    'max_kb' => (int) env('IMAGE_UPLOAD_MAX_KB', 4096),
    'thumbnail_width' => (int) env('IMAGE_UPLOAD_THUMBNAIL_WIDTH', 480),
    'thumbnail_height' => (int) env('IMAGE_UPLOAD_THUMBNAIL_HEIGHT', 480),
    'fallback_public_path' => env('IMAGE_UPLOAD_FALLBACK_PUBLIC_PATH', 'images/og-image.jpg'),
    'allowed_mime_types' => [
        'image/jpeg',
        'image/png',
        'image/webp',
    ],
    'extensions' => [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ],
];
