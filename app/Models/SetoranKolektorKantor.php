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

    /**
     * Ringkasan kas per kolektor: akumulasi selisih rekonsiliasi (status kurang/lebih)
     * dan saldo berjalan yang belum disetor ke kantor.
     *
     * @return array<int, array{selisih_kumulatif: float, saldo_berjalan: float}>
     */
    public static function ringkasKasPerKolektor(): array
    {
        $ringkas = [];

        $rowsSelisih = static::whereIn('status', ['kurang', 'lebih'])
            ->whereNotNull('selisih')
            ->groupBy('kolektor_id')
            ->selectRaw('kolektor_id, COALESCE(SUM(selisih), 0) as total_selisih')
            ->get();

        foreach ($rowsSelisih as $row) {
            $kolektorId = (int) $row->getAttribute('kolektor_id');
            $ringkas[$kolektorId] = [
                'selisih_kumulatif' => (float) $row->getAttribute('total_selisih'),
                'saldo_berjalan' => 0.0,
            ];
        }

        $rowsBerjalan = TransaksiSetoran::belumDisetor()
            ->whereNotNull('input_by')
            ->groupBy('input_by')
            ->selectRaw('input_by, COALESCE(SUM(nominal), 0) as total_nominal')
            ->get();

        foreach ($rowsBerjalan as $row) {
            $kolektorId = (int) $row->getAttribute('input_by');
            $ringkas[$kolektorId] ??= ['selisih_kumulatif' => 0.0, 'saldo_berjalan' => 0.0];
            $ringkas[$kolektorId]['saldo_berjalan'] = (float) $row->getAttribute('total_nominal');
        }

        return $ringkas;
    }
}
