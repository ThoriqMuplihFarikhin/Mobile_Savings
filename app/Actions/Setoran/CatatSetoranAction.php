<?php

namespace App\Actions\Setoran;

use App\Actions\Tabungan\HitungTunggakanAction;
use App\Helpers\ActivityLogger;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiSetoran;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CatatSetoranAction
{
    /**
     * Mencatat setoran tabungan: idempotency key, lock saldo, kepemilikan
     * kepesertaan, log aktivitas, dan notifikasi ke nasabah.
     *
     * @param  array{
     *     nasabah_id: int,
     *     produk_id: int,
     *     nominal: int|float|string,
     *     tanggal_transaksi: string,
     *     sumber_input: string,
     *     input_by: int,
     *     catatan: ?string,
     *     sudah_disetor_ke_kantor: bool,
     *     idempotency_key: string,
     * }  $data
     *
     * @throws DomainException saat key sudah terpakai atau transaksi gagal
     */
    public function execute(array $data): TransaksiSetoran
    {
        $nominal = $this->normalisasiNominal($data['nominal']);

        $cacheKey = 'setoran-submit:'.$data['input_by'].':'.$data['idempotency_key'];
        if (! Cache::add($cacheKey, true, now()->addMinutes(10))) {
            throw new DomainException('Setoran ini sudah diproses. Muat ulang halaman untuk setoran baru.');
        }

        try {
            $transaksi = DB::transaction(function () use ($data, $nominal) {
                $saldo = SaldoProduk::firstOrCreate(
                    ['nasabah_id' => $data['nasabah_id'], 'produk_id' => $data['produk_id']],
                    ['saldo' => 0]
                );
                $saldo = SaldoProduk::whereKey($saldo->id)->lockForUpdate()->first();

                $kepesertaanId = null;
                if ($this->produkPaket((int) $data['produk_id'])) {
                    $kepesertaanId = app(HitungTunggakanAction::class)
                        ->kepesertaanAktif((int) $data['nasabah_id'], (int) $data['produk_id'], buatJikaBelumAda: true)
                        ?->id;
                }

                $transaksi = TransaksiSetoran::create([
                    'nasabah_id' => $data['nasabah_id'],
                    'produk_id' => $data['produk_id'],
                    'kepesertaan_id' => $kepesertaanId,
                    'nominal' => $nominal,
                    'tanggal_transaksi' => $data['tanggal_transaksi'],
                    'tanggal_input_sistem' => now(),
                    'input_by' => $data['input_by'],
                    'sumber_input' => $data['sumber_input'],
                    'status' => 'tercatat',
                    'catatan' => $data['catatan'] ?: null,
                    'sudah_disetor_ke_kantor' => $data['sudah_disetor_ke_kantor'],
                ]);

                $saldo->increment('saldo', $nominal);

                if ($this->produkPaket((int) $data['produk_id'])) {
                    app(HitungTunggakanAction::class)
                        ->execute((int) $data['nasabah_id'], (int) $data['produk_id'], simpan: true);
                }

                return $transaksi;
            });
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);
            report($e);
            throw new DomainException('Gagal mencatat setoran. Silakan coba lagi.');
        }

        $this->catatLog($data, $transaksi, $nominal);

        return $transaksi;
    }

    /**
     * Menormalkan nominal masukan menjadi angka sebelum dipakai menggerakkan
     * saldo; string non-numerik ditolak alih-alih diam-diam menjadi nol.
     */
    private function normalisasiNominal(int|float|string $nominal): float|int
    {
        if (is_string($nominal)) {
            if (! is_numeric($nominal)) {
                throw new DomainException('Nominal setoran tidak valid.');
            }

            return (float) $nominal;
        }

        return $nominal;
    }

    /**
     * @param  array{
     *     nasabah_id: int,
     *     produk_id: int,
     *     input_by: int,
     * }  $data
     */
    private function catatLog(array $data, TransaksiSetoran $transaksi, float|int $nominal): void
    {
        try {
            ActivityLogger::log('setor', 'transaksi_setoran', $transaksi->id, [
                'nasabah_id' => $data['nasabah_id'],
                'nominal' => (int) $nominal,
                'produk_id' => $data['produk_id'],
                'input_by' => $data['input_by'],
            ]);

            ActivityLogger::notify(
                (int) $data['nasabah_id'],
                'Setoran Dicatat',
                'Setoran Rp '.number_format((float) $nominal, 0, ',', '.').' ke produk '
                    .(string) (ProdukTabungan::whereKey($data['produk_id'])->value('nama') ?? '-')
                    .' telah dicatat.',
                'both'
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function produkPaket(int $produkId): bool
    {
        return ProdukTabungan::whereKey($produkId)->value('tipe') === 'paket';
    }
}
