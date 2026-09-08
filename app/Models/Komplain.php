<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Komplain extends Model
{
    use HasFactory;

    protected $table = 'komplain';

    protected $fillable = [
        'nasabah_id',
        'kategori',
        'transaksi_terkait_id',
        'deskripsi',
        'status',
        'ditangani_oleh',
        'catatan_penyelesaian',
        'tanggal_dibuat',
        'tanggal_selesai',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_dibuat' => 'datetime',
            'tanggal_selesai' => 'datetime',
        ];
    }

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nasabah_id');
    }

    public function ditanganiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditangani_oleh');
    }

    public function transaksiTerkait(): BelongsTo
    {
        return $this->belongsTo(TransaksiSetoran::class, 'transaksi_terkait_id');
    }
}
