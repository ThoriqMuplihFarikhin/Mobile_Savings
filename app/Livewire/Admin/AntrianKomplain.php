<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\Komplain;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AntrianKomplain extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public string $statusFilter = 'baru';

    public ?int $selectedId = null;

    public string $catatan = '';

    public bool $tampilDetail = false;

    /** @var array<string, string> */
    protected $listeners = ['komplainUpdated' => '$refresh'];

    public function getSelectedKomplainProperty(): ?Komplain
    {
        if (! $this->selectedId) {
            return null;
        }

        return Komplain::with(['nasabah', 'transaksiTerkait.produk'])
            ->find($this->selectedId);
    }

    public function render(): View
    {
        $komplains = Komplain::with('nasabah')
            ->where('status', $this->statusFilter)
            ->latest()
            ->paginate(10);

        return view('livewire.admin.antrian-komplain', compact('komplains'));
    }

    public function showDetail(int $id): void
    {
        $this->selectedId = $id;
        $this->tampilDetail = true;
        $this->catatan = '';
    }

    public function proses(int $id): void
    {
        try {
            $komplain = DB::transaction(function () use ($id) {
                $komplain = Komplain::whereKey($id)->lockForUpdate()->first();

                if (! $komplain) {
                    throw new \DomainException('Komplain tidak ditemukan.');
                }

                if ($komplain->status !== 'baru') {
                    throw new \DomainException('Hanya komplain berstatus baru yang dapat diproses.');
                }

                $komplain->update(['status' => 'diproses']);

                return $komplain;
            });
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Gagal memproses komplain. Silakan coba lagi.');

            return;
        }

        if ($komplain->nasabah_id) {
            try {
                ActivityLogger::notify(
                    $komplain->nasabah_id,
                    'Komplain Diproses',
                    'Komplain Anda sedang diproses oleh tim kami. Mohon tunggu informasi selanjutnya.',
                    'in_app'
                );
            } catch (\Exception $e) {
                \Log::error('Gagal kirim notifikasi komplain diproses', [
                    'komplain_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        session()->flash('success', 'Komplain ditandai sedang diproses!');
    }

    public function selesai(int $id): void
    {
        $this->validate(['catatan' => 'required|string|min:3']);

        try {
            $komplain = DB::transaction(function () use ($id) {
                $komplain = Komplain::whereKey($id)->lockForUpdate()->first();

                if (! $komplain) {
                    throw new \DomainException('Komplain tidak ditemukan.');
                }

                if ($komplain->status !== 'diproses') {
                    throw new \DomainException('Hanya komplain berstatus diproses yang dapat diselesaikan.');
                }

                $komplain->update([
                    'status' => 'selesai',
                    'catatan_penyelesaian' => $this->catatan,
                    'tanggal_selesai' => now(),
                    'ditangani_oleh' => auth()->id(),
                ]);

                return $komplain;
            });
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Gagal menyelesaikan komplain. Silakan coba lagi.');

            return;
        }

        ActivityLogger::log('selesai_komplain', 'komplain', $id, [
            'nasabah_id' => $komplain->nasabah_id,
        ]);

        if ($komplain->nasabah_id) {
            try {
                ActivityLogger::notify(
                    $komplain->nasabah_id,
                    'Komplain Selesai',
                    'Komplain Anda telah diselesaikan. Catatan: '.$this->catatan,
                    'both'
                );
            } catch (\Exception $e) {
                \Log::error('Gagal kirim notifikasi komplain selesai', [
                    'komplain_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->tampilDetail = false;
        $this->selectedId = null;
        $this->catatan = '';
        session()->flash('success', 'Komplain berhasil diselesaikan!');
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }
}
