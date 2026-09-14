# Perbaikan Duplikasi Halaman Pengaturan Admin

## Akar Masalah (sudah saya audit langsung ke kode kamu)

Ternyata implementasi sebelumnya **cuma diterapkan separuh**. Ini yang terjadi:

| Yang sudah ada | Yang HILANG/salah |
|---|---|
| ✅ `AdminSetting.php` (model) | |
| ✅ Migration `admin_settings` | |
| ✅ `Admin\Pengaturan.php` (Livewire) | ❌ View-nya masih link ke `profile.edit`/`security.edit` (shared) — bukan yang khusus admin |
| ✅ `livewire/admin/pengaturan.blade.php` (view) | ❌ **Tidak pernah dipanggil!** |

**Akibatnya ada 2 sistem "Pengaturan Admin" yang hidup berdampingan:**

1. **`PengaturanController::index()`** → return `view('pages.admin.pengaturan')` → ini adalah **file statis lama**, isinya hardcoded: profile card, link ke `profile.edit`/`security.edit`/`appearance.edit` (shared — padahal ini yang harusnya diblokir untuk admin), link aneh "Kelola Data" ke halaman Nasabah, dan link Bantuan WA.
2. **`Admin\Pengaturan.php` + `livewire/admin/pengaturan.blade.php`** → komponen yang punya form konfigurasi sistem + daftar admin (lebih lengkap & benar), **tapi tidak pernah dirender** karena controller tidak mengarah ke sini.

Selain itu:
- `routes/web.php` **tidak punya** route `/admin/settings/*` sama sekali.
- `routes/settings.php` masih shared untuk semua role (belum dibatasi `role:kolektor|nasabah`).
- 3 file `⚡profile/security/appearance.blade.php` masih punya logic `isAdmin()` (harusnya sudah dihapus).
- `desktop-user-menu.blade.php` masih versi lama: "Settings" → `profile.edit` (shared), bukan settings khusus admin.

**Kesimpulan: bukan cuma duplikat, tapi kode yang benar sama sekali tidak nyambung/dead code.** Berikut perbaikan lengkapnya.

---

## FASE 1 — Hapus duplikat: sambungkan controller ke Livewire yang benar

### 1a. `resources/views/pages/admin/pengaturan.blade.php` — GANTI SELURUH ISI
Hapus semua HTML statis lama, ganti jadi cuma pemanggil Livewire:
```blade
<x-layouts::app title="Pengaturan Sistem"><livewire:admin.pengaturan /></x-layouts::app>
```
*(Ini menghapus duplikat #1 — link "Kelola Data" yang aneh, profile card ganda, dan link ke shared settings yang salah, semuanya hilang karena file ini sekarang cuma 1 baris.)*


## FASE 2 — Buat `/admin/settings/*` yang memang belum ada

### 2a. `app/Http/Controllers/Admin/SettingsController.php` — FILE BARU
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function profile()
    {
        return view('pages.admin.settings.profile');
    }

    public function security()
    {
        return view('pages.admin.settings.security');
    }

    public function appearance()
    {
        return view('pages.admin.settings.appearance');
    }
}
```

### 2b. `routes/web.php` — TAMBAH import & route
Cari baris import controller admin, tambahkan:
```php
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
```

Cari blok:
```php
        Route::get('/pengaturan', [AdminPengaturanController::class, 'index'])->name('pengaturan.index');
    });
```
Ganti dengan:
```php
        Route::get('/pengaturan', [AdminPengaturanController::class, 'index'])->name('pengaturan.index');

        // Settings khusus admin - terpisah total dari settings shared kolektor/nasabah
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/profile', [AdminSettingsController::class, 'profile'])->name('profile');
            Route::get('/security', [AdminSettingsController::class, 'security'])->name('security');
            Route::get('/appearance', [AdminSettingsController::class, 'appearance'])->name('appearance');
        });
    });
```

### 2c. `app/Livewire/Admin/Settings/Profile.php` — FILE BARU
```php
<?php

