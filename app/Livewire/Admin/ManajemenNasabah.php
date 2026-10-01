<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\NasabahProfil;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManajemenNasabah extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public $search = '';

    public $showForm = false;

    public $editId = null;

    public $nama = '';

    public $no_hp = '';

    public $alamat = '';

    public $pin = '';

    public $confirmDelete = false;

    public $deleteId = null;

    protected $listeners = ['nasabahCreated' => '$refresh'];

    public function render()
    {
        $nasabah = NasabahProfil::with(['user', 'didaftarkanOleh'])
            ->where('nama', 'like', "%{$this->search}%")
            ->orWhereHas('user', function ($query) {
                $query->where('no_hp', 'like', "%{$this->search}%");
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.manajemen-nasabah', compact('nasabah'));
    }

    public function toggleForm()
    {
        $this->showForm = ! $this->showForm;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->nama = '';
        $this->no_hp = '';
        $this->alamat = '';
        $this->pin = '';
    }

    public function save()
    {
        $this->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20|unique:users,no_hp,'.$this->editId,
            'alamat' => 'required|string',
            'pin' => $this->editId ? 'nullable|string|digits:6' : 'required|string|digits:6',
        ]);

        if ($this->editId) {
            $user = User::find($this->editId);
            $user->update([
                'name' => $this->nama,
                'no_hp' => $this->no_hp,
            ]);

            $user->nasabahProfil()->update([
                'nama' => $this->nama,
                'alamat' => $this->alamat,
            ]);

            if ($this->pin) {
                $user->update([
                    'pin_hash' => Hash::make($this->pin),
                    'harus_ganti_pin' => true,
                    'percobaan_gagal' => 0,
                    'login_terkunci_hingga' => null,
                ]);
            }
        } else {
            $user = User::create([
                'name' => $this->nama,
                'no_hp' => $this->no_hp,
                'pin_hash' => Hash::make($this->pin),
                'role' => 'nasabah',
                'status_akun' => 'aktif',
                'harus_ganti_pin' => false,
            ]);

            $user->assignRole('nasabah');

            $user->nasabahProfil()->create([
                'nama' => $this->nama,
                'alamat' => $this->alamat,
                'didaftarkan_oleh' => auth()->id(),
                'status_pendaftaran' => 'aktif',
                'diverifikasi_oleh' => auth()->id(),
                'tanggal_verifikasi' => now(),
            ]);
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'Nasabah berhasil disimpan!');
    }

    public function edit($id)
    {
        $profil = NasabahProfil::findOrFail($id);
        $this->editId = $profil->user_id;
        $this->nama = $profil->nama;
        $this->no_hp = $profil->user->no_hp;
        $this->alamat = $profil->alamat;
        $this->showForm = true;
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function delete()
    {
        $profil = NasabahProfil::find($this->deleteId);
        if ($profil) {
            $profil->user->delete();
        }
        $this->confirmDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Nasabah berhasil dihapus!');
    }

    public function toggleStatus($id)
    {
        $profil = NasabahProfil::find($id);
        if ($profil) {
            $newStatus = $profil->status_pendaftaran === 'aktif' ? 'ditolak' : 'aktif';
            $profil->update([
                'status_pendaftaran' => $newStatus,
                'diverifikasi_oleh' => auth()->id(),
                'tanggal_verifikasi' => now(),
            ]);
            $profil->user->update(['status_akun' => $newStatus === 'aktif' ? 'aktif' : 'terkunci']);
        }
        session()->flash('success', 'Status nasabah berhasil diubah!');
    }

    public function bukaKunci(int $id): void
    {
        $profil = NasabahProfil::find($id);
        if (! $profil) {
            return;
        }

        if ($profil->status_pendaftaran !== 'aktif') {
            session()->flash('error', 'Nasabah belum diverifikasi. Selesaikan verifikasi terlebih dahulu sebelum membuka kunci.');

            return;
        }

        $profil->user->update([
            'status_akun' => 'aktif',
            'percobaan_gagal' => 0,
            'login_terkunci_hingga' => null,
        ]);

        session()->flash('success', 'Kunci akun nasabah berhasil dibuka!');
    }
}
