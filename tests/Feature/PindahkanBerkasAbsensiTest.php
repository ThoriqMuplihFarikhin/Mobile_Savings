<?php

use App\Models\AbsensiKolektor;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
});

it('memindahkan berkas absensi lama ke disk private dan idempoten', function () {
    $kolektor = User::factory()->kolektor()->create();

    Storage::disk('public')->put('selfie/1_1700000000.jpg', 'isi-selfie');
    Storage::disk('public')->put('tanda_tangan/1_1700000000.png', 'isi-tanda-tangan');

    $absensi = AbsensiKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal' => '2026-01-05',
        'waktu_masuk' => '08:00:00',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'foto_selfie_path' => 'selfie/1_1700000000.jpg',
        'tanda_tangan_path' => 'tanda_tangan/1_1700000000.png',
    ]);

    $this->artisan('absensi:pindahkan-berkas-lama')->assertSuccessful();

    $absensi->refresh();
    expect($absensi->foto_selfie_path)->toStartWith('absensi/selfie-')
        ->and($absensi->tanda_tangan_path)->toStartWith('absensi/tanda-tangan-')
        ->and(Storage::disk('local')->get($absensi->foto_selfie_path))->toBe('isi-selfie')
        ->and(Storage::disk('local')->get($absensi->tanda_tangan_path))->toBe('isi-tanda-tangan')
        ->and(Storage::disk('public')->exists('selfie/1_1700000000.jpg'))->toBeFalse()
        ->and(Storage::disk('public')->exists('tanda_tangan/1_1700000000.png'))->toBeFalse();

    $jalanKedua = [$absensi->foto_selfie_path, $absensi->tanda_tangan_path];

    $this->artisan('absensi:pindahkan-berkas-lama')->assertSuccessful();

    $absensi->refresh();
    expect([$absensi->foto_selfie_path, $absensi->tanda_tangan_path])->toBe($jalanKedua)
        ->and(Storage::disk('local')->allFiles('absensi'))->toHaveCount(2);
});

it('mode dry-run tidak mengubah berkas maupun kolom', function () {
    $kolektor = User::factory()->kolektor()->create();

    Storage::disk('public')->put('selfie/1_1700000000.jpg', 'isi-selfie');

    $absensi = AbsensiKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal' => '2026-01-05',
        'waktu_masuk' => '08:00:00',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'foto_selfie_path' => 'selfie/1_1700000000.jpg',
    ]);

    $this->artisan('absensi:pindahkan-berkas-lama', ['--dry-run' => true])->assertSuccessful();

    $absensi->refresh();
    expect($absensi->foto_selfie_path)->toBe('selfie/1_1700000000.jpg')
        ->and(Storage::disk('public')->exists('selfie/1_1700000000.jpg'))->toBeTrue()
        ->and(Storage::disk('local')->allFiles('absensi'))->toBeEmpty();
});

it('mengarsipkan berkas lama yang tidak lagi dirujuk baris absensi', function () {
    $kolektor = User::factory()->kolektor()->create();

    Storage::disk('public')->put('selfie/yatim_1700000000.jpg', 'isi-yatim');

    AbsensiKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal' => '2026-01-05',
        'waktu_masuk' => '08:00:00',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'foto_selfie_path' => 'absensi/selfie-baru.jpg',
    ]);

    $this->artisan('absensi:pindahkan-berkas-lama')->assertSuccessful();

    expect(Storage::disk('public')->allFiles('selfie'))->toBeEmpty()
        ->and(Storage::disk('local')->exists('absensi/arsip/yatim_1700000000.jpg'))->toBeTrue()
        ->and(Storage::disk('local')->get('absensi/arsip/yatim_1700000000.jpg'))->toBe('isi-yatim');
});

it('controller menyajikan berkas absensi setelah dipindahkan', function () {
    $kolektor = User::factory()->kolektor()->create();

    Storage::disk('public')->put('selfie/1_1700000000.jpg', 'isi-selfie');

    $absensi = AbsensiKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal' => now()->toDateString(),
        'waktu_masuk' => now()->toTimeString(),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'foto_selfie_path' => 'selfie/1_1700000000.jpg',
    ]);

    $this->artisan('absensi:pindahkan-berkas-lama')->assertSuccessful();

    $this->actingAs($kolektor)
        ->get(route('absensi.foto', [$absensi->fresh(), 'selfie']))
        ->assertOk();
});
