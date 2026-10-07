<?php

use App\Models\IzinKolektor;
use App\Models\KepesertaanPaket;
use App\Models\Komplain;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiPenarikan;
use App\Models\User;

/**
 * @return array{admin: User, kolektor: User, nasabah: User}
 */
function p71Peran(): array
{
    return [
        'admin' => User::factory()->admin()->create(),
        'kolektor' => User::factory()->kolektor()->create(),
        'nasabah' => User::factory()->nasabah()->create(),
    ];
}

/**
 * Satu baris pending untuk tiap antrean di hub persetujuan.
 *
 * @return array{produk: ProdukTabungan, kolektor: User, nasabah: User}
 */
function p71IsiAntrean(User $kolektor, User $nasabah): array
{
    $produk = ProdukTabungan::create([
        'nama' => 'Paket P71',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 10000,
        'tanggal_boleh_cair' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 0,
        'nominal_komisi' => 0,
        'nominal_diterima' => 50000,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'pending',
    ]);

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah P71',
        'alamat' => 'Jl. P71 No. 1',
        'tanggal_lahir' => '1990-01-01',
        'jenis_kelamin' => 'perempuan',
        'pekerjaan' => 'Wiraswasta',
        'didaftarkan_oleh' => $kolektor->id,
        'status_pendaftaran' => 'pending_verifikasi',
    ]);

    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_setor' => now()->toDateString(),
        'total_seharusnya' => 100000,
        'status' => 'pending',
    ]);

    Komplain::create([
        'nasabah_id' => $nasabah->id,
        'kategori' => 'saldo',
        'deskripsi' => 'Saldo tidak sesuai',
        'status' => 'baru',
        'tanggal_dibuat' => now(),
    ]);

    IzinKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_mulai' => now()->toDateString(),
        'tanggal_selesai' => now()->addDay()->toDateString(),
        'alasan' => 'Cuti',
        'status' => 'pending',
    ]);

    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(30)->toDateString(),
        'tunggakan' => 5,
        'status_alert' => 'perlu_review',
    ]);

    return ['produk' => $produk, 'kolektor' => $kolektor, 'nasabah' => $nasabah];
}

it('menampilkan hub persetujuan dengan hitungan antrean', function () {
    $peran = p71Peran();
    p71IsiAntrean($peran['kolektor'], $peran['nasabah']);

    $this->actingAs($peran['admin'])
        ->get(route('admin.persetujuan.index'))
        ->assertOk()
        ->assertViewHas('antrean', fn (array $antrean): bool => $antrean === [
            'penarikan' => 1,
            'verifikasi' => 1,
            'setoran_kantor' => 1,
            'komplain' => 1,
            'izin' => 1,
            'bermasalah' => 1,
        ]);
});

it('menampilkan nol untuk antrean yang kosong', function () {
    $peran = p71Peran();

    $this->actingAs($peran['admin'])
        ->get(route('admin.persetujuan.index'))
        ->assertOk()
        ->assertViewHas('antrean', fn (array $antrean): bool => array_sum($antrean) === 0);
});

it('menautkan tiap hitungan ke halaman antriannya', function () {
    $peran = p71Peran();
    p71IsiAntrean($peran['kolektor'], $peran['nasabah']);

    $this->actingAs($peran['admin'])
        ->get(route('admin.persetujuan.index'))
        ->assertOk()
        ->assertSee(route('admin.penarikan.index'))
        ->assertSee(route('admin.verifikasi.index'))
        ->assertSee(route('admin.rekonsiliasi.index'))
        ->assertSee(route('admin.komplain.index'))
        ->assertSee(route('admin.monitoring-absensi.index'))
        ->assertSee(route('admin.bermasalah.index'));
});

it('menolak role selain admin membuka hub persetujuan', function () {
    $peran = p71Peran();

    $this->actingAs($peran['kolektor'])->get(route('admin.persetujuan.index'))->assertForbidden();
    $this->actingAs($peran['nasabah'])->get(route('admin.persetujuan.index'))->assertForbidden();
});
