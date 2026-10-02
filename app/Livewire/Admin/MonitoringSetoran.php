<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\SaldoProduk;
use App\Models\TransaksiSetoran;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class MonitoringSetoran extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'admin';
    }

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
            'nominalBaru' => 'required|numeric|min:1|max:1000000000',
            'alasanKoreksi' => 'required|string|max:500',
        ]);

        try {
            $hasil = DB::transaction(function () {
                $setoran = TransaksiSetoran::whereKey($this->selectedId)->lockForUpdate()->first();

                if (! $setoran || $setoran->status !== 'tercatat') {
                    throw new \DomainException('Setoran tidak valid atau sudah dikoreksi/dibatalkan.');
                }

                $nominalLama = (float) $setoran->nominal;
                $nominalBaruVal = round((float) $this->nominalBaru, 2);
                $selisih = round($nominalBaruVal - $nominalLama, 2);

                $saldo = SaldoProduk::where('nasabah_id', $setoran->nasabah_id)
                    ->where('produk_id', $setoran->produk_id)
                    ->lockForUpdate()
                    ->first();

                if ($selisih < 0 && ($saldo === null || bccomp((string) $saldo->saldo, (string) abs($selisih), 2) < 0)) {
                    throw new \DomainException('Saldo nasabah tidak mencukupi untuk koreksi ini.');
                }

                $setoran->update([
                    'status' => 'dikoreksi',
                    'nominal_asli' => $nominalLama,
                    'nominal' => $nominalBaruVal,
                    'dikoreksi_oleh' => auth()->id(),
                    'alasan_koreksi' => $this->alasanKoreksi,
                ]);

                if ($saldo && $selisih > 0) {
                    $saldo->increment('saldo', $selisih);
                } elseif ($saldo && $selisih < 0) {
                    $saldo->decrement('saldo', abs($selisih));
                }

                return [
                    'setoran' => $setoran,
                    'nominalLama' => $nominalLama,
                    'nominalBaru' => $nominalBaruVal,
                ];
            });
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Gagal mengoreksi setoran. Silakan coba lagi.');

            return;
        }

        try {
            ActivityLogger::log('koreksi_setoran', 'transaksi_setoran', $hasil['setoran']->id, [
                'nasabah_id' => $hasil['setoran']->nasabah_id,
                'nominal_lama' => $hasil['nominalLama'],
                'nominal_baru' => $hasil['nominalBaru'],
                'alasan' => $this->alasanKoreksi,
            ]);

            ActivityLogger::notify(
                $hasil['setoran']->nasabah_id,
                'Setoran Dikoreksi',
                'Setoran Rp '.number_format($hasil['nominalLama'], 0, ',', '.').' telah dikoreksi menjadi Rp '.number_format($hasil['nominalBaru'], 0, ',', '.').'.',
                'both'
            );
        } catch (\Throwable $e) {
            report($e);
        }

        $this->showKoreksi = false;
        $this->selectedId = null;
        $this->nominalBaru = '';
        $this->alasanKoreksi = '';

        session()->flash('success', 'Setoran berhasil dikoreksi!');
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

        try {
            $setoran = DB::transaction(function () {
                $setoran = TransaksiSetoran::whereKey($this->selectedBatalId)->lockForUpdate()->first();

                if (! $setoran || $setoran->status !== 'tercatat') {
                    throw new \DomainException('Setoran tidak valid atau sudah diproses.');
                }

                $saldo = SaldoProduk::where('nasabah_id', $setoran->nasabah_id)
                    ->where('produk_id', $setoran->produk_id)
                    ->lockForUpdate()
                    ->first();

                if ($saldo === null || bccomp((string) $saldo->saldo, (string) $setoran->nominal, 2) < 0) {
                    throw new \DomainException('Saldo nasabah tidak mencukupi untuk membatalkan setoran ini.');
                }

                $setoran->update([
                    'status' => 'dibatalkan',
                    'alasan_koreksi' => $this->alasanBatal,
                    'dikoreksi_oleh' => auth()->id(),
                ]);

                $saldo->decrement('saldo', $setoran->nominal);

                return $setoran;
            });
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Gagal membatalkan setoran. Silakan coba lagi.');

            return;
        }

        try {
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
        } catch (\Throwable $e) {
            report($e);
        }

        $this->showBatal = false;
        $this->selectedBatalId = null;
        $this->alasanBatal = '';

        session()->flash('success', 'Setoran berhasil dibatalkan!');
    }
}
