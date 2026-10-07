<?php

use App\Livewire\Admin\Laporan;
use App\Models\User;
use Livewire\Livewire;

it('menukarkan seluruh tabel laporan menjadi kartu di layar kecil', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $ekspektasi = [
        'keuangan' => 1,
        'kolektor' => 1,
        'rekon' => 2,
        'umurkas' => 1,
        'mutasi' => 1,
        'penarikan' => 1,
        'tunggakan' => 1,
        'serah' => 1,
        'absensi' => 2,
        'nasabah' => 1,
        'paket' => 1,
        'barang' => 1,
    ];

    foreach ($ekspektasi as $seksi => $jumlah) {
        $komponen = Livewire::test(Laporan::class)->set('seksi', $seksi);

        if ($seksi === 'keuangan') {
            $komponen->set('periode', 'bulanan');
        }

        $html = $komponen->html();

        expect(substr_count($html, 'hidden md:table'))
            ->toBe($jumlah, "jumlah tabel md untuk seksi {$seksi} tidak sesuai");
        expect(substr_count($html, 'data-test="kartu-tabel"'))
            ->toBe($jumlah, "jumlah kartu md untuk seksi {$seksi} tidak sesuai");
    }
});

it('tetap menampilkan tabel laporan untuk layar besar', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $html = Livewire::test(Laporan::class)->set('seksi', 'penarikan')->html();

    expect($html)->toContain('hidden md:table');
    expect($html)->toContain('Detail Penarikan');
    expect($html)->toContain('data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden');
});
