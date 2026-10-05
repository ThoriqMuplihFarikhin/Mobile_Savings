<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\KepesertaanPaket;
use App\Models\LogAktivitas;
use App\Models\ProdukTabungan;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Laporan extends Component
{
    use AuthorizesRole;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public string $periode = 'harian';

    public string $tanggal = '';

    public string $bulan = '';

    public int $tahun = 0;

    public string $seksi = 'keuangan';

    public function mount()
    {
        $this->tanggal = now()->format('Y-m-d');
        $this->bulan = now()->format('Y-m');
        $this->tahun = (int) now()->format('Y');
    }

    public function pilihSeksi(string $seksi): void
    {
        if (! in_array($seksi, ['keuangan', 'kolektor', 'paket', 'barang'], true)) {
            return;
        }

        $this->seksi = $seksi;
    }

    public function render()
    {
        $valid = $this->filterValid();

        $data = match ($this->seksi) {
            'kolektor' => ['kolektorRows' => $valid ? $this->kolektorRows() : collect()],
            'paket' => ['paketRows' => $this->paketRows()],
            'barang' => ['barangRows' => $this->barangRows()],
            default => $valid
                ? match ($this->periode) {
                    'bulanan' => $this->getBulanan(),
                    default => $this->getHarian(),
                }
            : $this->laporanKosong(),
        };

        return view('livewire.admin.laporan', $data);
    }

    public function exportCsv()
    {
        if (in_array($this->seksi, ['keuangan', 'kolektor'], true)) {
            $this->validate($this->filterRules());
        }

        $periodeValue = $this->periode === 'bulanan' ? $this->bulan : $this->tanggal;
        $filename = match ($this->seksi) {
            'kolektor' => 'laporan-kolektor-'.$this->periode.'-'.$periodeValue.'.csv',
            'paket' => 'laporan-paket-'.now()->toDateString().'.csv',
            'barang' => 'laporan-kebutuhan-barang-'.now()->toDateString().'.csv',
            default => 'laporan-'.$this->periode.'-'.$periodeValue.'.csv',
        };
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
            if ($file === false) {
                return;
            }

            match ($this->seksi) {
                'kolektor' => $this->tulisCsvKolektor($file, $safe),
                'paket' => $this->tulisCsvPaket($file, $safe),
                'barang' => $this->tulisCsvBarang($file, $safe),
                default => $this->tulisCsvKeuangan($file, $safe),
            };

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
     * Batas waktu laporan berdasarkan periode yang valid.
     *
     * @return array{0: string, 1: string}
     */
    private function periodeRange(): array
    {
        if ($this->periode === 'bulanan') {
            $mulai = Carbon::parse($this->bulan)->startOfMonth();

            return [$mulai->toDateString(), $mulai->copy()->endOfMonth()->toDateString()];
        }

        return [$this->tanggal, $this->tanggal];
    }

    /**
     * @return Builder<TransaksiSetoran>
     */
    private function querySetoranPeriode(): Builder
    {
        $query = TransaksiSetoran::query()->masihAktif();

        if ($this->periode === 'bulanan') {
            [$mulai, $selesai] = $this->periodeRange();
            $query->whereBetween('tanggal_transaksi', [$mulai, $selesai]);
        } else {
            $query->whereDate('tanggal_transaksi', $this->tanggal);
        }

        return $query;
    }

    /**
     * @return Builder<TransaksiPenarikan>
     */
    private function queryPenarikanPeriode(): Builder
    {
        $query = TransaksiPenarikan::query()->whereIn('status', ['approved', 'selesai']);

        if ($this->periode === 'bulanan') {
            $start = Carbon::parse($this->bulan)->startOfMonth();
            $query->whereBetween('waktu_approval', [$start, $start->copy()->endOfMonth()]);
        } else {
            $query->whereDate('waktu_approval', $this->tanggal);
        }

        return $query;
    }

    /**
     * @return array{
     *     totalSetoran: float,
     *     totalPenarikan: float,
     *     totalKomisi: float,
     *     jumlahTransaksiSetoran: int,
     *     jumlahTransaksiPenarikan: int,
     *     jumlahOverrideRisiko: int
     * }
     */
    private function getHarian(): array
    {
        $date = Carbon::parse($this->tanggal);

        $setoran = $this->querySetoranPeriode()
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(nominal), 0) as total')
            ->first();
        $penarikan = $this->queryPenarikanPeriode()
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(nominal_diminta), 0) as diminta, COALESCE(SUM(nominal_komisi), 0) as komisi')
            ->first();
        $jumlahOverrideRisiko = LogAktivitas::where('aksi', 'selesai_penarikan_override')
            ->whereDate('timestamp', $date)
            ->count();

        return [
            'totalSetoran' => (float) ($setoran?->getAttribute('total') ?? 0),
            'totalPenarikan' => (float) ($penarikan?->getAttribute('diminta') ?? 0),
            'totalKomisi' => (float) ($penarikan?->getAttribute('komisi') ?? 0),
            'jumlahTransaksiSetoran' => (int) ($setoran?->getAttribute('jumlah') ?? 0),
            'jumlahTransaksiPenarikan' => (int) ($penarikan?->getAttribute('jumlah') ?? 0),
            'jumlahOverrideRisiko' => $jumlahOverrideRisiko,
        ];
    }

    /**
     * @return array{
     *     totalSetoran: float,
     *     totalPenarikan: float,
     *     totalKomisi: float,
     *     jumlahTransaksiSetoran: int,
     *     jumlahTransaksiPenarikan: int,
     *     jumlahOverrideRisiko: int,
     *     dailySetoran: array<int|string, float>,
     *     dailyPenarikan: array<int|string, float>,
     *     days: int
     * }
     */
    private function getBulanan(): array
    {
        $setoran = $this->querySetoranPeriode()
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(nominal), 0) as total')
            ->first();
        $penarikan = $this->queryPenarikanPeriode()
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(nominal_diminta), 0) as diminta, COALESCE(SUM(nominal_komisi), 0) as komisi')
            ->first();

        $dailySetoran = $this->querySetoranPeriode()
            ->groupBy(DB::raw('DATE(tanggal_transaksi)'))
            ->selectRaw('DATE(tanggal_transaksi) as hari, COALESCE(SUM(nominal), 0) as total')
            ->get()
            ->mapWithKeys(fn (TransaksiSetoran $baris): array => [
                Carbon::parse((string) $baris->getAttribute('hari'))->format('d') => (float) $baris->getAttribute('total'),
            ])->all();
        $dailyPenarikan = $this->queryPenarikanPeriode()
            ->groupBy(DB::raw('DATE(waktu_approval)'))
            ->selectRaw('DATE(waktu_approval) as hari, COALESCE(SUM(nominal_diminta), 0) as total')
            ->get()
            ->mapWithKeys(fn (TransaksiPenarikan $baris): array => [
                Carbon::parse((string) $baris->getAttribute('hari'))->format('d') => (float) $baris->getAttribute('total'),
            ])->all();

        $jumlahOverrideRisiko = LogAktivitas::where('aksi', 'selesai_penarikan_override')
            ->whereBetween('timestamp', [Carbon::parse($this->bulan)->startOfMonth(), Carbon::parse($this->bulan)->endOfMonth()])
            ->count();

        $start = Carbon::parse($this->bulan)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return [
            'totalSetoran' => (float) ($setoran?->getAttribute('total') ?? 0),
            'totalPenarikan' => (float) ($penarikan?->getAttribute('diminta') ?? 0),
            'totalKomisi' => (float) ($penarikan?->getAttribute('komisi') ?? 0),
            'jumlahTransaksiSetoran' => (int) ($setoran?->getAttribute('jumlah') ?? 0),
            'jumlahTransaksiPenarikan' => (int) ($penarikan?->getAttribute('jumlah') ?? 0),
            'jumlahOverrideRisiko' => $jumlahOverrideRisiko,
            'dailySetoran' => $dailySetoran,
            'dailyPenarikan' => $dailyPenarikan,
            'days' => (int) $start->diffInDays($end) + 1,
        ];
    }

    /**
     * @return array{
     *     totalSetoran: int,
     *     totalPenarikan: int,
     *     totalKomisi: int,
     *     jumlahTransaksiSetoran: int,
     *     jumlahTransaksiPenarikan: int,
     *     jumlahOverrideRisiko: int,
     *     dailySetoran: array<int|string, float>,
     *     dailyPenarikan: array<int|string, float>,
     *     days: int
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
            'jumlahOverrideRisiko' => 0,
            'dailySetoran' => [],
            'dailyPenarikan' => [],
            'days' => 0,
        ];
    }

    /**
     * Rekap setoran input kolektor dan selisih rekon setoran kantor pada periode berjalan.
     *
     * @return Collection<int, array{nama: string, total_setoran: float, jumlah_transaksi: int, selisih: float}>
     */
    private function kolektorRows(): Collection
    {
        [$mulai, $selesai] = $this->periodeRange();

        $setoran = TransaksiSetoran::query()
            ->masihAktif()
            ->whereNotNull('input_by')
            ->whereBetween('tanggal_transaksi', [$mulai, $selesai])
            ->groupBy('input_by')
            ->selectRaw('input_by, COUNT(*) as jumlah, COALESCE(SUM(nominal), 0) as total')
            ->get();

        $selisih = SetoranKolektorKantor::query()
            ->whereIn('status', ['lebih', 'kurang'])
            ->whereNotNull('selisih')
            ->whereBetween('tanggal_setor', [$mulai, $selesai])
            ->groupBy('kolektor_id')
            ->selectRaw('kolektor_id, COALESCE(SUM(selisih), 0) as total')
            ->get();

        $perId = [];

        foreach ($setoran as $baris) {
            $id = (int) $baris->getAttribute('input_by');
            $perId[$id] = [
                'nama' => '',
                'total_setoran' => (float) $baris->getAttribute('total'),
                'jumlah_transaksi' => (int) $baris->getAttribute('jumlah'),
                'selisih' => 0.0,
            ];
        }

        foreach ($selisih as $baris) {
            $id = (int) $baris->getAttribute('kolektor_id');
            $perId[$id] ??= ['nama' => '', 'total_setoran' => 0.0, 'jumlah_transaksi' => 0, 'selisih' => 0.0];
            $perId[$id]['selisih'] += (float) $baris->getAttribute('total');
        }

        foreach (User::where('role', 'kolektor')->get(['id', 'name']) as $kolektor) {
            $perId[$kolektor->id] ??= ['nama' => '', 'total_setoran' => 0.0, 'jumlah_transaksi' => 0, 'selisih' => 0.0];
            $perId[$kolektor->id]['nama'] = $kolektor->name;
        }

        foreach ($perId as $id => $baris) {
            if ($baris['nama'] === '') {
                $perId[$id]['nama'] = 'Kolektor #'.$id;
            }
        }

        return collect($perId)->sortBy('nama')->values();
    }

    /**
     * Peserta aktif per produk paket: belum ada keputusan akhir dan belum diserahkan.
     *
     * @return Collection<int|string, KepesertaanPaket>
     */
    private function kepesertaanAktifPerProduk(): Collection
    {
        return KepesertaanPaket::query()
            ->whereNull('keputusan_akhir')
            ->where('status_serah_terima', 'belum')
            ->groupBy('produk_id')
            ->selectRaw('produk_id, COUNT(*) as jumlah, COALESCE(SUM(total_aktual_terkumpul), 0) as terkumpul, COALESCE(SUM(tunggakan), 0) as tunggakan')
            ->get()
            ->keyBy('produk_id');
    }

    /**
     * @return Collection<int, array{produk: string, peserta_aktif: int, total_terkumpul: float, total_tunggakan: float}>
     */
    private function paketRows(): Collection
    {
        $statistik = $this->kepesertaanAktifPerProduk();

        return ProdukTabungan::where('tipe', 'paket')
            ->orderBy('nama')
            ->get()
            ->map(function (ProdukTabungan $produk) use ($statistik): array {
                $baris = $statistik->get($produk->id);

                return [
                    'produk' => $produk->nama,
                    'peserta_aktif' => $baris === null ? 0 : (int) $baris->getAttribute('jumlah'),
                    'total_terkumpul' => $baris === null ? 0.0 : (float) $baris->getAttribute('terkumpul'),
                    'total_tunggakan' => $baris === null ? 0.0 : (float) $baris->getAttribute('tunggakan'),
                ];
            });
    }

    /**
     * Kebutuhan barang per item isi paket: peserta aktif × isi_paket.
     *
     * @return array<int, array{produk: string, item: string, jumlah: string, peserta: int, total: ?string}>
     */
    private function barangRows(): array
    {
        $statistik = $this->kepesertaanAktifPerProduk();
        $rows = [];

        foreach (ProdukTabungan::where('tipe', 'paket')->orderBy('nama')->get() as $produk) {
            $isiPaket = $produk->isi_paket;
            if ($isiPaket === null) {
                continue;
            }

            $baris = $statistik->get($produk->id);
            $peserta = $baris === null ? 0 : (int) $baris->getAttribute('jumlah');

            foreach ($isiPaket as $item) {
                $jumlah = (string) data_get($item, 'jumlah', '');

                $rows[] = [
                    'produk' => $produk->nama,
                    'item' => (string) data_get($item, 'nama', '-'),
                    'jumlah' => $jumlah,
                    'peserta' => $peserta,
                    'total' => $this->hitungKebutuhan($peserta, $jumlah),
                ];
            }
        }

        return $rows;
    }

    /**
     * Hitung peserta × angka di awal teks. Bila angka tidak ada, biarkan
     * tampil sebagai ekspresi "N peserta × teks asli".
     */
    private function hitungKebutuhan(int $peserta, string $jumlah): ?string
    {
        if (preg_match('/^\s*([0-9]+(?:[.,][0-9]+)?)/', $jumlah, $cocok) !== 1) {
            return null;
        }

        $angka = (float) str_replace(',', '.', $cocok[1]);
        $satuan = trim(substr($jumlah, strlen($cocok[0])));
        $total = rtrim(rtrim(number_format($peserta * $angka, 2, ',', '.'), '0'), ',');

        return $satuan === '' ? $total : $total.' '.$satuan;
    }

    /**
     * @param  resource  $file
     * @param  Closure(mixed): string  $safe
     */
    private function tulisCsvKeuangan($file, Closure $safe): void
    {
        fputcsv($file, [$safe('Laporan Keuangan - '.ucfirst($this->periode))]);
        fputcsv($file, [$safe('Periode'), $safe($this->periode === 'harian' ? $this->tanggal : $this->bulan)]);
        fputcsv($file, []);

        fputcsv($file, ['RINGKASAN']);
        $data = $this->periode === 'bulanan' ? $this->getBulanan() : $this->getHarian();
        fputcsv($file, ['Total Setoran', $data['totalSetoran']]);
        fputcsv($file, ['Total Penarikan', $data['totalPenarikan']]);
        fputcsv($file, ['Total Komisi', $data['totalKomisi']]);
        fputcsv($file, ['Jumlah Transaksi Setoran', $data['jumlahTransaksiSetoran']]);
        fputcsv($file, ['Jumlah Transaksi Penarikan', $data['jumlahTransaksiPenarikan']]);
        fputcsv($file, []);

        fputcsv($file, ['DETAIL SETORAN']);
        fputcsv($file, ['Tanggal', 'Nasabah', 'Produk', 'Nominal', 'Sumber', 'Status']);
        $this->querySetoranPeriode()
            ->with(['nasabah', 'produk'])
            ->chunkById(200, function (Collection $rows) use ($file, $safe): void {
                foreach ($rows as $item) {
                    fputcsv($file, [
                        Carbon::parse($item->tanggal_transaksi)->format('d/m/Y'),
                        $safe($item->nasabah->name ?? '-'),
                        $safe($item->produk->nama ?? '-'),
                        $item->nominal,
                        $safe($item->sumber_input),
                        $safe($item->status),
                    ]);
                }
            });
        fputcsv($file, []);

        fputcsv($file, ['DETAIL PENARIKAN']);
        fputcsv($file, ['Tanggal', 'Nasabah', 'Produk', 'Diminta', 'Komisi', 'Diterima', 'Status']);
        $this->queryPenarikanPeriode()
            ->with(['nasabah', 'produk'])
            ->chunkById(200, function (Collection $rows) use ($file, $safe): void {
                foreach ($rows as $item) {
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
            });
    }

    /**
     * @param  resource  $file
     * @param  Closure(mixed): string  $safe
     */
    private function tulisCsvKolektor($file, Closure $safe): void
    {
        fputcsv($file, [$safe('Laporan Per Kolektor - '.ucfirst($this->periode))]);
        fputcsv($file, [$safe('Periode'), $safe($this->periode === 'harian' ? $this->tanggal : $this->bulan)]);
        fputcsv($file, []);
        fputcsv($file, ['Kolektor', 'Total Setoran', 'Jumlah Transaksi', 'Selisih Rekon']);

        foreach ($this->kolektorRows() as $baris) {
            fputcsv($file, [
                $safe($baris['nama']),
                $baris['total_setoran'],
                $baris['jumlah_transaksi'],
                $baris['selisih'],
            ]);
        }
    }

    /**
     * @param  resource  $file
     * @param  Closure(mixed): string  $safe
     */
    private function tulisCsvPaket($file, Closure $safe): void
    {
        fputcsv($file, [$safe('Laporan Per Paket')]);
        fputcsv($file, [$safe('Tanggal'), now()->toDateString()]);
        fputcsv($file, []);
        fputcsv($file, ['Paket', 'Peserta Aktif', 'Total Terkumpul', 'Total Tunggakan']);

        foreach ($this->paketRows() as $baris) {
            fputcsv($file, [
                $safe($baris['produk']),
                $baris['peserta_aktif'],
                $baris['total_terkumpul'],
                $baris['total_tunggakan'],
            ]);
        }
    }

    /**
     * @param  resource  $file
     * @param  Closure(mixed): string  $safe
     */
    private function tulisCsvBarang($file, Closure $safe): void
    {
        fputcsv($file, [$safe('Laporan Kebutuhan Barang')]);
        fputcsv($file, [$safe('Tanggal'), now()->toDateString()]);
        fputcsv($file, []);
        fputcsv($file, ['Paket', 'Item', 'Jumlah per Orang', 'Peserta Aktif', 'Total Kebutuhan']);

        foreach ($this->barangRows() as $baris) {
            fputcsv($file, [
                $safe($baris['produk']),
                $safe($baris['item']),
                $safe($baris['jumlah']),
                $baris['peserta'],
                $safe($baris['total'] ?? $baris['peserta'].' peserta × '.$baris['jumlah']),
            ]);
        }
    }
}
