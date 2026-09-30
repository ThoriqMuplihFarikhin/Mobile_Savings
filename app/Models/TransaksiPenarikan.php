<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'waktu_approval',
        'waktu_pencairan',
        'diverifikasi_oleh',
        'metode_verifikasi',
        'percobaan_verifikasi_gagal',
        'terkunci_hingga',
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

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function sedangTerkunci(): bool
    {
        return $this->terkunci_hingga !== null && now()->lt($this->terkunci_hingga);
    }
}
