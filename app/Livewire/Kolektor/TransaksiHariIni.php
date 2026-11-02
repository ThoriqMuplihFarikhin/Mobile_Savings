<?php

namespace App\Livewire\Kolektor;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.mobile')]
#[Title('Transaksi Hari Ini')]
class TransaksiHariIni extends Component
{
    use AuthorizesRole;

    public string $filter = 'semua';

    protected function requiredRole(): string
    {
        return 'kolektor';
    }

    public function setFilter(string $filter): void
    {
        if (in_array($filter, ['semua', 'setoran', 'penarikan'], true)) {
            $this->filter = $filter;
        }
    }

    public function render(): View
    {
        $kolektorId = (int) auth()->id();
        $hariIni = today();

        $setoranQuery = TransaksiSetoran::with(['nasabah', 'produk'])
            ->where('input_by', $kolektorId)
            ->where('tanggal_transaksi', $hariIni)
            ->whereIn('status', ['tercatat', 'dikoreksi']);

        $setoranList = $setoranQuery->get()->map(fn (TransaksiSetoran $s) => [
            'id' => $s->id,
            'jenis' => 'setoran',
            'nasabah_id' => $s->nasabah_id,
            'nasabah_nama' => $s->nasabah?->name ?? 'Nasabah',
            'produk_nama' => $s->produk?->nama ?? 'Tabungan',
            'nominal' => (float) $s->nominal,
            'status' => $s->status,
            'no_bukti' => 'STR-'.str_pad((string) $s->id, 4, '0', STR_PAD_LEFT),
            'waktu' => $s->tanggal_input_sistem ? Carbon::parse($s->tanggal_input_sistem)->format('H.i') : Carbon::parse($s->created_at)->format('H.i'),
            'timestamp' => $s->tanggal_input_sistem ? Carbon::parse($s->tanggal_input_sistem)->timestamp : Carbon::parse($s->created_at)->timestamp,
        ]);

        $penarikanQuery = TransaksiPenarikan::with(['nasabah', 'produk'])
            ->where('dibayar_oleh', $kolektorId)
            ->where('status', 'selesai')
            ->whereDate('waktu_pencairan', $hariIni);

        $penarikanList = $penarikanQuery->get()->map(fn (TransaksiPenarikan $p) => [
            'id' => $p->id,
            'jenis' => 'penarikan',
            'nasabah_id' => $p->nasabah_id,
            'nasabah_nama' => $p->nasabah?->name ?? 'Nasabah',
            'produk_nama' => $p->produk?->nama ?? 'Tabungan',
            'nominal' => (float) $p->nominal_diterima,
            'status' => $p->status,
            'no_bukti' => 'TRK-'.str_pad((string) $p->id, 4, '0', STR_PAD_LEFT),
            'waktu' => $p->waktu_pencairan ? Carbon::parse($p->waktu_pencairan)->format('H.i') : '00.00',
            'timestamp' => $p->waktu_pencairan ? Carbon::parse($p->waktu_pencairan)->timestamp : 0,
        ]);

        $totalSetoranMasuk = (float) $setoranList->sum('nominal');
        $totalPenarikanTunai = (float) $penarikanList->sum('nominal');

        /** @var Collection<int, array<string, mixed>> $semuaTransaksi */
        $semuaTransaksi = $setoranList->concat($penarikanList)->sortByDesc('timestamp')->values();

        if ($this->filter === 'setoran') {
            $transaksi = $semuaTransaksi->where('jenis', 'setoran')->values();
        } elseif ($this->filter === 'penarikan') {
            $transaksi = $semuaTransaksi->where('jenis', 'penarikan')->values();
        } else {
            $transaksi = $semuaTransaksi;
        }

        return view('livewire.kolektor.transaksi-hari-ini', compact(
            'transaksi',
            'totalSetoranMasuk',
            'totalPenarikanTunai'
        ));
    }
}