namespace App\Livewire\Admin\Settings;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Profile extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $no_hp = '';

    public $fotoBaru;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->no_hp = $user->no_hp;
    }

    public function updatedFotoBaru(): void
    {
        $this->validate(['fotoBaru' => 'image|max:2048']);

        $path = $this->fotoBaru->store('profil', 'public');
        Auth::user()->update(['foto_profil_path' => $path]);

        Flux::toast(variant: 'success', text: 'Foto profil berhasil diubah!');
    }

    public function updateProfileInformation(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Auth::user()->update(['name' => $validated['name']]);

        Flux::toast(variant: 'success', text: 'Profil berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.admin.settings.profile');
    }
}
```

### 2d. `app/Livewire/Admin/Settings/Security.php` — FILE BARU
```php
<?php

namespace App\Livewire\Admin\Settings;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Security extends Component
{
    public string $current_pin = '';

    public string $pin = '';

    public string $pin_confirmation = '';

    public function updatePin(): void
    {
        try {
            $validated = $this->validate([
                'current_pin' => ['required', 'string', function ($attribute, $value, $fail) {
                    if (! Hash::check($value, Auth::user()->pin_hash)) {
                        $fail(__('PIN saat ini tidak sesuai.'));
                    }
                }],
                'pin' => ['required', 'string', 'digits:6', 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_pin', 'pin', 'pin_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'pin_hash' => Hash::make($validated['pin']),
        ]);

        $this->reset('current_pin', 'pin', 'pin_confirmation');

        Flux::toast(variant: 'success', text: 'PIN berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.admin.settings.security');
    }
}
```

### 2e. `app/Livewire/Admin/Settings/Appearance.php` — FILE BARU
```php
<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Appearance extends Component
{
    public function render()
    {
        return view('livewire.admin.settings.appearance');
    }
}
```

### 2f–2h. Views Livewire — FILE BARU

**`resources/views/livewire/admin/settings/profile.blade.php`**
```blade
<div class="mx-auto max-w-2xl">
    <flux:button :href="route('admin.pengaturan.index')" wire:navigate variant="ghost" icon="arrow-left" class="mb-4">
        Kembali ke Pengaturan Sistem
    </flux:button>

    <flux:heading size="xl">Edit Profil</flux:heading>
    <flux:subheading class="mb-6">Perbarui nama dan foto profil akun admin Anda.</flux:subheading>

    <flux:card class="space-y-5">
        <div class="flex items-center gap-4">
            @if (auth()->user()->foto_profil_path)
                <img src="{{ asset('storage/'.auth()->user()->foto_profil_path) }}" alt="Foto Profil"
                     class="h-16 w-16 rounded-full object-cover">
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-800 text-lg font-semibold text-white dark:bg-zinc-700">
                    {{ auth()->user()->initials() }}
                </div>
            @endif

            <flux:button size="sm" variant="ghost" as="label">
                Ganti Foto
                <input type="file" wire:model="fotoBaru" accept="image/*" class="hidden">
            </flux:button>
        </div>

        <form wire:submit="updateProfileInformation" class="space-y-4">
            <flux:input wire:model="name" label="Nama" required />

            <flux:input :value="$no_hp" label="No. HP" disabled readonly />
            <flux:text size="sm" class="text-zinc-500">No. HP tidak dapat diubah karena digunakan untuk login.</flux:text>

            <flux:button type="submit" variant="primary">Simpan Perubahan</flux:button>
        </form>
    </flux:card>
</div>
```

**`resources/views/livewire/admin/settings/security.blade.php`**
```blade
<div class="mx-auto max-w-2xl">
    <flux:button :href="route('admin.pengaturan.index')" wire:navigate variant="ghost" icon="arrow-left" class="mb-4">
        Kembali ke Pengaturan Sistem
    </flux:button>

    <flux:heading size="xl">Ganti PIN</flux:heading>
    <flux:subheading class="mb-6">Pastikan PIN Anda aman dan mudah diingat.</flux:subheading>

    <flux:card>
        <form wire:submit="updatePin" class="space-y-4">
            <flux:input type="password" wire:model="current_pin" label="PIN Saat Ini" maxlength="6" autocomplete="off" />
            <flux:input type="password" wire:model="pin" label="PIN Baru" maxlength="6" autocomplete="off" />
            <flux:input type="password" wire:model="pin_confirmation" label="Konfirmasi PIN Baru" maxlength="6" autocomplete="off" />
            <flux:button type="submit" variant="primary">Update PIN</flux:button>
        </form>
    </flux:card>
</div>
```

**`resources/views/livewire/admin/settings/appearance.blade.php`**
```blade
<div class="mx-auto max-w-2xl">
    <flux:button :href="route('admin.pengaturan.index')" wire:navigate variant="ghost" icon="arrow-left" class="mb-4">
        Kembali ke Pengaturan Sistem
    </flux:button>

    <flux:heading size="xl">Tampilan</flux:heading>
    <flux:subheading class="mb-6">Atur tema tampilan untuk akun admin Anda.</flux:subheading>

    <flux:card>
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">Terang</flux:radio>
            <flux:radio value="dark" icon="moon">Gelap</flux:radio>
            <flux:radio value="system" icon="computer-desktop">Sistem</flux:radio>
        </flux:radio.group>
    </flux:card>
</div>
```

### 2i–2k. Page wrapper — FILE BARU

**`resources/views/pages/admin/settings/profile.blade.php`**
```blade
<x-layouts::app title="Edit Profil"><livewire:admin.settings.profile /></x-layouts::app>
```
**`resources/views/pages/admin/settings/security.blade.php`**
```blade
<x-layouts::app title="Ganti PIN"><livewire:admin.settings.security /></x-layouts::app>
```
**`resources/views/pages/admin/settings/appearance.blade.php`**
```blade
<x-layouts::app title="Tampilan"><livewire:admin.settings.appearance /></x-layouts::app>
```

## FASE 3 — Perbaiki link yang masih salah arah

### 3a. `resources/views/livewire/admin/pengaturan.blade.php` — GANTI 2 BARIS INI
Cari:
```blade
            <flux:button :href="route('profile.edit')" wire:navigate variant="ghost">Edit Profil</flux:button>
            <flux:button :href="route('security.edit')" wire:navigate variant="ghost">Ganti PIN</flux:button>
```
Ganti dengan:
```blade
            <flux:button :href="route('admin.settings.profile')" wire:navigate variant="ghost">Edit Profil</flux:button>
            <flux:button :href="route('admin.settings.security')" wire:navigate variant="ghost">Ganti PIN</flux:button>
            <flux:button :href="route('admin.settings.appearance')" wire:navigate variant="ghost">Tampilan</flux:button>
```
*(Sekaligus menambah shortcut Tampilan yang sebelumnya belum ada — sekarang cocok dengan tombol Logout yang sudah ada di baris setelahnya.)*

### 3b. `resources/views/components/desktop-user-menu.blade.php` — GANTI SELURUH ISI
Komponen ini **hanya dipakai admin** (dipanggil dari `layouts/app/header.blade.php` & `sidebar.blade.php`, dan cuma halaman admin yang pakai `layouts.app`). Jadi aman hapus pengecekan `isAdmin()` dan arahkan langsung ke settings admin:

```blade
<flux:dropdown position="bottom" align="start">
    <flux:sidebar.profile
        :name="auth()->user()->name"
        :initials="auth()->user()->initials()"
        icon:trailing="chevrons-up-down"
        data-test="sidebar-menu-button"
    />

    <flux:menu>
        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
            <flux:avatar
                :name="auth()->user()->name"
                :initials="auth()->user()->initials()"
            />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
            </div>
        </div>
        <flux:menu.separator />
        <flux:menu.radio.group>
            <flux:menu.item :href="route('admin.settings.profile')" icon="settings" wire:navigate>
                {{ __('Settings') }}
            </flux:menu.item>
            <flux:menu.item :href="route('admin.pengaturan.index')" icon="cog" wire:navigate>
                {{ __('Pengaturan Sistem') }}
            </flux:menu.item>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item
                    as="button"
                    type="submit"
                    class="w-full cursor-pointer"
                    data-test="logout-button"
                >
                    {{ __('Log out') }}
                </flux:menu.item>
            </form>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
```

### 3c. `routes/settings.php` — GANTI SELURUH ISI
Batasi shared settings jadi eksklusif kolektor/nasabah (admin sekarang punya jalur sendiri di Fase 2):
```php
<?php

use Illuminate\Support\Facades\Route;

// Settings shared ini khusus untuk kolektor & nasabah.
// Admin punya settings sendiri di /admin/settings/* (lihat routes/web.php).
Route::middleware(['auth', 'active', 'role:kolektor|nasabah'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');
});

Route::middleware(['auth', 'active', 'verified', 'role:kolektor|nasabah'])->group(function () {
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');

    Route::livewire('settings/security', 'pages::settings.security')
        ->name('security.edit');
});
```

### 3d. 3 file `⚡profile/security/appearance.blade.php` — HAPUS logic `isAdmin()`

**`resources/views/pages/settings/⚡profile.blade.php`** — cari:
```php
            ->layout(auth()->user()->isAdmin() ? 'layouts.app' : 'layouts.mobile');
```
ganti:
```php
            ->layout('layouts.mobile');
```

**`resources/views/pages/settings/⚡security.blade.php`** — cari baris yang sama, ganti sama.

**`resources/views/pages/settings/⚡appearance.blade.php`** — cari baris yang sama, ganti sama.

*(Aman dihapus karena admin sekarang tidak akan pernah lagi sampai ke halaman ini — sudah diblokir middleware `role:kolektor|nasabah` di 3c.)*

## Urutan Penerapan

1. **Fase 2 dulu** (buat semua file baru: controller, 3 Livewire component, 6 view) — kalau langsung mulai dari Fase 1/3, akan ada route/view yang dipanggil tapi belum ada (error 500).
2. **Fase 3** (perbaiki link & restrict route).
3. **Fase 1 terakhir** (ganti page wrapper jadi cuma `<livewire:admin.pengaturan />`) — ini yang benar-benar "menghapus" duplikat, jadi paling aman dilakukan setelah semua link tujuan sudah benar.
4. Jalankan:
   ```bash
   php artisan test --filter=Settings   # kalau sudah ada test dari implementasi sebelumnya
   ```
5. Cek manual:
   - `/admin/pengaturan` → harus tampil form konfigurasi + daftar admin (BUKAN lagi profile card + link "Kelola Data").
   - Klik "Edit Profil" dari situ → harus ke `/admin/settings/profile` (bukan `/settings/profile`).
   - Coba akses `/settings/profile` langsung sebagai admin → harus 403.
   - Kolektor/nasabah tetap bisa akses `/settings/profile` seperti biasa.

## Yang TIDAK Diubah (supaya jelas scope-nya)

- `App\Livewire\Admin\Pengaturan.php` (komponen & isinya) — **tidak diubah**, sudah benar, cuma view-nya yang salah link (sudah diperbaiki di 3a).
- `AdminSetting.php` & migration — sudah benar, tidak disentuh.
- Halaman lain di admin (Nasabah, Produk, Rekonsiliasi, dst.) — di luar scope permintaan ini, tidak disentuh.

## Catatan Tambahan b

Saya sempat lihat project kamu **sudah punya** halaman "Log Aktivitas" (`/admin/log`, `LogController`, `Admin\LogAktivitas` Livewire) — jadi item Tier 3 di `PLAN-FITUR-TAMBAHAN.md` sebelumnya soal "Log Aktivitas" **sudah tidak perlu dikerjakan lagi**, kemungkinan sudah ada duluan. Kalau nanti mau saya audit juga apakah halaman itu sudah lengkap atau ada bug serupa, tinggal bilang.