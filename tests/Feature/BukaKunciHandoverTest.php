<?php

use App\Livewire\Admin\KelolaKolektor;
use App\Models\LogHandoverKolektor;
use App\Models\User;
use Livewire\Livewire;

it('tidak membuka kunci kolektor yang dikunci hasil serah terima', function () {
    $admin = User::factory()->admin()->create();
    $kolektorLama = User::factory()->kolektor()->create(['status_akun' => 'terkunci']);
    $kolektorBaru = User::factory()->kolektor()->create(['status_akun' => 'aktif']);

    LogHandoverKolektor::create([
        'kolektor_lama_id' => $kolektorLama->id,
        'kolektor_baru_id' => $kolektorBaru->id,
        'tanggal_handover' => now()->toDateString(),
        'jumlah_nasabah_dipindah' => 2,
        'status_kas_saat_handover' => 'lunas',
        'diproses_oleh' => $admin->id,
    ]);

    $this->actingAs($admin);

    Livewire::test(KelolaKolektor::class)
        ->call('bukaKunci', $kolektorLama->id)
        ->assertSee('serah terima');

    expect($kolektorLama->fresh()->status_akun)->toBe('terkunci');
});

it('tetap membuka kunci kolektor yang dikunci bukan karena serah terima', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create(['status_akun' => 'terkunci']);

    $this->actingAs($admin);

    Livewire::test(KelolaKolektor::class)
        ->call('bukaKunci', $kolektor->id)
        ->assertSee('berhasil dibuka');

    expect($kolektor->fresh()->status_akun)->toBe('aktif');
});
