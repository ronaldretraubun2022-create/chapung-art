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

test('env example uses placeholders instead of real secrets', function () {
    $env = exampleEnvironment();

    expect($env['APP_KEY'] ?? null)->toBe('')
        ->and($env['DB_PASSWORD'] ?? null)->toStartWith('CHANGE_ME')
        ->and($env['MAIL_PASSWORD'] ?? null)->toBe('')
        ->and($env['AWS_ACCESS_KEY_ID'] ?? null)->toBe('')
        ->and($env['AWS_SECRET_ACCESS_KEY'] ?? null)->toBe('')
        ->and($env['BACKUP_ARCHIVE_PASSWORD'] ?? null)->toStartWith('CHANGE_ME')
        ->and(File::get(base_path('.env.example')))->not->toMatch('/sk-[A-Za-z0-9]/')
        ->not->toMatch('/AKIA[0-9A-Z]{16}/')
        ->not->toMatch('/-----BEGIN (RSA |OPENSSH |EC )?PRIVATE KEY-----/');
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

test('production readiness and checklist documentation cover cpanel rollout', function () {
    $readiness = File::get(base_path('docs/PRODUCTION_READINESS.md'));
    $checklist = File::get(base_path('docs/PRODUCTION_CHECKLIST.md'));

    expect($readiness)->toContain('cPanel')
        ->toContain('php artisan migrate --force')
        ->toContain('php artisan storage:link')
        ->toContain('APP_DEBUG=false')
        ->toContain('localhost:5173')
        ->toContain('Rollback')
        ->and($checklist)->toContain('Before Deploy')
        ->toContain('Backup')
        ->toContain('Server Permissions')
        ->toContain('Safe Migration')
        ->toContain('Security Verification')
        ->toContain('Rollback');
});
