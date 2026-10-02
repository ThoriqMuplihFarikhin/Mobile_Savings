<?php

namespace App\Livewire\Kolektor;

use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\NasabahProfil;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class DaftarNasabah extends Component
{
    use AuthorizesRole;

    protected function requiredRole(): string
    {
        return 'kolektor';
    }

    public $showForm = false;

    public $nama = '';

    public $noHp = '';

    public $alamat = '';

    public $tanggalLahir = '';

    public $jenisKelamin = 'laki-laki';

    public $pekerjaan = '';

    public function render()
    {
        $nasabahList = NasabahProfil::where('didaftarkan_oleh', Auth::id())
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('livewire.kolektor.daftar-nasabah', compact('nasabahList'));
    }

    public function toggleForm()
    {
        $this->showForm = ! $this->showForm;
        $this->reset(['nama', 'noHp', 'alamat', 'tanggalLahir', 'jenisKelamin', 'pekerjaan']);
    }

    public function submit()
    {
        $this->validate([
            'nama' => 'required|string|min:3',
            'noHp' => 'required|string|unique:users,no_hp|min:10|max:15',
            'alamat' => 'required|string|min:5',
            'tanggalLahir' => 'required|date|before:today',
            'jenisKelamin' => 'required|in:laki-laki,perempuan',
            'pekerjaan' => 'nullable|string',
        ]);

        $pinDefault = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user = User::create([
            'name' => $this->nama,
            'no_hp' => $this->noHp,
            'pin_hash' => Hash::make($pinDefault),
            'role' => 'nasabah',
            'status_akun' => 'terkunci',
            'harus_ganti_pin' => true,
        ]);

        $user->assignRole('nasabah');

        $user->nasabahProfil()->create([
            'nama' => $this->nama,
            'alamat' => $this->alamat,
            'tanggal_lahir' => $this->tanggalLahir,
            'jenis_kelamin' => $this->jenisKelamin,
            'pekerjaan' => $this->pekerjaan ?: null,
            'didaftarkan_oleh' => Auth::id(),
            'status_pendaftaran' => 'pending_verifikasi',
        ]);

        ActivityLogger::log('registrasi_nasabah', 'users', $user->id, [
            'nama' => $this->nama,
            'no_hp' => $this->noHp,
            'didaftarkan_oleh' => Auth::id(),
        ]);

        $this->showForm = false;
        $this->reset(['nama', 'noHp', 'alamat', 'tanggalLahir', 'jenisKelamin', 'pekerjaan']);
        session()->flash('success', "Nasabah berhasil didaftarkan! PIN awal: {$pinDefault} — sampaikan ke nasabah secara langsung/aman. Nasabah wajib login dan mengganti PIN sebelum penarikan pertama. Menunggu verifikasi dari admin.");
    }
}
