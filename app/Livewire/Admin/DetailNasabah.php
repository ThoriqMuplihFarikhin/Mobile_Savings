<?php

namespace App\Livewire\Admin;

use App\Actions\Pin\ResetPinOlehAdminAction;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetailNasabah extends Component
{
    use AuthorizesRole;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public User $user;

    public string $periode = '30hari';

    public bool $tampilKonfirmasiResetPin = false;

    public function mount(User $user): void
    {
        abort_if($user->role !== 'nasabah', 404);
        $this->user = $user;
    }

    public function render()
    {
        $saldoPerProduk = SaldoProduk::where('nasabah_id', $this->user->id)
            ->with('produk')
            ->get();

        [$start, $end] = $this->rentangTanggal();

        $setoran = TransaksiSetoran::where('nasabah_id', $this->user->id)
            ->where('status', '!=', 'dibatalkan')
            ->whereBetween('tanggal_transaksi', [$start, $end])
            ->orderBy('tanggal_transaksi')
            ->get();

        $penarikan = TransaksiPenarikan::where('nasabah_id', $this->user->id)
            ->whereIn('status', ['approved', 'selesai'])
            ->whereBetween('waktu_approval', [$start, $end])
            ->orderBy('waktu_approval')
            ->get();

        $chartData = $this->buildChartData($setoran, $penarikan, $start, $end);
        $riwayat = $this->getRiwayatTransaksi($setoran, $penarikan);

        return view('livewire.admin.detail-nasabah', compact('saldoPerProduk', 'chartData', 'riwayat'));
    }

    public function updatedPeriode(): void
    {
        // trigger re-render
    }

    public function confirmResetPin(): void
    {
        $this->tampilKonfirmasiResetPin = true;
    }

    public function resetPin(): void
    {
        $pinBaru = app(ResetPinOlehAdminAction::class)->execute($this->user);

        $this->tampilKonfirmasiResetPin = false;

        session()->flash('success', "PIN berhasil direset. PIN baru: {$pinBaru}. Catat sekarang karena hanya ditampilkan sekali. Pengguna wajib mengganti PIN setelah login.");
    }

    protected function rentangTanggal(): array
    {
        $end = now()->endOfDay();
        $start = match ($this->periode) {
            '90hari' => now()->subDays(90)->startOfDay(),
            'tahun_ini' => now()->startOfYear(),
            default => now()->subDays(30)->startOfDay(),
        };

        return [$start, $end];
    }

    protected function buildChartData($setoran, $penarikan, $start, $end): array
    {
        $totalSetoranPeriode = $setoran->sum('nominal');
        $totalPenarikanPeriode = $penarikan->sum('nominal_diminta');
        $saldoSekarang = SaldoProduk::where('nasabah_id', $this->user->id)->sum('saldo');
        $saldoAwal = (float) $saldoSekarang - (float) $totalSetoranPeriode + (float) $totalPenarikanPeriode;

        $perHari = [];
        $cursor = $start->copy();
        $runningSaldo = (float) $saldoAwal;
        $totalDays = (int) $start->startOfDay()->diffInDays($end->startOfDay());

        $setoranByDate = $setoran->groupBy(fn ($s) => substr($s->tanggal_transaksi, 0, 10));
        $penarikanByDate = $penarikan->groupBy(fn ($p) => substr($p->waktu_approval, 0, 10));

        for ($i = 0; $i <= $totalDays; $i++) {
            $tanggalKey = $cursor->format('Y-m-d');
            $runningSaldo += (float) ($setoranByDate[$tanggalKey] ?? collect())->sum('nominal');
            $runningSaldo -= (float) ($penarikanByDate[$tanggalKey] ?? collect())->sum('nominal_diminta');
            $perHari[] = ['tanggal' => $tanggalKey, 'saldo' => $runningSaldo];
            $cursor = $cursor->addDay();
        }

        $maxSaldo = count($perHari) > 0 ? max(collect($perHari)->pluck('saldo')->max(), 1) : 1;
        $chartWidth = 560;
        $chartHeight = 160;
        $padding = 10;
        $graphWidth = $chartWidth - ($padding * 2);
        $graphHeight = $chartHeight - ($padding * 2);
        $count = count($perHari);

        $points = collect($perHari)->map(function ($item, $i) use ($maxSaldo, $graphWidth, $graphHeight, $padding, $count) {
            $x = $padding + ($i / max($count - 1, 1)) * $graphWidth;
            $y = $padding + $graphHeight - ($item['saldo'] / $maxSaldo) * $graphHeight;

            return ['x' => $x, 'y' => $y];
        });

        $pathD = $points->map(function ($p, $i) {
            return ($i === 0 ? 'M' : 'L').round($p['x'], 1).','.round($p['y'], 1);
        })->implode(' ');

        $areaD = $pathD.' L'.round($points->last()['x'] ?? 0, 1).','.($padding + $graphHeight).' L'.round($points->first()['x'] ?? 0, 1).','.($padding + $graphHeight).' Z';

        return [
            'hasData' => $count > 0,
            'pathD' => $pathD,
            'areaD' => $areaD,
            'saldoAkhir' => $count > 0 ? (float) $perHari[$count - 1]['saldo'] : 0.0,
            'lastPoint' => $points->last(),
            'chartWidth' => $chartWidth,
            'chartHeight' => $chartHeight,
            'padding' => $padding,
            'graphHeight' => $graphHeight,
        ];
    }

    protected function getRiwayatTransaksi($setoran, $penarikan)
    {
        $riwayatSetoran = $setoran->map(fn ($s) => [
            'tanggal' => $s->tanggal_transaksi,
            'tipe' => 'Setoran',
            'nominal' => $s->nominal,
            'status' => $s->status,
        ]);

        $riwayatPenarikan = $penarikan->map(fn ($p) => [
            'tanggal' => $p->waktu_approval->format('Y-m-d'),
            'tipe' => 'Penarikan',
            'nominal' => $p->nominal_diminta,
            'status' => $p->status,
        ]);

        return $riwayatSetoran->concat($riwayatPenarikan)->sortByDesc('tanggal')->take(20)->values();
    }
}
