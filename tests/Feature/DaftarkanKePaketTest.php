<?php

use App\Livewire\Admin\DetailNasabah;
use App\Livewire\Kolektor\NasabahBinaan;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/**
 * @return array{admin: User, kolektor: User, nasabah: User, produk: ProdukTabungan}
 */
function seedDaftarkanKePaket(): array
{
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Daftar Paket',
        'alamat' => 'Jl. Paket No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Paket Daftar Admin',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 10000,
        'periode_mulai' => now()->toDateString(),
        'periode_selesai' => now()->addDays(20)->toDateString(),
        'tanggal_boleh_cair' => now()->addDays(21)->toDateString(),
        'status' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    return compact('admin', 'kolektor', 'nasabah', 'produk');
}

function isiFormDaftarPaket(Testable $komponen, int $produkId, string $catatan = 'Nasabah setuju di kantor.'): Testable
{
    return $komponen
        ->set('produkDaftarId', $produkId)
        ->set('setujuDaftar', true)
        ->set('catatanDaftar', $catatan);
}

it('mendaftarkan nasabah ke paket oleh admin dengan komitmen catatan', function () {
    $data = seedDaftarkanKePaket();

    $this->actingAs($data['admin']);
    Livewire::test(DetailNasabah::class, ['user' => $data['nasabah']])
        ->call('bukaDaftarPaket')
        ->tap(fn ($k) => isiFormDaftarPaket($k, $data['produk']->id))
        ->call('daftarkanKePaket')
        ->assertHasNoErrors();

    $kepesertaan = KepesertaanPaket::where('nasabah_id', $data['nasabah']->id)
        ->where('produk_id', $data['produk']->id)
        ->firstOrFail();

    expect($kepesertaan->komitmen_via)->toBe('admin')
        ->and($kepesertaan->komitmen_dicatat_oleh)->toBe($data['admin']->id)
        ->and($kepesertaan->komitmen_catatan)->toBe('Nasabah setuju di kantor.')
        ->and($kepesertaan->komitmen_teks)->not->toBeEmpty()
        ->and($kepesertaan->komitmen_disetujui_pada)->not->toBeNull()
        ->and(SaldoProduk::where('nasabah_id', $data['nasabah']->id)
            ->where('produk_id', $data['produk']->id)->exists())->toBeTrue();
});

it('mendaftarkan binaan ke paket oleh kolektor', function () {
    $data = seedDaftarkanKePaket();

    $this->actingAs($data['kolektor']);
    Livewire::test(NasabahBinaan::class)
        ->call('bukaDaftarPaket', $data['nasabah']->id)
        ->tap(fn ($k) => isiFormDaftarPaket($k, $data['produk']->id))
        ->call('daftarkanKePaket')
        ->assertHasNoErrors();

    $kepesertaan = KepesertaanPaket::where('nasabah_id', $data['nasabah']->id)
        ->where('produk_id', $data['produk']->id)
        ->firstOrFail();

    expect($kepesertaan->komitmen_via)->toBe('kolektor')
        ->and($kepesertaan->komitmen_dicatat_oleh)->toBe($data['kolektor']->id);
});

it('menolak kolektor yang mendaftarkan nasabah di luar binaannya', function () {
    $data = seedDaftarkanKePaket();
    KolektorNasabah::where('kolektor_id', $data['kolektor']->id)->delete();

    $this->actingAs($data['kolektor']);
    Livewire::test(NasabahBinaan::class)
        ->call('bukaDaftarPaket', $data['nasabah']->id)
        ->tap(fn ($k) => isiFormDaftarPaket($k, $data['produk']->id))
        ->call('daftarkanKePaket')
        ->assertSet('pesanErrorDaftar', 'Nasabah ini bukan binaan Anda.');

    expect(KepesertaanPaket::count())->toBe(0);
});

it('mewajibkan centang persetujuan dan catatan pendaftaran', function () {
    $data = seedDaftarkanKePaket();

    $this->actingAs($data['admin']);
    Livewire::test(DetailNasabah::class, ['user' => $data['nasabah']])
        ->call('bukaDaftarPaket')
        ->set('produkDaftarId', $data['produk']->id)
        ->set('setujuDaftar', false)
        ->set('catatanDaftar', '')
        ->call('daftarkanKePaket')
        ->assertHasErrors(['setujuDaftar', 'catatanDaftar']);

    expect(KepesertaanPaket::count())->toBe(0);
});

it('menolak pendaftaran ganda pada paket yang sama', function () {
    $data = seedDaftarkanKePaket();

    KepesertaanPaket::create([
        'nasabah_id' => $data['nasabah']->id,
        'produk_id' => $data['produk']->id,
        'tanggal_mulai_ikut' => now()->toDateString(),
    ]);

    $this->actingAs($data['admin']);
    Livewire::test(DetailNasabah::class, ['user' => $data['nasabah']])
        ->call('bukaDaftarPaket')
        ->tap(fn ($k) => isiFormDaftarPaket($k, $data['produk']->id))
        ->call('daftarkanKePaket')
        ->assertSet('pesanErrorDaftar', 'Anda sudah mengikuti paket ini.');

    expect(KepesertaanPaket::count())->toBe(1);
});

it('menolak pendaftaran ke paket yang pendaftarannya sudah ditutup', function () {
    $data = seedDaftarkanKePaket();
    ProdukTabungan::whereKey($data['produk']->id)
        ->update(['batas_daftar_hingga' => now()->subDay()->toDateString()]);

    $this->actingAs($data['admin']);
    Livewire::test(DetailNasabah::class, ['user' => $data['nasabah']])
        ->call('bukaDaftarPaket')
        ->tap(fn ($k) => isiFormDaftarPaket($k, $data['produk']->id))
        ->call('daftarkanKePaket')
        ->assertSet('pesanErrorDaftar', 'Paket ini tidak lagi terbuka untuk pendaftaran.');

    expect(KepesertaanPaket::count())->toBe(0);
});
