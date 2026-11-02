# Peta Konsistensi UI: Portal Nasabah vs Portal Kolektor (Task U7.0)

> Dokumen ini memetakan perbandingan pola visual antara **Portal Kolektor (Jangkar)** dengan **Portal Nasabah (Target Harmonisasi)**, mengacu pada `DESIGN.md`, kode aktif di `resources/views/`, dan prototipe di `docs/design/`.
> Berdasarkan arahan pengguna: Prototipe berfungsi sebagai **bahan referensi**, dan setiap pertimbangan tata letak atau opsi warna harus dipetakan serta dipertimbangkan dengan matang.

---

## 1. Matriks Perbandingan Pola Komponen & Layar

| Komponen / Elemen UI | Pola di Portal Kolektor (Jangkar Kode) | Pola di Portal Nasabah (Saat Ini) | Status Evaluasi | Rencana Penyelarasan & Pertimbangan Desain |
|---|---|---|---|---|
| **1. Header Dashboard** | Hero hijau tua melengkung `bg-primary-deep` dengan gelombang transisi kanvas SVG, koin float emas `bg-decor-gold animate-hero-float`, sapaan waktu dinamis, avatar inisial, tombol notifikasi transparan (`bg-white/15`) dengan badge emas, tombol A+ transparan. | Header terang di atas kanvas: avatar, "Halo, {nama depan}", tombol A+ ber-border hairline, dan lonceng notifikasi putih. | **Perlu Disamakan** | Terapkan hero melengkung hijau tua `bg-primary-deep` dengan gelombang SVG transisi kanvas di dashboard nasabah agar memiliki keselarasan visual brand yang hangat dan mewah. Pertahankan tombol A+ dan lonceng notifikasi ber-badge. |
| **2. Kartu Saldo / Kas Utama** | Kartu overlap `-mt-10` ber-rounded `rounded-[20px]`, border `hairline-warm`, chip status 'Tersambung', nominal besar `x-ui.rupiah`, dan cincin progres persentase SVG. | `<x-ui.kartu-saldo-hero>` hijau tua mandiri dengan toggle intip saldo Alpine, chip produk, dan tombol aksi "Tarik saldo" & "Rincian". | **Perlu Disamakan** | Integrasikan kartu saldo nasabah dengan efek overlap `-mt-10` di bawah header hero. Lengkapi dengan: (1) toggle Intip Saldo, (2) cincin progres paket aktif (`x-ui.progress-ring` atau SVG) dengan status pencairan, (3) chip saldo per produk, dan (4) tombol aksi cepat Tarik Saldo (disabled jika saldo kosong). |
| **3. Grid Menu Aksi Cepat** | Grid 4 kolom ubin `x-ui.ubin-aksi` (Jadwal, Nasabah, Setoran, Izin). Tile 44px dengan latar warna lembut per kategori (hijau, biru, amber, teal). | Grid 4 ubin (Tarik, Riwayat, Paket, Bantuan). | **Perlu Disamakan** | Perluas menjadi Grid 8 Ubin Aksi (2 baris x 4 kolom) sesuai referensi prototipe: **Tarik, Riwayat, Paket, Bantuan, Saldo, Ikuti Paket, Penarikan, Notifikasi**. Setiap ubin memakai ikon `flux:icon` yang sesuai dan hanya dipasang bila rutenya valid (`nasabah.*`). |
| **4. Kartu Metrik / Progres** | Kartu putih rounded 20px ber-border `hairline-warm`, grafik batang kinerja 6 hari dengan garis target putus-putus. | Kartu streak 7 titik "Setoran minggu ini" dan kartu progres paket aktif. | **Sudah Sama (Beda Sengaja)** | Kolektor berfokus pada target kinerja kas; Nasabah berfokus pada kebiasaan menabung (streak) dan capaian paket barang/tunai. Pertahankan kartu streak 7 titik dan progres paket. |
| **5. Judul Halaman Tab (Top-Level Nav)** | Menggunakan judul besar di kanvas (`title-lg`, `text-xl font-semibold`) dengan tombol aksi pil hijau di kanan (contoh: "Nasabah binaan" + tombol `+ Daftar`). Tanpa bilah kembali. | Pada `progres-paket`, saat ini masih menggunakan pita teal atau bilah judul campuran. | **Perlu Disamakan** | Tab `progres-paket` distandarkan sebagai **Halaman Tab**: judul "Paket Tabungan" dengan tombol aksi pil hijau `+ Ikuti` di kanan atas yang mengarah ke `nasabah.paket.pilih`. Tab `riwayat-tabungan` juga menggunakan format halaman tab murni. |
| **6. Bilah Judul Halaman Dalam (Child Page)** | Bar navigasi dengan tombol kembali bulat chevron di kiri (`size-9 rounded-full border border-hairline bg-white`) dan judul halaman di tengah/kiri. | Sebagian view nasabah sudah memakai pola tombol kembali, namun gaya border dan penempatan tombol masih bervariasi. | **Perlu Disamakan** | Seragamkan bilah navigasi halaman dalam (`ajukan-penarikan`, `pilih-paket`, `pengajuan-aktif`, `komplain`, dll) dengan tombol kembali bundar 36px dan judul tegas. |
| **7. Keypad PIN & Dots** | `<x-ui.pin-pad>` 62px berbentuk tombol bulat tanpa border dengan ripple/active scale, dipasangkan dengan `<x-ui.pin-dots>` 6 titik. Digunakan pada verifikasi offline. | Menggunakan `<x-ui.pin-pad>` dan `<x-ui.pin-dots>` pada lembar konfirmasi penarikan dan komitmen paket. | **Sudah Sama** | Pertahankan penggunaan kanonikal `x-ui.pin-pad` dan `x-ui.pin-dots`. Pastikan `wire:model="pin"` terhubung tanpa lag. |
| **8. Status Chips** | `<x-ui.status-chip>` dengan palet terstandar di `DESIGN.md`: hijau (selesai/hadir), amber (pending/menunggu), biru (diproses), merah (ditolak/batal). | Menggunakan kombinasi badge Tailwind ad-hoc dan status chip. | **Perlu Disamakan** | Ganti seluruh badge status manual di riwayat penarikan, komplain, dan kepesertaan paket dengan komponen resmi `<x-ui.status-chip>`. |
| **9. Stepper & Alur Tahapan** | Stepper proses pendaftaran & alur absensi. | Stepper 4 langkah pada progres paket dan stepper 3 tahap penarikan. | **Sudah Sama** | Komponen `x-ui.stepper` dan `x-ui.stepper-langkah` sudah seragam, pertahankan styling dan warna indikatornya. |
| **10. Kartu Peringatan / Banner Edukasi** | Kotak peringatan amber lembut `bg-warning-soft border border-soft-amber-border text-warning-deep rounded-2xl` dengan ikon peringatan di kiri. | Beberapa layar menggunakan latar abu-abu atau biru. | **Perlu Disamakan** | Standarkan banner informasi (mis. aturan penarikan, toleransi tunggakan paket, peringatan verifikasi) ke gaya amber lembut yang kontras dan ramah. |
| **11. Modal / Bottom Sheet** | Modal lembar bawah (*bottom sheet*) dengan pegangan drag (*handle bar*), backdrop blur `bg-black/40 backdrop-blur-xs`, sudut atas melengkung `rounded-t-[28px]`. | Modal konfirmasi penarikan dan modal komitmen paket. | **Sudah Sama** | Pertahankan pola bottom sheet dengan animasi slide-up yang lembut dan transisi keluar yang bersih. |
| **12. Layar Sukses (Success State)** | `<x-ui.layar-sukses>`: ikon centang hijau besar dengan lingkaran cahaya lembut, judul tegas, kartu ringkasan transaksi bergaris putus-putus, dan tombol aksi kembali. | Form penarikan menggunakan tampilan layar sukses penarikan. | **Sudah Sama** | Pastikan layar sukses pengajuan penarikan memakai `<x-ui.layar-sukses>` yang identik dengan layar sukses setoran kolektor. |
| **13. Empty State** | Ilustrasi tunas/dompet `x-ui.ilustrasi` berukuran 80px/96px, judul semibold, teks penjelasan `text-mute text-xs`, dan tombol ajakan aksi (*CTA*). | Beberapa daftar nasabah memakai teks kosong sederhana tanpa ilustrasi tunas. | **Perlu Disamakan** | Gunakan `<x-ui.ilustrasi nama="tunas">` atau `<x-ui.ilustrasi nama="dompet">` pada seluruh kondisi daftar kosong (riwayat, notifikasi, tiket komplain, kepesertaan). |
| **14. Halaman Pengaturan & Profil** | Kartu profil hero (avatar, nama, no HP, status akun), grup menu berlabel (*Akun & Keamanan*, *Preferensi & Tampilan*, *Bantuan & Sesi*), baris menu ikon + chevron + toggle A+, tombol keluar merah. | Pengaturan nasabah memiliki tata letak serupa tetapi ada perbedaan padding, ikon, dan kartu PJ kolektor. | **Perlu Disamakan** | Samakan persis struktur grup menu nasabah dengan `kolektor/pengaturan`, termasuk kartu "Petugas Kolektor Anda" dengan tombol pintas Chat WhatsApp langsung (`wa.me`). |
| **15. Bottom Navigation** | 5 item (Beranda, Jadwal, Absen di tengah 56px, Binaan, Profil) dengan state aktif hijau `text-primary`. | 4 item (Beranda, Riwayat, Paket, Profil) dengan state aktif hijau `text-primary`. | **Beda Sengaja (Struktur)** / **Perlu Disamakan (Gaya)** | Nasabah tetap **4 item** (jangan menambah tombol absen!), namun gaya visual (ukuran ikon 24px, label 11-12px, pill indikator aktif, padding safe-area `env(safe-area-inset-bottom)`) disamakan persis dengan kolektor. |

