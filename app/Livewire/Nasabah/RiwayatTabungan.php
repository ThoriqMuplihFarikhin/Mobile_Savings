<?php

namespace App\Livewire\Nasabah;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

        $setoran7Hari = $this->agregat7Hari($this->produkFilter);

        return view('livewire.nasabah.riwayat-tabungan', compact('setoran', 'produkSummary', 'produkList', 'setoran7Hari'));
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

    /**
     * Agregat setoran per hari untuk tujuh hari terakhir (DESIGN.md §14 baris
     * Riwayat tabungan: "Grafik batang 7 hari: butuh agregat per hari").
     * Jendela grafik tetap 7 hari lalu mengikuti filter produk; filter tanggal
     * daftar tidak dipakai. Status dibatalkan dikecualikan, konsisten dengan
     * ringkasan produk di layar yang sama. Nasabah aktif dari guard AuthorizesRole.
     *
     * @return Collection<int, array{label: string, tanggal: string, label_tanggal: string, hari: string, total: float}>
     */
    private function agregat7Hari(mixed $produkFilter): Collection
    {
        $hariIni = Carbon::today();
        $rentang = collect(range(6, 0))->map(fn (int $i) => $hariIni->copy()->subDays($i));

        $query = TransaksiSetoran::where('nasabah_id', Auth::id())
            ->where('status', '!=', 'dibatalkan')
            ->whereBetween('tanggal_transaksi', [$rentang->first()->toDateString(), $hariIni->toDateString()]);

        if ($produkFilter !== '' && $produkFilter !== null) {
            $query->where('produk_id', $produkFilter);
        }

        $perTanggal = $query
            ->selectRaw('tanggal_transaksi, SUM(nominal) as total')
            ->groupBy('tanggal_transaksi')
            ->pluck('total', 'tanggal_transaksi');

        $labelHari = [0 => 'M', 1 => 'S', 2 => 'S', 3 => 'R', 4 => 'K', 5 => 'J', 6 => 'S'];

        return $rentang->map(function (Carbon $tanggal) use ($perTanggal, $labelHari) {
            $kunci = $tanggal->toDateString();

            return [
                'label' => $labelHari[(int) $tanggal->dayOfWeek],
                'tanggal' => $kunci,
                'label_tanggal' => $tanggal->format('d M'),
                'hari' => $tanggal->translatedFormat('l'),
                'total' => (float) ($perTanggal[$kunci] ?? 0),
            ];
        })->values();
    }
}
