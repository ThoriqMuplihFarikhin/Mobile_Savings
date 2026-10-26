<?php

use App\Actions\Penarikan\AjukanPenarikanAction;
use App\Livewire\Admin\Pengaturan;
use App\Livewire\Kolektor\PenarikanOffline;
use App\Livewire\Nasabah\AjukanPenarikan;
use App\Models\AdminSetting;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use Livewire\Livewire;

function produkPresisiD7(float $persen, string $status = 'aktif'): ProdukTabungan
{
    return ProdukTabungan::create([
        'nama' => uniqid('Produk Presisi '.$persen.'_'),
        'tipe' => 'bebas',
        'persen_komisi' => $persen,
        'minimal_setor' => 10000,
        'status' => $status,
    ]);
}

it('menjaga jumlah diterima dan komisi tetap persis pada komisi 2,5 persen', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = produkPresisiD7(2.5);

    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id, 'saldo' => 100000]);

    $hasil = (new AjukanPenarikanAction)->execute($nasabah, $produk, 10001, 'online');
    $tersimpan = $hasil->fresh();

    expect(bccomp(
        bcadd($tersimpan->nominal_diterima, $tersimpan->nominal_komisi, 2),
        $tersimpan->nominal_diminta,
        2
    ))->toBe(0)
        ->and($tersimpan->nominal_komisi)->toBe('250.03')
        ->and($tersimpan->nominal_diterima)->toBe('9750.97');
});

it('menjaga jumlah diterima dan komisi tetap persis pada komisi 3 persen nominal ganjil', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = produkPresisiD7(3.0);

    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id, 'saldo' => 100000]);

    $hasil = (new AjukanPenarikanAction)->execute($nasabah, $produk, 33333, 'online');
    $tersimpan = $hasil->fresh();

    expect(bccomp(
        bcadd($tersimpan->nominal_diterima, $tersimpan->nominal_komisi, 2),
        $tersimpan->nominal_diminta,
        2
    ))->toBe(0)
        ->and($tersimpan->nominal_komisi)->toBe('999.99')
        ->and($tersimpan->nominal_diterima)->toBe('32333.01');
});

it('menolak nominal nol negatif dan lebih dari dua desimal', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = produkPresisiD7(0);
    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id, 'saldo' => 100000]);

    $action = new AjukanPenarikanAction;

    expect(fn () => $action->execute($nasabah, $produk, 0, 'online'))
        ->toThrow(Exception::class);
    expect(fn () => $action->execute($nasabah, $produk, -1000, 'online'))
        ->toThrow(Exception::class);
    expect(fn () => $action->execute($nasabah, $produk, '50000.555', 'online'))
        ->toThrow(Exception::class);
});

it('menerapkan penarikan minimal default sepuluh ribu dengan pengecualian tarik habis', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = produkPresisiD7(0);
    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id, 'saldo' => 20000]);

    $action = new AjukanPenarikanAction;

    expect(fn () => $action->execute($nasabah, $produk, 5000, 'online'))
        ->toThrow(Exception::class);

    $habis = $action->execute($nasabah, $produk, 20000, 'online');

    expect($habis->status)->toBe('pending')
        ->and($habis->nominal_diminta)->toBe('20000.00');
});

it('menghormati kunci pengaturan penarikan minimal dan tetap mengizinkan tarik habis', function () {
    AdminSetting::set('penarikan_minimal', '50000');

    $nasabah = User::factory()->nasabah()->create();
    $produk = produkPresisiD7(0);
    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id, 'saldo' => 30000]);

    $action = new AjukanPenarikanAction;

    expect(fn () => $action->execute($nasabah, $produk, 5000, 'online'))
        ->toThrow(Exception::class);

    $habis = $action->execute($nasabah, $produk, 30000, 'online');

    expect($habis->status)->toBe('pending')
        ->and($habis->nominal_diminta)->toBe('30000.00');
});

it('memuat produk nonaktif bersaldo pada dropdown penarikan nasabah berlabel nonaktif', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produkAktif = produkPresisiD7(0, 'aktif');
    $produkBersaldo = produkPresisiD7(0, 'nonaktif');
    $produkKosong = produkPresisiD7(0, 'nonaktif');

    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produkBersaldo->id, 'saldo' => 15000]);

    $this->actingAs($nasabah);

    Livewire::test(AjukanPenarikan::class)
        ->assertSee($produkAktif->nama)
        ->assertSee($produkBersaldo->nama)
        ->assertSee('(nonaktif)')
        ->assertDontSee($produkKosong->nama);
});

it('memvalidasi nominal penarikan nasabah lebih dari nol dan maksimal dua desimal', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = produkPresisiD7(0);

    $this->actingAs($nasabah);

    Livewire::test(AjukanPenarikan::class)
        ->set('produkId', $produk->id)
        ->set('nominal', '10000.555')
        ->call('submit')
        ->assertHasErrors(['nominal' => 'decimal']);

    Livewire::test(AjukanPenarikan::class)
        ->set('produkId', $produk->id)
        ->set('nominal', '0')
        ->call('submit')
        ->assertHasErrors(['nominal' => 'gt']);
});

it('memvalidasi nominal penarikan kolektor lebih dari nol dan maksimal dua desimal', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();
    $produk = produkPresisiD7(0);

    $this->actingAs($kolektor);

    Livewire::test(PenarikanOffline::class)
        ->set('nasabahId', $nasabah->id)
        ->set('produkId', $produk->id)
        ->set('nominal', '10000.555')
        ->call('submit')
        ->assertHasErrors(['nominal' => 'decimal']);
});

it('menyimpan kunci penarikan minimal dari halaman pengaturan admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(Pengaturan::class)
        ->assertSet('penarikanMinimal', '10000')
        ->set('penarikanMinimal', '50000')
        ->call('simpanKonfigurasi');

    expect(AdminSetting::get('penarikan_minimal'))->toBe('50000');
});
