<?php

namespace App\Livewire\Nasabah;

use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\TransaksiPenarikan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.mobile')]
class RiwayatPenarikan extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'nasabah';
    }

    public function batalkan(int $penarikanId): void
    {
        $hasil = DB::transaction(function () use ($penarikanId): ?TransaksiPenarikan {
            $penarikan = TransaksiPenarikan::where('id', $penarikanId)
                ->where('nasabah_id', Auth::id())
                ->lockForUpdate()
                ->first();

            if ($penarikan === null || $penarikan->status !== 'pending') {
                return null;
            }

            $penarikan->update(['status' => 'dibatalkan']);

            return $penarikan;
        });

        if ($hasil === null) {
            session()->flash('error', 'Pengajuan penarikan tidak dapat dibatalkan.');

            return;
        }

        ActivityLogger::log('batalkan_penarikan', 'transaksi_penarikan', $hasil->id, [
            'nominal_diminta' => $hasil->nominal_diminta,
            'produk_id' => $hasil->produk_id,
        ]);

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
