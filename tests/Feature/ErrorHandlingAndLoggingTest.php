<?php

use App\Logging\SanitizeLogContext;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

test('403 error page is branded and does not expose stack traces', function () {
    Route::get('/__test-forbidden', fn () => abort(403));

    $this->get('/__test-forbidden')
        ->assertForbidden()
        ->assertSee('Chapung Art')
        ->assertSee('Akses Tidak Diizinkan')
        ->assertSee('Kembali Beranda')
        ->assertDontSee('Stack trace')
        ->assertDontSee('vendor/laravel');
});

test('404 error page is branded and does not expose stack traces', function () {
    $this->get('/__missing-page-for-error-test')
        ->assertNotFound()
        ->assertSee('Chapung Art')
        ->assertSee('Halaman Tidak Ditemukan')
        ->assertSee('Kembali Beranda')
        ->assertDontSee('Stack trace')
        ->assertDontSee('vendor/laravel');
});

test('419 error page is branded when token mismatch is rendered', function () {
    Route::get('/__test-token-mismatch', fn () => throw new TokenMismatchException('Token leaked detail'));

    $this->get('/__test-token-mismatch')
        ->assertStatus(419)
        ->assertSee('Chapung Art')
        ->assertSee('Sesi Telah Berakhir')
        ->assertSee('Kembali Beranda')
        ->assertDontSee('Token leaked detail')
        ->assertDontSee('Stack trace');
});

test('error pages render english copy from active locale', function () {
    $this->withSession(['locale' => 'en'])
        ->get('/__missing-page-for-english-error-test')
        ->assertNotFound()
        ->assertSee('lang="en"', false)
        ->assertSee('Page Not Found')
        ->assertSee('Back Home')
        ->assertDontSee('Halaman Tidak Ditemukan')
        ->assertDontSee('Stack trace');
});

test('production log channel uses daily rotation and sanitizer tap', function () {
    expect(config('logging.channels.production.driver'))->toBe('daily')
        ->and(config('logging.channels.production.tap'))->toContain(SanitizeLogContext::class);
});

test('log sanitizer removes sensitive context and keeps safe request context', function () {
    $path = storage_path('logs/security-sanitizer-test.log');
    File::delete($path);

    config([
        'logging.channels.security_sanitizer_test' => [
            'driver' => 'single',
            'path' => $path,
            'level' => 'debug',
            'replace_placeholders' => true,
            'tap' => [SanitizeLogContext::class],
        ],
    ]);

    Log::forgetChannel('security_sanitizer_test');
    Log::channel('security_sanitizer_test')->warning('Admin error captured token=raw-token-value Bearer rawBearerToken123', [
        'password' => 'top-secret-password',
        'payment_secret' => 'payment-secret-value',
        'safe_key' => 'safe-value',
    ]);

    $content = File::get($path);

    expect($content)->toContain('Admin error captured')
        ->toContain('[filtered]')
        ->toContain('safe-value')
        ->toContain('request')
        ->not->toContain('top-secret-password')
        ->not->toContain('payment-secret-value')
        ->not->toContain('raw-token-value')
        ->not->toContain('rawBearerToken123');

    File::delete($path);
});
