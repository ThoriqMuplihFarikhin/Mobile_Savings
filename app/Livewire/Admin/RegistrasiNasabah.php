<?php

namespace App\Livewire\Admin;

use App\Helpers\ActivityLogger;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class RegistrasiNasabah extends Component
{
    public $showForm = false;

    public $nama = '';

    public $noHp = '';

    public $alamat = '';

    public $tanggalLahir = '';

    public $jenisKelamin = 'laki-laki';

    public $pekerjaan = '';

    public function render()
    {
        $nasabah = User::where('role', 'nasabah')
            ->with('nasabahProfil')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.registrasi-nasabah', compact('nasabah'));
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

        $user = User::create([
            'name' => $this->nama,
            'no_hp' => $this->noHp,
            'pin_hash' => Hash::make('123456'),
            'role' => 'nasabah',
            'status_akun' => 'aktif',
        ]);

        $user->assignRole('nasabah');

        $user->nasabahProfil()->create([
            'nama' => $this->nama,
            'alamat' => $this->alamat,
            'tanggal_lahir' => $this->tanggalLahir,
            'jenis_kelamin' => $this->jenisKelamin,
            'pekerjaan' => $this->pekerjaan,
            'didaftarkan_oleh' => auth()->id(),
            'status_pendaftaran' => 'aktif',
            'diverifikasi_oleh' => auth()->id(),
            'tanggal_verifikasi' => now(),
        ]);

        ActivityLogger::log('registrasi_nasabah', 'users', $user->id, [
            'nama' => $this->nama,
            'no_hp' => $this->noHp,
        ]);

        $this->showForm = false;
        $this->reset(['nama', 'noHp', 'alamat', 'tanggalLahir', 'jenisKelamin', 'pekerjaan']);
        session()->flash('success', 'Nasabah berhasil didaftarkan! PIN default: 123456');
    }
}
