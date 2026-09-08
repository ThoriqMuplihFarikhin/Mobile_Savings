<?php

namespace App\Livewire\Kolektor;

use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class PenarikanOffline extends Component
{
    public $nasabahId = '';

    public $produkId = '';

    public $nominal = '';

    public $lokasi = 'kantor';

    public $nasabahList = [];

    public $produkList = [];

    public $selectedNasabah = null;

    public $persenKomisi = 0;

    public $nominalKomisi = 0;

    public $nominalDiterima = 0;

    public function mount()
    {
        $kolektorId = Auth::id();

        $this->nasabahList = NasabahProfil::where('status_pendaftaran', 'aktif')
            ->whereIn('user_id', function ($query) use ($kolektorId) {
                $query->select('nasabah_id')
                    ->from('kolektor_nasabah')
                    ->where('kolektor_id', $kolektorId)
                    ->where('status', 'aktif');
            })
            ->with('user')
            ->get();

        $this->produkList = ProdukTabungan::where('status', 'aktif')->get();
    }

    public function updatedNasabahId()
    {
        if ($this->nasabahId) {
            $this->selectedNasabah = NasabahProfil::where('user_id', $this->nasabahId)
                ->with(['user', 'user.saldoProduks.produk'])
                ->first();
        } else {
            $this->selectedNasabah = null;
        }
    }

    public function updatedProdukId()
    {
        if ($this->produkId) {
            $produk = ProdukTabungan::find($this->produkId);
            $this->persenKomisi = $produk->persen_komisi ?? 0;
        } else {
            $this->persenKomisi = 0;
        }
        $this->hitungKomisi();
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
            'nasabahId' => 'required|exists:users,id',
            'produkId' => 'required|exists:produk_tabungan,id',
            'nominal' => 'required|numeric|min:10000',
            'lokasi' => 'required|in:kantor,rumah_kolektor',
        ]);

        $isTanggungJawab = KolektorNasabah::where('kolektor_id', Auth::id())
            ->where('nasabah_id', $this->nasabahId)
            ->where('status', 'aktif')
            ->exists();

        if (! $isTanggungJawab) {
            session()->flash('error', 'Nasabah ini bukan tanggung jawab Anda.');

            return;
        }

        $saldo = SaldoProduk::where('nasabah_id', $this->nasabahId)
            ->where('produk_id', $this->produkId)
            ->first();

        $totalPending = TransaksiPenarikan::where('nasabah_id', $this->nasabahId)
            ->where('produk_id', $this->produkId)
            ->where('status', 'pending')
            ->sum('nominal_diminta');

        $available = ($saldo->saldo ?? 0) - $totalPending;

        if ($available < $this->nominal) {
            session()->flash('error', 'Saldo nasabah tidak mencukupi! Sisa saldo tersedia: Rp '.number_format($available, 0, ',', '.'));

            return;
        }

        $produk = ProdukTabungan::find($this->produkId);
        if ($produk && $produk->isPaket() && $produk->tanggal_boleh_cair && now()->lt($produk->tanggal_boleh_cair)) {
            session()->flash('error', 'Penarikan paket belum bisa dilakukan sebelum tanggal '.$produk->tanggal_boleh_cair->translatedFormat('d M Y'));

            return;
        }

        TransaksiPenarikan::create([
            'nasabah_id' => $this->nasabahId,
            'produk_id' => $this->produkId,
            'nominal_diminta' => $this->nominal,
            'persen_komisi_terpakai' => $this->persenKomisi,
            'nominal_komisi' => $this->nominalKomisi,
            'nominal_diterima' => $this->nominalDiterima,
            'jalur_pengajuan' => 'offline',
            'lokasi_pengambilan' => $this->lokasi,
            'status' => 'pending',
        ]);

        $this->reset(['nasabahId', 'produkId', 'nominal', 'lokasi']);
        $this->selectedNasabah = null;
        $this->persenKomisi = 0;
        $this->nominalKomisi = 0;
        $this->nominalDiterima = 0;

        session()->flash('success', 'Penarikan offline berhasil diajukan! Menunggu persetujuan admin.');
    }

    public function render()
    {
        return view('livewire.kolektor.penarikan-offline');
    }
}