---

## 2. Pertimbangan Tata Letak & Opsi Warna Alternatif

Sesuai instruksi pengguna agar tata letak dan warna alternatif dipertimbangkan terlebih dahulu:

### A. Pertimbangan Tata Letak Dashboard Nasabah (Task U7.1)
1. **Opsi Header Hero (Rekomendasi Utama):**
   * *Desain:* Memakai hero hijau tua `bg-primary-deep` (`#166534`) dengan ornamen koin emas floating `Rp` dan transisi gelombang SVG ke kanvas.
   * *Alasan:* Memberikan kesan visual yang senada dengan portal kolektor dan aplikasi perbankan modern (BCA Mobile, Livin', Jago). Kartu saldo tumpang tindih (*overlap*) di atasnya memberikan kedalaman visual (*visual depth*).
2. **Opsi Grid Menu 8 Ubin vs 4 Ubin:**
   * *Desain:* 8 ubin aksi cepat dalam 2 baris x 4 kolom:
     * Baris 1: **Tarik** (Kategori Emas/Hijau), **Riwayat** (Biru), **Paket** (Teal), **Bantuan** (Amber).
     * Baris 2: **Saldo** (Hijau Muda), **Ikuti Paket** (Teal Muda), **Penarikan** (Biru Muda), **Notifikasi** (Abu/Netral).
   * *Alasan Ergonomi:* Pada layar ponsel 360–390px, grid 4 kolom dengan ukuran ubin ~72px sangat mudah dijangkau satu tangan dan langsung menyajikan seluruh fungsi nasabah tanpa harus mencari-cari di menu samping.
3. **Cincin Progres Paket di Dashboard:**
   * Menempatkan cincin progres persentase capaian di dalam atau tepat di bawah kartu saldo utama, sehingga nasabah yang memiliki paket aktif langsung mengetahui berapa hari/rupiah lagi target tabungan mereka akan tercapai.

### B. Pertimbangan Warna & Kategori Ubin (Mengacu `DESIGN.md`)
* Warna diatur **hanya pada latar belakang ikon (*tile/icon background*) dan ikon itu sendiri**, sedangkan teks judul ubin tetap menggunakan warna tinta netral `text-ink dark:text-zinc-100` agar kontras WCAG AA tetap terjaga:
  * **Setoran / Saldo:** Latar `primary-soft` (`#dcfce7`), ikon `primary` (`#15803d`).
  * **Penarikan / Tarik:** Latar `info-soft` (`#dbeafe`), ikon `info` (`#1d4ed8`).
  * **Paket Tabungan:** Latar `tile-teal-bg` (`#ccfbf1`), ikon `tile-teal-fg` (`#0f766e`).
  * **Bantuan / Komplain:** Latar `warning-soft` (`#fef3c7`), ikon `warning-deep` (`#92400e`).
  * **Notifikasi:** Latar `surface-inset` (`#f5f5f5`), ikon `subtle` (`#737373`).

---

## 3. Langkah Lanjutan Bertahap
Dengan disepakatinya peta konsistensi dan pertimbangan desain ini, tahapan pengerjaan kode produksi akan dimulai dari **U7.1 (Dashboard Nasabah)**:
1. Membaca pengujian yang mengawal dashboard nasabah (`GayaUiDashboardMobileTest`, `DashboardTest`, `UiProgressRingTest`).
2. Menyesuaikan tampilan `resources/views/dashboard.blade.php` pada cabang nasabah mengikuti tata letak hero + overlap saldo + 8 ubin aksi.
3. Melakukan verifikasi lokal: Pint, tes feature dashboard, dan build aset.
