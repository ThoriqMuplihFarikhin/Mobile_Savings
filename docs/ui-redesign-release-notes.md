# Catatan Rilis & Panduan Penggabungan: Redesain UI Mobile (2026-10)

Dokumen ini merangkum seluruh perubahan visual, komponen, arsitektur antarmuka, hasil verifikasi pengujian otomatis, serta panduan penggabungan (*merge*) dan *rollback* untuk program **Redesain Antarmuka Mobile (Nasabah & Kolektor)** pada branch `feat/ui-redesign-2026-10`.

---

## 1. Ringkasan Eksekutif

- **Tujuan**: Meremajakan tampilan portal Nasabah dan Kolektor agar lebih modern, hangat, berpusat pada angka (*number-centric*), ramah pengguna lapangan (target sentuh ≥ 44px, kontras tinggi WCAG AA), dan mendukung mode teks besar (A+) serta mode offline tanpa merusak logika finansial dan otorisasi.
- **Cakupan**:
  - Halaman Autentikasi / Login (Nasabah, Kolektor, Admin).
  - Seluruh 13 layar Portal Nasabah (Beranda, Saldo, Paket, Riwayat, Penarikan, Komplain, Notifikasi, Pengaturan, Bottom Nav).
  - Seluruh 14 layar/komponen Portal Kolektor (Beranda Kas, Jadwal Kunjungan, Input Setoran, Layar Sukses, Daftar Binaan, Absensi Kamera/TTD, Setor Kantor, Penarikan Offline, Bottom Nav).
- **Keamanan Finansial & Logika**: **0 perubahan skema database/migrasi**. Tidak ada perubahan pada rumus perhitungan kas (`KasKolektorHitung`), rekonsiliasi setor kantor, verifikasi PIN penarikan, idempotensi setoran, dan handover kolektor.

---

## 2. Rincian Perubahan per Portal

### 2.1 Autentikasi & Login (D38, D40, D41, D42)
- Form login 2 langkah interaktif berbasis Alpine.js dengan jembatan centang otomatis saat 6 digit PIN selesai dimasukkan.
- Fallback penuh tanpa JavaScript untuk perangkat lawas atau koneksi terganggu.
- Papan tombol angka digital (*keypad*) 62px dengan feedback visual titik PIN 20px (*shake/pop*).
- Pembeda visual per portal:
  - Nasabah: Aksen hijau `#15803d` (`colors.primary`).
  - Kolektor: Aksen hijau primary dengan chip identitas biru `#2563eb`.
  - Admin: Aksen gelap netral `#171717`.
- Modal bantuan WhatsApp resmi admin langsung dari layar login (D41).
- Mode terang kanvas hangat tanpa gradien dan tanpa font monospace (D42).

### 2.2 Portal Nasabah (Fase 3: U3.1 – U3.13)
- **Beranda (`dashboard`)**: Kartu saldo hero `<x-ui.kartu-saldo-hero>` dengan toggle intip saldo Alpine.js, 4 ubin navigasi aksi cepat (Tarik, Riwayat, Paket, Bantuan), kartu progres paket aktif dengan streak 7 titik, dan 3 riwayat transaksi terbaru.
- **Saldo (`nasabah/saldo`)**: Rincian saldo per paket tabungan, bar pencapaian target, dan tombol tarik tunai cepat.
- **Pilih Paket (`nasabah/pilih-paket`)**: Katalog kartu paket bebas biaya admin (D34), modal komitmen dengan keypad PIN 62px, dan empty state tunas.
- **Progres Paket (`nasabah/progres-paket`)**: Cincin progres 132px, daftar target barang 56px dengan status-chip, pilihan metode penyerahan, dan tautan bukti foto.
- **Ajukan Penarikan (`nasabah/ajukan-penarikan`)**: Form 3 langkah dengan chips nominal cepat (Rp 50.000, Rp 100.000, Rp 200.000, Tarik Habis), pilihan lokasi pengambilan, konfirmasi PIN, dan layar sukses animasi centang konfeti statis (D36).
- **Pengajuan Aktif**: Kartu status pengajuan penarikan berjalan dengan aksi pembatalan tanpa kode 4 digit (D33).
- **Riwayat Tabungan & Penarikan**: Hero mini statistik, grafik batang 7 hari dengan aksesibilitas aria-label, filter rentang tanggal Flatpickr, dan baris riwayat tile 40px.
- **Komplain & Notifikasi**: Banner pintas WhatsApp resmi (D41), tiket bantuan ber-stepper, dan kartu notifikasi per kategori.
- **Pengaturan & Bottom Nav**: Kartu profil nasabah, kontak petugas kolektor binaan, toggle teks besar A+ (D37), dan bottom navigation 4 tab hijau primary.

