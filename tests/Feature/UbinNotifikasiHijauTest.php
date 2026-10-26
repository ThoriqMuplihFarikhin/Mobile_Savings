<?php

use App\Livewire\Nasabah\Notifikasi;
use App\Models\LogNotifikasi;
use App\Models\User;
use Livewire\Livewire;

/**
 * Fase E butir F: ubin notifikasi selalu hijau (DESIGN.md §14 baris Notifikasi:
 * kolom jenis_notifikasi ada dan kini terdaftar sebagai registry enum App\Support\JenisNotifikasi; batasan
 * jenis — hanya berisi channel in_app/whatsapp/both dan pin_awal — sehingga
 * fallback "Sebelum itu pakai ubin hijau" berlaku). Mengganti ubin biru/netral
 * per-status buatan Fase D; status baca tetap terbaca lewat gaya kartu, bobot
 * teks, ikon lonceng, dan titik "Belum dibaca".
 */
function ubinNotifikasiHijauFixture(): User
{
    return User::factory()->nasabah()->create();
}

function ubinNotifikasiBuat(User $nasabah, bool $sudahDibaca): void
{
    LogNotifikasi::create([
        'nasabah_id' => $nasabah->id,
        'judul' => 'Notifikasi '.($sudahDibaca ? 'dibaca' : 'baru'),
        'pesan' => 'Pesan uji ubin hijau.',
        'jenis_notifikasi' => 'in_app',
        'channel' => 'in_app',
        'status_kirim' => 'terkirim',
        'is_read' => $sudahDibaca,
        'waktu_kirim' => now(),
    ]);
}

it('memakai ubin hijau untuk notifikasi yang belum dibaca', function () {
    $nasabah = ubinNotifikasiHijauFixture();
    ubinNotifikasiBuat($nasabah, false);

    $html = Livewire::actingAs($nasabah)->test(Notifikasi::class)->html();

    expect($html)
        ->toContain('bg-primary-soft text-on-primary-soft size-10', false)
        ->not->toContain('bg-info-soft text-info size-10', false)
        ->not->toContain('bg-inset text-body size-10', false);
});

it('tetap memakai ubin hijau untuk notifikasi yang sudah dibaca', function () {
    $nasabah = ubinNotifikasiHijauFixture();
    ubinNotifikasiBuat($nasabah, true);

    $html = Livewire::actingAs($nasabah)->test(Notifikasi::class)->html();

    expect($html)
        ->toContain('bg-primary-soft text-on-primary-soft size-10', false)
        ->not->toContain('bg-info-soft text-info size-10', false)
        ->not->toContain('bg-inset text-body size-10', false)
        ->not->toContain('Belum dibaca', false);
});
