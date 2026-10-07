<?php

use App\Livewire\Nasabah\PilihPaket;
use App\Models\AdminSetting;
use App\Models\KepesertaanPaket;
use App\Models\LogAktivitas;
use App\Models\LogNotifikasi;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/**
 * @return array{nasabah: User, layak: ProdukTabungan}
 */
function seedPilihPaket(): array
{
    $nasabah = User::factory()->nasabah()->create();

    $layak = ProdukTabungan::create([
        'nama' => 'Paket Layak Ikut',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 10000,
        'periode_mulai' => now()->toDateString(),
        'periode_selesai' => now()->addDays(29)->toDateString(),
        'tanggal_boleh_cair' => now()->addDays(30)->toDateString(),
        'batas_toleransi_tunggakan_hari' => 3,
        'isi_paket' => [
            ['nama' => 'Beras', 'jumlah' => '5 kg', 'harga' => 60000],
        ],
        'status' => 'aktif',
    ]);

    return compact('nasabah', 'layak');
}

/**
 * @param  array<string, mixed>  $perubahan
 */
function buatPaketTakLayakPilih(array $perubahan): ProdukTabungan
{
    return ProdukTabungan::create(array_merge([
        'nama' => 'Paket Tak Layak',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 5000,
        'status' => 'nonaktif',
    ], $perubahan));
}

/**
 * Buka modal komitmen lalu isi persetujuan dan PIN.
 */
function isiKomitmenPilihPaket(Testable $komponen, string $pin = '123456'): Testable
{
    return $komponen->set('setuju', true)->set('pin', $pin);
}

it('menampilkan halaman dan hanya paket yang masih bisa diikuti', function () {
    $data = seedPilihPaket();

    $nonaktif = buatPaketTakLayakPilih(['nama' => 'Nonaktif']);
    $periodeLewat = buatPaketTakLayakPilih([
        'nama' => 'Periode Lewat',
        'status' => 'aktif',
        'periode_selesai' => now()->subDay()->toDateString(),
    ]);
    $batasLewat = buatPaketTakLayakPilih([
        'nama' => 'Batas Lewat',
        'status' => 'aktif',
        'periode_selesai' => now()->addDays(10)->toDateString(),
        'batas_daftar_hingga' => now()->subDay()->toDateString(),
    ]);
    $sudahDiikuti = buatPaketTakLayakPilih([
        'nama' => 'Sudah Diikuti',
        'status' => 'aktif',
        'periode_selesai' => now()->addDays(10)->toDateString(),
    ]);
    KepesertaanPaket::create([
        'nasabah_id' => $data['nasabah']->id,
        'produk_id' => $sudahDiikuti->id,
        'tanggal_mulai_ikut' => now()->subDay()->toDateString(),
    ]);

    $this->actingAs($data['nasabah'])->get(route('nasabah.paket.pilih'))->assertOk();

    $this->actingAs($data['nasabah']);
    $daftar = collect(Livewire::test(PilihPaket::class)->viewData('paketTersedia'));

    expect($daftar->pluck('id')->all())->toBe([$data['layak']->id])
        ->and($daftar->firstWhere('id', $nonaktif->id))->toBeNull()
        ->and($daftar->firstWhere('id', $periodeLewat->id))->toBeNull()
        ->and($daftar->firstWhere('id', $batasLewat->id))->toBeNull();
});

it('menyembunyikan harga per item isi paket dari nasabah', function () {
    $data = seedPilihPaket();

    $this->actingAs($data['nasabah']);
    $item = collect(Livewire::test(PilihPaket::class)->viewData('paketTersedia'))->first()['isi'][0];

    expect($item)->not->toHaveKey('harga')
        ->and($item['nama'])->toBe('Beras')
        ->and($item['jumlah'])->toBe('5 kg');
});

it('mengikuti paket dengan konfirmasi pin dan mencatat komitmen mandiri', function () {
    $data = seedPilihPaket();

    $this->actingAs($data['nasabah']);
    Livewire::test(PilihPaket::class)
        ->call('bukaKomitmen', $data['layak']->id)
        ->tap(fn ($komponen) => isiKomitmenPilihPaket($komponen))
        ->call('ikutiPaket')
        ->assertHasNoErrors();

    $kepesertaan = KepesertaanPaket::where('nasabah_id', $data['nasabah']->id)
        ->where('produk_id', $data['layak']->id)
        ->firstOrFail();

    expect($kepesertaan->komitmen_via)->toBe('mandiri')
        ->and($kepesertaan->komitmen_dicatat_oleh)->toBe($data['nasabah']->id)
        ->and($kepesertaan->komitmen_teks)->not->toBeEmpty()
        ->and($kepesertaan->komitmen_disetujui_pada)->not->toBeNull()
        ->and(SaldoProduk::where('nasabah_id', $data['nasabah']->id)
            ->where('produk_id', $data['layak']->id)->exists())->toBeTrue()
        ->and(LogAktivitas::where('aksi', 'ikut_paket')
            ->where('entitas_terkait', 'kepesertaan_paket')
            ->where('entitas_id', $kepesertaan->id)->exists())->toBeTrue()
        ->and(LogNotifikasi::where('nasabah_id', $data['nasabah']->id)
            ->where('judul', 'Berhasil Ikut Paket')->exists())->toBeTrue();
});

