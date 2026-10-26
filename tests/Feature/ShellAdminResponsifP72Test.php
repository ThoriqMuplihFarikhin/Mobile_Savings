<?php

use App\Models\Komplain;
use App\Models\User;

/**
 * Satu komplain baru supaya lencana tab Persetujuan menampilkan 1.
 */
function p72KomplainBaru(User $nasabah): Komplain
{
    return Komplain::create([
        'nasabah_id' => $nasabah->id,
        'kategori' => 'saldo',
        'deskripsi' => 'Saldo tidak sesuai',
        'status' => 'baru',
        'tanggal_dibuat' => now(),
    ]);
}

it('menampilkan bottom-nav admin dengan lima tab di shell admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-test="bottom-nav-admin"', false)
        ->assertSee('data-test="tab-beranda"', false)
        ->assertSee('data-test="tab-persetujuan"', false)
        ->assertSee('data-test="tab-keuangan"', false)
        ->assertSee('data-test="tab-nasabah"', false)
        ->assertSee('data-test="tab-menu"', false)
        ->assertSee(route('admin.persetujuan.index'), false)
        ->assertSee(route('admin.rekonsiliasi.index'), false)
        ->assertSee(route('admin.nasabah.index'), false);
});

it('menampilkan lencana jumlah antrean pada tab persetujuan', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();
    p72KomplainBaru($nasabah);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-test="bottom-nav-badge">1</span>', false);
});

it('menyembunyikan lencana antrean bila semua antrean kosong', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('data-test="bottom-nav-badge"', false);
});

it('membuka sheet menu berisi halaman admin lainnya', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-test="sheet-menu"', false)
        ->assertSee(route('admin.laporan.index'), false)
        ->assertSee(route('admin.komisi.index'), false)
        ->assertSee(route('admin.pengaturan.index'), false)
        ->assertSee(route('admin.monitoring-absensi.index'), false);
});

it('memakai viewport-fit cover dan safe-area inset pada shell admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('viewport-fit=cover', false)
        ->assertSee('safe-area-inset-bottom', false);
});

it('menampilkan bottom-nav admin pada shell flux yang dipakai admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.log.index'))
        ->assertOk()
        ->assertSee('data-test="bottom-nav-admin"', false);
});

it('tidak menampilkan bottom-nav admin untuk role selain admin', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('data-test="bottom-nav-admin"', false);
});
