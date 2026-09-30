<?php

namespace App\Livewire\Kolektor;

use App\Actions\Penarikan\AjukanPenarikanAction;
use App\Livewire\Concerns\ValidatesKolektorNasabah;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class PenarikanOffline extends Component
{
    use ValidatesKolektorNasabah;

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

    public $jumlahMenungguVerifikasi = 0;

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

        $this->jumlahMenungguVerifikasi = TransaksiPenarikan::where('jalur_pengajuan', 'offline')
            ->where('status', 'approved')
            ->whereIn('nasabah_id', function ($query) use ($kolektorId) {
                $query->select('nasabah_id')
                    ->from('kolektor_nasabah')
                    ->where('kolektor_id', $kolektorId)
                    ->where('status', 'aktif');
            })
            ->count();
    }

    public function updatedNasabahId()
    {
        if ($this->nasabahId && ! $this->isNasabahBinaan((int) $this->nasabahId)) {
            $this->nasabahId = '';
            $this->selectedNasabah = null;
            session()->flash('error', 'Nasabah tidak valid.');

            return;
        }

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
        if ($this->nasabahId && ! $this->isNasabahBinaan((int) $this->nasabahId)) {
            return;
        }

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

    public function submit(AjukanPenarikanAction $action)
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

        $nasabah = User::findOrFail($this->nasabahId);
        $produk = ProdukTabungan::findOrFail($this->produkId);

        try {
            $action->execute(
                $nasabah,
                $produk,
                (float) $this->nominal,
                'offline',
                $this->lokasi
            );

            $this->reset(['nasabahId', 'produkId', 'nominal', 'lokasi']);
            $this->selectedNasabah = null;
            $this->persenKomisi = 0;
            $this->nominalKomisi = 0;
            $this->nominalDiterima = 0;

            session()->flash('success', 'Penarikan offline berhasil diajukan! Menunggu persetujuan admin.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.kolektor.penarikan-offline');
    }
}
