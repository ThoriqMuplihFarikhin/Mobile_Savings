<?php

namespace App\Livewire\Admin;

use App\Actions\Pin\UbahPinAction;
use App\Livewire\Actions\Logout;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\AdminSetting;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportRedirects\Redirector;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;

#[Layout('layouts.admin')]
class Pengaturan extends Component
{
    use AuthorizesRole;
    use WithFileUploads;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public string $activeTab = 'umum';

    // Tab 1: Umum
    public string $namaProdukDefault = '';

    public string $batasToleransiHari = '';

    public string $penarikanMinimal = '';

    public string $penarikanBatasDuaApprover = '0';

    public string $batasKasKolektor = '0';

    public string $batasHariKas = '0';

    public string $izinkanKasMinus = 'false';

    public string $offlineBatasLangsung = '0';

    public string $offlineBatasHarian = '0';

    public string $nomorWaBantuan = '';

    // Tab 2: Admin & Peran
    /** @var Collection<int, User> */
    public $daftarAdmin;

    // Tab 3: Integrasi WhatsApp
    public string $waProvider = '';

    public string $waApiKey = '';

    public bool $waApiKeyTersimpan = false;

    public string $waTestNumber = '';

    // Tab 4: Backup & Keamanan
    public ?string $backupTerakhir = null;

    public string $retensiLogBulan = '12';

    // Tab 5: Akun Saya (Popups)
    public bool $showModalProfil = false;

    public string $profilNama = '';

    public string $profilNoHp = '';

    /** @var TemporaryUploadedFile|null */
    public $profilFoto;

    public bool $showModalPin = false;

    public string $current_pin = '';

    public string $pin = '';

    public string $pin_confirmation = '';

    public bool $showModalTema = false;

