<?php

namespace App\Livewire\Nasabah;

use App\Models\NasabahProfil;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class Profil extends Component
{
    public $nama;

    public $noHp;

    public $alamat;

    public $tanggalLahir;

    public $jenisKelamin;

    public $pekerjaan;

    public function mount()
    {
        $user = Auth::user();
        $profil = NasabahProfil::where('user_id', $user->id)->first();

        $this->nama = $user->name;
        $this->noHp = $user->no_hp;
        $this->alamat = $profil->alamat ?? '';
        $this->tanggalLahir = $profil->tanggal_lahir ?? '';
        $this->jenisKelamin = $profil->jenis_kelamin ?? 'laki-laki';
        $this->pekerjaan = $profil->pekerjaan ?? '';
    }

    public function render()
    {
        return view('livewire.nasabah.profil');
    }

    public function update()
    {
        $this->validate([
            'nama' => 'required|string|min:3',
            'noHp' => 'required|string|min:10|max:15',
            'alamat' => 'required|string|min:5',
            'tanggalLahir' => 'required|date|before:today',
            'jenisKelamin' => 'required|in:laki-laki,perempuan',
            'pekerjaan' => 'nullable|string',
        ]);

        $user = Auth::user();
        $user->update(['name' => $this->nama, 'no_hp' => $this->noHp]);

        NasabahProfil::where('user_id', $user->id)->update([
            'nama' => $this->nama,
            'alamat' => $this->alamat,
            'tanggal_lahir' => $this->tanggalLahir,
            'jenis_kelamin' => $this->jenisKelamin,
            'pekerjaan' => $this->pekerjaan,
        ]);

        session()->flash('success', 'Profil berhasil diupdate!');
    }
}
