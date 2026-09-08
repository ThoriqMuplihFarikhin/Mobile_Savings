<?php

namespace App\Models;

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
}
