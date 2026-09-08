<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProdukTabungan extends Model
{
    use HasFactory;

    protected $table = 'produk_tabungan';

    protected $fillable = [
        'nama',
        'tipe',
        'persen_komisi',
        'minimal_setor',
        'harga_per_hari',
        'isi_paket',
        'periode_mulai',
        'periode_selesai',
        'tanggal_boleh_cair',
        'batas_toleransi_tunggakan_hari',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'persen_komisi' => 'decimal:2',
            'minimal_setor' => 'decimal:2',
            'harga_per_hari' => 'decimal:2',
            'isi_paket' => 'array',
            'periode_mulai' => 'date',
            'periode_selesai' => 'date',
            'tanggal_boleh_cair' => 'date',
        ];
    }

    public function isPaket(): bool
    {
        return $this->tipe === 'paket';
    }

    public function isBebas(): bool
    {
        return $this->tipe === 'bebas';
    }

    public function transaksiSetorans(): HasMany
    {
        return $this->hasMany(TransaksiSetoran::class, 'produk_id');
    }

    public function transaksiPenarikans(): HasMany
    {
        return $this->hasMany(TransaksiPenarikan::class, 'produk_id');
    }

    public function saldoProduks(): HasMany
    {
        return $this->hasMany(SaldoProduk::class, 'produk_id');
    }

    public function kepesertaanPakets(): HasMany
    {
        return $this->hasMany(KepesertaanPaket::class, 'produk_id');
    }
}
