<?php

namespace App\Livewire\Admin;

use App\Livewire\Actions\Logout;
use App\Livewire\Concerns\AuthorizesRole;
use App\Models\AdminSetting;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;

#[Layout('layouts.app')]
class Pengaturan extends Component
{
    use AuthorizesRole;

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

    public string $nomorWaBantuan = '';

    // Tab 2: Admin & Peran
    public $daftarAdmin = [];

    // Tab 3: Integrasi WhatsApp
    public string $waProvider = '';

    public string $waApiKey = '';

    public bool $waApiKeyTersimpan = false;

    public string $waTestNumber = '';

    // Tab 4: Backup & Keamanan
    public ?string $backupTerakhir = null;

    public string $retensiLogBulan = '12';

    public function mount(): void
    {
        // Tab 1: Umum
        $this->namaProdukDefault = AdminSetting::get('nama_koperasi', '');
        $this->batasToleransiHari = AdminSetting::get('batas_toleransi_hari', '3');
        $this->penarikanMinimal = (string) AdminSetting::get('penarikan_minimal', '10000');
        $this->penarikanBatasDuaApprover = (string) AdminSetting::get('penarikan_batas_dua_approver', '0');
        $this->batasKasKolektor = (string) AdminSetting::get('batas_kas_kolektor', '0');
        $this->batasHariKas = (string) AdminSetting::get('batas_hari_kas', '0');
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
            'nomorWaBantuan' => ['nullable', 'string', 'max:20'],
        ]);

        AdminSetting::set('nama_koperasi', $this->namaProdukDefault);
        AdminSetting::set('batas_toleransi_hari', (string) $this->batasToleransiHari);
        AdminSetting::set('penarikan_minimal', (string) $this->penarikanMinimal);
        AdminSetting::set('penarikan_batas_dua_approver', (string) ((int) $this->penarikanBatasDuaApprover));
        AdminSetting::set('batas_kas_kolektor', (string) ((int) $this->batasKasKolektor));
        AdminSetting::set('batas_hari_kas', (string) ((int) $this->batasHariKas));
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
        $berhasil = $whatsapp->sendNotification($this->waTestNumber, 'Ini pesan uji coba dari sistem Tabungan Digital. Jika Anda menerima ini, integrasi WhatsApp berhasil!');

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

    public function logout(): Redirector|RedirectResponse
    {
        return app(Logout::class)();
    }

    public function render()
    {
        $peringatanDuaApprover = (int) AdminSetting::get('penarikan_batas_dua_approver', '0') > 0
            && User::where('role', 'admin')->count() < 2;

        return view('livewire.admin.pengaturan', compact('peringatanDuaApprover'));
    }
}
