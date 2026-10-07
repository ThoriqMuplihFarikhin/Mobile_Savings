<?php

namespace App\Actions\Paket;

use App\Helpers\ActivityLogger;
use App\Models\AdminSetting;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class IkutiPaketAction
{
    /**
     * Teks komitmen bila admin belum menyetel `teks_komitmen_paket`.
     */
    public const TEKS_KOMITMEN_DEFAULT = 'Saya menyatakan mengikuti paket tabungan ini secara sukarela, '
        .'memahami aturan setoran harian, ketentuan tunggakan, dan waktu pencairan paket, '
        .'serta bersedia mematuhi seluruh ketentuan yang berlaku.';

    private const MAX_PERCOBAAN = 5;

    private const RATELIMIT_DECAY_DETIK = 900;

    /**
     * Pendaftaran mandiri nasabah ke sebuah paket: konfirmasi PIN ber-rate-limit
     * lalu pembuatan kepesertaan + saldo produk dalam satu transaksi (D16).
     *
     * @throws DomainException saat PIN salah, paket tak lagi terbuka, atau sudah ikut
     */
    public function execute(User $nasabah, ProdukTabungan $produk, string $pin): KepesertaanPaket
    {
        $key = 'ikut-paket:nasabah:'.$nasabah->id;

        if (RateLimiter::tooManyAttempts($key, self::MAX_PERCOBAAN)) {
            throw new DomainException('Terlalu banyak percobaan PIN. Coba lagi nanti atau hubungi admin.');
        }

        if ($nasabah->harus_ganti_pin) {
            throw new DomainException('Nasabah belum mengganti PIN awal. Silakan ganti PIN terlebih dahulu di halaman Pengaturan.');
        }

        if (! Hash::check($pin, $nasabah->pin_hash)) {
            RateLimiter::hit($key, self::RATELIMIT_DECAY_DETIK);

            $sisa = self::MAX_PERCOBAAN - RateLimiter::attempts($key);

            throw new DomainException("PIN salah. Sisa percobaan: {$sisa}.");
        }

        RateLimiter::clear($key);

        $kepesertaan = DB::transaction(function () use ($nasabah, $produk) {
            // Kunci baris nasabah agar dua pendaftaran bersamaan terserialisasi.
            User::whereKey($nasabah->id)->lockForUpdate()->first();

            $sudahIkut = KepesertaanPaket::where('nasabah_id', $nasabah->id)
                ->where('produk_id', $produk->id)
                ->whereNull('keputusan_akhir')
                ->exists();

            if ($sudahIkut) {
                throw new DomainException('Anda sudah mengikuti paket ini.');
            }

            if (! $this->paketMasihTerbuka($produk)) {
                throw new DomainException('Paket ini tidak lagi terbuka untuk pendaftaran.');
            }

            $kepesertaan = KepesertaanPaket::create([
                'nasabah_id' => $nasabah->id,
                'produk_id' => $produk->id,
                'tanggal_mulai_ikut' => now()->toDateString(),
                'total_seharusnya_terkumpul' => 0,
                'total_aktual_terkumpul' => 0,
                'tunggakan' => 0,
                'status_alert' => 'normal',
                'komitmen_disetujui_pada' => now(),
                'komitmen_via' => 'mandiri',
                'komitmen_dicatat_oleh' => $nasabah->id,
                'komitmen_teks' => static::teksKomitmen(),
            ]);

            SaldoProduk::firstOrCreate(
                ['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id],
                ['saldo' => 0]
            );

            return $kepesertaan;
        });

        try {
            ActivityLogger::log('ikut_paket', 'kepesertaan_paket', (int) $kepesertaan->id, [
                'nasabah_id' => $nasabah->id,
                'produk_id' => $produk->id,
                'via' => 'mandiri',
            ]);

            ActivityLogger::notify(
                (int) $nasabah->id,
                'Berhasil Ikut Paket',
                'Anda resmi mengikuti paket '.$produk->nama.'. Mulai menabung sesuai aturan paket.',
                'both'
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return $kepesertaan;
    }

    /**
     * Teks komitmen yang berlaku: setelan admin, bila belum ada memakai default kode.
     */
    public static function teksKomitmen(): string
    {
        return AdminSetting::get('teks_komitmen_paket', self::TEKS_KOMITMEN_DEFAULT)
            ?? self::TEKS_KOMITMEN_DEFAULT;
    }

    private function paketMasihTerbuka(ProdukTabungan $produk): bool
    {
        $hariIni = now()->toDateString();

        return ProdukTabungan::whereKey($produk->id)
            ->where('status', 'aktif')
            ->where(fn ($q) => $q->whereNull('periode_selesai')->orWhere('periode_selesai', '>=', $hariIni))
            ->where(fn ($q) => $q->whereNull('batas_daftar_hingga')->orWhere('batas_daftar_hingga', '>=', $hariIni))
            ->exists();
    }
}
