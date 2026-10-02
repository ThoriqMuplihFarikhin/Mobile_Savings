<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class VerifikasiNasabah extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'admin';
    }

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
        $hasil = DB::transaction(function () use ($id) {
            $profil = NasabahProfil::whereKey($id)->lockForUpdate()->first();

            if (! $profil || $profil->status_pendaftaran !== 'pending_verifikasi') {
                return ['error' => 'Hanya nasabah berstatus menunggu verifikasi yang bisa diproses.'];
            }

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

            return ['user_id' => $profil->user_id];
        });

        if (isset($hasil['error'])) {
            session()->flash('error', $hasil['error']);

            return;
        }

        ActivityLogger::log('verifikasi_nasabah', 'nasabah_profil', (int) $id, [
            'user_id' => $hasil['user_id'],
        ]);
        session()->flash('success', 'Nasabah berhasil diverifikasi!');
    }

    public function reject($id)
    {
        $hasil = DB::transaction(function () use ($id) {
            $profil = NasabahProfil::whereKey($id)->lockForUpdate()->first();

            if (! $profil || $profil->status_pendaftaran !== 'pending_verifikasi') {
                return ['error' => 'Hanya nasabah berstatus menunggu verifikasi yang bisa diproses.'];
            }

            $profil->update([
                'status_pendaftaran' => 'ditolak',
                'diverifikasi_oleh' => auth()->id(),
                'tanggal_verifikasi' => now(),
            ]);
            $profil->user->update(['status_akun' => 'terkunci']);

            return ['user_id' => $profil->user_id];
        });

        if (isset($hasil['error'])) {
            session()->flash('error', $hasil['error']);

            return;
        }

        ActivityLogger::log('tolak_nasabah', 'nasabah_profil', (int) $id, [
            'user_id' => $hasil['user_id'],
        ]);
        session()->flash('success', 'Nasabah ditolak.');
    }
}
