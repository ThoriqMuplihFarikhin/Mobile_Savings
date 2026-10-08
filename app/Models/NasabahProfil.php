<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NasabahProfil extends Model
{
    use HasFactory;

    protected $table = 'nasabah_profil';

    protected $fillable = [
        'user_id',
        'nama',
        'alamat',
        'catatan_offline',
        'tanggal_lahir',
        'jenis_kelamin',
        'pekerjaan',
        'didaftarkan_oleh',
        'status_pendaftaran',
        'diverifikasi_oleh',
        'tanggal_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_verifikasi' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function didaftarkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'didaftarkan_oleh');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
