<?php

use App\Livewire\Admin\ApprovalPenarikan;
use App\Livewire\Admin\Pengaturan;
use App\Models\AdminSetting;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

/**
 * Buat penarikan pending + saldo awal untuk skenario persetujuan ganda (D9).
 */
function buatPenarikanD9(User $admin, User $nasabah, int $nominal, int $saldo): TransaksiPenarikan
{
    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => $nasabah->name,
        'alamat' => 'Jalan Contoh',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas D9',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => $saldo,
    ]);

    return TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => $nominal,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => $nominal * 0.05,
        'nominal_diterima' => $nominal * 0.95,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'pending',
    ]);
}

it('approve pertama penarikan di atas batas tetap pending tanpa memotong saldo', function () {
    Queue::fake();
    $adminA = User::factory()->admin()->create();
    User::factory()->admin()->create();
    AdminSetting::set('penarikan_batas_dua_approver', '100000');
    $nasabah = User::factory()->nasabah()->create();
    $penarikan = buatPenarikanD9($adminA, $nasabah, 500000, 1000000);

    $this->actingAs($adminA);

    Livewire::test(ApprovalPenarikan::class)
        ->call('approve', $penarikan->id)
        ->assertSee('Persetujuan pertama tercatat');

    $penarikan->refresh();

    expect($penarikan->status)->toBe('pending')
        ->and((int) $penarikan->disetujui_oleh)->toBe((int) $adminA->id)
        ->and($penarikan->disetujui_oleh_2)->toBeNull()
        ->and((float) SaldoProduk::where('nasabah_id', $nasabah->id)->value('saldo'))->toBe(1000000.0);
});

it('approve kedua oleh admin berbeda mengesahkan penarikan dan memotong saldo', function () {
    Queue::fake();
    $adminA = User::factory()->admin()->create();
    $adminB = User::factory()->admin()->create();
    AdminSetting::set('penarikan_batas_dua_approver', '100000');
    $nasabah = User::factory()->nasabah()->create();
    $penarikan = buatPenarikanD9($adminA, $nasabah, 500000, 1000000);

    $this->actingAs($adminA);
    Livewire::test(ApprovalPenarikan::class)->call('approve', $penarikan->id);

    $this->actingAs($adminB);
    Livewire::test(ApprovalPenarikan::class)
        ->call('approve', $penarikan->id)
        ->assertSee('Penarikan disetujui!');

    $penarikan->refresh();

    expect($penarikan->status)->toBe('approved')
        ->and((int) $penarikan->disetujui_oleh)->toBe((int) $adminA->id)
        ->and((int) $penarikan->disetujui_oleh_2)->toBe((int) $adminB->id)
        ->and($penarikan->waktu_approval)->not->toBeNull()
        ->and((float) SaldoProduk::where('nasabah_id', $nasabah->id)->value('saldo'))->toBe(500000.0);
});

it('admin yang sama tidak boleh menyetujui dua kali', function () {
    Queue::fake();
    $adminA = User::factory()->admin()->create();
    User::factory()->admin()->create();
    AdminSetting::set('penarikan_batas_dua_approver', '100000');
    $nasabah = User::factory()->nasabah()->create();
    $penarikan = buatPenarikanD9($adminA, $nasabah, 500000, 1000000);

    $this->actingAs($adminA);
    Livewire::test(ApprovalPenarikan::class)->call('approve', $penarikan->id);

    Livewire::test(ApprovalPenarikan::class)
        ->call('approve', $penarikan->id)
        ->assertSee('Persetujuan kedua harus admin lain');

    $penarikan->refresh();

    expect($penarikan->status)->toBe('pending')
        ->and($penarikan->disetujui_oleh_2)->toBeNull()
        ->and((float) SaldoProduk::where('nasabah_id', $nasabah->id)->value('saldo'))->toBe(1000000.0);
});

