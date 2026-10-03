<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
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
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'admin';
    }

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

    public ?int $rejectingId = null;

    public string $rejectAlasan = '';

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

    public function startReject(int $id): void
    {
        $this->rejectingId = $id;
        $this->rejectAlasan = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingId = null;
        $this->rejectAlasan = '';
    }

    public function rejectSubmission(): void
    {
        $this->validate([
            'rejectAlasan' => 'required|string|max:500',
        ]);

        $id = (int) $this->rejectingId;

        try {
            DB::transaction(function () use ($id) {
                $setoran = SetoranKolektorKantor::whereKey($id)->lockForUpdate()->first();

                if (! $setoran || $setoran->status !== 'pending') {
                    throw new \DomainException('Pengajuan setoran ini sudah diproses atau tidak valid.');
                }

                $setoran->update(['status' => 'dibatalkan']);

                TransaksiSetoran::where('setoran_kolektor_id', $setoran->id)
                    ->update(['setoran_kolektor_id' => null]);
            });
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Gagal menolak pengajuan setoran. Silakan coba lagi.');

            return;
        }

        $setoran = SetoranKolektorKantor::findOrFail($id);
        $alasan = $this->rejectAlasan;

        ActivityLogger::log('tolak_setoran_kantor', 'setoran_kolektor_kantor', $id, [
            'kolektor_id' => $setoran->kolektor_id,
            'alasan' => $alasan,
        ]);

        ActivityLogger::notify(
            (int) $setoran->kolektor_id,
            'Pengajuan setoran ditolak',
            'Pengajuan setoran ke kantor Anda ditolak oleh admin. Alasan: '.$alasan
        );

        $this->rejectingId = null;
        $this->rejectAlasan = '';
        $this->loadPendingSubmissions();
        session()->flash('success', 'Pengajuan setoran berhasil ditolak.');
    }

    public function processSubmission($id)
    {
        $this->validate([
            'processTotalDiterima' => 'required|numeric|min:0',
        ]);

        try {
            $hasil = DB::transaction(function () use ($id) {
                $setoran = SetoranKolektorKantor::whereKey($id)->lockForUpdate()->first();

                if (! $setoran || $setoran->status !== 'pending') {
                    throw new \DomainException('Pengajuan setoran ini sudah diproses atau tidak valid.');
                }

                $totalSeharusnya = (float) TransaksiSetoran::where('setoran_kolektor_id', $setoran->id)
                    ->where('status', '!=', 'dibatalkan')
                    ->lockForUpdate()
                    ->sum('nominal');

                $selisih = round((float) $this->processTotalDiterima - $totalSeharusnya, 2);

                $status = match (true) {
                    $selisih > 0 => 'lebih',
                    $selisih < 0 => 'kurang',
                    default => 'cocok',
                };

                if ($selisih != 0 && empty($this->processKeterangan)) {
                    throw new \DomainException('Keterangan wajib diisi jika ada selisih!');
                }

                $setoran->update([
                    'total_seharusnya' => $totalSeharusnya,
                    'total_diterima' => $this->processTotalDiterima,
                    'selisih' => $selisih,
                    'keterangan_selisih' => $this->processKeterangan ?: $setoran->keterangan_selisih,
                    'diterima_oleh' => auth()->id(),
                    'status' => $status,
                ]);

                TransaksiSetoran::where('setoran_kolektor_id', $setoran->id)
                    ->update(['sudah_disetor_ke_kantor' => true]);

                return $setoran->refresh();
            });
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Gagal memproses rekonsiliasi. Silakan coba lagi.');

            return;
        }

        ActivityLogger::log('rekon', 'setoran_kolektor_kantor', $hasil->id, [
            'kolektor_id' => $hasil->kolektor_id,
            'total_seharusnya' => $hasil->total_seharusnya,
            'total_diterima' => $hasil->total_diterima,
            'selisih' => $hasil->selisih,
            'status' => $hasil->status,
        ]);

        $this->processingId = null;
        $this->processTotalDiterima = '';
        $this->processKeterangan = '';
        $this->loadPendingSubmissions();
        session()->flash('success', 'Rekonsiliasi kas berhasil diproses!');
    }

    public function render()
    {
        $riwayat = SetoranKolektorKantor::with(['kolektor', 'diterimaOleh'])->latest()->paginate(10);

        return view('livewire.admin.rekonsiliasi-kas', compact('riwayat'));
    }

    public function updatedKolektorId()
    {
        if ($this->kolektorId) {
            $this->totalSeharusnya = TransaksiSetoran::belumDisetor()
                ->where('input_by', $this->kolektorId)
                ->whereNull('setoran_kolektor_id')
                ->sum('nominal');

            $this->detailTransaksi = TransaksiSetoran::belumDisetor()
                ->where('input_by', $this->kolektorId)
                ->whereNull('setoran_kolektor_id')
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

        $kolektorId = $this->kolektorId;

        try {
            $hasil = DB::transaction(function () use ($kolektorId) {
                $adaPengajuan = SetoranKolektorKantor::where('kolektor_id', $kolektorId)
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->exists();

                if ($adaPengajuan) {
                    throw new \DomainException('Kolektor ini sudah memiliki pengajuan setoran yang masih menunggu proses admin. Proses pengajuan tersebut terlebih dahulu.');
                }

                $totalSeharusnya = (float) TransaksiSetoran::belumDisetor()
                    ->where('input_by', $kolektorId)
                    ->whereNull('setoran_kolektor_id')
                    ->lockForUpdate()
                    ->sum('nominal');

                if ($totalSeharusnya <= 0) {
                    throw new \DomainException('Tidak ada setoran yang perlu direkonsiliasi.');
                }

                $selisih = round((float) $this->totalDiterima - $totalSeharusnya, 2);

                $status = match (true) {
                    $selisih > 0 => 'lebih',
                    $selisih < 0 => 'kurang',
                    default => 'cocok',
                };

                if ($selisih != 0 && empty($this->keterangan)) {
                    throw new \DomainException('Keterangan wajib diisi jika ada selisih!');
                }

                $setoran = SetoranKolektorKantor::create([
                    'kolektor_id' => $kolektorId,
                    'tanggal_setor' => now()->toDateString(),
                    'total_seharusnya' => $totalSeharusnya,
                    'total_diterima' => $this->totalDiterima,
                    'selisih' => $selisih,
                    'keterangan_selisih' => $this->keterangan ?: null,
                    'diterima_oleh' => auth()->id(),
                    'status' => $status,
                ]);

                TransaksiSetoran::belumDisetor()
                    ->where('input_by', $kolektorId)
                    ->whereNull('setoran_kolektor_id')
                    ->update(['setoran_kolektor_id' => $setoran->id]);

                TransaksiSetoran::where('setoran_kolektor_id', $setoran->id)
                    ->update(['sudah_disetor_ke_kantor' => true]);

                return $setoran->refresh();
            });
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Gagal menyimpan rekonsiliasi. Silakan coba lagi.');

            return;
        }

        ActivityLogger::log('rekon', 'setoran_kolektor_kantor', $hasil->id, [
            'kolektor_id' => $hasil->kolektor_id,
            'total_seharusnya' => $hasil->total_seharusnya,
            'total_diterima' => $hasil->total_diterima,
            'selisih' => $hasil->selisih,
            'status' => $hasil->status,
        ]);

        $this->reset(['kolektorId', 'totalDiterima', 'keterangan', 'showForm', 'totalSeharusnya']);
        $this->detailTransaksi = [];
        $this->loadPendingSubmissions();
        session()->flash('success', 'Rekonsiliasi kas berhasil disimpan!');
    }
}
