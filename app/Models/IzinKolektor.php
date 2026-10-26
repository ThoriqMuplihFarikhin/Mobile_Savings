<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IzinKolektor extends Model
{
    protected $table = 'izin_kolektor';

    protected $fillable = [
        'kolektor_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'status',
        'diproses_oleh',
        'catatan_admin',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function kolektor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kolektor_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }
}
