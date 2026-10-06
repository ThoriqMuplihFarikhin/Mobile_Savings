<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class TransaksiPenarikan extends Model
{
    use HasFactory;

    protected $table = 'transaksi_penarikan';

    protected $fillable = [
        'nasabah_id',
        'produk_id',
        'nominal_diminta',
        'persen_komisi_terpakai',
        'nominal_komisi',
        'nominal_diterima',
        'jalur_pengajuan',
        'lokasi_pengambilan',
        'status',
        'disetujui_oleh',
        'disetujui_oleh_2',
        'waktu_approval',
        'waktu_pencairan',
        'diverifikasi_oleh',
        'metode_verifikasi',
        'percobaan_verifikasi_gagal',
        'terkunci_hingga',
        'dibayar_oleh',
        'mempengaruhi_kas',
        'setoran_kolektor_id',
        'dibatalkan_oleh',
        'alasan_batal',
        'waktu_dibatalkan',
    ];

    protected function casts(): array
    {
        return [
            'nominal_diminta' => 'decimal:2',
            'persen_komisi_terpakai' => 'decimal:2',
            'nominal_komisi' => 'decimal:2',
            'nominal_diterima' => 'decimal:2',
            'waktu_approval' => 'datetime',
            'waktu_pencairan' => 'datetime',
            'terkunci_hingga' => 'datetime',
            'mempengaruhi_kas' => 'boolean',
            'waktu_dibatalkan' => 'datetime',
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

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function disetujuiOleh2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh_2');
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function dibayarOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibayar_oleh');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function dibatalkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibatalkan_oleh');
    }

    /**
     * @return BelongsTo<SetoranKolektorKantor, $this>
     */
    public function setoranKolektor(): BelongsTo
    {
        return $this->belongsTo(SetoranKolektorKantor::class, 'setoran_kolektor_id');
    }

    /**
     * Backfill idempoten (D13): penarikan tunai historis yang selesai di rumah
     * kolektor dicatat pembayarnya tetapi sengaja tidak menggerakkan kas —
     * kolektor sudah menyetor penuh pada periode lalu, sehingga menghitung
     * mundur akan membuat kas semua kolektor mendadak minus.
     *
     * Idempoten lewat guard `dibayar_oleh IS NULL` dan hanya baris yang bisa
     * ditatribusikan (`diverifikasi_oleh` terisi); pemanggilan ulang = 0 baris.
     */
    public static function backfillHistorisKas(): int
    {
        return static::query()
            ->where('status', 'selesai')
            ->where('lokasi_pengambilan', 'rumah_kolektor')
            ->whereNull('dibayar_oleh')
            ->whereNotNull('diverifikasi_oleh')
            ->update([
                'dibayar_oleh' => DB::raw('diverifikasi_oleh'),
                'mempengaruhi_kas' => false,
            ]);
    }

    public function sedangTerkunci(): bool
    {
        return $this->terkunci_hingga !== null && now()->lt($this->terkunci_hingga);
    }
}
