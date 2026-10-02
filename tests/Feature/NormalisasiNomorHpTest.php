<?php

use App\Livewire\Admin\RegistrasiNasabah;
use App\Models\AdminSetting;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('login dengan nomor format +62 berhasil untuk akun format 08', function () {
    User::factory()->create([
        'no_hp' => '081234567890',
        'pin_hash' => bcrypt('123456'),
    ]);

    $this->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'no_hp' => '+6281234567890',
            'password' => '123456',
        ])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

it('pendaftaran dengan format nomor lain pada nomor yang sama gagal unique', function () {
    User::factory()->create(['no_hp' => '081234567890']);
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(RegistrasiNasabah::class)
        ->set([
            'nama' => 'Budi Santoso',
            'noHp' => '+6281234567890',
            'alamat' => 'Jalan Contoh No. 1',
            'tanggalLahir' => '1990-01-01',
            'jenisKelamin' => 'laki-laki',
        ])
        ->call('submit')
        ->assertHasErrors(['noHp' => 'unique']);

    expect(User::where('no_hp', '081234567890')->count())->toBe(1);
});

it('menolak nomor yang tetap tidak valid setelah normalisasi', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(RegistrasiNasabah::class)
        ->set([
            'nama' => 'Budi Santoso',
            'noHp' => '0211234567',
            'alamat' => 'Jalan Contoh No. 1',
            'tanggalLahir' => '1990-01-01',
            'jenisKelamin' => 'laki-laki',
        ])
        ->call('submit')
        ->assertHasErrors(['noHp' => 'regex']);

    expect(User::where('no_hp', '0211234567')->exists())->toBeFalse();
});

it('command users:normalisasi-hp melaporkan duplikat tanpa mengubah data', function () {
    User::factory()->create(['no_hp' => '081234567890']);

    DB::table('users')->insert([
        [
            'name' => 'Kedua',
            'no_hp' => '6281234567890',
            'pin_hash' => bcrypt('123456'),
            'role' => 'nasabah',
            'status_akun' => 'aktif',
            'harus_ganti_pin' => true,
        ],
        [
            'name' => 'Ketiga',
            'no_hp' => '+6281234567891',
            'pin_hash' => bcrypt('123456'),
            'role' => 'nasabah',
            'status_akun' => 'aktif',
            'harus_ganti_pin' => true,
        ],
    ]);

    $this->artisan('users:normalisasi-hp')
        ->expectsOutputToContain('6281234567890')
        ->assertExitCode(1);

    expect(DB::table('users')->where('name', 'Kedua')->value('no_hp'))->toBe('6281234567890')
        ->and(DB::table('users')->where('name', 'Ketiga')->value('no_hp'))->toBe('+6281234567891');
});

it('mengirim format 62 ke provider wablas', function () {
    AdminSetting::set('wa_provider', 'wablas');
    AdminSetting::set('wa_api_url', 'https://wablas.test/api/send-message');
    AdminSetting::set('wa_api_key', 'token-wablas');

    Http::fake(['wablas.test/*' => Http::response(['status' => 'success'])]);

    $hasil = app(WhatsAppService::class)->sendNotification('081234567890', 'Pesan uji');

    expect($hasil)->toBeTrue();
    Http::assertSent(fn ($request) => $request['phone'] === '6281234567890');
});
