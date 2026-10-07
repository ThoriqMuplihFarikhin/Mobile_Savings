<?php

use App\Livewire\Admin\RekonsiliasiKas;
use App\Models\SetoranKolektorKantor;
use App\Models\User;
use Livewire\Livewire;

it('melipat baris filter dan aksi komisi di layar kecil', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.komisi.index'))
        ->assertOk()
        ->assertSee('data-test="baris-tanggal-filter" class="flex flex-wrap', false)
        ->assertSee('data-test="baris-aksi-filter" class="flex flex-wrap', false)
        ->assertSee('hidden md:table', false)
        ->assertSee('data-test="kartu-komisi-bulan"', false)
        ->assertSee('data-test="kartu-riwayat-komisi"', false);
});

it('menukarkan tabel kas kolektor menjadi kartu di layar kecil', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.kas-kolektor.index'))
        ->assertOk()
        ->assertSee('hidden md:table', false)
        ->assertSee('data-test="kartu-kas"', false)
        ->assertSee('data-test="kartu-kas" class="divide-y divide-[#ebebeb] md:hidden"', false);
});

it('melipat baris pengajuan pending rekonsiliasi dan menukarkan riwayatnya jadi kartu', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_setor' => now()->toDateString(),
        'total_seharusnya' => 100000,
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.rekonsiliasi.index'))
        ->assertOk()
        ->assertSee('data-test="baris-pengajuan" class="flex flex-wrap', false)
        ->assertSee('hidden md:table', false)
        ->assertSee('data-test="kartu-riwayat"', false);
});

it('menyediakan input nominal diterima, selisih, dan keterangan saat memproses pengajuan', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $setoran = SetoranKolektorKantor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_setor' => now()->toDateString(),
        'total_seharusnya' => 100000,
        'status' => 'pending',
    ]);

    $this->actingAs($admin);

    Livewire::test(RekonsiliasiKas::class)
        ->call('startProcess', $setoran->id)
        ->assertSee('Total Diterima (Fisik)', false)
        ->set('processTotalDiterima', 90000)
        ->assertSee('Selisih: Rp 10.000 (Kurang)', false)
        ->assertSee('wire:model.live="processKeterangan"', false)
        ->assertSee('Keterangan Selisih (Wajib)', false);
});

it('melipat baris periode dan aksi laporan di layar kecil', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.laporan.index'))
        ->assertOk()
        ->assertSee('data-test="baris-periode" class="flex flex-wrap', false)
        ->assertSee('data-test="baris-aksi" class="flex flex-wrap', false);
});
