<?php

$splitSources = static fn (string $key): array => array_values(array_filter(array_map(
    static fn (string $source): string => trim($source),
    explode(',', (string) env($key, '')),
)));

return [
    'headers' => [
        'x_frame_options' => env('SECURITY_X_FRAME_OPTIONS', 'SAMEORIGIN'),
        'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'permissions_policy' => env('SECURITY_PERMISSIONS_POLICY', 'camera=(), microphone=(), geolocation=(), payment=()'),
    ],

    'hsts' => [
        'enabled' => env('SECURITY_HSTS_ENABLED', true),
        'max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
        'include_subdomains' => env('SECURITY_HSTS_INCLUDE_SUBDOMAINS', true),
        'preload' => env('SECURITY_HSTS_PRELOAD', true),
    ],

    'csp' => [
        'enabled' => env('SECURITY_CSP_ENABLED', true),
        'report_only' => env('SECURITY_CSP_REPORT_ONLY', false),
        'directives' => [
            'default-src' => ["'self'"],
            'base-uri' => ["'self'"],
            'script-src' => array_merge(["'self'", "'unsafe-inline'", "'unsafe-eval'", 'blob:', 'https:'], $splitSources('SECURITY_CSP_EXTRA_SCRIPT_SRC')),
            'style-src' => array_merge(["'self'", "'unsafe-inline'", 'https:'], $splitSources('SECURITY_CSP_EXTRA_STYLE_SRC')),
            'img-src' => array_merge(["'self'", 'data:', 'blob:', 'https:'], $splitSources('SECURITY_CSP_EXTRA_IMG_SRC')),
            'font-src' => array_merge(["'self'", 'data:', 'https:'], $splitSources('SECURITY_CSP_EXTRA_FONT_SRC')),
            'connect-src' => array_merge(["'self'", 'https:', 'ws:', 'wss:'], $splitSources('SECURITY_CSP_EXTRA_CONNECT_SRC')),
            'media-src' => array_merge(["'self'", 'data:', 'blob:', 'https:'], $splitSources('SECURITY_CSP_EXTRA_MEDIA_SRC')),
            'frame-src' => array_merge(["'self'", 'blob:', 'https://www.google.com', 'https://maps.google.com'], $splitSources('SECURITY_CSP_EXTRA_FRAME_SRC')),
            'worker-src' => array_merge(["'self'", 'blob:'], $splitSources('SECURITY_CSP_EXTRA_WORKER_SRC')),
            'object-src' => ["'none'"],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'self'"],
        ],
    ],
];
