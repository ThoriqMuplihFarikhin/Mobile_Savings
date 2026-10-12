<?php

namespace App\Actions\Penarikan;

use App\Helpers\ActivityLogger;
use App\Livewire\Admin\ApprovalPenarikan;
use App\Models\AdminSetting;
use App\Models\KolektorNasabah;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use App\Support\BuktiPenarikan;
use App\Support\KasKolektorHitung;
use Illuminate\Support\Facades\DB;

/**
 * Catat penarikan tunai kolektor untuk nasabah mode offline (D14, §5.3.5).
 *
 * Validasi saldo (lockForUpdate), nominal minimal, gerbang paket, dan
 * tunggakan dipinjam dari AjukanPenarikanAction. Seluruh keputusan
 * "langsung selesai atau masuk antrian" ada di satu metode tunggal
 * {@see alasanTidakLangsung()} yang menerapkan syarat D23 secara berurutan
 * dan mengembalikan kode alasan; selain itu penarikan masuk antrian approval
 * admin seperti biasa. Kas kolektor (D13) hanya berkurang untuk pengambilan
 * di rumah kolektor.
 */
class CatatPenarikanOfflineLangsungAction
{
    /**
     * @param  string  $catatan  Catatan wajib penarikan offline (tersimpan di kolom transaksi_penarikan, D24).
     * @param  string|null  $buktiBase64  Gambar bukti (tanda tangan/foto) wajib untuk nasabah offline.
     * @param  string  $buktiJenis  `tanda_tangan` atau `foto`.
     * @return array{penarikan: TransaksiPenarikan, langsung: bool, alasan: string}
     *
     * @throws \Exception bila validasi ditolak
     */
    public function execute(
        User $kolektor,
        User $nasabah,
        ProdukTabungan $produk,
        float|string $nominal,
        string $lokasiPengambilan = 'kantor',
        string $catatan = '',
        ?string $buktiBase64 = null,
        string $buktiJenis = 'tanda_tangan',
    ): array {
        $isTanggungJawab = KolektorNasabah::where('kolektor_id', $kolektor->id)
            ->where('nasabah_id', $nasabah->id)
            ->where('status', 'aktif')
            ->exists();

        if (! $isTanggungJawab) {
            throw new \Exception('Nasabah ini bukan tanggung jawab Anda.');
        }

        $buktiPath = null;

        if ($nasabah->isOffline()) {
            // D24: bukti wajib untuk seluruh penarikan nasabah offline,
            // termasuk yang berakhir di antrian approval agar admin melihatnya.
            $bukti = BuktiPenarikan::simpan($buktiBase64, $buktiJenis);

            if (! $bukti['ok']) {
                throw new \Exception('Bukti penarikan: '.$bukti['error']);
            }

            $buktiPath = $bukti['path'];
        }

        try {
            $penarikan = (new AjukanPenarikanAction)->execute(
                $nasabah,
                $produk,
                $nominal,
                'offline',
                $lokasiPengambilan,
            );

            $hasil = DB::transaction(function () use ($penarikan, $kolektor, $nasabah, $lokasiPengambilan, $catatan, $buktiPath, $buktiJenis) {
                // Kunci baris kolektor lebih dahulu: seluruh pemeriksaan batas
                // harian dan kas kolektor harus serial per kolektor (D23).
                User::whereKey($kolektor->id)->lockForUpdate()->first();
                KasKolektorHitung::kunciBarisKas($kolektor->id);

                $p = TransaksiPenarikan::whereKey($penarikan->id)->lockForUpdate()->first();

                if (! $p) {
                    throw new \Exception('Data penarikan tidak ditemukan.');
                }

                $p->update([
                    'bukti_path' => $buktiPath,
                    'bukti_jenis' => $buktiPath !== null ? $buktiJenis : null,
                    'catatan_kolektor' => $catatan !== '' ? $catatan : null,
                ]);

                if ($p->status !== 'pending') {
                    return ['penarikan' => $p, 'langsung' => false, 'alasan' => 'tidak_pending'];
                }

                $saldo = SaldoProduk::where('nasabah_id', $p->nasabah_id)
                    ->where('produk_id', $p->produk_id)
                    ->lockForUpdate()
                    ->first();

                if (! $saldo || bccomp((string) $saldo->saldo, (string) $p->nominal_diminta, 2) < 0) {
                    throw new \Exception('Saldo nasabah tidak mencukupi!');
                }

                $alasan = static::alasanTidakLangsung(
                    $kolektor,
                    $nasabah,
                    (string) $p->nominal_diminta,
                    (string) $p->nominal_diterima,
                    $lokasiPengambilan,
                    $buktiPath !== null,
                );

                if ($alasan !== null) {
                    // Guard gagal → penarikan tetap di antrian approval admin.
                    return ['penarikan' => $p, 'langsung' => false, 'alasan' => $alasan];
                }

                $diRumah = $lokasiPengambilan === 'rumah_kolektor';
                $kasSebelum = $diRumah ? KasKolektorHitung::kasDiTangan($kolektor->id) : null;

                $saldo->decrement('saldo', $p->nominal_diminta);

                $p->update([
                    'status' => 'selesai',
                    'jalur_pengajuan' => 'offline_kolektor',
                    'metode_verifikasi' => 'tanpa_verifikasi_offline',
                    'diverifikasi_oleh' => $kolektor->id,
                    'dibayar_oleh' => $kolektor->id,
                    'mempengaruhi_kas' => $diRumah,
                    'waktu_approval' => now(),
                    'waktu_pencairan' => now(),
                ]);

                if ($diRumah) {
                    // D13: tautkan ke pengajuan setor pending dan perbarui total net.
                    $pengajuanPending = SetoranKolektorKantor::where('kolektor_id', $kolektor->id)
                        ->where('status', 'pending')
                        ->lockForUpdate()
                        ->first();

                    if ($pengajuanPending) {
                        $p->update(['setoran_kolektor_id' => $pengajuanPending->id]);
                        $pengajuanPending->update([
                            'total_seharusnya' => KasKolektorHitung::totalSeharusnyaPengajuan($pengajuanPending->id),
                        ]);
                    }

                    ActivityLogger::log('kas_berkurang_penarikan_tunai', 'transaksi_penarikan', $p->id, [
                        'kolektor_id' => $kolektor->id,
                        'nominal_diterima' => $p->nominal_diterima,
                        'kas_sebelum' => $kasSebelum,
                        'kas_sesudah' => bcsub(self::bc($kasSebelum), (string) $p->nominal_diterima, 2),
                        'jalur' => 'offline_kolektor',
                    ]);
                }

                ActivityLogger::log('selesai_penarikan_offline', 'transaksi_penarikan', $p->id, [
                    'nasabah_id' => $p->nasabah_id,
                    'kolektor_id' => $kolektor->id,
                    'nominal' => $p->nominal_diminta,
                    'metode' => 'tanpa_verifikasi_offline',
                    'catatan' => $catatan,
                ]);

                return ['penarikan' => $p, 'langsung' => true, 'alasan' => 'langsung'];
            });

            if ($hasil['langsung']) {
                $this->notifikasiAdmin($hasil['penarikan']);
            }

            return $hasil;
        } catch (\Throwable $e) {
            // Tanpa berkas yatim: bila transaksi gagal, berkas bukti ikut hilang.
            BuktiPenarikan::hapus($buktiPath);

            throw $e;
        }
    }

