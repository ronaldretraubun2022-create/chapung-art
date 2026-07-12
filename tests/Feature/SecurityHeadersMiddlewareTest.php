<?php

test('security headers are attached to web responses', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

    $csp = $response->headers->get('Content-Security-Policy');

    expect($csp)->toContain("default-src 'self'")
        ->toContain("script-src 'self' 'unsafe-inline' 'unsafe-eval' blob: https:")
        ->toContain("style-src 'self' 'unsafe-inline' https:")
        ->toContain("img-src 'self' data: blob: https:")
        ->toContain("connect-src 'self' https: ws: wss:")
        ->toContain("frame-src 'self' blob: https://www.google.com https://maps.google.com https://www.youtube-nocookie.com")
        ->toContain("frame-ancestors 'self'");
});

test('hsts is not sent on non production http responses', function () {
    $this->get('/')
        ->assertOk()
        ->assertHeaderMissing('Strict-Transport-Security');
});

test('hsts is sent only for https production responses', function () {
    config(['app.env' => 'production']);

    $this->get('https://localhost/')
        ->assertOk()
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
});

test('local csp allows vite dev server only in local environment', function () {
    config(['app.env' => 'local']);

    $csp = $this->get('/')
        ->assertOk()
        ->headers->get('Content-Security-Policy');

    expect($csp)->toContain('http://localhost:5173')
        ->toContain('http://127.0.0.1:5173')
        ->toContain('ws://localhost:5173')
        ->toContain('ws://127.0.0.1:5173');
});

test('production csp does not allow vite dev server sources', function () {
    config(['app.env' => 'production']);

    $csp = $this->get('https://localhost/')
        ->assertOk()
        ->headers->get('Content-Security-Policy');

    expect($csp)->not->toContain('localhost:5173')
        ->not->toContain('127.0.0.1:5173');
});

test('csp can run in report only mode from configuration', function () {
    config(['security.csp.report_only' => true]);

    $response = $this->get('/');

    $response->assertOk()
        ->assertHeaderMissing('Content-Security-Policy')
        ->assertHeader('Content-Security-Policy-Report-Only');
});

test('csp can be disabled from string configuration values', function () {
    config(['security.csp.enabled' => 'false']);

    $this->get('/')
        ->assertOk()
        ->assertHeaderMissing('Content-Security-Policy')
        ->assertHeaderMissing('Content-Security-Policy-Report-Only');
});

test('csp directives are normalized before they are attached', function () {
    config([
        'security.csp.directives' => [
            'default-src' => "'self' https:",
            'img-src' => ["'self'", '', null, "'self'", 'data:'],
            'bad;directive' => ["'none'"],
        ],
    ]);

    $response = $this->get('/');
    $csp = $response->headers->get('Content-Security-Policy');

    $response->assertOk();

    expect($csp)->toContain("default-src 'self' https:")
        ->toContain("img-src 'self' data:")
        ->not->toContain('bad;directive')
        ->not->toContain("'self' 'self'");
});
