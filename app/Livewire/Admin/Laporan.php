<?php

namespace App\Livewire\Admin;

use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Laporan extends Component
{
    public $periode = 'harian';

    public $tanggal = '';

    public $bulan = '';

    public $tahun = '';

    public function mount()
    {
        $this->tanggal = now()->format('Y-m-d');
        $this->bulan = now()->format('Y-m');
        $this->tahun = (int) now()->format('Y');
    }

    public function render()
    {
        $data = match ($this->periode) {
            'harian' => $this->getHarian(),
            'bulanan' => $this->getBulanan(),
        };

        return view('livewire.admin.laporan', $data);
    }

    public function exportCsv()
    {
        $filename = 'laporan-'.$this->periode.'-'.$this->tanggal.'.csv';
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, ['Laporan Keuangan - '.ucfirst($this->periode)]);
            fputcsv($file, ['Periode', $this->periode === 'harian' ? $this->tanggal : $this->bulan]);
            fputcsv($file, []);

            // Summary
            fputcsv($file, ['RINGKASAN']);
            $data = $this->periode === 'harian' ? $this->getHarian() : $this->getBulanan();
            fputcsv($file, ['Total Setoran', $data['totalSetoran']]);
            fputcsv($file, ['Total Penarikan', $data['totalPenarikan']]);
            fputcsv($file, ['Total Komisi', $data['totalKomisi']]);
            fputcsv($file, ['Jumlah Transaksi Setoran', $data['jumlahTransaksiSetoran']]);
            fputcsv($file, ['Jumlah Transaksi Penarikan', $data['jumlahTransaksiPenarikan']]);
            fputcsv($file, []);

            // Detail Setoran
            fputcsv($file, ['DETAIL SETORAN']);
            fputcsv($file, ['Tanggal', 'Nasabah', 'Produk', 'Nominal', 'Sumber', 'Status']);
            foreach ($data['setoranDetails'] as $item) {
                fputcsv($file, [
                    $item->tanggal_transaksi->format('d/m/Y'),
                    $item->nasabah->name ?? '-',
                    $item->produk->nama ?? '-',
                    $item->nominal,
                    $item->sumber_input,
                    $item->status,
                ]);
            }
            fputcsv($file, []);

            // Detail Penarikan
            fputcsv($file, ['DETAIL PENARIKAN']);
            fputcsv($file, ['Tanggal', 'Nasabah', 'Produk', 'Diminta', 'Komisi', 'Diterima', 'Status']);
            foreach ($data['penarikanDetails'] as $item) {
                fputcsv($file, [
                    $item->waktu_approval?->format('d/m/Y') ?? '-',
                    $item->nasabah->name ?? '-',
                    $item->produk->nama ?? '-',
                    $item->nominal_diminta,
                    $item->nominal_komisi,
                    $item->nominal_diterima,
                    $item->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getHarian(): array
    {
        $date = Carbon::parse($this->tanggal);

        $setoran = TransaksiSetoran::with(['nasabah', 'produk'])
            ->whereDate('created_at', $date)
            ->get();
        $penarikan = TransaksiPenarikan::with(['nasabah', 'produk'])
            ->whereIn('status', ['approved', 'selesai'])
            ->whereDate('waktu_approval', $date)
            ->get();

        return [
            'totalSetoran' => $setoran->sum('nominal'),
            'totalPenarikan' => $penarikan->sum('nominal_diminta'),
            'totalKomisi' => $penarikan->sum('nominal_komisi'),
            'jumlahTransaksiSetoran' => $setoran->count(),
            'jumlahTransaksiPenarikan' => $penarikan->count(),
            'setoranDetails' => $setoran,
            'penarikanDetails' => $penarikan,
        ];
    }

    private function getBulanan(): array
    {
        $start = Carbon::parse($this->bulan)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $setoran = TransaksiSetoran::with(['nasabah', 'produk'])
            ->whereBetween('created_at', [$start, $end])
            ->get();
        $penarikan = TransaksiPenarikan::with(['nasabah', 'produk'])
            ->whereIn('status', ['approved', 'selesai'])
            ->whereBetween('waktu_approval', [$start, $end])
            ->get();

        $dailySetoran = $setoran->groupBy(fn ($t) => Carbon::parse($t->created_at)->format('d'));
        $dailyPenarikan = $penarikan->groupBy(fn ($t) => Carbon::parse($t->waktu_approval)->format('d'));

        return [
            'totalSetoran' => $setoran->sum('nominal'),
            'totalPenarikan' => $penarikan->sum('nominal_diminta'),
            'totalKomisi' => $penarikan->sum('nominal_komisi'),
            'jumlahTransaksiSetoran' => $setoran->count(),
            'jumlahTransaksiPenarikan' => $penarikan->count(),
            'dailySetoran' => $dailySetoran,
            'dailyPenarikan' => $dailyPenarikan,
            'days' => (int) $start->diffInDays($end) + 1,
            'setoranDetails' => $setoran,
            'penarikanDetails' => $penarikan,
        ];
    }
}
