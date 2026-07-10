<?php

use Illuminate\Support\Facades\File;

function exampleEnvironment(): array
{
    return parse_ini_file(base_path('.env.example'), false, INI_SCANNER_RAW) ?: [];
}

test('env example is production ready for cpanel mariadb and ssl', function () {
    $env = exampleEnvironment();

    expect($env['APP_ENV'] ?? null)->toBe('production')
        ->and($env['APP_DEBUG'] ?? null)->toBe('false')
        ->and($env['APP_URL'] ?? null)->toBe('https://chapungart.com')
        ->and($env['DB_CONNECTION'] ?? null)->toBe('mariadb')
        ->and($env['SESSION_DRIVER'] ?? null)->toBe('database')
        ->and($env['SESSION_CONNECTION'] ?? null)->toBe('mariadb')
        ->and($env['SESSION_SECURE_COOKIE'] ?? null)->toBe('true')
        ->and($env['SESSION_HTTP_ONLY'] ?? null)->toBe('true')
        ->and($env['CACHE_STORE'] ?? null)->toBe('database')
        ->and($env['DB_CACHE_CONNECTION'] ?? null)->toBe('mariadb')
        ->and($env['QUEUE_CONNECTION'] ?? null)->toBe('database')
        ->and($env['FILESYSTEM_DISK'] ?? null)->toBe('local')
        ->and($env['IMAGE_UPLOAD_DISK'] ?? null)->toBe('public')
        ->and($env['SECURITY_HSTS_ENABLED'] ?? null)->toBe('true');
});

test('production environment documentation covers deployment requirements', function () {
    $document = File::get(base_path('docs/PRODUCTION_ENVIRONMENT.md'));

    expect($document)->toContain('cPanel')
        ->and($document)->toContain('PHP 8.3')
        ->and($document)->toContain('MariaDB')
        ->and($document)->toContain('SSL')
        ->and($document)->toContain('FILESYSTEM_DISK=local')
        ->and($document)->toContain('SESSION_DRIVER=database')
        ->and($document)->toContain('CACHE_STORE=database')
        ->and($document)->toContain('php artisan storage:link')
        ->and($document)->toContain('php artisan config:cache')
        ->and($document)->toContain('php artisan route:cache')
        ->and($document)->toContain('php artisan view:cache');
});