    /**
     * Satu-satunya tempat keputusan "langsung atau antrian" (D23).
     *
     * Syarat diterapkan berurutan dan mengembalikan kode alasan pertama yang
     * terpenuhi, atau null bila penarikan layak langsung `selesai`. Metode ini
     * juga dipakai UI untuk menampilkan pratinjau keputusan sebelum kirim
     * (tanpa menulis apa pun ke database), sehingga tidak boleh bergantung
     * pada baris penarikan yang sudah tersimpan.
     *
     * Pemanggilan dari dalam transaksi wajib sudah mengunci baris kolektor
     * dan baris kas ({@see KasKolektorHitung::kunciBarisKas}) agar dua
     * permintaan paralel tidak melampaui batas harian.
     *
     * @param  float|string  $nominalDiminta  Nominal yang diminta nasabah.
     * @param  float|string  $nominalDiterima  Nominal yang benar-benar diterima nasabah (setelah komisi).
     */
    public static function alasanTidakLangsung(
        User $kolektor,
        User $nasabah,
        float|string $nominalDiminta,
        float|string $nominalDiterima,
        string $lokasiPengambilan,
        bool $adaBukti,
    ): ?string {
        if (! $nasabah->isOffline()) {
            return 'digital';
        }

        if (AdminSetting::get('offline_penarikan_langsung_selesai', 'true') !== 'true') {
            return 'setting_nonaktif';
        }

        $batas = AdminSetting::get('offline_penarikan_batas_langsung', '0');
        if (! is_numeric($batas) || (float) $batas <= 0) {
            // Default aman D23: 0 = penarikan langsung dinonaktifkan.
            return 'batas_nol';
        }

        if ($lokasiPengambilan !== 'rumah_kolektor') {
            // D23: pengambilan di kantor dibayar admin → tidak pernah langsung.
            return 'lokasi_kantor';
        }

        if (bccomp(self::bc($nominalDiminta), self::bc($batas), 2) > 0) {
            return 'melebihi_batas';
        }

        $batasHarian = AdminSetting::get('offline_penarikan_batas_harian', '0');
        if (is_numeric($batasHarian) && (float) $batasHarian > 0) {
            $total = bcadd(static::totalLangsungHariIni($kolektor->id), self::bc($nominalDiminta), 2);

            if (bccomp($total, self::bc($batasHarian), 2) > 0) {
                return 'melebihi_batas_harian';
            }
        }

        if (! $kolektor->izin_penarikan_langsung) {
            return 'kolektor_dibekukan';
        }

        if (! $adaBukti) {
            return 'tanpa_bukti';
        }

        $kas = KasKolektorHitung::kasDiTangan($kolektor->id);
        if (! KasKolektorHitung::bolehKasMinus()
            && bccomp($kas, self::bc($nominalDiterima), 2) < 0) {
            return 'kas_kurang';
        }

        if (ApprovalPenarikan::melewatiAmbangDuaApprover((float) $nominalDiminta)) {
            return 'melewati_ambang';
        }

        return null;
    }

