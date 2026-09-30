<?php

namespace App\Livewire\Kolektor;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\AbsensiKolektor;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    public function setLokasi($lat, $lng): void
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
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
        $this->selfieBase64 = null;
        $this->tandaTanganBase64 = null;
        $this->lokasiError = null;
        $this->lokasiDitolak = false;
    }

    public function absenMasuk(): void
    {
        if ($this->sudahAbsenHariIni) {
            session()->flash('error', 'Anda sudah absen hari ini.');

            return;
        }

        if (! $this->latitude || ! $this->selfieBase64 || ! $this->tandaTanganBase64) {
            session()->flash('error', 'Lengkapi lokasi, foto selfie, dan tanda tangan terlebih dahulu.');

            return;
        }

        $fotoSelfiePath = null;
        if ($this->selfieBase64) {
            $data = str_replace('data:image/jpeg;base64,', '', $this->selfieBase64);
            $data = str_replace('data:image/png;base64,', '', $data);
            $data = base64_decode($data);
            $filename = 'selfie/'.Auth::id().'_'.now()->timestamp.'.jpg';
            Storage::disk('public')->put($filename, $data);
            $fotoSelfiePath = $filename;
        }

        $tandaTanganPath = null;
        if ($this->tandaTanganBase64) {
            $data = str_replace('data:image/png;base64,', '', $this->tandaTanganBase64);
            $data = str_replace('data:image/jpeg;base64,', '', $data);
            $data = base64_decode($data);
            $filename = 'tanda_tangan/'.Auth::id().'_'.now()->timestamp.'.png';
            Storage::disk('public')->put($filename, $data);
            $tandaTanganPath = $filename;
        }

        try {
            AbsensiKolektor::create([
                'kolektor_id' => Auth::id(),
                'tanggal' => now()->toDateString(),
                'waktu_masuk' => now()->toTimeString(),
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'foto_selfie_path' => $fotoSelfiePath,
                'tanda_tangan_path' => $tandaTanganPath,
            ]);

            $this->sudahAbsenHariIni = true;
            $this->waktuAbsenHariIni = now()->toTimeString();
            $this->resetFormCheckout();
            session()->flash('success', 'Absen Masuk berhasil dicatat!');
        } catch (QueryException $e) {
            session()->flash('error', 'Gagal mencatat absen: Anda sudah absen hari ini atau terjadi kesalahan database.');
        }
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
