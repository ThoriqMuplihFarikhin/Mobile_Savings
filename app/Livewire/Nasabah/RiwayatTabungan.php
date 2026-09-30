<?php

namespace App\Livewire\Nasabah;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.mobile')]
class RiwayatTabungan extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'nasabah';
    }

    public $produkFilter = '';

    public $dariTanggal = '';

    public $sampaiTanggal = '';

    public function render()
    {
        $userId = Auth::id();

        $query = TransaksiSetoran::with('produk')
            ->where('nasabah_id', $userId);

        if ($this->produkFilter) {
            $query->where('produk_id', $this->produkFilter);
        }

        if ($this->dariTanggal) {
            $query->whereDate('tanggal_transaksi', '>=', $this->dariTanggal);
        }

        if ($this->sampaiTanggal) {
            $query->whereDate('tanggal_transaksi', '<=', $this->sampaiTanggal);
        }

        $setoran = $query->latest('tanggal_transaksi')->paginate(15);

        $produkSummary = TransaksiSetoran::where('nasabah_id', $userId)
            ->where('status', '!=', 'dibatalkan')
            ->with('produk')
            ->get()
            ->groupBy('produk_id')
            ->map(fn ($items) => [
                'nama' => $items->first()->produk->nama ?? '-',
                'tipe' => $items->first()->produk->tipe ?? '-',
                'total' => $items->sum('nominal'),
                'jumlah' => $items->count(),
            ])
            ->values();

        $produkList = ProdukTabungan::where('status', 'aktif')->get();

        return view('livewire.nasabah.riwayat-tabungan', compact('setoran', 'produkSummary', 'produkList'));
    }

    public function resetFilters()
    {
        $this->produkFilter = '';
        $this->dariTanggal = '';
        $this->sampaiTanggal = '';
        $this->resetPage();
    }

    public function filterThisMonth()
    {
        $this->dariTanggal = now()->startOfMonth()->format('Y-m-d');
        $this->sampaiTanggal = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function filterLastMonth()
    {
        $this->dariTanggal = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->sampaiTanggal = now()->subMonth()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function updatedProdukFilter()
    {
        $this->resetPage();
    }

    public function updatedDariTanggal()
    {
        $this->resetPage();
    }

    public function updatedSampaiTanggal()
    {
        $this->resetPage();
    }
}
