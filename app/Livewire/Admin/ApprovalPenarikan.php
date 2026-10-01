<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ApprovalPenarikan extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public $statusFilter = 'pending';

    public string $alasan = '';

    public bool $showSelesai = false;

    public ?int $selesaiId = null;

    public function toggleSelesai(int $id): void
    {
        $this->selesaiId = $id;
        $this->alasan = '';
        $this->showSelesai = true;
    }

    public function render()
    {
        $penarikan = TransaksiPenarikan::with(['nasabah', 'produk'])
            ->where('status', $this->statusFilter)
            ->latest()
            ->paginate(10);

        return view('livewire.admin.approval-penarikan', compact('penarikan'));
    }

    public function approve($id)
    {
        $penarikan = DB::transaction(function () use ($id) {
            $penarikan = TransaksiPenarikan::where('id', $id)->lockForUpdate()->first();

            if (! $penarikan || $penarikan->status !== 'pending') {
                return null;
            }

            $saldo = SaldoProduk::where('nasabah_id', $penarikan->nasabah_id)
                ->where('produk_id', $penarikan->produk_id)
                ->lockForUpdate()
                ->first();

            if (! $saldo || $saldo->saldo < $penarikan->nominal_diminta) {
                session()->flash('error', 'Saldo nasabah tidak mencukupi!');

                return null;
            }

            $saldo->decrement('saldo', $penarikan->nominal_diminta);

            $penarikan->update([
                'status' => 'approved',
                'disetujui_oleh' => auth()->id(),
                'waktu_approval' => now(),
            ]);

            ActivityLogger::log('approve_penarikan', 'transaksi_penarikan', $id, [
                'nasabah_id' => $penarikan->nasabah_id,
                'nominal' => $penarikan->nominal_diminta,
            ]);

            return $penarikan;
        });

        if ($penarikan) {
            try {
                ActivityLogger::notify(
                    $penarikan->nasabah_id,
                    'Penarikan Disetujui',
                    'Penarikan Rp '.number_format($penarikan->nominal_diminta, 0, ',', '.').' telah disetujui admin.',
                    'both'
                );
            } catch (\Exception $e) {
                \Log::error('Gagal kirim notifikasi penarikan disetujui', [
                    'nasabah_id' => $penarikan->nasabah_id,
                    'error' => $e->getMessage(),
                ]);
            }

            session()->flash('success', 'Penarikan disetujui!');
        }
    }

    public function reject($id)
    {
        $penarikan = DB::transaction(function () use ($id) {
            $penarikan = TransaksiPenarikan::where('id', $id)->lockForUpdate()->first();

            if (! $penarikan || $penarikan->status !== 'pending') {
                return null;
            }

            $penarikan->update([
                'status' => 'ditolak',
                'disetujui_oleh' => auth()->id(),
                'waktu_approval' => now(),
            ]);

            ActivityLogger::log('reject_penarikan', 'transaksi_penarikan', $id, [
                'nasabah_id' => $penarikan->nasabah_id,
            ]);

            return $penarikan;
        });

        if ($penarikan) {
            try {
                ActivityLogger::notify(
                    $penarikan->nasabah_id,
                    'Penarikan Ditolak',
                    'Pengajuan penarikan Rp '.number_format($penarikan->nominal_diminta, 0, ',', '.').' ditolak oleh admin.',
                    'both'
                );
            } catch (\Exception $e) {
                \Log::error('Gagal kirim notifikasi penarikan ditolak', [
                    'nasabah_id' => $penarikan->nasabah_id,
                    'error' => $e->getMessage(),
                ]);
            }

            session()->flash('success', 'Penarikan ditolak.');
        }
    }

    public function selesai($id)
    {
        $this->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $penarikan = TransaksiPenarikan::find($id);

        if ($penarikan && $penarikan->jalur_pengajuan === 'offline' && $penarikan->lokasi_pengambilan === 'rumah_kolektor') {
            session()->flash('error', 'Penarikan ini diserahkan oleh kolektor di rumah nasabah — harus diselesaikan lewat verifikasi PIN oleh kolektor, bukan admin.');

            return;
        }

        if ($penarikan && $penarikan->status === 'approved') {
            $penarikan->update([
                'status' => 'selesai',
                'waktu_pencairan' => now(),
                'metode_verifikasi' => 'manual_admin',
            ]);

            ActivityLogger::log('selesai_penarikan', 'transaksi_penarikan', $id, [
                'nasabah_id' => $penarikan->nasabah_id,
                'alasan' => $this->alasan,
            ]);

            $this->reset('alasan', 'selesaiId');
            $this->showSelesai = false;

            session()->flash('success', 'Penarikan ditandai selesai!');
        }
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }
}
