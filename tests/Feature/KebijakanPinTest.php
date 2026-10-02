<?php

use App\Livewire\Admin\ApprovalPenarikan;
use App\Livewire\Admin\KelolaKolektor;
use App\Livewire\Admin\ManajemenNasabah;
use App\Livewire\Admin\RegistrasiNasabah;
use App\Models\LogAktivitas;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

function produkKebijakanT33(): ProdukTabungan
{
    return ProdukTabungan::create([
        'nama' => 'Tabungan Bebas T33',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);
}

it('admin mengubah pin nasabah menandai harus ganti pin dan mereset kunci percobaan gagal', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create([
        'pin_hash' => bcrypt('111111'),
        'harus_ganti_pin' => false,
        'percobaan_gagal' => 4,
        'login_terkunci_hingga' => now()->addMinutes(5),
    ]);
    $profil = NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => $nasabah->name,
        'alamat' => 'Jalan Contoh',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    Livewire::actingAs($admin)
        ->test(ManajemenNasabah::class)
        ->call('edit', $profil->id)
        ->set('pin', '222222')
        ->call('save');

    $nasabah->refresh();
    expect(Hash::check('222222', $nasabah->pin_hash))->toBeTrue()
        ->and($nasabah->harus_ganti_pin)->toBeTrue()
        ->and($nasabah->percobaan_gagal)->toBe(0)
        ->and($nasabah->login_terkunci_hingga)->toBeNull();
});

it('admin mengubah pin kolektor menandai harus ganti pin dan mereset kunci percobaan gagal', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create([
        'pin_hash' => bcrypt('111111'),
        'harus_ganti_pin' => false,
        'percobaan_gagal' => 3,
        'login_terkunci_hingga' => now()->addMinutes(5),
    ]);

    Livewire::actingAs($admin)
        ->test(KelolaKolektor::class)
        ->call('edit', $kolektor->id)
        ->set('pin', '333333')
        ->call('save');

    $kolektor->refresh();
    expect(Hash::check('333333', $kolektor->pin_hash))->toBeTrue()
        ->and($kolektor->harus_ganti_pin)->toBeTrue()
        ->and($kolektor->percobaan_gagal)->toBe(0)
        ->and($kolektor->login_terkunci_hingga)->toBeNull();
});

it('setelah admin mereset pin nasabah terkunci, login kembali berhasil dengan pin baru', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create([
        'pin_hash' => bcrypt('111111'),
        'harus_ganti_pin' => false,
        'percobaan_gagal' => 4,
        'login_terkunci_hingga' => now()->addMinutes(5),
    ]);
    $profil = NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => $nasabah->name,
        'alamat' => 'Jalan Contoh',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $this->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), ['no_hp' => $nasabah->no_hp, 'password' => '111111']);
    $this->assertGuest();

    Livewire::actingAs($admin)
        ->test(ManajemenNasabah::class)
        ->call('edit', $profil->id)
        ->set('pin', '222222')
        ->call('save');

    $this->app['auth']->guard('web')->logout();

    $this->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), ['no_hp' => $nasabah->no_hp, 'password' => '222222'])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    expect($this->app['auth']->id())->toBe($nasabah->id);
});

it('kolektor baru dibuat admin wajib ganti pin', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(KelolaKolektor::class)
        ->set('name', 'Kolektor Baru')
        ->set('noHp', '0811999888777')
        ->set('pin', '123456')
        ->call('save');

    $kolektor = User::where('no_hp', '0811999888777')->firstOrFail();
    expect($kolektor->role)->toBe('kolektor')
        ->and($kolektor->harus_ganti_pin)->toBeTrue();
});

it('admin dengan harus ganti pin diarahkan ke halaman security admin', function () {
    $admin = User::factory()->admin()->create(['harus_ganti_pin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.nasabah.index'))
        ->assertRedirect(route('admin.settings.security'));
});

it('admin dengan harus ganti pin tetap bisa membuka halaman security admin', function () {
    $admin = User::factory()->admin()->create(['harus_ganti_pin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.settings.security'))
        ->assertOk();
});

it('kolektor dengan harus ganti pin tetap diarahkan ke halaman ganti pin', function () {
    $kolektor = User::factory()->kolektor()->create(['harus_ganti_pin' => true]);

    $this->actingAs($kolektor)
        ->get(route('kolektor.setoran.index'))
        ->assertRedirect(route('security.edit'));
});

it('pesanan flash pin awal registrasi tidak tersimpan di log aktivitas', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(RegistrasiNasabah::class)
        ->set('nama', 'Nasabah Baru')
        ->set('noHp', '081234567777')
        ->set('alamat', 'Jalan Registrasi 1')
        ->set('tanggalLahir', '1995-05-05')
        ->set('jenisKelamin', 'laki-laki')
        ->call('submit')
        ->assertHasNoErrors();

    $log = LogAktivitas::latest('id')->first();
    expect($log)->not->toBeNull()
        ->and($log->aksi)->toBe('registrasi_nasabah')
        ->and(json_encode($log->detail))->not->toMatch('/(?<!\d)\d{6}(?!\d)/');
});

it('pencairan manual admin ditolak tanpa alasan', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();
    $produk = produkKebijakanT33();
    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 0,
        'nominal_diterima' => 50000,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'approved',
        'disetujui_oleh' => $admin->id,
        'waktu_approval' => now(),
    ]);

    Livewire::actingAs($admin)
        ->test(ApprovalPenarikan::class)
        ->call('selesai', $penarikan->id);

    expect($penarikan->refresh()->status)->toBe('approved');
});

it('pencairan manual admin mencatat alasan di log aktivitas', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();
    $produk = produkKebijakanT33();
    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 0,
        'nominal_diterima' => 50000,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'approved',
        'disetujui_oleh' => $admin->id,
        'waktu_approval' => now(),
    ]);

    Livewire::actingAs($admin)
        ->test(ApprovalPenarikan::class)
        ->set('alasan', 'Pencairan dikonfirmasi langsung di kantor')
        ->call('selesai', $penarikan->id);

    $penarikan->refresh();
    expect($penarikan->status)->toBe('selesai')
        ->and($penarikan->metode_verifikasi)->toBe('manual_admin');

    $log = LogAktivitas::latest('id')->first();
    expect($log->aksi)->toBe('selesai_penarikan')
        ->and($log->detail['alasan'] ?? null)->toBe('Pencairan dikonfirmasi langsung di kantor');
});
