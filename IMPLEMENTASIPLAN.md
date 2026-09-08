# Implementation Plan: Pemisahan Pengaturan (Settings) — Admin / Kolektor / Nasabah

> Proyek: `tabungan-digital` (Laravel + Livewire Flux + Spatie Permission)
> Tujuan: memisahkan halaman & logic "Pengaturan" secara bersih per role, tanpa duplikasi kode, dan menutup akses lintas-role.

## 0. Temuan Audit Awal (baseline)

| Area | Status saat ini |
|---|---|
| Route `/admin/*`, `/kolektor/*`, `/nasabah/*` | ✅ Sudah dikawal `middleware('role:admin')` dst. |
| `routes/settings.php` (`/settings/profile`, `/security`, `/appearance`) | ❌ Hanya `middleware(['auth'])`, tidak ada pembatasan role — semua role bisa akses & saling "menumpang" di halaman yang sama |
| `App\Livewire\Kolektor\Pengaturan` vs `App\Livewire\Nasabah\Pengaturan` | ❌ Kode identik (toggle `notifikasi_wa_aktif` + logout) → duplikasi |
| Admin | ❌ Tidak punya halaman Pengaturan sendiri, otomatis "numpang" ke settings generic lewat `desktop-user-menu.blade.php` |
| Test | ❌ Belum ada test cross-role untuk halaman settings |

## 1. Target Arsitektur

```
/settings/profile     -> shared (semua role boleh, tapi field ditentukan per role)
/settings/security     -> shared (ganti PIN/password, semua role)
/settings/appearance   -> shared (tema, semua role)

/admin/pengaturan       -> khusus admin (setting sistem, bukan cuma profil)
/kolektor/pengaturan    -> khusus kolektor (WA notif, jam kerja, dst)
/nasabah/pengaturan     -> khusus nasabah (WA notif, preferensi notifikasi)
```

Prinsip:
- **Shared settings** (profile/security/appearance) tetap satu route, tapi kontennya *role-aware* (field yang tampil beda tergantung `auth()->user()->role`), bukan dihapus/diduplikasi.
- **Role-specific settings** (pengaturan operasional) tetap terpisah per role, tapi logic yang sama (toggle notifikasi WA) ditarik ke satu trait/service supaya tidak dobel.
- Setiap grup route wajib middleware `role:<nama_role>` yang eksplisit — no more "unguarded" settings.

## 2. Langkah Implementasi (urutan untuk agent)

### Fase 1 — Kunci celah akses (paling kritis, kerjakan duluan)
1. Buat middleware group baru di `routes/settings.php`, pisahkan jadi 3 grup:
   ```php
   Route::middleware(['auth', 'active'])->group(function () {
       Route::redirect('settings', 'settings/profile');
       Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');
   });

   Route::middleware(['auth', 'active', 'verified'])->group(function () {
       Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');
       Route::livewire('settings/security', 'pages::settings.security')->name('security.edit');
   });
   ```
   (tambahkan `active` yang sebelumnya juga tidak dipakai di sini — konsisten dengan `web.php`)
2. Di dalam komponen `⚡profile.blade.php` / `Profile` Livewire, tampilkan field berbeda berdasar `auth()->user()->role` (mis. nasabah lihat "Nomor HP", kolektor lihat "Wilayah tugas", admin lihat field minimal). **Jangan** pecah jadi 3 route berbeda kalau isinya 90% sama — cukup 1 view dengan blok kondisional.

### Fase 2 — Hilangkan duplikasi Kolektor/Nasabah Pengaturan
3. Buat trait `App\Livewire\Concerns\HasNotifikasiWaToggle`:
   ```php
   trait HasNotifikasiWaToggle
   {
       public bool $notifikasiWaAktif = true;

       public function mountNotifikasiWaToggle(): void
       {
           $this->notifikasiWaAktif = Auth::user()->notifikasi_wa_aktif ?? true;
       }

       public function toggleNotifikasiWa(): void
       {
           $this->notifikasiWaAktif = ! $this->notifikasiWaAktif;
           Auth::user()->update(['notifikasi_wa_aktif' => $this->notifikasiWaAktif]);
       }
   }
   ```
