<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Cast `array` pada `isi_paket` mengembalikan list item, bukan string JSON mentah kolom.
 *
 * @property array<int, array<string, mixed>>|null $isi_paket
 */
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
        'uang_tunai',
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
            'uang_tunai' => 'decimal:2',
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

    public function totalHariPaket(): ?int
    {
        if ($this->periode_mulai && $this->periode_selesai) {
            return max(0, (int) $this->periode_mulai->diffInDays($this->periode_selesai) + 1);
        }

        return null;
    }

    public function targetAkhir(): ?float
    {
        $totalHari = $this->totalHariPaket();
        if (! $totalHari || ! $this->harga_per_hari) {
            return null;
        }

        return $totalHari * $this->harga_per_hari;
    }
}
