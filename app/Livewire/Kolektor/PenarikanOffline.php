<?php

namespace App\Livewire\Kolektor;

use App\Actions\Penarikan\AjukanPenarikanAction;
use App\Actions\Penarikan\CatatPenarikanOfflineLangsungAction;
use App\Livewire\Concerns\AuthorizesRole;
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
    use AuthorizesRole;
    use ValidatesKolektorNasabah;

    protected function requiredRole(): string
    {
        return 'kolektor';
    }

    public $nasabahId = '';

    public $produkId = '';

    public $nominal = '';

    public $lokasi = 'kantor';

    public string $catatan = '';

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

        $this->loadProduk();

        $this->jumlahMenungguVerifikasi = TransaksiPenarikan::where('lokasi_pengambilan', 'rumah_kolektor')
            ->where('status', 'approved')
            ->whereIn('nasabah_id', function ($query) use ($kolektorId) {
                $query->select('nasabah_id')
                    ->from('kolektor_nasabah')
                    ->where('kolektor_id', $kolektorId)
                    ->where('status', 'aktif');
            })
            ->count();
    }

    public function loadProduk(?int $nasabahId = null): void
    {
        $this->produkList = ProdukTabungan::query()
            ->when($nasabahId === null, function ($query) {
                $query->where('status', 'aktif');
            }, function ($query) use ($nasabahId) {
                $query->where(function ($inner) use ($nasabahId) {
                    $inner->where('status', 'aktif')
                        ->orWhereIn('id', function ($sub) use ($nasabahId) {
                            $sub->select('produk_id')
                                ->from('saldo_produk')
                                ->where('nasabah_id', $nasabahId)
                                ->where('saldo', '>', 0);
                        });
                });
            })
            ->orderBy('nama')
            ->get();
    }

    public function updatedNasabahId()
    {
        if ($this->nasabahId && ! $this->isNasabahBinaan((int) $this->nasabahId)) {
            $this->nasabahId = '';
            $this->selectedNasabah = null;
            $this->loadProduk();
            session()->flash('error', 'Nasabah tidak valid.');

            return;
        }

        if ($this->nasabahId) {
            $this->selectedNasabah = NasabahProfil::where('user_id', $this->nasabahId)
                ->with(['user', 'user.saldoProduks.produk'])
                ->first();
            $this->loadProduk((int) $this->nasabahId);
        } else {
            $this->selectedNasabah = null;
            $this->loadProduk();
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
        $teks = trim((string) $this->nominal);

        if (! is_numeric($teks) || preg_match('/^\d{1,13}(\.\d{1,2})?$/', $teks) !== 1 || (float) $teks <= 0) {
            $this->nominalKomisi = '0.00';
            $this->nominalDiterima = $teks;

            return;
        }

        $terbulatkan = bcadd($teks, '0', 2);

        if ($this->persenKomisi <= 0) {
            $this->nominalKomisi = '0.00';
            $this->nominalDiterima = $terbulatkan;

            return;
        }

        $hasil = AjukanPenarikanAction::hitungKomisi($terbulatkan, $this->persenKomisi);
        $this->nominalKomisi = $hasil['komisi'];
        $this->nominalDiterima = $hasil['diterima'];
    }

    public function submit(CatatPenarikanOfflineLangsungAction $action)
    {
        $this->validate([
            'nasabahId' => 'required|exists:users,id',
            'produkId' => 'required|exists:produk_tabungan,id',
            'nominal' => 'required|numeric|decimal:0,2|gt:0',
            'lokasi' => 'required|in:kantor,rumah_kolektor',
            'catatan' => 'nullable|string|max:500',
        ]);

        $isTanggungJawab = KolektorNasabah::where('kolektor_id', Auth::id())
            ->where('nasabah_id', $this->nasabahId)
            ->where('status', 'aktif')
            ->exists();

        if (! $isTanggungJawab) {
            session()->flash('error', 'Nasabah ini bukan tanggung jawab Anda.');

            return;
        }

        $nasabah = User::query()->whereKey($this->nasabahId)->first();
        $produk = ProdukTabungan::query()->whereKey($this->produkId)->first();

        if (! $nasabah instanceof User || ! $produk instanceof ProdukTabungan) {
            session()->flash('error', 'Data penarikan tidak ditemukan.');

            return;
        }

        if ($nasabah->isOffline()) {
            $this->validate(['catatan' => 'required|string|max:500']);
        }

        $kolektor = Auth::user();

        if (! $kolektor instanceof User) {
            session()->flash('error', 'Sesi berakhir. Silakan login kembali.');

            return;
        }

        try {
            $hasil = $action->execute(
                $kolektor,
                $nasabah,
                $produk,
                (string) $this->nominal,
                $this->lokasi,
                (string) $this->catatan,
            );

            $this->reset(['nasabahId', 'produkId', 'nominal', 'lokasi', 'catatan']);
            $this->selectedNasabah = null;
            $this->persenKomisi = 0;
            $this->nominalKomisi = 0;
            $this->nominalDiterima = 0;
            $this->loadProduk();

            if ($hasil['langsung']) {
                session()->flash('success', 'Penarikan offline langsung diselesaikan! Saldo nasabah sudah dipotong.');
            } elseif ($hasil['alasan'] === 'kas_kurang') {
                session()->flash('error', 'Kas di tangan tidak mencukupi — penarikan masuk antrian approval admin.');
            } else {
                session()->flash('success', 'Penarikan offline berhasil diajukan! Menunggu persetujuan admin.');
            }
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.kolektor.penarikan-offline');
    }
}