4. Refactor `App\Livewire\Kolektor\Pengaturan` dan `App\Livewire\Nasabah\Pengaturan` supaya `use HasNotifikasiWaToggle;` — hapus method yang duplikat. Method `logout()` bisa juga ditarik ke trait/action `App\Livewire\Actions\Logout` yang sudah ada di project (cek `app/Livewire/Actions/Logout.php`, kemungkinan besar sudah bisa dipakai ulang).
5. Tambah field spesifik role di masing-masing:
   - `Kolektor\Pengaturan`: tambahkan setting relevan kolektor, misalnya lihat status handover kas (`hasUnsettledCash()` sudah ada di model `User` — tampilkan sebagai warning di halaman pengaturan kolektor).
   - `Nasabah\Pengaturan`: tambahkan link ke `profil.index` (data pribadi nasabah) supaya nasabah tidak perlu ke `/settings/profile` yang desktop-oriented.

### Fase 3 — Buat halaman Pengaturan khusus Admin
6. Buat `App\Http\Controllers\Admin\PengaturanController` + route:
   ```php
   Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
   ```
   di dalam grup `prefix('admin')->middleware('role:admin')` yang sudah ada di `routes/web.php`.
7. Isi minimal halaman admin pengaturan (sesuaikan kebutuhan bisnis, contoh):
   - Kelola daftar admin lain (jika multi-admin)
   - Konfigurasi umum (nama koperasi/produk default, threshold rekonsiliasi, dll — cek apakah sudah ada tabel `config`/`settings` di migrations; jika belum ada, buat migration `admin_settings` key-value sederhana)
   - Link ke `/settings/security` untuk ganti PIN admin sendiri (reuse, jangan duplikasi)

### Fase 4 — Konsistenkan navigasi
8. Update `desktop-user-menu.blade.php`: menu "Settings" tetap ke `profile.edit`, tapi tambahkan menu terpisah "Pengaturan Sistem" → `admin.pengaturan.index` **hanya render jika** `auth()->user()->isAdmin()`.
9. Pastikan mobile layout (kolektor/nasabah) link "Pengaturan" tetap ke route masing-masing (`kolektor.pengaturan.index` / `nasabah.pengaturan.index`) — tidak berubah, cuma pastikan tidak ada link tersisa ke `/settings/*` yang generic di navigasi mobile (biar UX tidak campur).

### Fase 5 — Test & guard
10. Tambah test baru `tests/Feature/SettingsAccessTest.php`:
    - Nasabah/kolektor **tidak bisa** GET `/admin/pengaturan` → 403.
    - Admin **tidak bisa** GET `/kolektor/pengaturan` atau `/nasabah/pengaturan` → 403.
    - Ketiga role **bisa** GET `/settings/profile`, `/settings/security`, `/settings/appearance` → 200.
    - Toggle `notifikasi_wa_aktif` lewat masing-masing Livewire component tetap berfungsi (regression test, pola sama seperti test lama di `Kolektor\Pengaturan`/`Nasabah\Pengaturan` sebelum refactor).
11. Jalankan `php artisan test --filter=Settings` dan `php artisan test --filter=AccessControl` untuk pastikan tidak ada regresi pada test lama.

## 3. File yang Disentuh (checklist ringkas untuk agent)

- [ ] `routes/settings.php` — tambah `active` middleware
- [ ] `routes/web.php` — tambah route `admin.pengaturan.index`
- [ ] `app/Livewire/Concerns/HasNotifikasiWaToggle.php` — baru
- [ ] `app/Livewire/Kolektor/Pengaturan.php` — refactor pakai trait
- [ ] `app/Livewire/Nasabah/Pengaturan.php` — refactor pakai trait
- [ ] `app/Http/Controllers/Admin/PengaturanController.php` — baru
- [ ] `resources/views/pages/admin/pengaturan.blade.php` — baru
- [ ] `resources/views/components/desktop-user-menu.blade.php` — tambah menu conditional admin
- [ ] `tests/Feature/SettingsAccessTest.php` — baru

## 4. Catatan Penting
- Jangan hapus kolom `notifikasi_wa_aktif` di tabel `users` — dipakai bersama, cukup akses lewat trait.
- Semua perubahan route wajib dites terhadap `role:admin|kolektor|nasabah` middleware yang sudah eksis (`Spatie\Permission\Middleware\RoleMiddleware`, alias `role` di `bootstrap/app.php`) — jangan buat middleware baru kalau yang lama sudah cukup.
- Kerjakan Fase 1 lebih dulu karena itu menutup lubang keamanan (akses settings tanpa batasan role) yang paling berisiko.