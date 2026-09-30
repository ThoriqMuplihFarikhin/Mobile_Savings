<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiSetoran extends Model
{
    use HasFactory;

    protected $table = 'transaksi_setoran';

    protected $fillable = [
        'nasabah_id',
        'produk_id',
        'nominal',
        'tanggal_transaksi',
        'tanggal_input_sistem',
        'input_by',
        'sumber_input',
        'status',
        'nominal_asli',
        'dikoreksi_oleh',
        'alasan_koreksi',
        'sudah_disetor_ke_kantor',
        'setoran_kolektor_id',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'nominal_asli' => 'decimal:2',
            'tanggal_transaksi' => 'date',
            'tanggal_input_sistem' => 'datetime',
            'sudah_disetor_ke_kantor' => 'boolean',
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

    public function inputBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    public function dikoreksiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikoreksi_oleh');
    }

    public function setoranKolektor(): BelongsTo
    {
        return $this->belongsTo(SetoranKolektorKantor::class, 'setoran_kolektor_id');
    }

    /**
     * Setoran yang masih ikut menggerakkan uang: tercatat maupun dikoreksi.
     * Setoran dibatalkan dikeluarkan dari seluruh perhitungan kas.
     */
    public function scopeMasihAktif(Builder $query): Builder
    {
        return $query->whereIn('status', ['tercatat', 'dikoreksi']);
    }

    public function scopeBelumDisetor(Builder $query): Builder
    {
        return $query->masihAktif()->where('sudah_disetor_ke_kantor', false);
    }
}
