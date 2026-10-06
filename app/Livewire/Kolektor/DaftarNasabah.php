<?php

namespace App\Livewire\Kolektor;

use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\NasabahProfil;
use App\Models\User;
use App\Support\NomorHp;
use App\Support\Pin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

    public bool $modeOffline = false;

    public $nama = '';

    public string $noHp = '';

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
        $this->reset(['nama', 'noHp', 'alamat', 'tanggalLahir', 'jenisKelamin', 'pekerjaan', 'modeOffline']);
    }

    public function submit()
    {
        $modeOffline = $this->modeOffline;

        $aturan = [
            'nama' => 'required|string|min:3',
            'alamat' => 'required|string|min:5',
            'tanggalLahir' => 'required|date|before:today',
            'jenisKelamin' => 'required|in:laki-laki,perempuan',
            'pekerjaan' => 'nullable|string',
        ];

        if ($modeOffline) {
            $this->validate($aturan);
        } else {
            $this->noHp = NomorHp::normalize($this->noHp);

            $this->validate($aturan + [
                'noHp' => 'required|string|unique:users,no_hp|min:10|max:15|regex:'.NomorHp::PATTERN,
            ]);
        }

        $user = DB::transaction(function () use ($modeOffline): User {
            $user = User::create([
                'name' => $this->nama,
                'no_hp' => $modeOffline ? null : $this->noHp,
                'pin_hash' => Hash::make($modeOffline ? Str::random(40) : Pin::acak()),
                'role' => 'nasabah',
                'status_akun' => 'terkunci',
                'mode_akses' => $modeOffline ? 'offline' : 'digital',
                'harus_ganti_pin' => ! $modeOffline,
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

            return $user;
        });

        ActivityLogger::log('registrasi_nasabah', 'users', $user->id, [
            'nama' => $this->nama,
            'no_hp' => $modeOffline ? null : $this->noHp,
            'mode_akses' => $user->mode_akses,
            'didaftarkan_oleh' => Auth::id(),
        ]);

        $this->showForm = false;
        $this->reset(['nama', 'noHp', 'alamat', 'tanggalLahir', 'jenisKelamin', 'pekerjaan', 'modeOffline']);

        if ($modeOffline) {
            session()->flash('success', 'Nasabah offline berhasil didaftarkan! Nomor HP dikosongkan dan akun ditandai mode offline tanpa PIN awal. Menunggu verifikasi dari admin.');

            return;
        }

        session()->flash('success', 'Nasabah berhasil didaftarkan! PIN awal disimpan aman dan tidak ditampilkan ke kolektor — PIN baru dikirim ke nasabah saat admin memverifikasi. Nasabah wajib login dan mengganti PIN sebelum penarikan pertama. Menunggu verifikasi dari admin.');
    }
}
