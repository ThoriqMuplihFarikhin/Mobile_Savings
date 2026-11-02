<?php

use App\Models\KolektorNasabah;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use App\Models\User;

/**
 * @return array{nasabah: User, kolektorPj: User, kolektorBukanPj: User, admin: User, setoran: TransaksiSetoran}
 */
function p64StrukFixture(array $opsi = []): array
{
    $nasabah = User::factory()->nasabah()->create();
    $kolektorPj = User::factory()->kolektor()->create();
    $kolektorBukanPj = User::factory()->kolektor()->create();
    $admin = User::factory()->admin()->create();

    KolektorNasabah::create([
        'kolektor_id' => $kolektorPj->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Bebas P64 Struk',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'status' => 'aktif',
    ]);

    $setoran = TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => $opsi['nominal'] ?? 100000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektorPj->id,
        'sumber_input' => 'real_time',
        'status' => $opsi['status'] ?? 'tercatat',
        'nominal_asli' => $opsi['nominal_asli'] ?? null,
    ]);

    return [
        'nasabah' => $nasabah,
        'kolektorPj' => $kolektorPj,
        'kolektorBukanPj' => $kolektorBukanPj,
        'admin' => $admin,
        'setoran' => $setoran,
        'produk' => $produk,
    ];
}

it('nasabah pemilik, kolektor penanggung jawab, dan admin dapat membuka struk', function () {
    ['nasabah' => $nasabah, 'kolektorPj' => $kolektorPj, 'admin' => $admin, 'setoran' => $setoran] = p64StrukFixture();

    $nomor = 'SET-'.str_pad((string) $setoran->id, 6, '0', STR_PAD_LEFT);

    $this->actingAs($nasabah)
        ->get(route('struk.setoran', $setoran))
        ->assertOk()
        ->assertSee($nomor)
        ->assertSee('Rp 100.000')
        ->assertSee($kolektorPj->name)
        ->assertSee('Cetak');

    $this->actingAs($kolektorPj)
        ->get(route('struk.setoran', $setoran))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('struk.setoran', $setoran))
        ->assertOk();
});

it('menolak kolektor bukan penanggung jawab dan nasabah lain', function () {
    ['kolektorBukanPj' => $kolektorBukanPj, 'setoran' => $setoran] = p64StrukFixture();
    $nasabahLain = User::factory()->nasabah()->create();

    $this->actingAs($kolektorBukanPj)
        ->get(route('struk.setoran', $setoran))
        ->assertForbidden();

    $this->actingAs($nasabahLain)
        ->get(route('struk.setoran', $setoran))
        ->assertForbidden();
});

it('menolak tamu dan setoran yang tidak ada', function () {
    ['setoran' => $setoran, 'nasabah' => $nasabah] = p64StrukFixture();

    $this->get(route('struk.setoran', $setoran))->assertRedirect();

    $this->actingAs($nasabah)
        ->get(route('struk.setoran', 999999))
        ->assertNotFound();
});

it('memuat status koreksi bila setoran pernah dikoreksi', function () {
    ['nasabah' => $nasabah, 'setoran' => $setoran] = p64StrukFixture([
        'status' => 'dikoreksi',
        'nominal' => 120000,
        'nominal_asli' => 150000,
    ]);

    $this->actingAs($nasabah)
        ->get(route('struk.setoran', $setoran))
        ->assertOk()
        ->assertSee('Dikoreksi')
        ->assertSee('Rp 150.000')
        ->assertSee('Rp 120.000');
});

it('halaman struk memiliki struktur pop up modal dengan role dialog dan tombol tutup', function () {
    ['nasabah' => $nasabah, 'setoran' => $setoran] = p64StrukFixture();

    $this->actingAs($nasabah)
        ->get(route('struk.setoran', $setoran))
        ->assertOk()
        ->assertSee('role="dialog"', false)
        ->assertSee('aria-modal="true"', false)
        ->assertSee('struk-popup')
        ->assertSee('tutupPopUp()')
        ->assertSee('Tutup Struk')
        ->assertSee('Bagikan');
});

it('endpoint struk mengembalikan format json saat diminta untuk pop up modal', function () {
    ['nasabah' => $nasabah, 'setoran' => $setoran] = p64StrukFixture([
        'nominal' => 250000,
    ]);

    $nomor = 'SET-'.str_pad((string) $setoran->id, 6, '0', STR_PAD_LEFT);

    $this->actingAs($nasabah)
        ->getJson(route('struk.setoran', $setoran))
        ->assertOk()
        ->assertJson([
            'id' => $setoran->id,
            'nomor' => $nomor,
            'nasabah' => $nasabah->name,
            'nominal' => 250000,
            'nominal_formatted' => 'Rp 250.000',
            'status' => 'tercatat',
            'status_label' => 'Diterima',
        ]);
});

it('dashboard memuat modal struk dan trigger pop up', function () {
    ['nasabah' => $nasabah] = p64StrukFixture();

    $this->actingAs($nasabah)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-test="modal-struk-popup"', false)
        ->assertSee('buka-struk');
});