it('memerlukan persetujuan centang dan pin sebelum mengikuti paket', function () {
    $data = seedPilihPaket();

    $this->actingAs($data['nasabah']);
    Livewire::test(PilihPaket::class)
        ->call('bukaKomitmen', $data['layak']->id)
        ->set('setuju', false)
        ->set('pin', '')
        ->call('ikutiPaket')
        ->assertHasErrors(['setuju', 'pin']);

    expect(KepesertaanPaket::count())->toBe(0);
});

it('menolak pin salah dengan sisa percobaan lalu mengunci', function () {
    $data = seedPilihPaket();

    $this->actingAs($data['nasabah']);
    $komponen = Livewire::test(PilihPaket::class)
        ->call('bukaKomitmen', $data['layak']->id)
        ->set('setuju', true)
        ->set('pin', 'salah-satu')
        ->call('ikutiPaket')
        ->assertSet('pesanError', 'PIN salah. Sisa percobaan: 4.');

    expect(KepesertaanPaket::count())->toBe(0);

    for ($i = 0; $i < 4; $i++) {
        $komponen->call('ikutiPaket');
    }

    $komponen->call('ikutiPaket')
        ->assertSet('pesanError', 'Terlalu banyak percobaan PIN. Coba lagi nanti atau hubungi admin.');

    expect(RateLimiter::tooManyAttempts('ikut-paket:nasabah:'.$data['nasabah']->id, 5))->toBeTrue()
        ->and(KepesertaanPaket::count())->toBe(0);
});

it('menolak nasabah yang sudah mengikuti paket yang sama', function () {
    $data = seedPilihPaket();

    KepesertaanPaket::create([
        'nasabah_id' => $data['nasabah']->id,
        'produk_id' => $data['layak']->id,
        'tanggal_mulai_ikut' => now()->toDateString(),
    ]);

    $this->actingAs($data['nasabah']);
    Livewire::test(PilihPaket::class)
        ->call('bukaKomitmen', $data['layak']->id)
        ->set('setuju', true)
        ->set('pin', '123456')
        ->call('ikutiPaket')
        ->assertSet('pesanError', 'Anda sudah mengikuti paket ini.');

    expect(KepesertaanPaket::count())->toBe(1);
});

it('menolak nasabah yang belum mengganti pin awal', function () {
    $data = seedPilihPaket();
    User::whereKey($data['nasabah']->id)->update(['harus_ganti_pin' => true]);

    $this->actingAs($data['nasabah']->fresh());
    Livewire::test(PilihPaket::class)
        ->call('bukaKomitmen', $data['layak']->id)
        ->set('setuju', true)
        ->set('pin', '123456')
        ->call('ikutiPaket')
        ->assertSet('pesanError', 'Nasabah belum mengganti PIN awal. Silakan ganti PIN terlebih dahulu di halaman Pengaturan.');

    expect(KepesertaanPaket::count())->toBe(0);
});

it('menggunakan teks komitmen dari pengaturan admin bila disetel', function () {
    $data = seedPilihPaket();
    AdminSetting::set('teks_komitmen_paket', 'Teks komitmen khusus admin.');

    $this->actingAs($data['nasabah']);
    $komponen = Livewire::test(PilihPaket::class)
        ->assertViewHas('teksKomitmen', 'Teks komitmen khusus admin.');

    $komponen->call('bukaKomitmen', $data['layak']->id)
        ->tap(fn ($k) => isiKomitmenPilihPaket($k))
        ->call('ikutiPaket');

    expect(KepesertaanPaket::firstOrFail()->komitmen_teks)->toBe('Teks komitmen khusus admin.');
});

it('hanya nasabah yang boleh membuka halaman ikuti paket', function () {
    $data = seedPilihPaket();

    $this->actingAs($data['nasabah'])->get(route('nasabah.paket.pilih'))->assertOk();
    $this->actingAs(User::factory()->admin()->create())->get(route('nasabah.paket.pilih'))->assertForbidden();
});
