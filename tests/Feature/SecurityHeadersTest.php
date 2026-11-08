<?php

it('sends required security headers on responses', function () {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(self), geolocation=(self), microphone=()')
        ->assertHeader('Content-Security-Policy-Report-Only');

    // Di environment testing (bukan production HTTPS), HSTS tidak boleh muncul
    expect($response->headers->has('Strict-Transport-Security'))->toBeFalse();
});

it('sends HSTS header only when secure and in production', function () {
    $this->app->detectEnvironment(fn () => 'production');

    $response = $this->get('https://localhost/login');

    $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});
