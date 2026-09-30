<?php

namespace App\Livewire\Admin;

use App\Models\KepesertaanPaket;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class NasabahBermasalah extends Component
{
    use WithPagination;

    public $search = '';

    public $selectedKepesertaan = null;

    public $showDetail = false;

    public $keputusan_akhir = '';

    public $catatan_admin = '';

    public $metode_pengambilan = '';

    public function render()
    {
        $query = KepesertaanPaket::with(['nasabah', 'produk'])
            ->where('status_alert', 'perlu_review');

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('nasabah', fn ($u) => $u->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('produk', fn ($p) => $p->where('nama', 'like', "%{$this->search}%"));
            });
        }

        $kepesertaan = $query->latest()->paginate(10);

        return view('livewire.admin.nasabah-bermasalah', compact('kepesertaan'));
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function selectKepesertaan($id)
    {
        $this->selectedKepesertaan = KepesertaanPaket::with(['nasabah', 'produk'])->find($id);
        $this->selectedKepesertaan->hitungUlangKepesertaan();
        $this->selectedKepesertaan->refresh();
        $this->keputusan_akhir = $this->selectedKepesertaan->keputusan_akhir ?? '';
        $this->catatan_admin = $this->selectedKepesertaan->catatan_admin ?? '';
        $this->metode_pengambilan = $this->selectedKepesertaan->metode_pengambilan ?? '';
        $this->showDetail = true;
    }

    public function updateKeputusan()
    {
        $this->validate([
            'keputusan_akhir' => 'required|in:lanjut,gagal_dikembalikan,gagal_dialihkan',
            'catatan_admin' => 'nullable|string|max:1000',
            'metode_pengambilan' => 'nullable|in:ambil_sendiri,diantar_kolektor',
        ]);

        $this->selectedKepesertaan->update([
            'keputusan_akhir' => $this->keputusan_akhir,
            'catatan_admin' => $this->catatan_admin,
            'metode_pengambilan' => $this->metode_pengambilan,
        ]);

        $this->showDetail = false;
        $this->selectedKepesertaan = null;

        session()->flash('success', 'Keputusan berhasil disimpan!');
    }
}
