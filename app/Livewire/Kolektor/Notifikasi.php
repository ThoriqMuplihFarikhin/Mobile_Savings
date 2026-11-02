<?php

namespace App\Livewire\Kolektor;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\LogNotifikasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.mobile')]
#[Title('Notifikasi')]
class Notifikasi extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'kolektor';
    }

    public function markAsRead(int $id): void
    {
        LogNotifikasi::where('id', $id)
            ->where('nasabah_id', Auth::id())
            ->update(['is_read' => true]);
    }

    public function markAllRead(): void
    {
        LogNotifikasi::where('nasabah_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function render(): View
    {
        $notifikasi = LogNotifikasi::where('nasabah_id', Auth::id())
            ->latest('waktu_kirim')
            ->latest('id')
            ->paginate(15);

        $unreadCount = LogNotifikasi::where('nasabah_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return view('livewire.kolektor.notifikasi', compact('notifikasi', 'unreadCount'));
    }
}
