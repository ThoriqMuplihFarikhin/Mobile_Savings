<?php

namespace App\Livewire\Kolektor;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\AbsensiKolektor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class Absen extends Component
{
    use AuthorizesRole;

    protected function requiredRole(): string
    {
        return 'kolektor';
    }

    public $sudahAbsenHariIni = false;

    public $waktuAbsenHariIni = null;

    public $sudahAbsenKeluarHariIni = false;

    public $waktuKeluarHariIni = null;

    public $latitude;

    public $longitude;

    public ?float $akurasi = null;

    public $lokasiError = null;

    public $lokasiDitolak = false;

    public $selfieBase64 = null;

    public $tandaTanganBase64 = null;

    public function mount(): void
    {
        $absen = AbsensiKolektor::where('kolektor_id', Auth::id())
            ->where('tanggal', now()->toDateString())
            ->first();

        if ($absen) {
            $this->sudahAbsenHariIni = true;
            $this->waktuAbsenHariIni = $absen->waktu_masuk;

            if ($absen->waktu_keluar) {
                $this->sudahAbsenKeluarHariIni = true;
                $this->waktuKeluarHariIni = $absen->waktu_keluar;
            }
        }
    }

    public function setLokasi($lat, $lng, ?float $akurasi = null): void
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
        $this->akurasi = $akurasi;
    }

    public function setLokasiError($pesan, $kode = null): void
    {
        $this->lokasiError = $pesan;
        $this->lokasiDitolak = ($kode == 1);
    }

    public function resetLokasiError(): void
    {
        $this->lokasiError = null;
        $this->lokasiDitolak = false;
    }

    public function setSelfieBase64($base64): void
    {
        $this->selfieBase64 = $base64;
    }

    public function setTandaTanganBase64($base64): void
    {
        $this->tandaTanganBase64 = $base64;
    }

    public function resetFormCheckout(): void
    {
        $this->latitude = null;
        $this->longitude = null;
        $this->akurasi = null;
        $this->selfieBase64 = null;
        $this->tandaTanganBase64 = null;
        $this->lokasiError = null;
        $this->lokasiDitolak = false;
    }

    public function absenMasuk(): void
    {
        $sudahAbsen = AbsensiKolektor::where('kolektor_id', Auth::id())
            ->where('tanggal', now()->toDateString())
            ->exists();

        if ($sudahAbsen) {
            $this->sudahAbsenHariIni = true;
            session()->flash('error', 'Anda sudah absen hari ini.');

            return;
        }

        if ($this->latitude === null || $this->latitude === ''
            || $this->longitude === null || $this->longitude === ''
            || $this->selfieBase64 === null || $this->selfieBase64 === ''
            || $this->tandaTanganBase64 === null || $this->tandaTanganBase64 === '') {
            session()->flash('error', 'Lengkapi lokasi, foto selfie, dan tanda tangan terlebih dahulu.');

            return;
        }

        if (! is_numeric($this->latitude) || ! is_numeric($this->longitude)
            || (float) $this->latitude < -90 || (float) $this->latitude > 90
            || (float) $this->longitude < -180 || (float) $this->longitude > 180) {
            session()->flash('error', 'Lokasi tidak valid. Pastikan GPS aktif, lalu coba lagi.');

            return;
        }

        $selfie = $this->decodeGambar((string) $this->selfieBase64);
        if (! $selfie['ok']) {
            session()->flash('error', 'Foto selfie: '.$selfie['error']);

            return;
        }

        $tandaTangan = $this->decodeGambar((string) $this->tandaTanganBase64);
        if (! $tandaTangan['ok']) {
            session()->flash('error', 'Tanda tangan: '.$tandaTangan['error']);

            return;
        }

        $fotoSelfiePath = 'absensi/selfie-'.Str::uuid().'.'.$selfie['ext'];
        $tandaTanganPath = 'absensi/tanda-tangan-'.Str::uuid().'.'.$tandaTangan['ext'];

        try {
            Storage::disk('local')->put($fotoSelfiePath, $selfie['data']);
            Storage::disk('local')->put($tandaTanganPath, $tandaTangan['data']);

            AbsensiKolektor::create([
                'kolektor_id' => Auth::id(),
                'tanggal' => now()->toDateString(),
                'waktu_masuk' => now()->toTimeString(),
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'akurasi' => $this->akurasi,
                'foto_selfie_path' => $fotoSelfiePath,
                'tanda_tangan_path' => $tandaTanganPath,
            ]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete([$fotoSelfiePath, $tandaTanganPath]);
            report($e);
            session()->flash('error', 'Gagal mencatat absen. Silakan coba lagi.');

            return;
        }

        $this->sudahAbsenHariIni = true;
        $this->waktuAbsenHariIni = now()->toTimeString();
        $this->resetFormCheckout();
        session()->flash('success', 'Absen Masuk berhasil dicatat!');
    }

    /**
     * Decode dan validasi base64 gambar secara ketat.
     *
     * @return array{ok: true, data: string, ext: string}|array{ok: false, error: string}
     */
    protected function decodeGambar(string $base64): array
    {
        if (! preg_match('#^data:image/(jpeg|png);base64,#', $base64)) {
            return ['ok' => false, 'error' => 'format harus JPEG atau PNG.'];
        }

        $payload = substr($base64, (int) strpos($base64, ',') + 1);
        $data = base64_decode($payload, true);

        if ($data === false || $data === '') {
            return ['ok' => false, 'error' => 'data gambar tidak valid.'];
        }

        if (strlen($data) > 1572864) {
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

    public function absenKeluar(): void
    {
        $absenHariIni = AbsensiKolektor::where('kolektor_id', Auth::id())
            ->where('tanggal', now()->toDateString())->first();

        if (! $absenHariIni) {
            session()->flash('error', 'Anda belum melakukan Absen Masuk hari ini.');

            return;
        }

        if ($absenHariIni->waktu_keluar) {
            session()->flash('error', 'Anda sudah melakukan Absen Keluar hari ini.');

            return;
        }

        $absenHariIni->update([
            'waktu_keluar' => now()->toTimeString(),
        ]);

        $this->sudahAbsenKeluarHariIni = true;
        $this->waktuKeluarHariIni = now()->toTimeString();
        session()->flash('success', 'Absen Keluar berhasil dicatat!');
    }

    public function render()
    {
        $riwayat = AbsensiKolektor::where('kolektor_id', Auth::id())
            ->orderByDesc('tanggal')
            ->limit(7)
            ->get();

        return view('livewire.kolektor.absen', compact('riwayat'));
    }
}
