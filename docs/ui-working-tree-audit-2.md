# Audit Working Tree 2 (Analisis Status Repositori Sebelum Fase U7)

> Dokumen ini disusun sesuai instruksi wajib pada `docs/promt_design` sebelum memulai Fase U7 (Redesain & Harmonisasi Portal Nasabah).
> Tanggal audit: 2026-10-29.
> Prinsip: Tidak melakukan `git checkout`, `git restore`, `git clean`, atau penghapusan file di working tree.

---

## 1. Klasifikasi Berkas di Working Tree

| No | Berkas | Status Git | Kategori | Deskripsi & Perubahan | Tingkat Risiko & Tindak Lanjut |
|---|---|---|---|---|---|
| 1 | `CLAUDE.md` | Deleted di WT | Konfigurasi / Aturan | Berkas aturan agent terhapus di working tree, namun tersimpan utuh di `git show HEAD:CLAUDE.md`. | **Penting**: Jangan dibuang permanen. Jangan di-commit sebagai penghapusan dalam task UI. |
| 2 | `IMPLEMENTASI_PLAN.md`, `ex.md` | Deleted di WT | Dokumentasi Lama | Dokumen kerja/catatan dari fase audit terdahulu. | Risiko Rendah: Tidak berdampak ke runtime. Dibiarkan tanpa commit pada tugas ini. |
| 3 | `resources/views/components/auth-*`, `layouts/auth/*`, `layouts/app/header.blade.php` | Deleted di WT | Boilerplate Starter Kit | View bawaan Laravel/Breeze lama yang telah digantikan oleh `layouts/mobile.blade.php` dan `pages/auth/login.blade.php`. | Risiko Rendah: Tidak dipanggil oleh view aktif saat ini. |
| 4 | `app/Http/Controllers/DashboardController.php` | Modified | Backend / Controller | Menghitung data dashboard kolektor & nasabah (rekap kas, sapaan waktu, unread notifikasi, kinerja 6 hari). | **Risiko Sedang**: Menyajikan data ke `dashboard.blade.php`. Jangan mengubah kalkulasi logika uang. |
| 5 | `app/Livewire/Admin/*` (`InputSetoran`, `ManajemenProduk`) | Modified | Non-Redesain Nasabah (Admin) | Penyesuaian form admin dari fase sebelumnya (P6/P7). | **Isolasi**: Jangan di-commit bersama task U7 portal nasabah. |
| 6 | `app/Livewire/Kolektor/*` (`InputSetoran`, `JadwalKunjungan`, `PenarikanOffline`, `Pengaturan`, `VerifikasiPenarikan`) | Modified | Portal Kolektor (Fase U4) | Penyesuaian logic UI kolektor (filter rute, toggle PIN, status kehadiran). | **Isolasi**: Merupakan bagian dari U4/U6. Jangan diubah/dihapus sembarangan. |
| 7 | `app/Livewire/Nasabah/*` (`PengajuanAktif`, `PilihPaket`, `RiwayatPenarikan`) | Modified | Portal Nasabah (Fase U3/U7) | Penyesuaian handling pembatalan, daftar paket terbuka, dan pagination riwayat penarikan. | **Relevan**: Akan ditinjau pada task U7.2, U7.3, U7.4 secara terisolasi. |
| 8 | `resources/css/app.css` | Modified | Sistem Desain / Styling | Token warna `DESIGN.md`, utilitas `td-anim-*`, dark mode, safe-area padding. | Risiko Rendah: Digunakan bersama oleh semua portal. |
| 9 | `resources/js/foto.js` | Modified | Frontend / Script | Handler kompresi foto klien canvas & signature pad sebelum upload ke server. | Risiko Rendah: Mendukung absensi & serah terima paket. |
| 10 | `resources/views/dashboard.blade.php` | Modified | View Dashboard | Dashboard terpadu Admin, Kolektor, dan Nasabah. | **Fokus Utama U7.1**: Cabang `@else` (nasabah) akan diselaraskan dengan tata letak kolektor. |
| 11 | `resources/views/components/ui/*` (`baris-riwayat`, `kartu-saldo-hero`) | Modified | Komponen UI | Penyesuaian gaya badge, format angka, dan toggle intip saldo. | Risiko Rendah: Komponen kanonikal yang dipakai bersama. |
| 12 | `resources/views/layouts/mobile.blade.php` & `layouts/app/bottom-nav.blade.php` | Modified | Layout Mobile | Frame mobile shell dan bottom navigation 4-tab nasabah & 5-tab kolektor. | **Fokus U7.6**: Harmonisasi gaya nav aktif, target sentuh 44px, safe area. |
| 13 | `resources/views/livewire/kolektor/*` | Modified | View Kolektor | Implementasi visual Fase U4 kolektor (absen, input setoran, jadwal, nasabah binaan, dll). | **Jangkar Acuan**: Menjadi tolak ukur konsistensi visual nasabah. Jangan dirusak. |
| 14 | `resources/views/livewire/nasabah/*` | Modified | View Nasabah | 10 view nasabah (ajukan penarikan, komplain, notifikasi, pengajuan aktif, pengaturan, pilih paket, progres paket, riwayat penarikan, riwayat tabungan, saldo). | **Fokus U7.2 s/d U7.5**: Area kerja utama harmonisasi visual nasabah. |
| 15 | `resources/views/pages/auth/login.blade.php`, `offline.blade.php`, `struk-setoran.blade.php` | Modified | Halaman Khusus | Login 2 langkah, halaman fallback offline PWA, dan tampilan struk digital WhatsApp/Share. | Terverifikasi: Tidak diubah kecuali ada kebutuhan penyesuaian token visual. |
| 16 | `routes/web.php` | Modified | Routing | Rute notifikasi & transaksi hari ini kolektor. | **Non-Redesain Nasabah**: Dibiarkan tetap utuh, jangan diotak-atik pada task nasabah. |
| 17 | `tests/Feature/*` (`DashboardKolektorTest`, `DepositTest`, `JadwalKunjunganOtomatisTest`) | Modified | Pengujian | Penyesuaian asersi visual dan filter jadwal. | Menjaga baseline tes tetap hijau. |
| 18 | `app/Http/Controllers/Kolektor/*`, `app/Livewire/Kolektor/Notifikasi*`, `resources/views/livewire/kolektor/notifikasi*`, `resources/views/pages/kolektor/*` | Untracked | Fitur Kolektor Baru (U4.10, U4.12) | Notifikasi kolektor dan rincian transaksi hari ini kolektor. | Dibiarkan untracked/terisolasi; tidak mengganggu alur nasabah. |
| 19 | `docs/design/*` | Untracked | Dokumen Referensi | Berkas prototipe HTML acuan tata letak (nasabah, kolektor, prototipe lama). | **Sebagai Bahan Referensi** tata letak dan inspirasi UI. |
| 20 | `docs/promt_design` | Untracked | Dokumen Instruksi | Petunjuk dan arahan tugas redesain portal nasabah. | Rujukan kerja aktif. |

---

## 2. Kebijakan Isolasi & Perlindungan Perubahan

1. **Prinsip Nol Kerusakan Working Tree:**
   Tidak ada perintah penghapusan paksa (`git restore`, `git reset --hard`, `git clean -fd`). Semua perubahan eksisting dipertahankan di working directory agar tidak ada pekerjaan rekan tim atau sesi terdahulu yang hilang.
2. **Isolasi Commit U7:**
   Setiap commit fase U7 (`feat(U7.1): ...`, dst) hanya akan memasukkan (*stage*) berkas-berkas yang secara langsung berkaitan dengan tugas tersebut (terutama `resources/views/livewire/nasabah/*`, `dashboard.blade.php`, dan komponen UI terkait). Perubahan non-nasabah (seperti controller admin atau rute kolektor) tidak akan dicampur ke dalam commit U7.
3. **Penyelarasan Desain:**
   Sesuai arahan pengguna, prototipe diposisikan sebagai **bahan referensi**. Bila ditemukan tata letak yang lebih ergonomis atau palet warna alternatif yang lebih proporsional dari sistem desain `DESIGN.md`, opsi tersebut akan dipertimbangkan dan dikonsultasikan terlebih dahulu sebelum finalisasi.
