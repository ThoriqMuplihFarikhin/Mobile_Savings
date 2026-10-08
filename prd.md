# PRD — Sistem Tabungan Digital (Kolektor Keliling)

**Versi:** 1.0 (Draft)
**Status:** Requirement gathering selesai — siap masuk fase desain teknis
**Tanggal:** September 2026

---

## 1. Latar Belakang

Bisnis ini saat ini berjalan secara manual: petugas (kolektor) keliling ke rumah nasabah membawa buku catatan fisik untuk mencatat setoran tabungan harian. Ada dua jenis produk: **Tabungan Bebas** (nominal & waktu fleksibel) dan **Tabungan Paket** (cicilan harian tetap, hasil berupa sembako, cair saat Lebaran — dengan tanggal ditentukan admin).

Masalah dari sistem manual saat ini:
- Nasabah tidak punya visibilitas real-time atas saldo mereka
- Pencatatan rawan human error dan sulit diaudit
- Tidak ada kontrol atas kas fisik yang dipegang kolektor
- Proses penarikan dan verifikasi tidak terstandarisasi

**Tujuan produk:** membangun aplikasi web (responsif, bisa diakses dari HP) yang mendigitalkan seluruh alur ini — dari pendaftaran nasabah, pencatatan setoran, pengajuan penarikan, sampai rekonsiliasi kas — dengan tetap mempertahankan model bisnis kolektor keliling yang sudah berjalan.

---

## 2. Tujuan (Goals)

1. Nasabah bisa memantau saldo & riwayat tabungannya secara real-time tanpa harus menunggu kolektor datang
2. Semua transaksi (setoran, penarikan) tercatat rapi dan bisa diaudit (siapa input, kapan, ada koreksi atau tidak)
3. Mengurangi risiko kehilangan/penyalahgunaan uang fisik lewat mekanisme rekonsiliasi kas kolektor
4. Mempermudah pengelolaan produk Tabungan Paket, termasuk perhitungan tunggakan dan kebutuhan barang menjelang Lebaran
5. Memberi nasabah jalur resmi untuk komplain, dan memastikan transisi tanggung jawab antar kolektor tidak menimbulkan celah data/uang hilang

## 2.1 Non-Goals (Di Luar Cakupan Awal)
- Integrasi pembayaran digital (transfer bank/e-wallet) — sistem ini murni berbasis kas fisik yang dicatat secara digital
- Aplikasi native iOS/Android (cukup web responsif)
- Legalitas sebagai lembaga keuangan formal (koperasi/OJK) — dicatat sebagai pertimbangan masa depan, bukan syarat peluncuran awal

---

## 3. Pengguna & Peran (User Roles)

| Role | Deskripsi Singkat | Akses Utama |
|---|---|---|
| **Nasabah** | Penabung yang dikunjungi kolektor | Read-only atas data sendiri + ajukan penarikan & komplain |
| **Kolektor** | Petugas lapangan, keliling ke rumah nasabah | Input setoran, proses penarikan offline, kelola kunjungan, setor kas ke kantor |
| **Admin** | Pengelola pusat/kantor | Kontrol penuh: verifikasi, approval, kelola produk/nasabah/kolektor, laporan, rekonsiliasi |

Autentikasi: **No. HP + PIN 6 digit** untuk seluruh role, dengan proteksi brute-force (limit percobaan gagal → kunci sementara) dan PIN disimpan ter-hash.

---

## 4. Kebutuhan Fungsional (Functional Requirements)

> **Status build `fix/audit-2026-10` + `feat/okt26`:** centang `[x]` pada FR yang terpenuhi dan terverifikasi lewat tes otomatis (FR lama: `docs/plan-log.md` seksi P7.2; FR baru 4.11: seksi P9.1). FR tanpa centang belum dikonfirmasi pada build ini.

### 4.1 Pendaftaran & Manajemen Nasabah
- FR-1: Nasabah **tidak bisa mendaftar sendiri** — hanya admin atau kolektor yang bisa mendaftarkan
- FR-2: Jika didaftarkan kolektor → status `pending_verifikasi`, wajib diverifikasi admin sebelum akun aktif
- FR-3: Jika didaftarkan admin langsung → status otomatis `aktif`
- FR-4: Admin bisa mencari, memfilter, melihat detail, mengaktifkan/menonaktifkan, dan reset PIN nasabah

