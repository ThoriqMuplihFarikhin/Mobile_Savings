<?php

use App\Actions\Penarikan\VerifikasiPenarikanOfflineAction;
use App\Livewire\Admin\ApprovalPenarikan;
use App\Livewire\Admin\HandoverKolektor;
use App\Livewire\Admin\KasKolektor;
use App\Livewire\Admin\Laporan;
use App\Livewire\Admin\RekonsiliasiKas;
use App\Livewire\Kolektor\SetorKantor;
use App\Models\AdminSetting;
use App\Models\LogAktivitas;
use App\Models\ProdukTabungan;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use App\Support\KasKolektorHitung;
use Livewire\Livewire;

function penarikanTunaiRumah(User $nasabah, ProdukTabungan $produk): TransaksiPenarikan
{
    return TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'approved',
    ]);
}

function selesaikanPenarikanTunai(User $kolektor, User $nasabah, ProdukTabungan $produk): TransaksiPenarikan
{
    $penarikan = penarikanTunaiRumah($nasabah, $produk);

    (new VerifikasiPenarikanOfflineAction)->execute($penarikan, '123456', $kolektor);

    return $penarikan->fresh();
}

function barisKasD13(iterable $rows, User $kolektor): ?array
{
    foreach ($rows as $row) {
        if ($row['kolektor_id'] === $kolektor->id) {
            return $row;
        }
    }

    return null;
}

it('kas berkurang tepat nominal diterima, komisi tetap di kas, dan log tercatat (D13)', function () {
    ['kolektor' => $k, 'nasabah' => $n, 'produk' => $p] = kolektorDenganKas(100000);

    $penarikan = selesaikanPenarikanTunai($k, $n, $p);

    expect($penarikan->status)->toBe('selesai')
        ->and($penarikan->dibayar_oleh)->toBe($k->id)
        ->and($penarikan->mempengaruhi_kas)->toBeTrue();

    // 100000 − 47500 (nominal_diterima) = 52500; komisi 2500 tetap ada di kas.
    expect(KasKolektorHitung::kasDiTangan($k->id))->toBe('52500.00')
        ->and(KasKolektorHitung::tunaiKeluarBelumDirekonsiliasi($k->id))->toBe('47500.00');

    expect(LogAktivitas::where('aksi', 'kas_berkurang_penarikan_tunai')
        ->where('entitas_id', $penarikan->id)->exists())->toBeTrue();
});

it('penarikan pengambilan di kantor tidak mengubah kas kolektor (D13)', function () {
    ['kolektor' => $k, 'nasabah' => $n, 'produk' => $p] = kolektorDenganKas(100000);

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $n->id,
        'produk_id' => $p->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'approved',
    ]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);
    Livewire::test(ApprovalPenarikan::class)
        ->set('alasan', 'Pencairan tunai diselesaikan di kantor')
        ->call('selesai', $penarikan->id)
        ->assertHasNoErrors();

    $penarikan->refresh();
    expect($penarikan->status)->toBe('selesai')
        ->and($penarikan->mempengaruhi_kas)->toBeFalse()
        ->and($penarikan->dibayar_oleh)->toBeNull()
        ->and(KasKolektorHitung::kasDiTangan($k->id))->toBe('100000.00');
});

it('kas tidak cukup menolak verifikasi penarikan tunai (D13)', function () {
    ['kolektor' => $k, 'nasabah' => $n, 'produk' => $p] = kolektorDenganKas(40000);

    $penarikan = penarikanTunaiRumah($n, $p);

    $pesan = null;
    try {
        (new VerifikasiPenarikanOfflineAction)->execute($penarikan, '123456', $k);
    } catch (Exception $e) {
        $pesan = $e->getMessage();
    }

    expect($pesan)->toContain(
        'Kas di tangan Anda (Rp 40.000) tidak cukup untuk membayar Rp 47.500. Pilih pengambilan di kantor atau setor/hubungi admin.'
    );

    $penarikan->refresh();
    expect($penarikan->status)->toBe('approved')
        ->and($penarikan->mempengaruhi_kas)->toBeFalse()
        ->and($penarikan->dibayar_oleh)->toBeNull()
        ->and(KasKolektorHitung::kasDiTangan($k->id))->toBe('40000.00')
        ->and(LogAktivitas::where('aksi', 'kas_berkurang_penarikan_tunai')
            ->where('entitas_id', $penarikan->id)->exists())->toBeFalse();
});

