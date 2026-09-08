<?php

namespace App\Livewire\Nasabah;

use App\Helpers\ActivityLogger;
use App\Models\Komplain as KomplainModel;
use App\Models\TransaksiSetoran;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.mobile')]
class Komplain extends Component
{
    use WithPagination;

    public $showForm = false;

    public $kategori = 'lainnya';

    public $deskripsi = '';

    public $transaksiTerkaitId = '';

    public $riwayatPage = false;

    public function render()
    {
        $komplains = KomplainModel::where('nasabah_id', Auth::id())
            ->latest()
            ->paginate(10);

        $riwayatTransaksi = TransaksiSetoran::where('nasabah_id', Auth::id())
            ->where('tanggal_transaksi', '>=', now()->subDays(30))
            ->with('produk')
            ->latest('tanggal_transaksi')
            ->get();

        return view('livewire.nasabah.komplain', compact('komplains', 'riwayatTransaksi'));
    }

    public function toggleForm()
    {
        $this->showForm = ! $this->showForm;
        $this->reset(['kategori', 'deskripsi', 'transaksiTerkaitId']);
    }

    public function submit()
    {
        $this->validate([
            'kategori' => 'required|in:saldo,barang_paket,penarikan,lainnya',
            'deskripsi' => 'required|string|min:10',
            'transaksiTerkaitId' => 'nullable|exists:transaksi_setoran,id',
        ]);

        $komplain = KomplainModel::create([
            'nasabah_id' => Auth::id(),
            'kategori' => $this->kategori,
            'transaksi_terkait_id' => $this->transaksiTerkaitId ?: null,
            'deskripsi' => $this->deskripsi,
            'status' => 'baru',
            'tanggal_dibuat' => now(),
        ]);

        ActivityLogger::log('buat_komplain', 'komplain', $komplain->id, [
            'kategori' => $this->kategori,
            'deskripsi' => $this->deskripsi,
            'transaksi_terkait_id' => $this->transaksiTerkaitId ?: null,
        ]);

        $this->showForm = false;
        $this->reset(['kategori', 'deskripsi', 'transaksiTerkaitId']);
        session()->flash('success', 'Komplain berhasil dikirim! Tim kami akan segera menindaklanjuti.');
    }
}
