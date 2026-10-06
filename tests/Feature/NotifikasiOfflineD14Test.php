<?php

use App\Actions\Setoran\CatatSetoranAction;
use App\Helpers\ActivityLogger;
use App\Jobs\KirimNotifikasiWhatsApp;
use App\Models\LogNotifikasi;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

function konfigurasiWaP23(): void
{
    config([
        'services.whatsapp.url' => 'https://wa.example.test/send',
        'services.whatsapp.token' => 'token-tes-p23',
    ]);
}

function nasabahOfflineP23(): User
{
    return User::factory()->nasabah()->create([
        'no_hp' => null,
        'mode_akses' => 'offline',
    ]);
}

it('melewatkan notifikasi nasabah offline tanpa baris log dan tanpa antrean whatsapp', function () {
    Queue::fake();
    konfigurasiWaP23();

    $offline = nasabahOfflineP23();

    ActivityLogger::notify($offline->id, 'Judul Offline', 'Isi pesan untuk nasabah offline.', 'both');

    Queue::assertNothingPushed();
    expect(LogNotifikasi::count())->toBe(0);
});

it('notifikasi tetap terkirim untuk nasabah digital', function () {
    Queue::fake();
    konfigurasiWaP23();

    $digital = User::factory()->nasabah()->create(['no_hp' => '081234567890']);

    ActivityLogger::notify($digital->id, 'Judul Digital', 'Isi pesan untuk nasabah digital.', 'both');

    Queue::assertPushed(KirimNotifikasiWhatsApp::class);
    expect(LogNotifikasi::where('nasabah_id', $digital->id)->count())->toBe(2);
});

it('setoran sukses tidak memberi notifikasi ke nasabah offline', function () {
    Queue::fake();
    konfigurasiWaP23();

    $offline = nasabahOfflineP23();
    $kolektor = User::factory()->kolektor()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Bebas Notifikasi',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'status' => 'aktif',
    ]);

    app(CatatSetoranAction::class)->execute([
        'nasabah_id' => $offline->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'sumber_input' => 'real_time',
        'input_by' => $kolektor->id,
        'catatan' => null,
        'sudah_disetor_ke_kantor' => false,
        'idempotency_key' => 'p23-offline-'.uniqid(),
    ]);

    Queue::assertNothingPushed();
    expect(LogNotifikasi::count())->toBe(0)
        ->and(TransaksiSetoran::count())->toBe(1);
});

it('setoran sukses tetap memberi notifikasi ke nasabah digital', function () {
    Queue::fake();
    konfigurasiWaP23();

    $digital = User::factory()->nasabah()->create(['no_hp' => '081234567891']);
    $kolektor = User::factory()->kolektor()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Bebas Notifikasi Digital',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'status' => 'aktif',
    ]);

    app(CatatSetoranAction::class)->execute([
        'nasabah_id' => $digital->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'sumber_input' => 'real_time',
        'input_by' => $kolektor->id,
        'catatan' => null,
        'sudah_disetor_ke_kantor' => false,
        'idempotency_key' => 'p23-digital-'.uniqid(),
    ]);

    Queue::assertPushed(KirimNotifikasiWhatsApp::class);
    expect(LogNotifikasi::where('nasabah_id', $digital->id)->count())->toBe(2)
        ->and(TransaksiSetoran::count())->toBe(1);
});
