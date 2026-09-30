<?php

use App\Models\User;

it('allows admin to access log aktivitas page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/log')
        ->assertOk()
        ->assertSee('Log Aktivitas');
});

it('prevents non-admin from accessing log aktivitas page', function () {
    $nasabah = User::factory()->nasabah()->create();

    $this->actingAs($nasabah)
        ->get('/admin/log')
        ->assertForbidden();
});
