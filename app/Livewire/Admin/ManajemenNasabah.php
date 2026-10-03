<?php

namespace App\Livewire\Admin;

use App\Actions\Pin\ResetPinOlehAdminAction;
use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\JadwalKunjungan;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use App\Models\Komplain;
use App\Models\LogAktivitas;
use App\Models\LogNotifikasi;
use App\Models\NasabahProfil;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use App\Support\NomorHp;
use Illuminate\Support\Facades\DB;
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

    public string $no_hp = '';

    public $alamat = '';

    public $pin = '';

    public $tampilKonfirmasiHapus = false;

    public $deleteId = null;

    public bool $tampilKonfirmasiResetPin = false;

    public ?int $resetPinId = null;

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
        $this->no_hp = NomorHp::normalize($this->no_hp);

        $this->validate([
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20|unique:users,no_hp,'.$this->editId.'|regex:'.NomorHp::PATTERN,
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
        $this->tampilKonfirmasiHapus = true;
    }

    public function delete(): void
    {
        $profilId = $this->deleteId;
        $nasabahId = DB::table('nasabah_profil')->where('id', $profilId)->value('user_id');

        if ($nasabahId === null) {
            $this->tampilKonfirmasiHapus = false;
            $this->deleteId = null;

            return;
        }

        $nasabahId = (int) $nasabahId;

        $punyaRiwayatKeuangan = SaldoProduk::where('nasabah_id', $nasabahId)->where('saldo', '!=', 0)->exists()
            || TransaksiSetoran::where('nasabah_id', $nasabahId)->exists()
            || TransaksiPenarikan::where('nasabah_id', $nasabahId)->exists();

        if ($punyaRiwayatKeuangan) {
            session()->flash('error', 'Nasabah tidak dapat dihapus karena masih memiliki saldo, setoran, atau penarikan. Nonaktifkan akun ini saja.');

            return;
        }

        DB::transaction(function () use ($nasabahId, $profilId): void {
            KolektorNasabah::where('nasabah_id', $nasabahId)->delete();
            JadwalKunjungan::where('nasabah_id', $nasabahId)->delete();
            Komplain::where('nasabah_id', $nasabahId)->delete();
            KepesertaanPaket::where('nasabah_id', $nasabahId)->delete();
            LogNotifikasi::where('nasabah_id', $nasabahId)->delete();
            LogAktivitas::where('user_id', $nasabahId)->delete();
            SaldoProduk::where('nasabah_id', $nasabahId)->delete();
            NasabahProfil::where('id', $profilId)->delete();
            DB::table('model_has_roles')
                ->where('model_id', $nasabahId)
                ->where('model_type', User::class)
                ->delete();
            User::where('id', $nasabahId)->delete();
        });

        $this->tampilKonfirmasiHapus = false;
        $this->deleteId = null;
        session()->flash('success', 'Nasabah berhasil dihapus!');
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

    public function toggleStatus($id)
    {
        $hasil = DB::transaction(function () use ($id) {
            $profil = NasabahProfil::whereKey($id)->lockForUpdate()->first();

            if (! $profil) {
                return ['error' => 'Nasabah tidak ditemukan.'];
            }

            if ($profil->status_pendaftaran === 'pending_verifikasi') {
                return ['error' => 'Nasabah belum diverifikasi. Selesaikan verifikasi terlebih dahulu.'];
            }

            $statusLama = $profil->status_pendaftaran;
            $statusBaru = $statusLama === 'aktif' ? 'ditolak' : 'aktif';

            if ($statusBaru === 'ditolak') {
                $adaPenarikanBerjalan = TransaksiPenarikan::where('nasabah_id', $profil->user_id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->lockForUpdate()
                    ->exists();

                if ($adaPenarikanBerjalan) {
                    return ['error' => 'Nasabah tidak dapat dinonaktifkan karena masih ada penarikan menunggu proses.'];
                }
            }

            $profil->update([
                'status_pendaftaran' => $statusBaru,
                'diverifikasi_oleh' => auth()->id(),
                'tanggal_verifikasi' => now(),
            ]);
            $profil->user->update(['status_akun' => $statusBaru === 'aktif' ? 'aktif' : 'terkunci']);

            return [
                'status_lama' => $statusLama,
                'status_baru' => $statusBaru,
                'user_id' => $profil->user_id,
            ];
        });

        if (isset($hasil['error'])) {
            session()->flash('error', $hasil['error']);

            return;
        }

        ActivityLogger::log('ubah_status_nasabah', 'nasabah_profil', (int) $id, [
            'status_lama' => $hasil['status_lama'],
            'status_baru' => $hasil['status_baru'],
            'user_id' => $hasil['user_id'],
        ]);

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
