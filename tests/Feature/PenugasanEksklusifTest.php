<?php

use App\Actions\Kolektor\TugaskanNasabahAction;
use App\Livewire\Admin\HandoverKolektor;
use App\Livewire\Admin\KelolaKolektor;
use App\Livewire\Admin\VerifikasiNasabah;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function fixturePenugasanEksklusifP32(): array
{
    $admin = User::factory()->admin()->create();
    $kolektor1 = User::factory()->kolektor()->create();
    $kolektor2 = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Eksklusif',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $kolektor1->id,
        'status_pendaftaran' => 'aktif',
    ]);

    return compact('admin', 'kolektor1', 'kolektor2', 'nasabah');
}

it('menolak penugasan nasabah yang sudah dipegang kolektor lain', function () {
    ['admin' => $admin, 'kolektor1' => $kolektor1, 'kolektor2' => $kolektor2, 'nasabah' => $nasabah] = fixturePenugasanEksklusifP32();

    KolektorNasabah::create([
        'kolektor_id' => $kolektor1->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
        'aktif_unik' => 1,
    ]);

    $this->actingAs($admin);

    Livewire::test(KelolaKolektor::class)
        ->call('toggleAssign', $kolektor2->id)
        ->set('assignNasabahId', $nasabah->id)
        ->call('assignNasabah')
        ->assertSee('masih dipegang kolektor lain');

    expect(KolektorNasabah::where('nasabah_id', $nasabah->id)->where('status', 'aktif')->count())->toBe(1)
        ->and(KolektorNasabah::where('nasabah_id', $nasabah->id)->where('kolektor_id', $kolektor2->id)->exists())->toBeFalse();
});

it('menolak penugasan untuk id yang bukan berperan nasabah', function () {
    ['admin' => $admin, 'kolektor1' => $kolektor1, 'kolektor2' => $kolektor2] = fixturePenugasanEksklusifP32();

    $this->actingAs($admin);

    Livewire::test(KelolaKolektor::class)
        ->call('toggleAssign', $kolektor1->id)
        ->set('assignNasabahId', $kolektor2->id)
        ->call('assignNasabah')
        ->assertSee('berperan nasabah');

    expect(KolektorNasabah::count())->toBe(0);
});

it('idempoten saat nasabah sudah aktif pada kolektor yang sama', function () {
    ['kolektor1' => $kolektor1, 'nasabah' => $nasabah] = fixturePenugasanEksklusifP32();

    app(TugaskanNasabahAction::class)->execute((int) $kolektor1->id, (int) $nasabah->id);
    app(TugaskanNasabahAction::class)->execute((int) $kolektor1->id, (int) $nasabah->id);

    expect(KolektorNasabah::where('nasabah_id', $nasabah->id)->count())->toBe(1)
        ->and(KolektorNasabah::where('nasabah_id', $nasabah->id)->first()->aktif_unik)->toBe(1);
});

it('memblokir dua penugasan aktif untuk nasabah sama pada level database', function () {
    ['kolektor1' => $kolektor1, 'kolektor2' => $kolektor2, 'nasabah' => $nasabah] = fixturePenugasanEksklusifP32();

    DB::table('kolektor_nasabah')->insert([
        'kolektor_id' => $kolektor1->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
        'aktif_unik' => 1,
    ]);

    expect(fn () => DB::table('kolektor_nasabah')->insert([
        'kolektor_id' => $kolektor2->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
        'aktif_unik' => 1,
    ]))->toThrow(QueryException::class);
});

it('handover mempertahankan jaring unik: baris lama dilepas dan baris baru bernilai 1', function () {
    ['admin' => $admin, 'kolektor1' => $kolektor1, 'kolektor2' => $kolektor2, 'nasabah' => $nasabah] = fixturePenugasanEksklusifP32();

    $lama = KolektorNasabah::create([
        'kolektor_id' => $kolektor1->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->subDays(5)->toDateString(),
        'status' => 'aktif',
        'aktif_unik' => 1,
    ]);

    $this->actingAs($admin);

    Livewire::test(HandoverKolektor::class)
        ->set('kolektorLamaId', $kolektor1->id)
        ->set('kolektorBaruId', $kolektor2->id)
        ->call('processHandover')
        ->assertHasNoErrors();

    expect($lama->fresh()->status)->toBe('nonaktif')
        ->and($lama->fresh()->aktif_unik)->toBeNull()
        ->and(KolektorNasabah::where('nasabah_id', $nasabah->id)->where('kolektor_id', $kolektor2->id)->where('status', 'aktif')->count())->toBe(1)
        ->and(KolektorNasabah::where('nasabah_id', $nasabah->id)->where('status', 'aktif')->count())->toBe(1)
        ->and(KolektorNasabah::where('nasabah_id', $nasabah->id)->where('status', 'aktif')->first()->aktif_unik)->toBe(1);
});

