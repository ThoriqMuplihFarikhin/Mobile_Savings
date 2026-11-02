<?php

namespace App\Livewire\Kolektor;

use App\Livewire\Actions\Logout;
use App\Livewire\Concerns\AuthorizesRole;
use App\Livewire\Concerns\HasNotifikasiWaToggle;
use App\Models\AbsensiKolektor;
use App\Models\JadwalKunjungan;
use App\Models\KolektorNasabah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

#[Layout('layouts.mobile')]
class Pengaturan extends Component
{
    use AuthorizesRole;
    use HasNotifikasiWaToggle;

    protected function requiredRole(): string
    {
        return 'kolektor';
    }

    /**
     * Info khusus kolektor: apakah masih ada setoran yang belum
     * disetor ke kantor (agar diingatkan lewat halaman pengaturan).
     */
    public bool $adaKasBelumDisetor = false;

    public function mount(): void
    {
        $this->mountNotifikasiWaToggle();
        $this->adaKasBelumDisetor = Auth::user()->hasUnsettledCash();
    }

    public function logout(): Redirector|RedirectResponse
    {
        return app(Logout::class)();
    }

    public function render()
    {
        $userId = (int) Auth::id();

        $totalKunjunganLancar = JadwalKunjungan::where('kolektor_id', $userId)
            ->where('status_kunjungan', 'dikunjungi')
            ->count();

        $totalNasabahBinaan = KolektorNasabah::where('kolektor_id', $userId)
            ->where('status', 'aktif')
            ->count();

        $streakTepatWaktu = AbsensiKolektor::where('kolektor_id', $userId)
            ->whereNotNull('waktu_masuk')
            ->count();

        return view('livewire.kolektor.pengaturan', compact(
            'totalKunjunganLancar',
            'totalNasabahBinaan',
            'streakTepatWaktu'
        ));
    }
}