    public function mount(): void
    {
        // Tab 1: Umum
        $this->namaProdukDefault = AdminSetting::get('nama_koperasi', '');
        $this->batasToleransiHari = AdminSetting::get('batas_toleransi_hari', '3');
        $this->penarikanMinimal = (string) AdminSetting::get('penarikan_minimal', '10000');
        $this->penarikanBatasDuaApprover = (string) AdminSetting::get('penarikan_batas_dua_approver', '0');
        $this->batasKasKolektor = (string) AdminSetting::get('batas_kas_kolektor', '0');
        $this->batasHariKas = (string) AdminSetting::get('batas_hari_kas', '0');
        $this->izinkanKasMinus = (string) AdminSetting::get('izinkan_kas_minus', 'false');
        $this->offlineBatasLangsung = (string) AdminSetting::get('offline_penarikan_batas_langsung', '0');
        $this->offlineBatasHarian = (string) AdminSetting::get('offline_penarikan_batas_harian', '0');
        $this->nomorWaBantuan = AdminSetting::get('nomor_wa_bantuan', '');

        // Tab 2: Admin & Peran
        $this->daftarAdmin = User::where('role', 'admin')->get(['id', 'name', 'no_hp']);

        // Tab 3: WhatsApp
        $this->waProvider = AdminSetting::get('wa_provider', '');
        $this->waApiKey = '';
        $this->waApiKeyTersimpan = (bool) AdminSetting::get('wa_api_key');

        // Tab 4: Backup & Keamanan
        $this->backupTerakhir = AdminSetting::get('backup_terakhir');
        $this->retensiLogBulan = AdminSetting::get('retensi_log_bulan', '12');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // Tab 1: Umum
    public function simpanKonfigurasi(): void
    {
        $this->validate([
            'namaProdukDefault' => ['nullable', 'string', 'max:100'],
            'batasToleransiHari' => ['nullable', 'integer', 'min:0', 'max:30'],
            'penarikanMinimal' => ['required', 'integer', 'min:0', 'max:9999999999999'],
            'penarikanBatasDuaApprover' => ['required', 'integer', 'min:0', 'max:9999999999999'],
            'batasKasKolektor' => ['nullable', 'integer', 'min:0', 'max:9999999999999'],
            'batasHariKas' => ['nullable', 'integer', 'min:0', 'max:365'],
            'izinkanKasMinus' => ['required', 'in:true,false'],
            'offlineBatasLangsung' => ['required', 'integer', 'min:0', 'max:9999999999999'],
            'offlineBatasHarian' => ['required', 'integer', 'min:0', 'max:9999999999999'],
            'nomorWaBantuan' => ['nullable', 'string', 'max:20'],
        ]);

        AdminSetting::set('nama_koperasi', $this->namaProdukDefault);
        AdminSetting::set('batas_toleransi_hari', (string) $this->batasToleransiHari);
        AdminSetting::set('penarikan_minimal', (string) $this->penarikanMinimal);
        AdminSetting::set('penarikan_batas_dua_approver', (string) ((int) $this->penarikanBatasDuaApprover));
        AdminSetting::set('batas_kas_kolektor', (string) ((int) $this->batasKasKolektor));
        AdminSetting::set('batas_hari_kas', (string) ((int) $this->batasHariKas));
        AdminSetting::set('izinkan_kas_minus', $this->izinkanKasMinus === 'true' ? 'true' : 'false');
        AdminSetting::set('offline_penarikan_batas_langsung', (string) ((int) $this->offlineBatasLangsung));
        AdminSetting::set('offline_penarikan_batas_harian', (string) ((int) $this->offlineBatasHarian));
        AdminSetting::set('nomor_wa_bantuan', $this->nomorWaBantuan);

        session()->flash('status', 'Konfigurasi umum berhasil disimpan.');
    }

    // Tab 3: Integrasi WhatsApp
    public function simpanWhatsApp(): void
    {
        $this->validate([
            'waProvider' => ['required', 'string', 'in:fonnte,wablas,official'],
            'waApiKey' => ['nullable', 'string', 'max:255'],
        ]);

        AdminSetting::set('wa_provider', $this->waProvider);

        if ($this->waApiKey !== '') {
            AdminSetting::set('wa_api_key', $this->waApiKey);
        }

        $this->waApiKey = '';
        $this->waApiKeyTersimpan = (bool) AdminSetting::get('wa_api_key');

        session()->flash('status', 'Konfigurasi WhatsApp berhasil disimpan.');
    }

    public function kirimPesanUjiCoba(): void
    {
        $this->validate(['waTestNumber' => 'required|string']);

        if (! AdminSetting::get('wa_api_key')) {
            session()->flash('error', 'Isi dan simpan API Key terlebih dahulu sebelum uji coba.');

            return;
        }

        $whatsapp = app(WhatsAppService::class);
        $berhasil = $whatsapp->sendNotification($this->waTestNumber, 'Ini pesan uji coba dari sistem KawalDuit. Jika Anda menerima ini, integrasi WhatsApp berhasil!');

        if ($berhasil) {
            session()->flash('success', 'Pesan uji coba berhasil dikirim! Cek WhatsApp nomor tersebut.');
        } else {
            session()->flash('error', 'Gagal mengirim pesan uji coba. Periksa kembali API Key dan koneksi.');
        }
    }

    // Tab 4: Backup & Keamanan
    public function backupSekarang(): ?BinaryFileResponse
    {
        if (config('database.default') !== 'mysql') {
            session()->flash('error', 'Backup otomatis hanya mendukung database MySQL. Untuk SQLite, salin file database secara manual.');

            return null;
        }

        if (! function_exists('proc_open')) {
            session()->flash('error', 'Fitur backup tidak tersedia di server ini (proc_open dinonaktifkan).');

            return null;
        }

        $filename = 'backup_'.now()->format('Y-m-d_His').'.sql';
        $directory = storage_path('app/private/backups');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $path = $directory.DIRECTORY_SEPARATOR.$filename;

        $process = $this->buildMysqldumpProcess($path);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            if (is_file($path)) {
                unlink($path);
            }
            report(new \RuntimeException('mysqldump gagal: '.trim($process->getErrorOutput())));

            session()->flash('error', 'Backup gagal. Pastikan mysqldump tersedia di server.');

            return null;
        }

        AdminSetting::set('backup_terakhir', now()->toDateTimeString());
        $this->backupTerakhir = now()->toDateTimeString();
        session()->flash('success', 'Backup berhasil dibuat!');

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Susun argumen mysqldump tanpa password — kata sandi hanya lewat env MYSQL_PWD.
     *
     * @return list<string>
     */
    protected function mysqldumpArguments(string $resultPath): array
    {
        $connection = (array) config('database.connections.mysql', []);

        return [
            'mysqldump',
            '--host='.($connection['host'] ?? '127.0.0.1'),
            '--port='.($connection['port'] ?? 3306),
            '--user='.($connection['username'] ?? ''),
            '--single-transaction',
            '--result-file='.$resultPath,
            (string) ($connection['database'] ?? ''),
        ];
    }

    protected function buildMysqldumpProcess(string $resultPath): Process
    {
        $connection = (array) config('database.connections.mysql', []);

        return new Process(
            $this->mysqldumpArguments($resultPath),
            null,
            ['MYSQL_PWD' => (string) ($connection['password'] ?? '')]
        );
    }

    public function simpanRetensiLog(): void
    {
        $this->validate([
            'retensiLogBulan' => ['required', 'string', 'in:3,6,12,24'],
        ]);

        AdminSetting::set('retensi_log_bulan', $this->retensiLogBulan);

        session()->flash('status', 'Retensi log berhasil disimpan.');
    }

    // Tab 5: Akun Saya (Popups)
    public function bukaModalProfil(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $this->profilNama = $user->name;
        $this->profilNoHp = $user->no_hp ?? '';
        $this->profilFoto = null;
        $this->resetValidation();
        $this->showModalProfil = true;
    }

    public function tutupModalProfil(): void
    {
        $this->showModalProfil = false;
        $this->profilFoto = null;
        $this->resetValidation();
    }

    public function simpanProfil(): void
    {
        $validated = $this->validate([
            'profilNama' => ['required', 'string', 'max:255'],
            'profilFoto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->update(['name' => $validated['profilNama']]);

        if ($this->profilFoto !== null) {
            $path = $this->profilFoto->store('profil', 'public');
            $user->update(['foto_profil_path' => $path]);
        }

        $this->showModalProfil = false;
        $this->profilFoto = null;
        session()->flash('status', 'Profil berhasil diperbarui.');
    }

    public function bukaModalPin(): void
    {
        $this->reset('current_pin', 'pin', 'pin_confirmation');
        $this->resetValidation();
        $this->showModalPin = true;
    }

    public function tutupModalPin(): void
    {
        $this->reset('current_pin', 'pin', 'pin_confirmation');
        $this->resetValidation();
        $this->showModalPin = false;
    }

    public function simpanPin(): void
    {
        try {
            $validated = $this->validate([
                'current_pin' => ['required', 'string', 'digits:6'],
                'pin' => ['required', 'string', 'digits:6', 'confirmed'],
            ]);

            /** @var User $user */
            $user = Auth::user();

            app(UbahPinAction::class)->execute(
                $user,
                $validated['current_pin'],
                $validated['pin'],
            );
        } catch (ValidationException $e) {
            $this->reset('current_pin', 'pin', 'pin_confirmation');

            throw $e;
        }

        $this->reset('current_pin', 'pin', 'pin_confirmation');
        $this->showModalPin = false;

        session()->flash('status', 'PIN berhasil diperbarui.');
    }

    public function bukaModalTema(): void
    {
        $this->showModalTema = true;
    }

    public function tutupModalTema(): void
    {
        $this->showModalTema = false;
    }

    public function logout(): Redirector|RedirectResponse
    {
        return app(Logout::class)();
    }

    public function render(): View
    {
        $peringatanDuaApprover = (int) AdminSetting::get('penarikan_batas_dua_approver', '0') > 0
            && User::where('role', 'admin')->count() < ApprovalPenarikan::MINIMAL_ADMIN_PERSETUJUAN_GANDA;

        // D23: penarikan langsung tanpa ambang dua approver aktif (atau admin
        // kurang) berarti transaksi besar bisa selesai tanpa pengawasan ganda.
        $peringatanOfflineLangsung = (int) AdminSetting::get('offline_penarikan_batas_langsung', '0') > 0
            && ((int) AdminSetting::get('penarikan_batas_dua_approver', '0') <= 0
                || User::where('role', 'admin')->count() < ApprovalPenarikan::MINIMAL_ADMIN_PERSETUJUAN_GANDA);

        return view('livewire.admin.pengaturan', compact('peringatanDuaApprover', 'peringatanOfflineLangsung'));
    }
}