it('audit penugasan gagal bila ada duplikat aktif atau penugasan ke non-kolektor', function () {
    ['admin' => $admin, 'kolektor1' => $kolektor1, 'kolektor2' => $kolektor2, 'nasabah' => $nasabah] = fixturePenugasanEksklusifP32();
    $nasabah2 = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah2->id,
        'nama' => 'Nasabah Kedua',
        'alamat' => 'Jl. Test No. 2',
        'jenis_kelamin' => 'perempuan',
        'didaftarkan_oleh' => $kolektor1->id,
        'status_pendaftaran' => 'aktif',
    ]);

    DB::table('kolektor_nasabah')->insert([
        [
            'kolektor_id' => $kolektor1->id,
            'nasabah_id' => $nasabah->id,
            'tanggal_mulai_ditangani' => now()->toDateString(),
            'status' => 'aktif',
            'aktif_unik' => 1,
        ],
        [
            'kolektor_id' => $kolektor2->id,
            'nasabah_id' => $nasabah->id,
            'tanggal_mulai_ditangani' => now()->toDateString(),
            'status' => 'aktif',
            'aktif_unik' => null,
        ],
        [
            'kolektor_id' => $admin->id,
            'nasabah_id' => $nasabah2->id,
            'tanggal_mulai_ditangani' => now()->toDateString(),
            'status' => 'aktif',
            'aktif_unik' => 1,
        ],
    ]);

    $exitCode = Artisan::call('kolektor:audit-penugasan');
    $output = Artisan::output();

    expect($exitCode)->toBe(1)
        ->and($output)->toContain('Nasabah dengan >1 penugasan aktif: 1')
        ->and($output)->toContain('Penugasan aktif ke akun non-kolektor: 1');
});

it('audit penugasan hijau bila data sehat', function () {
    ['kolektor1' => $kolektor1, 'nasabah' => $nasabah] = fixturePenugasanEksklusifP32();

    DB::table('kolektor_nasabah')->insert([
        'kolektor_id' => $kolektor1->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
        'aktif_unik' => 1,
    ]);

    $exitCode = Artisan::call('kolektor:audit-penugasan');

    expect($exitCode)->toBe(0)
        ->and(Artisan::output())->toContain('sehat');
});

it('removeAssign melepas baris unik saat menonaktifkan penugasan', function () {
    ['admin' => $admin, 'kolektor1' => $kolektor1, 'nasabah' => $nasabah] = fixturePenugasanEksklusifP32();

    $assignment = KolektorNasabah::create([
        'kolektor_id' => $kolektor1->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
        'aktif_unik' => 1,
    ]);

    $this->actingAs($admin);

    Livewire::test(KelolaKolektor::class)
        ->call('toggleAssign', $kolektor1->id)
        ->call('removeAssign', $assignment->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('kolektor_nasabah', [
        'id' => $assignment->id,
        'status' => 'nonaktif',
        'aktif_unik' => null,
    ]);
});

it('approve verifikasi membuat penugasan kolektor pendaftar dengan baris unik aktif', function () {
    ['admin' => $admin, 'kolektor1' => $kolektor1, 'nasabah' => $nasabah] = fixturePenugasanEksklusifP32();

    $profil = NasabahProfil::where('user_id', $nasabah->id)->first();
    $profil->update(['status_pendaftaran' => 'pending_verifikasi']);

    $this->actingAs($admin);

    Livewire::test(VerifikasiNasabah::class)
        ->call('approve', $profil->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('kolektor_nasabah', [
        'kolektor_id' => $kolektor1->id,
        'nasabah_id' => $nasabah->id,
        'status' => 'aktif',
        'aktif_unik' => 1,
    ]);
});