    /**
     * Total penarikan langsung kolektor hari ini (zona Asia/Jakarta, D23).
     *
     * @return numeric-string
     */
    public static function totalLangsungHariIni(int $kolektorId): string
    {
        $total = TransaksiPenarikan::query()
            ->where('metode_verifikasi', 'tanpa_verifikasi_offline')
            ->where('dibayar_oleh', $kolektorId)
            ->whereNotNull('waktu_pencairan')
            ->whereBetween('waktu_pencairan', [now()->startOfDay(), now()->endOfDay()])
            ->sum('nominal_diminta');

        return self::bc($total);
    }

    /**
     * Terjemahan kode alasan menjadi pesan yang dimengerti pengguna.
     *
     * @var array<string, string>
     */
    public const ALASAN_MANUSIA = [
        'digital' => 'nasabah tidak dalam mode offline',
        'setting_nonaktif' => 'pengaturan penarikan langsung sedang nonaktif',
        'batas_nol' => 'batas penarikan langsung belum diatur (0 = nonaktif)',
        'lokasi_kantor' => 'pengambilan di kantor harus lewat persetujuan admin',
        'melebihi_batas' => 'nominal melebihi batas penarikan langsung',
        'melebihi_batas_harian' => 'melebihi batas penarikan langsung hari ini',
        'kolektor_dibekukan' => 'izin penarikan langsung Anda sedang dibekukan',
        'tanpa_bukti' => 'bukti penarikan belum dilampirkan',
        'kas_kurang' => 'kas di tangan tidak mencukupi',
        'melewati_ambang' => 'melewati ambang persetujuan ganda admin',
        'tidak_pending' => 'status penarikan berubah, silakan muat ulang',
    ];

    /**
     * Pesan ramah untuk sebuah kode alasan.
     */
    public static function alasanManusia(string $kode): string
    {
        return self::ALASAN_MANUSIA[$kode] ?? $kode;
    }

    /**
     * Normalisasi nilai ke string bcmath skala 2.
     *
     * @return numeric-string
     */
    private static function bc(mixed $nilai): string
    {
        $teks = (string) $nilai;

        return is_numeric($teks) ? bcadd($teks, '0', 2) : '0.00';
    }

    /**
     * Notifikasi in-app ke seluruh admin pasca-commit (bukan ke nasabah offline),
     * kini dengan tautan ke antrian tinjauan penarikan offline (D25).
     */
    private function notifikasiAdmin(TransaksiPenarikan $penarikan): void
    {
        try {
            $tautan = route('admin.tinjauan-penarikan-offline');

            foreach (User::where('role', 'admin')->pluck('id') as $adminId) {
                ActivityLogger::notify(
                    (int) $adminId,
                    'Penarikan Offline Selesai',
                    'Penarikan Rp '.number_format((float) $penarikan->nominal_diminta, 0, ',', '.')
                        .' tanpa verifikasi PIN telah langsung diselesaikan kolektor. '
                        ."Perlu ditinjau: {$tautan}",
                );
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
