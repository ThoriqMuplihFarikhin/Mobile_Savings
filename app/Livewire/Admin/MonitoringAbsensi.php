<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\AbsensiKolektor;
use App\Models\IzinKolektor;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Monitoring Absensi')]
class MonitoringAbsensi extends Component
{
    use AuthorizesRole;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public string $tanggal;

    public ?int $selectedKolektorId = null;

    /** @var array<int, string> */
    public array $catatanIzin = [];

    public int $jumlahIzin = 0;

    public int $jumlahBelumAbsen = 0;

    public function mount(): void
    {
        $this->tanggal = now()->toDateString();
    }

    public function selectKolektor(?int $id): void
    {
        $this->selectedKolektorId = $id;
    }

    public function prosesIzin(int $id, string $status): void
    {
        if (! in_array($status, ['disetujui', 'ditolak'], true)) {
            return;
        }

        $izin = DB::transaction(function () use ($id, $status) {
            $izin = IzinKolektor::whereKey($id)->lockForUpdate()->first();

            if (! $izin || $izin->status !== 'pending') {
                return null;
            }

            $catatan = trim((string) ($this->catatanIzin[$id] ?? ''));

            $izin->update([
                'status' => $status,
                'diproses_oleh' => auth()->id(),
                'catatan_admin' => $catatan !== '' ? $catatan : null,
            ]);

            return $izin;
        });

        if (! $izin) {
            session()->flash('error', 'Pengajuan izin tidak dapat diproses.');

            return;
        }

        ActivityLogger::log('proses_izin', 'izin_kolektor', $izin->id, [
            'kolektor_id' => $izin->kolektor_id,
            'status' => $status,
        ]);

        session()->flash('success', 'Pengajuan izin berhasil '.($status === 'disetujui' ? 'disetujui' : 'ditolak').'.');
    }

    public function render(): View
    {
        $tanggal = Carbon::parse($this->tanggal);
        $kolektors = User::where('role', 'kolektor')->get();
        $absensi = AbsensiKolektor::whereIn('kolektor_id', $kolektors->pluck('id'))
            ->where('tanggal', $this->tanggal)
            ->get()
            ->keyBy('kolektor_id');

        $izinHariIni = IzinKolektor::whereIn('kolektor_id', $kolektors->pluck('id'))
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', $this->tanggal)
            ->where('tanggal_selesai', '>=', $this->tanggal)
            ->pluck('kolektor_id');

        $this->jumlahIzin = $izinHariIni->reject(fn ($id) => $absensi->has($id))->count();
        $this->jumlahBelumAbsen = $kolektors->count() - $absensi->count() - $this->jumlahIzin;

        $izinPending = IzinKolektor::with('kolektor')
            ->whereIn('kolektor_id', $kolektors->pluck('id'))
            ->where('status', 'pending')
            ->latest()
            ->get();

        $selectedAbsen = $this->selectedKolektorId
            ? $absensi->get($this->selectedKolektorId)
            : null;

        return view('livewire.admin.monitoring-absensi', compact('kolektors', 'absensi', 'tanggal', 'selectedAbsen', 'izinHariIni', 'izinPending'));
    }
}