### 2.3 Portal Kolektor (Fase 4: U4.1 – U4.14)
- **Beranda Kolektor**: Hero "Kas di tangan" (rumus net cash D4/D13), bilah progres kunjungan hari ini teal, tombol "Setor ke kantor", 4 ubin navigasi (Jadwal, Nasabah, Setoran, Izin), dan kartu penunjuk kunjungan berikutnya.
- **Jadwal Kunjungan**: Segmen waktu (Hari ini, Besok, Minggu ini), pengelompokan rute lapangan, penyorotan nasabah berikutnya, chip status kunjungan, dan tombol catat setoran langsung.
- **Input Setoran & Layar Sukses**: Kartu nasabah dan paket, kalkulasi saldo baru dan tunggakan seketika, chip nominal cepat, serta layar sukses dengan tombol struk digital (Web Share API / tautan WhatsApp `wa.me` sesuai D35).
- **Daftar & Nasabah Binaan**: 4 metrik KPI binaan, filter mode (Semua/Digital/Offline), label eksplisit `Mode Offline` untuk nasabah buku tabungan fisik, dan formulir pendaftaran 3 langkah.
- **Absensi Lapangan**: Alur 3 langkah berurutan (GPS geolokasi, swafoto kamera `capture`, tanda tangan digital `canvas-ttd` ke private disk), pesan alasan saat tombol nonaktif, dan riwayat absensi.
- **Setor Kantor & Penarikan Offline**: Hero kas net D13, form penarikan offline langsung D14 dengan input catatan wajib bagi nasabah offline, dan modal verifikasi PIN nasabah 62px.
- **Izin & Pengaturan Kolektor**: Pengajuan izin dengan riwayat status persetujuan, kartu profil tanpa chip peran (D40), banner peringatan kas belum disetor, toggle teks besar A+, dan bottom nav 5 tab dengan tombol Absen adaptif di tengah.

---

## 3. Komponen Antarmuka Baru (`resources/views/components/ui/*`)

Seluruh komponen dibuat dengan standar token [`DESIGN.md`](DESIGN.md) dan dilengkapi atribut pengujian `data-test`:

| Komponen | Props Kunci | Fungsi |
|---|---|---|
| `<x-ui.kartu-saldo-hero>` | `:saldo`, `:totalMenabung`, `:frekuensi`, `:intip` | Hero card hijau tua untuk ringkasan saldo dengan tombol intip Alpine.js |
| `<x-ui.ubin-aksi>` | `:judul`, `:rute`, `:warna`, `:ikon` | Ubin sentuh cepat 56px dengan warna aksen lembut sesuai kategori |
| `<x-ui.baris-riwayat>` | `:judul`, `:subjudul`, `:nominal`, `:tipe`, `:status` | Baris transaksi ber-tile 40px dengan format nominal rupiah dan status-chip |
| `<x-ui.pin-pad>` | `:panjang`, `:name` | Papan tombol angka digital 62px yang ramah jari lapangan |
| `<x-ui.pin-dots>` | `:panjang`, `:aktif` | Indikator titik PIN 20px dengan animasi responsif |
| `<x-ui.status-chip>` | `:status`, `:tipe` | Chip badge semantik (success, warning, danger, info, neutral) tanpa gradien |
| `<x-ui.rupiah>` | `:nilai` | Formatter angka moneter konsisten (`Rp&nbsp;X.XXX.XXX`) |
| `<x-ui.stepper-langkah>` | `:langkahAktif`, `:totalLangkah`, `:daftarLabel` | Penunjuk tahapan form multi-langkah |
| `<x-ui.layar-sukses>` | `:judul`, `:pesan`, `:nominal`, `:tautanRoute` | Layar konfirmasi keberhasilan transaksi dengan animasi centang halus |

