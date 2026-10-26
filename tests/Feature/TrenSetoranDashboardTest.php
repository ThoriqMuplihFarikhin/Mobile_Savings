<?php

use App\Http\Controllers\DashboardController;
use App\Models\ProdukTabungan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @return array{0: Collection<int, array{tanggal: string, nominal: float}>, 1: int}
 */
function p52PanggilTrenSetoran(): array
{
    $controller = new DashboardController;
    $metode = new ReflectionMethod($controller, 'trenSetoranHarian');
    $metode->setAccessible(true);

    DB::enableQueryLog();

    try {
        $hasil = $metode->invoke($controller);
        $jumlahQuery = count(DB::getQueryLog());
    } finally {
        DB::flushQueryLog();
        DB::disableQueryLog();
    }

    return [$hasil, $jumlahQuery];
}

function p52InsertSetoran(int $inputBy, int $nasabahId, int $produkId, string $tanggal, float $nominal, string $status): void
{
    DB::table('transaksi_setoran')->insert([
        'nasabah_id' => $nasabahId,
        'produk_id' => $produkId,
        'nominal' => $nominal,
        'tanggal_transaksi' => $tanggal,
        'tanggal_input_sistem' => now(),
        'input_by' => $inputBy,
        'sumber_input' => 'real_time',
        'status' => $status,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function p52BuatFixtureTren(): void
{
    $nasabah = User::factory()->nasabah()->create();
    $kolektor = User::factory()->kolektor()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket P52',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 1000,
        'status' => 'aktif',
    ]);

    p52InsertSetoran($kolektor->id, $nasabah->id, $produk->id, now()->toDateString(), 10000, 'tercatat');
    p52InsertSetoran($kolektor->id, $nasabah->id, $produk->id, now()->subDays(3)->toDateString(), 5000, 'dikoreksi');
    p52InsertSetoran($kolektor->id, $nasabah->id, $produk->id, now()->subDays(3)->toDateString(), 7000, 'tercatat');
    p52InsertSetoran($kolektor->id, $nasabah->id, $produk->id, now()->subDays(40)->toDateString(), 999000, 'tercatat');
    p52InsertSetoran($kolektor->id, $nasabah->id, $produk->id, now()->toDateString(), 2000, 'dibatalkan');
}

it('menghasilkan tren setoran identik dengan perhitungan per hari dan maksimal 3 query', function () {
    p52BuatFixtureTren();

    [$tren, $jumlahQuery] = p52PanggilTrenSetoran();

    $ekspektasi = collect(range(29, 0))->map(fn ($i) => [
        'tanggal' => Carbon::today()->subDays($i)->format('d M'),
        'nominal' => (float) DB::table('transaksi_setoran')
            ->where('tanggal_transaksi', Carbon::today()->subDays($i))
            ->whereIn('status', ['tercatat', 'dikoreksi'])
            ->sum('nominal'),
    ])->values();

    expect($tren->toArray())->toBe($ekspektasi->toArray())
        ->and($tren)->toHaveCount(30)
        ->and($jumlahQuery)->toBeLessThanOrEqual(3);
});

it('mengisi hari kosong dengan nol saat tidak ada setoran sama sekali', function () {
    [$tren, $jumlahQuery] = p52PanggilTrenSetoran();

    expect($tren)->toHaveCount(30)
        ->and($tren->pluck('nominal')->sum())->toBe(0.0)
        ->and($jumlahQuery)->toBeLessThanOrEqual(3);
});
