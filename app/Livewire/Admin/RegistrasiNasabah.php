<?php

namespace App\Livewire\Admin;

use App\Actions\Pin\KirimPinAwalAction;
use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\User;
use App\Support\NomorHp;
use App\Support\Pin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
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

    public bool $showForm = false;

    public bool $modeOffline = false;

    public string $nama = '';

    public string $noHp = '';

    public string $alamat = '';

    public string $tanggalLahir = '';

    public string $jenisKelamin = 'laki-laki';

    public string $pekerjaan = '';

    public function render(): View
    {
        $nasabah = User::where('role', 'nasabah')
            ->with('nasabahProfil')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.registrasi-nasabah', compact('nasabah'));
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
        $this->reset(['nama', 'noHp', 'alamat', 'tanggalLahir', 'jenisKelamin', 'pekerjaan', 'modeOffline']);
    }

    public function submit(): void
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

        $pinDefault = Pin::acak();

        $user = DB::transaction(function () use ($pinDefault, $modeOffline): User {
            $user = User::create([
                'name' => $this->nama,
                'no_hp' => $modeOffline ? null : $this->noHp,
                'pin_hash' => Hash::make($modeOffline ? Str::random(40) : $pinDefault),
                'role' => 'nasabah',
                'status_akun' => 'aktif',
                'mode_akses' => $modeOffline ? 'offline' : 'digital',
                'harus_ganti_pin' => ! $modeOffline,
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

            return $user;
        });

        ActivityLogger::log('registrasi_nasabah', 'users', $user->id, [
            'nama' => $this->nama,
            'no_hp' => $modeOffline ? null : $this->noHp,
            'mode_akses' => $user->mode_akses,
        ]);

        $this->showForm = false;
        $this->reset(['nama', 'noHp', 'alamat', 'tanggalLahir', 'jenisKelamin', 'pekerjaan', 'modeOffline']);

        if ($modeOffline) {
            session()->flash('success', 'Nasabah offline berhasil didaftarkan! Nasabah tidak memakai aplikasi sehingga nomor HP dikosongkan, tidak ada PIN awal yang dikirim, dan akun ditandai mode offline.');

            return;
        }

        $terkirim = app(KirimPinAwalAction::class)->kirim($user, $pinDefault);

        session()->flash('success', $terkirim
            ? 'Nasabah berhasil didaftarkan! PIN awal dikirim ke WhatsApp nasabah dan tidak ditampilkan di sini. Nasabah wajib login dan mengganti PIN sebelum penarikan pertama.'
            : "Nasabah berhasil didaftarkan! PIN awal: {$pinDefault} — sampaikan ke nasabah secara langsung/aman. Nasabah wajib login dan mengganti PIN sebelum penarikan pertama.");
    }
}
