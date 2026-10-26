<?php

use App\Models\ProdukTabungan;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * @return array<string, list<string>>
 */
function p51DaftarIndeks(string $tabel): array
{
    $baris = DB::select(
        'SELECT INDEX_NAME, COLUMN_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY INDEX_NAME, SEQ_IN_INDEX',
        [$tabel]
    );

    $peta = [];
    foreach ($baris as $barisIndeks) {
        $peta[$barisIndeks->INDEX_NAME][] = $barisIndeks->COLUMN_NAME;
    }

    return $peta;
}

beforeEach(function () {
    if (DB::connection()->getDriverName() !== 'mysql') {
        $this->markTestSkipped('Introspeksi information_schema hanya berjalan di MySQL.');
    }
});

it('memiliki indeks tanggal_transaksi dan komposit setoran ke kantor', function () {
    $indeks = p51DaftarIndeks('transaksi_setoran');

    expect($indeks)->toHaveKey('transaksi_setoran_tanggal_transaksi_index')
        ->and($indeks['transaksi_setoran_tanggal_transaksi_index'] ?? null)->toBe(['tanggal_transaksi'])
        ->and($indeks)->toHaveKey('transaksi_setoran_input_by_status_sudah_disetor_ke_kantor_index')
        ->and($indeks['transaksi_setoran_input_by_status_sudah_disetor_ke_kantor_index'] ?? null)
        ->toBe(['input_by', 'status', 'sudah_disetor_ke_kantor'])
        ->and($indeks)->toHaveKey('transaksi_setoran_setoran_kolektor_id_index');
});

it('mengikat setoran_kolektor_id dengan foreign key nullOnDelete', function () {
    $fk = DB::select(
        'SELECT kcu.REFERENCED_TABLE_NAME AS referensi, rc.DELETE_RULE AS aturan
         FROM information_schema.KEY_COLUMN_USAGE kcu
         JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
           ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
         WHERE kcu.TABLE_SCHEMA = DATABASE() AND kcu.TABLE_NAME = ? AND kcu.COLUMN_NAME = ?',
        ['transaksi_setoran', 'setoran_kolektor_id']
    );

    expect($fk)->toHaveCount(1)
        ->and($fk[0]->referensi)->toBe('setoran_kolektor_kantor')
        ->and($fk[0]->aturan)->toBe('SET NULL');
});

it('memiliki constraint check nominal setoran, nominal penarikan, dan saldo', function () {
    $cek = DB::select(
        'SELECT TABLE_NAME AS tabel, CONSTRAINT_NAME AS nama FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND CONSTRAINT_TYPE = ?',
        ['CHECK']
    );

    $nama = array_map(fn ($baris) => $baris->tabel.':'.$baris->nama, $cek);

    expect($nama)->toContain('saldo_produk:chk_saldo_produk_nonnegative')
        ->and($nama)->toContain('transaksi_setoran:chk_transaksi_setoran_nominal_positive')
        ->and($nama)->toContain('transaksi_penarikan:chk_transaksi_penarikan_nominal_diminta_positive');
});

it('menolak setoran yang menunjuk setoran kantor tidak ada', function () {
    $nasabah = User::factory()->nasabah()->create();
    $kolektor = User::factory()->kolektor()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket P51 FK',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 1000,
        'status' => 'aktif',
    ]);

    expect(fn () => DB::table('transaksi_setoran')->insert([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 5000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'setoran_kolektor_id' => 999999,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});

it('menolak saldo, setoran, dan penarikan yang melanggar constraint di level database', function () {
    $nasabah = User::factory()->nasabah()->create();
    $kolektor = User::factory()->kolektor()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket P51',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 1000,
        'status' => 'aktif',
    ]);

    expect(fn () => DB::table('saldo_produk')->insert([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => -1,
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('transaksi_setoran')->insert([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 0,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('transaksi_penarikan')->insert([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 0,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 0,
        'nominal_diterima' => 0,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'created_at' => now(),
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});
