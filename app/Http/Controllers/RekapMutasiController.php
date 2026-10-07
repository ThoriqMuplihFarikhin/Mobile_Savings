<?php

namespace App\Http\Controllers;

use App\Models\KolektorNasabah;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RekapMutasiController extends Controller
{
    /**
     * Rekap mutasi siap cetak per nasabah (D14, §5.3.8) untuk ditempel
     * di buku tabungan fisik — hanya admin dan kolektor penanggung jawab.
     */
    public function show(User $user): View
    {
        $nasabah = $user;

        abort_unless($nasabah->role === 'nasabah', 404);

        $pemohon = Auth::user();

        abort_unless($pemohon !== null, 403);

        $berwenang = $pemohon->isAdmin()
            || KolektorNasabah::where('kolektor_id', $pemohon->id)
                ->where('nasabah_id', $nasabah->id)
                ->where('status', 'aktif')
                ->exists();

        abort_unless($berwenang, 403);

        $setoran = TransaksiSetoran::with('produk')
            ->where('nasabah_id', $nasabah->id)
            ->where('status', '!=', 'dibatalkan')
            ->orderBy('tanggal_transaksi')
            ->orderBy('id')
            ->get();

        $penarikan = TransaksiPenarikan::with('produk')
            ->where('nasabah_id', $nasabah->id)
            ->whereIn('status', ['approved', 'selesai'])
            ->orderBy('waktu_approval')
            ->orderBy('id')
            ->get();

        $saldoSekarang = (float) SaldoProduk::where('nasabah_id', $nasabah->id)->sum('saldo');
        $totalSetoran = (float) $setoran->sum('nominal');
        $totalPenarikan = (float) $penarikan->sum('nominal_diminta');
        $saldoAwal = bcsub(
            bcadd($this->uang($saldoSekarang), $this->uang($totalPenarikan), 2),
            $this->uang($totalSetoran),
            2
        );

        $kronologi = $setoran->map(fn ($s): array => [
            'tanggal' => (string) $s->tanggal_transaksi,
            'tipe' => 'Setoran',
            'produk' => $s->produk->nama ?? '-',
            'nominal' => (float) $s->nominal,
            'arah' => 1,
        ])->concat($penarikan->map(fn ($p): array => [
            'tanggal' => Carbon::parse($p->waktu_approval)->format('Y-m-d'),
            'tipe' => 'Penarikan',
            'produk' => $p->produk->nama ?? '-',
            'nominal' => (float) $p->nominal_diminta,
            'arah' => -1,
        ]))->sortBy('tanggal')->values();

        $saldoBerjalan = (float) $saldoAwal;

        $kronologi = $kronologi->map(function (array $baris) use (&$saldoBerjalan): array {
            $saldoBerjalan = $baris['arah'] > 0
                ? $saldoBerjalan + $baris['nominal']
                : $saldoBerjalan - $baris['nominal'];

            $baris['saldo'] = $saldoBerjalan;

            return $baris;
        });

        $saldoPerProduk = SaldoProduk::where('nasabah_id', $nasabah->id)
            ->with('produk')
            ->get();

        return view('rekap-mutasi', [
            'nasabah' => $nasabah,
            'kronologi' => $kronologi,
            'saldoPerProduk' => $saldoPerProduk,
            'totalSetoran' => $totalSetoran,
            'totalPenarikan' => $totalPenarikan,
            'saldoSekarang' => $saldoSekarang,
        ]);
    }

    /**
     * Normalisasi nilai float ke numeric-string untuk bcmath.
     *
     * @return numeric-string
     */
    private function uang(mixed $nilai): string
    {
        $teks = (string) $nilai;

        if (! is_numeric($teks)) {
            return '0.00';
        }

        return $teks;
    }
}
