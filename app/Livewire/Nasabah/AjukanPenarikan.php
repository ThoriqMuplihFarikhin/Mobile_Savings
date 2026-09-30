<?php

namespace App\Livewire\Nasabah;

use App\Actions\Penarikan\AjukanPenarikanAction;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class AjukanPenarikan extends Component
{
    use AuthorizesRole;

    protected function requiredRole(): string
    {
        return 'nasabah';
    }

    public $produkId = '';

    public $nominal = '';

    public $lokasi_pengambilan = 'kantor';

    public $produkList = [];

    public $saldoList = [];

    public $selectedProduk = null;

    public $persenKomisi = 0;

    public $nominalKomisi = 0;

    public $nominalDiterima = 0;

    public function mount()
    {
        $this->loadProduk();
        $this->loadSaldo();
    }

    public function loadProduk()
    {
        $this->produkList = ProdukTabungan::where('status', 'aktif')->get();
    }

    public function loadSaldo()
    {
        $this->saldoList = SaldoProduk::where('nasabah_id', Auth::id())
            ->with('produk')
            ->get();
    }

    public function updatedProdukId()
    {
        if ($this->produkId) {
            $this->selectedProduk = ProdukTabungan::find($this->produkId);
            $this->persenKomisi = $this->selectedProduk->persen_komisi ?? 0;
            $this->hitungKomisi();
        } else {
            $this->selectedProduk = null;
            $this->persenKomisi = 0;
            $this->nominalKomisi = 0;
            $this->nominalDiterima = 0;
        }
    }

    public function updatedNominal()
    {
        $this->hitungKomisi();
    }

    public function hitungKomisi()
    {
        if ($this->nominal > 0 && $this->persenKomisi > 0) {
            $this->nominalKomisi = ($this->nominal * $this->persenKomisi) / 100;
            $this->nominalDiterima = $this->nominal - $this->nominalKomisi;
        } else {
            $this->nominalKomisi = 0;
            $this->nominalDiterima = $this->nominal;
        }
    }

    public function submit(AjukanPenarikanAction $action)
    {
        $this->validate([
            'produkId' => 'required|exists:produk_tabungan,id',
            'nominal' => 'required|numeric|min:10000',
            'lokasi_pengambilan' => 'required|in:rumah_kolektor,kantor',
        ]);

        $nasabah = Auth::user();
        $produk = ProdukTabungan::findOrFail($this->produkId);

        try {
            $action->execute(
                $nasabah,
                $produk,
                (float) $this->nominal,
                'online',
                $this->lokasi_pengambilan
            );

            $this->reset(['produkId', 'nominal', 'lokasi_pengambilan']);
            $this->selectedProduk = null;

            session()->flash('success', 'Pengajuan penarikan berhasil dikirim! Menunggu persetujuan admin.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.nasabah.ajukan-penarikan');
    }
}