### 4.2 Setoran
- [x] FR-5: Hanya kolektor dan admin yang bisa menginput transaksi setoran (nasabah tidak bisa input sendiri)
- FR-6: Input bisa dilakukan real-time (saat di rumah nasabah) atau susulan (dari buku fisik, dengan tanggal transaksi bisa mundur/backdate)
- FR-7: Saldo nasabah **langsung bertambah** saat transaksi diinput — tidak ada jeda approval
- FR-8: Admin bisa mengoreksi atau membatalkan transaksi setoran yang keliru, dengan alasan wajib diisi dan riwayat perubahan tersimpan
- FR-9: Setiap transaksi setoran tercatat siapa yang input, kapan transaksi terjadi vs kapan diinput ke sistem

### 4.3 Penarikan
- FR-10: Nasabah bisa mengajukan penarikan secara online (lewat web) atau offline (datang ke kolektor/kantor)
- FR-11: Setiap penarikan dikenakan komisi sesuai persentase yang berlaku untuk jenis produk terkait (besaran berbeda per produk)
- FR-12: Seluruh pengajuan penarikan **wajib melalui approval admin** sebelum dana dicairkan
- FR-13: Khusus produk Tabungan Paket, penarikan diblokir sistem sebelum `tanggal_boleh_cair` (ditentukan manual oleh admin) tercapai
- FR-14: Nasabah memilih lokasi pengambilan dana: rumah kolektor atau kantor

### 4.4 Produk Tabungan
- FR-15: Admin bisa membuat dan mengelola jenis produk (Tabungan Bebas, Paket A/B/C, dst), masing-masing dengan % komisi, minimal setor, dan (khusus paket) isi barang serta periode berlaku
- FR-16: Tabungan Bebas tidak memiliki kewajiban setor harian — nasabah boleh tidak menabung pada hari tertentu tanpa konsekuensi
- FR-17: Tabungan Paket memiliki kewajiban cicilan harian tetap. Jika nasabah tidak menabung suatu hari, sistem otomatis menghitung tunggakan yang harus dilunasi (2x lipat) pada kesempatan setor berikutnya
- FR-18: Sistem menghitung otomatis: jumlah hari berjalan, total seharusnya terkumpul, total aktual terkumpul, dan tunggakan untuk tiap kepesertaan paket
- [x] FR-19: Jika tunggakan melewati batas toleransi (dapat dikonfigurasi admin per produk), sistem memunculkan alert ke admin — keputusan akhir (memberi kelonggaran, menyatakan gagal, dsb) dilakukan manual oleh admin

### 4.5 Pencairan & Serah Terima Paket
- [x] FR-20: Setelah lunas dan `tanggal_boleh_cair` tercapai, nasabah dapat memilih metode pengambilan barang: ambil sendiri (ke kantor/rumah kolektor) atau diantar kolektor
- [x] FR-21: Setiap pencairan paket wajib dicatat status serah terima (belum/sudah diterima), termasuk siapa yang mengonfirmasi dan kapan

### 4.6 Kolektor & Penugasan
- FR-22: Admin dapat menugaskan (assign) sejumlah nasabah ke kolektor tertentu, dengan riwayat penugasan tersimpan (termasuk histori jika terjadi perpindahan)
- [x] FR-23: Kolektor memiliki halaman jadwal kunjungan harian dengan status per kunjungan (dikunjungi/dilewati/nasabah tidak ada)
- FR-24: Ketika kolektor resign/pindah wilayah, sistem menyediakan alur handover: kas yang masih dipegang wajib disetor lunas terlebih dahulu, lalu seluruh nasabah dialihkan ke kolektor pengganti

### 4.7 Rekonsiliasi Kas
- FR-25: Sistem otomatis mengakumulasi total kas yang seharusnya dipegang tiap kolektor (dari transaksi setoran yang sudah diinput namun belum disetor ke kantor)
- FR-26: Saat kolektor menyetor uang fisik ke kantor, admin menginput nominal yang diterima, dan sistem otomatis menampilkan selisih (jika ada) antara yang seharusnya dan yang diterima
- FR-27: Selisih wajib disertai keterangan sebelum status setoran ditandai selesai

