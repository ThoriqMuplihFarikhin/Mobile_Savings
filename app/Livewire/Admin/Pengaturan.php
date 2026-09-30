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

    public string $nomorWaBantuan = '';

    // Tab 2: Admin & Peran
    public $daftarAdmin = [];

    // Tab 3: Integrasi WhatsApp
    public string $waProvider = '';

    public string $waApiKey = '';

    public string $waTestNumber = '';

    // Tab 4: Backup & Keamanan
    public ?string $backupTerakhir = null;

    public string $retensiLogBulan = '12';

    public function mount(): void
    {
        // Tab 1: Umum
        $this->namaProdukDefault = AdminSetting::get('nama_koperasi', '');
        $this->batasToleransiHari = AdminSetting::get('batas_toleransi_hari', '3');
        $this->nomorWaBantuan = AdminSetting::get('nomor_wa_bantuan', '');

        // Tab 2: Admin & Peran
        $this->daftarAdmin = User::where('role', 'admin')->get(['id', 'name', 'no_hp']);

        // Tab 3: WhatsApp
        $this->waProvider = AdminSetting::get('wa_provider', '');
        $this->waApiKey = AdminSetting::get('wa_api_key', '');

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
            'nomorWaBantuan' => ['nullable', 'string', 'max:20'],
        ]);

        AdminSetting::set('nama_koperasi', $this->namaProdukDefault);
        AdminSetting::set('batas_toleransi_hari', (string) $this->batasToleransiHari);
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
        AdminSetting::set('wa_api_key', $this->waApiKey);

        session()->flash('status', 'Konfigurasi WhatsApp berhasil disimpan.');
    }

    public function kirimPesanUjiCoba(): void
    {
        $this->validate(['waTestNumber' => 'required|string']);

        if (empty($this->waApiKey)) {
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
    public function backupSekarang()
    {
        if (! function_exists('exec')) {
            session()->flash('error', 'Fitur backup tidak tersedia di server ini (exec dinonaktifkan).');

            return;
        }

        $filename = 'backup_'.now()->format('Y-m-d_His').'.sql';
        $path = storage_path('app/backups/'.$filename);

        if (! is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $host = config('database.connections.mysql.host');
        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');

        $command = "mysqldump --host={$host} --user={$user} --password={$pass} {$db} > {$path}";
        exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            AdminSetting::set('backup_terakhir', now()->toDateTimeString());
            $this->backupTerakhir = now()->toDateTimeString();
            session()->flash('success', 'Backup berhasil dibuat!');

            return response()->download($path)->deleteFileAfterSend(false);
        } else {
            session()->flash('error', 'Backup gagal. Pastikan mysqldump tersedia di server.');
        }
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
        return view('livewire.admin.pengaturan');
    }
}
