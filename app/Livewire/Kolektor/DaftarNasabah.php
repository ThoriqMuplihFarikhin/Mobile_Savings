<?php

namespace App\Livewire\Kolektor;

use App\Helpers\ActivityLogger;
use App\Models\NasabahProfil;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class DaftarNasabah extends Component
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

        $user = User::create([
            'name' => $this->nama,
            'no_hp' => $this->noHp,
            'pin_hash' => Hash::make('123456'),
            'role' => 'nasabah',
            'status_akun' => 'terkunci',
        ]);

        $user->assignRole('nasabah');

        $user->nasabahProfil()->create([
            'nama' => $this->nama,
            'alamat' => $this->alamat,
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
        session()->flash('success', 'Nasabah berhasil didaftarkan! Menunggu verifikasi dari admin.');
    }
}
