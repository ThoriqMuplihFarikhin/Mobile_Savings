<?php

namespace App\Livewire\Nasabah;

use App\Models\LogNotifikasi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.mobile')]
class Notifikasi extends Component
{
    use WithPagination;

    public function render()
    {
        $notifikasi = LogNotifikasi::where('nasabah_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('livewire.nasabah.notifikasi', compact('notifikasi'));
    }

    public function markAsRead($id)
    {
        LogNotifikasi::where('id', $id)->update(['is_read' => true]);
    }

    public function markAllRead()
    {
        LogNotifikasi::where('nasabah_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}
