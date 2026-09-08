<?php

namespace App\Livewire\Kolektor;

use App\Models\JadwalKunjungan;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class JadwalKunjungan extends Component
{
    public $jadwalHari = [];

    public $tanggal = '';

    public function mount()
    {
        $this->tanggal = now()->format('Y-m-d');
        $this->loadJadwal();
    }

    public function loadJadwal()
    {
        $kolektorId = Auth::id();

        $nasabahIds = KolektorNasabah::where('kolektor_id', $kolektorId)
            ->where('status', 'aktif')
            ->pluck('nasabah_id');

        $this->jadwalHari = NasabahProfil::whereIn('user_id', $nasabahIds)
            ->with('user')
            ->get()
            ->map(function ($profil) {
                $jadwal = JadwalKunjungan::where('kolektor_id', Auth::id())
                    ->where('nasabah_id', $profil->user_id)
                    ->where('tanggal_jadwal', $this->tanggal)
                    ->first();

                return [
                    'profil' => $profil,
                    'jadwal' => $jadwal,
                    'status' => $jadwal->status_kunjungan ?? null,
                ];
            });
    }

    public function updateStatus($nasabahId, $status)
    {
        JadwalKunjungan::updateOrCreate(
            [
                'kolektor_id' => Auth::id(),
                'nasabah_id' => $nasabahId,
                'tanggal_jadwal' => $this->tanggal,
            ],
            ['status_kunjungan' => $status]
        );

        $this->loadJadwal();
        session()->flash('success', 'Status kunjungan berhasil diupdate!');
    }

    public function render()
    {
        return view('livewire.kolektor.jadwal-kunjungan');
    }
}
