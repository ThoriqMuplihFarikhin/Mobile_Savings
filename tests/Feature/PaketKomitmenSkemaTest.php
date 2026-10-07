<?php

use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\User;

/**
 * @return array{admin: User, nasabah: User, produk: ProdukTabungan}
 */
function seedPaketKomitmenSkema(): array
{
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Komitmen',
        'tipe' => 'paket',
        'persen_komisi' => 5.00,
        'harga_per_hari' => 10000,
        'batas_toleransi_tunggakan_hari' => 3,
        'status' => 'aktif',
    ]);

    return compact('admin', 'nasabah', 'produk');
}

it('produk tabungan menyimpan opsi harga dan pencairan', function () {
    ['produk' => $produk] = seedPaketKomitmenSkema();

    $produkBaru = ProdukTabungan::create([
        'nama' => 'Paket Opsi',
        'tipe' => 'paket',
        'persen_komisi' => 5.00,
        'harga_per_hari' => 10000,
        'status' => 'aktif',
        'tampilkan_harga_ke_nasabah' => true,
        'boleh_cair_saat_target' => true,
        'batas_daftar_hingga' => '2026-12-31',
    ]);
    $produkBaru->refresh();

    expect($produkBaru->tampilkan_harga_ke_nasabah)->toBeTrue()
        ->and($produkBaru->boleh_cair_saat_target)->toBeTrue()
        ->and($produkBaru->batas_daftar_hingga->toDateString())->toBe('2026-12-31');
    expect($produk->refresh()->tampilkan_harga_ke_nasabah)->toBeFalse();
});

it('opsi produk baru bernilai default aman', function () {
    ['produk' => $produk] = seedPaketKomitmenSkema();

    $produk->refresh();

    expect($produk->tampilkan_harga_ke_nasabah)->toBeFalse()
        ->and($produk->boleh_cair_saat_target)->toBeFalse()
        ->and($produk->batas_daftar_hingga)->toBeNull();
});

it('kepesertaan menyimpan data komitmen dan null secara default', function () {
    ['nasabah' => $nasabah, 'produk' => $produk] = seedPaketKomitmenSkema();

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->toDateString(),
    ]);
    $kepesertaan->refresh();

    expect($kepesertaan->komitmen_disetujui_pada)->toBeNull()
        ->and($kepesertaan->komitmen_via)->toBeNull()
        ->and($kepesertaan->komitmen_dicatat_oleh)->toBeNull()
        ->and($kepesertaan->komitmen_teks)->toBeNull()
        ->and($kepesertaan->komitmen_catatan)->toBeNull();

    $kepesertaan->update([
        'komitmen_disetujui_pada' => now()->toDateTimeString(),
        'komitmen_via' => 'mandiri',
        'komitmen_dicatat_oleh' => $kepesertaan->nasabah_id,
        'komitmen_teks' => 'Saya berkomitmen menabung setiap hari.',
        'komitmen_catatan' => 'Disetujui lewat aplikasi.',
    ]);
    $kepesertaan->refresh();

    expect($kepesertaan->komitmen_via)->toBe('mandiri')
        ->and($kepesertaan->komitmen_teks)->toBe('Saya berkomitmen menabung setiap hari.')
        ->and($kepesertaan->komitmen_catatan)->toBe('Disetujui lewat aplikasi.')
        ->and($kepesertaan->komitmen_disetujui_pada)->not->toBeNull();
});

it('backfill menandai kepesertaan lama sebagai komitmen migrasi', function () {
    ['nasabah' => $nasabah, 'produk' => $produk] = seedPaketKomitmenSkema();

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(10)->toDateString(),
    ]);

    $sisa = (new AddKomitmenToKepesertaanPaketTable)->backfill();
    $kepesertaan->refresh();

    expect($kepesertaan->komitmen_via)->toBe('migrasi')
        ->and($kepesertaan->komitmen_disetujui_pada->toDateTimeString())
        ->toBe($kepesertaan->created_at->toDateTimeString())
        ->and($sisa)->toBe(0);

    expect((new AddKomitmenToKepesertaanPaketTable)->backfill())->toBe(0);
});
