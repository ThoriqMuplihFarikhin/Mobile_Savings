<?php

namespace App\Livewire\Kolektor;

use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\IzinKolektor;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class AjukanIzin extends Component
{
    use AuthorizesRole;

    public string $tanggalMulai = '';

    public string $tanggalSelesai = '';

    public string $alasan = '';

    protected function requiredRole(): string
    {
        return 'kolektor';
    }

    public function ajukan(): void
    {
        $this->validate([
            'tanggalMulai' => ['required', 'date'],
            'tanggalSelesai' => ['required', 'date', 'after_or_equal:tanggalMulai'],
            'alasan' => ['required', 'string', 'max:500'],
        ]);

        $izin = IzinKolektor::create([
            'kolektor_id' => auth()->id(),
            'tanggal_mulai' => $this->tanggalMulai,
            'tanggal_selesai' => $this->tanggalSelesai,
            'alasan' => $this->alasan,
            'status' => 'pending',
        ]);

        ActivityLogger::log('ajukan_izin', 'izin_kolektor', $izin->id, [
            'kolektor_id' => auth()->id(),
            'tanggal_mulai' => $this->tanggalMulai,
            'tanggal_selesai' => $this->tanggalSelesai,
        ]);

        $this->reset('tanggalMulai', 'tanggalSelesai', 'alasan');

        session()->flash('success', 'Pengajuan izin terkirim. Menunggu persetujuan admin.');
    }

    public function render(): View
    {
        $daftarIzin = IzinKolektor::where('kolektor_id', auth()->id())
            ->latest()
            ->get();

        return view('livewire.kolektor.ajukan-izin', compact('daftarIzin'));
    }
}
