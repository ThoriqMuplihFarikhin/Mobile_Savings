<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Validasi & penyimpanan bukti penarikan offline nasabah (D24).
 *
 * Wajib minimal satu bukti: tanda tangan/cap jempol yang digambar di layar
 * atau foto (buku tabungan dicoret / nasabah menerima uang). Gambar divalidasi
 * ketat seperti absensi (format, ukuran, MIME ganda) dan disimpan pada disk
 * privat `local` dengan nama acak — tidak pernah di disk publik.
 */
class BuktiPenarikan
{
    /** Batas ukuran berkas bukti, sama dengan absensi: 1,5 MB. */
    public const MAKS_BYTES = 1572864;

    /** @var array<int, string> */
    public const JENIS_SAHIH = ['tanda_tangan', 'foto'];

    /**
     * Decode dan validasi base64 gambar bukti secara ketat.
     *
     * @return array{ok: true, data: string, ext: string}|array{ok: false, error: string}
     */
    public static function decode(?string $base64): array
    {
        if ($base64 === null || trim($base64) === '') {
            return ['ok' => false, 'error' => 'bukti wajib diisi.'];
        }

        if (! preg_match('#^data:image/(jpeg|png);base64,#', $base64)) {
            return ['ok' => false, 'error' => 'format harus JPEG atau PNG.'];
        }

        $payload = substr($base64, (int) strpos($base64, ',') + 1);
        $data = base64_decode($payload, true);

        if ($data === false || $data === '') {
            return ['ok' => false, 'error' => 'data gambar tidak valid.'];
        }

        if (strlen($data) > self::MAKS_BYTES) {
            return ['ok' => false, 'error' => 'ukuran maksimal 1,5 MB.'];
        }

        $info = getimagesizefromstring($data);

        if ($info === false || ! in_array($info['mime'], ['image/jpeg', 'image/png'], true)) {
            return ['ok' => false, 'error' => 'file bukan gambar JPEG atau PNG yang valid.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo !== false ? finfo_buffer($finfo, $data) : $info['mime'];
        if ($finfo !== false) {
            finfo_close($finfo);
        }

        if (! in_array($mime, ['image/jpeg', 'image/png'], true)) {
            return ['ok' => false, 'error' => 'file bukan gambar JPEG atau PNG yang valid.'];
        }

        return ['ok' => true, 'data' => $data, 'ext' => $info['mime'] === 'image/png' ? 'png' : 'jpg'];
    }

    /**
     * Simpan bukti ke disk privat dengan nama acak.
     *
     * @return array{ok: true, path: string, ext: string}|array{ok: false, error: string}
     */
    public static function simpan(?string $base64, string $jenis): array
    {
        if (! in_array($jenis, self::JENIS_SAHIH, true)) {
            return ['ok' => false, 'error' => 'jenis bukti tidak valid.'];
        }

        $decoded = static::decode($base64);

        if (! $decoded['ok']) {
            return $decoded;
        }

        $path = 'penarikan/bukti-'.Str::uuid().'.'.$decoded['ext'];
        Storage::disk('local')->put($path, $decoded['data']);

        return ['ok' => true, 'path' => $path, 'ext' => $decoded['ext']];
    }

    /**
     * Hapus berkas bukti bila transaksi perekaman gagal (tanpa berkas yatim).
     */
    public static function hapus(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk('local')->delete($path);
        }
    }
}
