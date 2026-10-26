# Audit Working Tree (Analisis 49+ Berkas Sebelum Fase 4)

> Dokumen ini disusun sesuai instruksi Langkah Pertama sebelum memulai Fase 4 (Redesain Portal Kolektor).  
> Branch: `feat/ui-redesign-2026-10`  
> Baseline Komit: `1abf0fa` (feat(U3.12): redesain pengaturan & profil nasabah)  

---

## 1. Klasifikasi Berkas yang Berubah di Working Tree

| No | Berkas | Jenis Perubahan | Kategori (Redesain / Non-Redesain) | Deskripsi & Asal Usul | Risiko & Catatan |
|---|---|---|---|---|---|
| 1 | `app/Actions/Penarikan/BatalkanPenarikanAction.php` | Modifikasi | **Non-Redesain (Logika Uang)** | Memungkinkan admin membatalkan penarikan berstatus `approved` dengan mengembalikan saldo nasabah | **Risiko Tinggi**: Bila kas fisik sudah diserahkan kolektor ke nasabah di lapangan, pembatalan sistem berisiko *double payout*. Perubahan ini menyentuh uang. |
| 2 | `app/Jobs/KirimNotifikasiWhatsApp.php` | Modifikasi | **Non-Redesain (Logika/Job)** | Job pengiriman notifikasi antrian WA asinkron | Risiko Rendah: Bergantung pada queue worker dan gateway WA. |
| 3 | `app/Services/WhatsAppService.php` | Modifikasi | **Non-Redesain (Service)** | Logika pengiriman pesan WA | Risiko Rendah: Tidak menyentuh kas/keuangan langsung. |
| 4 | `app/Livewire/Admin/ApprovalPenarikan.php` | Modifikasi | **Non-Redesain (Admin)** | Menambah tombol dan method pembatalan untuk penarikan approved | Terkait butir 1. Menyentuh otorisasi admin. |
| 5 | `resources/views/components/admin/*` | Modifikasi | **Non-Redesain (Admin)** | Penyesuaian navigasi admin sidebar dan item menu | Risiko Rendah: Khusus portal admin desktop. |
| 6 | `resources/views/livewire/admin/*` | Modifikasi | **Non-Redesain (Admin)** | Perbaikan tampilan admin (approval, dashboard, laporan, manajemen nasabah & produk, pengaturan) | Risiko Rendah: Tidak merusak UI nasabah/kolektor. |
| 7 | `resources/views/layouts/app/admin-bottom-nav.blade.php` | Modifikasi | **Non-Redesain (Admin)** | Bottom nav responsif admin layar kecil (P7.2) | Risiko Rendah. |
| 8 | `resources/views/pages/admin/log.blade.php` | Modifikasi | **Non-Redesain (Admin)** | Halaman log aktivitas admin | Risiko Rendah. |
| 9 | `routes/web.php` & `routes/settings.php` | Modifikasi | **Non-Redesain (Routing)** | Pembersihan route login cek-nama dan rute settings | Risiko Rendah: Menutup celah kebocoran nama (PII leak). |
| 10 | `CLAUDE.md` & `IMPLEMENTASI_PLAN.md` | Dihapus di WT | **Non-Redesain (Dokumentasi/Config)** | Berkas terhapus di working tree | `CLAUDE.md` terbukti masih utuh di riwayat git (`git show HEAD:CLAUDE.md`). Jangan dibuang permanen tanpa izin. |
| 11 | `resources/views/layouts/app/header.blade.php`, `sidebar.blade.php`, `auth/card.blade.php`, `auth/split.blade.php` | Dihapus di WT | **Non-Redesain (Pembersihan Boilerplate)** | Template lama bawaan starter kit yang digantikan oleh `layouts/mobile.blade.php` dan `layouts/auth/portal.blade.php` | Risiko Rendah: Tidak lagi di-import oleh view aktif. |
| 12 | `resources/views/pages/settings/*`, `partials/settings-heading` | Dihapus di WT | **Non-Redesain (Pembersihan Boilerplate)** | View settings lama bawaan starter kit Breeze/Volt | Sudah digantikan oleh `livewire/nasabah/pengaturan.blade.php` dan `livewire/kolektor/pengaturan.blade.php`. |
| 13 | `resources/views/components/auth/pin-pad.blade.php` | Dihapus di WT | **Redesain (Pembersihan Duplikat)** | Berkas duplikat dari `components/ui/pin-pad.blade.php` | Aman: Kanonikal memakai `x-ui.pin-pad`. |
| 14 | `resources/views/layouts/app/bottom-nav.blade.php` | Modifikasi | **Redesain (Task U3.13 & U4.9)** | Bottom navigation nasabah & kolektor dengan tombol absen tengah hijau datar 56px (DESIGN.md §7) | **Tugas U3.13 yang belum di-commit secara terpisah**. |
| 15 | `resources/views/pages/auth/login.blade.php` | Modifikasi | **Redesain (Fase 2)** | Form login 2 langkah, tombol hijau primary (D40), form terang (D42) | Selesai dan terverifikasi oleh `LoginRedesignTest`. |
| 16 | `resources/views/livewire/nasabah/*` | Modifikasi | **Redesain (Fase 3)** | Layar nasabah U3.1 - U3.12 (saldo, paket, penarikan, riwayat, komplain, dll) | Selesai di U3.1 - U3.12. |
| 17 | `resources/css/app.css` | Modifikasi | **Redesain (Fase 1 & 5)** | Animasi `td-anim-*`, token warna DESIGN.md, `@media (prefers-reduced-motion: reduce)` | Aman dan terverifikasi. |

---

## 2. Tindak Lanjut & Isolasi Perubahan

1. **Perubahan Non-Redesain (Khususnya Logika Uang/Admin)**:
   - `app/Actions/Penarikan/BatalkanPenarikanAction.php`, `app/Livewire/Admin/ApprovalPenarikan.php`, `app/Jobs/KirimNotifikasiWhatsApp.php`, dan `app/Services/WhatsAppService.php` **TIDAK AKAN di-commit bersama task redesain Fase 4**.
   - Berkas-berkas non-redesain ini dibiarkan tetap utuh di working directory (tidak dibuang dan tidak di-revert) sesuai instruksi poin 1 & 3, agar dapat ditinjau secara independen oleh pemilik sistem.
2. **Penyelesaian Task U3.13**:
   - Memisahkan dan melakukan commit tunggal untuk Task U3.13 (`resources/views/layouts/app/bottom-nav.blade.php` dan `tests/Feature/BottomNavMobileTest.php`).
   - Melakukan verifikasi test suite baseline (memastikan $\ge 977$ tes lulus).
3. **Pembaruan Rencana UI (`docs/ui-redesign-plan.md`)**:
   - Menambahkan definisi tugas **U4.10 sampai U4.14** ke dalam `docs/ui-redesign-plan.md` sebagai commit dokumentasi awal Fase 4.
4. **Memulai Fase 4 Kolektor (U4.1 sampai U4.14)**:
   - Menjalankan pengerjaan per-task dengan aturan *satu task = satu commit* mengikuti acuan prototipe dan kode yang ada.
