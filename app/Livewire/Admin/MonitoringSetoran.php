<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Models\SaldoProduk;
use App\Models\TransaksiSetoran;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class MonitoringSetoran extends Component
{
    use WithPagination;

    public $search = '';

    public $statusFilter = '';

    public $tanggalFilter = '';

    public $showKoreksi = false;

    public $selectedId = null;

    public $nominalBaru = '';

    public $alasanKoreksi = '';

    public $showBatal = false;

    public $selectedBatalId = null;

    public $alasanBatal = '';

    public function render()
    {
        $query = TransaksiSetoran::with(['nasabah', 'produk', 'inputBy', 'dikoreksiOleh']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('nasabah', fn ($u) => $u->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('produk', fn ($p) => $p->where('nama', 'like', "%{$this->search}%"));
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->tanggalFilter) {
            $query->whereDate('tanggal_transaksi', $this->tanggalFilter);
        }

        $setoran = $query->latest('tanggal_transaksi')->paginate(15);

        return view('livewire.admin.monitoring-setoran', compact('setoran'));
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function toggleKoreksi($id)
    {
        $this->showKoreksi = true;
        $this->selectedId = $id;
        $setoran = TransaksiSetoran::find($id);
        $this->nominalBaru = $setoran->nominal ?? '';
        $this->alasanKoreksi = '';
    }

    public function koreksi()
    {
        $this->validate([
            'nominalBaru' => 'required|numeric|min:0',
            'alasanKoreksi' => 'required|string|max:500',
        ]);

        $setoran = TransaksiSetoran::find($this->selectedId);
        if (! $setoran || $setoran->status !== 'tercatat') {
            session()->flash('error', 'Setoran tidak valid atau sudah dikoreksi/dibatalkan.');

            return;
        }

        $nominalLama = (float) $setoran->nominal;
        $nominalBaruVal = (float) $this->nominalBaru;
        $selisih = $nominalBaruVal - $nominalLama;

        DB::beginTransaction();

        try {
            $setoran->update([
                'status' => 'dikoreksi',
                'nominal_asli' => $nominalLama,
                'nominal' => $nominalBaruVal,
                'dikoreksi_oleh' => auth()->id(),
                'alasan_koreksi' => $this->alasanKoreksi,
            ]);

            $saldo = SaldoProduk::where('nasabah_id', $setoran->nasabah_id)
                ->where('produk_id', $setoran->produk_id)
                ->lockForUpdate()
                ->first();

            if ($saldo) {
                if ($selisih > 0) {
                    $saldo->increment('saldo', $selisih);
                } elseif ($selisih < 0) {
                    $saldo->decrement('saldo', abs($selisih));
                }
            }

            DB::commit();

            ActivityLogger::log('koreksi_setoran', 'transaksi_setoran', $setoran->id, [
                'nasabah_id' => $setoran->nasabah_id,
                'nominal_lama' => $nominalLama,
                'nominal_baru' => $nominalBaruVal,
                'alasan' => $this->alasanKoreksi,
            ]);

            ActivityLogger::notify(
                $setoran->nasabah_id,
                'Setoran Dikoreksi',
                'Setoran Rp '.number_format($nominalLama, 0, ',', '.').' telah dikoreksi menjadi Rp '.number_format($nominalBaruVal, 0, ',', '.').'.',
                'both'
            );

            $this->showKoreksi = false;
            $this->selectedId = null;
            $this->nominalBaru = '';
            $this->alasanKoreksi = '';

            session()->flash('success', 'Setoran berhasil dikoreksi!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal mengoreksi setoran: '.$e->getMessage());
        }
    }

    public function toggleBatal($id)
    {
        $this->showBatal = true;
        $this->selectedBatalId = $id;
        $this->alasanBatal = '';
    }

    public function batal()
    {
        $this->validate([
            'alasanBatal' => 'required|string|max:500',
        ]);

        $setoran = TransaksiSetoran::find($this->selectedBatalId);
        if (! $setoran || $setoran->status !== 'tercatat') {
            session()->flash('error', 'Setoran tidak valid atau sudah diproses.');

            return;
        }

        DB::beginTransaction();

        try {
            $setoran->update([
                'status' => 'dibatalkan',
                'alasan_koreksi' => $this->alasanBatal,
                'dikoreksi_oleh' => auth()->id(),
            ]);

            $saldo = SaldoProduk::where('nasabah_id', $setoran->nasabah_id)
                ->where('produk_id', $setoran->produk_id)
                ->lockForUpdate()
                ->first();

            if ($saldo) {
                $saldo->decrement('saldo', $setoran->nominal);
            }

            DB::commit();

            ActivityLogger::log('batal_setoran', 'transaksi_setoran', $setoran->id, [
                'nasabah_id' => $setoran->nasabah_id,
                'nominal' => $setoran->nominal,
                'alasan' => $this->alasanBatal,
            ]);

            ActivityLogger::notify(
                $setoran->nasabah_id,
                'Setoran Dibatalkan',
                'Setoran Rp '.number_format($setoran->nominal, 0, ',', '.').' telah dibatalkan oleh admin.',
                'both'
            );

            $this->showBatal = false;
            $this->selectedBatalId = null;
            $this->alasanBatal = '';

            session()->flash('success', 'Setoran berhasil dibatalkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal membatalkan setoran: '.$e->getMessage());
        }
    }
}
