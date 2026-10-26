<?php

use App\Helpers\ActivityLogger;
use App\Livewire\Admin\ManajemenNasabah;
use App\Models\LogAktivitas;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

function nasabahAuditP42(User $admin): array
{
    $nasabah = User::factory()->nasabah()->create();

    $profil = NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Audit P42',
        'alamat' => 'Jl. Audit No. 1',
        'tanggal_lahir' => '1990-01-01',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    return ['nasabah' => $nasabah, 'profil' => $profil];
}

it('mempertahankan log aktivitas lama dengan user_id null setelah nasabah dihapus', function () {
    $admin = User::factory()->admin()->create();
    ['nasabah' => $nasabah, 'profil' => $profil] = nasabahAuditP42($admin);

    ActivityLogger::log('setor', 'transaksi_setoran', 99, ['nominal' => 50000], $nasabah->id);
    $logLamaId = LogAktivitas::latest('id')->firstOrFail()->id;

    $this->actingAs($admin);

    Livewire::test(ManajemenNasabah::class)
        ->call('confirmDelete', $profil->id)
        ->call('delete')
        ->assertSet('tampilKonfirmasiHapus', false);

    $this->assertDatabaseMissing('users', ['id' => $nasabah->id]);

    $log = LogAktivitas::find($logLamaId);

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBeNull();

    $hapus = LogAktivitas::where('aksi', 'hapus_nasabah')
        ->where('entitas_terkait', 'users')
        ->where('entitas_id', $nasabah->id)
        ->first();

    expect($hapus)->not->toBeNull()
        ->and($hapus->user_id)->toBe($admin->id)
        ->and($hapus->detail['id_lama'])->toBe($nasabah->id)
        ->and($hapus->detail['nama'])->toBe($profil->nama)
        ->and($hapus->detail['no_hp_masked'])->toBe(Str::mask($nasabah->no_hp, '*', 4, -3))
        ->and(json_encode($hapus->detail))->not->toContain($nasabah->no_hp);
});

it('guard keuangan tetap menolak dan tidak mencatat log hapus_nasabah', function () {
    $admin = User::factory()->admin()->create();
    ['nasabah' => $nasabah, 'profil' => $profil] = nasabahAuditP42($admin);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Audit P42',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 50000,
    ]);

    ActivityLogger::log('setor', 'transaksi_setoran', 99, ['nominal' => 50000], $nasabah->id);
    $logLamaId = LogAktivitas::latest('id')->firstOrFail()->id;

    $this->actingAs($admin);

    Livewire::test(ManajemenNasabah::class)
        ->call('confirmDelete', $profil->id)
        ->call('delete');

    expect(LogAktivitas::where('aksi', 'hapus_nasabah')->exists())->toBeFalse()
        ->and(LogAktivitas::find($logLamaId))->not->toBeNull();

    $this->assertDatabaseHas('users', ['id' => $nasabah->id]);
    $this->assertDatabaseHas('nasabah_profil', ['id' => $profil->id]);
});
