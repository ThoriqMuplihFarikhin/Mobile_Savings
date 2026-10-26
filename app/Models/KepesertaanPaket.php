<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KepesertaanPaket extends Model
{
    use HasFactory;

    /** Pesan pencairan tunggal (D17): identik di semua titik pengecekan. */
    public const ALASAN_BELUM_KOMITMEN = 'Komitmen paket belum disetujui. Hubungi admin.';

    public const ALASAN_SUDAH_DISERAHKAN = 'Paket ini sudah diserahkan dan tidak dapat dicairkan lagi.';

    public const ALASAN_TUNGGAKAN = 'Lunasi tunggakan sebelum mencairkan paket.';

    public const ALASAN_BELUM_WAKTU = 'Paket ini belum boleh cair pada tanggal ini.';

    public const ALASAN_TANGGAL_KOSONG = 'Tanggal pencairan paket belum ditetapkan. Hubungi admin.';

    protected $table = 'kepesertaan_paket';

    protected $fillable = [
        'nasabah_id',
        'produk_id',
        'tanggal_mulai_ikut',
        'total_seharusnya_terkumpul',
        'total_aktual_terkumpul',
        'tunggakan',
        'status_alert',
        'catatan_admin',
        'ditunda_hingga',
        'komitmen_disetujui_pada',
        'komitmen_via',
        'komitmen_dicatat_oleh',
        'komitmen_teks',
        'komitmen_catatan',
        'keputusan_akhir',
        'metode_pengambilan',
        'status_serah_terima',
        'diterima_oleh',
        'tanggal_serah_terima',
        'bukti_foto_url',
    ];

    protected function casts(): array
    {
        return [
            'total_seharusnya_terkumpul' => 'decimal:2',
            'total_aktual_terkumpul' => 'decimal:2',
            'tunggakan' => 'decimal:2',
            'tanggal_mulai_ikut' => 'date',
            'ditunda_hingga' => 'date',
            'tanggal_serah_terima' => 'date',
            'komitmen_disetujui_pada' => 'datetime',
        ];
    }

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nasabah_id');
    }

    /**
     * @return BelongsTo<ProdukTabungan, $this>
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(ProdukTabungan::class, 'produk_id');
    }

    /**
     * @return HasMany<TransaksiSetoran, $this>
     */
    public function setoran(): HasMany
    {
        return $this->hasMany(TransaksiSetoran::class, 'kepesertaan_id');
    }

    /**
     * Hitung ulang tunggakan berbasis hari terbayar.
     *
     * Rumus: hari_terbayar = floor(total_aktual / harga_per_hari)
     * tunggakan = max(0, hari_berjalan - hari_terbayar)
     *
     * Otomatis menangani: bolong hari, bayar susulan/dobel, DAN bayar lebih
     * (nabung di muka untuk hari-hari berikutnya).
     *
     * Kepesertaan yang sudah diserahkan ke nasabah keluar dari hitungan tunggakan.
     */
    public function hitungUlangKepesertaan(bool $simpan = true): array
    {
        if ($this->status_serah_terima === 'sudah_diterima') {
            if ($simpan) {
                $this->update(['tunggakan' => 0, 'status_alert' => 'normal']);
            }

            return ['tunggakan_hari' => 0, 'tunggakan_rupiah' => 0];
        }

        $produk = $this->produk;
        if (! $produk || ! $produk->harga_per_hari || $produk->harga_per_hari <= 0) {
            return ['tunggakan_hari' => 0, 'tunggakan_rupiah' => 0];
        }

        $hargaPerHari = (float) $produk->harga_per_hari;
        $hariBerjalan = $this->hitungHariBerjalan($produk);

        $totalAktual = $this->setoran()
            ->where('status', '!=', 'dibatalkan')
            ->sum('nominal');

        $hariTerbayar = (int) floor(((float) $totalAktual) / $hargaPerHari);
        $tunggakanHari = max(0, $hariBerjalan - $hariTerbayar);
        $seharusnyaSampaiHariIni = round($hariBerjalan * $hargaPerHari, 2);

        $statusAlert = 'normal';
        if ($tunggakanHari > 0) {
            $statusAlert = 'peringatan';
            $ditundaAktif = $this->ditunda_hingga !== null
                && ! Carbon::parse($this->ditunda_hingga)->lt(today());

            if (! $ditundaAktif && $produk->batas_toleransi_tunggakan_hari && $tunggakanHari >= $produk->batas_toleransi_tunggakan_hari) {
                $statusAlert = 'perlu_review';
            }
        }

        if ($simpan) {
            $this->update([
                'total_seharusnya_terkumpul' => $seharusnyaSampaiHariIni,
                'total_aktual_terkumpul' => $totalAktual,
                'tunggakan' => $tunggakanHari,
                'status_alert' => $statusAlert,
            ]);
        }

        return [
            'tunggakan_hari' => $tunggakanHari,
            'tunggakan_rupiah' => round($tunggakanHari * $hargaPerHari, 2),
        ];
    }

    /**
     * Total hari kepesertaan ini: dari `tanggal_mulai_ikut` s/d
     * `periode_selesai` paket (gabung terlambat punya komitmen lebih
     * pendek daripada periode penuh). Null bila paket tanpa periode selesai.
     */
    public function totalHariKepesertaan(): ?int
    {
        $produk = $this->produk;
        if (! $produk || ! $produk->periode_selesai) {
            return null;
        }

        $mulai = Carbon::parse($this->tanggal_mulai_ikut)->startOfDay();
        $selesai = Carbon::parse($produk->periode_selesai)->startOfDay();

        return max(0, (int) $mulai->diffInDays($selesai) + 1);
    }

    /**
     * Target akhir kepesertaan = harga_per_hari x totalHariKepesertaan.
     */
    public function targetKepesertaan(): ?float
    {
        $produk = $this->produk;
        $totalHari = $this->totalHariKepesertaan();

        if (! $produk || ! $produk->harga_per_hari || $totalHari === null || $totalHari <= 0) {
            return null;
        }

        return round($totalHari * (float) $produk->harga_per_hari, 2);
    }

    /**
     * Gerbang pencairan tunggal (D17): kepesertaan boleh mencair bila komitmen
     * ada, belum diserahterimakan, tunggakan = 0, dan (tanggal boleh cair sudah
     * tiba ATAU toggle `boleh_cair_saat_target` aktif dengan target tercapai).
     *
     * Selalu memanggil hitungUlangKepesertaan() lebih dahulu agar keputusan
     * memakai data tunggakan terbaru.
     *
     * @return array{boleh: bool, alasan: ?string, kode: string}
     */
    public function statusPencairan(bool $simpan = true): array
    {
        $hasil = $this->hitungUlangKepesertaan($simpan);
        $tunggakanHari = (int) $hasil['tunggakan_hari'];

        if ($this->komitmen_disetujui_pada === null) {
            return $this->gerbangDitolak('belum_komitmen', self::ALASAN_BELUM_KOMITMEN);
        }

        if ($this->status_serah_terima === 'sudah_diterima') {
            return $this->gerbangDitolak('sudah_diserahkan', self::ALASAN_SUDAH_DISERAHKAN);
        }

        $produk = $this->produk;
        if ($produk === null) {
            return $this->gerbangDitolak('belum_waktunya', self::ALASAN_TANGGAL_KOSONG);
        }

        $tanggalCair = $produk->tanggal_boleh_cair;
        $bolehWaktu = $tanggalCair !== null && ! Carbon::parse($tanggalCair)->startOfDay()->isFuture();

        if (! $bolehWaktu && $produk->boleh_cair_saat_target && $this->targetTercapai()) {
            $bolehWaktu = true;
        }

        if (! $bolehWaktu) {
            return $this->gerbangDitolak(
                'belum_waktunya',
                $tanggalCair === null ? self::ALASAN_TANGGAL_KOSONG : self::ALASAN_BELUM_WAKTU,
            );
        }

        if ($tunggakanHari > 0) {
            return $this->gerbangDitolak('ada_tunggakan', self::ALASAN_TUNGGAKAN);
        }

        return ['boleh' => true, 'alasan' => null, 'kode' => 'ok'];
    }

    /**
     * @return array{boleh: bool, alasan: ?string, kode: string}
     */
    private function gerbangDitolak(string $kode, string $alasan): array
    {
        return ['boleh' => false, 'alasan' => $alasan, 'kode' => $kode];
    }

    /**
     * Total terkumpul (dari setoran aktif) sudah mencapai target kepesertaan.
     */
    private function targetTercapai(): bool
    {
        $target = $this->targetKepesertaan();
        if ($target === null) {
            return false;
        }

        $terkumpul = (float) $this->setoran()
            ->where('status', '!=', 'dibatalkan')
            ->sum('nominal');

        return bccomp(number_format($terkumpul, 2, '.', ''), number_format($target, 2, '.', ''), 2) >= 0;
    }

    /**
     * Hari ke berapa kepesertaan ini berjalan hari ini, dalam bilangan bulat.
     *
     * - hari pertama ikut paket dihitung hari ke-1,
     * - tanggal mulai ikut di masa depan dihitung 0,
     * - tidak pernah melebihi totalHariKepesertaan (gabung terlambat
     *   dibatasi sampai periode selesai, bukan seluruh periode paket).
     */
    protected function hitungHariBerjalan(ProdukTabungan $produk): int
    {
        $mulai = Carbon::parse($this->tanggal_mulai_ikut)->startOfDay();
        $hariBerjalan = max(0, (int) $mulai->diffInDays(now()->startOfDay()) + 1);

        $totalHari = $this->totalHariKepesertaan() ?? $produk->totalHariPaket();
        if ($totalHari !== null) {
            $hariBerjalan = min($hariBerjalan, $totalHari);
        }

        return $hariBerjalan;
    }
}
