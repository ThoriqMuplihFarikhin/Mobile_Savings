<?php

use App\Livewire\Admin\Laporan;
use App\Models\AbsensiKolektor;
use App\Models\IzinKolektor;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\User;
use Livewire\Livewire;

/**
 * @return array{admin: User, kolektor: User, nasabah: User}
 */
function seedLaporanAbsensiNasabah(): array
{
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    return compact('admin', 'kolektor', 'nasabah');
}

it('menampilkan absensi kolektor dan izin pada periode', function () {
    ['admin' => $admin, 'kolektor' => $kolektor] = seedLaporanAbsensiNasabah();

    $hadirPulang = AbsensiKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal' => now()->toDateString(),
        'waktu_masuk' => '08:00:00',
    ]);
    $hadirPulang->forceFill(['waktu_keluar' => '16:00:00'])->save();

    AbsensiKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal' => now()->subDay()->toDateString(),
        'waktu_masuk' => '07:45:00',
    ]);

    IzinKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_mulai' => now()->subDay()->toDateString(),
        'tanggal_selesai' => now()->addDay()->toDateString(),
        'alasan' => 'Sakit keluarga',
        'status' => 'disetujui',
        'diproses_oleh' => $admin->id,
    ]);

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class)
        ->set('periode', 'rentang')
        ->set('dariTanggal', now()->subDay()->toDateString())
        ->set('sampaiTanggal', now()->toDateString())
        ->call('pilihSeksi', 'absensi')
        ->assertSee('Definisi angka')
        ->assertSee('Jam Masuk')
        ->assertSee('Jam Keluar')
        ->assertSee('Sakit keluarga');

    $absensi = collect($component->viewData('absensiRows'));
    expect($absensi)->toHaveCount(2);
    $barisPulang = $absensi->firstWhere('jam_keluar', '16:00');
    expect($barisPulang)->not->toBeNull()
        ->and($barisPulang['jam_masuk'])->toBe('08:00');

    $izin = collect($component->viewData('izinRows'));
    expect($izin)->toHaveCount(1)
        ->and($izin[0]['status'])->toBe('Disetujui')
        ->and($izin[0]['pemroses'])->toBe($admin->name);

    $rekap = $component->viewData('absensiRekap');
    expect($rekap['hadir'])->toBe(2)
        ->and($rekap['izinDisetujui'])->toBe(1)
        ->and($rekap['izinPending'])->toBe(0);
});

it('menampilkan laporan nasabah per status mode akses dan kolektor', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah] = seedLaporanAbsensiNasabah();

    $nasabahPending = User::factory()->nasabah()->create(['name' => 'Nasabah Pending']);
    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => $nasabah->name,
        'alamat' => 'Jl. Contoh 1',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);
    NasabahProfil::create([
        'user_id' => $nasabahPending->id,
        'nama' => 'Nasabah Pending',
        'alamat' => 'Jl. Contoh 2',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'pending_verifikasi',
    ]);
    $nasabah->forceFill(['mode_akses' => 'offline'])->save();
    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->subMonth()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class)
        ->call('pilihSeksi', 'nasabah')
        ->assertSee('Definisi angka')
        ->assertSee('Digital')
        ->assertSee(route('admin.komisi.index'));

    $rows = collect($component->viewData('nasabahRows'));
    $baris = $rows->firstWhere('nama', $nasabah->name);
    expect($baris)->not->toBeNull()
        ->and($baris['status_pendaftaran'])->toBe('Aktif')
        ->and($baris['mode_akses'])->toBe('Offline')
        ->and($baris['kolektor'])->toBe($kolektor->name);

    $pending = $rows->firstWhere('nama', 'Nasabah Pending');
    expect($pending)->not->toBeNull()
        ->and($pending['status_pendaftaran'])->toBe('Pending Verifikasi')
        ->and($pending['kolektor'])->toBe('-');

    $rekap = $component->viewData('nasabahRekap');
    expect($rekap['aktif'])->toBe(1)
        ->and($rekap['pending'])->toBe(1)
        ->and($rekap['ditolak'])->toBe(0)
        ->and($rekap['digital'])->toBe(1)
        ->and($rekap['offline'])->toBe(1);
});

it('mengekspor csv laporan absensi dan nasabah', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah] = seedLaporanAbsensiNasabah();

    AbsensiKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal' => now()->toDateString(),
        'waktu_masuk' => '08:15:00',
    ]);
    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => $nasabah->name,
        'alamat' => 'Jl. Contoh 1',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'ditolak',
    ]);

    $this->actingAs($admin);

    $absensi = Livewire::test(Laporan::class)
        ->set('periode', 'rentang')
        ->set('dariTanggal', now()->subDay()->toDateString())
        ->set('sampaiTanggal', now()->toDateString())
        ->call('pilihSeksi', 'absensi');
    $absensi->call('exportCsv')->assertFileDownloaded(
        'laporan-absensi-rentang-'.now()->subDay()->toDateString().'-'.now()->toDateString().'.csv'
    );
    $csvAbsensi = base64_decode((string) data_get($absensi->effects, 'download.content'));
    expect($csvAbsensi)->toContain('Jam Masuk')
        ->and($csvAbsensi)->toContain('08:15');

    $nasabahCsv = Livewire::test(Laporan::class)->call('pilihSeksi', 'nasabah');
    $nasabahCsv->call('exportCsv')->assertFileDownloaded('laporan-nasabah-'.now()->toDateString().'.csv');
    $csvNasabah = base64_decode((string) data_get($nasabahCsv->effects, 'download.content'));
    expect($csvNasabah)->toContain('Mode Akses')
        ->and($csvNasabah)->toContain('Ditolak')
        ->and($csvNasabah)->toContain($nasabah->name);
});
