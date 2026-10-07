<?php

namespace App\Actions\Paket;

use App\Helpers\ActivityLogger;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DaftarkanKePaketAction
{
    /**
     * Pendaftaran dibantu admin/kolektor (nasabah offline atau dibantu):
     * tanpa PIN, wajib centang persetujuan + catatan, `via` mengikuti peran
     * pelaku (D16).
     *
     * @throws DomainException saat bukan admin/kolektor, bukan binaan, sudah ikut, atau paket tutup
     */
    public function execute(User $pelaku, User $nasabah, ProdukTabungan $produk, string $catatan): KepesertaanPaket
    {
        $via = match ($pelaku->role) {
            'admin' => 'admin',
            'kolektor' => 'kolektor',
            default => null,
        };

        if ($via === null) {
            throw new DomainException('Hanya admin atau kolektor yang dapat mendaftarkan nasabah.');
        }

        if ($via === 'kolektor' && ! KolektorNasabah::where('kolektor_id', $pelaku->id)
            ->where('nasabah_id', $nasabah->id)
            ->where('status', 'aktif')
            ->exists()) {
            throw new DomainException('Nasabah ini bukan binaan Anda.');
        }

        $kepesertaan = DB::transaction(function () use ($nasabah, $produk, $pelaku, $via, $catatan) {
            // Kunci baris nasabah agar dua pendaftaran bersamaan terserialisasi.
            User::whereKey($nasabah->id)->lockForUpdate()->first();

            $sudahIkut = KepesertaanPaket::where('nasabah_id', $nasabah->id)
                ->where('produk_id', $produk->id)
                ->whereNull('keputusan_akhir')
                ->exists();

            if ($sudahIkut) {
                throw new DomainException('Anda sudah mengikuti paket ini.');
            }

            $hariIni = now()->toDateString();
            $terbuka = ProdukTabungan::whereKey($produk->id)
                ->where('status', 'aktif')
                ->where(fn ($q) => $q->whereNull('periode_selesai')->orWhere('periode_selesai', '>=', $hariIni))
                ->where(fn ($q) => $q->whereNull('batas_daftar_hingga')->orWhere('batas_daftar_hingga', '>=', $hariIni))
                ->exists();

            if (! $terbuka) {
                throw new DomainException('Paket ini tidak lagi terbuka untuk pendaftaran.');
            }

            $kepesertaan = KepesertaanPaket::create([
                'nasabah_id' => $nasabah->id,
                'produk_id' => $produk->id,
                'tanggal_mulai_ikut' => $hariIni,
                'total_seharusnya_terkumpul' => 0,
                'total_aktual_terkumpul' => 0,
                'tunggakan' => 0,
                'status_alert' => 'normal',
                'komitmen_disetujui_pada' => now(),
                'komitmen_via' => $via,
                'komitmen_dicatat_oleh' => $pelaku->id,
                'komitmen_teks' => IkutiPaketAction::teksKomitmen(),
                'komitmen_catatan' => $catatan,
            ]);

            SaldoProduk::firstOrCreate(
                ['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id],
                ['saldo' => 0]
            );

            return $kepesertaan;
        });

        try {
            ActivityLogger::log('daftarkan_paket', 'kepesertaan_paket', (int) $kepesertaan->id, [
                'nasabah_id' => $nasabah->id,
                'produk_id' => $produk->id,
                'pelaku_id' => $pelaku->id,
                'via' => $via,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return $kepesertaan;
    }

    /**
     * Paket yang masih terbuka untuk kepesertaan nasabah (dropdown pendaftaran).
     *
     * @return Collection<int, ProdukTabungan>
     */
    public static function paketTerbukaUntuk(int $nasabahId): Collection
    {
        $hariIni = now()->toDateString();

        $sudahDiikuti = KepesertaanPaket::where('nasabah_id', $nasabahId)
            ->whereNull('keputusan_akhir')
            ->pluck('produk_id');

        return ProdukTabungan::where('tipe', 'paket')
            ->where('status', 'aktif')
            ->where(fn ($q) => $q->whereNull('periode_selesai')->orWhere('periode_selesai', '>=', $hariIni))
            ->where(fn ($q) => $q->whereNull('batas_daftar_hingga')->orWhere('batas_daftar_hingga', '>=', $hariIni))
            ->whereNotIn('id', $sudahDiikuti)
            ->orderBy('nama')
            ->get();
    }
}
