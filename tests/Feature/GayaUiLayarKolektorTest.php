<?php

use App\Livewire\Kolektor\Absen;
use App\Livewire\Kolektor\AjukanIzin;
use App\Livewire\Kolektor\DaftarNasabah;
use App\Livewire\Kolektor\InputSetoran;
use App\Livewire\Kolektor\JadwalKunjungan;
use App\Livewire\Kolektor\NasabahBinaan;
use App\Livewire\Kolektor\PenarikanOffline;
use App\Livewire\Kolektor\Pengaturan;
use App\Livewire\Kolektor\RiwayatAbsensi;
use App\Livewire\Kolektor\SetorKantor;
use App\Livewire\Kolektor\VerifikasiPenarikan;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

/**
 * Fase 3 migrasi DESIGN.md: layar kolektor memakai token dan komponen sistem
 * desain. Pola lama yang dilarang dokumen: gradien, blur, warna mentah di luar
 * tabel pemetaan, font-mono untuk nominal, teks di bawah 12px, dan bobot di
 * atas 600.
 *
 * @return array<int, string>
 */
function polaLamaLayarKolektor(): array
{
    return [
        'bg-gradient',
        'blur-',
        'font-mono',
        'text-[9px]',
        'text-[10px]',
        'text-[11px]',
        'font-bold',
        'font-extrabold',
        'font-black',
        'rounded-3xl',
        'uppercase tracking',
        'emerald-',
        'rose-',
        'sky-',
        'bg-blue-',
        'text-blue-',
        'bg-amber-',
        'text-amber-',
        'shadow-2xl',
    ];
}

/**
 * @return array{kolektor: User, nasabah: User, produk: ProdukTabungan}
 */
function seedGayaUiLayarKolektor(): array
{
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create(['mode_akses' => 'offline']);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Kolektor Gaya',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Gaya Kolektor',
        'alamat' => 'Jl. Kolektor No. 1',
        'jenis_kelamin' => 'laki-laki',
        'tanggal_lahir' => '1990-05-15',
        'didaftarkan_oleh' => $kolektor->id,
        'status_pendaftaran' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 150000,
    ]);

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
        'sudah_disetor_ke_kantor' => false,
    ]);

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'approved',
    ]);

    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_setor' => now()->subDays(2)->toDateString(),
        'total_seharusnya' => 50000,
        'total_diterima' => 45000,
        'selisih' => -5000,
        'status' => 'kurang',
    ]);

    return compact('kolektor', 'nasabah', 'produk');
}

/**
 * @return array<int, string>
 */
function terlarangPadaKolektor(string $html): array
{
    return array_values(array_filter(
        polaLamaLayarKolektor(),
        fn (string $larangan): bool => str_contains($html, $larangan)
    ));
}

it('layar input setoran memakai token desain, bukan gradien atau mono', function () {
    $kolektor = seedGayaUiLayarKolektor()['kolektor'];

    $html = Livewire::actingAs($kolektor)->test(InputSetoran::class)->html();

    expect(terlarangPadaKolektor($html))->toBe([]);
    expect($html)->toContain('data-test="rupiah"', false);
});

it('layar setor kantor memakai chip status dari tabel pemetaan', function () {
    $kolektor = seedGayaUiLayarKolektor()['kolektor'];

    $html = Livewire::actingAs($kolektor)->test(SetorKantor::class)->html();

    expect(terlarangPadaKolektor($html))->toBe([]);
    expect($html)
        ->toContain('data-test="rupiah"', false)
        ->toContain('data-test="status-chip"', false);
});

it('layar daftar nasabah memakai chip status dan label mode offline', function () {
    $kolektor = seedGayaUiLayarKolektor()['kolektor'];

    $html = Livewire::actingAs($kolektor)->test(DaftarNasabah::class)->html();

    expect(terlarangPadaKolektor($html))->toBe([]);
    expect($html)
        ->toContain('data-test="status-chip"', false)
        ->toContain('Mode Offline', false);
});

it('layar absen memakai token desain tanpa gradien hero', function () {
    $kolektor = seedGayaUiLayarKolektor()['kolektor'];

    $html = Livewire::actingAs($kolektor)->test(Absen::class)->html();

    expect(terlarangPadaKolektor($html))->toBe([]);
    expect($html)->toContain('canvas-ttd', false);
});

it('layar riwayat absensi memakai token desain', function () {
    $kolektor = seedGayaUiLayarKolektor()['kolektor'];

    $html = Livewire::actingAs($kolektor)->test(RiwayatAbsensi::class)->html();

    expect(terlarangPadaKolektor($html))->toBe([]);
});

it('layar jadwal kunjungan memakai chip status kunjungan', function () {
    $kolektor = seedGayaUiLayarKolektor()['kolektor'];

    $html = Livewire::actingAs($kolektor)->test(JadwalKunjungan::class)->html();

    expect(terlarangPadaKolektor($html))->toBe([]);
});

it('layar ajukan izin memakai chip status persetujuan', function () {
    $kolektor = seedGayaUiLayarKolektor()['kolektor'];

    $html = Livewire::actingAs($kolektor)->test(AjukanIzin::class)->html();

    expect(terlarangPadaKolektor($html))->toBe([]);
});

it('layar pengaturan kolektor datar tanpa gradien profil', function () {
    $kolektor = seedGayaUiLayarKolektor()['kolektor'];

    $html = Livewire::actingAs($kolektor)->test(Pengaturan::class)->html();

    expect(terlarangPadaKolektor($html))->toBe([]);
    expect($html)
        ->toContain('Ajukan izin / cuti', false)
        ->not->toContain('chip-peran', false);
});

it('layar nasabah binaan memakai komponen rupiah untuk seluruh nominalnya', function () {
    $kolektor = seedGayaUiLayarKolektor()['kolektor'];

    $html = Livewire::actingAs($kolektor)->test(NasabahBinaan::class)->html();

    expect(terlarangPadaKolektor($html))->toBe([]);
    expect($html)
        ->toContain('data-test="rupiah"', false)
        ->toContain('Rp&nbsp;', false);
});

it('layar penarikan offline memakai minus ink untuk komisi', function () {
    $sumber = seedGayaUiLayarKolektor();

    $html = Livewire::actingAs($sumber['kolektor'])
        ->test(PenarikanOffline::class)
        ->set('nasabahId', $sumber['nasabah']->id)
        ->set('produkId', $sumber['produk']->id)
        ->set('nominal', '50000')
        ->html();

    expect(terlarangPadaKolektor($html))->toBe([]);
    expect($html)
        ->toContain("\u{2212}Rp&nbsp;", false)
        ->not->toContain('text-rose');
});

it('layar verifikasi penarikan memakai chip status penarikan', function () {
    $kolektor = seedGayaUiLayarKolektor()['kolektor'];

    $html = Livewire::actingAs($kolektor)->test(VerifikasiPenarikan::class)->html();

    expect(terlarangPadaKolektor($html))->toBe([]);
    expect($html)
        ->toContain('data-test="status-chip"', false)
        ->toContain('data-test="rupiah"', false);
});
