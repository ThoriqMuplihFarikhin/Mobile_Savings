<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));
    $response->assertOk();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create([
        'no_hp' => '081234567890',
        'pin_hash' => bcrypt('123456'),
    ]);

    $response = $this->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'no_hp' => $user->no_hp,
            'password' => '123456',
        ]);

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();
});

test('users can not authenticate with invalid pin', function () {
    $user = User::factory()->create([
        'no_hp' => '081234567890',
        'pin_hash' => bcrypt('123456'),
    ]);

    $response = $this->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'no_hp' => $user->no_hp,
            'password' => '999999',
        ]);

    $response->assertSessionHasErrorsIn('no_hp');
    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('logout'));

    $response->assertRedirect(route('home'));
    $this->assertGuest();
});

test('account gets temporarily locked after 5 failed attempts', function () {
    $user = User::factory()->create([
        'no_hp' => '081234567890',
        'pin_hash' => bcrypt('123456'),
        'percobaan_gagal' => 0,
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->withoutMiddleware(PreventRequestForgery::class)
            ->post(route('login.store'), [
                'no_hp' => $user->no_hp,
                'password' => '999999',
            ]);
    }

    $user->refresh();
    expect($user->status_akun)->toEqual('aktif')
        ->and($user->login_terkunci_hingga->isFuture())->toBeTrue()
        ->and($user->percobaan_gagal)->toBe(0);
});
