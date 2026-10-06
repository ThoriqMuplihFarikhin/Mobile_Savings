<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Komisi extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public string $dariTanggal = '';

    public string $sampaiTanggal = '';

    public string $produkId = '';

    public float $totalKomisi = 0;

    public int $totalTransaksi = 0;

    public float $totalNominalPenarikan = 0;

    public function mount(): void
    {
        $this->dariTanggal = now()->startOfMonth()->toDateString();
        $this->sampaiTanggal = now()->toDateString();
    }

    public function updated(string $nama): void
    {
        if (in_array($nama, ['dariTanggal', 'sampaiTanggal', 'produkId'])) {
            $this->resetPage();
        }
    }

    public function resetFilter(): void
    {
        $this->dariTanggal = now()->startOfMonth()->toDateString();
        $this->sampaiTanggal = now()->toDateString();
        $this->produkId = '';
        $this->resetPage();
    }

    /**
     * @return Builder<TransaksiPenarikan>
     */
    protected function baseQuery(): Builder
    {
        return TransaksiPenarikan::query()
            ->whereIn('transaksi_penarikan.status', ['approved', 'selesai'])
            ->when($this->dariTanggal, fn ($q) => $q->whereDate('transaksi_penarikan.waktu_approval', '>=', $this->dariTanggal))
            ->when($this->sampaiTanggal, fn ($q) => $q->whereDate('transaksi_penarikan.waktu_approval', '<=', $this->sampaiTanggal))
            ->when($this->produkId, fn ($q) => $q->where('transaksi_penarikan.produk_id', $this->produkId));
    }

    public function render(): View
    {
        $this->totalKomisi = (float) (clone $this->baseQuery())->sum('nominal_komisi');
        $this->totalTransaksi = (clone $this->baseQuery())->count();
        $this->totalNominalPenarikan = (float) (clone $this->baseQuery())->sum('nominal_diminta');

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
