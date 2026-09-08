<?php

namespace App\Livewire\Kolektor;

use App\Helpers\ActivityLogger;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiSetoran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class InputSetoran extends Component
{
    public $nasabahId = '';

    public $produkId = '';

    public $nominal = '';

    public $tanggal_transaksi = '';

    public $sumber_input = 'real_time';

    public $catatan = '';

    public $nasabahList = [];

    public $produkList = [];

    public $selectedNasabah = null;

    public $showSuccess = false;

    public function mount()
    {
        $this->tanggal_transaksi = now()->format('Y-m-d');
        $this->loadNasabah();
        $this->loadProduk();
    }

    public function loadNasabah()
    {
        $kolektorId = Auth::id();

        $this->nasabahList = NasabahProfil::where('status_pendaftaran', 'aktif')
            ->whereIn('user_id', function ($query) use ($kolektorId) {
                $query->select('nasabah_id')
                    ->from('kolektor_nasabah')
                    ->where('kolektor_id', $kolektorId)
                    ->where('status', 'aktif');
            })
            ->orWhereIn('user_id', function ($query) use ($kolektorId) {
                $query->select('nasabah_id')
                    ->from('kolektor_nasabah')
                    ->where('kolektor_id', $kolektorId)
                    ->where('status', 'aktif');
            })
            ->with('user')
            ->get();
    }

    public function loadProduk()
    {
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

    public function submit()
    {
        $this->validate([
            'nasabahId' => 'required|exists:users,id',
            'produkId' => 'required|exists:produk_tabungan,id',
            'nominal' => 'required|numeric|min:1000',
            'tanggal_transaksi' => 'required|date',
            'sumber_input' => 'required|in:real_time,susulan',
        ]);

        $isTanggungJawab = KolektorNasabah::where('kolektor_id', Auth::id())
            ->where('nasabah_id', $this->nasabahId)
            ->where('status', 'aktif')
            ->exists();

        if (! $isTanggungJawab) {
            session()->flash('error', 'Nasabah ini bukan tanggung jawab Anda.');

            return;
        }

        $produk = ProdukTabungan::find($this->produkId);

        if ($produk && $produk->tipe === 'paket') {
            $existingActive = KepesertaanPaket::where('nasabah_id', $this->nasabahId)
                ->where('produk_id', $this->produkId)
                ->whereNull('keputusan_akhir')
                ->exists();

            if ($existingActive) {
                session()->flash('error', 'Nasabah ini sudah memiliki kepesertaan aktif untuk produk paket tersebut. Tidak bisa didaftarkan ulang sampai kepesertaan sebelumnya selesai.');

                return;
            }
        }

        DB::beginTransaction();

        try {
            $transaksi = TransaksiSetoran::create([
                'nasabah_id' => $this->nasabahId,
                'produk_id' => $this->produkId,
                'nominal' => $this->nominal,
                'tanggal_transaksi' => $this->tanggal_transaksi,
                'tanggal_input_sistem' => now(),
                'input_by' => Auth::id(),
                'sumber_input' => $this->sumber_input,
                'status' => 'tercatat',
            ]);

            $saldo = SaldoProduk::where('nasabah_id', $this->nasabahId)
                ->where('produk_id', $this->produkId)
                ->lockForUpdate()
                ->first();

            if (! $saldo) {
                $saldo = SaldoProduk::create([
                    'nasabah_id' => $this->nasabahId,
                    'produk_id' => $this->produkId,
                    'saldo' => 0,
                ]);
            }

            $saldo->increment('saldo', $this->nominal);

            if ($produk && $produk->tipe === 'paket') {
                $this->refreshTunggakan($this->nasabahId, $this->produkId);
            }

            DB::commit();

            ActivityLogger::log('setor', 'transaksi_setoran', $transaksi->id, [
                'nasabah_id' => $this->nasabahId,
                'nominal' => $this->nominal,
                'produk_id' => $this->produkId,
            ]);

            ActivityLogger::notify(
                $this->nasabahId,
                'Setoran Dicatat',
                'Setoran Rp '.number_format($this->nominal, 0, ',', '.').' ke produk '.($produk->nama ?? '-').' telah dicatat.',
                'both'
            );

            $this->showSuccess = true;
            $this->reset(['nasabahId', 'produkId', 'nominal', 'catatan']);
            $this->selectedNasabah = null;

            session()->flash('success', 'Setoran berhasil dicatat!');

            $this->dispatch('setoranCreated');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal mencatat setoran: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.kolektor.input-setoran');
    }

    protected function refreshTunggakan(int $nasabahId, int $produkId): void
    {
        $produk = ProdukTabungan::find($produkId);
        if (! $produk || ! $produk->harga_per_hari) {
            return;
        }

        $kepesertaan = KepesertaanPaket::where('nasabah_id', $nasabahId)
            ->where('produk_id', $produkId)
            ->whereNull('keputusan_akhir')
            ->first();

        if (! $kepesertaan) {
            $kepesertaan = KepesertaanPaket::create([
                'nasabah_id' => $nasabahId,
                'produk_id' => $produkId,
                'tanggal_mulai_ikut' => now()->toDateString(),
                'total_seharusnya_terkumpul' => 0,
                'total_aktual_terkumpul' => 0,
                'tunggakan' => 0,
                'status_alert' => 'normal',
            ]);
        }

        $hariBerjalan = Carbon::parse($kepesertaan->tanggal_mulai_ikut)->diffInDays(now());
        $seharusnya = $hariBerjalan * $produk->harga_per_hari;

        $aktual = TransaksiSetoran::where('nasabah_id', $nasabahId)
            ->where('produk_id', $produkId)
            ->where('status', '!=', 'dibatalkan')
            ->sum('nominal');

        $tunggakan = max(0, $seharusnya - $aktual);

        $statusAlert = 'normal';
        if ($tunggakan > 0) {
            $statusAlert = 'peringatan';
            if ($produk->batas_toleransi_tunggakan_hari && $hariBerjalan >= $produk->batas_toleransi_tunggakan_hari) {
                $statusAlert = 'perlu_review';
            }
        }

        $kepesertaan->update([
            'total_seharusnya_terkumpul' => $seharusnya,
            'total_aktual_terkumpul' => $aktual,
            'tunggakan' => $tunggakan,
            'status_alert' => $statusAlert,
        ]);
    }
}
