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
        ->toContain("frame-ancestors 'self'");
});

test('hsts is not sent on non production http responses', function () {
    $this->get('/')
        ->assertOk()
        ->assertHeaderMissing('Strict-Transport-Security');
});

test('hsts is sent only for https production responses', function () {
    config(['app.env' => 'production']);

    $this->withServerVariables(['HTTPS' => 'on'])
        ->get('/')
        ->assertOk()
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
});

test('csp can run in report only mode from configuration', function () {
    config(['security.csp.report_only' => true]);

    $response = $this->get('/');

    $response->assertOk()
        ->assertHeaderMissing('Content-Security-Policy')
        ->assertHeader('Content-Security-Policy-Report-Only');
});
