<?php

namespace Database\Seeders;

use App\Actions\Paket\IkutiPaketAction;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data demo lengkap untuk pengembangan dan uji manual (P9.3).
 *
 * Tidak pernah membuat data di produksi, tidak dipanggil `DatabaseSeeder`,
 * dan aman dijalankan berulang (guard penanda "Kolektor Demo").
 *
 * Isi, sesuai syarat plan 12.3:
 * - kolektor dengan kas fisik (setoran Rp 150.000 belum disetor ke kantor);
 * - nasabah offline (`mode_akses = offline`, `no_hp = null`, catatan offline);
 * - produk paket dengan `isi_paket` berisi harga per barang (D15);
 * - kepesertaan paket dengan komitmen disetujui (D16);
 * - penarikan `approved` yang bisa dibatalkan (D18) beserta saldo awal.
 */
class DemoSeeder extends Seeder
{
    private const PIN_DEMO = '123456';

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->info('DemoSeeder dilewati: environment produksi.');

            return;
        }

        if (User::where('name', 'Kolektor Demo')->exists()) {
            $this->command->info('Data demo sudah ada — tidak dibuat ulang.');

            return;
        }

        $this->call(RolesAndPermissionsSeeder::class);

        $admin = $this->admin();

        $kolektor = User::create([
            'name' => 'Kolektor Demo',
            'no_hp' => '089900000001',
            'pin_hash' => Hash::make(self::PIN_DEMO),
            'role' => 'kolektor',
            'status_akun' => 'aktif',
            'harus_ganti_pin' => true,
        ]);
        $kolektor->assignRole('kolektor');

        $nasabah = User::create([
            'name' => 'Nasabah Demo',
            'no_hp' => '089900000002',
            'pin_hash' => Hash::make(self::PIN_DEMO),
            'role' => 'nasabah',
            'status_akun' => 'aktif',
            'harus_ganti_pin' => true,
        ]);
        $nasabah->assignRole('nasabah');
        $nasabah->nasabahProfil()->create([
            'nama' => 'Nasabah Demo',
            'alamat' => 'Jl. Demo No. 1, Jakarta',
            'didaftarkan_oleh' => $admin->id,
            'status_pendaftaran' => 'aktif',
            'diverifikasi_oleh' => $admin->id,
            'tanggal_verifikasi' => now(),
        ]);

        $offline = User::create([
            'name' => 'Nasabah Offline Demo',
            'no_hp' => null,
            'pin_hash' => Hash::make(self::PIN_DEMO),
            'role' => 'nasabah',
            'mode_akses' => 'offline',
            'status_akun' => 'aktif',
            'harus_ganti_pin' => true,
        ]);
        $offline->assignRole('nasabah');
        $offline->nasabahProfil()->create([
            'nama' => 'Nasabah Offline Demo',
            'alamat' => 'Dusun Demo, Kec. Contoh',
            'catatan_offline' => 'Buku tabungan fisik no. 12',
            'didaftarkan_oleh' => $admin->id,
            'status_pendaftaran' => 'aktif',
            'diverifikasi_oleh' => $admin->id,
            'tanggal_verifikasi' => now(),
        ]);

        foreach ([$nasabah, $offline] as $warga) {
            KolektorNasabah::create([
                'kolektor_id' => $kolektor->id,
                'nasabah_id' => $warga->id,
                'tanggal_mulai_ditangani' => now()->toDateString(),
                'status' => 'aktif',
            ]);
        }

        $this->setoranKasKolektor($kolektor, $nasabah);
        $this->penarikanApproved($nasabah, $admin);
        $this->paketBerkomitmen($nasabah, $admin);

        $this->command->info('Data demo dibuat: kolektor + kas, nasabah digital & offline, paket berharga, komitmen, penarikan approved.');
    }

    private function admin(): User
    {
        $admin = User::where('role', 'admin')->orderBy('id')->first();
        if ($admin !== null) {
            return $admin;
        }

        $pinAdmin = $_ENV['SEED_ADMIN_PIN'] ?? $_SERVER['SEED_ADMIN_PIN'] ?? '';
        $pinDariEnv = is_string($pinAdmin) && $pinAdmin !== '';

        if (! $pinDariEnv) {
            $pinAdmin = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        }

        $admin = User::create([
            'name' => 'Admin Demo',
            'no_hp' => '089900000000',
            'pin_hash' => Hash::make($pinAdmin),
            'role' => 'admin',
            'status_akun' => 'aktif',
            'harus_ganti_pin' => true,
        ]);
        $admin->assignRole('admin');

        if (! $pinDariEnv) {
            $this->command->info("PIN admin demo hasil generate: {$pinAdmin} — tampil sekali, wajib diganti setelah login pertama.");
        }

        return $admin;
    }

    private function setoranKasKolektor(User $kolektor, User $nasabah): void
    {
        $produkBebas = ProdukTabungan::create([
            'nama' => 'Tabungan Bebas Demo',
            'tipe' => 'bebas',
            'persen_komisi' => 5.00,
            'minimal_setor' => 10000,
            'status' => 'aktif',
        ]);

        SaldoProduk::create([
            'nasabah_id' => $nasabah->id,
            'produk_id' => $produkBebas->id,
            'saldo' => 150000,
        ]);

        TransaksiSetoran::create([
            'nasabah_id' => $nasabah->id,
            'produk_id' => $produkBebas->id,
            'nominal' => 150000,
            'tanggal_transaksi' => now()->toDateString(),
            'tanggal_input_sistem' => now(),
            'input_by' => $kolektor->id,
            'sumber_input' => 'real_time',
            'status' => 'tercatat',
        ]);
    }

    private function penarikanApproved(User $nasabah, User $admin): void
    {
        $produkBebas = ProdukTabungan::where('nama', 'Tabungan Bebas Demo')->firstOrFail();

        TransaksiPenarikan::create([
            'nasabah_id' => $nasabah->id,
            'produk_id' => $produkBebas->id,
            'nominal_diminta' => 50000,
            'persen_komisi_terpakai' => 5.00,
            'nominal_komisi' => 2500,
            'nominal_diterima' => 47500,
            'jalur_pengajuan' => 'online',
            'lokasi_pengambilan' => 'kantor',
            'status' => 'approved',
            'disetujui_oleh' => $admin->id,
            'waktu_approval' => now(),
            'mempengaruhi_kas' => false,
        ]);

        SaldoProduk::where('nasabah_id', $nasabah->id)
            ->where('produk_id', $produkBebas->id)
            ->decrement('saldo', 50000);
    }

    private function paketBerkomitmen(User $nasabah, User $admin): void
    {
        $produkPaket = ProdukTabungan::create([
            'nama' => 'Paket Sembako Demo',
            'tipe' => 'paket',
            'persen_komisi' => 0,
            'minimal_setor' => 10000,
            'harga_per_hari' => 10000,
            'isi_paket' => [
                ['nama' => 'Beras 5 kg', 'jumlah' => '1 karung', 'harga' => 350000],
                ['nama' => 'Minyak goreng', 'jumlah' => '2 liter', 'harga' => 60000],
                ['nama' => 'Gula pasir', 'jumlah' => '1 kg', 'harga' => 20000],
            ],
            'periode_mulai' => now()->toDateString(),
            'periode_selesai' => now()->addDays(30)->toDateString(),
            'tanggal_boleh_cair' => now()->addDays(30)->toDateString(),
            'batas_toleransi_tunggakan_hari' => 3,
            'status' => 'aktif',
        ]);

        $kepesertaan = KepesertaanPaket::create([
            'nasabah_id' => $nasabah->id,
            'produk_id' => $produkPaket->id,
            'tanggal_mulai_ikut' => now()->toDateString(),
            'status_alert' => 'normal',
            'komitmen_disetujui_pada' => now(),
            'komitmen_via' => 'mandiri',
            'komitmen_dicatat_oleh' => $nasabah->id,
            'komitmen_teks' => IkutiPaketAction::teksKomitmen(),
        ]);

        SaldoProduk::create([
            'nasabah_id' => $nasabah->id,
            'produk_id' => $produkPaket->id,
            'saldo' => 10000,
        ]);

        TransaksiSetoran::create([
            'nasabah_id' => $nasabah->id,
            'produk_id' => $produkPaket->id,
            'kepesertaan_id' => $kepesertaan->id,
            'nominal' => 10000,
            'tanggal_transaksi' => now()->toDateString(),
            'tanggal_input_sistem' => now(),
            'input_by' => $admin->id,
            'sumber_input' => 'real_time',
            'status' => 'tercatat',
            'sudah_disetor_ke_kantor' => true,
        ]);

        $kepesertaan->hitungUlangKepesertaan();
    }
}
