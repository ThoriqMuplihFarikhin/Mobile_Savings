<?php

namespace App\Livewire\Nasabah;

use App\Actions\Paket\HitungProgresBarangAction;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\KepesertaanPaket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class ProgresPaket extends Component
{
    use AuthorizesRole;

    protected function requiredRole(): string
    {
        return 'nasabah';
    }

    /**
     * Pilih cara nasabah menerima paket; hanya boleh setelah lulus gerbang
     * pencairan tunggal (D17), sebelum paket diserahkan.
     */
    public function pilihMetodePengambilan(int $kepesertaanId, string $metode): void
    {
        if (! in_array($metode, ['ambil_sendiri', 'diantar_kolektor'], true)) {
            session()->flash('error', 'Metode pengambilan tidak dikenal.');

            return;
        }

        $kepesertaan = KepesertaanPaket::with('produk')
            ->whereKey($kepesertaanId)
            ->where('nasabah_id', Auth::id())
            ->first();

        if (! $kepesertaan instanceof KepesertaanPaket) {
            session()->flash('error', 'Kepesertaan tidak ditemukan.');

            return;
        }

        $status = $kepesertaan->statusPencairan();

        if (! $status['boleh']) {
            session()->flash('error', $status['alasan']);

            return;
        }

        $kepesertaan->update(['metode_pengambilan' => $metode]);

        session()->flash('success', 'Metode pengambilan berhasil dipilih.');
    }

    public function render()
    {
        $kepesertaan = KepesertaanPaket::with('produk')
            ->where('nasabah_id', Auth::id())
            ->latest('tanggal_mulai_ikut')
            ->get();

        // Dihitung di render(), bukan properti publik: data progres tidak
        // boleh tersimpan di payload Livewire (anti bocor harga, D15).
        $pencairan = [];
        foreach ($kepesertaan as $item) {
            $pencairan[(int) $item->id] = $item->statusPencairan();
        }

        $aksi = new HitungProgresBarangAction;
        $progres = $kepesertaan
            ->map(fn (KepesertaanPaket $item): array => array_merge(
                ['kepesertaan_id' => (int) $item->id],
                $aksi->untukNasabah($item),
            ))
            ->values();

        return view('livewire.nasabah.progres-paket', compact('kepesertaan', 'progres', 'pencairan'));
    }
}
