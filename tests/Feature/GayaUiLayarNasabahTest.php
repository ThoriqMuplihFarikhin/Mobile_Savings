<?php

use App\Livewire\Nasabah\AjukanPenarikan;
use App\Livewire\Nasabah\Komplain;
use App\Livewire\Nasabah\Notifikasi;
use App\Livewire\Nasabah\Pengaturan;
use App\Livewire\Nasabah\PilihPaket;
use App\Livewire\Nasabah\ProgresPaket;
use App\Livewire\Nasabah\RiwayatPenarikan;
use App\Livewire\Nasabah\RiwayatTabungan;
use App\Livewire\Nasabah\Saldo;
use App\Models\KepesertaanPaket;
use App\Models\Komplain as TiketKomplain;
use App\Models\LogNotifikasi;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

/**
 * Fase 2 migrasi DESIGN.md: layar nasabah memakai token dan komponen sistem
 * desain. Pola lama yang dilarang dokumen: gradien, blur, font-mono untuk
 * nominal, teks di bawah 12px, dan bobot di atas 600.
 *
 * @return array<int, string>
 */
function polaLamaLayarNasabah(): array
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
    ];
}

/**
 * @return array{nasabah: User, produk: ProdukTabungan}
 */
function seedGayaUiLayarNasabah(): array
{
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Gaya Ui',
        'alamat' => 'Jl. Gaya No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas Gaya',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    $paket = ProdukTabungan::create([
        'nama' => 'Paket Gaya',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 10000,
        'periode_mulai' => now()->toDateString(),
        'periode_selesai' => now()->addDays(29)->toDateString(),
        'tanggal_boleh_cair' => now()->addDays(30)->toDateString(),
        'isi_paket' => [
            ['nama' => 'Beras', 'jumlah' => '5 kg', 'harga' => 70000],
        ],
        'uang_tunai' => 50000,
        'status' => 'aktif',
    ]);

    $dasar = [
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $admin->id,
        'sumber_input' => 'real_time',
    ];

    TransaksiSetoran::create($dasar + ['nominal' => 20000, 'status' => 'tercatat']);
    TransaksiSetoran::create($dasar + [
        'nominal' => 15000,
        'nominal_asli' => 12000,
        'status' => 'dikoreksi',
    ]);
    TransaksiSetoran::create($dasar + ['nominal' => 9000, 'status' => 'dibatalkan']);

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'pending',
    ]);

    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $paket->id,
        'tanggal_mulai_ikut' => now()->toDateString(),
        'komitmen_disetujui_pada' => now(),
        'komitmen_via' => 'mandiri',
        'komitmen_dicatat_oleh' => $nasabah->id,
        'komitmen_teks' => 'Saya setuju komitmen paket.',
    ]);

    LogNotifikasi::create([
        'nasabah_id' => $nasabah->id,
        'judul' => 'Setoran dicatat',
        'pesan' => 'Setoran Rp 20.000 sudah masuk.',
        'jenis_notifikasi' => 'in_app',
        'channel' => 'in_app',
        'status_kirim' => 'terkirim',
        'is_read' => false,
        'waktu_kirim' => now(),
    ]);

    TiketKomplain::create([
        'nasabah_id' => $nasabah->id,
        'kategori' => 'saldo',
        'deskripsi' => 'Saldo saya belum bertambah setelah setoran kemarin.',
        'status' => 'diproses',
        'tanggal_dibuat' => now(),
    ]);

    return compact('nasabah', 'produk');
}

/**
 * @return array<int, string>
 */
function terlarangPada(string $html): array
{
    return array_values(array_filter(
        polaLamaLayarNasabah(),
        fn (string $larangan): bool => str_contains($html, $larangan)
    ));
}

it('layar riwayat tabungan memakai token desain, bukan gradien atau mono', function () {
    $nasabah = seedGayaUiLayarNasabah()['nasabah'];

    $html = Livewire::actingAs($nasabah)->test(RiwayatTabungan::class)->html();

    expect(terlarangPada($html))->toBe([]);
    expect($html)
        ->toContain('data-test="rupiah"', false)
        ->toContain('data-test="status-chip"', false)
        ->toContain('data-test="hero-card"', false)
        ->toContain('data-test="tile"', false)
        ->toContain('Rp&nbsp;', false);
});

it('biaya admin riwayat penarikan memakai minus ink, bukan merah', function () {
    $nasabah = seedGayaUiLayarNasabah()['nasabah'];

    $html = Livewire::actingAs($nasabah)->test(RiwayatPenarikan::class)->html();

    expect(terlarangPada($html))->toBe([]);
    expect($html)
        ->toContain("\u{2212}Rp&nbsp;", false)
        ->toContain('data-test="status-chip"', false)
        // §7: kartu token, ubin kategori biru, penanda tahap dari status.
        ->toContain('data-test="tile"', false)
        ->toContain('data-test="stepper"', false)
        ->not->toContain('text-rose');
});