### 4.8 Komplain
- FR-28: Nasabah dapat mengajukan komplain melalui web dengan kategori tertentu dan opsional menghubungkannya ke transaksi spesifik
- FR-29: Admin memproses komplain melalui antrian dengan status (baru/diproses/selesai) dan mencatat penyelesaiannya
- FR-30: Nasabah menerima notifikasi setiap ada perubahan status komplainnya

### 4.9 Notifikasi
- [x] FR-31: Sistem mengirim notifikasi ke nasabah (setoran tercatat, penarikan disetujui, reminder tunggakan, dll) melalui **dua kanal sekaligus**: WhatsApp otomatis dan notifikasi in-app

### 4.10 Laporan & Audit
- [x] FR-32: Admin dapat melihat dan mengekspor laporan (harian/bulanan) mencakup total setoran, penarikan, komisi, per kolektor, dan per paket (termasuk kalkulasi kebutuhan barang untuk pengadaan sembako)
- FR-33: Seluruh aksi penting (input, koreksi, approval, verifikasi, perubahan data) tercatat di log aktivitas dengan jejak siapa dan kapan

### 4.11 Penambahan Fitur (Oktober 2026 — keputusan D13–D22)
- [x] FR-34: **Nasabah mode offline** — nasabah tanpa HP/tanpa aplikasi tetap punya akun, saldo, riwayat, dan kepesertaan paket; `mode_akses = offline` (login ditolak, tanpa notifikasi WA/in-app), seluruh pencatatan oleh kolektor/admin, termasuk penarikan tunai yang dicatat langsung `selesai` (di bawah ambang persetujuan ganda) dan konversi mode dua arah oleh admin — tes: `NasabahOfflineAkunTest`, `NoHpNullSafetyTest`, `NotifikasiOfflineD14Test`, `PenarikanOfflineLangsungD14Test`, `KonversiModeOfflineD14Test`, `RekapMutasiNasabahTest`
- [x] FR-35: **Kas kolektor dikurangi penarikan tunai** — kas di tangan = setoran belum disetor − penarikan tunai (`nominal_diterima`) yang dibayarkan kolektor dan belum direkonsiliasi; kas tidak cukup ditolak kecuali `izinkan_kas_minus`; `total_seharusnya` setor kantor ikut dikurangi; komisi tetap di kas — tes: `KasPenarikanTunaiTest`
- [x] FR-36: **Komitmen paket** — kepesertaan tidak lagi dibuat otomatis saat setoran; nasabah memilih paket sendiri (konfirmasi PIN + teks komitmen) atau didaftarkan kolektor/admin (catatan persetujuan); setoran ke paket mensyaratkan kepesertaan aktif; gabung terlambat memakai `totalHariKepesertaan()`; status komitmen tampil di progres & detail admin — tes: `SetoranWajibKepesertaanTest`, `PilihPaketTest`, `DaftarkanKePaketTest`, `StatusKomitmenUiTest`, `TargetKepesertaanGabungTerlambatTest`
- [x] FR-37: **Login portal terpisah** — `/login` (nasabah), `/login/kolektor`, `/login/admin`; role tidak cocok portal ditolak dengan pesan generik anti-enumerasi dan tercatat di log; logout kembali ke portal asal; pembeda visual per portal (warna, chip peran, judul tab, manifest per portal) — tes: `LoginPortalTerpisahD19Test`, `PembedaVisualPortalD19Test`
- [x] FR-38: **Ekspor komisi & laporan lengkap** — halaman komisi memiliki filter (status, lokasi, kolektor, dasar tanggal), rekap per bulan, ekspor CSV (aman formula-injection), dan cetak; laporan admin bertambah mode rentang tanggal serta seksi rekonsiliasi kas, umur kas, mutasi nasabah, penarikan, tunggakan, serah terima, absensi/izin, dan nasabah — tes: `KomisiEksporD21Test`, `LaporanPeriodeRentangTest`, `LaporanRekonUmurKasTest`, `LaporanMutasiPenarikanTest`, `LaporanTunggakanSerahTest`, `LaporanAbsensiNasabahTest`
- [x] FR-39: **Pembatalan penarikan oleh nasabah** — boleh untuk `pending` (termasuk fase-1 approval ganda) dan `approved` (saldo dikembalikan dalam transaksi berlock); `selesai`/`ditolak` ditolak; log + notifikasi admin/kolektor penanggung jawab; penarikan `dibatalkan`/kedaluwarsa tidak masuk komisi/laporan — tes: `BatalkanPenarikanD18Test`
- [x] FR-40: **APK (Capacitor) & PWA** — proyek `mobile/` tiga flavor (`nasabah`/`kolektor`/`admin`) + workflow GitHub Actions build APK + PWA dasar (manifest per portal, service worker aset statis + halaman `/offline`, bundel Leaflet/signature_pad tanpa CDN) — tes: `ProyekCapacitorP85Test`, `WorkflowBuildApkP86Test`, `PwaDasarTest`, `HalamanOfflineP82Test`, `PrasyaratWebP81Test`, `AksesibilitasKameraP83Test`. **Catatan:** pengisian secrets/`APP_DOMAIN`, sekali build CI, dan uji perangkat Android nyata masih menunggu pemilik (lihat `docs/apk.md`).

