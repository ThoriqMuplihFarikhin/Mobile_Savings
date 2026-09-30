<?php

namespace App\Livewire\Kolektor;

use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiSetoran;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class SetorKantor extends Component
{
    public $totalBelumDisetor = 0;

    public $jumlahTransaksi = 0;

    public $catatan = '';

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->totalBelumDisetor = TransaksiSetoran::belumDisetor()
            ->where('input_by', Auth::id())
            ->sum('nominal');

        $this->jumlahTransaksi = TransaksiSetoran::belumDisetor()
            ->where('input_by', Auth::id())
            ->count();
    }

    public function submit()
    {
        if ($this->totalBelumDisetor <= 0) {
            session()->flash('error', 'Tidak ada setoran yang perlu disetor ke kantor.');

            return;
        }

        $existingPending = SetoranKolektorKantor::where('kolektor_id', Auth::id())
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            session()->flash('error', 'Anda sudah memiliki pengajuan setoran pending untuk hari ini. Mohon tunggu proses dari admin.');

            return;
        }

        $setoran = SetoranKolektorKantor::create([
            'kolektor_id' => Auth::id(),
            'tanggal_setor' => now()->toDateString(),
            'total_seharusnya' => $this->totalBelumDisetor,
            'status' => 'pending',
            'keterangan_selisih' => $this->catatan ?: null,
        ]);

        TransaksiSetoran::belumDisetor()
            ->where('input_by', Auth::id())
            ->update(['setoran_kolektor_id' => $setoran->id]);

        $this->catatan = '';
        $this->loadData();
        session()->flash('success', 'Pengajuan setoran ke kantor berhasil diajukan! Mohon tunggu verifikasi dari admin.');
    }

    public function render()
    {
        $riwayat = SetoranKolektorKantor::where('kolektor_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('livewire.kolektor.setor-kantor', compact('riwayat'));
    }
}
