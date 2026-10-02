<?php

namespace App\Livewire\Admin;

use App\Actions\Kolektor\TugaskanNasabahAction;
use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\KolektorNasabah;
use App\Models\LogHandoverKolektor;
use App\Models\TransaksiSetoran;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class HandoverKolektor extends Component
{
    use AuthorizesRole;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public $kolektorLamaId = '';

    public $kolektorBaruId = '';

    public $kolektorList = [];

    public $selectedKolektorLama = null;

    public $nasabahList = [];

    #[Locked]
    public $unsettledCash = 0;

    #[Locked]
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

            $this->unsettledCash = TransaksiSetoran::belumDisetor()
                ->where('input_by', $this->kolektorLamaId)
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
            'kolektorLamaId' => ['required', Rule::exists('users', 'id')->where('role', 'kolektor')],
            'kolektorBaruId' => ['required', Rule::exists('users', 'id')->where('role', 'kolektor')->where('status_akun', 'aktif')],
        ]);

        $nasabahToNotify = collect();

        DB::beginTransaction();

        try {
            $today = now()->toDateString();

            $adaKas = TransaksiSetoran::belumDisetor()
                ->where('input_by', $this->kolektorLamaId)
                ->lockForUpdate()
                ->exists();

            $nasabahIds = KolektorNasabah::where('kolektor_id', $this->kolektorLamaId)
                ->where('status', 'aktif')
                ->lockForUpdate()
                ->pluck('nasabah_id');

            if ($adaKas) {
                DB::rollBack();
                $this->hasUnsettledCash = true;
                $this->unsettledCash = (float) TransaksiSetoran::belumDisetor()
                    ->where('input_by', $this->kolektorLamaId)
                    ->sum('nominal');
                session()->flash('error', 'Handover diblokir! Kolektor masih memiliki kas yang belum disetor ke kantor.');

                return;
            }

            if ($nasabahIds->isEmpty()) {
                DB::rollBack();
                session()->flash('error', 'Tidak ada nasabah yang perlu dipindahkan!');

                return;
            }

            KolektorNasabah::where('kolektor_id', $this->kolektorLamaId)
                ->where('status', 'aktif')
                ->update([
                    'tanggal_selesai_ditangani' => $today,
                    'status' => 'nonaktif',
                    'aktif_unik' => null,
                ]);

            foreach ($nasabahIds as $nasabahId) {
                app(TugaskanNasabahAction::class)->execute(
                    (int) $this->kolektorBaruId,
                    (int) $nasabahId,
                );
            }

            $statusKas = 'lunas';

            $log = LogHandoverKolektor::create([
                'kolektor_lama_id' => $this->kolektorLamaId,
                'kolektor_baru_id' => $this->kolektorBaruId,
                'tanggal_handover' => $today,
                'jumlah_nasabah_dipindah' => $nasabahIds->count(),
                'status_kas_saat_handover' => $statusKas,
                'diproses_oleh' => auth()->id(),
            ]);

            User::where('id', $this->kolektorLamaId)->update(['status_akun' => 'terkunci']);

            $kolektorBaru = User::find($this->kolektorBaruId);

            $nasabahToNotify = User::whereIn('id', $nasabahIds)->get()
                ->map(fn (User $nasabah) => [
                    'nasabah_id' => $nasabah->id,
                    'name' => $nasabah->name,
                ])
                ->values();

            ActivityLogger::log('handover_kolektor', 'log_handover_kolektor', $log->id, [
                'kolektor_lama_id' => $this->kolektorLamaId,
                'kolektor_baru_id' => $this->kolektorBaruId,
                'jumlah_nasabah_dipindah' => $nasabahIds->count(),
                'status_kas' => $statusKas,
            ]);

            $jumlahNasabah = $nasabahIds->count();

            DB::commit();
        } catch (DomainException $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', $e->getMessage());

            return;
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', 'Gagal memproses handover. Silakan coba lagi.');

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
