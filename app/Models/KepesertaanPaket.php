<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'tanggal_serah_terima' => 'date',
        ];
    }

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nasabah_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(ProdukTabungan::class, 'produk_id');
    }

    /**
     * Hitung ulang tunggakan berbasis hari terbayar.
     *
     * Rumus: hari_terbayar = floor(total_aktual / harga_per_hari)
     * tunggakan = max(0, hari_berjalan - hari_terbayar)
     *
     * Otomatis menangani: bolong hari, bayar susulan/dobel, DAN bayar lebih
     * (nabung di muka untuk hari-hari berikutnya).
     */
    public function hitungUlangKepesertaan(bool $simpan = true): array
    {
        $produk = $this->produk;
        if (! $produk || ! $produk->harga_per_hari || $produk->harga_per_hari <= 0) {
            return ['tunggakan_hari' => 0, 'tunggakan_rupiah' => 0];
        }

        $hariBerjalan = Carbon::parse($this->tanggal_mulai_ikut)->diffInDays(now()) + 1;

        $totalAktual = TransaksiSetoran::where('nasabah_id', $this->nasabah_id)
            ->where('produk_id', $this->produk_id)
            ->where('status', '!=', 'dibatalkan')
            ->sum('nominal');

        $hariTerbayar = intdiv((int) $totalAktual, (int) $produk->harga_per_hari);
        $tunggakanHari = max(0, $hariBerjalan - $hariTerbayar);
        $seharusnyaSampaiHariIni = $hariBerjalan * $produk->harga_per_hari;

        $statusAlert = 'normal';
        if ($tunggakanHari > 0) {
            $statusAlert = 'peringatan';
            if ($produk->batas_toleransi_tunggakan_hari && $tunggakanHari >= $produk->batas_toleransi_tunggakan_hari) {
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
            'tunggakan_rupiah' => $tunggakanHari * $produk->harga_per_hari,
        ];
    }
}
