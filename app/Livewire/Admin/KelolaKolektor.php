<?php

namespace App\Livewire\Admin;

use App\Actions\Kolektor\TugaskanNasabahAction;
use App\Actions\Pin\ResetPinOlehAdminAction;
use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\KolektorNasabah;
use App\Models\LogHandoverKolektor;
use App\Models\NasabahProfil;
use App\Models\User;
use App\Support\NomorHp;
use DomainException;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class KelolaKolektor extends Component
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

    public $name = '';

    public string $noHp = '';

    public $pin = '';

    public $showAssign = false;

    public $selectedKolektor = null;

    public $availableNasabah = [];

    public $assignNasabahId = '';

    public bool $tampilKonfirmasiResetPin = false;

    public ?int $resetPinId = null;

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
        $this->noHp = NomorHp::normalize($this->noHp);

        $this->validate([
            'name' => 'required|string|max:255',
            'noHp' => 'required|string|max:20|unique:users,no_hp,'.($this->editId ?? '').'|regex:'.NomorHp::PATTERN,
            'pin' => $this->editId ? 'nullable|string|digits:6' : 'required|string|digits:6',
        ]);

        if ($this->editId) {
            $user = User::find($this->editId);

            if (! $user) {
                session()->flash('error', 'Kolektor tidak ditemukan. Muat ulang daftar lalu coba lagi.');

                return;
            }

            $user->update([
                'name' => $this->name,
                'no_hp' => $this->noHp,
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
                'name' => $this->name,
                'no_hp' => $this->noHp,
                'pin_hash' => Hash::make($this->pin),
                'role' => 'kolektor',
                'status_akun' => 'aktif',
                'harus_ganti_pin' => true,
            ]);

            $user->assignRole('kolektor');
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'Kolektor berhasil disimpan!');
    }

    public function edit($id)
    {
        $user = User::find($id);

        if (! $user instanceof User || $user->role !== 'kolektor') {
            session()->flash('error', 'Kolektor tidak ditemukan. Muat ulang daftar lalu coba lagi.');

            return;
        }

        $this->editId = $user->id;
        $this->name = $user->name;
        $this->noHp = $user->no_hp;
        $this->showForm = true;
    }

    public function toggleStatus($id)
    {
        $user = User::find($id);
        if (! $user instanceof User || $user->role !== 'kolektor') {
            return;
        }

        $mengunci = $user->status_akun === 'aktif';
        $denganKas = $mengunci && $user->hasUnsettledCash();

        $user->update(['status_akun' => $mengunci ? 'terkunci' : 'aktif']);

        if ($denganKas) {
            try {
                ActivityLogger::log('kunci_kolektor_dengan_kas', 'users', $user->id, [
                    'kolektor_id' => $user->id,
                    'kas_belum_disetor' => true,
                ]);
            } catch (\Exception $e) {
                report($e);
            }

            session()->flash('success', 'Status kolektor berhasil diubah! Kas yang belum disetor tetap dipantau di dashboard kas.');

            return;
        }

        session()->flash('success', 'Status kolektor berhasil diubah!');
    }

    public function bukaKunci(int $id): void
    {
        $user = User::find($id);
        if (! $user || $user->role !== 'kolektor') {
            return;
        }

        if (LogHandoverKolektor::where('kolektor_lama_id', $user->id)->exists()) {
            session()->flash('error', 'Status kolektor ini berasal dari serah terima (handover) nasabah. Kolektor lama sengaja dinonaktifkan; jangan dibuka kunci lewat tombol ini.');

            return;
        }

        $user->update([
            'status_akun' => 'aktif',
            'percobaan_gagal' => 0,
            'login_terkunci_hingga' => null,
        ]);

        session()->flash('success', 'Kunci akun kolektor berhasil dibuka!');
    }

    public function confirmResetPin(int $userId): void
    {
        $this->resetPinId = $userId;
        $this->tampilKonfirmasiResetPin = true;
    }

    public function resetPin(): void
    {
        $target = User::find($this->resetPinId);

        if (! $target) {
            $this->tampilKonfirmasiResetPin = false;
            $this->resetPinId = null;
            session()->flash('error', 'Pengguna tidak ditemukan.');

            return;
        }

        $pinBaru = app(ResetPinOlehAdminAction::class)->execute($target);

        $this->tampilKonfirmasiResetPin = false;
        $this->resetPinId = null;

        session()->flash('success', "PIN berhasil direset. PIN baru: {$pinBaru}. Catat sekarang karena hanya ditampilkan sekali. Pengguna wajib mengganti PIN setelah login.");
    }

    public function toggleAssign($id)
    {
        $kolektor = User::find($id);

        if (! $kolektor instanceof User || $kolektor->role !== 'kolektor') {
            $this->showAssign = false;
            $this->selectedKolektor = null;
            $this->availableNasabah = collect();
            session()->flash('error', 'Kolektor tidak ditemukan. Muat ulang daftar lalu coba lagi.');

            return;
        }

        $this->showAssign = true;
        $this->selectedKolektor = $kolektor;
        $this->assignNasabahId = '';
        $this->searchNasabah();
    }

    public function searchNasabah()
    {
        if (! $this->selectedKolektor instanceof User) {
            $this->availableNasabah = collect();

            return;
        }

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
        if (! $this->selectedKolektor instanceof User) {
            session()->flash('error', 'Pilih kolektor terlebih dahulu.');

            return;
        }

        $this->validate([
            'assignNasabahId' => 'required|exists:users,id',
        ]);

        try {
            app(TugaskanNasabahAction::class)->execute(
                (int) $this->selectedKolektor->id,
                (int) $this->assignNasabahId,
            );
        } catch (DomainException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

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
                'aktif_unik' => null,
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