---

## 4. Jaminan Kualitas & Hasil Uji

Pemeriksaan otomatis dijalankan pada lingkungan PHP 8.3 / Laravel 13:

- **Pest Test Suite**: **983 / 983 tests passed (100% hijau, 4.095+ asersi)**.
  - 36 tes `GayaUi*` structural UI lulus.
  - Seluruh alur autentikasi, setoran, penarikan, absensi, jadwal, dan izin lulus.
- **Static Analysis (PHPStan)**: Level 7 + Larastan tuntas dengan **0 error**.
- **Code Style (Laravel Pint)**: Sesuai standar PSR-12/Laravel Pint (`passed`).
- **Asset Compilation (Vite)**: Selesai dalam 2.98 detik.
  - CSS bundle gzip: **46.53 kB** (anggaran ≤ 48 kB).
  - JS bundle gzip: **49.97 kB** (anggaran ≤ 55 kB).
  - 0 file gambar raster baru.
- **Aksesibilitas & Lapangan**:
  - Target sentuh: Keypad 62px, ubin 56px, tombol aksi 48px (standar WCAG ≥ 44px).
  - Kontras teks: Memenuhi standar WCAG AA di mode terang maupun `.dark`.
  - Dukungan preferensi gerak: `@media (prefers-reduced-motion: reduce)` menonaktifkan seluruh animasi dekoratif.

---

## 5. Prosedur Penggabungan (*Merge*) & *Rollback*

### 5.1 Penggabungan (Merge ke Main)
Karena perubahan pada branch ini murni bersifat antarmuka (Blade, CSS, Alpine.js) tanpa migrasi database:
1. Pastikan build aset telah selesai:
   ```bash
   npm run build
   ```
2. Lakukan merge branch `feat/ui-redesign-2026-10` ke branch target:
   ```bash
   git checkout main
   git merge --no-ff feat/ui-redesign-2026-10 -m "merge: rilis redesain UI mobile portal nasabah dan kolektor 2026-10"
   ```
3. Jalankan verifikasi pasca-merge:
   ```bash
   php artisan test --compact
   php artisan deploy:check
   ```

### 5.2 Rollback Darurat
Jika terjadi kendala pada lingkungan produksi:
- Karena tidak ada migrasi skema database yang berjalan, rollback cukup dilakukan dengan me-revert merge commit:
  ```bash
  git revert -m 1 <MERGE_COMMIT_HASH>
  npm run build
  ```
- Seluruh basis data, saldo nasabah, dan catatan setoran dijamin tetap aman dan utuh.

---

## 6. Keputusan Terbuka untuk Pemilik Produk (`[BUTUH KEPUTUSAN]`)

Fitur-fitur opsional berikut sengaja disembunyikan tampilannya agar tidak memicu error rute atau data palsu di UI, dan menunggu arahan sprint berikutnya:
1. **Notifikasi Staf Kolektor (U4.10)**: Pembuatan entitas/tabel notifikasi khusus peran kolektor di database.
2. **Target Harian Kolektor (U4.11)**: Penambahan setting konfigurasi target setoran harian (mis. Rp 1.500.000) pada tabel `admin_settings`.
3. **Peta Rute Lapangan Interaktif (U4.13)**: Penambahan koordinat GPS nasabah pada `nasabah_profil` untuk modul peta rute Leaflet/OpenStreetMap.
