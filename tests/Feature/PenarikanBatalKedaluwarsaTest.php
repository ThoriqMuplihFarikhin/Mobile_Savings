<?php

use App\Livewire\Admin\ApprovalPenarikan;
use App\Livewire\Nasabah\RiwayatPenarikan;
use App\Models\AdminSetting;
use App\Models\LogAktivitas;
use App\Models\LogNotifikasi;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

/**
 * @return array{nasabah: User, produk: ProdukTabungan, penarikan: TransaksiPenarikan}
 */
function p64Fixture(array $opsi = []): array
{
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => 'Bebas P64',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'status' => 'aktif',
    ]);

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 100000,
        'persen_komisi_terpakai' => 0,
        'nominal_komisi' => 0,
        'nominal_diterima' => 100000,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => $opsi['status'] ?? 'pending',
    ]);

    if (isset($opsi['umur_hari'])) {
        DB::table('transaksi_penarikan')
            ->where('id', $penarikan->id)
            ->update(['created_at' => now()->subDays($opsi['umur_hari'])]);
    }

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => $opsi['saldo'] ?? 500000,
    ]);

    return [
        'nasabah' => $nasabah,
        'produk' => $produk,
        'penarikan' => $penarikan->fresh(),
    ];
}

it('nasabah membatalkan pengajuan penarikan pending milik sendiri', function () {
    ['nasabah' => $nasabah, 'penarikan' => $penarikan] = p64Fixture();

    Livewire::actingAs($nasabah)
        ->test(RiwayatPenarikan::class)
        ->call('batalkan', $penarikan->id)
        ->assertSee('berhasil dibatalkan');

    expect($penarikan->fresh()->status)->toBe('dibatalkan')
        ->and(LogAktivitas::where('aksi', 'batalkan_penarikan')
            ->where('entitas_terkait', 'transaksi_penarikan')
            ->where('entitas_id', $penarikan->id)
            ->count())->toBe(1)
        ->and((float) SaldoProduk::where('nasabah_id', $nasabah->id)->value('saldo'))->toBe(500000.0);
});

it('menolak pembatalan penarikan milik nasabah lain', function () {
    $pemilik = p64Fixture();
    $penarikanLain = p64Fixture();

    Livewire::actingAs($pemilik['nasabah'])
        ->test(RiwayatPenarikan::class)
        ->call('batalkan', $penarikanLain['penarikan']->id)
        ->assertSee('tidak dapat dibatalkan');

    expect($penarikanLain['penarikan']->fresh()->status)->toBe('pending')
        ->and(LogAktivitas::where('aksi', 'batalkan_penarikan')->count())->toBe(0);
});

it('menolak pembatalan penarikan yang bukan berstatus pending', function () {
    ['nasabah' => $nasabah, 'penarikan' => $penarikan] = p64Fixture(['status' => 'approved']);

    Livewire::actingAs($nasabah)
        ->test(RiwayatPenarikan::class)
        ->call('batalkan', $penarikan->id)
        ->assertSee('tidak dapat dibatalkan');

    expect($penarikan->fresh()->status)->toBe('approved');
});

it('pembatalan dua kali hanya mengubah status dan log sekali', function () {
    ['nasabah' => $nasabah, 'penarikan' => $penarikan] = p64Fixture();

    $tes = Livewire::actingAs($nasabah)->test(RiwayatPenarikan::class);

    $tes->call('batalkan', $penarikan->id)->assertSee('berhasil dibatalkan');
    $tes->call('batalkan', $penarikan->id)->assertSee('tidak dapat dibatalkan');

    expect($penarikan->fresh()->status)->toBe('dibatalkan')
        ->and(LogAktivitas::where('aksi', 'batalkan_penarikan')->count())->toBe(1);
});

it('tombol batalkan hanya tampil untuk pengajuan pending dan status baru punya label', function () {
    ['nasabah' => $nasabah, 'penarikan' => $penarikan] = p64Fixture();

    Livewire::actingAs($nasabah)
        ->test(RiwayatPenarikan::class)
        ->assertSee('Batalkan');

    $penarikan->update(['status' => 'dibatalkan']);

    Livewire::actingAs($nasabah)
        ->test(RiwayatPenarikan::class)
        ->assertSee('Dibatalkan')
        ->assertDontSee('Batalkan');
});

it('penarikan:kedaluwarsakan menandai pending tua menjadi kedaluwarsa beserta notifikasi', function () {
    Queue::fake();

    $tua = p64Fixture(['umur_hari' => 8]);
    $baru = p64Fixture(['umur_hari' => 1]);
    $approvedTua = p64Fixture(['umur_hari' => 9, 'status' => 'approved']);

    $this->artisan('penarikan:kedaluwarsakan')->assertSuccessful();

    expect($tua['penarikan']->fresh()->status)->toBe('kedaluwarsa')
        ->and($baru['penarikan']->fresh()->status)->toBe('pending')
        ->and($approvedTua['penarikan']->fresh()->status)->toBe('approved')
        ->and(LogNotifikasi::where('nasabah_id', $tua['nasabah']->id)
            ->where('channel', 'in_app')->count())->toBe(1)
        ->and(LogNotifikasi::where('nasabah_id', $baru['nasabah']->id)->count())->toBe(0);
});

it('membaca batas kedaluwarsa dari AdminSetting penarikan_kedaluwarsa_hari', function () {
    Queue::fake();

    AdminSetting::set('penarikan_kedaluwarsa_hari', '2');

    $tigaHari = p64Fixture(['umur_hari' => 3]);
    $sehari = p64Fixture(['umur_hari' => 1]);

    $this->artisan('penarikan:kedaluwarsakan')->assertSuccessful();

    expect($tigaHari['penarikan']->fresh()->status)->toBe('kedaluwarsa')
        ->and($sehari['penarikan']->fresh()->status)->toBe('pending');
});

it('scheduler mendaftarkan penarikan:kedaluwarsakan setiap pukul 00.40', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('penarikan:kedaluwarsakan')
        ->assertExitCode(0);

    $event = collect(app(Schedule::class)->events())
        ->first(fn ($e) => str_contains((string) $e->command, 'penarikan:kedaluwarsakan'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('40 0 * * *');
});

it('daftar filter persetujuan admin memuat status dibatalkan dan kedaluwarsa', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(ApprovalPenarikan::class)
        ->assertSee('Dibatalkan')
        ->assertSee('Kedaluwarsa');
});
