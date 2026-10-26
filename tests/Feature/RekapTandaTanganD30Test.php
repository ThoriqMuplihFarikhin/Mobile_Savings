<?php

use App\Livewire\Admin\Laporan;
use App\Models\KolektorNasabah;
use App\Models\LogAktivitas;
use App\Models\NasabahProfil;
use App\Models\User;
use Livewire\Livewire;

/**
 * F5 / D30 — saluran kontrol nasabah offline: cetak rekap bertanda tangan,
 * jejak waktu cetak, dan kolom "rekap terakhir dicetak" pada laporan.
 *
 * @return array{nasabah: User, kolektorPj: User, kolektorBukanPj: User, admin: User, profil: NasabahProfil}
 */
function d30TandaTanganFixture(): array
{
    $nasabah = User::factory()->nasabah()->create([
        'name' => 'Nasabah Offline D30',
        'mode_akses' => 'offline',
    ]);
    $kolektorPj = User::factory()->kolektor()->create();
    $kolektorBukanPj = User::factory()->kolektor()->create();
    $admin = User::factory()->admin()->create();

    KolektorNasabah::create([
        'kolektor_id' => $kolektorPj->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $profil = NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => $nasabah->name,
        'alamat' => 'Jalan Contoh D30',
        'didaftarkan_oleh' => $kolektorPj->id,
        'status_pendaftaran' => 'aktif',
    ]);

    return compact('nasabah', 'kolektorPj', 'kolektorBukanPj', 'admin', 'profil');
}

it('halaman rekap memuat baris tanda tangan nasabah, tanggal cetak, dan nama kolektor (D30)', function () {
    ['nasabah' => $nasabah, 'kolektorPj' => $kolektorPj, 'admin' => $admin] = d30TandaTanganFixture();

    $this->actingAs($admin)
        ->get(route('rekap.nasabah', $nasabah))
        ->assertOk()
        ->assertSee('Tanda Tangan Nasabah')
        ->assertSee('Tanggal Cetak')
        ->assertSee('Nama Kolektor')
        ->assertSee($kolektorPj->name);
});

it('setiap cetak rekap dicatat ke log aktivitas dan stempel rekap_dicetak_pada (D30)', function () {
    ['nasabah' => $nasabah, 'admin' => $admin, 'profil' => $profil] = d30TandaTanganFixture();

    $this->actingAs($admin)
        ->post(route('rekap.nasabah.cetak', $nasabah))
        ->assertRedirect(route('rekap.nasabah', $nasabah));

    expect(LogAktivitas::where('aksi', 'cetak_rekap_nasabah')->count())->toBe(1)
        ->and(NasabahProfil::find($profil->id)?->rekap_dicetak_pada)->not->toBeNull();

    $this->actingAs($admin)->post(route('rekap.nasabah.cetak', $nasabah));

    expect(LogAktivitas::where('aksi', 'cetak_rekap_nasabah')->count())->toBe(2);
});

it('menolak aksi cetak oleh kolektor bukan penanggung jawab, nasabah, dan tamu (D30)', function () {
    ['nasabah' => $nasabah, 'kolektorBukanPj' => $kolektorBukanPj, 'profil' => $profil] = d30TandaTanganFixture();

    $this->post(route('rekap.nasabah.cetak', $nasabah))->assertRedirect();

    $this->actingAs($kolektorBukanPj)
        ->post(route('rekap.nasabah.cetak', $nasabah))
        ->assertForbidden();

    $this->actingAs($nasabah)
        ->post(route('rekap.nasabah.cetak', $nasabah))
        ->assertForbidden();

    expect(LogAktivitas::where('aksi', 'cetak_rekap_nasabah')->count())->toBe(0)
        ->and(NasabahProfil::find($profil->id)?->rekap_dicetak_pada)->toBeNull();
});

it('laporan nasabah menambah kolom rekap terakhir dicetak dan mengurutkan yang terlama dahulu (D30)', function () {
    $admin = User::factory()->admin()->create();

    foreach ([
        ['Nasabah Dicetak Maret', '2026-03-05 09:00:00'],
        ['Nasabah Dicetak Januari', '2026-01-10 09:00:00'],
        ['Nasabah Belum Pernah', null],
    ] as [$nama, $rekap]) {
        $nasabah = User::factory()->nasabah()->create(['name' => $nama]);
        NasabahProfil::create([
            'user_id' => $nasabah->id,
            'nama' => $nama,
            'alamat' => 'Jl. D30',
            'didaftarkan_oleh' => $admin->id,
            'status_pendaftaran' => 'aktif',
        ]);

        if ($rekap !== null) {
            NasabahProfil::where('user_id', $nasabah->id)->update(['rekap_dicetak_pada' => $rekap]);
        }
    }

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class)
        ->call('pilihSeksi', 'nasabah')
        ->assertSee('Rekap Terakhir Dicetak');

    $urut = collect($component->viewData('nasabahRows'))->pluck('rekap_terakhir_dicetak');

    expect($urut->all())->toBe(['Belum pernah', '10-01-2026', '05-03-2026']);
});

it('ekspor csv laporan nasabah memuat kolom rekap terakhir dicetak (D30)', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class)
        ->call('pilihSeksi', 'nasabah')
        ->call('exportCsv');

    $isi = base64_decode((string) data_get($component->effects, 'download.content'));

    expect($isi)->toContain('Rekap Terakhir Dicetak');
});

it('menyediakan dokumen SOP lapangan untuk operasional di luar kantor (D30)', function () {
    $path = base_path('docs/SOP_lapangan.md');

    expect(file_exists($path))->toBeTrue();

    $isi = (string) file_get_contents($path);

    expect($isi)
        ->toContain('nasabah offline')
        ->toContain('tanda tangan')
        ->toContain('minus')
        ->toContain('pengambilan di kantor')
        ->toContain('Cetak Rekap')
        ->toContain('bermasalah')
        ->and(mb_strlen($isi))->toBeLessThanOrEqual(12000);
});
