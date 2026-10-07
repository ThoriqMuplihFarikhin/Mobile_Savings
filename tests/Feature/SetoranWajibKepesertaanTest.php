<?php

use App\Actions\Setoran\CatatSetoranAction;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;

/**
 * @return array{nasabah: User, admin: User, produkPaket: ProdukTabungan, produkBebas: ProdukTabungan}
 */
function seedSetoranWajibKepesertaan(): array
{
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();

    $produkPaket = ProdukTabungan::create([
        'nama' => 'Paket Wajib Kepesertaan',
        'tipe' => 'paket',
        'persen_komisi' => 5.00,
        'harga_per_hari' => 10000,
        'status' => 'aktif',
    ]);

    $produkBebas = ProdukTabungan::create([
        'nama' => 'Produk Bebas Wajib',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    return compact('admin', 'nasabah', 'produkPaket', 'produkBebas');
}

/**
 * @param  array{nasabah: User, admin: User, produkPaket: ProdukTabungan, produkBebas: ProdukTabungan}  $data
 * @param  array<string, mixed>  $perubahan
 * @return array<string, mixed>
 */
function payloadSetoranWajibKepesertaan(array $data, array $perubahan = []): array
{
    return array_merge([
        'nasabah_id' => $data['nasabah']->id,
        'produk_id' => $data['produkPaket']->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'sumber_input' => 'real_time',
        'input_by' => $data['admin']->id,
        'catatan' => null,
        'sudah_disetor_ke_kantor' => true,
        'idempotency_key' => 'wajib-kepesertaan-'.uniqid(),
    ], $perubahan);
}

it('menolak setoran paket ketika nasabah belum terdaftar di paket', function () {
    $data = seedSetoranWajibKepesertaan();

    expect(fn () => app(CatatSetoranAction::class)->execute(payloadSetoranWajibKepesertaan($data)))
        ->toThrow(DomainException::class, 'Nasabah belum terdaftar di paket ini. Daftarkan terlebih dahulu.');

    $this->assertDatabaseCount('transaksi_setoran', 0);
    expect(SaldoProduk::where('nasabah_id', $data['nasabah']->id)->count())->toBe(0);
});

it('tetap memperbolehkan setoran lanjutan setelah penolakan karena idempotency key tidak terkunci', function () {
    $data = seedSetoranWajibKepesertaan();

    expect(fn () => app(CatatSetoranAction::class)->execute(payloadSetoranWajibKepesertaan($data)))
        ->toThrow(DomainException::class, 'Nasabah belum terdaftar di paket ini. Daftarkan terlebih dahulu.');

    KepesertaanPaket::create([
        'nasabah_id' => $data['nasabah']->id,
        'produk_id' => $data['produkPaket']->id,
        'tanggal_mulai_ikut' => now()->subDay()->toDateString(),
    ]);

    app(CatatSetoranAction::class)->execute(payloadSetoranWajibKepesertaan($data));

    $this->assertDatabaseCount('transaksi_setoran', 1);
});

it('menolak setoran paket ketika kepesertaan sudah berakhir dengan keputusan akhir', function () {
    $data = seedSetoranWajibKepesertaan();

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $data['nasabah']->id,
        'produk_id' => $data['produkPaket']->id,
        'tanggal_mulai_ikut' => now()->subDays(5)->toDateString(),
    ]);
    KepesertaanPaket::whereKey($kepesertaan->id)->update(['keputusan_akhir' => 'gagal_dikembalikan']);

    expect(fn () => app(CatatSetoranAction::class)->execute(payloadSetoranWajibKepesertaan($data)))
        ->toThrow(DomainException::class, 'Nasabah belum terdaftar di paket ini. Daftarkan terlebih dahulu.');

    $this->assertDatabaseCount('transaksi_setoran', 0);
});

it('mencatat setoran pakat ketika kepesertaan aktif ada dan menautkan kepesertaannya', function () {
    $data = seedSetoranWajibKepesertaan();

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $data['nasabah']->id,
        'produk_id' => $data['produkPaket']->id,
        'tanggal_mulai_ikut' => now()->subDay()->toDateString(),
    ]);

    $transaksi = app(CatatSetoranAction::class)->execute(payloadSetoranWajibKepesertaan($data));

    expect($transaksi->kepesertaan_id)->toBe($kepesertaan->id)
        ->and(SaldoProduk::where('nasabah_id', $data['nasabah']->id)->value('saldo'))->toBe('50000.00');
});

it('mencatat setoran produk bebas tanpa kepesertaan paket', function () {
    $data = seedSetoranWajibKepesertaan();

    $transaksi = app(CatatSetoranAction::class)->execute(
        payloadSetoranWajibKepesertaan($data, ['produk_id' => $data['produkBebas']->id])
    );

    expect($transaksi->kepesertaan_id)->toBeNull()
        ->and(SaldoProduk::where('nasabah_id', $data['nasabah']->id)->value('saldo'))->toBe('50000.00');
});
