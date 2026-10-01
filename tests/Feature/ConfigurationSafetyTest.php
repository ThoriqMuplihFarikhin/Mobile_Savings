<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

it('memiliki konfigurasi aman pada env example', function () {
    $env = file_get_contents(base_path('.env.example'));

    expect($env)
        ->toContain('APP_DEBUG=false')
        ->toContain('APP_LOCALE=id')
        ->toContain('APP_FALLBACK_LOCALE=id')
        ->toContain('SESSION_ENCRYPT=true')
        ->toContain('SESSION_SECURE_COOKIE=false')
        ->toContain('APP_TIMEZONE=Asia/Jakarta');
});

it('seeder memakai pin admin dari env dan menandai wajib ganti pin', function () {
    $_ENV['SEED_ADMIN_PIN'] = '999999';
    $_SERVER['SEED_ADMIN_PIN'] = '999999';
    putenv('SEED_ADMIN_PIN=999999');

    $this->seed(DatabaseSeeder::class);

    $admin = User::where('role', 'admin')->firstOrFail();

    expect(Hash::check('999999', $admin->pin_hash))->toBeTrue()
        ->and($admin->harus_ganti_pin)->toBeTrue();
});

it('seeder tidak memakai pin hardcode 123456', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::where('role', 'admin')->firstOrFail();

    expect(Hash::check('123456', $admin->pin_hash))->toBeFalse()
        ->and($admin->harus_ganti_pin)->toBeTrue();
});

beforeEach(function () {
    unset($_ENV['SEED_ADMIN_PIN'], $_SERVER['SEED_ADMIN_PIN']);
    putenv('SEED_ADMIN_PIN');
});

afterEach(function () {
    unset($_ENV['SEED_ADMIN_PIN'], $_SERVER['SEED_ADMIN_PIN']);
    putenv('SEED_ADMIN_PIN');
});
