<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\AbsensiKolektor;
use App\Models\IzinKolektor;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use App\Models\LogAktivitas;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use App\Support\CsvSafe;
use App\Support\KasKolektorHitung;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public string $dariTanggal = '';

    public string $sampaiTanggal = '';

    public int $tahun = 0;

    public string $seksi = 'keuangan';

    public int $mutasiNasabahId = 0;

    public function mount(): void
    {
        $this->tanggal = now()->format('Y-m-d');
        $this->bulan = now()->format('Y-m');
        $this->dariTanggal = now()->startOfMonth()->toDateString();
        $this->sampaiTanggal = now()->toDateString();
        $this->tahun = (int) now()->format('Y');
    }

    public function pilihSeksi(string $seksi): void
    {
        if (! in_array($seksi, ['keuangan', 'kolektor', 'paket', 'barang', 'rekon', 'umurkas', 'mutasi', 'penarikan', 'tunggakan', 'serah', 'absensi', 'nasabah'], true)) {
            return;
        }

        $this->seksi = $seksi;
    }

    public function render(): View
    {
        $valid = $this->filterValid();

        $data = match ($this->seksi) {
            'kolektor' => ['kolektorRows' => $valid ? $this->kolektorRows() : collect()],
            'paket' => ['paketRows' => $this->paketRows()],
            'barang' => ['barangRows' => $this->barangRows()],
            'rekon' => $this->rekonData($valid),
            'umurkas' => $this->umurKasData(),
            'mutasi' => $this->mutasiData($valid),
            'penarikan' => $this->penarikanData($valid),
            'tunggakan' => ['tunggakanRows' => $this->tunggakanRows()],
            'serah' => ['serahRows' => $this->serahRows()],
            'absensi' => $this->absensiData($valid),
            'nasabah' => $this->nasabahData(),
            default => $valid
                ? match ($this->periode) {
                    'bulanan' => $this->getBulanan(),
                    default => $this->getHarian(),
                }
            : $this->laporanKosong(),
        };

        $data['jumlahNasabahOffline'] = User::where('role', 'nasabah')
            ->where('mode_akses', 'offline')
            ->count();

        return view('livewire.admin.laporan', $data);
    }

    public function exportCsv(): StreamedResponse
    {
        if (in_array($this->seksi, ['keuangan', 'kolektor', 'rekon', 'absensi'], true)) {
            $this->validate($this->filterRules());
        } elseif ($this->seksi === 'mutasi') {
            $this->validate(array_merge($this->filterRules(), [
                'mutasiNasabahId' => 'required|integer|exists:users,id',
            ]));
        }

        try {
            ActivityLogger::log('ekspor_laporan', 'laporan', 0, [
                'seksi' => $this->seksi,
                'periode' => $this->periode,
                'tanggal' => $this->tanggal,
                'bulan' => $this->bulan,
                'dari' => $this->dariTanggal,
                'sampai' => $this->sampaiTanggal,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        $periodeValue = $this->periode === 'bulanan'
            ? $this->bulan
            : ($this->periode === 'rentang'
                ? $this->dariTanggal.'-'.$this->sampaiTanggal
                : $this->tanggal);
        $filename = match ($this->seksi) {
            'kolektor' => 'laporan-kolektor-'.$this->periode.'-'.$periodeValue.'.csv',
            'rekon' => 'laporan-rekon-'.$this->periode.'-'.$periodeValue.'.csv',
            'umurkas' => 'laporan-umur-kas-'.now()->toDateString().'.csv',
            'mutasi' => 'laporan-mutasi-'.$this->periode.'-'.$periodeValue.'.csv',
            'penarikan' => 'laporan-penarikan-'.$this->periode.'-'.$periodeValue.'.csv',
            'tunggakan' => 'laporan-tunggakan-'.now()->toDateString().'.csv',
            'serah' => 'laporan-serah-terima-'.now()->toDateString().'.csv',
            'absensi' => 'laporan-absensi-'.$this->periode.'-'.$periodeValue.'.csv',
            'nasabah' => 'laporan-nasabah-'.now()->toDateString().'.csv',
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
        $safe = fn ($value): string => CsvSafe::teks($value);

        $callback = function () use ($safe) {
            $file = fopen('php://output', 'w');
            if ($file === false) {
                return;
            }

            match ($this->seksi) {
                'kolektor' => $this->tulisCsvKolektor($file, $safe),
                'rekon' => $this->tulisCsvRekon($file, $safe),
                'umurkas' => $this->tulisCsvUmurKas($file, $safe),
                'mutasi' => $this->tulisCsvMutasi($file, $safe),
                'penarikan' => $this->tulisCsvPenarikan($file, $safe),
                'tunggakan' => $this->tulisCsvTunggakan($file, $safe),
                'serah' => $this->tulisCsvSerah($file, $safe),
                'absensi' => $this->tulisCsvAbsensi($file, $safe),
                'nasabah' => $this->tulisCsvNasabah($file, $safe),
                'paket' => $this->tulisCsvPaket($file, $safe),
                'barang' => $this->tulisCsvBarang($file, $safe),
                default => $this->tulisCsvKeuangan($file, $safe),
            };

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * @return array{periode: string, tanggal: string, bulan: string, dariTanggal: array<int, string>, sampaiTanggal: array<int, string>}
     */
    private function filterRules(): array
    {
        return [
            'periode' => 'required|in:harian,bulanan,rentang',
            'tanggal' => 'required|date_format:Y-m-d',
            'bulan' => 'required|date_format:Y-m',
            'dariTanggal' => ['nullable', 'required_if:periode,rentang', 'date_format:Y-m-d'],
            'sampaiTanggal' => ['nullable', 'required_if:periode,rentang', 'date_format:Y-m-d', 'after_or_equal:dariTanggal'],
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

        if ($this->periode === 'rentang') {
            return [$this->dariTanggal, $this->sampaiTanggal];
        }

        return [$this->tanggal, $this->tanggal];
    }

    /**
     * Batas timestamp inklusif (harian, bulanan, maupun rentang).
     *
     * @param  array{0: string, 1: string}  $range
     * @return array{0: string, 1: string}
     */
    private function batasWaktu(array $range): array
    {
        return [$range[0].' 00:00:00', $range[1].' 23:59:59'];
    }

    /**
     * @return Builder<TransaksiSetoran>
     */
    private function querySetoranPeriode(): Builder
    {
        [$mulai, $selesai] = $this->periodeRange();

        return TransaksiSetoran::query()
            ->masihAktif()
            ->whereBetween('tanggal_transaksi', [$mulai, $selesai]);
    }

    /**
     * @return Builder<TransaksiPenarikan>
     */
    private function queryPenarikanPeriode(): Builder
    {
        $query = TransaksiPenarikan::query()->whereIn('status', ['approved', 'selesai']);
        $query->whereBetween('waktu_approval', $this->batasWaktu($this->periodeRange()));

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
        $setoran = $this->querySetoranPeriode()
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(nominal), 0) as total')
            ->first();
        $penarikan = $this->queryPenarikanPeriode()
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(nominal_diminta), 0) as diminta, COALESCE(SUM(nominal_komisi), 0) as komisi')
            ->first();
        $jumlahOverrideRisiko = LogAktivitas::where('aksi', 'selesai_penarikan_override')
            ->whereBetween('timestamp', $this->batasWaktu($this->periodeRange()))
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
     * Rekap setoran input kolektor, selisih rekon, penarikan tunai dibayar,
     * setor kantor (periode), dan kas di tangan D13 (posisi terkini).
     *
     * @return Collection<int, array{nama: string, total_setoran: float, jumlah_transaksi: int, selisih: float, penarikan_dibayar: float, setor_kantor: float, kas_di_tangan: float}>
     */
    private function kolektorRows(): Collection
    {
        [$mulai, $selesai] = $this->periodeRange();

        $kosong = fn (): array => [
            'nama' => '',
            'total_setoran' => 0.0,
            'jumlah_transaksi' => 0,
            'selisih' => 0.0,
            'penarikan_dibayar' => 0.0,
            'setor_kantor' => 0.0,
            'kas_di_tangan' => 0.0,
        ];

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

        $penarikanTunai = TransaksiPenarikan::query()
            ->whereIn('status', ['approved', 'selesai'])
            ->whereNotNull('dibayar_oleh')
            ->whereBetween('waktu_approval', $this->batasWaktu($this->periodeRange()))
            ->groupBy('dibayar_oleh')
            ->selectRaw('dibayar_oleh, COALESCE(SUM(nominal_diterima), 0) as total')
            ->get();

        $setorKantor = SetoranKolektorKantor::query()
            ->where('status', '!=', 'dibatalkan')
            ->whereBetween('tanggal_setor', [$mulai, $selesai])
            ->groupBy('kolektor_id')
            ->selectRaw('kolektor_id, COALESCE(SUM(total_seharusnya), 0) as total')
            ->get();

        $kasPerKolektor = KasKolektorHitung::kasDiTanganPerKolektor();

        $perId = [];

        foreach ($setoran as $baris) {
            $id = (int) $baris->getAttribute('input_by');
            $perId[$id] = $kosong();
            $perId[$id]['total_setoran'] = (float) $baris->getAttribute('total');
            $perId[$id]['jumlah_transaksi'] = (int) $baris->getAttribute('jumlah');
        }

        foreach ($selisih as $baris) {
            $id = (int) $baris->getAttribute('kolektor_id');
            $perId[$id] ??= $kosong();
            $perId[$id]['selisih'] += (float) $baris->getAttribute('total');
        }

        foreach ($penarikanTunai as $baris) {
            $id = (int) $baris->getAttribute('dibayar_oleh');
            $perId[$id] ??= $kosong();
            $perId[$id]['penarikan_dibayar'] = (float) $baris->getAttribute('total');
        }

        foreach ($setorKantor as $baris) {
            $id = (int) $baris->getAttribute('kolektor_id');
            $perId[$id] ??= $kosong();
            $perId[$id]['setor_kantor'] = (float) $baris->getAttribute('total');
        }

        foreach ($kasPerKolektor as $id => $kas) {
            $perId[$id] ??= $kosong();
            $perId[$id]['kas_di_tangan'] = $kas;
        }

        foreach (User::where('role', 'kolektor')->get(['id', 'name']) as $kolektor) {
            $perId[$kolektor->id] ??= $kosong();
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
     * Data seksi rekonsiliasi kas: rekap per kolektor, detail pengajuan, dan total.
     *
     * @return array{
     *     rekonRows: array<int, array{kolektor_id: int, nama: string, jumlah: int, seharusnya: float, diterima: float, selisih: float}>,
     *     rekonDetail: array<int, SetoranKolektorKantor>,
     *     rekonTotal: array{jumlah: int, seharusnya: float, diterima: float, selisih: float}
     * }
     */
    private function rekonData(bool $valid): array
    {
        $rows = $valid ? $this->rekonRows() : [];
        $detail = $valid ? $this->rekonDetail() : [];

        return [
            'rekonRows' => $rows,
            'rekonDetail' => $detail,
            'rekonTotal' => $this->rekonTotal($rows),
        ];
    }

    /**
     * Rekap pengajuan setor ke kantor per kolektor pada periode.
     *
     * @return array<int, array{kolektor_id: int, nama: string, jumlah: int, seharusnya: float, diterima: float, selisih: float}>
     */
    private function rekonRows(): array
    {
        [$mulai, $selesai] = $this->periodeRange();

        $agregat = SetoranKolektorKantor::query()
            ->whereBetween('tanggal_setor', [$mulai, $selesai])
            ->groupBy('kolektor_id')
            ->selectRaw('kolektor_id, COUNT(*) as jumlah, COALESCE(SUM(total_seharusnya), 0) as seharusnya, COALESCE(SUM(total_diterima), 0) as diterima, COALESCE(SUM(selisih), 0) as selisih')
            ->get();

        $nama = User::where('role', 'kolektor')->pluck('name', 'id')->all();

        $rows = [];
        foreach ($agregat as $baris) {
            $id = (int) $baris->getAttribute('kolektor_id');
            $rows[] = [
                'kolektor_id' => $id,
                'nama' => (string) ($nama[$id] ?? 'Kolektor #'.$id),
                'jumlah' => (int) $baris->getAttribute('jumlah'),
                'seharusnya' => (float) $baris->getAttribute('seharusnya'),
                'diterima' => (float) $baris->getAttribute('diterima'),
                'selisih' => (float) $baris->getAttribute('selisih'),
            ];
        }

        usort($rows, fn (array $a, array $b): int => strcmp($a['nama'], $b['nama']));

        return $rows;
    }

    /**
     * Detail pengajuan setor ke kantor pada periode, terbaru dulu.
     *
     * @return array<int, SetoranKolektorKantor>
     */
    private function rekonDetail(): array
    {
        [$mulai, $selesai] = $this->periodeRange();

        return SetoranKolektorKantor::query()
            ->with(['kolektor', 'diterimaOleh'])
            ->whereBetween('tanggal_setor', [$mulai, $selesai])
            ->orderByDesc('tanggal_setor')
            ->orderByDesc('id')
            ->get()
            ->all();
    }

    /**
     * @param  array<int, array{jumlah: int, seharusnya: float, diterima: float, selisih: float}>  $rows
     * @return array{jumlah: int, seharusnya: float, diterima: float, selisih: float}
     */
    private function rekonTotal(array $rows): array
    {
        $total = ['jumlah' => 0, 'seharusnya' => 0.0, 'diterima' => 0.0, 'selisih' => 0.0];

        foreach ($rows as $baris) {
            $total['jumlah'] += $baris['jumlah'];
            $total['seharusnya'] += $baris['seharusnya'];
            $total['diterima'] += $baris['diterima'];
            $total['selisih'] += $baris['selisih'];
        }

        return $total;
    }

    /**
     * Data seksi umur kas: posisi terkini per kolektor dikelompokkan 0-1,
     * 2-3, dan lebih dari 3 hari (selaras KasKolektor::kumpulkanKas()).
     *
     * @return array{
     *     umurKasRows: array<int, array{kolektor_id: int, nama: string, kas_di_tangan: float, umur_terlama_hari: int, lewat_batas: bool}>,
     *     umurKelompok: array<string, array{jumlah: int, kas: float}>,
     *     umurTotal: array{kas: float, jumlah: int, lewat_batas: int}
     * }
     */
    private function umurKasData(): array
    {
        $rows = KasKolektor::kumpulkanKas();

        return [
            'umurKasRows' => $rows,
            'umurKelompok' => $this->umurKelompok($rows),
            'umurTotal' => [
                'kas' => array_sum(array_column($rows, 'kas_di_tangan')),
                'jumlah' => count($rows),
                'lewat_batas' => count(array_filter($rows, fn (array $baris): bool => $baris['lewat_batas'])),
            ],
        ];
    }

    /**
     * @param  array<int, array{umur_terlama_hari: int, kas_di_tangan: float}>  $rows
     * @return array<string, array{jumlah: int, kas: float}>
     */
    private function umurKelompok(array $rows): array
    {
        $akumulasi = [];

        foreach ($rows as $baris) {
            $kunci = $this->kelompokUmur((int) $baris['umur_terlama_hari']);
            $akumulasi[$kunci] = [
                'jumlah' => ($akumulasi[$kunci]['jumlah'] ?? 0) + 1,
                'kas' => ($akumulasi[$kunci]['kas'] ?? 0.0) + $baris['kas_di_tangan'],
            ];
        }

        return [
            '0-1 hari' => $akumulasi['0-1 hari'] ?? ['jumlah' => 0, 'kas' => 0.0],
            '2-3 hari' => $akumulasi['2-3 hari'] ?? ['jumlah' => 0, 'kas' => 0.0],
            '>3 hari' => $akumulasi['>3 hari'] ?? ['jumlah' => 0, 'kas' => 0.0],
        ];
    }

    private function kelompokUmur(int $umur): string
    {
        return match (true) {
            $umur <= 1 => '0-1 hari',
            $umur <= 3 => '2-3 hari',
            default => '>3 hari',
        };
    }

    /**
     * Normalisasi nilai uang ke numeric-string untuk perhitungan bcmath.
     *
     * @return numeric-string
     */
    private function uang(mixed $nilai): string
    {
        $teks = (string) $nilai;

        return is_numeric($teks) ? $teks : '0.00';
    }

    /**
     * Buku tabungan per nasabah: kronologi setoran/penarikan pada periode
     * dengan saldo berjalan (pola Rekonsiliasi D14, siap cetak).
     *
     * @return array{
     *     mutasiRows: array<int, array{tanggal: string, tipe: string, produk: string, nominal: float, arah: int, saldo: float}>,
     *     mutasiRingkas: array{saldoAwal: float, saldoAkhir: float, totalSetoran: float, totalPenarikan: float, jumlahMutasi: int},
     *     mutasiNasabahList: array<int, array{id: int, nama: string}>
     * }
     */
    private function mutasiData(bool $valid): array
    {
        $daftar = User::where('role', 'nasabah')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $nasabah): array => ['id' => (int) $nasabah->id, 'nama' => (string) $nasabah->name])
            ->all();

        $kosong = [
            'mutasiRows' => [],
            'mutasiRingkas' => [
                'saldoAwal' => 0.0,
                'saldoAkhir' => 0.0,
                'totalSetoran' => 0.0,
                'totalPenarikan' => 0.0,
                'jumlahMutasi' => 0,
            ],
            'mutasiNasabahList' => $daftar,
        ];

        if (! $valid || $this->mutasiNasabahId <= 0) {
            return $kosong;
        }

        $nasabahId = $this->mutasiNasabahId;
        [$mulai, $selesai] = $this->periodeRange();

        $setoran = TransaksiSetoran::with('produk')
            ->where('nasabah_id', $nasabahId)
            ->where('status', '!=', 'dibatalkan')
            ->orderBy('tanggal_transaksi')
            ->orderBy('id')
            ->get();

        $penarikan = TransaksiPenarikan::with('produk')
            ->where('nasabah_id', $nasabahId)
            ->whereIn('status', ['approved', 'selesai'])
            ->orderBy('waktu_approval')
            ->orderBy('id')
            ->get();

        $kronologi = $setoran->map(fn (TransaksiSetoran $s): array => [
            'tanggal' => Carbon::parse($s->tanggal_transaksi)->format('Y-m-d'),
            'waktu' => Carbon::parse($s->tanggal_transaksi)->format('Y-m-d 00:00:00'),
            'tipe' => 'Setoran',
            'produk' => $s->produk->nama ?? '-',
            'nominal' => (float) $s->nominal,
            'arah' => 1,
        ])->concat($penarikan->map(fn (TransaksiPenarikan $p): array => [
            'tanggal' => Carbon::parse($p->waktu_approval)->format('Y-m-d'),
            'waktu' => Carbon::parse($p->waktu_approval)->format('Y-m-d H:i:s'),
            'tipe' => 'Penarikan',
            'produk' => $p->produk->nama ?? '-',
            'nominal' => (float) $p->nominal_diminta,
            'arah' => -1,
        ]))->sortBy('waktu')->values();

        $saldoSekarang = (float) SaldoProduk::where('nasabah_id', $nasabahId)->sum('saldo');

        $mundur = $this->uang($saldoSekarang);
        foreach ($kronologi as $baris) {
            if ($baris['tanggal'] >= $mulai) {
                $mundur = $baris['arah'] > 0
                    ? bcsub($mundur, $this->uang($baris['nominal']), 2)
                    : bcadd($mundur, $this->uang($baris['nominal']), 2);
            }
        }

        $rows = [];
        $saldo = $mundur;
        $totalSetoran = '0.00';
        $totalPenarikan = '0.00';

        foreach ($kronologi as $baris) {
            if ($baris['tanggal'] < $mulai || $baris['tanggal'] > $selesai) {
                continue;
            }

            if ($baris['arah'] > 0) {
                $saldo = bcadd($saldo, $this->uang($baris['nominal']), 2);
                $totalSetoran = bcadd($totalSetoran, $this->uang($baris['nominal']), 2);
            } else {
                $saldo = bcsub($saldo, $this->uang($baris['nominal']), 2);
                $totalPenarikan = bcadd($totalPenarikan, $this->uang($baris['nominal']), 2);
            }

            $rows[] = [
                'tanggal' => $baris['tanggal'],
                'tipe' => $baris['tipe'],
                'produk' => $baris['produk'],
                'nominal' => $baris['nominal'],
                'arah' => $baris['arah'],
                'saldo' => (float) $saldo,
            ];
        }

        return [
            'mutasiRows' => $rows,
            'mutasiRingkas' => [
                'saldoAwal' => (float) $mundur,
                'saldoAkhir' => (float) $saldo,
                'totalSetoran' => (float) $totalSetoran,
                'totalPenarikan' => (float) $totalPenarikan,
                'jumlahMutasi' => count($rows),
            ],
            'mutasiNasabahList' => $daftar,
        ];
    }

    /**
     * Penarikan pada periode (filter saat pengajuan/created_at): rekap per
     * status dan detail lokasi, waktu proses, serta alasan batal.
     *
     * @return array{
     *     penarikanRows: array<int, array{id: int, tanggal: string, nasabah: string, produk: string, diminta: float, komisi: float, diterima: float, status: string, lokasi: string, waktu_proses: ?string, alasan: string}>,
     *     penarikanRekap: array<int, array{status: string, label: string, jumlah: int, total: float}>
     * }
     */
    private function penarikanData(bool $valid): array
    {
        $label = static fn (string $status): array => [
            'status' => $status,
            'label' => ucfirst($status),
            'jumlah' => 0,
            'total' => 0.0,
        ];

        $rekap = array_map($label, ['pending', 'approved', 'selesai', 'ditolak', 'dibatalkan', 'kedaluwarsa']);

        if (! $valid) {
            return ['penarikanRows' => [], 'penarikanRekap' => $rekap];
        }

        $rows = TransaksiPenarikan::with(['nasabah', 'produk'])
            ->whereBetween('created_at', $this->batasWaktu($this->periodeRange()))
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->map(fn (TransaksiPenarikan $item): array => [
                'id' => (int) $item->id,
                'tanggal' => Carbon::parse($item->created_at)->format('d/m/Y'),
                'nasabah' => $item->nasabah->name ?? '-',
                'produk' => $item->produk->nama ?? '-',
                'diminta' => (float) $item->nominal_diminta,
                'komisi' => (float) $item->nominal_komisi,
                'diterima' => (float) $item->nominal_diterima,
                'status' => (string) $item->status,
                'lokasi' => match ($item->lokasi_pengambilan) {
                    'kantor' => 'Kantor',
                    default => 'Rumah Kolektor',
                },
                'waktu_proses' => $item->waktu_approval !== null
                    ? Carbon::parse($item->waktu_approval)->format('d/m/Y H:i')
                    : null,
                'alasan' => $item->alasan_batal ?? '-',
            ])->all();

        foreach ($rows as $baris) {
            foreach ($rekap as $i => $ringkas) {
                if ($ringkas['status'] === $baris['status']) {
                    $rekap[$i]['jumlah']++;
                    $rekap[$i]['total'] += $baris['diminta'];
                    break;
                }
            }
        }

        return ['penarikanRows' => $rows, 'penarikanRekap' => $rekap];
    }

    /**
     * Nasabah menunggak pada paket: hari & rupiah tunggakan, status alert,
     * dan keputusan akhir. Posisi terkini tanpa filter periode (konsisten
     * seksi paket).
     *
     * @return array<int, array{id: int, nasabah: string, produk: string, tunggakan_hari: int, tunggakan_rupiah: float, status_alert: string, keputusan_akhir: string, ditunda_hingga: ?string}>
     */
    private function tunggakanRows(): array
    {
        return KepesertaanPaket::query()
            ->with(['nasabah', 'produk'])
            ->where('tunggakan', '>', 0)
            ->orderByDesc('tunggakan')
            ->orderBy('id')
            ->get()
            ->map(function (KepesertaanPaket $item): array {
                $hargaPerHari = (float) ($item->produk->harga_per_hari ?? 0);

                return [
                    'id' => (int) $item->id,
                    'nasabah' => $item->nasabah->name ?? '-',
                    'produk' => $item->produk->nama ?? '-',
                    'tunggakan_hari' => (int) (float) $item->tunggakan,
                    'tunggakan_rupiah' => round((float) $item->tunggakan * $hargaPerHari, 2),
                    'status_alert' => match ($item->status_alert) {
                        'peringatan' => 'Peringatan',
                        'perlu_review' => 'Perlu Review',
                        default => 'Normal',
                    },
                    'keputusan_akhir' => match ($item->keputusan_akhir) {
                        'lanjut' => 'Lanjut',
                        'gagal_dikembalikan' => 'Gagal Dikembalikan',
                        'gagal_dialihkan' => 'Gagal Dialihkan',
                        default => '-',
                    },
                    'ditunda_hingga' => $item->ditunda_hingga !== null
                        ? Carbon::parse($item->ditunda_hingga)->format('d/m/Y')
                        : null,
                ];
            })
            ->all();
    }

    /**
     * Status serah terima paket: status, metode, penerima, tanggal, dan
     * tautan foto bukti. Posisi terkini tanpa filter periode.
     *
     * @return array<int, array{id: int, nasabah: string, produk: string, status: string, status_kunci: string, metode: string, penerima: string, tanggal: ?string, foto: ?string}>
     */
    private function serahRows(): array
    {
        return KepesertaanPaket::query()
            ->with(['nasabah', 'produk'])
            ->orderBy('status_serah_terima')
            ->orderByDesc('tanggal_serah_terima')
            ->orderBy('id')
            ->get()
            ->map(function (KepesertaanPaket $item): array {
                return [
                    'id' => (int) $item->id,
                    'nasabah' => $item->nasabah->name ?? '-',
                    'produk' => $item->produk->nama ?? '-',
                    'status' => $item->status_serah_terima === 'sudah_diterima' ? 'Sudah Diterima' : 'Belum',
                    'status_kunci' => (string) $item->status_serah_terima,
                    'metode' => match ($item->metode_pengambilan) {
                        'ambil_sendiri' => 'Ambil Sendiri',
                        'diantar_kolektor' => 'Diantar Kolektor',
                        default => '-',
                    },
                    'penerima' => $item->diterima_oleh ?? '-',
                    'tanggal' => $item->tanggal_serah_terima !== null
                        ? Carbon::parse($item->tanggal_serah_terima)->format('d/m/Y')
                        : null,
                    'foto' => $item->bukti_foto_url,
                ];
            })
            ->all();
    }

    /**
     * Absensi kolektor (hadir per hari, jam masuk/keluar) dan daftar izin
     * yang tumpang tindih dengan periode, plus rekap singkat.
     *
     * @return array{
     *     absensiRows: array<int, array{tanggal: string, kolektor: string, jam_masuk: string, jam_keluar: string|null}>,
     *     izinRows: array<int, array{kolektor: string, dari: string, sampai: string, alasan: string, status: string, pemroses: string, catatan: string|null}>,
     *     absensiRekap: array{hadir: int, izinDisetujui: int, izinPending: int}
     * }
     */
    private function absensiData(bool $valid): array
    {
        if (! $valid) {
            return [
                'absensiRows' => [],
                'izinRows' => [],
                'absensiRekap' => ['hadir' => 0, 'izinDisetujui' => 0, 'izinPending' => 0],
            ];
        }

        [$mulai, $selesai] = $this->periodeRange();

        $absensiRows = AbsensiKolektor::with('kolektor')
            ->whereBetween('tanggal', [$mulai, $selesai])
            ->orderByDesc('tanggal')
            ->orderBy('kolektor_id')
            ->get()
            ->map(fn (AbsensiKolektor $item): array => [
                'tanggal' => Carbon::parse($item->tanggal)->format('d/m/Y'),
                'kolektor' => $item->kolektor->name ?? '-',
                'jam_masuk' => substr((string) $item->waktu_masuk, 0, 5),
                'jam_keluar' => $item->waktu_keluar !== null
                    ? substr((string) $item->waktu_keluar, 0, 5)
                    : null,
            ])
            ->all();

        $izinRows = IzinKolektor::with(['kolektor', 'diprosesOleh'])
            ->where('tanggal_mulai', '<=', $selesai)
            ->where('tanggal_selesai', '>=', $mulai)
            ->orderByDesc('tanggal_mulai')
            ->orderBy('id')
            ->get()
            ->map(fn (IzinKolektor $item): array => [
                'kolektor' => $item->kolektor->name ?? '-',
                'dari' => Carbon::parse($item->tanggal_mulai)->format('d/m/Y'),
                'sampai' => Carbon::parse($item->tanggal_selesai)->format('d/m/Y'),
                'alasan' => (string) $item->alasan,
                'status' => match ($item->status) {
                    'disetujui' => 'Disetujui',
                    'ditolak' => 'Ditolak',
                    default => 'Pending',
                },
                'pemroses' => $item->diprosesOleh->name ?? '-',
                'catatan' => $item->catatan_admin,
            ])
            ->all();

        return [
            'absensiRows' => $absensiRows,
            'izinRows' => $izinRows,
            'absensiRekap' => [
                'hadir' => count($absensiRows),
                'izinDisetujui' => count(array_filter($izinRows, fn (array $baris): bool => $baris['status'] === 'Disetujui')),
                'izinPending' => count(array_filter($izinRows, fn (array $baris): bool => $baris['status'] === 'Pending')),
            ],
        ];
    }

    /**
     * Posisi terkini nasabah: status pendaftaran, mode akses, dan kolektor
     * penanggung jawab (tanpa filter periode).
     *
     * @return array{
     *     nasabahRows: array<int, array{id: int, nama: string, no_hp: string, status_pendaftaran: string, mode_akses: string, kolektor: string}>,
     *     nasabahRekap: array{aktif: int, pending: int, ditolak: int, digital: int, offline: int}
     * }
     */
    private function nasabahData(): array
    {
        $kolektorPerNasabah = KolektorNasabah::where('status', 'aktif')
            ->get(['nasabah_id', 'kolektor_id'])
            ->mapWithKeys(fn (KolektorNasabah $baris): array => [
                (int) $baris->nasabah_id => (int) $baris->kolektor_id,
            ]);

        $namaKolektor = User::query()
            ->whereIn('id', $kolektorPerNasabah->unique()->values())
            ->pluck('name', 'id');

        $nasabahRows = User::with('nasabahProfil')
            ->where('role', 'nasabah')
            ->orderBy('name')
            ->get()
            ->map(function (User $nasabah) use ($kolektorPerNasabah, $namaKolektor): array {
                $kolektorId = $kolektorPerNasabah->get((int) $nasabah->id);
                $profil = $nasabah->nasabahProfil;

                return [
                    'id' => (int) $nasabah->id,
                    'nama' => (string) $nasabah->name,
                    'no_hp' => (string) ($nasabah->no_hp ?? '-'),
                    'status_pendaftaran' => match ($profil?->status_pendaftaran) {
                        'aktif' => 'Aktif',
                        'pending_verifikasi' => 'Pending Verifikasi',
                        'ditolak' => 'Ditolak',
                        default => '-',
                    },
                    'mode_akses' => $nasabah->mode_akses === 'offline' ? 'Offline' : 'Digital',
                    'kolektor' => $kolektorId !== null
                        ? (string) ($namaKolektor[$kolektorId] ?? 'Kolektor #'.$kolektorId)
                        : '-',
                ];
            })
            ->all();

        return [
            'nasabahRows' => $nasabahRows,
            'nasabahRekap' => [
                'aktif' => count(array_filter($nasabahRows, fn (array $baris): bool => $baris['status_pendaftaran'] === 'Aktif')),
                'pending' => count(array_filter($nasabahRows, fn (array $baris): bool => $baris['status_pendaftaran'] === 'Pending Verifikasi')),
                'ditolak' => count(array_filter($nasabahRows, fn (array $baris): bool => $baris['status_pendaftaran'] === 'Ditolak')),
                'digital' => count(array_filter($nasabahRows, fn (array $baris): bool => $baris['mode_akses'] === 'Digital')),
                'offline' => count(array_filter($nasabahRows, fn (array $baris): bool => $baris['mode_akses'] === 'Offline')),
            ],
        ];
    }

    /**
     * Label periode untuk baris "Periode" pada CSV.
     */
    private function labelPeriodeCsv(): string
    {
        return match ($this->periode) {
            'bulanan' => $this->bulan,
            'rentang' => $this->dariTanggal.' s/d '.$this->sampaiTanggal,
            default => $this->tanggal,
        };
    }

    /**
     * @param  resource  $file
     * @param  Closure(mixed): string  $safe
     */
    private function tulisCsvKeuangan($file, Closure $safe): void
    {
        fputcsv($file, [$safe('Laporan Keuangan - '.ucfirst($this->periode))]);
        fputcsv($file, [$safe('Periode'), $safe($this->labelPeriodeCsv())]);
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
        fputcsv($file, [$safe('Periode'), $safe($this->labelPeriodeCsv())]);
        fputcsv($file, []);
        fputcsv($file, ['Kolektor', 'Total Setoran', 'Jumlah Transaksi', 'Selisih Rekon', 'Penarikan Tunai Dibayar', 'Kas di Tangan', 'Setor Kantor']);

        foreach ($this->kolektorRows() as $baris) {
            fputcsv($file, [
                $safe($baris['nama']),
                $baris['total_setoran'],
                $baris['jumlah_transaksi'],
                $baris['selisih'],
                $baris['penarikan_dibayar'],
                $baris['kas_di_tangan'],
                $baris['setor_kantor'],
            ]);
        }
    }

    /**
     * @param  resource  $file
     * @param  Closure(mixed): string  $safe
     */
    private function tulisCsvRekon($file, Closure $safe): void
    {
        fputcsv($file, [$safe('Laporan Rekonsiliasi Kas - '.ucfirst($this->periode))]);
        fputcsv($file, [$safe('Periode'), $safe($this->labelPeriodeCsv())]);
        fputcsv($file, []);

        $rows = $this->rekonData(true);

        fputcsv($file, ['REKON PER KOLEKTOR']);
        fputcsv($file, ['Kolektor', 'Pengajuan', 'Seharusnya', 'Diterima', 'Selisih']);
        foreach ($rows['rekonRows'] as $baris) {
            fputcsv($file, [
                $safe($baris['nama']),
                $baris['jumlah'],
                $baris['seharusnya'],
                $baris['diterima'],
                $baris['selisih'],
            ]);
        }
        fputcsv($file, [
            'TOTAL',
            $rows['rekonTotal']['jumlah'],
            $rows['rekonTotal']['seharusnya'],
            $rows['rekonTotal']['diterima'],
            $rows['rekonTotal']['selisih'],
        ]);
        fputcsv($file, []);

        fputcsv($file, ['DETAIL PENGAJUAN']);
        fputcsv($file, ['Tanggal', 'Kolektor', 'Seharusnya', 'Diterima', 'Selisih', 'Keterangan', 'Penerima']);
        foreach ($rows['rekonDetail'] as $item) {
            fputcsv($file, [
                Carbon::parse($item->tanggal_setor)->format('d/m/Y'),
                $safe($item->kolektor->name ?? '-'),
                $item->total_seharusnya,
                $item->total_diterima,
                $item->selisih ?? 0,
                $safe($item->keterangan_selisih ?? '-'),
                $safe($item->diterimaOleh->name ?? '-'),
            ]);
        }
    }

    /**
     * @param  resource  $file
     * @param  Closure(mixed): string  $safe
     */
    private function tulisCsvUmurKas($file, Closure $safe): void
    {
        fputcsv($file, [$safe('Laporan Umur Kas')]);
        fputcsv($file, [$safe('Tanggal'), now()->toDateString()]);
        fputcsv($file, []);

        $data = $this->umurKasData();

        fputcsv($file, ['UMUR PER KELOMPOK']);
        fputcsv($file, ['Kelompok', 'Jumlah Kolektor', 'Kas di Tangan']);
        foreach ($data['umurKelompok'] as $kelompok => $ringkas) {
            fputcsv($file, [$safe($kelompok), $ringkas['jumlah'], $ringkas['kas']]);
        }
        fputcsv($file, ['TOTAL', $data['umurTotal']['jumlah'], $data['umurTotal']['kas']]);
        fputcsv($file, []);

        fputcsv($file, ['UMUR PER KOLEKTOR']);
        fputcsv($file, ['Kolektor', 'Kas di Tangan', 'Umur Terlama (hari)', 'Kelompok', 'Lewat Batas']);
        foreach ($data['umurKasRows'] as $baris) {
            fputcsv($file, [
                $safe($baris['nama']),
                $baris['kas_di_tangan'],
                $baris['umur_terlama_hari'],
                $safe($this->kelompokUmur($baris['umur_terlama_hari'])),
                $safe($baris['lewat_batas'] ? 'Ya' : 'Tidak'),
            ]);
        }
    }

    /**
     * @param  resource  $file
     * @param  Closure(mixed): string  $safe
     */
    private function tulisCsvMutasi($file, Closure $safe): void
    {
        $data = $this->mutasiData(true);
        $nasabah = User::find($this->mutasiNasabahId);

        fputcsv($file, [$safe('Laporan Mutasi Nasabah - '.ucfirst($this->periode))]);
        fputcsv($file, [$safe('Periode'), $safe($this->labelPeriodeCsv())]);
        fputcsv($file, [$safe('Nasabah'), $safe($nasabah->name ?? '-')]);
        fputcsv($file, ['Saldo Awal', $data['mutasiRingkas']['saldoAwal']]);
        fputcsv($file, []);

        fputcsv($file, ['Tanggal', 'Tipe', 'Produk', 'Nominal', 'Saldo Berjalan']);
        foreach ($data['mutasiRows'] as $baris) {
            fputcsv($file, [
                $baris['tanggal'],
                $safe($baris['tipe']),
                $safe($baris['produk']),
                $baris['nominal'],
                $baris['saldo'],
            ]);
        }
        fputcsv($file, []);

        fputcsv($file, ['Saldo Akhir', $data['mutasiRingkas']['saldoAkhir']]);
        fputcsv($file, ['Total Setoran', $data['mutasiRingkas']['totalSetoran']]);
        fputcsv($file, ['Total Penarikan', $data['mutasiRingkas']['totalPenarikan']]);
        fputcsv($file, ['Jumlah Mutasi', $data['mutasiRingkas']['jumlahMutasi']]);
    }

    /**
     * @param  resource  $file
     * @param  Closure(mixed): string  $safe
     */
    private function tulisCsvPenarikan($file, Closure $safe): void
    {
        $data = $this->penarikanData(true);

        fputcsv($file, [$safe('Laporan Penarikan - '.ucfirst($this->periode))]);
        fputcsv($file, [$safe('Periode'), $safe($this->labelPeriodeCsv())]);
        fputcsv($file, []);

        fputcsv($file, ['REKAP PER STATUS']);
        fputcsv($file, ['Status', 'Jumlah', 'Total Diminta']);
        foreach ($data['penarikanRekap'] as $ringkas) {
            fputcsv($file, [$safe($ringkas['label']), $ringkas['jumlah'], $ringkas['total']]);
        }
        fputcsv($file, []);

        fputcsv($file, ['DETAIL PENARIKAN']);
        fputcsv($file, ['Tanggal Pengajuan', 'Nasabah', 'Produk', 'Diminta', 'Komisi', 'Diterima', 'Status', 'Lokasi', 'Waktu Proses', 'Alasan']);
        foreach ($data['penarikanRows'] as $baris) {
            fputcsv($file, [
                $baris['tanggal'],
                $safe($baris['nasabah']),
                $safe($baris['produk']),
                $baris['diminta'],
                $baris['komisi'],
                $baris['diterima'],
                $safe($baris['status']),
                $safe($baris['lokasi']),
                $safe($baris['waktu_proses'] ?? '-'),
                $safe($baris['alasan']),
            ]);
        }
    }

    /**
     * @param  resource  $file
     * @param  Closure(mixed): string  $safe
     */
    private function tulisCsvTunggakan($file, Closure $safe): void
    {
        fputcsv($file, [$safe('Laporan Tunggakan & Paket Gagal')]);
        fputcsv($file, [$safe('Tanggal'), now()->toDateString()]);
        fputcsv($file, []);
        fputcsv($file, ['Nasabah', 'Produk', 'Tunggakan (hari)', 'Tunggakan (Rp)', 'Status Alert', 'Keputusan Akhir', 'Ditunda Hingga']);

        foreach ($this->tunggakanRows() as $baris) {
            fputcsv($file, [
                $safe($baris['nasabah']),
                $safe($baris['produk']),
                $baris['tunggakan_hari'],
                $baris['tunggakan_rupiah'],
                $safe($baris['status_alert']),
                $safe($baris['keputusan_akhir']),
                $safe($baris['ditunda_hingga'] ?? '-'),
            ]);
        }
    }

    /**
     * @param  resource  $file
     * @param  Closure(mixed): string  $safe
     */
    private function tulisCsvSerah($file, Closure $safe): void
    {
        fputcsv($file, [$safe('Laporan Serah Terima Paket')]);
        fputcsv($file, [$safe('Tanggal'), now()->toDateString()]);
        fputcsv($file, []);
        fputcsv($file, ['Nasabah', 'Produk', 'Status', 'Metode', 'Penerima', 'Tanggal Serah', 'Foto Bukti']);

        foreach ($this->serahRows() as $baris) {
            fputcsv($file, [
                $safe($baris['nasabah']),
                $safe($baris['produk']),
                $safe($baris['status']),
                $safe($baris['metode']),
                $safe($baris['penerima']),
                $safe($baris['tanggal'] ?? '-'),
                $safe($baris['foto'] ?? '-'),
            ]);
        }
    }

    /**
     * @param  resource  $file
     * @param  Closure(mixed): string  $safe
     */
    private function tulisCsvAbsensi($file, Closure $safe): void
    {
        $data = $this->absensiData(true);

        fputcsv($file, [$safe('Laporan Absensi & Izin Kolektor')]);
        fputcsv($file, [$safe('Periode'), $this->labelPeriodeCsv()]);
        fputcsv($file, []);
        fputcsv($file, ['Hadir', $data['absensiRekap']['hadir']]);
        fputcsv($file, ['Izin Disetujui', $data['absensiRekap']['izinDisetujui']]);
        fputcsv($file, ['Izin Pending', $data['absensiRekap']['izinPending']]);
        fputcsv($file, []);
        fputcsv($file, ['ABSENSI']);
        fputcsv($file, ['Tanggal', 'Kolektor', 'Jam Masuk', 'Jam Keluar']);

        foreach ($data['absensiRows'] as $baris) {
            fputcsv($file, [
                $baris['tanggal'],
                $safe($baris['kolektor']),
                $baris['jam_masuk'],
                $safe($baris['jam_keluar'] ?? '-'),
            ]);
        }

        fputcsv($file, []);
        fputcsv($file, ['IZIN']);
        fputcsv($file, ['Kolektor', 'Dari', 'Sampai', 'Alasan', 'Status', 'Diproses Oleh', 'Catatan']);

        foreach ($data['izinRows'] as $baris) {
            fputcsv($file, [
                $safe($baris['kolektor']),
                $baris['dari'],
                $baris['sampai'],
                $safe($baris['alasan']),
                $baris['status'],
                $safe($baris['pemroses']),
                $safe($baris['catatan'] ?? '-'),
            ]);
        }
    }

    /**
     * @param  resource  $file
     * @param  Closure(mixed): string  $safe
     */
    private function tulisCsvNasabah($file, Closure $safe): void
    {
        $data = $this->nasabahData();

        fputcsv($file, [$safe('Laporan Nasabah')]);
        fputcsv($file, [$safe('Tanggal'), now()->toDateString()]);
        fputcsv($file, []);
        fputcsv($file, ['Aktif', $data['nasabahRekap']['aktif']]);
        fputcsv($file, ['Pending Verifikasi', $data['nasabahRekap']['pending']]);
        fputcsv($file, ['Ditolak', $data['nasabahRekap']['ditolak']]);
        fputcsv($file, []);
        fputcsv($file, ['Nama', 'No HP', 'Status Pendaftaran', 'Mode Akses', 'Kolektor Penanggung Jawab']);

        foreach ($data['nasabahRows'] as $baris) {
            fputcsv($file, [
                $safe($baris['nama']),
                $safe($baris['no_hp']),
                $baris['status_pendaftaran'],
                $baris['mode_akses'],
                $safe($baris['kolektor']),
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
