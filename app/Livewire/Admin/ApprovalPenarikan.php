<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\AdminSetting;
use App\Models\KolektorNasabah;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\User;
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
        $penarikan = TransaksiPenarikan::with(['nasabah', 'produk', 'disetujuiOleh'])
            ->where('status', $this->statusFilter)
            ->latest()
            ->paginate(10);

        return view('livewire.admin.approval-penarikan', compact('penarikan'));
    }

    /**
     * Persetujuan ganda (D9) berlaku bila batas > 0, minimal dua admin, dan nominal melebihi batas.
     */
    protected function perluDuaApprover(TransaksiPenarikan $penarikan): bool
    {
        $batas = (int) AdminSetting::get('penarikan_batas_dua_approver', '0');

        if ($batas <= 0) {
            return false;
        }

        if (User::where('role', 'admin')->count() < 2) {
            return false;
        }

        return (float) $penarikan->nominal_diminta > $batas;
    }

    public function approve($id)
    {
        $hasil = DB::transaction(function () use ($id) {
            $penarikan = TransaksiPenarikan::where('id', $id)->lockForUpdate()->first();

            if (! $penarikan || $penarikan->status !== 'pending') {
                return null;
            }

            $faseKedua = $penarikan->disetujui_oleh !== null;

            if ($faseKedua && (int) $penarikan->disetujui_oleh === (int) auth()->id()) {
                return ['error' => 'Anda sudah menyetujui pengajuan ini. Persetujuan kedua harus admin lain.'];
            }

            $saldo = SaldoProduk::where('nasabah_id', $penarikan->nasabah_id)
                ->where('produk_id', $penarikan->produk_id)
                ->lockForUpdate()
                ->first();

            if (! $saldo || $saldo->saldo < $penarikan->nominal_diminta) {
                session()->flash('error', 'Saldo nasabah tidak mencukupi!');

                return null;
            }

            if (! $faseKedua && $this->perluDuaApprover($penarikan)) {
                $penarikan->update([
                    'disetujui_oleh' => auth()->id(),
                ]);

                $kolektorPenanggungJawab = KolektorNasabah::where('nasabah_id', $penarikan->nasabah_id)
                    ->where('status', 'aktif')
                    ->value('kolektor_id');

                ActivityLogger::log('approve_penarikan_pertama', 'transaksi_penarikan', $id, [
                    'nasabah_id' => $penarikan->nasabah_id,
                    'nominal' => $penarikan->nominal_diminta,
                    'kolektor_id' => $kolektorPenanggungJawab,
                ]);

                return ['tahap' => 'pertama', 'penarikan' => $penarikan];
            }

            $saldo->decrement('saldo', $penarikan->nominal_diminta);

            $penarikan->update([
                'status' => 'approved',
                'disetujui_oleh' => $faseKedua ? $penarikan->disetujui_oleh : auth()->id(),
                'disetujui_oleh_2' => $faseKedua ? auth()->id() : null,
                'waktu_approval' => now(),
            ]);

            $kolektorPenanggungJawab = KolektorNasabah::where('nasabah_id', $penarikan->nasabah_id)
                ->where('status', 'aktif')
                ->value('kolektor_id');

            ActivityLogger::log('approve_penarikan', 'transaksi_penarikan', $id, [
                'nasabah_id' => $penarikan->nasabah_id,
                'nominal' => $penarikan->nominal_diminta,
                'kolektor_id' => $kolektorPenanggungJawab,
            ]);

            return ['tahap' => 'final', 'penarikan' => $penarikan];
        });

        if (is_array($hasil) && isset($hasil['error'])) {
            session()->flash('error', $hasil['error']);

            return;
        }

        if (is_array($hasil) && $hasil['tahap'] === 'pertama') {
            session()->flash('success', 'Persetujuan pertama tercatat. Menunggu persetujuan admin kedua.');

            return;
        }

        $penarikan = is_array($hasil) ? $hasil['penarikan'] : null;

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

        $hasil = DB::transaction(function () use ($id) {
            $penarikan = TransaksiPenarikan::whereKey($id)->lockForUpdate()->first();

            if (! $penarikan || $penarikan->status !== 'approved') {
                return null;
            }

            if ($penarikan->disetujui_oleh_2 !== null && in_array(
                (int) auth()->id(),
                array_map('intval', [$penarikan->disetujui_oleh, $penarikan->disetujui_oleh_2]),
                true
            )) {
                return ['error' => 'Penarikan dengan persetujuan ganda harus ditandai selesai oleh admin lain.'];
            }

            $isOverrideRumah = $penarikan->lokasi_pengambilan === 'rumah_kolektor';

            if ($isOverrideRumah && mb_strlen($this->alasan) < 10) {
                return ['error' => 'Alasan override minimal 10 karakter.'];
            }

            $nasabah = $penarikan->nasabah;
            $belumGantiPin = $nasabah instanceof User && $nasabah->harus_ganti_pin;

            if ($isOverrideRumah && ! $belumGantiPin) {
                return ['error' => 'Penarikan ini diserahkan oleh kolektor di rumah nasabah — harus diselesaikan lewat verifikasi PIN oleh kolektor, bukan admin.'];
            }

            $penarikan->update([
                'status' => 'selesai',
                'waktu_pencairan' => now(),
                'metode_verifikasi' => 'manual_admin',
            ]);

            $detail = [
                'nasabah_id' => $penarikan->nasabah_id,
                'alasan' => $this->alasan,
            ];

            if ($isOverrideRumah) {
                $detail['risiko_tinggi'] = true;
                $detail['override_rumah'] = true;
            }

            ActivityLogger::log(
                $isOverrideRumah ? 'selesai_penarikan_override' : 'selesai_penarikan',
                'transaksi_penarikan',
                $id,
                $detail,
            );

            return $penarikan;
        });

        if (is_array($hasil)) {
            session()->flash('error', $hasil['error']);

            return;
        }

        if ($hasil) {
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
