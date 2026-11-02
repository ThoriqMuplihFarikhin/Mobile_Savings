<?php

namespace App\Livewire\Admin;

use App\Actions\Paket\DaftarkanKePaketAction;
use App\Actions\Pin\KirimPinAwalAction;
use App\Actions\Pin\ResetPinOlehAdminAction;
use App\Helpers\ActivityLogger;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\JadwalKunjungan;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use App\Models\Komplain;
use App\Models\LogNotifikasi;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use App\Support\NomorHp;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
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

    public string $search = '';

    public bool $showForm = false;

    public ?int $editId = null;

    public string $nama = '';

    public string $no_hp = '';

    public string $alamat = '';

    public string $pin = '';

    public bool $tampilKonfirmasiHapus = false;

    public ?int $deleteId = null;

    public bool $tampilKonfirmasiResetPin = false;

    public ?int $resetPinId = null;

    public string $modeFilter = '';

    public string $statusFilter = '';

    public bool $tampilKonversiMode = false;

    public ?int $konversiProfilId = null;

    public string $konversiNoHp = '';

    public ?int $detailNasabahId = null;

    public string $periodeDetail = '30 Hari';

    public bool $tampilDaftarPaket = false;

    public ?int $targetDaftarUserId = null;

    public int $produkDaftarId = 0;

    public bool $setujuDaftar = false;

    public string $catatanDaftar = '';

    public ?string $pesanErrorDaftar = null;

    /** @var array<string, string> */
    protected $listeners = ['nasabahCreated' => '$refresh'];

    public function mount(?int $detailId = null): void
    {
        $target = $detailId ?? (request()->filled('detail') ? (int) request('detail') : null);
        if ($target) {
            $this->bukaDetail($target);
        }
    }

    public function render(): View
    {
        $nasabah = NasabahProfil::with(['user', 'didaftarkanOleh', 'penugasanKolektor.kolektor'])
            ->where(function ($query) {
                $query->where('nama', 'like', "%{$this->search}%")
                    ->orWhereHas('user', function ($sub) {
                        $sub->where('no_hp', 'like', "%{$this->search}%");
                    });
            })
            ->when($this->modeFilter !== '', function ($query) {
                $query->whereHas('user', function ($sub) {
                    $sub->where('mode_akses', $this->modeFilter);
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                if ($this->statusFilter === 'terkunci') {
                    $query->whereHas('user', function ($sub) {
                        $sub->where('status_akun', 'terkunci')
                            ->orWhere(function ($q) {
                                $q->whereNotNull('login_terkunci_hingga')
                                    ->where('login_terkunci_hingga', '>', now());
                            });
                    });
                } else {
                    $query->where('status_pendaftaran', $this->statusFilter);
                }
            })
            ->latest()
            ->paginate(10);

        $detailData = null;
        if ($this->detailNasabahId) {
            $profil = NasabahProfil::with(['user', 'didaftarkanOleh', 'penugasanKolektor.kolektor'])->find($this->detailNasabahId);
            if ($profil) {
                $saldoProduk = SaldoProduk::where('nasabah_id', $profil->user_id)->with('produk')->get();
                $totalSaldo = (float) $saldoProduk->sum('saldo');

                $kepesertaan = KepesertaanPaket::where('nasabah_id', $profil->user_id)->with('produk')->latest('tanggal_mulai_ikut')->get();

                $barCount = match ($this->periodeDetail) {
                    '90 Hari' => 30,
                    'Tahun Ini' => 40,
                    default => 14,
                };

                $bars = [];
                $maxVal = max($totalSaldo, 1);
                for ($i = 0; $i < $barCount; $i++) {
                    $val = $totalSaldo > 0 ? round($totalSaldo * (0.5 + ($i / $barCount) * 0.5)) : 0;
                    $pct = $totalSaldo > 0 ? max(4, round(($val / $maxVal) * 100)) : 4;
                    $bars[] = [
                        'val' => $val,
                        'pct' => $pct,
                    ];
                }

                $riwayatSetoran = TransaksiSetoran::where('nasabah_id', $profil->user_id)
                    ->where('status', '!=', 'dibatalkan')
                    ->latest('tanggal_transaksi')
                    ->take(5)
                    ->get()
                    ->map(fn ($s) => [
                        'tanggal' => Carbon::parse($s->tanggal_transaksi)->translatedFormat('d M'),
                        'tipe' => 'Setoran',
                        'nominal' => (float) $s->nominal,
                        'status' => 'Berhasil',
                    ]);

                $riwayatPenarikan = TransaksiPenarikan::where('nasabah_id', $profil->user_id)
                    ->whereIn('status', ['approved', 'selesai', 'pending'])
                    ->latest('created_at')
                    ->take(5)
                    ->get()
                    ->map(fn ($p) => [
                        'tanggal' => Carbon::parse($p->waktu_approval ?? $p->created_at)->translatedFormat('d M'),
                        'tipe' => 'Penarikan',
                        'nominal' => (float) $p->nominal_diminta,
                        'status' => $p->status === 'pending' ? 'Pending' : 'Berhasil',
                    ]);

                $riwayat = $riwayatSetoran->concat($riwayatPenarikan)->take(5);

                $kolektorNama = $profil->penugasanKolektor?->kolektor?->name
                    ?? ($profil->didaftarkanOleh?->isKolektor() ? $profil->didaftarkanOleh->name : '-');

                $detailData = [
                    'profil' => $profil,
                    'kolektor' => $kolektorNama,
                    'saldoProduk' => $saldoProduk,
                    'totalSaldo' => $totalSaldo,
                    'kepesertaan' => $kepesertaan,
                    'bars' => $bars,
                    'riwayat' => $riwayat,
                ];
            }
        }

        $targetUserId = $this->targetDaftarUserId;
        if (! $targetUserId && $detailData) {
            $targetUserId = $detailData['profil']->user_id;
        }
        $paketTerbuka = $targetUserId ? DaftarkanKePaketAction::paketTerbukaUntuk((int) $targetUserId) : collect();

        return view('livewire.admin.manajemen-nasabah', compact('nasabah', 'detailData', 'paketTerbuka'));
    }

    public function bukaDetail(int $id): void
    {
        $profil = NasabahProfil::find($id) ?? NasabahProfil::where('user_id', $id)->first();
        if ($profil) {
            $this->detailNasabahId = $profil->id;
            $this->periodeDetail = '30 Hari';
        }
    }

    public function tutupDetail(): void
    {
        $this->detailNasabahId = null;
    }

    public function setPeriodeDetail(string $periode): void
    {
        $this->periodeDetail = $periode;
    }

    public function bukaDaftarPaket(int $userId): void
    {
        $this->targetDaftarUserId = $userId;
        $this->produkDaftarId = 0;
        $this->setujuDaftar = false;
        $this->catatanDaftar = '';
        $this->pesanErrorDaftar = null;
        $this->tampilDaftarPaket = true;
    }

    public function tutupDaftarPaket(): void
    {
        $this->tampilDaftarPaket = false;
        $this->targetDaftarUserId = null;
        $this->produkDaftarId = 0;
        $this->setujuDaftar = false;
        $this->catatanDaftar = '';
        $this->pesanErrorDaftar = null;
    }

    public function daftarkanKePaket(): void
    {
        $this->validate([
            'produkDaftarId' => 'required|integer|exists:produk_tabungan,id',
            'setujuDaftar' => 'accepted',
            'catatanDaftar' => 'required|string|max:255',
        ], [
            'produkDaftarId.required' => 'Pilih paket tabungan terlebih dahulu.',
            'produkDaftarId.exists' => 'Paket tabungan tidak valid.',
            'setujuDaftar.accepted' => 'Persetujuan komitmen wajib dicentang.',
            'catatanDaftar.required' => 'Catatan komitmen pendaftaran wajib diisi.',
        ]);

        $pelaku = auth()->user();
        if (! $pelaku instanceof User) {
            abort(403);
        }

        $targetUser = User::find($this->targetDaftarUserId);
        if (! $targetUser) {
            $this->pesanErrorDaftar = 'Nasabah tidak ditemukan.';

            return;
        }

        $produk = ProdukTabungan::find($this->produkDaftarId);
        if (! $produk instanceof ProdukTabungan) {
            $this->pesanErrorDaftar = 'Paket tidak ditemukan.';

            return;
        }

        try {
            app(DaftarkanKePaketAction::class)->execute($pelaku, $targetUser, $produk, $this->catatanDaftar);
        } catch (DomainException $e) {
            $this->pesanErrorDaftar = $e->getMessage();

            return;
        }

        session()->flash('success', 'Nasabah berhasil didaftarkan ke paket '.$produk->nama.'.');
        $this->tutupDaftarPaket();
    }

    public function updatedModeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editId = null;
        $this->nama = '';
        $this->no_hp = '';
        $this->alamat = '';
        $this->pin = '';
    }

    public function save(): void
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

            if (! $user) {
                session()->flash('error', 'Nasabah tidak ditemukan. Muat ulang daftar lalu coba lagi.');

                return;
            }

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

    public function edit(int $id): void
    {
        $profil = NasabahProfil::find($id) ?? NasabahProfil::where('user_id', $id)->first();

        if (! $profil) {
            session()->flash('error', 'Nasabah tidak ditemukan. Muat ulang daftar lalu coba lagi.');

            return;
        }

        $this->editId = $profil->user_id;
        $this->nama = $profil->nama;
        $this->no_hp = $profil->user->no_hp ?? '';
        $this->alamat = $profil->alamat;
        $this->pin = '';
        $this->showForm = true;
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->tampilKonfirmasiHapus = true;
    }

    public function delete(): void
    {
        $profilId = $this->deleteId;
        $profilRow = DB::table('nasabah_profil')->where('id', $profilId)->first(['user_id', 'nama']);

        if ($profilRow === null || $profilRow->user_id === null) {
            $this->tampilKonfirmasiHapus = false;
            $this->deleteId = null;

            return;
        }

        $nasabahId = (int) $profilRow->user_id;

        $punyaRiwayatKeuangan = SaldoProduk::where('nasabah_id', $nasabahId)->where('saldo', '!=', 0)->exists()
            || TransaksiSetoran::where('nasabah_id', $nasabahId)->exists()
            || TransaksiPenarikan::where('nasabah_id', $nasabahId)->exists();

        if ($punyaRiwayatKeuangan) {
            session()->flash('error', 'Nasabah tidak dapat dihapus karena masih memiliki saldo, setoran, atau penarikan. Nonaktifkan akun ini saja.');

            return;
        }

        DB::transaction(function () use ($nasabahId, $profilId, $profilRow): void {
            $nasabah = User::find($nasabahId);

            if ($nasabah !== null) {
                ActivityLogger::log('hapus_nasabah', 'users', $nasabahId, [
                    'id_lama' => $nasabahId,
                    'nama' => $profilRow->nama ?: $nasabah->name,
                    'no_hp_masked' => $nasabah->no_hp !== null ? Str::mask($nasabah->no_hp, '*', 4, -3) : null,
                ]);
            }

            KolektorNasabah::where('nasabah_id', $nasabahId)->delete();
            JadwalKunjungan::where('nasabah_id', $nasabahId)->delete();
            Komplain::where('nasabah_id', $nasabahId)->delete();
            KepesertaanPaket::where('nasabah_id', $nasabahId)->delete();
            LogNotifikasi::where('nasabah_id', $nasabahId)->delete();
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

    public function toggleStatus(int $id): void
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

    public function confirmKonversi(int $profilId): void
    {
        $profil = NasabahProfil::find($profilId);

        if (! $profil) {
            session()->flash('error', 'Nasabah tidak ditemukan. Muat ulang daftar lalu coba lagi.');

            return;
        }

        $this->konversiProfilId = $profil->id;
        $this->konversiNoHp = '';
        $this->tampilKonversiMode = true;
    }

    /**
     * Konversi mode akses nasabah dua arah (D14, §5.3.6):
     * offline -> digital (isi No. HP unik + kirim PIN awal), digital -> offline
     * (hapus sesi + matikan notifikasi). Keduanya dicatat di log aktivitas.
     */
    public function konversiMode(): void
    {
        $profil = NasabahProfil::find($this->konversiProfilId);
        $user = $profil?->user;

        if ($profil === null || ! $user instanceof User) {
            $this->tampilKonversiMode = false;
            $this->konversiProfilId = null;
            session()->flash('error', 'Nasabah tidak ditemukan. Muat ulang daftar lalu coba lagi.');

            return;
        }

        $menujuDigital = $user->isOffline();

        if ($menujuDigital) {
            $this->konversiNoHp = NomorHp::normalize($this->konversiNoHp);

            $this->validate([
                'konversiNoHp' => 'required|string|max:20|unique:users,no_hp|regex:'.NomorHp::PATTERN,
            ], [
                'konversiNoHp.unique' => 'No. HP sudah dipakai oleh pengguna lain.',
            ]);
        }

        $modeLama = $user->mode_akses;
        $modeBaru = $menujuDigital ? 'digital' : 'offline';

        DB::transaction(function () use ($user, $menujuDigital, $modeLama, $modeBaru): void {
            if ($menujuDigital) {
                $user->update([
                    'mode_akses' => 'digital',
                    'no_hp' => $this->konversiNoHp,
                ]);
            } else {
                $user->update([
                    'mode_akses' => 'offline',
                    'notifikasi_wa_aktif' => false,
                ]);

                DB::table('sessions')->where('user_id', $user->id)->delete();
            }

            ActivityLogger::log('ubah_mode_akses', 'users', $user->id, [
                'user_id' => $user->id,
                'mode_lama' => $modeLama,
                'mode_baru' => $modeBaru,
            ]);
        });

        $this->tampilKonversiMode = false;
        $this->konversiProfilId = null;
        $this->konversiNoHp = '';

        if ($menujuDigital) {
            $pin = app(KirimPinAwalAction::class)->buatDanKirim($user);

            session()->flash('success', $pin === null
                ? 'Mode akses diubah ke digital. PIN awal dikirim ke WhatsApp nasabah.'
                : "Mode akses diubah ke digital. PIN awal: {$pin} — catat sekarang karena hanya ditampilkan sekali.");
        } else {
            session()->flash('success', 'Mode akses nasabah diubah ke mode offline. Sesi aktif dihapus dan notifikasi dimatikan.');
        }
    }
}
