<?php

namespace App\Livewire\Admin;

use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
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
        $data = $this->filterValid()
            ? match ($this->periode) {
                'bulanan' => $this->getBulanan(),
                default => $this->getHarian(),
            }
        : $this->laporanKosong();

        return view('livewire.admin.laporan', $data);
    }

    public function exportCsv()
    {
        $this->validate($this->filterRules());

        $periodeValue = $this->periode === 'bulanan' ? $this->bulan : $this->tanggal;
        $filename = 'laporan-'.$this->periode.'-'.$periodeValue.'.csv';
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        /** Sanitasi sel teks agar formula tidak dieksekusi spreadsheet. */
        $safe = fn ($value): string => is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;

        $callback = function () use ($safe) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, [$safe('Laporan Keuangan - '.ucfirst($this->periode))]);
            fputcsv($file, [$safe('Periode'), $safe($this->periode === 'harian' ? $this->tanggal : $this->bulan)]);
            fputcsv($file, []);

            // Summary
            fputcsv($file, ['RINGKASAN']);
            $data = $this->periode === 'bulanan' ? $this->getBulanan() : $this->getHarian();
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
                    Carbon::parse($item->tanggal_transaksi)->format('d/m/Y'),
                    $safe($item->nasabah->name ?? '-'),
                    $safe($item->produk->nama ?? '-'),
                    $item->nominal,
                    $safe($item->sumber_input),
                    $safe($item->status),
                ]);
            }
            fputcsv($file, []);

            // Detail Penarikan
            fputcsv($file, ['DETAIL PENARIKAN']);
            fputcsv($file, ['Tanggal', 'Nasabah', 'Produk', 'Diminta', 'Komisi', 'Diterima', 'Status']);
            foreach ($data['penarikanDetails'] as $item) {
                fputcsv($file, [
                    $item->waktu_approval !== null ? Carbon::parse($item->waktu_approval)->format('d/m/Y') : '-',
                    $safe($item->nasabah->name ?? '-'),
                    $safe($item->produk->nama ?? '-'),
                    $item->nominal_diminta,
                    $item->nominal_komisi,
                    $item->nominal_diterima,
                    $safe($item->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * @return array{periode: string, tanggal: string, bulan: string}
     */
    private function filterRules(): array
    {
        return [
            'periode' => 'required|in:harian,bulanan',
            'tanggal' => 'required|date_format:Y-m-d',
            'bulan' => 'required|date_format:Y-m',
        ];
    }

    private function filterValid(): bool
    {
        try {
            $this->validate($this->filterRules());
        } catch (ValidationException $e) {
            foreach ($e->validator->errors()->messages() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            session()->flash('error', 'Filter laporan tidak valid. Silakan periksa periode, tanggal, atau bulan yang dipilih.');

            return false;
        }

        return true;
    }

    /**
     * @return array{
     *     totalSetoran: int,
     *     totalPenarikan: int,
     *     totalKomisi: int,
     *     jumlahTransaksiSetoran: int,
     *     jumlahTransaksiPenarikan: int,
     *     dailySetoran: Collection<int, Collection<int, TransaksiSetoran>>,
     *     dailyPenarikan: Collection<int, Collection<int, TransaksiPenarikan>>,
     *     days: int,
     *     setoranDetails: Collection<int, TransaksiSetoran>,
     *     penarikanDetails: Collection<int, TransaksiPenarikan>
     * }
     */
    private function laporanKosong(): array
    {
        return [
            'totalSetoran' => 0,
            'totalPenarikan' => 0,
            'totalKomisi' => 0,
            'jumlahTransaksiSetoran' => 0,
            'jumlahTransaksiPenarikan' => 0,
            'dailySetoran' => collect(),
            'dailyPenarikan' => collect(),
            'days' => 0,
            'setoranDetails' => collect(),
            'penarikanDetails' => collect(),
        ];
    }

    /**
     * @return array{
     *     totalSetoran: float|int,
     *     totalPenarikan: float|int,
     *     totalKomisi: float|int,
     *     jumlahTransaksiSetoran: int,
     *     jumlahTransaksiPenarikan: int,
     *     setoranDetails: \Illuminate\Database\Eloquent\Collection<int, TransaksiSetoran>,
     *     penarikanDetails: \Illuminate\Database\Eloquent\Collection<int, TransaksiPenarikan>
     * }
     */
    private function getHarian(): array
    {
        $date = Carbon::parse($this->tanggal);

        $setoran = TransaksiSetoran::with(['nasabah', 'produk'])
            ->masihAktif()
            ->whereDate('tanggal_transaksi', $date)
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

    /**
     * @return array{
     *     totalSetoran: float|int,
     *     totalPenarikan: float|int,
     *     totalKomisi: float|int,
     *     jumlahTransaksiSetoran: int,
     *     jumlahTransaksiPenarikan: int,
     *     dailySetoran: Collection<(int|string), \Illuminate\Database\Eloquent\Collection<int, TransaksiSetoran>>,
     *     dailyPenarikan: Collection<(int|string), \Illuminate\Database\Eloquent\Collection<int, TransaksiPenarikan>>,
     *     days: int,
     *     setoranDetails: \Illuminate\Database\Eloquent\Collection<int, TransaksiSetoran>,
     *     penarikanDetails: \Illuminate\Database\Eloquent\Collection<int, TransaksiPenarikan>
     * }
     */
    private function getBulanan(): array
    {
        $start = Carbon::parse($this->bulan)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $setoran = TransaksiSetoran::with(['nasabah', 'produk'])
            ->masihAktif()
            ->whereBetween('tanggal_transaksi', [$start->toDateString(), $end->toDateString()])
            ->get();
        $penarikan = TransaksiPenarikan::with(['nasabah', 'produk'])
            ->whereIn('status', ['approved', 'selesai'])
            ->whereBetween('waktu_approval', [$start, $end])
            ->get();

        $dailySetoran = $setoran->groupBy(fn ($t) => Carbon::parse($t->tanggal_transaksi)->format('d'));
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
