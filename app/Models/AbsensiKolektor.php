<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiKolektor extends Model
{
    use HasFactory;

    protected $table = 'absensi_kolektor';

    protected $fillable = [
        'kolektor_id',
        'tanggal',
        'waktu_masuk',
        'latitude',
        'longitude',
        'foto_selfie_path',
        'tanda_tangan_path',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function kolektor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kolektor_id');
    }
}
