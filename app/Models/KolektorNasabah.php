<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KolektorNasabah extends Model
{
    use HasFactory;

    protected $table = 'kolektor_nasabah';

    protected $fillable = [
        'kolektor_id',
        'nasabah_id',
        'tanggal_mulai_ditangani',
        'tanggal_selesai_ditangani',
        'status',
        'aktif_unik',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai_ditangani' => 'date',
            'tanggal_selesai_ditangani' => 'date',
            'aktif_unik' => 'integer',
        ];
    }

    public function kolektor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kolektor_id');
    }

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nasabah_id');
    }
}