it('izinkan_kas_minus mengizinkan verifikasi dengan kas minus (D13)', function () {
    ['kolektor' => $k, 'nasabah' => $n, 'produk' => $p] = kolektorDenganKas(40000);
    $penarikan = penarikanTunaiRumah($n, $p);

    AdminSetting::set('izinkan_kas_minus', 'true');

    $hasil = (new VerifikasiPenarikanOfflineAction)->execute($penarikan, '123456', $k);

    expect($hasil->status)->toBe('selesai')
        ->and($hasil->mempengaruhi_kas)->toBeTrue()
        ->and(KasKolektorHitung::kasDiTangan($k->id))->toBe('-7500.00')
        ->and(LogAktivitas::where('aksi', 'kas_berkurang_penarikan_tunai')
            ->where('entitas_id', $penarikan->id)->exists())->toBeTrue();
});

it('penyelesaian manual admin penarikan rumah ikut memotong kas (D13)', function () {
    ['kolektor' => $k, 'nasabah' => $n, 'produk' => $p] = kolektorDenganKas(100000);
    $n->update(['harus_ganti_pin' => true]);
    $penarikan = penarikanTunaiRumah($n, $p);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);
    Livewire::test(ApprovalPenarikan::class)
        ->set('alasan', 'Nasabah belum ganti PIN, pencairan manual di rumah')
        ->call('selesai', $penarikan->id)
        ->assertHasNoErrors();

    $penarikan->refresh();
    expect($penarikan->status)->toBe('selesai')
        ->and($penarikan->dibayar_oleh)->toBe($k->id)
        ->and($penarikan->mempengaruhi_kas)->toBeTrue()
        ->and(KasKolektorHitung::kasDiTangan($k->id))->toBe('52500.00');
});

it('penyelesaian manual admin rumah ditolak bila kas tidak cukup (D13)', function () {
    ['kolektor' => $k, 'nasabah' => $n, 'produk' => $p] = kolektorDenganKas(40000);
    $n->update(['harus_ganti_pin' => true]);
    $penarikan = penarikanTunaiRumah($n, $p);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);
    Livewire::test(ApprovalPenarikan::class)
        ->set('alasan', 'Nasabah belum ganti PIN, pencairan manual di rumah')
        ->call('selesai', $penarikan->id);

    $penarikan->refresh();
    expect($penarikan->status)->toBe('approved')
        ->and($penarikan->mempengaruhi_kas)->toBeFalse()
        ->and($penarikan->dibayar_oleh)->toBeNull()
        ->and(KasKolektorHitung::kasDiTangan($k->id))->toBe('40000.00');
});

it('total seharusnya setor kantor dikurangi penarikan tunai tertaut (D13)', function () {
    ['kolektor' => $k, 'nasabah' => $n, 'produk' => $p] = kolektorDenganKas(100000);
    $penarikan = selesaikanPenarikanTunai($k, $n, $p);

    $this->actingAs($k);
    Livewire::test(SetorKantor::class)->call('submit')->assertHasNoErrors();

    $pengajuan = SetoranKolektorKantor::where('kolektor_id', $k->id)->first();

    expect($pengajuan)->not->toBeNull()
        ->and($pengajuan->status)->toBe('pending')
        ->and((float) $pengajuan->total_seharusnya)->toBe(52500.0)
        ->and($penarikan->fresh()->setoran_kolektor_id)->toBe($pengajuan->id)
        ->and(TransaksiSetoran::where('setoran_kolektor_id', $pengajuan->id)->count())->toBe(1);
});

it('kas tetap konsisten selama pengajuan setor menunggu proses admin (D13)', function () {
    ['kolektor' => $k, 'nasabah' => $n, 'produk' => $p] = kolektorDenganKas(100000);
    selesaikanPenarikanTunai($k, $n, $p);

    $this->actingAs($k);
    Livewire::test(SetorKantor::class)->call('submit');

    // Kas di tangan selama pengajuan pending tetap net, sama dengan total_seharusnya.
    expect(KasKolektorHitung::kasDiTangan($k->id))->toBe('52500.00');

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    $baris = barisKasD13(Livewire::test(KasKolektor::class)->viewData('daftarKas'), $k);

    expect($baris)->not->toBeNull()
        ->and($baris['total_belum_disetor'])->toBe(100000.0)
        ->and($baris['penarikan_tunai'])->toBe(47500.0)
        ->and($baris['kas_di_tangan'])->toBe(52500.0);
});

it('penarikan baru saat pengajuan pending ikut tertaut dan total diperbarui (D13)', function () {
    ['kolektor' => $k, 'nasabah' => $n, 'produk' => $p] = kolektorDenganKas(100000);

    $this->actingAs($k);
    Livewire::test(SetorKantor::class)->call('submit');

    $pengajuan = SetoranKolektorKantor::where('kolektor_id', $k->id)->first();
    expect((float) $pengajuan->total_seharusnya)->toBe(100000.0);

    $penarikan = selesaikanPenarikanTunai($k, $n, $p);

    expect($penarikan->setoran_kolektor_id)->toBe($pengajuan->id)
        ->and((float) $pengajuan->fresh()->total_seharusnya)->toBe(52500.0)
        ->and(KasKolektorHitung::kasDiTangan($k->id))->toBe('52500.00');
});

