<?php

namespace App\Livewire\Nasabah;

use App\Actions\Penarikan\BatalkanPenarikanAction;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Kartu "Pengajuan Aktif" (D18): menampilkan pengajuan penarikan berjalan
 * (pending/approved) dengan tombol pembatalan; dipasang di halaman Ajukan
 * Penarikan dan dashboard nasabah.
 */
class PengajuanAktif extends Component
{
    use AuthorizesRole;

    /** Alasan opsional pembatalan yang diisi nasabah (D18). */
    public string $alasanBatal = '';

    protected function requiredRole(): string
    {
        return 'nasabah';
    }

    public function batalkan(BatalkanPenarikanAction $action, int $penarikanId): void
    {
        $nasabah = Auth::user();

        if (! $nasabah instanceof User) {
            return;
        }

        try {
            $action->execute($penarikanId, $nasabah, $this->alasanBatal);
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->reset('alasanBatal');
        session()->flash('success', 'Pengajuan penarikan berhasil dibatalkan.');
    }

    public function render(): View
    {
        $pengajuan = TransaksiPenarikan::with('produk')
            ->where('nasabah_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->latest()
            ->get();

        return view('livewire.nasabah.pengajuan-aktif', compact('pengajuan'));
    }
}
