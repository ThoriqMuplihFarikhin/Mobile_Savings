<?php

use App\Livewire\Nasabah\ProgresPaket;
use App\Models\KepesertaanPaket;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function buatNasabahDenganProdukT45(): array
{
    $user = User::factory()->nasabah()->create();
    NasabahProfil::create([
        'user_id' => $user->id,
        'nama' => $user->name,
        'alamat' => 'Jalan Contoh',
        'didaftarkan_oleh' => $user->id,
        'status_pendaftaran' => 'aktif',
    ]);
    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas T45',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    return [$user, $produk];
}

function hapusProdukTanpaCekFK(ProdukTabungan $produk): void
{
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    $produk->delete();
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
}

test('dashboard tetap tampil saat produk riwayat/saldo terhapus', function () {
    [$user, $produk] = buatNasabahDenganProdukT45();

    SaldoProduk::create(['nasabah_id' => $user->id, 'produk_id' => $produk->id, 'saldo' => 50000]);
    TransaksiSetoran::create([
        'nasabah_id' => $user->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $user->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    hapusProdukTanpaCekFK($produk);

    $this->actingAs($user);

    $this->get(route('dashboard'))->assertOk();
});

test('progres paket tetap tampil saat produk terhapus', function () {
    [$user, $produk] = buatNasabahDenganProdukT45();

    KepesertaanPaket::create([
        'nasabah_id' => $user->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(5)->toDateString(),
    ]);

    hapusProdukTanpaCekFK($produk);

    $this->actingAs($user);

    Livewire::test(ProgresPaket::class)->assertOk();
});
