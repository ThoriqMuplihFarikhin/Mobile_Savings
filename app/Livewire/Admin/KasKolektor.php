<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\AdminSetting;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiSetoran;
use App\Models\User;
use App\Support\KasKolektorHitung;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class KasKolektor extends Component
{
    use AuthorizesRole;

    /** @var array<int, array{kolektor_id: int, nama: string, total_belum_disetor: float, penarikan_tunai: float, kas_di_tangan: float, jumlah_transaksi: int, umur_terlama_hari: int, pengajuan_pending: int, selisih_kumulatif: float, lewat_batas: bool, terkunci: bool}> */
    public array $daftarKas = [];

    public float $totalKas = 0.0;

    public int $jumlahLewatBatas = 0;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public function mount(): void
    {
        $this->refreshData();
    }

    public function refreshData(): void
    {
        $this->daftarKas = static::kumpulkanKas();
        $this->totalKas = (float) array_sum(array_column($this->daftarKas, 'kas_di_tangan'));
        $this->jumlahLewatBatas = count(array_filter($this->daftarKas, fn (array $baris): bool => $baris['lewat_batas']));
    }

    /**
     * Termasuk kolektor terkunci yang masih memiliki berkas kas (D12):
     * kas yang belum disetor wajib tetap terlihat agar tidak "hilang"
     * dari pengawasan, diurutkan paling atas dengan badge terkunci.
     *
     * @return array<int, array{kolektor_id: int, nama: string, total_belum_disetor: float, penarikan_tunai: float, kas_di_tangan: float, jumlah_transaksi: int, umur_terlama_hari: int, pengajuan_pending: int, selisih_kumulatif: float, lewat_batas: bool, terkunci: bool}>
     */
    public static function kumpulkanKas(): array
    {
        $batas = self::batas();
        $teragregasi = TransaksiSetoran::teragregasiPerKolektor();
        $tunaiKeluar = KasKolektorHitung::tunaiKeluarPerKolektor();
        $ringkas = SetoranKolektorKantor::ringkasKasPerKolektor();

        $pending = [];
        $rowsPending = SetoranKolektorKantor::where('status', 'pending')
            ->groupBy('kolektor_id')
            ->selectRaw('kolektor_id, COUNT(*) as jumlah_pending')
            ->get();
        foreach ($rowsPending as $row) {
            $pending[(int) $row->getAttribute('kolektor_id')] = (int) $row->getAttribute('jumlah_pending');
        }

        $barisTerkunci = [];
        $barisAktif = [];
        $kolektorSemua = User::where('role', 'kolektor')
            ->whereIn('status_akun', ['aktif', 'terkunci'])
            ->orderBy('name')
            ->get();

        foreach ($kolektorSemua as $kolektor) {
            $kolektorId = (int) $kolektor->id;
            $agregat = $teragregasi[$kolektorId] ?? ['total' => 0.0, 'jumlah' => 0, 'terlama' => null];
            $umur = $agregat['terlama'] !== null
                ? (int) Carbon::parse($agregat['terlama'])->diffInDays(today(), true)
                : 0;
            $pengajuanPending = $pending[$kolektorId] ?? 0;
            $terkunci = $kolektor->status_akun === 'terkunci';
            $penarikanTunai = $tunaiKeluar[$kolektorId] ?? 0.0;
            $kasDiTangan = $agregat['total'] - $penarikanTunai;

            if ($terkunci && $agregat['total'] <= 0 && $pengajuanPending <= 0) {
                continue;
            }

            $baris = [
                'kolektor_id' => $kolektorId,
                'nama' => (string) $kolektor->name,
                'total_belum_disetor' => $agregat['total'],
                'penarikan_tunai' => $penarikanTunai,
                'kas_di_tangan' => $kasDiTangan,
                'jumlah_transaksi' => $agregat['jumlah'],
                'umur_terlama_hari' => $umur,
                'pengajuan_pending' => $pengajuanPending,
                'selisih_kumulatif' => $ringkas[$kolektorId]['selisih_kumulatif'] ?? 0.0,
                'lewat_batas' => self::lewatBatas($kasDiTangan, $umur, $batas['kas'], $batas['hari']),
                'terkunci' => $terkunci,
            ];

            if ($terkunci) {
                $barisTerkunci[] = $baris;
            } else {
                $barisAktif[] = $baris;
            }
        }

        return array_merge($barisTerkunci, $barisAktif);
    }

    /**
     * Ringkasan kartu dashboard admin dari sumber data yang sama.
     *
     * @return array{total_kas: float, lewat_batas: int}
     */
    public static function ringkasUntukDashboard(): array
    {
        $total = 0.0;
        $lewat = 0;
        foreach (static::kumpulkanKas() as $baris) {
            $total += $baris['kas_di_tangan'];
            if ($baris['lewat_batas']) {
                $lewat++;
            }
        }

        return ['total_kas' => $total, 'lewat_batas' => $lewat];
    }

    /**
     * @return array{kas: float, hari: int}
     */
    private static function batas(): array
    {
        $kas = (string) AdminSetting::get('batas_kas_kolektor', '0');
        $hari = (string) AdminSetting::get('batas_hari_kas', '0');

        return [
            'kas' => is_numeric($kas) ? (float) $kas : 0.0,
            'hari' => is_numeric($hari) ? (int) $hari : 0,
        ];
    }

    private static function lewatBatas(float $total, int $umur, float $batasKas, int $batasHari): bool
    {
        return ($batasKas > 0 && $total > $batasKas)
            || ($batasHari > 0 && $umur > $batasHari);
    }

    public function render(): View
    {
        return view('livewire.admin.kas-kolektor');
    }
}
