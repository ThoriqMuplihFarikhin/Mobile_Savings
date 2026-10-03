<?php

namespace App\Actions\Penarikan;

use App\Actions\Tabungan\HitungTunggakanAction;
use App\Models\AdminSetting;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AjukanPenarikanAction
{
    public function execute(User $nasabah, ProdukTabungan $produk, float|string $nominal, string $jalur, string $lokasiPengambilan = 'kantor'): TransaksiPenarikan
    {
        $nominalUang = $this->normalisasiNominal($nominal);

        return DB::transaction(function () use ($nasabah, $produk, $nominalUang, $jalur, $lokasiPengambilan) {
            $saldo = SaldoProduk::where('nasabah_id', $nasabah->id)
                ->where('produk_id', $produk->id)
                ->lockForUpdate()
                ->first();

            $totalPending = TransaksiPenarikan::where('nasabah_id', $nasabah->id)
                ->where('produk_id', $produk->id)
                ->where('status', 'pending')
                ->sum('nominal_diminta');

            $tersedia = bcsub((string) ($saldo->saldo ?? 0), (string) $totalPending, 2);

            if (bccomp($nominalUang, $tersedia, 2) > 0) {
                throw new \Exception('Saldo tidak mencukupi! Sisa saldo tersedia: Rp '.number_format((float) $tersedia, 0, ',', '.'));
            }

            $this->assertAturanMinimal($nominalUang, $tersedia);

            if ($produk->isPaket()) {
                if ($produk->tanggal_boleh_cair === null) {
                    throw new \InvalidArgumentException('Tanggal pencairan paket belum ditetapkan. Hubungi admin.');
                }

                if (now()->lt($produk->tanggal_boleh_cair)) {
                    throw new \Exception('Penarikan paket belum bisa dilakukan sebelum tanggal '.$produk->tanggal_boleh_cair->translatedFormat('d M Y'));
                }

                $statusTunggakan = (new HitungTunggakanAction)->execute($nasabah->id, $produk->id);

                if ($statusTunggakan && $statusTunggakan['tunggakan'] > 0) {
                    throw new \Exception('Penarikan paket belum bisa dilakukan karena masih ada tunggakan sebesar Rp '.number_format($statusTunggakan['tunggakan'], 0, ',', '.').'. Lunasi tunggakan terlebih dahulu atau hubungi admin.');
                }
            }

            $persenKomisi = $produk->persen_komisi ?? 0;
            $komisi = static::hitungKomisi($nominalUang, $persenKomisi);

            return TransaksiPenarikan::create([
                'nasabah_id' => $nasabah->id,
                'produk_id' => $produk->id,
                'nominal_diminta' => $nominalUang,
                'persen_komisi_terpakai' => $persenKomisi,
                'nominal_komisi' => $komisi['komisi'],
                'nominal_diterima' => $komisi['diterima'],
                'jalur_pengajuan' => $jalur,
                'lokasi_pengambilan' => $lokasiPengambilan,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Hitung komisi dan nominal diterima dengan presisi bcmath (scale 2, setengah ke atas).
     * Jumlah nominal_diterima + nominal_komisi dijamin sama persis dengan nominal.
     *
     * @param  numeric-string  $nominal
     * @return array{komisi: numeric-string, diterima: numeric-string}
     */
    public static function hitungKomisi(string $nominal, float|string $persenKomisi): array
    {
        if (bccomp($nominal, '0', 2) <= 0 || (float) $persenKomisi <= 0) {
            return ['komisi' => '0.00', 'diterima' => $nominal];
        }

        $kasar = bcmul($nominal, bcdiv((string) $persenKomisi, '100', 10), 10);
        $komisi = bcdiv(bcadd($kasar, '0.005', 10), '1', 2);

        return ['komisi' => $komisi, 'diterima' => bcsub($nominal, $komisi, 2)];
    }

    /**
     * Terapkan nominal minimal dari AdminSetting `penarikan_minimal` (default 10000),
     * dengan pengecualian tarik habis seluruh saldo tersedia.
     *
     * @param  numeric-string  $nominal
     * @param  numeric-string  $tersedia
     */
    private function assertAturanMinimal(string $nominal, string $tersedia): void
    {
        $minimal = AdminSetting::get('penarikan_minimal', '10000');

        if (! is_numeric($minimal)) {
            $minimal = '10000';
        }

        if (bccomp($nominal, $minimal, 2) >= 0) {
            return;
        }

        if (bccomp($nominal, $tersedia, 2) === 0) {
            return;
        }

        throw new \Exception('Penarikan minimal Rp '.number_format((float) $minimal, 0, ',', '.').'. Untuk nominal di bawah minimal, tarik habis seluruh saldo tersedia.');
    }

    /**
     * Normalisasi nominal menjadi string desimal dua tempat.
     * Menolak nilai tidak valid, nol, negatif, atau lebih dari dua desimal.
     *
     *
     * @return numeric-string
     *
     * @throws \Exception bila nominal tidak valid
     */
    private function normalisasiNominal(float|string $nominal): string
    {
        $teks = is_float($nominal)
            ? rtrim(rtrim(sprintf('%.10F', $nominal), '0'), '.')
            : trim($nominal);

        if (! is_numeric($teks) || ! preg_match('/^\d{1,13}(\.\d{1,2})?$/', $teks)) {
            throw new \Exception('Nominal penarikan tidak valid. Gunakan angka dengan maksimal 2 desimal.');
        }

        $terbulatkan = bcadd($teks, '0', 2);

        if (bccomp($terbulatkan, '0', 2) <= 0) {
            throw new \Exception('Nominal penarikan harus lebih dari 0.');
        }

        return $terbulatkan;
    }
}
