<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Models\Komplain;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AntrianKomplain extends Component
{
    use WithPagination;

    public $statusFilter = 'baru';

    public $selectedId = null;

    public $catatan = '';

    public $showDetail = false;

    protected $listeners = ['komplainUpdated' => '$refresh'];

    public function getSelectedKomplainProperty()
    {
        if (! $this->selectedId) {
            return null;
        }

        return Komplain::with(['nasabah', 'transaksiTerkait.produk'])
            ->find($this->selectedId);
    }

    public function render()
    {
        $komplains = Komplain::with('nasabah')
            ->where('status', $this->statusFilter)
            ->latest()
            ->paginate(10);

        return view('livewire.admin.antrian-komplain', compact('komplains'));
    }

    public function showDetail($id)
    {
        $this->selectedId = $id;
        $this->showDetail = true;
        $this->catatan = '';
    }

    public function proses($id)
    {
        $komplain = Komplain::find($id);
        Komplain::where('id', $id)->update(['status' => 'diproses']);

        if ($komplain && $komplain->nasabah_id) {
            try {
                ActivityLogger::notify(
                    $komplain->nasabah_id,
                    'Komplain Diproses',
                    'Komplain Anda sedang diproses oleh tim kami. Mohon tunggu informasi selanjutnya.',
                    'in_app'
                );
            } catch (\Exception $e) {
                \Log::error('Gagal kirim notifikasi komplain diproses', [
                    'komplain_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        session()->flash('success', 'Komplain ditandai sedang diproses!');
    }

    public function selesai($id)
    {
        $this->validate(['catatan' => 'required|string|min:3']);

        $komplain = Komplain::find($id);
        Komplain::where('id', $id)->update([
            'status' => 'selesai',
            'catatan_penyelesaian' => $this->catatan,
            'tanggal_selesai' => now(),
            'ditangani_oleh' => auth()->id(),
        ]);

        ActivityLogger::log('selesai_komplain', 'komplain', $id, [
            'nasabah_id' => $komplain->nasabah_id ?? null,
        ]);

        if ($komplain && $komplain->nasabah_id) {
            try {
                ActivityLogger::notify(
                    $komplain->nasabah_id,
                    'Komplain Selesai',
                    'Komplain Anda telah diselesaikan. Catatan: '.$this->catatan,
                    'both'
                );
            } catch (\Exception $e) {
                \Log::error('Gagal kirim notifikasi komplain selesai', [
                    'komplain_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->showDetail = false;
        $this->selectedId = null;
        $this->catatan = '';
        session()->flash('success', 'Komplain berhasil diselesaikan!');
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }
}
