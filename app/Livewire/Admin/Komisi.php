<?php

namespace App\Livewire\Admin;

use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Komisi extends Component
{
    use WithPagination;

    public $dariTanggal = '';

    public $sampaiTanggal = '';

    public $produkId = '';

    public $totalKomisi = 0;

    public $totalTransaksi = 0;

    public $totalNominalPenarikan = 0;

    public function mount()
    {
        $this->dariTanggal = now()->startOfMonth()->toDateString();
        $this->sampaiTanggal = now()->toDateString();
    }

    public function updated($nama)
    {
        if (in_array($nama, ['dariTanggal', 'sampaiTanggal', 'produkId'])) {
            $this->resetPage();
        }
    }

    public function resetFilter()
    {
        $this->dariTanggal = now()->startOfMonth()->toDateString();
        $this->sampaiTanggal = now()->toDateString();
        $this->produkId = '';
        $this->resetPage();
    }

    protected function baseQuery()
    {
        return TransaksiPenarikan::query()
            ->whereIn('transaksi_penarikan.status', ['approved', 'selesai'])
            ->when($this->dariTanggal, fn ($q) => $q->whereDate('transaksi_penarikan.waktu_approval', '>=', $this->dariTanggal))
            ->when($this->sampaiTanggal, fn ($q) => $q->whereDate('transaksi_penarikan.waktu_approval', '<=', $this->sampaiTanggal))
            ->when($this->produkId, fn ($q) => $q->where('transaksi_penarikan.produk_id', $this->produkId));
    }

    public function render()
    {
        $this->totalKomisi = (clone $this->baseQuery())->sum('nominal_komisi');
        $this->totalTransaksi = (clone $this->baseQuery())->count();
        $this->totalNominalPenarikan = (clone $this->baseQuery())->sum('nominal_diminta');

        $komisiPerProduk = (clone $this->baseQuery())
            ->join('produk_tabungan', 'transaksi_penarikan.produk_id', '=', 'produk_tabungan.id')
            ->select(
                'produk_tabungan.id',
                'produk_tabungan.nama',
                DB::raw('SUM(transaksi_penarikan.nominal_komisi) as total_komisi'),
                DB::raw('COUNT(*) as jumlah_transaksi')
            )
            ->groupBy('produk_tabungan.id', 'produk_tabungan.nama')
            ->orderByDesc('total_komisi')
            ->get();

        $riwayat = (clone $this->baseQuery())
            ->with(['nasabah', 'produk', 'disetujuiOleh'])
            ->latest('waktu_approval')
            ->paginate(15);

        $produkList = ProdukTabungan::where('persen_komisi', '>', 0)->orderBy('nama')->get();

        return view('livewire.admin.komisi', compact(
            'komisiPerProduk',
            'riwayat',
            'produkList',
        ));
    }
}
