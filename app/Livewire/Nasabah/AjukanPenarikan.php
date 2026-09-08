<?php

namespace App\Livewire\Nasabah;

use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class AjukanPenarikan extends Component
{
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

    public function submit()
    {
        $this->validate([
            'produkId' => 'required|exists:produk_tabungan,id',
            'nominal' => 'required|numeric|min:10000',
            'lokasi_pengambilan' => 'required|in:rumah_kolektor,kantor',
        ]);

        $saldo = SaldoProduk::where('nasabah_id', Auth::id())
            ->where('produk_id', $this->produkId)
            ->first();

        $totalPending = TransaksiPenarikan::where('nasabah_id', Auth::id())
            ->where('produk_id', $this->produkId)
            ->where('status', 'pending')
            ->sum('nominal_diminta');

        $available = ($saldo->saldo ?? 0) - $totalPending;

        if ($available < $this->nominal) {
            session()->flash('error', 'Saldo tidak mencukupi! Sisa saldo tersedia: Rp '.number_format($available, 0, ',', '.'));

            return;
        }

        if ($this->selectedProduk && $this->selectedProduk->isPaket()) {
            if ($this->selectedProduk->tanggal_boleh_cair && now()->lt($this->selectedProduk->tanggal_boleh_cair)) {
                session()->flash('error', 'Penarikan paket belum bisa dilakukan sebelum tanggal '.$this->selectedProduk->tanggal_boleh_cair->translatedFormat('d M Y'));

                return;
            }
        }

        TransaksiPenarikan::create([
            'nasabah_id' => Auth::id(),
            'produk_id' => $this->produkId,
            'nominal_diminta' => $this->nominal,
            'persen_komisi_terpakai' => $this->persenKomisi,
            'nominal_komisi' => $this->nominalKomisi,
            'nominal_diterima' => $this->nominalDiterima,
            'jalur_pengajuan' => 'online',
            'lokasi_pengambilan' => $this->lokasi_pengambilan,
            'status' => 'pending',
        ]);

        $this->reset(['produkId', 'nominal', 'lokasi_pengambilan']);
        $this->selectedProduk = null;

        session()->flash('success', 'Pengajuan penarikan berhasil dikirim! Menunggu persetujuan admin.');
    }

    public function render()
    {
        return view('livewire.nasabah.ajukan-penarikan');
    }
}
