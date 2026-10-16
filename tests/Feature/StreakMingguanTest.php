<?php

use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Fase E butir D: titik streak mingguan di beranda nasabah (DESIGN.md §7
 * "Streak": tujuh lingkaran 30px S S R K J S M; hari tanpa setoran = lingkaran
 * kosong berborder hairline-strong, bukan merah) dan §14 baris Beranda (data
 * setoran per hari ditambahkan di DashboardController — jumlah $streak lama
 * tetap dihitung terpisah). Setoran ber-tanggal masa depan diabaikan, konsisten
 * dengan tes streak lama.
 */
function streakMingguanFixture(): array
{
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => uniqid('Produk Streak '),
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    return [$nasabah, $produk];
}

function streakMingguanSetoran(User $nasabah, ProdukTabungan $produk, string $tanggal): void
{
    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 10000,
        'tanggal_transaksi' => $tanggal,
        'tanggal_input_sistem' => $tanggal,
        'input_by' => $nasabah->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);
}

it('menyusun tujuh titik minggu berjalan dan menandai hari ber-setoran', function () {
    [$nasabah, $produk] = streakMingguanFixture();

    streakMingguanSetoran($nasabah, $produk, Carbon::today()->toDateString());

    $this->actingAs($nasabah)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('streakMingguIni', function (Collection $titik) {
            expect($titik)->toHaveCount(7);
            expect(collect($titik)->pluck('label')->all())->toBe(['S', 'S', 'R', 'K', 'J', 'S', 'M']);

            $hariIni = collect($titik)->firstWhere('tanggal', Carbon::today()->toDateString());

            return $hariIni['ada_setoran'] === true;
        })
        ->assertSee('Setoran minggu ini');
});

it('menampilkan lingkaran kosong berborder untuk hari tanpa setoran', function () {
    [$nasabah] = streakMingguanFixture();

    $response = $this->actingAs($nasabah)->get(route('dashboard'));

    $response->assertOk()
        ->assertViewHas('streakMingguIni', fn (Collection $titik) => collect($titik)->where('ada_setoran', true)->count() === 0)
        ->assertSee('border-2 border-hairline-strong', false);

    expect(substr_count($response->getContent(), 'flex h-[30px] w-[30px]'))->toBe(7);
});

it('mengabaikan setoran ber-tanggal masa depan dan sebelum minggu berjalan', function () {
    [$nasabah, $produk] = streakMingguanFixture();

    streakMingguanSetoran($nasabah, $produk, Carbon::today()->addDay()->toDateString());
    streakMingguanSetoran($nasabah, $produk, Carbon::today()->subDays(8)->toDateString());

    $this->actingAs($nasabah)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('streakMingguIni', fn (Collection $titik) => collect($titik)->where('ada_setoran', true)->count() === 0);
});

it('menandai hari Senin minggu berjalan pada titik pertama', function () {
    [$nasabah, $produk] = streakMingguanFixture();

    $seninMingguIni = Carbon::today()->startOfWeek(Carbon::MONDAY)->toDateString();
    streakMingguanSetoran($nasabah, $produk, $seninMingguIni);

    $this->actingAs($nasabah)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('streakMingguIni', function (Collection $titik) use ($seninMingguIni) {
            $tertandai = collect($titik)->where('ada_setoran', true);

            return $tertandai->count() === 1
                && $tertandai->first()['tanggal'] === $seninMingguIni;
        });
});
