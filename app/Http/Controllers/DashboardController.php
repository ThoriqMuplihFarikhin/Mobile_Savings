<?php

namespace App\Http\Controllers;

use App\Models\KepesertaanPaket;
use App\Models\LogNotifikasi;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use Illuminate\Http\Request;
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
        $stats = [
            'total_nasabah' => DB::table('users')->where('role', 'nasabah')->count(),
            'total_kolektor' => DB::table('users')->where('role', 'kolektor')->count(),
            'nasabah_pending' => DB::table('nasabah_profil')->where('status_pendaftaran', 'pending_verifikasi')->count(),
            'penarikan_pending' => DB::table('transaksi_penarikan')->where('status', 'pending')->count(),
            'total_setoran_hari' => DB::table('transaksi_setoran')
                ->where('tanggal_transaksi', today())
                ->where('status', 'tercatat')
                ->sum('nominal'),
            'total_saldo_semua' => DB::table('saldo_produk')->sum('saldo'),
        ];

        return view('dashboard', compact('user', 'stats'));
    }

    private function kolektorDashboard($user)
    {
        $stats = [
            'nasabah_ditangani' => DB::table('kolektor_nasabah')
                ->where('kolektor_id', $user->id)
                ->where('status', 'aktif')
                ->count(),
            'setoran_hari' => DB::table('transaksi_setoran')
                ->where('input_by', $user->id)
                ->where('tanggal_transaksi', today())
                ->where('status', 'tercatat')
                ->sum('nominal'),
            'setoran_belum_disetor' => DB::table('transaksi_setoran')
                ->where('input_by', $user->id)
                ->where('sudah_disetor_ke_kantor', false)
                ->where('status', 'tercatat')
                ->sum('nominal'),
            'jadwal_hari' => DB::table('jadwal_kunjungan')
                ->where('kolektor_id', $user->id)
                ->where('tanggal_jadwal', today())
                ->count(),
        ];

        $jadwalHariIni = DB::table('jadwal_kunjungan')
            ->where('kolektor_id', $user->id)
            ->where('tanggal_jadwal', today())
            ->join('users', 'jadwal_kunjungan.nasabah_id', '=', 'users.id')
            ->leftJoin('kepesertaan_paket', function ($join) {
                $join->on('kepesertaan_paket.nasabah_id', '=', 'jadwal_kunjungan.nasabah_id')
                    ->where('kepesertaan_paket.status_alert', '!=', 'aman');
            })
            ->leftJoin('produk_tabungan', 'kepesertaan_paket.produk_id', '=', 'produk_tabungan.id')
            ->select(
                'jadwal_kunjungan.*',
                'users.name as nasabah_name',
                'kepesertaan_paket.tunggakan',
                'kepesertaan_paket.status_alert',
                'produk_tabungan.nama as produk_name'
            )
            ->get();

        $nasabahIds = DB::table('kolektor_nasabah')
            ->where('kolektor_id', $user->id)
            ->where('status', 'aktif')
            ->pluck('nasabah_id');

        $nasabahTunggak = KepesertaanPaket::whereIn('nasabah_id', $nasabahIds)
            ->whereIn('status_alert', ['peringatan', 'perlu_review'])
            ->count();

        return view('dashboard', compact('user', 'stats', 'jadwalHariIni', 'nasabahTunggak'));
    }

    private function nasabahDashboard($user)
    {
        $saldo = SaldoProduk::where('nasabah_id', $user->id)->get();
        $totalSaldo = $saldo->sum('saldo');

        $riwayatSetoran = TransaksiSetoran::where('nasabah_id', $user->id)
            ->where('status', 'tercatat')
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
            'nama' => $t->produk->nama,
            'tanggal' => $t->tanggal_transaksi,
            'nominal' => $t->nominal,
        ])->concat($riwayatPenarikan->map(fn ($t) => [
            'type' => 'penarikan',
            'nama' => $t->produk->nama,
            'tanggal' => $t->waktu_pencairan,
            'nominal' => $t->nominal_diterima,
        ]))->sortByDesc('tanggal')->take(5)->values();

        $unreadNotifikasi = LogNotifikasi::where('nasabah_id', $user->id)
            ->where('is_read', false)
            ->count();

        $kepesertaanPaket = KepesertaanPaket::where('nasabah_id', $user->id)
            ->whereNull('keputusan_akhir')
            ->with('produk')
            ->first();

        return view('dashboard', compact('user', 'saldo', 'totalSaldo', 'riwayatGabungan', 'unreadNotifikasi', 'kepesertaanPaket'));
    }
}
