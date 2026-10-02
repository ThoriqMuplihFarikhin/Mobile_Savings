<?php

use App\Livewire\Admin\HandoverKolektor;
use App\Models\KolektorNasabah;
use App\Models\LogHandoverKolektor;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

function seedHandoverKolektor(): array
{
    $admin = User::factory()->admin()->create();
    $lama = User::factory()->kolektor()->create();
    $baru = User::factory()->kolektor()->create();

    $nasabahs = collect([1, 2])->map(function () use ($admin) {
        $nasabah = User::factory()->nasabah()->create();

        NasabahProfil::create([
            'user_id' => $nasabah->id,
            'nama' => 'Nasabah Handover',
            'alamat' => 'Jl. Test No. 1',
            'jenis_kelamin' => 'perempuan',
            'didaftarkan_oleh' => $admin->id,
            'status_pendaftaran' => 'aktif',
        ]);

        return $nasabah;
    });

    $nasabahs->each(fn (User $nasabah) => KolektorNasabah::create([
        'kolektor_id' => $lama->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->subDays(10)->toDateString(),
        'status' => 'aktif',
        'aktif_unik' => 1,
    ]));

    return compact('admin', 'lama', 'baru', 'nasabahs');
}

it('memindahkan nasabah ke kolektor baru dan mengunci kolektor lama', function () {
    ['admin' => $admin, 'lama' => $lama, 'baru' => $baru] = seedHandoverKolektor();

    $this->actingAs($admin);

    Livewire::test(HandoverKolektor::class)
        ->set('kolektorLamaId', $lama->id)
        ->set('kolektorBaruId', $baru->id)
        ->call('processHandover')
        ->assertHasNoErrors();

    expect(KolektorNasabah::where('kolektor_id', $baru->id)->where('status', 'aktif')->count())->toBe(2)
        ->and(KolektorNasabah::where('kolektor_id', $lama->id)->where('status', 'aktif')->count())->toBe(0)
        ->and(KolektorNasabah::where('kolektor_id', $lama->id)->where('status', 'nonaktif')->count())->toBe(2);

    $this->assertDatabaseHas('log_handover_kolektor', [
        'kolektor_lama_id' => $lama->id,
        'kolektor_baru_id' => $baru->id,
        'jumlah_nasabah_dipindah' => 2,
        'status_kas_saat_handover' => 'lunas',
        'diproses_oleh' => $admin->id,
    ]);

    expect($lama->refresh()->status_akun)->toBe('terkunci')
        ->and($baru->refresh()->status_akun)->toBe('aktif')
        ->and(LogHandoverKolektor::count())->toBe(1);
});

it('memblokir handover jika masih ada kas yang belum disetor ke kantor', function () {
    ['admin' => $admin, 'lama' => $lama, 'baru' => $baru, 'nasabahs' => $nasabahs] = seedHandoverKolektor();

    $this->actingAs($admin);

    $component = Livewire::test(HandoverKolektor::class)
        ->set('kolektorLamaId', $lama->id)
        ->set('kolektorBaruId', $baru->id);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    TransaksiSetoran::create([
        'nasabah_id' => $nasabahs->first()->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $lama->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    $component->call('processHandover')->assertHasNoErrors();

    expect(LogHandoverKolektor::count())->toBe(0)
        ->and($lama->refresh()->status_akun)->toBe('aktif')
        ->and(KolektorNasabah::where('kolektor_id', $baru->id)->count())->toBe(0)
        ->and(KolektorNasabah::where('kolektor_id', $lama->id)->where('status', 'aktif')->count())->toBe(2);
});

it('menolak handover dengan kolektor baru yang sama dengan kolektor lama', function () {
    ['admin' => $admin, 'lama' => $lama] = seedHandoverKolektor();

    $this->actingAs($admin);

    Livewire::test(HandoverKolektor::class)
        ->set('kolektorLamaId', $lama->id)
        ->set('kolektorBaruId', $lama->id)
        ->call('processHandover')
        ->assertHasNoErrors();

    expect(LogHandoverKolektor::count())->toBe(0)
        ->and($lama->refresh()->status_akun)->toBe('aktif')
        ->and(KolektorNasabah::where('kolektor_id', $lama->id)->where('status', 'aktif')->count())->toBe(2);
});

it('menolak kolektor pengganti yang bukan kolektor aktif', function () {
    ['admin' => $admin, 'lama' => $lama] = seedHandoverKolektor();
    $nasabahLain = User::factory()->nasabah()->create();
    $kolektorTerkunci = User::factory()->kolektor()->create(['status_akun' => 'terkunci']);

    $this->actingAs($admin);

    Livewire::test(HandoverKolektor::class)
        ->set('kolektorLamaId', $lama->id)
        ->set('kolektorBaruId', $nasabahLain->id)
        ->call('processHandover')
        ->assertHasErrors(['kolektorBaruId']);

    Livewire::test(HandoverKolektor::class)
        ->set('kolektorLamaId', $lama->id)
        ->set('kolektorBaruId', $kolektorTerkunci->id)
        ->call('processHandover')
        ->assertHasErrors(['kolektorBaruId']);

    expect(LogHandoverKolektor::count())->toBe(0)
        ->and($lama->refresh()->status_akun)->toBe('aktif');
});

it('menghitung ulang kas dan daftar nasabah dari database saat proses handover', function () {
    ['admin' => $admin, 'lama' => $lama, 'baru' => $baru] = seedHandoverKolektor();

    $this->actingAs($admin);

    $component = Livewire::test(HandoverKolektor::class)
        ->set('kolektorLamaId', $lama->id)
        ->set('kolektorBaruId', $baru->id);

    $nasabahBaru = User::factory()->nasabah()->create();
    NasabahProfil::create([
        'user_id' => $nasabahBaru->id,
        'nama' => 'Nasabah Baru',
        'alamat' => 'Jl. Test No. 3',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);
    KolektorNasabah::create([
        'kolektor_id' => $lama->id,
        'nasabah_id' => $nasabahBaru->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $component->call('processHandover')->assertHasNoErrors();

    expect(KolektorNasabah::where('kolektor_id', $baru->id)->where('status', 'aktif')->count())->toBe(3)
        ->and(LogHandoverKolektor::first()->jumlah_nasabah_dipindah)->toBe(3);
});

it('melarang manipulasi properti kas lewat payload klien', function () {
    ['admin' => $admin, 'lama' => $lama] = seedHandoverKolektor();

    $this->actingAs($admin);

    $component = Livewire::test(HandoverKolektor::class)
        ->set('kolektorLamaId', $lama->id);

    expect(fn () => $component->set('unsettledCash', 0))
        ->toThrow(CannotUpdateLockedPropertyException::class);

    expect(fn () => $component->set('hasUnsettledCash', false))
        ->toThrow(CannotUpdateLockedPropertyException::class);
});
