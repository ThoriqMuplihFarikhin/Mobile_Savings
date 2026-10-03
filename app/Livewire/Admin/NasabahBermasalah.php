<?php

namespace App\Livewire\Admin;

use App\Actions\Paket\ProsesKegagalanPaketAction;
use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class NasabahBermasalah extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public $search = '';

    /** @var KepesertaanPaket|null */
    public $selectedKepesertaan = null;

    public $showDetail = false;

    public $keputusan_akhir = '';

    public $catatan_admin = '';

    public $metode_pengambilan = '';

    public string $ditunda_hingga = '';

    public string $produkTujuanId = '';

    public function render()
    {
        $query = KepesertaanPaket::with(['nasabah', 'produk'])
            ->where('status_alert', 'perlu_review');

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('nasabah', fn ($u) => $u->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('produk', fn ($p) => $p->where('nama', 'like', "%{$this->search}%"));
            });
        }

        $kepesertaan = $query->latest()->paginate(10);

        $produkTujuan = ProdukTabungan::where('status', 'aktif')
            ->where('id', '!=', $this->selectedKepesertaan->produk_id ?? 0)
            ->orderBy('nama')
            ->get();

        return view('livewire.admin.nasabah-bermasalah', compact('kepesertaan', 'produkTujuan'));
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function selectKepesertaan(int $id)
    {
        $kepesertaan = KepesertaanPaket::with(['nasabah', 'produk'])->find($id);

        if (! $kepesertaan) {
            session()->flash('error', 'Kepesertaan tidak ditemukan.');

            return;
        }

        $this->selectedKepesertaan = $kepesertaan;
        $kepesertaan->hitungUlangKepesertaan();
        $kepesertaan->refresh();
        $this->keputusan_akhir = $kepesertaan->keputusan_akhir ?? '';
        $this->catatan_admin = $kepesertaan->catatan_admin ?? '';
        $this->metode_pengambilan = $kepesertaan->metode_pengambilan ?? '';
        $this->ditunda_hingga = $kepesertaan->ditunda_hingga
            ? Carbon::parse($kepesertaan->ditunda_hingga)->toDateString()
            : '';
        $this->produkTujuanId = '';
        $this->showDetail = true;
    }

    public function updateKeputusan()
    {
        $this->validate([
            'keputusan_akhir' => 'required|in:lanjut,gagal_dikembalikan,gagal_dialihkan',
            'catatan_admin' => 'nullable|string|max:1000',
            'metode_pengambilan' => 'required_if:keputusan_akhir,gagal_dikembalikan|nullable|in:ambil_sendiri,diantar_kolektor',
            'ditunda_hingga' => 'required_if:keputusan_akhir,lanjut|nullable|date|after_or_equal:today|before_or_equal:+90 days',
            'produkTujuanId' => 'required_if:keputusan_akhir,gagal_dialihkan|nullable|integer|exists:produk_tabungan,id',
        ]);

        if (! $this->selectedKepesertaan) {
            session()->flash('error', 'Tidak ada kepesertaan yang dipilih.');

            return;
        }

        try {
            match ($this->keputusan_akhir) {
                'lanjut' => $this->lanjutkanKepesertaan(),
                'gagal_dikembalikan' => app(ProsesKegagalanPaketAction::class)->dikembalikan(
                    (int) $this->selectedKepesertaan->id,
                    (string) $this->metode_pengambilan,
                    (string) ($this->catatan_admin ?? ''),
                ),
                'gagal_dialihkan' => app(ProsesKegagalanPaketAction::class)->dialihkan(
                    (int) $this->selectedKepesertaan->id,
                    (int) $this->produkTujuanId,
                    (string) ($this->catatan_admin ?? ''),
                ),
            };
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Gagal menyimpan keputusan. Silakan coba lagi.');

            return;
        }

        $this->showDetail = false;
        $this->selectedKepesertaan = null;

        session()->flash('success', 'Keputusan berhasil disimpan!');
    }

    /**
     * Lanjut tidak mengisi keputusan_akhir: hanya menunda perhitungan lewat
     * ditunda_hingga (maksimal 90 hari) sambil mempertahankan catatan admin.
     *
     * @throws \DomainException bila kepesertaan sudah diputuskan
     */
    private function lanjutkanKepesertaan(): void
    {
        $kepesertaanId = (int) $this->selectedKepesertaan->id;

        DB::transaction(function () use ($kepesertaanId) {
            $kepesertaan = KepesertaanPaket::whereKey($kepesertaanId)->lockForUpdate()->first();

            if (! $kepesertaan) {
                throw new \DomainException('Kepesertaan tidak ditemukan.');
            }

            if ($kepesertaan->keputusan_akhir !== null) {
                throw new \DomainException('Kepesertaan sudah memiliki keputusan akhir.');
            }

            $kepesertaan->update([
                'ditunda_hingga' => $this->ditunda_hingga,
                'catatan_admin' => $this->catatan_admin,
            ]);

            $kepesertaan->hitungUlangKepesertaan();
        });

        try {
            ActivityLogger::log('keputusan_paket', 'kepesertaan_paket', $kepesertaanId, [
                'keputusan' => 'lanjut',
                'ditunda_hingga' => $this->ditunda_hingga,
                'catatan' => $this->catatan_admin,
            ]);

            ActivityLogger::notify(
                (int) $this->selectedKepesertaan->nasabah_id,
                'Keputusan Kepesertaan Paket',
                'Kepesertaan paket Anda dilanjutkan dan ditunda hingga '.Carbon::parse($this->ditunda_hingga)->translatedFormat('d M Y').'.',
                'both',
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