it('membatalkan pengajuan melepas tautan penarikan tunai (D13)', function () {
    ['kolektor' => $k, 'nasabah' => $n, 'produk' => $p] = kolektorDenganKas(100000);
    $penarikan = selesaikanPenarikanTunai($k, $n, $p);

    $this->actingAs($k);
    Livewire::test(SetorKantor::class)->call('submit');

    $pengajuan = SetoranKolektorKantor::where('kolektor_id', $k->id)->first();

    Livewire::test(SetorKantor::class)->call('batalkan', $pengajuan->id);

    expect($pengajuan->fresh()->status)->toBe('dibatalkan')
        ->and($penarikan->fresh()->setoran_kolektor_id)->toBeNull()
        ->and(TransaksiSetoran::where('setoran_kolektor_id', $pengajuan->id)->count())->toBe(0)
        ->and(KasKolektorHitung::kasDiTangan($k->id))->toBe('52500.00');
});

it('penolakan pengajuan oleh admin melepas tautan penarikan tunai (D13)', function () {
    ['kolektor' => $k, 'nasabah' => $n, 'produk' => $p] = kolektorDenganKas(100000);
    $penarikan = selesaikanPenarikanTunai($k, $n, $p);

    $this->actingAs($k);
    Livewire::test(SetorKantor::class)->call('submit');

    $pengajuan = SetoranKolektorKantor::where('kolektor_id', $k->id)->first();

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    Livewire::test(RekonsiliasiKas::class)
        ->call('startReject', $pengajuan->id)
        ->set('rejectAlasan', 'Kas kurang saat penyerahan ke kantor')
        ->call('rejectSubmission')
        ->assertHasNoErrors();

    expect($pengajuan->fresh()->status)->toBe('dibatalkan')
        ->and($penarikan->fresh()->setoran_kolektor_id)->toBeNull()
        ->and(KasKolektorHitung::kasDiTangan($k->id))->toBe('52500.00');
});

it('proses admin rekonsiliasi memakai total seharusnya net (D13)', function () {
    ['kolektor' => $k, 'nasabah' => $n, 'produk' => $p] = kolektorDenganKas(100000);
    selesaikanPenarikanTunai($k, $n, $p);

    $this->actingAs($k);
    Livewire::test(SetorKantor::class)->call('submit');

    $pengajuan = SetoranKolektorKantor::where('kolektor_id', $k->id)->first();

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    Livewire::test(RekonsiliasiKas::class)
        ->call('startProcess', $pengajuan->id)
        ->set('processTotalDiterima', '52500')
        ->call('processSubmission', $pengajuan->id)
        ->assertHasNoErrors();

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('cocok')
        ->and((float) $pengajuan->total_seharusnya)->toBe(52500.0)
        ->and((float) $pengajuan->selisih)->toBe(0.0)
        ->and(KasKolektorHitung::kasDiTangan($k->id))->toBe('0.00');
});

it('backfill historis tidak menggerakkan kas dan idempoten (D13)', function () {
    ['kolektor' => $k, 'nasabah' => $n, 'produk' => $p] = kolektorDenganKas(100000);

    $historis = TransaksiPenarikan::create([
        'nasabah_id' => $n->id,
        'produk_id' => $p->id,
        'nominal_diminta' => 30000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 1500,
        'nominal_diterima' => 28500,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'selesai',
        'waktu_pencairan' => now()->subDay(),
        'diverifikasi_oleh' => $k->id,
    ]);
    $kantorHistoris = TransaksiPenarikan::create([
        'nasabah_id' => $n->id,
        'produk_id' => $p->id,
        'nominal_diminta' => 20000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 1000,
        'nominal_diterima' => 19000,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'selesai',
        'waktu_pencairan' => now()->subDay(),
    ]);

    expect(TransaksiPenarikan::backfillHistorisKas())->toBe(1);

    $historis->refresh();
    expect($historis->dibayar_oleh)->toBe($k->id)
        ->and($historis->mempengaruhi_kas)->toBeFalse()
        ->and($kantorHistoris->fresh()->dibayar_oleh)->toBeNull()
        ->and(KasKolektorHitung::kasDiTangan($k->id))->toBe('100000.00');

    expect(TransaksiPenarikan::backfillHistorisKas())->toBe(0);
});