---

## 5. User Stories Ringkas

- *Sebagai nasabah*, saya ingin melihat saldo tabungan saya kapan saja tanpa harus menunggu kolektor datang, supaya saya percaya uang saya tercatat dengan benar.
- *Sebagai nasabah peserta paket*, saya ingin tahu berapa tunggakan saya jika bolong menabung, supaya saya bisa segera melunasinya.
- *Sebagai kolektor*, saya ingin mencatat setoran langsung dari HP saat berkunjung, atau menyusulkannya nanti dari catatan manual, supaya fleksibel sesuai kondisi lapangan.
- *Sebagai kolektor*, saya ingin melihat daftar nasabah dan jadwal kunjungan harian saya, supaya kerja saya lebih terarah.
- *Sebagai admin*, saya ingin memverifikasi nasabah baru yang didaftarkan kolektor, supaya tidak ada data nasabah fiktif.
- *Sebagai admin*, saya ingin mencocokkan kas fisik yang disetor kolektor dengan catatan sistem, supaya tidak ada uang yang hilang di lapangan.
- *Sebagai admin*, saya ingin melihat daftar nasabah dengan tunggakan paket yang sudah melewati batas, supaya saya bisa menindaklanjutinya sebelum mepet Lebaran.

---

## 6. Metrik Keberhasilan (Success Metrics) — Usulan Awal

- % transaksi setoran yang tercatat tanpa perlu koreksi admin (indikator akurasi input lapangan)
- Waktu rata-rata dari pengajuan penarikan sampai approval
- % kas kolektor yang cocok tanpa selisih saat rekonsiliasi
- % nasabah paket yang berhasil lunas sebelum tanggal cair (tanpa perlu review khusus)
- Waktu rata-rata penyelesaian komplain nasabah

---

## 7. Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Kolektor input setoran tapi tidak menyetor uang fisik ke kantor | Rekonsiliasi kas wajib + dashboard "kas di tangan kolektor" real-time untuk admin |
| Kolektor resign mendadak, data/kas menggantung | Alur handover wajib melunasi kas sebelum status kolektor dinonaktifkan |
| Nasabah tidak sanggup melunasi tunggakan paket mendekati Lebaran | Alert dini (batas toleransi tunggakan) + review manual admin sejak awal, bukan menunggu mepet |
| Sengketa "saya belum terima barang paket" | Pencatatan status serah terima wajib, dengan opsi bukti foto |
| Data nasabah palsu dari kolektor | Wajib verifikasi admin untuk pendaftaran via kolektor |

---

## 8. Item yang Masih Perlu Diputuskan (Open Questions)

- Provider & skema biaya integrasi WhatsApp Business API
- Kebijakan final dana nasabah yang dinyatakan gagal ikut paket (kembali vs dialihkan)
- Perlakuan kelebihan bayar (overpayment) pada cicilan paket
- Kebutuhan legalitas/badan usaha jika skala bisnis membesar

---

## 9. Fase Pengembangan Lanjutan (Backlog, Belum Diprioritaskan)

- Struk fisik untuk nasabah yang kurang familiar dengan aplikasi
- Alur renewal otomatis untuk paket tahun berikutnya
- Mode offline pada aplikasi kolektor (input tersimpan lokal, sync saat online kembali)