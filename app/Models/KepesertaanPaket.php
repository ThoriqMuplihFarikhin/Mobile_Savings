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
     * Hari ke berapa kepesertaan ini berjalan hari ini, dalam bilangan bulat.
     *
     * - hari pertama ikut paket dihitung hari ke-1,
     * - tanggal mulai ikut di masa depan dihitung 0,
     * - tidak pernah melebihi total hari periode paket.
     */
    protected function hitungHariBerjalan(ProdukTabungan $produk): int
    {
        $mulai = Carbon::parse($this->tanggal_mulai_ikut)->startOfDay();
        $hariBerjalan = max(0, (int) $mulai->diffInDays(now()->startOfDay()) + 1);

        $totalHariPaket = $produk->totalHariPaket();
        if ($totalHariPaket !== null) {
            $hariBerjalan = min($hariBerjalan, $totalHariPaket);
        }

        return $hariBerjalan;
    }
}
