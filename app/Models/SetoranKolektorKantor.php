<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SetoranKolektorKantor extends Model
{
    use HasFactory;

    protected $table = 'setoran_kolektor_kantor';

    protected $fillable = [
        'kolektor_id',
        'tanggal_setor',
        'total_seharusnya',
        'total_diterima',
        'selisih',
        'keterangan_selisih',
        'diterima_oleh',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_seharusnya' => 'decimal:2',
            'total_diterima' => 'decimal:2',
            'selisih' => 'decimal:2',
            'tanggal_setor' => 'date',
        ];
    }

    public function kolektor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kolektor_id');
    }

    public function diterimaOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterima_oleh');
    }

    public function transaksiSetorans(): HasMany
    {
        return $this->hasMany(TransaksiSetoran::class, 'setoran_kolektor_id');
    }
}
