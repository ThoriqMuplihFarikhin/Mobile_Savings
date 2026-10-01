<?php

use App\Livewire\Admin\Pengaturan;
use App\Models\AdminSetting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('tidak mengirim api key tersimpan ke state livewire', function () {
    AdminSetting::set('wa_api_key', 'rahasia123');

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Pengaturan::class)
        ->assertSet('waApiKey', '')
        ->assertSet('waApiKeyTersimpan', true)
        ->call('setTab', 'whatsapp')
        ->assertSee('••••••••', false);
});

it('mempertahankan api key saat disimpan tanpa input ulang', function () {
    AdminSetting::set('wa_provider', 'fonnte');
    AdminSetting::set('wa_api_key', 'rahasia123');

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Pengaturan::class)
        ->call('simpanWhatsApp')
        ->assertSet('waApiKey', '')
        ->assertSet('waApiKeyTersimpan', true);

    expect(AdminSetting::get('wa_api_key'))->toBe('rahasia123');
});

it('mengganti api key saat diisi ulang', function () {
    AdminSetting::set('wa_provider', 'fonnte');
    AdminSetting::set('wa_api_key', 'rahasia123');

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Pengaturan::class)
        ->set('waApiKey', 'token-baru')
        ->call('simpanWhatsApp')
        ->assertSet('waApiKeyTersimpan', true);

    expect(AdminSetting::get('wa_api_key'))->toBe('token-baru');
});

it('kirim pesan uji coba tetap memakai api key tersimpan', function () {
    AdminSetting::set('wa_provider', 'fonnte');
    AdminSetting::set('wa_api_url', 'https://fonnte.test/send');
    AdminSetting::set('wa_api_key', 'rahasia123');

    Http::fake(['fonnte.test/*' => Http::response(['status' => true])]);

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Pengaturan::class)
        ->set('waTestNumber', '628111111')
        ->call('kirimPesanUjiCoba');

    Http::assertSent(fn ($request) => $request->url() === 'https://fonnte.test/send');
});