it('penandai selesai harus berbeda dari kedua penyetuju', function () {
    Queue::fake();
    $adminA = User::factory()->admin()->create();
    $adminB = User::factory()->admin()->create();
    $adminC = User::factory()->admin()->create();
    AdminSetting::set('penarikan_batas_dua_approver', '100000');
    $nasabah = User::factory()->nasabah()->create();
    $penarikan = buatPenarikanD9($adminA, $nasabah, 500000, 1000000);

    $this->actingAs($adminA);
    Livewire::test(ApprovalPenarikan::class)->call('approve', $penarikan->id);
    $this->actingAs($adminB);
    Livewire::test(ApprovalPenarikan::class)->call('approve', $penarikan->id);

    $pesan = 'Penarikan dengan persetujuan ganda harus ditandai selesai oleh admin lain.';

    $this->actingAs($adminA);
    Livewire::test(ApprovalPenarikan::class)
        ->set('alasan', 'Pencairan manual di kantor sesuai permintaan nasabah.')
        ->call('selesai', $penarikan->id)
        ->assertSee($pesan);

    $this->actingAs($adminB);
    Livewire::test(ApprovalPenarikan::class)
        ->set('alasan', 'Pencairan manual di kantor sesuai permintaan nasabah.')
        ->call('selesai', $penarikan->id)
        ->assertSee($pesan);

    expect($penarikan->fresh()->status)->toBe('approved');

    $this->actingAs($adminC);
    Livewire::test(ApprovalPenarikan::class)
        ->set('alasan', 'Pencairan manual di kantor sesuai permintaan nasabah.')
        ->call('selesai', $penarikan->id)
        ->assertSee('Penarikan ditandai selesai!');

    expect($penarikan->fresh()->status)->toBe('selesai');
});

it('fitur persetujuan ganda tidak berlaku saat hanya satu admin', function () {
    Queue::fake();
    $admin = User::factory()->admin()->create();
    AdminSetting::set('penarikan_batas_dua_approver', '100000');
    $nasabah = User::factory()->nasabah()->create();
    $penarikan = buatPenarikanD9($admin, $nasabah, 500000, 1000000);

    $this->actingAs($admin);

    Livewire::test(ApprovalPenarikan::class)
        ->call('approve', $penarikan->id)
        ->assertSee('Penarikan disetujui!');

    $penarikan->refresh();

    expect($penarikan->status)->toBe('approved')
        ->and((int) $penarikan->disetujui_oleh)->toBe((int) $admin->id)
        ->and($penarikan->disetujui_oleh_2)->toBeNull()
        ->and((float) SaldoProduk::where('nasabah_id', $nasabah->id)->value('saldo'))->toBe(500000.0);
});

it('fitur tetap single approval saat batas nol', function () {
    Queue::fake();
    $adminA = User::factory()->admin()->create();
    User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();
    $penarikan = buatPenarikanD9($adminA, $nasabah, 500000, 1000000);

    $this->actingAs($adminA);

    Livewire::test(ApprovalPenarikan::class)
        ->call('approve', $penarikan->id)
        ->assertSee('Penarikan disetujui!');

    $penarikan->refresh();

    expect($penarikan->status)->toBe('approved')
        ->and($penarikan->disetujui_oleh_2)->toBeNull()
        ->and((float) SaldoProduk::where('nasabah_id', $nasabah->id)->value('saldo'))->toBe(500000.0);
});

it('pengaturan memuat batas dua approver dan memperingatkan bila admin kurang dari dua', function () {
    $admin = User::factory()->admin()->create();
    AdminSetting::set('penarikan_batas_dua_approver', '250000');

    $this->actingAs($admin);

    Livewire::test(Pengaturan::class)
        ->assertSet('penarikanBatasDuaApprover', '250000')
        ->assertSee('Batas Persetujuan Ganda Penarikan')
        ->assertSee('Fitur persetujuan ganda tidak berlaku');

    User::factory()->admin()->create();

    Livewire::test(Pengaturan::class)
        ->assertDontSee('Fitur persetujuan ganda tidak berlaku');
});
