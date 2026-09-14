<?php

use App\Models\User;

it('automatically syncs Spatie role when role column is updated', function () {
    $user = User::factory()->nasabah()->create();

    expect($user->hasRole('nasabah'))->toBeTrue();

    $user->update(['role' => 'kolektor']);

    expect($user->fresh()->hasRole('kolektor'))->toBeTrue();
    expect($user->fresh()->hasRole('nasabah'))->toBeFalse();
});

it('syncs role on initial creation via factory', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->hasRole('admin'))->toBeTrue();
    expect($admin->role)->toBe('admin');
});

it('detects inconsistent role via check command', function () {
    $user = User::factory()->nasabah()->create();

    $user->update(['role' => 'admin']);
    DB::table('model_has_roles')->where('model_id', $user->id)->update(['role_id' => DB::table('roles')->where('name', 'nasabah')->value('id')]);

    $this->artisan('role:check-consistency')
        ->assertExitCode(1);
});

it('reports consistent roles via check command', function () {
    User::factory()->admin()->create();
    User::factory()->kolektor()->create();
    User::factory()->nasabah()->create();

    $this->artisan('role:check-consistency')
        ->assertExitCode(0);
});