it('layar progres paket memakai token desain', function () {
    $nasabah = seedGayaUiLayarNasabah()['nasabah'];

    $html = Livewire::actingAs($nasabah)->test(ProgresPaket::class)->html();

    expect(terlarangPada($html))->toBe([]);
    expect($html)
        ->toContain('data-test="rupiah"', false)
        ->toContain('data-test="status-chip"', false)
        // §7: pita header paket tile-teal-bg + cincin progres132px dan lencana.
        ->toContain('bg-tile-teal-bg', false)
        ->toContain('data-test="progress-ring"', false);
});

it('layar ikuti paket memakai token desain dan komponen rupiah', function () {
    $nasabah = seedGayaUiLayarNasabah()['nasabah'];

    ProdukTabungan::create([
        'nama' => 'Paket Masih Bisa Diikuti',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 10000,
        'periode_mulai' => now()->toDateString(),
        'periode_selesai' => now()->addDays(29)->toDateString(),
        'tanggal_boleh_cair' => now()->addDays(30)->toDateString(),
        'isi_paket' => [
            ['nama' => 'Minyak', 'jumlah' => '2 liter'],
        ],
        'status' => 'aktif',
    ]);

    $html = Livewire::actingAs($nasabah)->test(PilihPaket::class)->html();

    expect(terlarangPada($html))->toBe([]);
    expect($html)
        ->toContain('data-test="rupiah"', false)
        ->toContain('data-test="tile"', false);
});

it('layar notifikasi memakai token desain', function () {
    $nasabah = seedGayaUiLayarNasabah()['nasabah'];

    $html = Livewire::actingAs($nasabah)->test(Notifikasi::class)->html();

    expect(terlarangPada($html))->toBe([]);
    expect($html)->toContain('data-test="tile"', false);
});

it('layar komplain memakai chip status dan penanda tahap dari tabel pemetaan', function () {
    $nasabah = seedGayaUiLayarNasabah()['nasabah'];

    $html = Livewire::actingAs($nasabah)->test(Komplain::class)->html();

    expect(terlarangPada($html))->toBe([]);
    expect($html)
        ->toContain('data-test="status-chip"', false)
        ->toContain('data-test="stepper"', false);
});

it('layar pengaturan memakai token desain dan chip status akun', function () {
    $nasabah = seedGayaUiLayarNasabah()['nasabah'];

    $html = Livewire::actingAs($nasabah)->test(Pengaturan::class)->html();

    expect(terlarangPada($html))->toBe([]);
    expect($html)
        ->toContain('data-test="status-chip"', false)
        ->toContain('data-test="tile"', false);
});

it('layar ajukan penarikan memakai komponen rupiah untuk seluruh nominalnya', function () {
    $nasabah = seedGayaUiLayarNasabah()['nasabah'];

    $html = Livewire::actingAs($nasabah)->test(AjukanPenarikan::class)->html();

    expect($html)
        ->toContain('data-test="hero-card"', false)
        ->toContain('data-test="rupiah"', false)
        ->toContain('Rp&nbsp;', false);
});

it('kondisi kosong memakai ilustrasi tunas, judul ruangan, dan ajakan', function () {
    $nasabah = User::factory()->nasabah()->create();

    $html = Livewire::actingAs($nasabah)->test(RiwayatTabungan::class)->html();

    expect(terlarangPada($html))->toBe([]);
    expect($html)
        ->toContain('Belum ada setoran')
        ->toContain('size-32', false)
        ->toContain('rounded-full bg-primary-soft', false);
});

it('layar saldo memakai hero-card, ubin kategori, dan bar capaian teal', function () {
    $seed = seedGayaUiLayarNasabah();
    $nasabah = $seed['nasabah'];
    $paket = ProdukTabungan::where('tipe', 'paket')->firstOrFail();

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $seed['produk']->id,
        'saldo' => 35000,
    ]);
    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $paket->id,
        'saldo' => 150000,
    ]);

    $html = Livewire::actingAs($nasabah)->test(Saldo::class)->html();

    expect(terlarangPada($html))->toBe([]);
    expect($html)
        ->toContain('data-test="hero-card"', false)
        ->toContain('data-test="tile"', false)
        ->toContain('data-test="rupiah"', false)
        ->toContain('Rp&nbsp;', false)
        ->toContain('Total saldo')
        ->toContain('Ajukan penarikan')
        ->toContain('Lihat riwayat setoran')
        ->toContain('Rincian tabungan')
        // Bar capaian hanya untuk produk paket (targetAkhir tersedia).
        ->toContain('bg-tile-teal-bg', false)
        ->toContain('bg-tile-teal-fg', false)
        ->toContain('Capaian 50% dari target')
        ->not->toContain('text-rose');
});
