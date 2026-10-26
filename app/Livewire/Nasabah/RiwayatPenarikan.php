<?php

namespace App\Livewire\Nasabah;

use App\Actions\Penarikan\BatalkanPenarikanAction;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.mobile')]
class RiwayatPenarikan extends Component
{
    use AuthorizesRole;
    use WithPagination;

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

    public function render()
    {
        $penarikan = TransaksiPenarikan::with('produk')
            ->where('nasabah_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('livewire.nasabah.riwayat-penarikan', compact('penarikan'));
    }
}
