<?php

use App\Livewire\Kolektor\JadwalKunjungan;
use App\Livewire\Kolektor\NasabahBinaan;
use App\Models\JadwalKunjungan as JadwalKunjunganModel;
use App\Models\KolektorNasabah;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * @return array{kolektor: User, nasabahA: User, nasabahB: User, nasabahNonaktif: User, nonBinaan: User}
 */
function p63Fixture(): array
{
    return [
        'kolektor' => User::factory()->kolektor()->create(),
        'nasabahA' => User::factory()->nasabah()->create(),
        'nasabahB' => User::factory()->nasabah()->create(),
        'nasabahNonaktif' => User::factory()->nasabah()->create(),
        'nonBinaan' => User::factory()->nasabah()->create(),
    ];
}

function p63Binaan(User $kolektor, User $nasabah, ?array $hari = null, string $status = 'aktif'): KolektorNasabah
{
    return KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => $status,
        'hari_kunjungan' => $hari,
    ]);
}

/**
 * @return array<int, int>|null
 */
function p63Hari(User $kolektor, User $nasabah): ?array
{
    $binaan = KolektorNasabah::where('kolektor_id', $kolektor->id)
        ->where('nasabah_id', $nasabah->id)
        ->first();

    return $binaan?->hari_kunjungan;
}

function p63HariIni(): int
{
    return (int) now()->isoWeekday();
}

function p63HariLain(): int
{
    $hari = p63HariIni();

    return $hari === 7 ? 1 : $hari + 1;
}

it('kolektor dapat mengatur hari kunjungan nasabah binaan', function () {
    ['kolektor' => $kolektor, 'nasabahA' => $nasabahA] = p63Fixture();
    p63Binaan($kolektor, $nasabahA);

    $tes = Livewire::actingAs($kolektor)->test(NasabahBinaan::class);

    $tes->call('toggleHariKunjungan', $nasabahA->id, 3);
    expect(p63Hari($kolektor, $nasabahA))->toBe([3]);

    $tes->call('toggleHariKunjungan', $nasabahA->id, 5);
    expect(p63Hari($kolektor, $nasabahA))->toBe([3, 5]);

    $tes->call('toggleHariKunjungan', $nasabahA->id, 3);
    expect(p63Hari($kolektor, $nasabahA))->toBe([5]);

    $tes->call('toggleHariKunjungan', $nasabahA->id, 5);
    expect(p63Hari($kolektor, $nasabahA))->toBeNull();
});

it('menolak atur hari untuk nasabah bukan binaan dan hari di luar rentang', function () {
    ['kolektor' => $kolektor, 'nasabahA' => $nasabahA, 'nonBinaan' => $nonBinaan] = p63Fixture();
    p63Binaan($kolektor, $nasabahA);

    $tes = Livewire::actingAs($kolektor)->test(NasabahBinaan::class);

    $tes->call('toggleHariKunjungan', $nonBinaan->id, 3);
    expect(p63Hari($kolektor, $nonBinaan))->toBeNull();

    $tes->call('toggleHariKunjungan', $nasabahA->id, 0);
    expect(p63Hari($kolektor, $nasabahA))->toBeNull();

    $tes->call('toggleHariKunjungan', $nasabahA->id, 8);
    expect(p63Hari($kolektor, $nasabahA))->toBeNull();
});

it('jadwal:generate membuat baris jadwal pada hari yang sesuai', function () {
    ['kolektor' => $kolektor, 'nasabahA' => $nasabahA, 'nasabahB' => $nasabahB, 'nasabahNonaktif' => $nonaktif] = p63Fixture();
    p63Binaan($kolektor, $nasabahA, [p63HariIni()]);
    p63Binaan($kolektor, $nasabahB, [p63HariLain()]);
    p63Binaan($kolektor, $nonaktif, [p63HariIni()], 'nonaktif');
    p63Binaan($kolektor, User::factory()->nasabah()->create(), null);

    $this->artisan('jadwal:generate')->assertSuccessful();

    $baris = DB::table('jadwal_kunjungan')->get();

    expect($baris)->toHaveCount(1)
        ->and((int) $baris->first()->kolektor_id)->toBe($kolektor->id)
        ->and((int) $baris->first()->nasabah_id)->toBe($nasabahA->id)
        ->and($baris->first()->tanggal_jadwal)->toBe(now()->toDateString())
        ->and($baris->first()->status_kunjungan)->toBeNull()
        ->and($baris->first()->catatan)->toBeNull();
});

it('jadwal:generate idempoten dan tidak mereset status yang sudah terisi', function () {
    ['kolektor' => $kolektor, 'nasabahA' => $nasabahA] = p63Fixture();
    p63Binaan($kolektor, $nasabahA, [p63HariIni()]);

    $this->artisan('jadwal:generate')->assertSuccessful();
    $this->artisan('jadwal:generate')->assertSuccessful();

    expect(JadwalKunjunganModel::count())->toBe(1);

    DB::table('jadwal_kunjungan')->update([
        'status_kunjungan' => 'dikunjungi',
        'catatan' => 'sudah dikunjungi',
    ]);

    $this->artisan('jadwal:generate')->assertSuccessful();

    $baris = JadwalKunjunganModel::sole();

    expect($baris->status_kunjungan)->toBe('dikunjungi')
        ->and($baris->catatan)->toBe('sudah dikunjungi');
});

it('unique index menolak jadwal ganda per kolektor nasabah dan tanggal', function () {
    ['kolektor' => $kolektor, 'nasabahA' => $nasabahA] = p63Fixture();
    $tanggal = now()->toDateString();

    $baris = [
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabahA->id,
        'tanggal_jadwal' => $tanggal,
        'status_kunjungan' => null,
        'catatan' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ];

    DB::table('jadwal_kunjungan')->insert($baris);

    expect(fn () => DB::table('jadwal_kunjungan')->insert($baris))
        ->toThrow(QueryException::class);
});

it('updateStatus memakai baris hasil generate tanpa membuat duplikat', function () {
    ['kolektor' => $kolektor, 'nasabahA' => $nasabahA] = p63Fixture();
    p63Binaan($kolektor, $nasabahA, [p63HariIni()]);

    $this->artisan('jadwal:generate')->assertSuccessful();

    Livewire::actingAs($kolektor)
        ->test(JadwalKunjungan::class)
        ->call('updateStatus', $nasabahA->id, 'dikunjungi');

    $baris = JadwalKunjunganModel::where('kolektor_id', $kolektor->id)
        ->where('nasabah_id', $nasabahA->id)
        ->where('tanggal_jadwal', now()->toDateString())
        ->get();

    expect($baris)->toHaveCount(1)
        ->and($baris->first()->status_kunjungan)->toBe('dikunjungi');
});

it('dashboard kolektor memakai jadwal hasil generate', function () {
    ['kolektor' => $kolektor, 'nasabahA' => $nasabahA] = p63Fixture();
    p63Binaan($kolektor, $nasabahA, [p63HariIni()]);

    $this->artisan('jadwal:generate')->assertSuccessful();

    $response = $this->actingAs($kolektor)->get(route('dashboard'));

    $response->assertOk();

    expect($response->viewData('jadwalHariIni'))->toHaveCount(1)
        ->and($response->viewData('stats')['kunjungan_total'])->toBe(1);
});

it('scheduler mendaftarkan jadwal:generate setiap pukul 00:30', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('jadwal:generate')
        ->assertExitCode(0);

    $event = collect(app(Schedule::class)->events())
        ->first(fn ($e) => str_contains((string) $e->command, 'jadwal:generate'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('30 0 * * *');
});
