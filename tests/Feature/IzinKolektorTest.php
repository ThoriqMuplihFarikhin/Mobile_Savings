<?php

use App\Livewire\Admin\MonitoringAbsensi;
use App\Livewire\Kolektor\AjukanIzin;
use App\Livewire\Kolektor\Pengaturan;
use App\Models\IzinKolektor;
use App\Models\User;
use Livewire\Livewire;

it('kolektor dapat mengajukan izin dengan tanggal dan alasan', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor);

    Livewire::test(AjukanIzin::class)
        ->set('tanggalMulai', now()->toDateString())
        ->set('tanggalSelesai', now()->addDays(2)->toDateString())
        ->set('alasan', 'Acara keluarga di luar kota')
        ->call('ajukan')
        ->assertSee('Pengajuan izin terkirim')
        ->assertSet('alasan', '');

    $this->assertDatabaseHas('izin_kolektor', [
        'kolektor_id' => $kolektor->id,
        'tanggal_mulai' => now()->toDateString(),
        'tanggal_selesai' => now()->addDays(2)->toDateString(),
        'alasan' => 'Acara keluarga di luar kota',
        'status' => 'pending',
    ]);
});

it('penanggalan izin tervalidasi: selesai tidak boleh sebelum mulai', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor);

    Livewire::test(AjukanIzin::class)
        ->set('tanggalMulai', now()->toDateString())
        ->set('tanggalSelesai', now()->subDay()->toDateString())
        ->set('alasan', 'Salah input tanggal')
        ->call('ajukan')
        ->assertHasErrors(['tanggalSelesai']);

    $this->assertDatabaseCount('izin_kolektor', 0);
});

it('daftar izin hanya menampilkan pengajuan milik sendiri', function () {
    $kolektor = User::factory()->kolektor()->create();
    $kolektorLain = User::factory()->kolektor()->create();

    IzinKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_mulai' => now()->toDateString(),
        'tanggal_selesai' => now()->addDay()->toDateString(),
        'alasan' => 'Cuti milik sendiri',
        'status' => 'pending',
    ]);
    IzinKolektor::create([
        'kolektor_id' => $kolektorLain->id,
        'tanggal_mulai' => now()->toDateString(),
        'tanggal_selesai' => now()->addDay()->toDateString(),
        'alasan' => 'Cuti milik kolektor lain',
        'status' => 'pending',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(AjukanIzin::class)
        ->assertSee('Cuti milik sendiri')
        ->assertDontSee('Cuti milik kolektor lain');
});

it('admin dapat menyetujui dan menolak pengajuan izin beserta catatan', function () {
    $kolektor = User::factory()->kolektor()->create();
    $kolektor2 = User::factory()->kolektor()->create();
    $admin = User::factory()->admin()->create();

    $izin1 = IzinKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_mulai' => now()->toDateString(),
        'tanggal_selesai' => now()->addDay()->toDateString(),
        'alasan' => 'Keperluan mendesak',
        'status' => 'pending',
    ]);
    $izin2 = IzinKolektor::create([
        'kolektor_id' => $kolektor2->id,
        'tanggal_mulai' => now()->addDays(3)->toDateString(),
        'tanggal_selesai' => now()->addDays(4)->toDateString(),
        'alasan' => 'Acara keluarga',
        'status' => 'pending',
    ]);

    $this->actingAs($admin);

    Livewire::test(MonitoringAbsensi::class)
        ->call('prosesIzin', $izin1->id, 'disetujui')
        ->assertSee('berhasil disetujui');

    $izin1->refresh();
    expect($izin1->status)->toBe('disetujui')
        ->and((int) $izin1->diproses_oleh)->toBe((int) $admin->id);

    Livewire::test(MonitoringAbsensi::class)
        ->set('catatanIzin', [$izin2->id => 'Periode berbenturan dengan jadwal kunjungan'])
        ->call('prosesIzin', $izin2->id, 'ditolak')
        ->assertSee('berhasil ditolak');

    $izin2->refresh();
    expect($izin2->status)->toBe('ditolak')
        ->and((int) $izin2->diproses_oleh)->toBe((int) $admin->id)
        ->and($izin2->catatan_admin)->toBe('Periode berbenturan dengan jadwal kunjungan');
});

it('pengajuan yang sudah diproses tidak bisa diproses ulang', function () {
    $kolektor = User::factory()->kolektor()->create();
    $admin = User::factory()->admin()->create();
    $adminLain = User::factory()->admin()->create();

    $izin = IzinKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_mulai' => now()->toDateString(),
        'tanggal_selesai' => now()->addDay()->toDateString(),
        'alasan' => 'Keperluan mendesak',
        'status' => 'disetujui',
        'diproses_oleh' => $admin->id,
    ]);

    $this->actingAs($adminLain);

    Livewire::test(MonitoringAbsensi::class)
        ->call('prosesIzin', $izin->id, 'ditolak')
        ->assertSee('tidak dapat diproses');

    $izin->refresh();
    expect($izin->status)->toBe('disetujui')
        ->and((int) $izin->diproses_oleh)->toBe((int) $admin->id);
});

it('izin disetujui dihitung sebagai izin bukan belum absen di monitoring', function () {
    $kolektorIzin = User::factory()->kolektor()->create();
    $kolektorKosong = User::factory()->kolektor()->create();
    $admin = User::factory()->admin()->create();

    IzinKolektor::create([
        'kolektor_id' => $kolektorIzin->id,
        'tanggal_mulai' => now()->toDateString(),
        'tanggal_selesai' => now()->toDateString(),
        'alasan' => 'Sakit',
        'status' => 'disetujui',
        'diproses_oleh' => $admin->id,
    ]);

    $this->actingAs($admin);

    Livewire::test(MonitoringAbsensi::class)
        ->assertSet('jumlahIzin', 1)
        ->assertSet('jumlahBelumAbsen', 1)
        ->assertSee('Sedang Izin')
        ->assertSee('Belum absen');
});

it('pengajuan izin menunggu tampil di monitoring untuk diproses admin', function () {
    $kolektor = User::factory()->kolektor()->create();
    $admin = User::factory()->admin()->create();

    IzinKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_mulai' => now()->addDay()->toDateString(),
        'tanggal_selesai' => now()->addDays(2)->toDateString(),
        'alasan' => 'Perjalanan dinas keluarga',
        'status' => 'pending',
    ]);

    $this->actingAs($admin);

    Livewire::test(MonitoringAbsensi::class)
        ->assertSee('Perjalanan dinas keluarga')
        ->assertSee('Setujui')
        ->assertSee('Tolak');
});

it('tautan ajukan izin kembali tampil di pengaturan kolektor', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor);

    Livewire::test(Pengaturan::class)
        ->assertSee('Ajukan Izin / Cuti');
});

it('halaman ajukan izin dapat diakses kolektor', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor)
        ->get(route('kolektor.izin.index'))
        ->assertOk()
        ->assertSee('Ajukan Izin');
});
