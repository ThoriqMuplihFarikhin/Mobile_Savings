<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tinjauan penarikan offline pasca-fakta (D25): setiap penarikan yang
 * diselesaikan langsung oleh kolektor wajib ditandai admin sebagai
 * `sesuai` atau `bermasalah`; penandaan ganda tidak boleh menimpa.
 */
#[Layout('layouts.app')]
class TinjauanPenarikanOffline extends Component
{
    use AuthorizesRole;
    use WithPagination;

    public string $statusFilter = 'belum';

    public string $kolektorFilter = '';

    public string $dariTanggal = '';

    public string $sampaiTanggal = '';

    public ?int $terpilihId = null;

    public string $catatanTinjauan = '';

    public bool $bekukanKolektor = true;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    /** @var array<string, string> */
    protected $listeners = ['tinjauanUpdated' => '$refresh'];

    public function updated(string $nama): void
    {
        if (in_array($nama, ['statusFilter', 'kolektorFilter', 'dariTanggal', 'sampaiTanggal'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $penarikans = TransaksiPenarikan::query()
            ->with(['nasabah', 'produk', 'dibayarOleh', 'ditinjauOleh'])
            ->where('metode_verifikasi', 'tanpa_verifikasi_offline')
            ->when($this->statusFilter === 'belum', fn ($q) => $q->whereNull('hasil_tinjauan'))
            ->when($this->statusFilter === 'sesuai', fn ($q) => $q->where('hasil_tinjauan', 'sesuai'))
            ->when($this->statusFilter === 'bermasalah', fn ($q) => $q->where('hasil_tinjauan', 'bermasalah'))
            ->when($this->kolektorFilter !== '', fn ($q) => $q->where('dibayar_oleh', $this->kolektorFilter))
            ->when($this->dariTanggal !== '', fn ($q) => $q->whereDate('waktu_pencairan', '>=', $this->dariTanggal))
            ->when($this->sampaiTanggal !== '', fn ($q) => $q->whereDate('waktu_pencairan', '<=', $this->sampaiTanggal))
            ->orderByDesc('waktu_pencairan')
            ->orderByDesc('id')
            ->paginate(15);

        $kolektors = User::where('role', 'kolektor')->orderBy('name')->get(['id', 'name']);

        return view('livewire.admin.tinjauan-penarikan-offline', compact('penarikans', 'kolektors'));
    }

    public function pilihTinjauan(int $id): void
    {
        $this->terpilihId = $id;
        $this->catatanTinjauan = '';
        $this->bekukanKolektor = true;
    }

    public function batalTinjauan(): void
    {
        $this->reset(['terpilihId', 'catatanTinjauan']);
        $this->bekukanKolektor = true;
    }

    public function sesuai(int $id): void
    {
        $this->catatanTinjauan = '';
        $this->prosesTinjauan($id, 'sesuai');
    }

    public function bermasalah(int $id): void
    {
        $this->validate([
            'catatanTinjauan' => ['required', 'string', 'min:10', 'max:255'],
        ]);

        $this->prosesTinjauan($id, 'bermasalah');
    }

    /**
     * Simpan hasil tinjauan dalam transaksi berlock; peninjauan kedua selalu
     * ditolak karena baris wajib masih berstatus `hasil_tinjauan IS NULL`.
     */
    private function prosesTinjauan(int $id, string $hasil): void
    {
        $admin = Auth::user();

        if (! $admin instanceof User) {
            session()->flash('error', 'Sesi berakhir karena tidak aktif. Silakan masuk kembali.');

            return;
        }

        $bekukan = $hasil === 'bermasalah' && $this->bekukanKolektor;

        try {
            $penarikan = DB::transaction(function () use ($id, $hasil, $admin) {
                $p = TransaksiPenarikan::whereKey($id)->lockForUpdate()->first();

                if (! $p instanceof TransaksiPenarikan) {
                    throw new DomainException('Penarikan tidak ditemukan.');
                }

                if ($p->metode_verifikasi !== 'tanpa_verifikasi_offline') {
                    throw new DomainException('Hanya penarikan offline langsung yang bisa ditinjau.');
                }

                if ($p->hasil_tinjauan !== null) {
                    throw new DomainException('Penarikan ini sudah pernah ditinjau admin lain.');
                }

                $p->update([
                    'hasil_tinjauan' => $hasil,
                    'catatan_tinjauan' => $this->catatanTinjauan !== '' ? $this->catatanTinjauan : null,
                    'ditinjau_oleh' => $admin->id,
                    'ditinjau_pada' => now(),
                ]);

                return $p;
            });
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Gagal menyimpan tinjauan. Silakan coba lagi.');

            return;
        }

        ActivityLogger::log('tinjau_penarikan_offline', 'transaksi_penarikan', $penarikan->id, [
            'hasil' => $hasil,
            'kolektor_id' => $penarikan->dibayar_oleh,
            'nominal' => $penarikan->nominal_diminta,
            'catatan' => $this->catatanTinjauan,
            'bekukan' => $bekukan,
        ]);

        if ($bekukan && $penarikan->dibayar_oleh) {
            $this->bekukanPenarikanLangsung((int) $penarikan->dibayar_oleh, $penarikan);
        }

        $this->reset(['terpilihId', 'catatanTinjauan']);
        $this->bekukanKolektor = true;
        $this->dispatch('tinjauanUpdated');

        session()->flash('success', $hasil === 'sesuai'
            ? 'Penarikan ditandai sesuai.'
            : 'Penarikan ditandai bermasalah.');
    }

    /**
     * Bekukan penarikan offline langsung kolektor (default aktif, D25).
     */
    private function bekukanPenarikanLangsung(int $kolektorId, TransaksiPenarikan $penarikan): void
    {
        User::whereKey($kolektorId)->update(['izin_penarikan_langsung' => false]);

        ActivityLogger::log('bekukan_penarikan_langsung', 'users', $kolektorId, [
            'penarikan_id' => $penarikan->id,
            'admin_id' => Auth::id(),
        ]);

        try {
            ActivityLogger::notify(
                $kolektorId,
                'Penarikan Offline Bermasalah',
                'Penarikan Rp '.number_format((float) $penarikan->nominal_diminta, 0, ',', '.')
                    .' ditandai bermasalah. Penarikan offline langsung Anda dibekukan sampai admin membukanya kembali.',
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
