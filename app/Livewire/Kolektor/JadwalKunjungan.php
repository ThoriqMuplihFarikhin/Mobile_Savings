<?php

namespace App\Livewire\Kolektor;

use App\Livewire\Concerns\AuthorizesRole;
use App\Livewire\Concerns\ValidatesKolektorNasabah;
use App\Models\JadwalKunjungan as JadwalKunjunganModel;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class JadwalKunjungan extends Component
{
    use AuthorizesRole;
    use ValidatesKolektorNasabah;

    protected function requiredRole(): string
    {
        return 'kolektor';
    }

    public $jadwalHari = [];

    public $tanggal = '';

    public $search = '';

    public $filterStatus = 'semua';

    public function mount()
    {
        $this->tanggal = now()->format('Y-m-d');
        $this->loadJadwal();
    }

    public function updatedTanggal()
    {
        $this->loadJadwal();
    }

    public function updatedSearch()
    {
        $this->loadJadwal();
    }

    public function updatedFilterStatus()
    {
        $this->loadJadwal();
    }

    public function setTanggalToday()
    {
        $this->tanggal = now()->format('Y-m-d');
        $this->loadJadwal();
    }

    public function setTanggalTomorrow()
    {
        $this->tanggal = now()->addDay()->format('Y-m-d');
        $this->loadJadwal();
    }

    public function setTanggalYesterday()
    {
        $this->tanggal = now()->subDay()->format('Y-m-d');
        $this->loadJadwal();
    }

    public function loadJadwal()
    {
        if (! $this->tanggalValid()) {
            $this->jadwalHari = [];

            return;
        }

        $kolektorId = Auth::id();

        $nasabahQuery = NasabahProfil::whereIn('user_id', function ($q) use ($kolektorId) {
            $q->select('nasabah_id')
                ->from('kolektor_nasabah')
                ->where('kolektor_id', $kolektorId)
                ->where('status', 'aktif');
        })->with('user');

        if ($this->search) {
            $nasabahQuery->where(function ($q) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', '%'.$this->search.'%'))
                    ->orWhere('alamat', 'like', '%'.$this->search.'%');
            });
        }

        $allNasabah = $nasabahQuery->get();

        $list = $allNasabah->map(function ($profil) {
            $jadwal = JadwalKunjunganModel::where('kolektor_id', Auth::id())
                ->where('nasabah_id', $profil->user_id)
                ->where('tanggal_jadwal', $this->tanggal)
                ->first();

            $status = $jadwal->status_kunjungan ?? 'belum';

            return [
                'profil' => $profil,
                'jadwal' => $jadwal,
                'status' => $status,
            ];
        });

        if ($this->filterStatus !== 'semua') {
            $list = $list->filter(fn ($item) => $item['status'] === $this->filterStatus);
        }

        $this->jadwalHari = $list->values()->toArray();
    }

    public function updateStatus($nasabahId, $status)
    {
        if (! is_numeric($nasabahId) || ! in_array($status, ['dikunjungi', 'dilewati', 'tidak_ada', 'belum'], true)) {
            session()->flash('error', 'Data kunjungan tidak valid.');

            return;
        }

        if (! $this->tanggalValid()) {
            session()->flash('error', 'Tanggal tidak valid.');

            return;
        }

        if (! $this->isNasabahBinaan((int) $nasabahId)) {
            session()->flash('error', 'Nasabah ini bukan binaan Anda.');

            return;
        }

        JadwalKunjunganModel::updateOrCreate(
            [
                'kolektor_id' => Auth::id(),
                'nasabah_id' => $nasabahId,
                'tanggal_jadwal' => $this->tanggal,
            ],
            ['status_kunjungan' => $status === 'belum' ? null : $status]
        );

        $this->loadJadwal();
        session()->flash('success', 'Status kunjungan berhasil diupdate!');
    }

    protected function tanggalValid(): bool
    {
        $tanggal = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $this->tanggal);

        return $tanggal !== false && $tanggal->format('Y-m-d') === $this->tanggal;
    }

    public function render()
    {
        $kolektorId = Auth::id();
        $totalNasabahCount = KolektorNasabah::where('kolektor_id', $kolektorId)->where('status', 'aktif')->count();

        $jadwalsToday = $this->tanggalValid()
            ? JadwalKunjunganModel::where('kolektor_id', $kolektorId)
                ->where('tanggal_jadwal', $this->tanggal)
                ->get()
            : collect();

        $dikunjungiCount = $jadwalsToday->where('status_kunjungan', 'dikunjungi')->count();
        $dilewatiCount = $jadwalsToday->where('status_kunjungan', 'dilewati')->count();
        $tidakAdaCount = $jadwalsToday->where('status_kunjungan', 'tidak_ada')->count();
        $belumCount = max(0, $totalNasabahCount - ($dikunjungiCount + $dilewatiCount + $tidakAdaCount));

        $progressPct = $totalNasabahCount > 0 ? round(($dikunjungiCount / $totalNasabahCount) * 100) : 0;

        return view('livewire.kolektor.jadwal-kunjungan', compact(
            'totalNasabahCount',
            'dikunjungiCount',
            'dilewatiCount',
            'tidakAdaCount',
            'belumCount',
            'progressPct'
        ));
    }
}
