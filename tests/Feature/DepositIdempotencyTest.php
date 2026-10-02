<?php

use App\Livewire\Kolektor\InputSetoran;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use App\Services\WhatsAppService;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

function seedSetoranIdempotensi(?ProdukTabungan $produkOverride = null): array
{
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Idempotensi',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = $produkOverride ?? ProdukTabungan::create([
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

    return compact('admin', 'kolektor', 'nasabah', 'produk');
}

function isiFormSetoran(array $data, array $options = []): Testable
{
    return Livewire::test(InputSetoran::class)
        ->set('nasabahId', $data['nasabah']->id)
        ->set('produkId', $data['produk']->id)
        ->set('nominal', $options['nominal'] ?? 50000)
        ->set('tanggal_transaksi', $options['tanggal'] ?? now()->toDateString())
        ->set('sumber_input', $options['sumber'] ?? 'real_time')
        ->set('catatan', $options['catatan'] ?? '');
}

it('tetap mencatat setoran walau notifikasi whatsapp gagal', function () {
    $data = seedSetoranIdempotensi();

    app()->instance(WhatsAppService::class, new class
    {
        public function sendNotification(string $phone, string $message): bool
        {
            throw new RuntimeException('Gateway WhatsApp sedang down.');
        }
    });

    $this->actingAs($data['kolektor']);

    isiFormSetoran($data)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('showSuccess', true);

    $this->assertDatabaseCount('transaksi_setoran', 1);

    $saldo = SaldoProduk::where('nasabah_id', $data['nasabah']->id)
        ->where('produk_id', $data['produk']->id)
        ->first();
    expect($saldo->saldo)->toBe('50000.00');
});

it('hanya membuat satu setoran untuk idempotency key yang sama', function () {
    $data = seedSetoranIdempotensi();

    $this->actingAs($data['kolektor']);

    isiFormSetoran($data)
        ->set('idempotencyKey', 'klik-ganda-1')
        ->call('submit')
        ->assertHasNoErrors();

    isiFormSetoran($data)
        ->set('idempotencyKey', 'klik-ganda-1')
        ->call('submit');

    $this->assertDatabaseCount('transaksi_setoran', 1);

    $saldo = SaldoProduk::where('nasabah_id', $data['nasabah']->id)
        ->where('produk_id', $data['produk']->id)
        ->first();
    expect($saldo->saldo)->toBe('50000.00');
});

it('menolak setoran di bawah minimal_setor produk', function () {
    $data = seedSetoranIdempotensi();

    $this->actingAs($data['kolektor']);

    isiFormSetoran($data)
        ->set('nominal', 5000)
        ->call('submit')
        ->assertHasErrors(['nominal']);

    $this->assertDatabaseCount('transaksi_setoran', 0);
});

it('menolak setoran real_time yang tidak bertanggal hari ini', function () {
    $data = seedSetoranIdempotensi();

    $this->actingAs($data['kolektor']);

    isiFormSetoran($data, ['tanggal' => now()->subDay()->toDateString(), 'sumber' => 'real_time'])
        ->call('submit')
        ->assertHasErrors(['tanggal_transaksi']);

    $this->assertDatabaseCount('transaksi_setoran', 0);
});

it('menolak setoran bertanggal masa depan', function () {
    $data = seedSetoranIdempotensi();

    $this->actingAs($data['kolektor']);

    isiFormSetoran($data, ['tanggal' => now()->addDay()->toDateString(), 'sumber' => 'susulan'])
        ->call('submit')
        ->assertHasErrors(['tanggal_transaksi']);

    $this->assertDatabaseCount('transaksi_setoran', 0);
});

it('mengizinkan setoran susulan tanpa batas mundur', function () {
    $data = seedSetoranIdempotensi();

    $this->actingAs($data['kolektor']);

    isiFormSetoran($data, ['tanggal' => now()->subMonths(6)->toDateString(), 'sumber' => 'susulan'])
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseCount('transaksi_setoran', 1);
});

it('menyimpan catatan setoran', function () {
    $data = seedSetoranIdempotensi();

    $this->actingAs($data['kolektor']);

    isiFormSetoran($data, ['catatan' => 'Setoran dari pos ronda'])
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('transaksi_setoran', [
        'nasabah_id' => $data['nasabah']->id,
        'catatan' => 'Setoran dari pos ronda',
    ]);
});
