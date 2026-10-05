<?php

namespace App\Livewire\Nasabah;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\KepesertaanPaket;
use Carbon\Carbon;
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
     * Pilih cara nasabah menerima paket; hanya boleh setelah tanggal boleh cair
     * dan tunggakan lunas, sebelum paket diserahkan.
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

        if ($kepesertaan->status_serah_terima === 'sudah_diterima') {
            session()->flash('error', 'Paket ini sudah diserahkan dan tidak dapat diubah.');

            return;
        }

        $tanggalBolehCair = $kepesertaan->produk?->tanggal_boleh_cair;

        if ($tanggalBolehCair === null || Carbon::parse($tanggalBolehCair)->startOfDay()->isFuture()) {
            session()->flash('error', 'Paket ini belum boleh cair pada tanggal ini.');

            return;
        }

        if ($kepesertaan->hitungUlangKepesertaan(false)['tunggakan_hari'] > 0) {
            session()->flash('error', 'Lunasi tunggakan sebelum memilih metode pengambilan.');

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

        foreach ($kepesertaan as $item) {
            $item->hitungUlangKepesertaan();
        }

        return view('livewire.nasabah.progres-paket', compact('kepesertaan'));
    }
}
