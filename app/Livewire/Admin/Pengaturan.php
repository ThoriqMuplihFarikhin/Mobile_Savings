<?php

namespace App\Livewire\Admin;

use App\Livewire\Actions\Logout;
use App\Models\AdminSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

#[Layout('layouts.app')]
class Pengaturan extends Component
{
    public string $namaProdukDefault = '';

    public string $batasToleransiHari = '';

    public string $nomorWaBantuan = '';

    /**
     * Daftar admin lain (untuk kebutuhan manajemen multi-admin).
     */
    public $daftarAdmin = [];

    public function mount(): void
    {
        $this->namaProdukDefault = AdminSetting::get('nama_koperasi', '');
        $this->batasToleransiHari = AdminSetting::get('batas_toleransi_hari', '3');
        $this->nomorWaBantuan = AdminSetting::get('nomor_wa_bantuan', '');

        $this->daftarAdmin = User::where('role', 'admin')->get(['id', 'name', 'no_hp']);
    }

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

        session()->flash('status', 'Konfigurasi sistem berhasil disimpan.');
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
