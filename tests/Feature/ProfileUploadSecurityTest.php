<?php

use App\Livewire\Admin\Pengaturan;
use App\Livewire\Admin\Settings\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

it('rejects svg and php uploads in admin settings profile', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $svg = UploadedFile::fake()->create('exploit.svg', 50, 'image/svg+xml');
    $php = UploadedFile::fake()->create('shell.php', 50, 'application/x-php');

    Livewire::test(Profile::class)
        ->set('fotoBaru', $svg)
        ->assertHasErrors(['fotoBaru']);

    Livewire::test(Profile::class)
        ->set('fotoBaru', $php)
        ->assertHasErrors(['fotoBaru']);
});

it('accepts valid jpg, png, and webp in admin settings profile', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $jpg = UploadedFile::fake()->image('avatar.jpg');

    Livewire::test(Profile::class)
        ->set('fotoBaru', $jpg)
        ->assertHasNoErrors(['fotoBaru']);
});

it('rejects svg and php uploads in admin pengaturan profilFoto', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $svg = UploadedFile::fake()->create('exploit.svg', 50, 'image/svg+xml');

    Livewire::test(Pengaturan::class)
        ->set('profilNama', 'Admin Test')
        ->set('profilFoto', $svg)
        ->call('simpanProfil')
        ->assertHasErrors(['profilFoto']);

    $php = UploadedFile::fake()->create('shell.php', 50, 'application/x-php');

    Livewire::test(Pengaturan::class)
        ->set('profilNama', 'Admin Test')
        ->set('profilFoto', $php)
        ->call('simpanProfil')
        ->assertHasErrors(['profilFoto']);
});

it('rejects svg and php uploads in shared settings profile', function () {
    $nasabah = User::factory()->nasabah()->create();

    $this->actingAs($nasabah);

    $svg = UploadedFile::fake()->create('exploit.svg', 50, 'image/svg+xml');
    $php = UploadedFile::fake()->create('shell.php', 50, 'application/x-php');

    Livewire::test('pages::settings.profile')
        ->set('fotoBaru', $svg)
        ->assertHasErrors(['fotoBaru']);

    Livewire::test('pages::settings.profile')
        ->set('fotoBaru', $php)
        ->assertHasErrors(['fotoBaru']);
});
