<?php

use App\Livewire\Admin\MonitoringSetoran;
use App\Models\KepesertaanPaket;
use App\Models\LogAktivitas;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

function seedKoreksiBatal(array $opsi = []): array
{
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Koreksi',
        'alamat' => 'Jl. Koreksi No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create(array_merge([
        'nama' => 'Tabungan Koreksi',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'minimal_setor' => 10000,
        'harga_per_hari' => null,
        'status' => 'aktif',
    ], $opsi['produk'] ?? []));

    $nominal = $opsi['nominal'] ?? 50000;

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => $nominal,
    ]);

    $setoran = TransaksiSetoran::create(array_merge([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => $nominal,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ], $opsi['setoran'] ?? []));

    return compact('admin', 'kolektor', 'nasabah', 'produk', 'setoran');
}

function saldoKoreksiBatal(User $nasabah, ProdukTabungan $produk): float
{
    return (float) SaldoProduk::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->value('saldo');
}

it('koreksi dua kali mempertahankan nominal_asli dan mencatat nominal_lama_baru di setiap log', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk, 'setoran' => $setoran] = seedKoreksiBatal();

    $this->actingAs($admin);

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $setoran->id)
        ->set('nominalBaru', 60000)
        ->set('alasanKoreksi', 'Salah ketik')
        ->call('koreksi')
        ->assertHasNoErrors();

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $setoran->id)
        ->set('nominalBaru', 75000)
        ->set('alasanKoreksi', 'Koreksi ulang')
        ->call('koreksi')
        ->assertHasNoErrors();

    $setoran->refresh();

    expect($setoran->status)->toBe('dikoreksi')
        ->and((float) $setoran->nominal)->toBe(75000.0)
        ->and((float) $setoran->nominal_asli)->toBe(50000.0)
        ->and(saldoKoreksiBatal($nasabah, $produk))->toBe(75000.0);

    $logs = LogAktivitas::where('aksi', 'koreksi_setoran')
        ->where('entitas_id', $setoran->id)
        ->orderBy('id')
        ->get();

    expect($logs)->toHaveCount(2)
        ->and((float) $logs[0]->detail['nominal_lama'])->toBe(50000.0)
        ->and((float) $logs[0]->detail['nominal_baru'])->toBe(60000.0)
        ->and((float) $logs[1]->detail['nominal_lama'])->toBe(60000.0)
        ->and((float) $logs[1]->detail['nominal_baru'])->toBe(75000.0);
});

it('batal setelah koreksi tetap dibolehkan dan saldo menyusut sesuai nominal akhir', function () {
    ['admin' => $admin, 'nasabah' => $nasabah, 'produk' => $produk, 'setoran' => $setoran] = seedKoreksiBatal();

    $this->actingAs($admin);

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $setoran->id)
        ->set('nominalBaru', 40000)
        ->set('alasanKoreksi', 'Penyesuaian')
        ->call('koreksi')
        ->assertHasNoErrors();

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleBatal', $setoran->id)
        ->set('alasanBatal', 'Duplikat input')
        ->call('batal')
        ->assertHasNoErrors();

    expect($setoran->refresh()->status)->toBe('dibatalkan')
        ->and(saldoKoreksiBatal($nasabah, $produk))->toBe(0.0);
});

it('koreksi dan batal setoran paket memperbarui tunggakan tanpa membuat kepesertaan baru', function () {
    ['admin' => $admin, 'nasabah' => $nasabah, 'produk' => $produk, 'setoran' => $setoran] = seedKoreksiBatal([
        'produk' => ['tipe' => 'paket', 'harga_per_hari' => 10000],
        'nominal' => 30000,
    ]);

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(2)->toDateString(),
        'total_seharusnya_terkumpul' => 0,
        'total_aktual_terkumpul' => 0,
        'tunggakan' => 0,
        'status_alert' => 'normal',
    ]);
    TransaksiSetoran::whereKey($setoran->id)->update(['kepesertaan_id' => $kepesertaan->id]);

    $this->actingAs($admin);

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $setoran->id)
        ->set('nominalBaru', 10000)
        ->set('alasanKoreksi', 'Salah nominal')
        ->call('koreksi')
        ->assertHasNoErrors();

    $kepesertaan->refresh();
    expect((float) $kepesertaan->total_aktual_terkumpul)->toBe(10000.0)
        ->and((int) $kepesertaan->tunggakan)->toBe(2)
        ->and(KepesertaanPaket::where('nasabah_id', $nasabah->id)->count())->toBe(1);

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleBatal', $setoran->id)
        ->set('alasanBatal', 'Input salah semua')
        ->call('batal')
        ->assertHasNoErrors();

    $kepesertaan->refresh();
    expect((float) $kepesertaan->total_aktual_terkumpul)->toBe(0.0)
        ->and((int) $kepesertaan->tunggakan)->toBe(3)
        ->and(KepesertaanPaket::where('nasabah_id', $nasabah->id)->count())->toBe(1);
});

it('setoran yang sudah disetor ke kantor memberi peringatan modal dan penanda di log', function () {
    ['admin' => $admin, 'setoran' => $setoran] = seedKoreksiBatal([
        'setoran' => ['sudah_disetor_ke_kantor' => true],
    ]);

    $this->actingAs($admin);

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $setoran->id)
        ->assertSee('Setoran ini sudah disetor ke kantor');

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleBatal', $setoran->id)
        ->assertSee('Setoran ini sudah disetor ke kantor');

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $setoran->id)
        ->set('nominalBaru', 60000)
        ->set('alasanKoreksi', 'Penyesuaian audit')
        ->call('koreksi')
        ->assertHasNoErrors();

    $logKoreksi = LogAktivitas::where('aksi', 'koreksi_setoran')
        ->where('entitas_id', $setoran->id)
        ->latest('id')
        ->firstOrFail();

    expect($logKoreksi->detail['sudah_disetor'])->toBeTrue();

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleBatal', $setoran->id)
        ->set('alasanBatal', 'Batal untuk audit')
        ->call('batal')
        ->assertHasNoErrors();

    $logBatal = LogAktivitas::where('aksi', 'batal_setoran')
        ->where('entitas_id', $setoran->id)
        ->latest('id')
        ->firstOrFail();

    expect($logBatal->detail['sudah_disetor'])->toBeTrue()
        ->and($setoran->refresh()->status)->toBe('dibatalkan');
});
