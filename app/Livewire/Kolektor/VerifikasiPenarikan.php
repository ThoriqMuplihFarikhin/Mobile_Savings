<?php

namespace App\Livewire\Kolektor;

use App\Actions\Penarikan\VerifikasiPenarikanOfflineAction;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\TransaksiPenarikan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.mobile')]
class VerifikasiPenarikan extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'kolektor';
    }

    public $penarikanId = null;

    public $pin = '';

    public $showModal = false;

    public function render()
    {
        $kolektorId = Auth::id();

        $penarikan = TransaksiPenarikan::with(['nasabah', 'produk'])
            ->where('jalur_pengajuan', 'offline')
            ->where('status', 'approved')
            ->whereIn('nasabah_id', function ($query) use ($kolektorId) {
                $query->select('nasabah_id')
                    ->from('kolektor_nasabah')
                    ->where('kolektor_id', $kolektorId)
                    ->where('status', 'aktif');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.kolektor.verifikasi-penarikan', compact('penarikan'));
    }

    public function bukaModal($id)
    {
        $this->penarikanId = $id;
        $this->pin = '';
        $this->showModal = true;
    }

    public function tutupModal()
    {
        $this->showModal = false;
        $this->penarikanId = null;
        $this->pin = '';
    }

    public function konfirmasi(VerifikasiPenarikanOfflineAction $action)
    {
        $this->validate([
            'pin' => 'required|digits:6',
        ], [], ['pin' => 'PIN nasabah']);

        $penarikan = TransaksiPenarikan::findOrFail($this->penarikanId);

        try {
            $action->execute($penarikan, $this->pin, Auth::user());

            $this->tutupModal();
            session()->flash('success', 'Verifikasi berhasil! Penarikan dinyatakan selesai.');
        } catch (\Exception $e) {
            $this->pin = '';
            $this->addError('pin', $e->getMessage());
        }
    }
}
