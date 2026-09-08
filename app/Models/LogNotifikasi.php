<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogNotifikasi extends Model
{
    use HasFactory;

    protected $table = 'log_notifikasi';

    protected $fillable = [
        'nasabah_id',
        'judul',
        'pesan',
        'jenis_notifikasi',
        'channel',
        'status_kirim',
        'is_read',
        'waktu_kirim',
    ];

    protected function casts(): array
    {
        return [
            'waktu_kirim' => 'datetime',
            'is_read' => 'boolean',
        ];
    }

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nasabah_id');
    }
}
