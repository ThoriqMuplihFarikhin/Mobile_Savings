<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalKunjungan extends Model
{
    use HasFactory;

    protected $table = 'jadwal_kunjungan';

    protected $fillable = [
        'kolektor_id',
        'nasabah_id',
        'tanggal_jadwal',
        'status_kunjungan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_jadwal' => 'date',
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
