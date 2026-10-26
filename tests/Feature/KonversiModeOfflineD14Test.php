<?php

use App\Helpers\ActivityLogger;
use App\Livewire\Admin\Laporan;
use App\Livewire\Admin\ManajemenNasabah;
use App\Livewire\Kolektor\DaftarNasabah;
use App\Livewire\Kolektor\NasabahBinaan;
use App\Models\KolektorNasabah;
use App\Models\LogAktivitas;
use App\Models\LogNotifikasi;
use App\Models\NasabahProfil;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Buat profil nasabah untuk tes konversi mode (D14, Â§5.3.6-5.3.7).
 */
function profilP25(User $user, ?User $pedaftar = null, string $nama = 'Nasabah P25'): NasabahProfil
{
    return NasabahProfil::create([
        'user_id' => $user->id,
        'nama' => $nama,
        'alamat' => 'Jl. P25 Nomor 1',
        'didaftarkan_oleh' => ($pedaftar ?? User::factory()->kolektor()->create())->id,
        'status_pendaftaran' => 'aktif',
    ]);
}

/**
 * Nasabah mode offline (tanpa no_hp) + profilnya.
 *
 * @return array{0: User, 1: NasabahProfil}
 */
function nasabahOfflineP25(string $nama = 'Siti Offline'): array
{
    $user = User::factory()->nasabah()->create([
        'no_hp' => null,
        'mode_akses' => 'offline',
        'pin_hash' => Hash::make(Str::random(40)),
    ]);

    return [$user, profilP25($user, null, $nama)];
}

it('admin mengubah nasabah offline menjadi digital dengan no hp unik dan pin awal (D14)', function () {
    [$user, $profil] = nasabahOfflineP25('Budi Offline');
    $hashLama = $user->pin_hash;

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManajemenNasabah::class)
        ->call('confirmKonversi', $profil->id)
        ->set('konversiNoHp', '081234567899')
        ->call('konversiMode')
        ->assertSee('PIN awal');

    $user->refresh();

    expect($user->mode_akses)->toBe('digital')
        ->and($user->no_hp)->toBe('081234567899')
        ->and($user->harus_ganti_pin)->toBeTrue()
        ->and($user->pin_hash)->not->toBe($hashLama)
        ->and(Hash::check('123456', $user->pin_hash))->toBeFalse();

    expect(LogAktivitas::where('aksi', 'ubah_mode_akses')
        ->where('entitas_id', $user->id)->exists())->toBeTrue();
});

it('menolak konversi offline ke digital dengan no hp yang sudah dipakai (D14)', function () {
    [$user, $profil] = nasabahOfflineP25('Offline Unik');
    User::factory()->nasabah()->create(['no_hp' => '089876543210']);

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManajemenNasabah::class)
        ->call('confirmKonversi', $profil->id)
        ->set('konversiNoHp', '089876543210')
        ->call('konversiMode')
        ->assertHasErrors(['konversiNoHp' => 'unique']);

    $user->refresh();

    expect($user->mode_akses)->toBe('offline')
        ->and($user->no_hp)->toBeNull()
        ->and(LogAktivitas::where('aksi', 'ubah_mode_akses')
            ->where('entitas_id', $user->id)->exists())->toBeFalse();
});

it('admin mengubah nasabah digital ke offline menghapus sesi dan mematikan notifikasi (D14)', function () {
    $nasabah = User::factory()->nasabah()->create([
        'no_hp' => '089811122233',
        'notifikasi_wa_aktif' => true,
    ]);
    $profil = profilP25($nasabah, null, 'Dewi Digital');

    DB::table('sessions')->insert([
        'id' => 'sesi-p25-offline',
        'user_id' => $nasabah->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'tes',
        'payload' => '',
        'last_activity' => now()->getTimestamp(),
    ]);

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManajemenNasabah::class)
        ->call('confirmKonversi', $profil->id)
        ->call('konversiMode')
        ->assertSee('diubah ke mode offline');

    $nasabah->refresh();

    expect($nasabah->mode_akses)->toBe('offline')
        ->and((bool) $nasabah->notifikasi_wa_aktif)->toBeFalse()
        ->and(DB::table('sessions')->where('id', 'sesi-p25-offline')->exists())->toBeFalse()
        ->and(LogAktivitas::where('aksi', 'ubah_mode_akses')
            ->where('entitas_id', $nasabah->id)->exists())->toBeTrue();

    ActivityLogger::notify($nasabah->id, 'Tes Setelah Offline', 'Pesan tes.');

    expect(LogNotifikasi::where('nasabah_id', $nasabah->id)->count())->toBe(0);
});

it('daftar nasabah admin menampilkan lencana offline dan menyaring per mode (D14)', function () {
    [, $profilOffline] = nasabahOfflineP25('Siti Offline Tanpa HP');

    $digital = User::factory()->nasabah()->create(['no_hp' => '081111111111']);
    profilP25($digital, null, 'Budi Digital');

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(ManajemenNasabah::class)
        ->assertSee('Mode Offline')
        ->set('modeFilter', 'offline')
        ->assertSee('Siti Offline Tanpa HP')
        ->assertDontSee('Budi Digital')
        ->set('modeFilter', 'digital')
        ->assertSee('Budi Digital')
        ->assertDontSee('Siti Offline Tanpa HP')
        ->assertDontSee('Mode Offline');
});

it('daftar binaan kolektor menampilkan lencana offline filter mode dan hitungan (D14)', function () {
    $kolektor = User::factory()->kolektor()->create();

    $offline = User::factory()->nasabah()->create(['no_hp' => null, 'mode_akses' => 'offline']);
    $digital = User::factory()->nasabah()->create(['no_hp' => '082222222222']);

    foreach ([$offline, $digital] as $target) {
        KolektorNasabah::create([
            'kolektor_id' => $kolektor->id,
            'nasabah_id' => $target->id,
            'tanggal_mulai_ditangani' => now()->toDateString(),
            'status' => 'aktif',
        ]);
    }

    profilP25($offline, $kolektor, 'Sari Offline');
    profilP25($digital, $kolektor, 'Rudi Digital');

    $this->actingAs($kolektor);

    Livewire::test(NasabahBinaan::class)
        ->assertSee('Mode Offline')
        ->assertViewHas('totalNasabahOffline', 1)
        ->set('filterMode', 'offline')
        ->assertSee('Sari Offline')
        ->assertDontSee('Rudi Digital')
        ->set('filterMode', 'digital')
        ->assertSee('Rudi Digital')
        ->assertDontSee('Sari Offline')
        ->assertDontSee('Mode Offline');
});

it('daftar nasabah yang didaftarkan kolektor menampilkan lencana offline (D14)', function () {
    $kolektor = User::factory()->kolektor()->create();

    $offline = User::factory()->nasabah()->create(['no_hp' => null, 'mode_akses' => 'offline']);
    profilP25($offline, $kolektor, 'Hana Offline');

    $this->actingAs($kolektor);

    Livewire::test(DaftarNasabah::class)
        ->assertSee('Mode Offline');
});

it('kartu dashboard admin menampilkan jumlah nasabah offline (D14)', function () {
    nasabahOfflineP25();
    User::factory()->nasabah()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Nasabah Offline')
        ->assertViewHas('nasabahOffline', 1);
});

it('halaman laporan menampilkan hitungan nasabah offline (D14)', function () {
    nasabahOfflineP25();

    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Laporan::class)
        ->assertSee('Nasabah Offline')
        ->assertViewHas('jumlahNasabahOffline', 1);
});