it('satu fixture menghasilkan angka kas konsisten di semua halaman (D13)', function () {
    $a = kolektorDenganKas(100000);
    $b = kolektorDenganKas(50000);
    $a['kolektor']->update(['name' => 'Kolektor Kas A']);
    $b['kolektor']->update(['name' => 'Kolektor Kas B']);

    selesaikanPenarikanTunai($a['kolektor'], $a['nasabah'], $a['produk']);

    $admin = User::factory()->admin()->create();

    // 1. Sumber tunggal.
    expect(KasKolektorHitung::kasDiTangan($a['kolektor']->id))->toBe('52500.00')
        ->and(KasKolektorHitung::kasDiTangan($b['kolektor']->id))->toBe('50000.00');

    // 2. Halaman admin Kas Kolektor.
    $this->actingAs($admin);
    $daftarKas = collect(Livewire::test(KasKolektor::class)->viewData('daftarKas'));
    $barisA = barisKasD13($daftarKas, $a['kolektor']);
    $barisB = barisKasD13($daftarKas, $b['kolektor']);
    expect($barisA['total_belum_disetor'])->toBe(100000.0)
        ->and($barisA['penarikan_tunai'])->toBe(47500.0)
        ->and($barisA['kas_di_tangan'])->toBe(52500.0)
        ->and($barisB['total_belum_disetor'])->toBe(50000.0)
        ->and($barisB['penarikan_tunai'])->toBe(0.0)
        ->and($barisB['kas_di_tangan'])->toBe(50000.0);

    // 3. Kartu dashboard admin.
    expect(KasKolektor::ringkasUntukDashboard())
        ->toBe(['total_kas' => 102500.0, 'lewat_batas' => 0]);

    $response = $this->actingAs($admin)->get('/dashboard');
    $response->assertOk();
    expect((float) $response->viewData('totalKasKolektor'))->toBe(102500.0);

    // 4. Dashboard kolektor memakai angka yang sama.
    $responseKolektor = $this->actingAs($a['kolektor'])->get('/dashboard');
    $responseKolektor->assertOk();
    expect((float) $responseKolektor->viewData('stats')['setoran_belum_disetor'])->toBe(52500.0);

    // 5. hasUnsettledCash memakai kas net.
    expect($a['kolektor']->hasUnsettledCash())->toBeTrue()
        ->and($b['kolektor']->hasUnsettledCash())->toBeTrue();

    // 6. Handover memakai kas net.
    $this->actingAs($admin);
    $handover = Livewire::test(HandoverKolektor::class)
        ->set('kolektorLamaId', (string) $a['kolektor']->id);
    expect((float) $handover->viewData('unsettledCash'))->toBe(52500.0)
        ->and($handover->viewData('hasUnsettledCash'))->toBeTrue();

    // 7. Pratinjau rekonsiliasi memakai kas net.
    $rekonsiliasi = Livewire::test(RekonsiliasiKas::class)
        ->set('kolektorId', (string) $a['kolektor']->id);
    expect((float) $rekonsiliasi->viewData('totalSeharusnya'))->toBe(52500.0);

    // 8. Halaman Setor Kantor kolektor menampilkan rincian yang sama.
    $this->actingAs($a['kolektor']);
    $setorKantor = Livewire::test(SetorKantor::class);
    expect((float) $setorKantor->viewData('totalSetoranMasuk'))->toBe(100000.0)
        ->and((float) $setorKantor->viewData('totalPenarikanTunai'))->toBe(47500.0)
        ->and((float) $setorKantor->viewData('totalBelumDisetor'))->toBe(52500.0);

    // 9. Laporan per kolektor memakai sumber setoran yang sama.
    $this->actingAs($admin);
    $laporan = Livewire::test(Laporan::class)->call('pilihSeksi', 'kolektor');
    $barisLaporan = collect($laporan->viewData('kolektorRows'));
    expect((float) $barisLaporan->firstWhere('nama', 'Kolektor Kas A')['total_setoran'])->toBe(100000.0)
        ->and((float) $barisLaporan->firstWhere('nama', 'Kolektor Kas B')['total_setoran'])->toBe(50000.0);
});

it('halaman kas kolektor menampilkan kolom penarikan tunai (D13)', function () {
    ['kolektor' => $k, 'nasabah' => $n, 'produk' => $p] = kolektorDenganKas(100000);
    selesaikanPenarikanTunai($k, $n, $p);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);
    Livewire::test(KasKolektor::class)->assertSee('Penarikan Tunai');
});

it('halaman setor kantor dan rekonsiliasi menampilkan rincian setoran masuk dan penarikan (D13)', function () {
    ['kolektor' => $k, 'nasabah' => $n, 'produk' => $p] = kolektorDenganKas(100000);
    selesaikanPenarikanTunai($k, $n, $p);

    $this->actingAs($k);
    Livewire::test(SetorKantor::class)
        ->assertSee('Setoran Masuk')
        ->assertSee('Penarikan Tunai')
        ->assertSee('Total Seharusnya');

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    Livewire::test(RekonsiliasiKas::class)
        ->set('kolektorId', (string) $k->id)
        ->assertSee('Setoran Masuk')
        ->assertSee('Penarikan Tunai')
        ->assertSee('Total Seharusnya');
});
