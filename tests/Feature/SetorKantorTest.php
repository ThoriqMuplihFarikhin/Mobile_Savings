<?php

use App\Livewire\Admin\MonitoringSetoran;
use App\Livewire\Admin\RekonsiliasiKas;
use App\Livewire\Kolektor\SetorKantor;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

function seedSetorKantor(): array
{
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Setor Kantor',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 0,
    ]);

    return compact('admin', 'kolektor', 'nasabah', 'produk');
}

function buatSetoranUntukKantor(User $kolektor, User $nasabah, ProdukTabungan $produk, float $nominal): TransaksiSetoran
{
    SaldoProduk::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->increment('saldo', $nominal);

    return TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => $nominal,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);
}

it('melarang manipulasi total belum disetor lewat payload klien', function () {
    ['kolektor' => $kolektor] = seedSetorKantor();

    $this->actingAs($kolektor);

    $component = Livewire::test(SetorKantor::class);

    expect(fn () => $component->set('totalBelumDisetor', 1))
        ->toThrow(CannotUpdateLockedPropertyException::class);

    expect(fn () => $component->set('jumlahTransaksi', 99))
        ->toThrow(CannotUpdateLockedPropertyException::class);
});

it('menghitung total pengajuan setor kantor dari database saat submit', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedSetorKantor();

    $this->actingAs($kolektor);

    $component = Livewire::test(SetorKantor::class);

    buatSetoranUntukKantor($kolektor, $nasabah, $produk, 50000);
    buatSetoranUntukKantor($kolektor, $nasabah, $produk, 30000);

    $component->call('submit');

    $pengajuan = SetoranKolektorKantor::where('kolektor_id', $kolektor->id)->first();

    expect($pengajuan)->not->toBeNull()
        ->and((float) $pengajuan->total_seharusnya)->toBe(80000.00)
        ->and($pengajuan->status)->toBe('pending');

    expect(TransaksiSetoran::where('setoran_kolektor_id', $pengajuan->id)->count())->toBe(2);
});

it('membuat satu pengajuan saja meski tombol submit ditekan dua kali', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedSetorKantor();

    $this->actingAs($kolektor);

    buatSetoranUntukKantor($kolektor, $nasabah, $produk, 50000);

    $component = Livewire::test(SetorKantor::class);
    $component->call('submit');
    $component->call('submit');

    expect(SetoranKolektorKantor::where('kolektor_id', $kolektor->id)->count())->toBe(1);
});

it('menolak rekon manual admin jika kolektor masih punya pengajuan pending', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedSetorKantor();

    buatSetoranUntukKantor($kolektor, $nasabah, $produk, 50000);

    $this->actingAs($kolektor);
    Livewire::test(SetorKantor::class)->call('submit');

    expect(SetoranKolektorKantor::count())->toBe(1);

    $this->actingAs($admin);
    Livewire::test(RekonsiliasiKas::class)
        ->set('kolektorId', $kolektor->id)
        ->set('totalDiterima', 50000)
        ->call('submit');

    expect(SetoranKolektorKantor::where('kolektor_id', $kolektor->id)->count())->toBe(1)
        ->and(SetoranKolektorKantor::where('kolektor_id', $kolektor->id)->first()->status)->toBe('pending');
});

it('menghitung ulang total seharusnya saat admin memproses pengajuan setoran', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedSetorKantor();

    $setoranBesar = buatSetoranUntukKantor($kolektor, $nasabah, $produk, 50000);
    buatSetoranUntukKantor($kolektor, $nasabah, $produk, 30000);

    $this->actingAs($kolektor);
    Livewire::test(SetorKantor::class)->call('submit');

    $pengajuan = SetoranKolektorKantor::where('kolektor_id', $kolektor->id)->firstOrFail();

    $this->actingAs($admin);
    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $setoranBesar->id)
        ->set('nominalBaru', 40000)
        ->set('alasanKoreksi', 'Salah input nominal')
        ->call('koreksi')
        ->assertHasNoErrors();

    Livewire::test(RekonsiliasiKas::class)
        ->set('processTotalDiterima', 70000)
        ->call('processSubmission', $pengajuan->id)
        ->assertHasNoErrors();

    $pengajuan->refresh();

    expect((float) $pengajuan->total_seharusnya)->toBe(70000.00)
        ->and((float) $pengajuan->total_diterima)->toBe(70000.00)
        ->and($pengajuan->status)->toBe('cocok');
});
