<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class RekonsiliasiKas extends Component
{
    use WithPagination;

    public $kolektorId = '';

    public $totalSeharusnya = 0;

    public $totalDiterima = '';

    public $keterangan = '';

    public $kolektorList = [];

    public $detailTransaksi = [];

    public $showForm = false;

    public $pendingSubmissions = [];

    public $processingId = null;

    public $processTotalDiterima = '';

    public $processKeterangan = '';

    public function mount()
    {
        $this->kolektorList = User::where('role', 'kolektor')->where('status_akun', 'aktif')->get();
        $this->loadPendingSubmissions();
    }

    public function loadPendingSubmissions()
    {
        $this->pendingSubmissions = SetoranKolektorKantor::with(['kolektor'])
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    public function startProcess($id)
    {
        $this->processingId = $id;
        $this->processTotalDiterima = '';
        $this->processKeterangan = '';
    }

    public function cancelProcess()
    {
        $this->processingId = null;
        $this->processTotalDiterima = '';
        $this->processKeterangan = '';
    }

    public function processSubmission($id)
    {
        $this->validate([
            'processTotalDiterima' => 'required|numeric|min:0',
        ]);

        $setoran = SetoranKolektorKantor::findOrFail($id);

        $selisih = $this->processTotalDiterima - $setoran->total_seharusnya;
        $status = match (true) {
            $selisih == 0 => 'cocok',
            $selisih > 0 => 'lebih',
            default => 'kurang',
        };

        if ($selisih != 0 && empty($this->processKeterangan)) {
            session()->flash('error', 'Keterangan wajib diisi jika ada selisih!');

            return;
        }

        DB::beginTransaction();

        try {
            $setoran->update([
                'total_diterima' => $this->processTotalDiterima,
                'selisih' => $selisih,
                'keterangan_selisih' => $this->processKeterangan ?: $setoran->keterangan_selisih,
                'diterima_oleh' => auth()->id(),
                'status' => $status,
            ]);

            TransaksiSetoran::where('setoran_kolektor_id', $setoran->id)
                ->update(['sudah_disetor_ke_kantor' => true]);

            DB::commit();

            ActivityLogger::log('rekon', 'setoran_kolektor_kantor', $setoran->id, [
                'kolektor_id' => $setoran->kolektor_id,
                'total_seharusnya' => $setoran->total_seharusnya,
                'total_diterima' => $this->processTotalDiterima,
                'selisih' => $selisih,
                'status' => $status,
            ]);

            $this->processingId = null;
            $this->processTotalDiterima = '';
            $this->processKeterangan = '';
            $this->loadPendingSubmissions();
            session()->flash('success', 'Rekonsiliasi kas berhasil diproses!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal memproses: '.$e->getMessage());
        }
    }

    public function render()
    {
        $riwayat = SetoranKolektorKantor::with(['kolektor', 'diterimaOleh'])->latest()->paginate(10);

        return view('livewire.admin.rekonsiliasi-kas', compact('riwayat'));
    }

    public function updatedKolektorId()
    {
        if ($this->kolektorId) {
            $this->totalSeharusnya = TransaksiSetoran::where('input_by', $this->kolektorId)
                ->where('sudah_disetor_ke_kantor', false)
                ->where('status', 'tercatat')
                ->sum('nominal');

            $this->detailTransaksi = TransaksiSetoran::where('input_by', $this->kolektorId)
                ->where('sudah_disetor_ke_kantor', false)
                ->where('status', 'tercatat')
                ->with(['nasabah', 'produk'])
                ->get();

            $this->showForm = true;
        } else {
            $this->totalSeharusnya = 0;
            $this->detailTransaksi = [];
            $this->showForm = false;
        }
    }

    public function submit()
    {
        $this->validate([
            'kolektorId' => 'required|exists:users,id',
            'totalDiterima' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $totalSeharusnya = TransaksiSetoran::where('input_by', $this->kolektorId)
                ->where('sudah_disetor_ke_kantor', false)
                ->where('status', 'tercatat')
                ->lockForUpdate()
                ->sum('nominal');

            $selisih = $this->totalDiterima - $totalSeharusnya;
            $status = match (true) {
                $selisih == 0 => 'cocok',
                $selisih > 0 => 'lebih',
                default => 'kurang',
            };

            if ($selisih != 0 && empty($this->keterangan)) {
                DB::rollBack();
                session()->flash('error', 'Keterangan wajib diisi jika ada selisih!');

                return;
            }

            $setoran = SetoranKolektorKantor::create([
                'kolektor_id' => $this->kolektorId,
                'tanggal_setor' => now()->toDateString(),
                'total_seharusnya' => $totalSeharusnya,
                'total_diterima' => $this->totalDiterima,
                'selisih' => $selisih,
                'keterangan_selisih' => $this->keterangan ?: null,
                'diterima_oleh' => auth()->id(),
                'status' => $status,
            ]);

            TransaksiSetoran::where('input_by', $this->kolektorId)
                ->where('sudah_disetor_ke_kantor', false)
                ->where('status', 'tercatat')
                ->update(['setoran_kolektor_id' => $setoran->id]);

            TransaksiSetoran::where('setoran_kolektor_id', $setoran->id)
                ->update(['sudah_disetor_ke_kantor' => true]);

            DB::commit();

            ActivityLogger::log('rekon', 'setoran_kolektor_kantor', $setoran->id, [
                'kolektor_id' => $this->kolektorId,
                'total_seharusnya' => $totalSeharusnya,
                'total_diterima' => $this->totalDiterima,
                'selisih' => $selisih,
                'status' => $status,
            ]);

            $this->reset(['kolektorId', 'totalDiterima', 'keterangan', 'showForm', 'totalSeharusnya']);
            $this->detailTransaksi = [];
            $this->loadPendingSubmissions();
            session()->flash('success', 'Rekonsiliasi kas berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan: '.$e->getMessage());
        }
    }
}
