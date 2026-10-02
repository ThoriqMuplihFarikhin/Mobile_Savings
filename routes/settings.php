<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'role:kolektor|nasabah'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');
});

Route::middleware(['auth', 'active', 'verified', 'role:kolektor|nasabah'])->group(function () {
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');

    Route::livewire('settings/security', 'pages::settings.security')
        ->name('security.edit');
});
