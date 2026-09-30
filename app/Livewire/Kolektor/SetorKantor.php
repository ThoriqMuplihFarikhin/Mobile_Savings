<?php

namespace App\Livewire\Kolektor;

use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiSetoran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.mobile')]
class SetorKantor extends Component
{
    #[Locked]
    public $totalBelumDisetor = 0;

    #[Locked]
    public $jumlahTransaksi = 0;

    public $catatan = '';

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->totalBelumDisetor = TransaksiSetoran::belumDisetor()
            ->where('input_by', Auth::id())
            ->whereNull('setoran_kolektor_id')
            ->sum('nominal');

        $this->jumlahTransaksi = TransaksiSetoran::belumDisetor()
            ->where('input_by', Auth::id())
            ->whereNull('setoran_kolektor_id')
            ->count();
    }

    public function submit()
    {
        $kolektorId = Auth::id();

        try {
            DB::transaction(function () use ($kolektorId) {
                $adaPengajuan = SetoranKolektorKantor::where('kolektor_id', $kolektorId)
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->exists();

                if ($adaPengajuan) {
                    throw new \DomainException('Anda sudah memiliki pengajuan setoran yang masih menunggu proses admin.');
                }

                $rows = TransaksiSetoran::belumDisetor()
                    ->where('input_by', $kolektorId)
                    ->whereNull('setoran_kolektor_id')
                    ->lockForUpdate()
                    ->get(['id', 'nominal']);

                if ($rows->isEmpty()) {
                    throw new \DomainException('Tidak ada setoran yang perlu disetor ke kantor.');
                }

                $setoran = SetoranKolektorKantor::create([
                    'kolektor_id' => $kolektorId,
                    'tanggal_setor' => now()->toDateString(),
                    'total_seharusnya' => $rows->sum('nominal'),
                    'status' => 'pending',
                    'keterangan_selisih' => $this->catatan ?: null,
                ]);

                TransaksiSetoran::whereIn('id', $rows->pluck('id'))
                    ->update(['setoran_kolektor_id' => $setoran->id]);
            });
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Gagal mengajukan setoran ke kantor. Silakan coba lagi.');

            return;
        }

        $this->catatan = '';
        $this->loadData();
        session()->flash('success', 'Pengajuan setoran ke kantor berhasil diajukan!');
    }

    public function render()
    {
        $riwayat = SetoranKolektorKantor::where('kolektor_id', Auth::id())
            ->latest()
            ->paginate(10);

        $menungguVerifikasi = SetoranKolektorKantor::where('kolektor_id', Auth::id())
            ->where('status', 'pending')
            ->exists();

        return view('livewire.kolektor.setor-kantor', compact('riwayat', 'menungguVerifikasi'));
    }
}
