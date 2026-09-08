<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogHandoverKolektor extends Model
{
    use HasFactory;

    protected $table = 'log_handover_kolektor';

    protected $fillable = [
        'kolektor_lama_id',
        'kolektor_baru_id',
        'tanggal_handover',
        'jumlah_nasabah_dipindah',
        'status_kas_saat_handover',
        'diproses_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_handover' => 'date',
        ];
    }

    public function kolektorLama(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kolektor_lama_id');
    }

    public function kolektorBaru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kolektor_baru_id');
    }

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }
}
