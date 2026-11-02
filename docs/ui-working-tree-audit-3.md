# Audit Working Tree 3 (Analisis Status Repositori Sebelum Fase U8 - Redesain Admin)

> Dokumen ini disusun sesuai instruksi wajib pada panduan implementasi desain admin sebelum memulai Fase U8 (Redesain & Harmonisasi Portal Admin Berdasarkan Prototipe `docs/design/admin-desain.html`).
> Tanggal audit: 2026-10-31.
> Prinsip utama: **TIDAK melakukan `git checkout`, `git restore`, `git clean`, atau penghapusan berkas apa pun di working tree.** Seluruh berkas yang dimodifikasi, ditambahkan, atau dihapus oleh sesi sebelumnya dilindungi seutuhnya.

---

## 1. Klasifikasi Berkas di Working Tree

| No | Kategori | Berkas | Status Git | Deskripsi & Peran | Tindak Lanjut & Isolasi |
|---|---|---|---|---|---|
| 1 | Konfigurasi / Dokumen Inti | `CLAUDE.md` | Deleted di WT | Berkas aturan operasional agent. Tersimpan utuh di commit `HEAD`. | **Penting**: Dibiarkan di working tree (jangan di-commit sebagai deletion, jangan di-restore paksa). |
| 2 | Dokumentasi Fase Lalu | `IMPLEMENTASI_PLAN.md`, `ex.md` | Deleted di WT | Dokumen perencanaan fase-fase awal sebelum redesign UI. | Tidak berpengaruh pada kode aplikasi runtime. Dibiarkan utuh. |
| 3 | Starter Kit / Boilerplate Lama | `resources/views/components/auth-*`, `layouts/auth/*`, `layouts/app/header.blade.php`, `placeholder-pattern.blade.php` | Deleted di WT | Komponen auth dan header lama bawaan starter kit Laravel yang telah digantikan oleh `portal.blade.php` dan `layouts/mobile.blade.php`. | Dibiarkan; tidak dipanggil oleh rute maupun view aktif saat ini. |
| 4 | Controller Backend | `app/Http/Controllers/DashboardController.php`, `app/Http/Controllers/StrukSetoranController.php` | Modified | Menyajikan data agregat beranda serta endpoint JSON untuk modal popup struk digital. | **Zona Terlindungi**: Logika kalkulasi uang dan data dilarang diubah. Tidak di-commit kecuali bila diperlukan terkait view admin. |
| 5 | Komponen Livewire Admin | `app/Livewire/Admin/*` (`AntrianKomplain`, `ApprovalPenarikan`, `InputSetoran`, `ManajemenNasabah`, `ManajemenProduk`, `TinjauanPenarikanOffline`) | Modified | Komponen backend Livewire untuk halaman admin (manajemen nasabah, produk, approval, tinjauan). | **Zona Terlindungi**: Logika bisnis uang, otorisasi, dan properti/method Livewire tidak boleh diubah. Hanya disentuh bila ada penyesuaian properti tampilan non-destruktif. |
| 6 | Komponen Livewire Kolektor & Nasabah | `app/Livewire/Kolektor/*`, `app/Livewire/Nasabah/*` | Modified | Logika interaktif portal Kolektor (U4/U7) dan Nasabah (U3/U7). | **Isolasi Penuh**: Merupakan pekerjaan fase U3, U4, U7 yang sudah stabil. Jangan diubah/di-commit pada tugas U8. |
| 7 | Dokumentasi Perencanaan | `docs/plan-log.md` | Modified | Log kronologis keputusan perancangan dan implementasi (U0 s/d U7). | **Relevan**: Akan ditambahkan catatan tugas U8.1 s/d U8.9 dan keputusan desain baru (D59+). |
| 8 | Styling & Script Frontend | `resources/css/app.css`, `resources/js/foto.js` | Modified | Token desain tema, animasi UI, serta skrip kompresi foto/canvas. | **Fokus U8.1**: Penambahan token kanvas, surface, border, dan aksen admin sesuai prototipe `admin-desain.html`. |
| 9 | Shell & Komponen Layout Admin | `resources/views/layouts/admin.blade.php`, `resources/views/components/admin/*` (`sidebar`, `topbar`, `nav-item`, `drawer`) | Modified / Untracked | Kerangka shell admin, topbar, sidebar navigasi, dan panel samping. | **Fokus Utama U8.1 & U8.2**: Transformasi menjadi layout persis prototipe (sidebar 268px, topbar 54px, token baru, drawer flyout). |
| 10 | View Halaman Admin | `resources/views/livewire/admin/*` (18 berkas), `resources/views/pages/admin/persetujuan.blade.php` | Modified | Seluruh tampilan halaman portal admin (dashboard, nasabah, produk, penarikan, transaksi, dll). | **Fokus Utama U8.3 s/d U8.8**: Perubahan visual persis prototipe tanpa mengubah `wire:model`, `wire:click`, dan `data-test`. |
| 11 | View Portal Kolektor & Nasabah | `resources/views/livewire/kolektor/*`, `resources/views/livewire/nasabah/*`, `resources/views/dashboard.blade.php`, `resources/views/layouts/mobile.blade.php` | Modified | Tampilan portal mobile nasabah dan kolektor yang telah dirapikan pada U7. | **Isolasi Penuh**: Dibiarkan utuh, jangan diubah dalam pengerjaan admin. |
| 12 | Rute & Halaman Khusus | `routes/web.php`, `resources/views/pages/auth/login.blade.php`, `offline.blade.php`, `struk-setoran.blade.php` | Modified | Rute aplikasi dan tampilan login/struk/offline. | **Terlindungi**: Tidak menambah rute admin baru, mempertahankan rute yang ada (`admin.*`). |
| 13 | Tes Fitur Eksisting | `tests/Feature/*` (`DashboardKolektorTest`, `DepositTest`, `JadwalKunjunganOtomatisTest`, `KomisiTest`, `KomisiEksporD21Test`, `StrukSetoranTest`) | Modified | Pengujian fitur yang telah disesuaikan pada fase sebelumnya. | **Jangkar Kualitas**: Wajib tetap hijau sepanjang pengerjaan U8. |
| 14 | Fitur Baru Kolektor & Galat Sistem | `app/Http/Controllers/Kolektor/*`, `app/Livewire/Kolektor/*`, `resources/views/errors/*`, `tests/Feature/HalamanGalatSistemTest.php`, dll | Untracked | Fitur notifikasi, transaksi hari ini kolektor, dan penanganan galat sistem. | Dibiarkan terisolasi; tidak bersinggungan dengan portal admin. |
| 15 | Prototipe Desain & Panduan | `docs/design/*` (termasuk `admin-desain.html`), `docs/promt_design`, `docs/ui-konsistensi-*.md` | Untracked | Spesifikasi acuan visual resmi untuk redesain admin dan mobile. | **Spesifikasi Acuan U8**: Dibaca dan dicocokkan untuk memastikan paritas persis sama. |

---

## 2. Kebijakan Eksekusi & Isolasi Tugas U8

1. **Perubahan Tampilan Murni:**
   Tidak ada perubahan pada logika backend, penghitungan uang, otorisasi peran (`role:admin`), migrasi database, model Eloquent, atau perilaku Livewire.
2. **Kepatuhan Atribut Pengujian (`data-test`):**
   Seluruh atribut `data-test` yang telah diuji (misal: `data-test="bottom-nav-admin"`, `data-test="sheet-menu"`, `data-test="kartu-tabel"`, `data-test="rupiah"`, dsb.) dipertahankan 100%.
3. **Penyelarasan Bertahap Berbasis Tugas (U8.1 - U8.9):**
   Setiap tugas U8 dijalankan secara terisolasi dengan siklus:
   - Modifikasi view/komponen terkait.
   - Verifikasi paritas terhadap `docs/design/admin-desain.html`.
   - Pemformatan kode: `vendor/bin/pint --dirty --format agent`.
   - Eksekusi pengujian terkait: PHPUnit / Pest.
   - Kompilasi frontend: `npm run build`.
   - Pencatatan di `docs/plan-log.md` dan commit terpisah: `feat(U8.n): ...`.
