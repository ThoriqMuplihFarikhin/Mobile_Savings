<?php

namespace App\Livewire\Nasabah;

use App\Actions\Paket\IkutiPaketAction;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class PilihPaket extends Component
{
    use AuthorizesRole;

    public ?int $produkTerpilih = null;

    public bool $tampilKomitmen = false;

    public bool $setuju = false;

    public string $pin = '';

    public ?string $pesanError = null;

    protected function requiredRole(): string
    {
        return 'nasabah';
    }

    public function bukaKomitmen(int $produkId): void
    {
        $this->pesanError = null;

        // Guard ketersediaan penuh (periode, batas, kepesertaan) dijalankan
        // IkutiPaketAction saat konfirmasi, bukan saat modal dibuka.
        $produk = ProdukTabungan::find($produkId);
        if (! $produk instanceof ProdukTabungan || ! $produk->isPaket()) {
            $this->pesanError = 'Paket tidak ditemukan.';

            return;
        }

        $this->produkTerpilih = $produkId;
        $this->tampilKomitmen = true;
        $this->setuju = false;
        $this->pin = '';
    }

    public function tutupKomitmen(): void
    {
        $this->tampilKomitmen = false;
        $this->setuju = false;
        $this->pin = '';
        $this->produkTerpilih = null;
        $this->pesanError = null;
    }

    public function ikutiPaket(): void
    {
        $this->validate([
            'setuju' => 'accepted',
            'pin' => 'required|string',
        ]);

        $nasabah = Auth::user();
        if (! $nasabah instanceof User) {
            abort(403);
        }

        $produk = ProdukTabungan::find($this->produkTerpilih);
        if (! $produk instanceof ProdukTabungan) {
            $this->pesanError = 'Paket tidak ditemukan.';

            return;
        }

        try {
            app(IkutiPaketAction::class)->execute($nasabah, $produk, $this->pin);
        } catch (DomainException $e) {
            $this->pesanError = $e->getMessage();

            return;
        }

        session()->flash('success', 'Anda berhasil mengikuti paket '.$produk->nama.'.');
        $this->tutupKomitmen();
    }

    public function render(): View
    {
        return view('livewire.nasabah.pilih-paket', [
            'paketTersedia' => $this->paketTersedia(),
            'teksKomitmen' => IkutiPaketAction::teksKomitmen(),
        ]);
    }

    /**
     * Paket yang masih boleh diikuti nasabah: aktif, belum lewat periode
     * maupun batas daftar, dan belum diikuti pada kepesertaan berjalan.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function paketTersedia(): Collection
    {
        $hariIni = now()->toDateString();

        $sudahDiikuti = KepesertaanPaket::where('nasabah_id', Auth::id())
            ->whereNull('keputusan_akhir')
            ->pluck('produk_id');

        return ProdukTabungan::where('tipe', 'paket')
            ->where('status', 'aktif')
            ->where(fn ($q) => $q->whereNull('periode_selesai')->orWhere('periode_selesai', '>=', $hariIni))
            ->where(fn ($q) => $q->whereNull('batas_daftar_hingga')->orWhere('batas_daftar_hingga', '>=', $hariIni))
            ->whereNotIn('id', $sudahDiikuti)
            ->orderBy('nama')
            ->get()
            ->map(fn (ProdukTabungan $p): array => $this->formatPaket($p))
            ->values();
    }

    /**
     * Data satu paket untuk ditampilkan; D15: harga per item tidak ikut.
     *
     * @return array<string, mixed>
     */
    private function formatPaket(ProdukTabungan $p): array
    {
        return [
            'id' => (int) $p->id,
            'nama' => $p->nama,
            'harga_per_hari' => (float) $p->harga_per_hari,
            'periode_mulai' => $p->periode_mulai ? Carbon::parse($p->periode_mulai)->format('d/m/Y') : null,
            'periode_selesai' => $p->periode_selesai ? Carbon::parse($p->periode_selesai)->format('d/m/Y') : null,
            'tanggal_boleh_cair' => $p->tanggal_boleh_cair ? Carbon::parse($p->tanggal_boleh_cair)->format('d/m/Y') : null,
            'batas_daftar_hingga' => $p->batas_daftar_hingga ? Carbon::parse($p->batas_daftar_hingga)->format('d/m/Y') : null,
            'toleransi_hari' => $p->batas_toleransi_tunggakan_hari,
            'target' => $p->targetAkhir(),
            'isi' => $p->isiPaketPublik() ?? [],
        ];
    }
}
