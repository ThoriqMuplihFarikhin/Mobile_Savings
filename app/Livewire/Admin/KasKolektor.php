<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\AdminSetting;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class KasKolektor extends Component
{
    use AuthorizesRole;

    /** @var array<int, array{kolektor_id: int, nama: string, total_belum_disetor: float, jumlah_transaksi: int, umur_terlama_hari: int, pengajuan_pending: int, selisih_kumulatif: float, lewat_batas: bool}> */
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
        $this->totalKas = (float) array_sum(array_column($this->daftarKas, 'total_belum_disetor'));
        $this->jumlahLewatBatas = count(array_filter($this->daftarKas, fn (array $baris): bool => $baris['lewat_batas']));
    }

    /**
     * @return array<int, array{kolektor_id: int, nama: string, total_belum_disetor: float, jumlah_transaksi: int, umur_terlama_hari: int, pengajuan_pending: int, selisih_kumulatif: float, lewat_batas: bool}>
     */
    public static function kumpulkanKas(): array
    {
        $batas = self::batas();
        $teragregasi = TransaksiSetoran::teragregasiPerKolektor();
        $ringkas = SetoranKolektorKantor::ringkasKasPerKolektor();

        $pending = [];
        $rowsPending = SetoranKolektorKantor::where('status', 'pending')
            ->groupBy('kolektor_id')
            ->selectRaw('kolektor_id, COUNT(*) as jumlah_pending')
            ->get();
        foreach ($rowsPending as $row) {
            $pending[(int) $row->getAttribute('kolektor_id')] = (int) $row->getAttribute('jumlah_pending');
        }

        $rows = [];
        $kolektorAktif = User::where('role', 'kolektor')
            ->where('status_akun', 'aktif')
            ->orderBy('name')
            ->get();

        foreach ($kolektorAktif as $kolektor) {
            $kolektorId = (int) $kolektor->id;
            $agregat = $teragregasi[$kolektorId] ?? ['total' => 0.0, 'jumlah' => 0, 'terlama' => null];
            $umur = $agregat['terlama'] !== null
                ? (int) Carbon::parse($agregat['terlama'])->diffInDays(today(), true)
                : 0;

            $rows[] = [
                'kolektor_id' => $kolektorId,
                'nama' => (string) $kolektor->name,
                'total_belum_disetor' => $agregat['total'],
                'jumlah_transaksi' => $agregat['jumlah'],
                'umur_terlama_hari' => $umur,
                'pengajuan_pending' => $pending[$kolektorId] ?? 0,
                'selisih_kumulatif' => $ringkas[$kolektorId]['selisih_kumulatif'] ?? 0.0,
                'lewat_batas' => self::lewatBatas($agregat['total'], $umur, $batas['kas'], $batas['hari']),
            ];
        }

        return $rows;
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
            $total += $baris['total_belum_disetor'];
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
