<?php

namespace App\Actions\Penarikan;

use App\Helpers\ActivityLogger;
use App\Models\KolektorNasabah;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class VerifikasiPenarikanOfflineAction
{
    private const MAX_PERCOBAAN = 3;

    private const DURASI_KUNCI_MENIT = 15;

    private const MAX_RATELIMIT_PERCOBAAN = 5;

    private const RATELIMIT_DECAY_DETIK = 900;

    /**
     * Verifikasi bahwa nasabah benar-benar menerima uang penarikan offline,
     * dikonfirmasi langsung dengan PIN nasabah sendiri di hadapan kolektor.
     *
     * Guard dan penambahan percobaan gagal dijalankan di dalam satu transaksi
     * ber-lock; exception dilemparkan setelah commit agar percobaan gagal
     * tidak ikut ter-rollback.
     *
     * @throws \Exception
     */
    public function execute(TransaksiPenarikan $penarikan, string $pin, User $kolektor): TransaksiPenarikan
    {
        $key = 'verif-pin:nasabah:'.$penarikan->nasabah_id;

        if (RateLimiter::tooManyAttempts($key, self::MAX_RATELIMIT_PERCOBAAN)) {
            throw new \Exception('Terlalu banyak percobaan PIN. Coba lagi nanti atau hubungi admin.');
        }

        $hasil = DB::transaction(function () use ($penarikan, $pin, $kolektor, $key) {
            $p = TransaksiPenarikan::whereKey($penarikan->id)->lockForUpdate()->first();

            if (! $p) {
                return ['error' => 'Data penarikan tidak ditemukan.'];
            }

            if ($p->jalur_pengajuan !== 'offline') {
                return ['error' => 'Verifikasi langsung hanya berlaku untuk penarikan offline.'];
            }

            if ($p->status !== 'approved') {
                return ['error' => 'Penarikan ini belum disetujui admin atau sudah diproses.'];
            }

            $isTanggungJawab = KolektorNasabah::where('kolektor_id', $kolektor->id)
                ->where('nasabah_id', $p->nasabah_id)
                ->where('status', 'aktif')
                ->exists();

            if (! $isTanggungJawab) {
                return ['error' => 'Nasabah ini bukan tanggung jawab Anda.'];
            }

            if ($p->sedangTerkunci()) {
                $sisaMenit = (int) now()->diffInMinutes($p->terkunci_hingga);

                return ['error' => "Verifikasi dikunci sementara karena PIN salah berulang kali. Coba lagi dalam {$sisaMenit} menit atau hubungi admin."];
            }

            $nasabah = $p->nasabah;

            if ($nasabah instanceof User && $nasabah->harus_ganti_pin) {
                return ['error' => 'Nasabah belum mengganti PIN awal. Minta nasabah login dan mengganti PIN terlebih dahulu.'];
            }

            if (! $nasabah || ! Hash::check($pin, $nasabah->pin_hash)) {
                RateLimiter::hit($key, self::RATELIMIT_DECAY_DETIK);

                $percobaan = $p->percobaan_verifikasi_gagal + 1;
                $update = ['percobaan_verifikasi_gagal' => $percobaan];

                if ($percobaan >= self::MAX_PERCOBAAN) {
                    $update['terkunci_hingga'] = now()->addMinutes(self::DURASI_KUNCI_MENIT);
                }

                $p->update($update);

                ActivityLogger::log('verifikasi_penarikan_gagal', 'transaksi_penarikan', $p->id, [
                    'nasabah_id' => $p->nasabah_id,
                    'kolektor_id' => $kolektor->id,
                    'percobaan_ke' => $percobaan,
                ]);

                $sisa = self::MAX_PERCOBAAN - $percobaan;
                if ($sisa <= 0) {
                    return ['error' => 'PIN salah. Verifikasi dikunci selama '.self::DURASI_KUNCI_MENIT.' menit karena melebihi batas percobaan.'];
                }

                return ['error' => "PIN salah. Sisa percobaan: {$sisa}."];
            }

            RateLimiter::clear($key);

            $p->update([
                'status' => 'selesai',
                'waktu_pencairan' => now(),
                'diverifikasi_oleh' => $kolektor->id,
                'metode_verifikasi' => 'pin_nasabah',
                'percobaan_verifikasi_gagal' => 0,
                'terkunci_hingga' => null,
            ]);

            ActivityLogger::log('verifikasi_penarikan_offline', 'transaksi_penarikan', $p->id, [
                'nasabah_id' => $p->nasabah_id,
                'kolektor_id' => $kolektor->id,
                'nominal' => $p->nominal_diminta,
                'metode' => 'pin_nasabah',
            ]);

            return ['ok' => $p];
        });

        if (isset($hasil['error'])) {
            throw new \Exception($hasil['error']);
        }

        $penarikan = $hasil['ok'];

        try {
            ActivityLogger::notify(
                $penarikan->nasabah_id,
                'Penarikan Terverifikasi',
                'Penarikan Rp '.number_format($penarikan->nominal_diminta, 0, ',', '.').' telah diverifikasi dengan PIN Anda dan dinyatakan selesai.',
                'both'
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return $penarikan;
    }
}
