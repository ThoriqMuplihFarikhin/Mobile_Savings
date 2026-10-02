<?php

use App\Livewire\Admin\ManajemenNasabah;
use App\Livewire\Admin\VerifikasiNasabah;
use App\Models\KolektorNasabah;
use App\Models\LogAktivitas;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Livewire\Livewire;

function fixtureProfilVerifikasiP31(string $statusPendaftaran): array
{
    $kolektor = User::factory()->kolektor()->create();
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create([
        'status_akun' => $statusPendaftaran === 'aktif' ? 'aktif' : 'terkunci',
    ]);

    $profil = NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah P31',
        'alamat' => 'Jl. P31 No. 1',
        'tanggal_lahir' => '1990-01-01',
        'jenis_kelamin' => 'perempuan',
        'pekerjaan' => 'Wiraswasta',
        'didaftarkan_oleh' => $kolektor->id,
        'status_pendaftaran' => $statusPendaftaran,
    ]);

    return ['profil' => $profil, 'nasabah' => $nasabah, 'kolektor' => $kolektor, 'admin' => $admin];
}

it('approve pada profil aktif tidak mengubah apa pun', function () {
    ['profil' => $profil, 'nasabah' => $nasabah, 'admin' => $admin] = fixtureProfilVerifikasiP31('aktif');

    $this->actingAs($admin);

    Livewire::test(VerifikasiNasabah::class)
        ->call('approve', $profil->id)
        ->assertSee('bisa diproses');

    expect($profil->refresh()->status_pendaftaran)->toBe('aktif')
        ->and($profil->diverifikasi_oleh)->toBeNull()
        ->and($nasabah->refresh()->status_akun)->toBe('aktif')
        ->and(KolektorNasabah::where('nasabah_id', $nasabah->id)->count())->toBe(0)
        ->and(LogAktivitas::where('aksi', 'verifikasi_nasabah')->count())->toBe(0);
});

it('reject pada profil aktif ditolak', function () {
    ['profil' => $profil, 'nasabah' => $nasabah, 'admin' => $admin] = fixtureProfilVerifikasiP31('aktif');

    $this->actingAs($admin);

    Livewire::test(VerifikasiNasabah::class)
        ->call('reject', $profil->id)
        ->assertSee('bisa diproses');

    expect($profil->refresh()->status_pendaftaran)->toBe('aktif')
        ->and($nasabah->refresh()->status_akun)->toBe('aktif')
        ->and(LogAktivitas::where('aksi', 'tolak_nasabah')->count())->toBe(0);
});

it('approve dan reject pada profil pending tercatat di log', function () {
    ['profil' => $profil, 'nasabah' => $nasabah, 'kolektor' => $kolektor, 'admin' => $admin] = fixtureProfilVerifikasiP31('pending_verifikasi');

    $this->actingAs($admin);

    Livewire::test(VerifikasiNasabah::class)
        ->call('approve', $profil->id)
        ->assertSee('berhasil diverifikasi');

    expect($profil->refresh()->status_pendaftaran)->toBe('aktif')
        ->and($profil->diverifikasi_oleh)->toBe($admin->id)
        ->and($nasabah->refresh()->status_akun)->toBe('aktif')
        ->and(KolektorNasabah::where('kolektor_id', $kolektor->id)
            ->where('nasabah_id', $nasabah->id)
            ->where('status', 'aktif')->count())->toBe(1)
        ->and(LogAktivitas::where('aksi', 'verifikasi_nasabah')
            ->where('entitas_terkait', 'nasabah_profil')
            ->where('entitas_id', $profil->id)
            ->where('user_id', $admin->id)->count())->toBe(1);

    ['profil' => $profil2, 'nasabah' => $nasabah2] = fixtureProfilVerifikasiP31('pending_verifikasi');

    Livewire::test(VerifikasiNasabah::class)
        ->call('reject', $profil2->id)
        ->assertSee('Nasabah ditolak');

    expect($profil2->refresh()->status_pendaftaran)->toBe('ditolak')
        ->and($nasabah2->refresh()->status_akun)->toBe('terkunci')
        ->and(LogAktivitas::where('aksi', 'tolak_nasabah')
            ->where('entitas_terkait', 'nasabah_profil')
            ->where('entitas_id', $profil2->id)
            ->where('user_id', $admin->id)->count())->toBe(1);
});

it('toggleStatus menolak penonaktifan bila ada penarikan pending atau approved', function () {
    foreach (['pending', 'approved'] as $statusPenarikan) {
        ['profil' => $profil, 'nasabah' => $nasabah, 'admin' => $admin] = fixtureProfilVerifikasiP31('aktif');

        $produk = ProdukTabungan::create([
            'nama' => 'Tabungan P31 '.$statusPenarikan,
            'tipe' => 'bebas',
            'persen_komisi' => 5.00,
            'minimal_setor' => 10000,
            'status' => 'aktif',
        ]);

        TransaksiPenarikan::create([
            'nasabah_id' => $nasabah->id,
            'produk_id' => $produk->id,
            'nominal_diminta' => 50000,
            'persen_komisi_terpakai' => 5.00,
            'nominal_komisi' => 2500,
            'nominal_diterima' => 47500,
            'jalur_pengajuan' => 'online',
            'lokasi_pengambilan' => 'kantor',
            'status' => $statusPenarikan,
        ]);

        $this->actingAs($admin);

        Livewire::test(ManajemenNasabah::class)
            ->call('toggleStatus', $profil->id)
            ->assertSee('penarikan menunggu proses');

        expect($profil->refresh()->status_pendaftaran)->toBe('aktif')
            ->and($nasabah->refresh()->status_akun)->toBe('aktif');
    }
});

it('toggleStatus menolak profil yang masih pending_verifikasi', function () {
    ['profil' => $profil, 'admin' => $admin] = fixtureProfilVerifikasiP31('pending_verifikasi');

    $this->actingAs($admin);

    Livewire::test(ManajemenNasabah::class)
        ->call('toggleStatus', $profil->id)
        ->assertSee('verifikasi terlebih dahulu');

    expect($profil->refresh()->status_pendaftaran)->toBe('pending_verifikasi');
});

it('toggleStatus menonaktifkan tanpa penarikan berjalan dan tercatat di log', function () {
    ['profil' => $profil, 'nasabah' => $nasabah, 'admin' => $admin] = fixtureProfilVerifikasiP31('aktif');

    $this->actingAs($admin);

    Livewire::test(ManajemenNasabah::class)
        ->call('toggleStatus', $profil->id)
        ->assertSee('berhasil diubah');

    expect($profil->refresh()->status_pendaftaran)->toBe('ditolak')
        ->and($nasabah->refresh()->status_akun)->toBe('terkunci')
        ->and(LogAktivitas::where('aksi', 'ubah_status_nasabah')
            ->where('entitas_terkait', 'nasabah_profil')
            ->where('entitas_id', $profil->id)->count())->toBe(1);
});
