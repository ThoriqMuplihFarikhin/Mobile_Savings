<?php

namespace App\Livewire\Kolektor;

use App\Actions\Paket\DaftarkanKePaketAction;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.mobile')]
class NasabahBinaan extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'kolektor';
    }

    public $search = '';

    public $filterTunggakan = 'semua';

    public string $filterMode = 'semua';

    public bool $tampilDaftarPaket = false;

    public int $nasabahDaftarId = 0;

    public int $produkDaftarId = 0;

    public bool $setujuDaftar = false;

    public string $catatanDaftar = '';

    public ?string $pesanErrorDaftar = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterTunggakan()
    {
        $this->resetPage();
    }

    public function updatedFilterMode(): void
    {
        $this->resetPage();
    }

    public function toggleHariKunjungan(int $nasabahId, int $hari): void
    {
        if ($hari < 1 || $hari > 7) {
            return;
        }

        $binaan = KolektorNasabah::where('kolektor_id', Auth::id())
            ->where('nasabah_id', $nasabahId)
            ->where('status', 'aktif')
            ->first();

        if ($binaan === null) {
            return;
        }

        $terpilih = array_map('intval', $binaan->hari_kunjungan ?? []);

        $terpilih = in_array($hari, $terpilih, true)
            ? array_values(array_diff($terpilih, [$hari]))
            : array_values(array_unique([...$terpilih, $hari]));

        sort($terpilih);

        $binaan->update(['hari_kunjungan' => empty($terpilih) ? null : $terpilih]);
    }

    public function bukaDaftarPaket(int $nasabahId): void
    {
        $this->resetDaftarPaket();
        $this->nasabahDaftarId = $nasabahId;
        $this->tampilDaftarPaket = true;
    }

    public function tutupDaftarPaket(): void
    {
        $this->tampilDaftarPaket = false;
        $this->resetDaftarPaket();
    }

    public function daftarkanKePaket(): void
    {
        $this->validate([
            'produkDaftarId' => 'required|integer|exists:produk_tabungan,id',
            'setujuDaftar' => 'accepted',
            'catatanDaftar' => 'required|string|max:255',
        ]);

        $kolektor = Auth::user();
        if (! $kolektor instanceof User) {
            abort(403);
        }

        $nasabah = User::find($this->nasabahDaftarId);
        $produk = ProdukTabungan::find($this->produkDaftarId);

        if (! $nasabah instanceof User) {
            $this->pesanErrorDaftar = 'Nasabah tidak ditemukan.';

            return;
        }

        if (! $produk instanceof ProdukTabungan) {
            $this->pesanErrorDaftar = 'Paket tidak ditemukan.';

            return;
        }

        try {
            app(DaftarkanKePaketAction::class)->execute($kolektor, $nasabah, $produk, $this->catatanDaftar);
        } catch (DomainException $e) {
            $this->pesanErrorDaftar = $e->getMessage();

            return;
        }

        session()->flash('success', 'Binaan berhasil didaftarkan ke paket '.$produk->nama.'.');
        $this->tampilDaftarPaket = false;
        $this->resetDaftarPaket();
    }

    private function resetDaftarPaket(): void
    {
        $this->nasabahDaftarId = 0;
        $this->produkDaftarId = 0;
        $this->setujuDaftar = false;
        $this->catatanDaftar = '';
        $this->pesanErrorDaftar = null;
    }

    public function render()
    {
        $kolektorId = Auth::id();

        $nasabahIds = KolektorNasabah::where('kolektor_id', $kolektorId)
            ->where('status', 'aktif')
            ->pluck('nasabah_id');

        $query = NasabahProfil::whereIn('user_id', $nasabahIds)
            ->with(['user', 'user.saldoProduks.produk', 'user.kepesertaanPakets']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nama', 'like', "%{$this->search}%")
                    ->orWhere('alamat', 'like', "%{$this->search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('no_hp', 'like', "%{$this->search}%"));
            });
        }

        if ($this->filterTunggakan === 'tunggakan') {
            $query->whereHas('user.kepesertaanPakets', function ($q) {
                $q->whereNull('keputusan_akhir')->where('tunggakan', '>', 0);
            });
        }

        if (in_array($this->filterMode, ['digital', 'offline'], true)) {
            $query->whereHas('user', function ($q) {
                $q->where('mode_akses', $this->filterMode);
            });
        }

        $nasabahList = $query->latest()->paginate(10);

        $totalNasabah = $nasabahIds->count();

        $totalNasabahOffline = User::whereIn('id', $nasabahIds)
            ->where('mode_akses', 'offline')
            ->count();

        $totalSaldoDikelola = SaldoProduk::whereIn('nasabah_id', $nasabahIds)->sum('saldo');

        $totalNasabahTunggakan = KepesertaanPaket::whereIn('nasabah_id', $nasabahIds)
            ->whereNull('keputusan_akhir')
            ->where('tunggakan', '>', 0)
            ->distinct('nasabah_id')
            ->count('nasabah_id');

        $hariKunjungan = KolektorNasabah::where('kolektor_id', $kolektorId)
            ->where('status', 'aktif')
            ->get()
            ->mapWithKeys(fn (KolektorNasabah $pasangan): array => [
                (int) $pasangan->nasabah_id => array_map('intval', $pasangan->hari_kunjungan ?? []),
            ])
            ->all();

        return view('livewire.kolektor.nasabah-binaan', compact(
            'nasabahList',
            'totalNasabah',
            'totalNasabahOffline',
            'totalSaldoDikelola',
            'totalNasabahTunggakan',
            'hariKunjungan'
        ))->with('paketTerbuka', $this->nasabahDaftarId > 0
            ? DaftarkanKePaketAction::paketTerbukaUntuk($this->nasabahDaftarId)
            : collect());
    }
}
