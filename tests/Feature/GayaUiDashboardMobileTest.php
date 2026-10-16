<?php

use App\Livewire\Nasabah\PengajuanAktif;
use App\Models\JadwalKunjungan;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

/**
 * Fase 5 migrasi DESIGN.md: dashboard mobile (cabang kolektor dan nasabah) serta
 * kartu pengajuan aktif memakai token dan komponen sistem desain. Pola lama yang
 * dilarang dokumen: gradien, blur, warna mentah di luar tabel pemetaan, font-mono
 * untuk nominal, teks di bawah 12px, bobot di atas 600, dan radius 24px di luar
 * hero dan sheet (§12).
 *
 * @return array<int, string>
 */
function polaLamaDashboardMobile(): array
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
        'uppercase',
        'rounded-3xl',
        'shadow-2xs',
        'shadow-lg',
        'shadow-xl',
        'shadow-2xl',
        // Palet warna mentah lama: pola regex dengan shade angka supaya nama
        // token sistem desain baru (bg-tile-teal-bg, fill-decor-gold) tidak
        // ikut tertangkap sebagai "warna mentah".
        '/-emerald-\d/',
        '/-rose-\d/',
        '/-sky-\d/',
        '/-indigo-\d/',
        '/-purple-\d/',
        '/-teal-\d/',
        '/-navy-\d/',
        '/-gold-\d/',
        '/bg-blue-\d/',
        '/text-blue-\d/',
        '/bg-amber-\d/',
        '/text-amber-\d/',
    ];
}

/**
 * Pindai pola lama pada isi dashboard. Larangan yang diawali "/" dibaca sebagai
 * regex. Tag pembuka <div data-test="hero-card"> dibuang lebih dahulu karena
 * radius 24px di dalam hero diizinkan DESIGN.md §12; pemakaian hero sendiri
 * diuji lewat asersi positif pada tiap tes.
 *
 * @param  array<int, string>  $larangan
 * @return array<int, string>
 */
function temukanPolaLamaDashboard(string $html, array $larangan): array
{
    $htmlTanpaHero = (string) preg_replace('/<div data-test="hero-card"[^>]*>/', '', $html);

    return array_values(array_filter(
        $larangan,
        fn (string $satu): bool => str_starts_with($satu, '/')
            ? preg_match($satu, $htmlTanpaHero) === 1
            : str_contains($htmlTanpaHero, $satu)
    ));
}

/**
 * Potong isi <main> agar pindai pola tidak membaca berkas CSS/JS yang disuntik
 * Flux di <head>.
 */
function potongIsiDashboard(string $html): string
{
    $mulai = strpos($html, '<main ');

    if ($mulai === false) {
        return '';
    }

    return (string) substr($html, $mulai, strpos($html, '</main>', $mulai) - $mulai);
}

/**
 * @return array{nasabah: User, produk: ProdukTabungan}
 */
function seedGayaUiDashboardNasabah(): array
{
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Dashboard Gaya',
        'alamat' => 'Jl. Dashboard No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Dashboard Gaya',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 250000,
    ]);

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $nasabah->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    return compact('nasabah', 'produk');
}

it('dashboard kolektor memakai kartu saldo hijau muda dan komponen rupiah', function () {
    $seed = kolektorDenganKas(120000);
    $kolektor = $seed['kolektor'];
    $nasabah = $seed['nasabah'];

    $nasabahAntre = User::factory()->nasabah()->create();
    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabahAntre->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    JadwalKunjungan::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_jadwal' => now()->toDateString(),
        'status_kunjungan' => 'dikunjungi',
    ]);
    JadwalKunjungan::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabahAntre->id,
        'tanggal_jadwal' => now()->toDateString(),
        'status_kunjungan' => null,
    ]);

    $isi = potongIsiDashboard($this->actingAs($kolektor)->get(route('dashboard'))->assertOk()->getContent());

    expect($isi)->not->toBe('')
        ->and(temukanPolaLamaDashboard($isi, polaLamaDashboardMobile()))->toBe([]);
    expect($isi)
        ->toContain('bg-primary-soft', false)
        ->toContain('data-test="rupiah"', false)
        ->toContain('Rp&nbsp;', false)
        ->toContain('Kas di Tangan')
        ->toContain('Rute kunjungan hari ini')
        ->toContain('Setor')
        ->toContain('rounded-full bg-primary', false);
});

it('dashboard nasabah memakai hero-card, sapaan waktu, dan komponen rupiah', function () {
    $nasabah = seedGayaUiDashboardNasabah()['nasabah'];

    $isi = potongIsiDashboard($this->actingAs($nasabah)->get(route('dashboard'))->assertOk()->getContent());

    expect($isi)->not->toBe('')
        ->and(temukanPolaLamaDashboard($isi, polaLamaDashboardMobile()))->toBe([]);
    expect($isi)
        ->toContain('bg-primary-soft', false)
        ->toContain('data-test="hero-card"', false)
        ->toContain('data-test="tile"', false)
        ->toContain('data-test="rupiah"', false)
        ->toContain('Rp&nbsp;', false)
        ->toContain('Total saldo Anda')
        ->toContain('Mau apa hari ini?')
        ->toContain('Aktivitas terakhir')
        ->toContain('Setoran hari ini sudah tercatat. Terima kasih!')
        ->toMatch('/Selamat (pagi|siang|sore|malam)/')
        ->toMatch('/Halo, [^<]+/')
        ->not->toContain('text-rose');
});

it('kartu pengajuan aktif memakai chip status warning dan danger dari tabel pemetaan', function () {
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => 'Bebas Gaya Dashboard',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'status' => 'aktif',
    ]);

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 100000,
        'persen_komisi_terpakai' => 0,
        'nominal_komisi' => 0,
        'nominal_diterima' => 100000,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'pending',
    ]);

    $html = Livewire::actingAs($nasabah)->test(PengajuanAktif::class)->html();

    expect(temukanPolaLamaDashboard($html, polaLamaDashboardMobile()))->toBe([]);
    expect($html)
        ->toContain('bg-warning-soft', false)
        ->toContain('text-warning-deep', false)
        ->toContain('data-test="rupiah"', false)
        ->toContain('Pengajuan Aktif');
});

it('kartu pengajuan aktif memakai chip info untuk penarikan yang sudah disetujui', function () {
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => 'Bebas Gaya Disetujui',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'status' => 'aktif',
    ]);

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 75000,
        'persen_komisi_terpakai' => 0,
        'nominal_komisi' => 0,
        'nominal_diterima' => 75000,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'approved',
        'waktu_approval' => now(),
    ]);

    $html = Livewire::actingAs($nasabah)->test(PengajuanAktif::class)->html();

    expect($html)
        ->toContain('bg-info-soft', false)
        ->toContain('text-info', false)
        ->toContain('Disetujui');
});
