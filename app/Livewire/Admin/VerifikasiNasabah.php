<?php

namespace App\Livewire\Admin;

use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class VerifikasiNasabah extends Component
{
    use WithPagination;

    public function render()
    {
        $pending = NasabahProfil::with(['user', 'didaftarkanOleh'])
            ->where('status_pendaftaran', 'pending_verifikasi')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.verifikasi-nasabah', compact('pending'));
    }

    public function approve($id)
    {
        DB::transaction(function () use ($id) {
            $profil = NasabahProfil::find($id);
            if ($profil) {
                $profil->update([
                    'status_pendaftaran' => 'aktif',
                    'diverifikasi_oleh' => auth()->id(),
                    'tanggal_verifikasi' => now(),
                ]);
                $profil->user->update(['status_akun' => 'aktif']);

                if ($profil->didaftarkan_oleh && $profil->didaftarkan_oleh !== auth()->id()) {
                    $existing = KolektorNasabah::where('kolektor_id', $profil->didaftarkan_oleh)
                        ->where('nasabah_id', $profil->user_id)
                        ->where('status', 'aktif')
                        ->exists();

                    if (! $existing) {
                        KolektorNasabah::create([
                            'kolektor_id' => $profil->didaftarkan_oleh,
                            'nasabah_id' => $profil->user_id,
                            'tanggal_mulai_ditangani' => now()->toDateString(),
                            'status' => 'aktif',
                        ]);
                    }
                }
            }
        });
        session()->flash('success', 'Nasabah berhasil diverifikasi!');
    }

    public function reject($id)
    {
        DB::transaction(function () use ($id) {
            $profil = NasabahProfil::find($id);
            if ($profil) {
                $profil->update([
                    'status_pendaftaran' => 'ditolak',
                    'diverifikasi_oleh' => auth()->id(),
                    'tanggal_verifikasi' => now(),
                ]);
                $profil->user->update(['status_akun' => 'terkunci']);
            }
        });
        session()->flash('success', 'Nasabah ditolak.');
    }
}
