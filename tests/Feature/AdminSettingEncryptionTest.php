<?php

use App\Models\AdminSetting;

it('encrypts wa_api_key in database but returns plain text via get', function () {
    AdminSetting::set('wa_api_key', 'rahasia123');

    $rawValue = DB::table('admin_settings')->where('key', 'wa_api_key')->value('value');
    expect($rawValue)->not->toBe('rahasia123');

    $decrypted = AdminSetting::get('wa_api_key');
    expect($decrypted)->toBe('rahasia123');
});

it('falls back to plain text for legacy unencrypted wa_api_key', function () {
    DB::table('admin_settings')->insert([
        'key' => 'wa_api_key',
        'value' => 'plain_token_old',
    ]);

    Cache::forget('admin_setting:wa_api_key');

    $result = AdminSetting::get('wa_api_key');
    expect($result)->toBe('plain_token_old');
});

it('does not encrypt non-encrypted keys', function () {
    AdminSetting::set('site_name', 'My App');

    $rawValue = DB::table('admin_settings')->where('key', 'site_name')->value('value');
    expect($rawValue)->toBe('My App');
});

it('returns default when key does not exist', function () {
    $result = AdminSetting::get('nonexistent_key', 'default_val');
    expect($result)->toBe('default_val');
});

it('does not cache wa_api_key plaintext after get', function () {
    AdminSetting::set('wa_api_key', 'rahasia123');

    $value = AdminSetting::get('wa_api_key');

    expect($value)->toBe('rahasia123')
        ->and(Cache::get('admin_setting:wa_api_key'))->toBeNull();
});

it('purges legacy plaintext cache entry on get', function () {
    AdminSetting::set('wa_api_key', 'rahasia123');
    Cache::put('admin_setting:wa_api_key', 'token_lama_plaintext', now()->addDay());

    $value = AdminSetting::get('wa_api_key');

    expect($value)->toBe('rahasia123')
        ->and(Cache::get('admin_setting:wa_api_key'))->toBeNull();
});
