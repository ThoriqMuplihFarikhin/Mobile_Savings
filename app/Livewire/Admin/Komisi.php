<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use App\Support\CsvSafe;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
class Komisi extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public string $dariTanggal = '';

    public string $sampaiTanggal = '';

    public string $produkId = '';

    public string $status = '';

    public string $lokasiPengambilan = '';

    public string $kolektorId = '';

    public string $dasarTanggal = 'waktu_approval';

    public float $totalKomisi = 0;

    public int $totalTransaksi = 0;

    public float $totalNominalPenarikan = 0;

    /**
     * Rekap komisi per bulan (bulan, total_komisi, jumlah_transaksi).
     *
     * @var Collection<int, TransaksiPenarikan>
     */
    public Collection $rekapBulan;

    private const FILTER_NAMA = [
        'dariTanggal',
        'sampaiTanggal',
        'produkId',
        'status',
        'lokasiPengambilan',
        'kolektorId',
        'dasarTanggal',
    ];

    public function mount(): void
    {
        $this->dariTanggal = now()->startOfMonth()->toDateString();
        $this->sampaiTanggal = now()->toDateString();
        $this->rekapBulan = new Collection;
    }

    public function updated(string $nama): void
    {
        if (in_array($nama, self::FILTER_NAMA, true)) {
            $this->resetPage();
        }
    }

    public function resetFilter(): void
    {
        $this->dariTanggal = now()->startOfMonth()->toDateString();
        $this->sampaiTanggal = now()->toDateString();
        $this->produkId = '';
        $this->status = '';
        $this->lokasiPengambilan = '';
        $this->kolektorId = '';
        $this->dasarTanggal = 'waktu_approval';
        $this->resetPage();
    }

    /**
     * @return Builder<TransaksiPenarikan>
     */
    protected function baseQuery(): Builder
    {
        $dasar = in_array($this->dasarTanggal, ['waktu_approval', 'waktu_pencairan'], true)
            ? $this->dasarTanggal
            : 'waktu_approval';
        $status = in_array($this->status, ['approved', 'selesai'], true)
            ? $this->status
            : '';

        return TransaksiPenarikan::query()
            ->whereIn('transaksi_penarikan.status', $status !== '' ? [$status] : ['approved', 'selesai'])
            ->when($this->dariTanggal, fn ($q) => $q->whereDate("transaksi_penarikan.$dasar", '>=', $this->dariTanggal))
            ->when($this->sampaiTanggal, fn ($q) => $q->whereDate("transaksi_penarikan.$dasar", '<=', $this->sampaiTanggal))
            ->when($this->produkId, fn ($q) => $q->where('transaksi_penarikan.produk_id', $this->produkId))
            ->when($this->lokasiPengambilan, fn ($q) => $q->where('transaksi_penarikan.lokasi_pengambilan', $this->lokasiPengambilan))
            ->when($this->kolektorId, fn ($q) => $q->where('transaksi_penarikan.dibayar_oleh', $this->kolektorId));
    }

    public function exportCsv(): StreamedResponse
    {
        $jumlah = (clone $this->baseQuery())->count();

        try {
            ActivityLogger::log('ekspor_komisi', 'transaksi_penarikan', 0, [
                'dari' => $this->dariTanggal,
                'sampai' => $this->sampaiTanggal,
                'produk_id' => $this->produkId,
                'status' => $this->status,
                'lokasi_pengambilan' => $this->lokasiPengambilan,
                'kolektor_id' => $this->kolektorId,
                'dasar_tanggal' => $this->dasarTanggal,
                'jumlah_baris' => $jumlah,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        $filename = 'komisi-'.$this->dariTanggal.'-'.$this->sampaiTanggal.'.csv';
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $file = fopen('php://output', 'w');
            if ($file === false) {
                return;
            }

            CsvSafe::mulai($file);
            fputcsv($file, [
                'Tanggal Approval',
                'Tanggal Pencairan',
                'Nasabah',
                'Produk',
                'Jalur',
                'Lokasi Pengambilan',
                'Kolektor Pembayar',
                'Nominal Diminta',
                'Persen Komisi',
                'Nominal Komisi',
                'Nominal Diterima',
                'Status',
            ]);

            $totalDiminta = 0.0;
            $totalKomisi = 0.0;
            $totalDiterima = 0.0;

            $baris = (clone $this->baseQuery())
                ->with(['nasabah', 'produk', 'dibayarOleh'])
                ->orderBy('waktu_approval')
                ->lazy(200);

            foreach ($baris as $item) {
                fputcsv($file, [
                    $item->waktu_approval !== null ? Carbon::parse($item->waktu_approval)->format('d/m/Y H:i') : '-',
                    $item->waktu_pencairan !== null ? Carbon::parse($item->waktu_pencairan)->format('d/m/Y H:i') : '-',
                    CsvSafe::teks($item->nasabah->name ?? '-'),
                    CsvSafe::teks($item->produk->nama ?? '-'),
                    $item->jalur_pengajuan,
                    $item->lokasi_pengambilan,
                    CsvSafe::teks($item->dibayarOleh->name ?? '-'),
                    number_format((float) $item->nominal_diminta, 2, '.', ''),
                    number_format((float) $item->persen_komisi_terpakai, 2, '.', ''),
                    number_format((float) $item->nominal_komisi, 2, '.', ''),
                    number_format((float) $item->nominal_diterima, 2, '.', ''),
                    $item->status,
                ]);

                $totalDiminta += (float) $item->nominal_diminta;
                $totalKomisi += (float) $item->nominal_komisi;
                $totalDiterima += (float) $item->nominal_diterima;
            }

            fputcsv($file, [
                '',
                '',
                'TOTAL',
                '',
                '',
                '',
                '',
                number_format($totalDiminta, 2, '.', ''),
                '',
                number_format($totalKomisi, 2, '.', ''),
                number_format($totalDiterima, 2, '.', ''),
                '',
            ]);

            fclose($file);
        }, 200, $headers);
    }

    public function render(): View
    {
        $this->totalKomisi = (float) (clone $this->baseQuery())->sum('nominal_komisi');
        $this->totalTransaksi = (clone $this->baseQuery())->count();
        $this->totalNominalPenarikan = (float) (clone $this->baseQuery())->sum('nominal_diminta');

        $komisiPerProduk = (clone $this->baseQuery())
            ->join('produk_tabungan', 'transaksi_penarikan.produk_id', '=', 'produk_tabungan.id')
            ->select(
                'produk_tabungan.id',
                'produk_tabungan.nama',
                DB::raw('SUM(transaksi_penarikan.nominal_komisi) as total_komisi'),
                DB::raw('COUNT(*) as jumlah_transaksi')
            )
            ->groupBy('produk_tabungan.id', 'produk_tabungan.nama')
            ->orderByDesc('total_komisi')
            ->get();

        $dasar = in_array($this->dasarTanggal, ['waktu_approval', 'waktu_pencairan'], true)
            ? $this->dasarTanggal
            : 'waktu_approval';

        $rekapBulan = (clone $this->baseQuery())
            ->selectRaw("DATE_FORMAT(transaksi_penarikan.$dasar, '%Y-%m') as bulan")
            ->selectRaw('SUM(transaksi_penarikan.nominal_komisi) as total_komisi')
            ->selectRaw('COUNT(*) as jumlah_transaksi')
            ->groupBy('bulan')
            ->orderByDesc('bulan')
            ->get();

        $this->rekapBulan = $rekapBulan;

        $riwayat = (clone $this->baseQuery())
            ->with(['nasabah', 'produk', 'disetujuiOleh'])
            ->latest('waktu_approval')
            ->paginate(15);

        $produkList = ProdukTabungan::where('persen_komisi', '>', 0)->orderBy('nama')->get();
        $kolektorList = User::where('role', 'kolektor')->orderBy('name')->get();

        return view('livewire.admin.komisi', compact(
            'komisiPerProduk',
            'rekapBulan',
            'riwayat',
            'produkList',
            'kolektorList',
        ));
    }
}
