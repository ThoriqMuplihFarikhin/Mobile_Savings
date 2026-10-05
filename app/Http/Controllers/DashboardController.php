<?php

namespace App\Http\Controllers;

use App\Livewire\Admin\KasKolektor;
use App\Models\AbsensiKolektor;
use App\Models\KepesertaanPaket;
use App\Models\LogNotifikasi;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            'admin' => $this->adminDashboard($user),
            'kolektor' => $this->kolektorDashboard($user),
            'nasabah' => $this->nasabahDashboard($user),
            default => view('dashboard'),
        };
    }

    private function adminDashboard($user)
    {
        $totalNasabah = DB::table('users')->where('role', 'nasabah')->count();
        $totalKolektor = DB::table('users')->where('role', 'kolektor')->count();
        $totalSaldo = DB::table('saldo_produk')->sum('saldo');
        $setoranHariIni = DB::table('transaksi_setoran')
            ->where('tanggal_transaksi', today())
            ->whereIn('status', ['tercatat', 'dikoreksi'])
            ->sum('nominal');

        $totalKomisi = DB::table('transaksi_penarikan')
            ->whereIn('status', ['approved', 'selesai'])
            ->sum('nominal_komisi');

        $perluReview = KepesertaanPaket::whereNull('keputusan_akhir')
            ->where('status_alert', 'perlu_review')
            ->where('status_serah_terima', 'belum')
            ->count();

        $ringkasKasKolektor = KasKolektor::ringkasUntukDashboard();
        $totalKasKolektor = $ringkasKasKolektor['total_kas'];
        $kolektorLewatBatas = $ringkasKasKolektor['lewat_batas'];

        $trenSetoran = $this->trenSetoranHarian();

        $komposisiProduk = DB::table('saldo_produk')
            ->join('produk_tabungan', 'saldo_produk.produk_id', '=', 'produk_tabungan.id')
            ->select('produk_tabungan.tipe', DB::raw('SUM(saldo_produk.saldo) as total'))
            ->groupBy('produk_tabungan.tipe')
            ->get();

        $transaksiTerbaru = DB::table('transaksi_setoran')
            ->join('users', 'transaksi_setoran.nasabah_id', '=', 'users.id')
            ->leftJoin('produk_tabungan', 'transaksi_setoran.produk_id', '=', 'produk_tabungan.id')
            ->select(
                'transaksi_setoran.*',
                'users.name as nasabah_name',
                'produk_tabungan.nama as produk_name'
            )
            ->whereIn('transaksi_setoran.status', ['tercatat', 'dikoreksi'])
            ->orderByDesc('transaksi_setoran.tanggal_transaksi')
            ->limit(5)
            ->get();

        $kolektorTeratas = DB::table('transaksi_setoran')
            ->join('users', 'transaksi_setoran.input_by', '=', 'users.id')
            ->select('users.name', DB::raw('COUNT(*) as jumlah_setoran'), DB::raw('SUM(transaksi_setoran.nominal) as total_nominal'))
            ->whereIn('transaksi_setoran.status', ['tercatat', 'dikoreksi'])
            ->where('transaksi_setoran.tanggal_transaksi', '>=', Carbon::now()->subDays(30))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_nominal')
            ->limit(4)
            ->get();

        return view('dashboard', compact(
            'user',
            'totalNasabah',
            'totalKolektor',
            'totalSaldo',
            'setoranHariIni',
            'totalKomisi',
            'perluReview',
            'totalKasKolektor',
            'kolektorLewatBatas',
            'trenSetoran',
            'komposisiProduk',
            'transaksiTerbaru',
            'kolektorTeratas',
        ));
    }

    /**
     * Tren setoran 30 hari terakhir dalam satu query agregat; hari tanpa setoran diisi 0 di PHP.
     *
     * @return Collection<int, array{tanggal: string, nominal: float}>
     */
    private function trenSetoranHarian(): Collection
    {
        $hariIni = Carbon::today();
        $rentang = collect(range(29, 0))->map(fn (int $i) => $hariIni->copy()->subDays($i));

        $perTanggal = DB::table('transaksi_setoran')
            ->selectRaw('tanggal_transaksi, SUM(nominal) as total')
            ->whereIn('status', ['tercatat', 'dikoreksi'])
            ->whereBetween('tanggal_transaksi', [
                $hariIni->copy()->subDays(29)->toDateString(),
                $hariIni->toDateString(),
            ])
            ->groupBy('tanggal_transaksi')
            ->pluck('total', 'tanggal_transaksi');

        return $rentang->map(fn (Carbon $tanggal) => [
            'tanggal' => $tanggal->format('d M'),
            'nominal' => (float) ($perTanggal[$tanggal->toDateString()] ?? 0),
        ])->values();
    }

    private function kolektorDashboard($user)
    {
        $sudahAbsenHariIni = AbsensiKolektor::where('kolektor_id', $user->id)
            ->where('tanggal', today())
            ->whereNotNull('waktu_masuk')
            ->exists();

        $jadwalHariIni = DB::table('jadwal_kunjungan')
            ->where('kolektor_id', $user->id)
            ->where('tanggal_jadwal', today())
            ->join('users', 'jadwal_kunjungan.nasabah_id', '=', 'users.id')
            ->select(
                'jadwal_kunjungan.*',
                'users.name as nasabah_name'
            )
            ->orderBy('jadwal_kunjungan.id')
            ->get();

        $kepesertaanTerpilih = KepesertaanPaket::with('produk')
            ->whereIn('nasabah_id', $jadwalHariIni->pluck('nasabah_id')->unique()->values())
            ->whereNull('keputusan_akhir')
            ->where('status_serah_terima', 'belum')
            ->orderByDesc('tunggakan')
            ->get()
            ->groupBy('nasabah_id')
            ->map(fn ($rows) => $rows->first());

        $jadwalHariIni->each(function ($jadwal) use ($kepesertaanTerpilih) {
            $kepesertaan = $kepesertaanTerpilih->get($jadwal->nasabah_id);

            $jadwal->tunggakan = $kepesertaan?->tunggakan;
            $jadwal->status_alert = $kepesertaan?->status_alert;
            $jadwal->produk_name = $kepesertaan?->produk->nama ?? null;
        });

        $jadwalTotal = $jadwalHariIni->count();
        $jadwalSelesai = $jadwalHariIni->where('status_kunjungan', 'dikunjungi')->count();

        $stats = [
            'setoran_belum_disetor' => DB::table('transaksi_setoran')
                ->where('input_by', $user->id)
                ->where('sudah_disetor_ke_kantor', false)
                ->whereIn('status', ['tercatat', 'dikoreksi'])
                ->sum('nominal'),
            'kunjungan_selesai' => $jadwalSelesai,
            'kunjungan_total' => $jadwalTotal,
        ];

        $nasabahIds = DB::table('kolektor_nasabah')
            ->where('kolektor_id', $user->id)
            ->where('status', 'aktif')
            ->pluck('nasabah_id');

        $nasabahTunggakParah = KepesertaanPaket::whereIn('nasabah_id', $nasabahIds)
            ->whereIn('status_alert', ['peringatan', 'perlu_review'])
            ->whereNull('keputusan_akhir')
            ->count();

        $totalNasabahBinaan = DB::table('kolektor_nasabah')
            ->where('kolektor_id', $user->id)
            ->where('status', 'aktif')
            ->count();

        $penarikanMenungguDiantar = TransaksiPenarikan::where('lokasi_pengambilan', 'rumah_kolektor')
            ->where('status', 'approved')
            ->whereIn('nasabah_id', $nasabahIds)
            ->count();

        return view('dashboard', compact('user', 'stats', 'jadwalHariIni', 'sudahAbsenHariIni', 'nasabahTunggakParah', 'totalNasabahBinaan', 'penarikanMenungguDiantar'));
    }

    private function nasabahDashboard($user)
    {
        $saldoPerProduk = SaldoProduk::where('nasabah_id', $user->id)
            ->with('produk')
            ->get();
        $totalSaldo = $saldoPerProduk->sum('saldo');

        $riwayatSetoran = TransaksiSetoran::where('nasabah_id', $user->id)
            ->whereIn('status', ['tercatat', 'dikoreksi'])
            ->with('produk')
            ->latest('tanggal_transaksi')
            ->limit(5)
            ->get();

        $riwayatPenarikan = TransaksiPenarikan::where('nasabah_id', $user->id)
            ->whereIn('status', ['selesai', 'approved'])
            ->with('produk')
            ->latest('waktu_pencairan')
            ->limit(5)
            ->get();

        $riwayatGabungan = $riwayatSetoran->map(fn ($t) => [
            'type' => 'setoran',
            'nama' => $t->produk->nama ?? '-',
            'tanggal' => $t->tanggal_transaksi,
            'nominal' => $t->nominal,
        ])->concat($riwayatPenarikan->filter(fn ($t) => $t->waktu_pencairan)->map(fn ($t) => [
            'type' => 'penarikan',
            'nama' => $t->produk->nama ?? '-',
            'tanggal' => $t->waktu_pencairan,
            'nominal' => $t->nominal_diterima,
        ]))->sortByDesc('tanggal')->take(5)->values();

        $unreadNotifikasi = LogNotifikasi::where('nasabah_id', $user->id)
            ->where('is_read', false)
            ->exists();

        $kepesertaanAktif = KepesertaanPaket::where('nasabah_id', $user->id)
            ->whereNull('keputusan_akhir')
            ->where('status_serah_terima', 'belum')
            ->with('produk')
            ->first();

        $streak = $this->hitungStreak($user->id);

        return view('dashboard', compact(
            'user',
            'saldoPerProduk',
            'totalSaldo',
            'riwayatGabungan',
            'unreadNotifikasi',
            'kepesertaanAktif',
            'streak',
        ));
    }

    private function hitungStreak(int $nasabahId): int
    {
        $tanggalSetor = TransaksiSetoran::where('nasabah_id', $nasabahId)
            ->whereIn('status', ['tercatat', 'dikoreksi'])
            ->orderByDesc('tanggal_transaksi')
            ->pluck('tanggal_transaksi')
            ->map(fn ($t) => Carbon::parse($t)->toDateString())
            ->unique()
            ->values();

        $hariIni = now()->toDateString();
        $streak = 0;
        $cursor = $hariIni;

        foreach ($tanggalSetor as $tanggal) {
            if ($tanggal > $hariIni) {
                continue;
            }

            if ($tanggal === $cursor || $tanggal === Carbon::parse($cursor)->subDay()->toDateString()) {
                $streak++;
                $cursor = $tanggal;
            } else {
                break;
            }
        }

        return $streak;
    }
}
