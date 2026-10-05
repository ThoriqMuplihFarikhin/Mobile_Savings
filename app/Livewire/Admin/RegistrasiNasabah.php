<?php

namespace App\Livewire\Admin;

use App\Actions\Pin\KirimPinAwalAction;
use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\User;
use App\Support\NomorHp;
use App\Support\Pin;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class RegistrasiNasabah extends Component
{
    use AuthorizesRole;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public $showForm = false;

    public $nama = '';

    public string $noHp = '';

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
        $this->noHp = NomorHp::normalize($this->noHp);

        $this->validate([
            'nama' => 'required|string|min:3',
            'noHp' => 'required|string|unique:users,no_hp|min:10|max:15|regex:'.NomorHp::PATTERN,
            'alamat' => 'required|string|min:5',
            'tanggalLahir' => 'required|date|before:today',
            'jenisKelamin' => 'required|in:laki-laki,perempuan',
            'pekerjaan' => 'nullable|string',
        ]);

        $pinDefault = Pin::acak();

        $user = User::create([
            'name' => $this->nama,
            'no_hp' => $this->noHp,
            'pin_hash' => Hash::make($pinDefault),
            'role' => 'nasabah',
            'status_akun' => 'aktif',
            'harus_ganti_pin' => true,
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

        $terkirim = app(KirimPinAwalAction::class)->kirim($user, $pinDefault);

        session()->flash('success', $terkirim
            ? 'Nasabah berhasil didaftarkan! PIN awal dikirim ke WhatsApp nasabah dan tidak ditampilkan di sini. Nasabah wajib login dan mengganti PIN sebelum penarikan pertama.'
            : "Nasabah berhasil didaftarkan! PIN awal: {$pinDefault} — sampaikan ke nasabah secara langsung/aman. Nasabah wajib login dan mengganti PIN sebelum penarikan pertama.");
    }
}
