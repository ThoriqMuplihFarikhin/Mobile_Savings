<?php

namespace App\Livewire\Admin;

use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class KelolaKolektor extends Component
{
    use WithPagination;

    public $search = '';

    public $showForm = false;

    public $editId = null;

    public $name = '';

    public $noHp = '';

    public $pin = '';

    public $showAssign = false;

    public $selectedKolektor = null;

    public $availableNasabah = [];

    public $assignNasabahId = '';

    public function render()
    {
        $kolektor = User::where('role', 'kolektor')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('no_hp', 'like', "%{$this->search}%");
            })
            ->withCount(['kolektorNasabahs as nasabah_aktif_count' => function ($q) {
                $q->where('status', 'aktif');
            }])
            ->latest()
            ->paginate(10);

        return view('livewire.admin.kelola-kolektor', compact('kolektor'));
    }

    public function toggleForm()
    {
        $this->showForm = ! $this->showForm;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->name = '';
        $this->noHp = '';
        $this->pin = '';
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'noHp' => 'required|string|max:20|unique:users,no_hp,'.($this->editId ?? ''),
            'pin' => $this->editId ? 'nullable|string|digits:6' : 'required|string|digits:6',
        ]);

        if ($this->editId) {
            $user = User::find($this->editId);
            $user->update([
                'name' => $this->name,
                'no_hp' => $this->noHp,
            ]);

            if ($this->pin) {
                $user->update(['pin_hash' => Hash::make($this->pin)]);
            }
        } else {
            $user = User::create([
                'name' => $this->name,
                'no_hp' => $this->noHp,
                'pin_hash' => Hash::make($this->pin),
                'role' => 'kolektor',
                'status_akun' => 'aktif',
            ]);

            $user->assignRole('kolektor');
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'Kolektor berhasil disimpan!');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->editId = $user->id;
        $this->name = $user->name;
        $this->noHp = $user->no_hp;
        $this->showForm = true;
    }

    public function toggleStatus($id)
    {
        $user = User::find($id);
        if (! $user || $user->role !== 'kolektor') {
            return;
        }

        if ($user->status_akun === 'aktif' && $user->hasUnsettledCash()) {
            session()->flash('error', 'Kolektor memiliki setoran yang belum disetor ke kantor. Selesaikan terlebih dahulu sebelum menonaktifkan.');

            return;
        }

        $newStatus = $user->status_akun === 'aktif' ? 'terkunci' : 'aktif';
        $user->update(['status_akun' => $newStatus]);

        session()->flash('success', 'Status kolektor berhasil diubah!');
    }

    public function bukaKunci(int $id): void
    {
        $user = User::find($id);
        if (! $user || $user->role !== 'kolektor') {
            return;
        }

        $user->update([
            'status_akun' => 'aktif',
            'percobaan_gagal' => 0,
            'login_terkunci_hingga' => null,
        ]);

        session()->flash('success', 'Kunci akun kolektor berhasil dibuka!');
    }

    public function toggleAssign($id)
    {
        $this->showAssign = true;
        $this->selectedKolektor = User::find($id);
        $this->assignNasabahId = '';
        $this->searchNasabah();
    }

    public function searchNasabah()
    {
        $assignedIds = KolektorNasabah::where('kolektor_id', $this->selectedKolektor->id)
            ->where('status', 'aktif')
            ->pluck('nasabah_id')
            ->toArray();

        $this->availableNasabah = NasabahProfil::where('status_pendaftaran', 'aktif')
            ->whereNotIn('user_id', $assignedIds)
            ->with('user')
            ->get();
    }

    public function assignNasabah()
    {
        $this->validate([
            'assignNasabahId' => 'required|exists:users,id',
        ]);

        KolektorNasabah::create([
            'kolektor_id' => $this->selectedKolektor->id,
            'nasabah_id' => $this->assignNasabahId,
            'tanggal_mulai_ditangani' => now()->toDateString(),
            'status' => 'aktif',
        ]);

        $this->assignNasabahId = '';
        $this->searchNasabah();

        session()->flash('success', 'Nasabah berhasil ditugaskan ke kolektor!');
    }

    public function removeAssign($id)
    {
        $assign = KolektorNasabah::find($id);
        if ($assign) {
            $assign->update([
                'status' => 'nonaktif',
                'tanggal_selesai_ditangani' => now()->toDateString(),
            ]);
        }

        $this->searchNasabah();
        session()->flash('success', 'Penugasan nasabah berhasil dihapus!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
}
