<?php

use App\Livewire\Kolektor\Absen;
use App\Models\AbsensiKolektor;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
});

function gambarValidAbsen(string $mime = 'png'): string
{
    $image = imagecreatetruecolor(8, 8);
    ob_start();
    $mime === 'png' ? imagepng($image) : imagejpeg($image);
    $raw = ob_get_clean();
    imagedestroy($image);

    return 'data:image/'.$mime.';base64,'.base64_encode($raw);
}

function isiFormAbsen($component, float $lat = -6.2, float $lng = 106.8)
{
    return $component
        ->call('setLokasi', $lat, $lng, 12.5)
        ->call('setSelfieBase64', gambarValidAbsen('jpeg'))
        ->call('setTandaTanganBase64', gambarValidAbsen('png'));
}

it('rejects absen masuk with non-image base64', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor);

    Livewire::test(Absen::class)
        ->call('setLokasi', -6.2, 106.8)
        ->call('setSelfieBase64', 'data:image/jpeg;base64,'.base64_encode('bukan gambar'))
        ->call('setTandaTanganBase64', gambarValidAbsen('png'))
        ->call('absenMasuk');

    $this->assertDatabaseMissing('absensi_kolektor', ['kolektor_id' => $kolektor->id]);
    expect(Storage::disk('local')->allFiles('absensi'))->toBeEmpty();
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('rejects absen masuk when image exceeds 1,5 MB', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor);

    Livewire::test(Absen::class)
        ->call('setLokasi', -6.2, 106.8)
        ->call('setSelfieBase64', 'data:image/png;base64,'.base64_encode(random_bytes(1600000)))
        ->call('setTandaTanganBase64', gambarValidAbsen('png'))
        ->call('absenMasuk');

    $this->assertDatabaseMissing('absensi_kolektor', ['kolektor_id' => $kolektor->id]);
    expect(Storage::disk('local')->allFiles('absensi'))->toBeEmpty();
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('rejects absen masuk with latitude out of range', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor);

    Livewire::test(Absen::class)
        ->call('setLokasi', 91, 106.8)
        ->call('setSelfieBase64', gambarValidAbsen('jpeg'))
        ->call('setTandaTanganBase64', gambarValidAbsen('png'))
        ->call('absenMasuk');

    $this->assertDatabaseMissing('absensi_kolektor', ['kolektor_id' => $kolektor->id]);
});

it('rejects absen masuk with longitude out of range', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor);

    Livewire::test(Absen::class)
        ->call('setLokasi', -6.2, -181)
        ->call('setSelfieBase64', gambarValidAbsen('jpeg'))
        ->call('setTandaTanganBase64', gambarValidAbsen('png'))
        ->call('absenMasuk');

    $this->assertDatabaseMissing('absensi_kolektor', ['kolektor_id' => $kolektor->id]);
});

it('stores absen masuk files on private disk with random names', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor);

    isiFormAbsen(Livewire::test(Absen::class))->call('absenMasuk');

    $this->assertDatabaseHas('absensi_kolektor', [
        'kolektor_id' => $kolektor->id,
        'tanggal' => now()->toDateString(),
        'akurasi' => 12.5,
    ]);

    $files = Storage::disk('local')->allFiles('absensi');
    expect($files)->toHaveCount(2)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('only creates one absen row per day even when flag is tampered', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor);

    $component = isiFormAbsen(Livewire::test(Absen::class))->call('absenMasuk');
    $component->set('sudahAbsenHariIni', false)->call('absenMasuk');

    expect(AbsensiKolektor::where('kolektor_id', $kolektor->id)->count())->toBe(1)
        ->and(Storage::disk('local')->allFiles('absensi'))->toHaveCount(2)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('does not leave orphan files when database insert fails', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor);

    AbsensiKolektor::creating(function (): void {
        throw new RuntimeException('simulasi kegagalan database');
    });

    try {
        isiFormAbsen(Livewire::test(Absen::class))->call('absenMasuk');
    } finally {
        AbsensiKolektor::flushEventListeners();
    }

    expect(AbsensiKolektor::where('kolektor_id', $kolektor->id)->count())->toBe(0)
        ->and(Storage::disk('local')->allFiles('absensi'))->toBeEmpty()
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('allows owner kolektor to view absensi foto through private route', function () {
    $kolektor = User::factory()->kolektor()->create();

    $absen = AbsensiKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal' => now()->toDateString(),
        'waktu_masuk' => now()->toTimeString(),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'foto_selfie_path' => 'absensi/selfie-contoh.png',
        'tanda_tangan_path' => 'absensi/ttd-contoh.png',
    ]);
    Storage::disk('local')->put('absensi/selfie-contoh.png', (string) base64_decode(gambarValidAbsen('png')));
    Storage::disk('local')->put('absensi/ttd-contoh.png', (string) base64_decode(gambarValidAbsen('png')));

    $response = $this->actingAs($kolektor)->get(route('absensi.foto', [$absen, 'selfie']));

    $response->assertOk();
    expect((string) $response->headers->get('Content-Type'))->toContain('image/png');

    $this->actingAs($kolektor)
        ->get(route('absensi.foto', [$absen, 'tanda-tangan']))
        ->assertOk();
});

it('allows admin to view absensi foto of any kolektor', function () {
    $kolektor = User::factory()->kolektor()->create();
    $admin = User::factory()->admin()->create();

    $absen = AbsensiKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal' => now()->toDateString(),
        'waktu_masuk' => now()->toTimeString(),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'foto_selfie_path' => 'absensi/selfie-contoh.png',
    ]);
    Storage::disk('local')->put('absensi/selfie-contoh.png', (string) base64_decode(gambarValidAbsen('png')));

    $this->actingAs($admin)
        ->get(route('absensi.foto', [$absen, 'selfie']))
        ->assertOk();
});

it('forbids other kolektor from viewing absensi foto', function () {
    $kolektor = User::factory()->kolektor()->create();
    $kolektorLain = User::factory()->kolektor()->create();

    $absen = AbsensiKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal' => now()->toDateString(),
        'waktu_masuk' => now()->toTimeString(),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'foto_selfie_path' => 'absensi/selfie-contoh.png',
    ]);
    Storage::disk('local')->put('absensi/selfie-contoh.png', (string) base64_decode(gambarValidAbsen('png')));

    $this->actingAs($kolektorLain)
        ->get(route('absensi.foto', [$absen, 'selfie']))
        ->assertForbidden();
});

it('redirects guest from absensi foto route', function () {
    $kolektor = User::factory()->kolektor()->create();

    $absen = AbsensiKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal' => now()->toDateString(),
        'waktu_masuk' => now()->toTimeString(),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'foto_selfie_path' => 'absensi/selfie-contoh.png',
    ]);

    $this->get(route('absensi.foto', [$absen, 'selfie']))
        ->assertRedirect();
});

it('rejects invalid jenis absensi foto route', function () {
    $kolektor = User::factory()->kolektor()->create();

    $absen = AbsensiKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal' => now()->toDateString(),
        'waktu_masuk' => now()->toTimeString(),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'foto_selfie_path' => 'absensi/selfie-contoh.png',
    ]);

    $this->actingAs($kolektor)
        ->get(route('absensi.foto', [$absen, 'rahasia']))
        ->assertNotFound();
});
