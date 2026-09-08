<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Models\KolektorNasabah;
use App\Models\LogHandoverKolektor;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class HandoverKolektor extends Component
{
    public $kolektorLamaId = '';

    public $kolektorBaruId = '';

    public $kolektorList = [];

    public $selectedKolektorLama = null;

    public $nasabahList = [];

    public $unsettledCash = 0;

    public $hasUnsettledCash = false;

    public $showConfirmation = false;

    public function mount()
    {
        $this->kolektorList = User::where('role', 'kolektor')
            ->where('status_akun', 'aktif')
            ->get();
    }

    public function updatedKolektorLamaId()
    {
        $this->kolektorBaruId = '';
        $this->selectedKolektorLama = null;
        $this->nasabahList = [];
        $this->unsettledCash = 0;
        $this->hasUnsettledCash = false;
        $this->showConfirmation = false;

        if ($this->kolektorLamaId) {
            $this->selectedKolektorLama = User::find($this->kolektorLamaId);

            $this->unsettledCash = TransaksiSetoran::where('input_by', $this->kolektorLamaId)
                ->where('sudah_disetor_ke_kantor', false)
                ->where('status', 'tercatat')
                ->sum('nominal');

            $this->hasUnsettledCash = $this->unsettledCash > 0;

            $this->nasabahList = KolektorNasabah::where('kolektor_id', $this->kolektorLamaId)
                ->where('status', 'aktif')
                ->with('nasabah')
                ->get();
        }
    }

    public function updatedKolektorBaruId()
    {
        $this->showConfirmation = $this->kolektorBaruId !== '' && ! $this->hasUnsettledCash && $this->nasabahList->count() > 0;
    }

    public function processHandover()
    {
        if ($this->hasUnsettledCash) {
            session()->flash('error', 'Handover diblokir! Kolektor masih memiliki kas yang belum disetor ke kantor.');

            return;
        }

        if (empty($this->kolektorLamaId) || empty($this->kolektorBaruId)) {
            session()->flash('error', 'Pilih kolektor lama dan kolektor pengganti!');

            return;
        }

        if ($this->kolektorLamaId == $this->kolektorBaruId) {
            session()->flash('error', 'Kolektor lama dan baru tidak boleh sama!');

            return;
        }

        if ($this->nasabahList->count() == 0) {
            session()->flash('error', 'Tidak ada nasabah yang perlu dipindahkan!');

            return;
        }

        $this->validate([
            'kolektorBaruId' => 'required|exists:users,id',
        ]);

        $nasabahToNotify = collect();

        DB::beginTransaction();

        try {
            $today = now()->toDateString();

            KolektorNasabah::where('kolektor_id', $this->kolektorLamaId)
                ->where('status', 'aktif')
                ->update([
                    'tanggal_selesai_ditangani' => $today,
                    'status' => 'nonaktif',
                ]);

            foreach ($this->nasabahList as $item) {
                KolektorNasabah::create([
                    'kolektor_id' => $this->kolektorBaruId,
                    'nasabah_id' => $item->nasabah_id,
                    'tanggal_mulai_ditangani' => $today,
                    'status' => 'aktif',
                ]);
            }

            $statusKas = 'lunas';

            LogHandoverKolektor::create([
                'kolektor_lama_id' => $this->kolektorLamaId,
                'kolektor_baru_id' => $this->kolektorBaruId,
                'tanggal_handover' => $today,
                'jumlah_nasabah_dipindah' => $this->nasabahList->count(),
                'status_kas_saat_handover' => $statusKas,
                'diproses_oleh' => auth()->id(),
            ]);

            User::where('id', $this->kolektorLamaId)->update(['status_akun' => 'nonaktif']);

            $kolektorBaru = User::find($this->kolektorBaruId);

            $nasabahToNotify = $this->nasabahList->filter(fn ($item) => $item->nasabah)
                ->map(fn ($item) => [
                    'nasabah_id' => $item->nasabah_id,
                    'name' => $item->nasabah->name,
                ])
                ->values();

            ActivityLogger::log('handover_kolektor', 'log_handover_kolektor', LogHandoverKolektor::latest()->first()->id, [
                'kolektor_lama_id' => $this->kolektorLamaId,
                'kolektor_baru_id' => $this->kolektorBaruId,
                'jumlah_nasabah_dipindah' => $this->nasabahList->count(),
                'status_kas' => $statusKas,
            ]);

            $jumlahNasabah = $this->nasabahList->count();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal memproses handover: '.$e->getMessage());

            return;
        }

        foreach ($nasabahToNotify as $nasabah) {
            try {
                ActivityLogger::notify(
                    $nasabah['nasabah_id'],
                    'Pergantian Kolektor',
                    'Halo '.$nasabah['name'].', mulai hari ini Anda ditangani oleh kolektor baru: '.$kolektorBaru->name.'. Terima kasih.',
                    'in_app'
                );
            } catch (\Exception $e) {
                \Log::error('Gagal kirim notifikasi handover', [
                    'nasabah_id' => $nasabah['nasabah_id'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->kolektorLamaId = '';
        $this->kolektorBaruId = '';
        $this->selectedKolektorLama = null;
        $this->nasabahList = [];
        $this->unsettledCash = 0;
        $this->hasUnsettledCash = false;
        $this->showConfirmation = false;

        $this->kolektorList = User::where('role', 'kolektor')
            ->where('status_akun', 'aktif')
            ->get();

        session()->flash('success', 'Handover kolektor berhasil diproses! '.$jumlahNasabah.' nasabah telah dipindahkan.');
    }

    public function render()
    {
        $riwayat = LogHandoverKolektor::with(['kolektorLama', 'kolektorBaru', 'diprosesOleh'])
            ->latest()
            ->paginate(10);

        return view('livewire.admin.handover-kolektor', compact('riwayat'));
    }
}
