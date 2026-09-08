<?php

use App\Models\User;

// --- Settings umum (shared): profile / security / appearance ---
// Semua role boleh akses, karena ini pengaturan akun pribadi masing-masing.

it('allows every role to access shared profile settings', function (string $role) {
    $user = User::factory()->{$role}()->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk();
})->with(['admin', 'kolektor', 'nasabah']);

it('allows every role to access shared security settings', function (string $role) {
    $user = User::factory()->{$role}()->create();

    $this->actingAs($user)
        ->get(route('security.edit'))
        ->assertOk();
})->with(['admin', 'kolektor', 'nasabah']);

it('allows every role to access shared appearance settings', function (string $role) {
    $user = User::factory()->{$role}()->create();

    $this->actingAs($user)
        ->get(route('appearance.edit'))
        ->assertOk();
})->with(['admin', 'kolektor', 'nasabah']);

it('blocks a locked account from accessing shared settings', function () {
    $user = User::factory()->nasabah()->create(['status_akun' => 'terkunci']);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertRedirect(route('login'));
});

// --- Pengaturan khusus admin ---

it('allows admin to access /admin/pengaturan', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/pengaturan')
        ->assertOk();
});

it('blocks kolektor and nasabah from /admin/pengaturan', function (string $role) {
    $user = User::factory()->{$role}()->create();

    $this->actingAs($user)
        ->get('/admin/pengaturan')
        ->assertForbidden();
})->with(['kolektor', 'nasabah']);

// --- Pengaturan khusus kolektor ---

it('allows kolektor to access /kolektor/pengaturan', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor)
        ->get('/kolektor/pengaturan')
        ->assertOk();
});

it('blocks admin and nasabah from /kolektor/pengaturan', function (string $role) {
    $user = User::factory()->{$role}()->create();

    $this->actingAs($user)
        ->get('/kolektor/pengaturan')
        ->assertForbidden();
})->with(['admin', 'nasabah']);

// --- Pengaturan khusus nasabah ---

it('allows nasabah to access /nasabah/pengaturan', function () {
    $nasabah = User::factory()->nasabah()->create();

    $this->actingAs($nasabah)
        ->get('/nasabah/pengaturan')
        ->assertOk();
});

it('blocks admin and kolektor from /nasabah/pengaturan', function (string $role) {
    $user = User::factory()->{$role}()->create();

    $this->actingAs($user)
        ->get('/nasabah/pengaturan')
        ->assertForbidden();
})->with(['admin', 'kolektor']);
