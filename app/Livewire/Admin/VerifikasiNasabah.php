<?php

namespace App\Livewire\Admin;

use App\Actions\Kolektor\TugaskanNasabahAction;
use App\Actions\Pin\KirimPinAwalAction;
use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
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

    public function render(): View
    {
        $pending = NasabahProfil::with(['user', 'didaftarkanOleh'])
            ->where('status_pendaftaran', 'pending_verifikasi')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.verifikasi-nasabah', compact('pending'));
    }

    public function approve(int $id): void
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
                    try {
                        app(TugaskanNasabahAction::class)->execute(
                            (int) $profil->didaftarkan_oleh,
                            (int) $profil->user_id,
                        );
                    } catch (DomainException $e) {
                        report($e);
                    }
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

        $nasabah = User::find((int) $hasil['user_id']);

        if ($nasabah !== null && $nasabah->isOffline()) {
            session()->flash('success', 'Nasabah offline berhasil diverifikasi! Tidak ada PIN yang dikirim karena nasabah tidak memakai aplikasi.');

            return;
        }

        $pin = $nasabah ? app(KirimPinAwalAction::class)->buatDanKirim($nasabah) : null;

        session()->flash('success', $pin === null
            ? 'Nasabah berhasil diverifikasi! PIN awal dikirim ke WhatsApp nasabah dan tidak ditampilkan di sini. Nasabah wajib mengganti PIN setelah login.'
            : "Nasabah berhasil diverifikasi! PIN awal: {$pin} — catat sekarang karena hanya ditampilkan sekali, lalu sampaikan ke nasabah. Nasabah wajib mengganti PIN setelah login.");
    }

    public function reject(int $id): void
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
