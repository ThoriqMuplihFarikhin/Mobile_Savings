<?php

use App\Livewire\Admin\DetailNasabah;
use App\Livewire\Nasabah\ProgresPaket;
use App\Models\KepesertaanPaket;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\User;
use Livewire\Livewire;

/**
 * @return array{admin: User, nasabah: User, kepesertaan: KepesertaanPaket}
 */
function seedStatusKomitmen(): array
{
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Komitmen',
        'alamat' => 'Jl. Komitmen No. 1',
        'jenis_kelamin' => 'perempuan',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Paket Komitmen',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 10000,
        'periode_mulai' => now()->toDateString(),
        'periode_selesai' => now()->addDays(29)->toDateString(),
        'tanggal_boleh_cair' => now()->addDays(30)->toDateString(),
        'status' => 'aktif',
    ]);

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->toDateString(),
        'komitmen_disetujui_pada' => now(),
        'komitmen_via' => 'mandiri',
        'komitmen_dicatat_oleh' => $nasabah->id,
        'komitmen_teks' => 'Saya setuju komitmen paket.',
    ]);

    return compact('admin', 'nasabah', 'kepesertaan');
}

it('menampilkan pesan terikat komitmen di halaman progres nasabah', function () {
    $data = seedStatusKomitmen();

    $this->actingAs($data['nasabah']);
    Livewire::test(ProgresPaket::class)
        ->assertSee('Anda terikat komitmen paket ini');
});

it('tidak menampilkan pesan terikat pada kepesertaan yang sudah diputuskan', function () {
    $data = seedStatusKomitmen();
    KepesertaanPaket::whereKey($data['kepesertaan']->id)
        ->update(['keputusan_akhir' => 'gagal_dikembalikan']);

    $this->actingAs($data['nasabah']);
    Livewire::test(ProgresPaket::class)
        ->assertDontSee('Anda terikat komitmen paket ini');
});

it('menampilkan status komitmen tanggal dan via di halaman progres nasabah', function () {
    $data = seedStatusKomitmen();

    $this->actingAs($data['nasabah']);
    Livewire::test(ProgresPaket::class)
        ->assertSee('Komitmen')
        ->assertSee('Mandiri');
});

it('menampilkan status komitmen tanggal dan via di detail admin', function () {
    $data = seedStatusKomitmen();

    $this->actingAs($data['admin']);
    Livewire::test(DetailNasabah::class, ['user' => $data['nasabah']])
        ->assertSee('Komitmen')
        ->assertSee('Mandiri');
});
