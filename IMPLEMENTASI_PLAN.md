# Implementation Plan — Catatan Terbaru (Okt 2026)

**Proyek:** Sistem Tabungan Digital — Kolektor Keliling (`Mobile_Savings`)
**Stack:** Laravel 13 · Livewire 4 · Flux UI (gratis, **bukan Pro**) · Fortify · spatie/laravel-permission · Pest 4 · MySQL (SQLite tidak didukung: `lockForUpdate`)
**Untuk:** AI agent pelaksana. Dokumen ini berdiri sendiri; tetap baca `AGENTS.md`, `CLAUDE.md`, `prd.md`, `docs/plan-log.md` (terutama P8.x) sebelum mulai.
**Sumber:** 13 catatan pemilik (Okt 2026) + audit kode pada zip `Mobile_Savings.zip`.

> Catatan penyusun: plan ini disusun lewat pembacaan kode, **tanpa menjalankan aplikasi atau tes** (zip tidak menyertakan `vendor/`). Semua klaim "sudah ada" di bawah berasal dari membaca kode; agent wajib memverifikasinya lagi dengan menjalankan tes sebelum mengubah apa pun.

---

## 0. Ringkasan Eksekutif

| # | Catatan pemilik | Status awal | Fase |
|---|---|---|---|
| 1 | Nasabah tanpa HP / tanpa aplikasi tetap bisa dicatat digital, tabungan tetap manual | Belum | P2 |
| 2 | Logika paket: harga barang di admin, nasabah hanya lihat nama barang + progres | Belum | P6 |
| 3 | Paket hanya cair bila waktunya tiba / target tercapai | Sebagian | P6 |
| 4 | Nasabah memilih paket sendiri di awal dan berkomitmen | Belum (kepesertaan dibuat otomatis saat setoran pertama) | P6 |
| 5 | Serah terima wajib foto bukti | **Sudah** (verifikasi + perkuat) | P6 |
| 6 | Kas di kolektor berkurang saat penarikan tunai; nasabah non-digital dicatat offline tanpa verifikasi | Belum (`TODO(D4)`) | P1 + P2 |
| 7 | Login admin / kolektor / nasabah dibedakan | Belum (satu form) | P3 |
| 8 | APK nasabah dan kolektor | Sebagian (PWA ada, APK belum) | P8 |
| 9 | Versi admin mobile (keuangan + persetujuan) | Belum | P7 |
| 10 | Fitur penarikan (ekspor) data komisi | Sebagian (halaman komisi ada) | P5 |
| 11 | Laporan lengkap | Sebagian | P5 |
| 12 | Semua kalender → pemilih tabel kalender | Belum (20 input native) | P4 |
| 13 | Tombol batalkan penarikan dari nasabah | **Sebagian** (tombol ada di riwayat, hanya status `pending`) | P1 |

**Urutan kerja (jangan diubah tanpa alasan, ada dependensi):**
`P0 baseline → P1 kas + batal penarikan → P2 nasabah offline → P3 login portal → P4 kalender → P5 komisi + laporan → P6 paket → P7 admin mobile → P8 APK → P9 penutup`

Satu fase = satu branch/PR kecil (`feat/okt26-p1-kas`, dst.), diuji penuh sebelum lanjut.

---

## 1. Aturan Kerja Agent (wajib)

1. **TDD gaya proyek ini:** tulis tes dulu, pastikan RED, baru implementasi sampai GREEN. Catat tiap tugas di `docs/plan-log.md` dengan format yang sama dengan entri P8.x (RED/GREEN, file yang diubah, hasil verifikasi, commit).
2. **Gerbang CI sebelum tiap commit:** `composer ci:check` (config:clear → pint → phpstan → `php artisan test`). Suite harus tetap hijau (terakhir tercatat 488/488). **Jangan menambah entri baru ke `phpstan-baseline.neon`**; baseline hanya boleh turun.
3. **Uang:** selalu `DB::transaction` + `lockForUpdate()` pada baris saldo/penarikan/setoran yang disentuh; hitung dengan **bcmath** (`bcadd/bcsub/bccomp`, skala 2) seperti `AjukanPenarikanAction`; tidak boleh float untuk logika penentu.
4. **Audit:** setiap perubahan status/nominal memanggil `ActivityLogger::log(...)`; notifikasi lewat `ActivityLogger::notify(...)` dibungkus `try/catch` + `report($e)` (pola yang sudah ada), tidak boleh menggagalkan transaksi uang.
5. **Otorisasi:** komponen Livewire memakai trait `AuthorizesRole`; kolektor hanya boleh menyentuh nasabah binaannya (`ValidatesKolektorNasabah`, `KolektorIdorTest`). Setiap fitur baru yang menerima ID dari klien wajib punya tes IDOR.
6. **Bahasa:** seluruh teks UI, pesan error, notifikasi, dan nama kolom baru memakai Bahasa Indonesia (konsisten dengan `transaksi_penarikan`, `kepesertaan_paket`, dst.). Zona waktu `Asia/Jakarta`.
7. **Migrasi:** harus reversible (`down()` lengkap), idempoten untuk backfill, dan aman dijalankan pada salinan data produksi. Jangan mengubah migrasi lama yang sudah ada; buat migrasi baru bertanggal 2026_10_xx.
8. **Artefak zip:** file `resources/views/pages/settings/#U26a1*.blade.php` di zip adalah korupsi nama akibat kompresi Windows. Di repo asli namanya `⚡*.blade.php`. **Jangan di-rename atau dihapus** dari zip yang diterima; kerjakan di repo git asli bila tersedia. Jangan sertakan `bootstrap/cache/*.php` dalam artefak.
9. **Livewire 4 / Flux / Tailwind 4:** ikuti `.agents/skills/*` (livewire-development, fluxui-development, tailwindcss-development, laravel-best-practices) dan gunakan tool dokumentasi Boost bila tersedia. Jangan menebak sintaks `entangle`/event Livewire 4.
10. **Jangan bertanya ke pemilik di tengah jalan.** Bila ada keputusan yang belum jelas, terapkan **nilai default** pada Bagian 2, jadikan bisa diubah lewat `AdminSetting`, dan tulis di `docs/plan-log.md` di bawah "Keputusan yang diambil agent".

---

## 2. Keputusan Desain (D13–D22) — default yang berlaku

Penomoran melanjutkan D4/D10/D11/D12 yang sudah ada.

**D13 — Kas kolektor dikurangi penarikan tunai (menutup `TODO(D4)`).**
`kas_di_tangan(k) = Σ setoran belum disetor (existing) − Σ nominal_diterima penarikan tunai yang dibayarkan kolektor k dan belum direkonsiliasi`.
Komisi (`nominal_komisi`) **tidak** keluar dari kas: uangnya tetap ada di tangan kolektor dan ikut disetor ke kantor. `SetoranKolektorKantor.total_seharusnya = Σ setoran tertaut − Σ penarikan tunai tertaut`.
Bila kas tidak cukup untuk membayar penarikan tunai: **ditolak**, kecuali `AdminSetting izinkan_kas_minus = true` (default `false`).

**D14 — Nasabah mode offline.**
`users.mode_akses ∈ {digital, offline}`. Nasabah offline: tidak punya HP/aplikasi, tidak bisa login, tidak menerima notifikasi WA/in-app, tetap punya akun, saldo, riwayat, dan kepesertaan paket. Seluruh pencatatan dilakukan kolektor/admin. Pendaftaran oleh kolektor tetap `pending_verifikasi` (FR-2). **Penarikan** oleh nasabah offline dicatat kolektor langsung berstatus `selesai` tanpa verifikasi PIN (sesuai catatan "tanpa verifikasi"), **kecuali** nominal melewati ambang "penarikan besar" yang sudah dipakai `ApprovalPenarikan::perluDuaApprover()` → ikut alur approval admin biasa. Dikendalikan `AdminSetting offline_penarikan_langsung_selesai` (default `true`).

**D15 — Harga barang paket.**
Setiap item `isi_paket` mendapat `harga` (total rupiah baris itu, bukan harga satuan, agar tidak perlu mem-parse `jumlah` yang berupa teks bebas). Harga **hanya terlihat admin**. Nasabah melihat nama + jumlah + status progres. Produk punya opsi `tampilkan_harga_ke_nasabah` (default `false`). Uang tidak benar-benar dibagi per barang; ini hanya penanda progres (alokasi berurutan terhadap `total_aktual_terkumpul`).

**D16 — Komitmen paket.**
Nasabah memilih paket sendiri (konfirmasi PIN + centang komitmen) atau didaftarkan kolektor/admin atas nama nasabah (centang "nasabah telah setuju"). Kepesertaan **tidak lagi dibuat otomatis** oleh setoran. Keluar di tengah jalan hanya lewat admin (`ProsesKegagalanPaketAction` yang sudah ada).

**D17 — Gerbang pencairan paket.**
Boleh cair bila: komitmen ada **dan** belum diserahterimakan **dan** tunggakan = 0 **dan** (`today ≥ tanggal_boleh_cair` **atau** (`produk.boleh_cair_saat_target = true` **dan** total terkumpul ≥ target)). Default `boleh_cair_saat_target = false` (perilaku sekarang dipertahankan). Satu fungsi tunggal dipakai semua tempat.

**D18 — Pembatalan penarikan oleh nasabah.**
Boleh saat status `pending` (termasuk sudah disetujui fase-1 pada approval ganda) **dan** `approved` selama belum `selesai`. Untuk `approved`, saldo sudah dipotong saat approve final (`ApprovalPenarikan::approve` → `$saldo->decrement`), maka pembatalan **wajib mengembalikan saldo** dalam transaksi yang sama.

**D19 — Login portal terpisah.**
Tiga halaman: `/login` (nasabah), `/login/kolektor`, `/login/admin`. POST tetap `login.store` (Fortify) dengan field tersembunyi `portal`. Role tidak cocok portal → ditolak dengan pesan generik yang sama dengan PIN salah (anti-enumerasi) dan dicatat di log.

**D20 — APK = Capacitor WebView, 3 varian** (`nasabah`, `kolektor`, `admin`), memuat server produksi lewat HTTPS. Build lewat GitHub Actions (runner Ubuntu sudah berisi Android SDK) karena agent/sandbox mungkin tidak punya SDK.

**D21 — "Penarikan data komisi" = ekspor data komisi** (CSV + cetak/PDF via print CSS) dengan filter. Bila pemilik ternyata bermaksud pencairan komisi ke seseorang, itu pekerjaan terpisah di luar plan ini; catat di plan-log sebagai pertanyaan terbuka.

**D22 — Kalender memakai flatpickr** (lokal via npm, locale `id`), dibungkus komponen Blade `<x-ui.tanggal>`. Alasan: `flux:date-picker` adalah Flux Pro dan `livewire/flux-pro` tidak ada di `composer.json`.

---

## 3. P0 — Baseline & Persiapan

1. Buat branch `feat/okt26` dari HEAD. Jalankan `composer setup` lalu `composer ci:check`; **catat angka baseline** (jumlah tes, error PHPStan) di `docs/plan-log.md` seksi baru "P9.x — Catatan Okt 2026".
2. Siapkan DB `tabungan_digital_test` (lihat README). Pastikan `.env` memakai MySQL.
3. Baca ulang: `app/Actions/Penarikan/*`, `app/Actions/Setoran/CatatSetoranAction.php`, `app/Actions/Tabungan/HitungTunggakanAction.php`, `app/Models/{KepesertaanPaket,TransaksiSetoran,SetoranKolektorKantor,TransaksiPenarikan}.php`, `app/Livewire/Admin/{ApprovalPenarikan,KasKolektor,RekonsiliasiKas,SerahTerimaPaket,Laporan,Komisi}.php`, `app/Livewire/Kolektor/{SetorKantor,PenarikanOffline,VerifikasiPenarikan}.php`.
4. Kerangka tes bersama (taruh di `tests/Pest.php` atau helper): factory/state untuk `nasabahOffline()`, `kolektorDenganKas($nominal)`, `paketDenganBarang([...])`.

**Selesai bila:** baseline hijau tercatat; tidak ada perubahan kode.

---

## 4. P1 — Kas Kolektor (D13) + Pembatalan Penarikan (D18)  [catatan #6a, #13]

### 4.1 Skema (satu migrasi)
`transaksi_penarikan`:
- `dibayar_oleh` unsignedBigInteger nullable FK → `users` (kolektor yang membayar tunai)
- `mempengaruhi_kas` boolean default `false`
- `setoran_kolektor_id` nullable FK → `setoran_kolektor_kantor` (indeks)
- `dibatalkan_oleh` nullable FK → `users`, `alasan_batal` string(255) nullable, `waktu_dibatalkan` timestamp nullable

**Backfill (idempoten):** penarikan historis `selesai` + `lokasi_pengambilan = 'rumah_kolektor'` → `dibayar_oleh = diverifikasi_oleh`, **`mempengaruhi_kas = false`**. Alasan: kolektor sudah menyetor penuh pada periode lalu; menghitung mundur akan membuat kas semua kolektor tiba-tiba "minus". Tulis ini di README.

Tambah `AdminSetting`: `izinkan_kas_minus` (default `false`).

### 4.2 Logika kas
1. Buat satu sumber kebenaran, mis. `app/Support/KasKolektorHitung.php` (atau metode statis di `TransaksiSetoran`/`SetoranKolektorKantor`):
   - `tunaiKeluarBelumDirekonsiliasi(int $kolektorId): string` = `SUM(nominal_diterima)` dari penarikan `status='selesai' AND lokasi_pengambilan='rumah_kolektor' AND mempengaruhi_kas=1 AND setoran_kolektor_id IS NULL AND dibayar_oleh=:k`.
   - `kasDiTangan(int $kolektorId): string` = setoran belum diterima admin − tunai keluar belum direkonsiliasi.
2. Pakai sumber tunggal ini di **semua** tempat yang kini menghitung kas (cari dengan `grep -rn "teragregasiPerKolektor\|belumDisetor\|total_belum_disetor\|hasUnsettledCash\|ringkasUntukDashboard"`): `TransaksiSetoran::teragregasiPerKolektor`, `Admin\KasKolektor::kumpulkanKas`, dashboard admin/kolektor (`DashboardController`), `User::hasUnsettledCash()` (handover & penguncian kolektor), `Admin\HandoverKolektor`, `Admin\RekonsiliasiKas`, `Admin\Laporan::kolektorRows`.
3. `VerifikasiPenarikanOfflineAction` (jalur PIN nasabah) dan jalur baru P2 (tanpa PIN) — di dalam transaksi berlock, saat menandai `selesai` untuk `lokasi_pengambilan='rumah_kolektor'`: kunci baris kolektor/setoran terkait, hitung `kasDiTangan`; bila `< nominal_diterima` dan `izinkan_kas_minus=false` → `throw` pesan: *"Kas di tangan Anda (Rp X) tidak cukup untuk membayar Rp Y. Pilih pengambilan di kantor atau setor/hubungi admin."*; bila cukup → set `dibayar_oleh`, `mempengaruhi_kas=true`. Log `kas_berkurang_penarikan_tunai` dengan kas sebelum/sesudah.
4. `Kolektor\SetorKantor::submit()` (ganti `TODO(D4)`): dalam transaksi yang sama kunci dan tautkan seluruh penarikan tunai yang memenuhi syarat (set `setoran_kolektor_id`), `total_seharusnya = Σ setoran − Σ nominal_diterima`. Bila total ≤ 0 setelah pengurangan, tetap izinkan (admin tinggal mencatat 0) namun tampilkan rinciannya.
5. Bila ada pengajuan setor `pending` **lalu** terjadi penarikan tunai baru: dalam transaksi pembayaran, tautkan penarikan itu ke setoran pending tersebut dan perbarui `total_seharusnya` (record masih bisa diubah selama `pending`). Dengan begitu uang fisik yang diserahkan = angka yang diharapkan admin.
6. `SetorKantor::batalkan()`: selain melepas `setoran_kolektor_id` pada `transaksi_setoran`, lepas juga pada `transaksi_penarikan`.
7. Tampilan: halaman kolektor "Setor Kantor" dan admin "Rekonsiliasi" menampilkan rincian: *Setoran masuk (+)*, *Penarikan tunai dibayar (−)*, *Total seharusnya*. Halaman admin "Kas Kolektor" menampilkan kolom "Penarikan tunai (−)".
8. Hapus catatan D4 di README bagian "Catatan Fitur & Risiko Terbuka"; ganti dengan penjelasan D13 + rumus + keputusan backfill.

### 4.3 Pembatalan penarikan (D18)
1. Satukan logika ke `app/Actions/Penarikan/BatalkanPenarikanAction.php` (`execute(TransaksiPenarikan|int $penarikan, User $pelaku, ?string $alasan): TransaksiPenarikan`), dipakai oleh `Nasabah\RiwayatPenarikan::batalkan()` yang sekarang menulis inline.
2. Aturan, semua di `DB::transaction` dengan `lockForUpdate` pada baris penarikan **lalu** `SaldoProduk`:
   - Hanya pemilik (`nasabah_id = Auth::id()`); status harus `pending` atau `approved`; selain itu → pesan "tidak dapat dibatalkan".
   - `pending` (termasuk `disetujui_oleh` fase-1 terisi): hanya ubah status. Saldo belum dipotong.
   - `approved`: `SaldoProduk::increment('saldo', nominal_diminta)` + ubah status. Kolektor/admin yang sedang memproses wajib gagal dengan aman karena `VerifikasiPenarikanOfflineAction` dan `ApprovalPenarikan::selesai()` sudah mengecek status di dalam transaksi berlock — **tambahkan tes balapan** untuk keduanya.
   - Isi `dibatalkan_oleh`, `alasan_batal`, `waktu_dibatalkan`; bersihkan `terkunci_hingga`.
3. Notifikasi in-app ke admin (dan kolektor penanggung jawab bila `lokasi_pengambilan='rumah_kolektor'`) bahwa pengajuan dibatalkan. Log `batalkan_penarikan` dengan `status_sebelumnya` dan flag `saldo_dikembalikan`.
4. UI nasabah: tombol **"Batalkan Pengajuan"** + `wire:confirm` + input alasan opsional pada (a) Riwayat Penarikan (sudah ada, perluas ke `approved`), (b) halaman Ajukan Penarikan di kartu "Pengajuan aktif", (c) kartu di dashboard nasabah bila ada pengajuan aktif. Setelah dibatalkan, saldo tersedia langsung ter-update.
5. Pastikan `kedaluwarsakan` (command) dan komisi/laporan memperlakukan `dibatalkan` sebagai tidak berlaku. **Cek `Admin\Komisi::baseQuery()`**: sekarang menghitung `approved` dan `selesai`; penarikan `approved` yang kemudian dibatalkan harus keluar dari komisi (otomatis karena status berubah, namun tulis tesnya).

### 4.4 Tes P1 (minimal)
- Kas berkurang tepat `nominal_diterima` (bukan `nominal_diminta`) saat penarikan tunai selesai; komisi tetap di kas.
- Penarikan di kantor tidak mengubah kas kolektor.
- Kas tidak cukup → ditolak; dengan `izinkan_kas_minus=true` → diizinkan dan kas bisa minus + log.
- `total_seharusnya` setor kantor = setoran − penarikan tunai; penarikan terjadi saat ada setor `pending` ikut tertaut; batal setor melepas tautan.
- Historis (backfill) tidak mengubah kas.
- Dashboard admin/kolektor, `hasUnsettledCash`, handover, rekonsiliasi, laporan kolektor konsisten satu sama lain (satu fixture, semua angka sama).
- Pembatalan: `pending` (tanpa dan dengan fase-1), `approved` (saldo kembali tepat), `selesai`/`ditolak`/`dibatalkan` ditolak, bukan pemilik ditolak, balapan dengan approve/selesai/verifikasi, log + notifikasi.

**Selesai bila:** semua angka kas di seluruh halaman konsisten; `TODO(D4)` hilang; `composer ci:check` hijau.

---

## 5. P2 — Nasabah Mode Offline (D14)  [catatan #1, #6b]

### 5.1 Skema
- `users.mode_akses` enum(`digital`,`offline`) default `digital`, indeks.
- `users.no_hp` → **nullable** (unique tetap; MySQL mengizinkan banyak NULL). `pin_hash` tetap NOT NULL: untuk akun offline isi `Hash::make(Str::random(40))` (tidak bisa dipakai login).
- `nasabah_profil.catatan_offline` text nullable (mis. "buku tabungan fisik no. 12").
- `transaksi_penarikan.jalur_pengajuan`: pastikan nilai `offline_kolektor` valid (cek tipe kolom/enum; ubah lewat migrasi baru bila enum). `metode_verifikasi`: nilai baru `tanpa_verifikasi_offline`.
- `AdminSetting offline_penarikan_langsung_selesai` default `true`.

### 5.2 Audit semua pemakaian `no_hp` agar null-safe
`grep -rn "no_hp\|noHp\|NomorHp" app resources database tests`. Minimal: `User::noHp()` attribute, `NomorHp::normalize`, `Console\Commands\NormalisasiNomorHp`, `WhatsAppService`, `Jobs\KirimNotifikasiWhatsApp`, `Jobs\KirimPinAwalWhatsApp`, `Actions\Pin\KirimPinAwalAction`, `ResetPinOlehAdminAction`, form registrasi (admin & kolektor), `Admin\Search`, daftar/detail nasabah, jadwal kunjungan, struk setoran. Tampilkan "— (offline)" bila kosong.

### 5.3 Perilaku
1. **Login ditolak** untuk `mode_akses='offline'` di `FortifyServiceProvider::authenticateUsing` (jalankan `Hash::check` dummy agar waktu respons sama, pesan generik).
2. **Registrasi** (`Admin\RegistrasiNasabah`, `Kolektor\DaftarNasabah`): toggle **"Nasabah tidak memakai aplikasi / tidak punya HP"**; bila aktif sembunyikan No. HP & PIN awal, tidak mengirim PIN WA, set `mode_akses='offline'`. Kolektor → tetap `pending_verifikasi`; admin → `aktif`.
3. **Notifikasi:** `ActivityLogger::notify` dan job WA melewati nasabah offline (catat di `log_notifikasi` dengan kanal `none` bila tabel mewajibkan baris; jangan antre WA).
4. **Setoran** nasabah offline: alur normal kolektor/admin (`CatatSetoranAction`), tanpa notifikasi; struk tetap bisa dicetak (`StrukSetoranController`).
5. **Penarikan offline** (`Kolektor\PenarikanOffline` + action baru `CatatPenarikanOfflineLangsungAction`): untuk nasabah offline yang menjadi tanggung jawab kolektor (cek `KolektorNasabah` aktif):
   - Validasi saldo (`lockForUpdate`), minimal penarikan, gerbang paket (D17), tunggakan.
   - Bila `offline_penarikan_langsung_selesai=true` **dan** nominal tidak melewati ambang `perluDuaApprover` → buat penarikan langsung `selesai` (`jalur_pengajuan='offline_kolektor'`, `metode_verifikasi='tanpa_verifikasi_offline'`, `diverifikasi_oleh=dibayar_oleh=kolektor`, `waktu_approval=waktu_pencairan=now()`), potong saldo, kurangi kas (D13), log, notifikasi ke **admin** (bukan nasabah).
   - Selain itu → masuk antrian approval admin seperti biasa.
   - Form wajib berisi catatan (mis. "buku tabungan dicoret, tanda tangan di buku").
6. **Admin** bisa mengubah mode: offline→digital (isi No. HP unik + kirim PIN awal via `KirimPinAwalAction`, set `harus_ganti_pin`) dan digital→offline (hapus sesi: `DB::table('sessions')->where('user_id', ...)->delete()`, nonaktifkan notifikasi). Keduanya dilog.
7. Lencana **"Offline"** di daftar nasabah admin & kolektor, filter mode, dan hitungan di dashboard/laporan.
8. Cetak **rekap mutasi** per nasabah (halaman print `/rekap/nasabah/{user}`, hanya admin & kolektor penanggung jawab) untuk ditempel/di-update di buku fisik — gunakan print CSS, tanpa dependensi baru.

### 5.4 Tes P2
Login offline ditolak (dan waktu/pesan sama dengan PIN salah); registrasi offline tanpa HP oleh admin & kolektor; dua nasabah offline (no_hp NULL ganda) tidak bentrok unik; tidak ada job WA yang masuk antrian; penarikan langsung (di bawah ambang) memotong saldo + kas + log; di atas ambang masuk approval; kolektor non-penanggung-jawab ditolak (IDOR); konversi mode dua arah; semua halaman daftar/detail/struk render tanpa error saat `no_hp` NULL.

**Selesai bila:** satu alur uji manual end-to-end: daftar nasabah offline → setoran → penarikan tunai → kas kolektor berkurang → setor kantor cocok.

---

## 6. P3 — Login Portal Terpisah (D19)  [catatan #7]

1. **Route & view:** `GET /login` (nasabah, default), `GET /login/kolektor`, `GET /login/admin`. Satu Blade `pages/auth/login.blade.php` diparametrisasi `$portal` (judul, ikon, warna aksen, teks bantuan) — atur lewat `Fortify::loginView` di `FortifyServiceProvider::configureViews()`, pilih portal dari route. Tambahkan field `<input type="hidden" name="portal" value="...">`.
2. **Validasi di `authenticateUsing`:** setelah PIN valid, bila `$user->role !== $request->input('portal', 'nasabah')` → `return null` (log `login_portal_salah` dengan role & portal; tetap hitung sebagai gagal pada rate limiter IP). Tidak membuka info bahwa akun ada di portal lain.
3. **Pasca-login:** arahkan sesuai role (sudah ditangani `DashboardController`). **Logout** kembali ke halaman login portal yang sama (simpan `portal` di sesi; bersihkan saat logout). `EnsureAccountIsActive` (`redirect()->route('login')`) → kirim ke portal sesuai role pengguna terakhir bila diketahui.
4. **Pembeda visual status** (agar tidak tertukar): warna & label tetap per portal — admin (gelap), kolektor (biru), nasabah (hijau); chip peran di header `layouts.mobile`, `layouts.app`, `components/admin/topbar`/`profile-chip`; judul tab browser berisi peran.
5. **Landing (`/`)**: tiga tombol "Masuk Nasabah / Kolektor / Admin" (CTA lama "Masuk Sekarang" diganti; ingat: nasabah tidak bisa daftar sendiri, FR-1).
6. **Manifest per portal** (dipakai P8): `public/manifest-nasabah.webmanifest`, `manifest-kolektor.webmanifest`, `manifest-admin.webmanifest` dengan `name`, `short_name`, `theme_color`, `start_url` (`/login`, `/login/kolektor`, `/login/admin`) berbeda; tautkan di `partials/head` sesuai peran/portal. Pertahankan `manifest.webmanifest` lama sebagai fallback.
7. Perbarui halaman bantuan: tautan "ke portal lain" kecil di bawah form.

**Tes P3:** matriks 3 role × 3 portal (hanya diagonal berhasil); kunci akun/`LoginLockoutTest` tetap bekerja di tiap portal; `harus_ganti_pin` tetap mengalihkan; semua tes login lama diperbarui (kirim `portal`); `RouteBladeSinkronTest` tetap hijau; akun offline ditolak di semua portal.

**Selesai bila:** akun admin tidak bisa masuk dari form nasabah dan sebaliknya, tanpa membocorkan keberadaan akun.

## 7. P4 — Pemilih Kalender di Semua Input Tanggal (D22)  [catatan #12]

### 7.1 Komponen
1. `npm i flatpickr` (+ `monthSelect` plugin bawaan flatpickr). Impor **lazy** (dynamic import) di `resources/js/app.js`; CSS di `resources/css/app.css`. Locale `id`. Tema terang/gelap mengikuti kelas `dark` (Flux) dan palet `--color-brand-*`. Build memakai `npm run build` (`vp build`).
2. Komponen anonim `resources/views/components/ui/tanggal.blade.php`:
   - Props: `mode` (`tanggal` | `bulan` | `rentang`), `min`, `max`, `placeholder`, `label`, `wajib`; atribut `wire:model[.live]` diteruskan.
   - Nilai ke server **tetap** `Y-m-d` (tanggal), `Y-m` (bulan), sehingga validasi dan query yang ada tidak berubah. Tampilan ke pengguna `j F Y` / `F Y` (Bahasa Indonesia).
   - `disableMobile: true` agar tabel kalender selalu muncul (bukan picker bawaan HP). Minggu mulai Senin. Tombol "Hari ini" dan "Hapus".
   - Pembungkus `wire:ignore` + sinkronisasi dua arah lewat Alpine sesuai panduan Livewire 4 di `.agents/skills/livewire-development` (jangan memakai `@entangle` lama bila sudah usang). Tangani `wire:model.live` (filter) dan update dari server (mis. tombol "Reset filter" harus mengosongkan picker).
   - Aksesibel: label terhubung, bisa diketik manual dengan format valid, fokus keyboard.
3. Untuk tanggal lahir: dropdown tahun/bulan (flatpickr `monthSelectorType: 'dropdown'`) dan `max=today`.

### 7.2 Ganti semua input (hasil grep awal: 20 input di 14 file — **jalankan ulang** `grep -rn 'type="date"\|type="month"\|datetime-local' resources/views`)
| File | Input | Batas |
|---|---|---|
| `livewire/admin/manajemen-produk` | `periode_mulai`, `periode_selesai`, `tanggal_boleh_cair` | selesai ≥ mulai |
| `livewire/admin/nasabah-bermasalah` | `ditunda_hingga` | min hari ini |
| `livewire/admin/laporan` | `tanggal` (tanggal), `bulan` (bulan) + rentang baru (P5) | max hari ini |
| `livewire/admin/monitoring-absensi`, `monitoring-setoran` | filter tanggal | — |
| `livewire/admin/komisi` | `dariTanggal`, `sampaiTanggal` | sampai ≥ dari |
| `livewire/admin/registrasi-nasabah`, `livewire/kolektor/daftar-nasabah` | `tanggalLahir` | max hari ini |
| `livewire/admin/serah-terima-paket` | `tanggalSerahTerima` | max hari ini |
| `livewire/kolektor/ajukan-izin` | `tanggalMulai`, `tanggalSelesai` | min hari ini; selesai ≥ mulai |
| `livewire/kolektor/input-setoran` | `tanggal_transaksi` | max hari ini (backdate sesuai FR-6) |
| `livewire/kolektor/jadwal-kunjungan` | `tanggal` | — |
| `livewire/nasabah/riwayat-tabungan` | `dariTanggal`, `sampaiTanggal` | — |
| `pages/settings/⚡profile` | `tanggalLahir` | max hari ini |
Termasuk input tanggal pada form baru P5/P6 (`batas_daftar_hingga`, rentang laporan).

### 7.3 Tes
- Tes "penjaga" (pola `RouteBladeSinkronTest`): gagal bila ada `type="date"`, `type="month"` atau `datetime-local` di `resources/views` di luar komponen `x-ui.tanggal`.
- Tes komponen: render menghasilkan atribut yang diharapkan; nilai `Y-m-d` dari server tampil benar; validasi server (`before_or_equal`, `after_or_equal`) tetap menolak tanggal di luar batas (jangan hanya mengandalkan `min/max` klien).
- Uji manual (daftar di plan-log): pilih, ketik, hapus, reset filter, dark mode, layar 360 px, iOS Safari & Chrome Android.

**Selesai bila:** tidak ada lagi picker bawaan browser di aplikasi.

---

## 8. P5 — Ekspor Komisi (D21) + Laporan Lengkap  [catatan #10, #11]

### 8.1 Komisi (`Admin\Komisi`)
1. Tambah `exportCsv(): StreamedResponse` yang **memakai `baseQuery()` yang sama** dengan tampilan (tidak boleh ada dua definisi filter). Pindahkan penulis CSV aman dari `Laporan` (closure `$safe` penangkal CSV/formula injection) ke `app/Support/CsvSafe.php` dan pakai di kedua tempat. Tambah BOM UTF-8 agar Excel membaca benar. Pakai `cursor()`/`lazy()` agar tidak memuat semua baris.
2. Kolom: tanggal approval, tanggal pencairan, nasabah, produk, jalur, lokasi, kolektor pembayar (`dibayar_oleh`), `nominal_diminta`, `persen_komisi_terpakai`, `nominal_komisi`, `nominal_diterima`, status. Baris total di akhir.
3. Filter tambahan: status (`approved`/`selesai`/keduanya), lokasi pengambilan, kolektor, dan dasar tanggal (`waktu_approval` default | `waktu_pencairan`). Rekap per produk dan per bulan.
4. Tombol **Cetak** (print CSS, tanpa dependensi) dan **Unduh CSV**. Hanya admin (`requiredRole`).
5. Log aktivitas `ekspor_komisi` (siapa, filter, jumlah baris).

### 8.2 Laporan (`Admin\Laporan`, sekarang: keuangan harian/bulanan, kolektor, paket, kebutuhan barang, ekspor CSV)
Tambah mode periode **rentang tanggal** (picker P4) dan katalog laporan berikut. Setiap laporan: tampilan layar ringkas, ekspor CSV, tombol cetak, filter, total, dan definisi angka dituliskan di UI (ikon info). Hitung di SQL teragregasi (`selectRaw` + `groupBy`), bukan loop N+1; tambahkan indeks bila `EXPLAIN` menunjukkan scan penuh.

| Laporan | Isi / definisi | Status |
|---|---|---|
| Keuangan harian/bulanan/rentang | setoran, penarikan (selesai), komisi, jumlah transaksi; `dibatalkan` dikecualikan | ada → tambah rentang |
| Per kolektor | setoran diinput, penarikan tunai dibayar, kas di tangan, setor kantor, selisih | ada → tambah kolom D13 |
| Per paket | peserta, progres, tunggakan, status serah terima | ada |
| Kebutuhan barang | peserta aktif × isi paket | ada (jangan memuat `harga` ke nasabah) |
| **Rekonsiliasi kas & selisih** | per kolektor & per setoran: seharusnya, diterima, selisih, keterangan, penerima | baru |
| **Umur kas** | kas di tangan per kolektor dikelompokkan 0–1, 2–3, >3 hari (selaras `batas()` di `KasKolektor`) | baru |
| **Mutasi nasabah / buku tabungan** | kronologi setoran/penarikan per produk + saldo berjalan, siap cetak (juga untuk nasabah offline) | baru |
| **Penarikan** | per status (pending/approved/selesai/ditolak/dibatalkan/kedaluwarsa), per lokasi, waktu proses, alasan batal | baru |
| **Tunggakan & paket gagal** | nasabah menunggak, hari/rupiah, status alert, keputusan akhir | baru |
| **Serah terima paket** | status, metode, penerima, tanggal, tautan foto bukti (hanya admin) | baru |
| **Absensi & izin kolektor** | kehadiran, jam masuk/keluar, izin | baru |
| **Nasabah** | aktif/pending/ditolak, digital vs offline, per kolektor | baru |
| Komisi | tautan ke halaman komisi (8.1) | baru |

Aturan lintas laporan: nominal sebagai desimal 2 digit; `Asia/Jakarta`; pembulatan hanya di tampilan; seluruh ekspor lolos `CsvSafe`; akses hanya admin; log `ekspor_laporan`.

### 8.3 Tes
Perluas `LaporanLengkapTest`/`LaporanTest`/`KomisiTest`: satu fixture besar → angka tiap laporan sama dengan penjumlahan manual; CSV tidak mengeksekusi formula (`=`, `+`, `-`, `@` di awal sel); filter & ekspor memakai himpunan baris yang sama; non-admin ditolak; penarikan `dibatalkan` tidak masuk komisi.

---

## 9. P6 — Paket: Harga Barang, Pilih & Komitmen, Gerbang Pencairan, Serah Terima  [catatan #2, #3, #4, #5]

### 9.1 Skema
- `produk_tabungan`: `tampilkan_harga_ke_nasabah` bool default `false`; `boleh_cair_saat_target` bool default `false`; `batas_daftar_hingga` date nullable.
- `isi_paket` (JSON, tanpa kolom baru): tiap item `{nama, jumlah, harga?}`; urutan array = urutan pemenuhan. Item "Uang Tunai" (yang sekarang ditambahkan otomatis oleh `ManajemenProduk`) ikut memakai `harga` = nominal tunai.
- `kepesertaan_paket`: `komitmen_disetujui_pada` datetime nullable, `komitmen_via` enum(`mandiri`,`kolektor`,`admin`,`migrasi`) nullable, `komitmen_dicatat_oleh` FK nullable, `komitmen_teks` text nullable (salinan teks yang disetujui), `komitmen_catatan` string nullable.
- **Backfill:** semua kepesertaan yang sudah ada → `komitmen_via='migrasi'`, `komitmen_disetujui_pada = created_at`.
- `AdminSetting teks_komitmen_paket` (bisa disunting admin; berikan default Bahasa Indonesia: nominal harian, tanggal cair, konsekuensi menunggak/berhenti).

### 9.2 Harga barang & progres (D15)
1. **Form produk** (`Admin\ManajemenProduk` + view): kolom `harga` per item (hanya admin), total harga dihitung langsung, bandingkan dengan `ProdukTabungan::targetAkhir()`; tampilkan **margin/selisih** dan peringatan bila total harga > target. Tombol naik/turun untuk urutan. Validasi `harga` numerik ≥ 0. Item lama tanpa `harga` tetap valid.
2. **Layanan progres:** `app/Actions/Paket/HitungProgresBarangAction.php` (atau service) menerima `KepesertaanPaket`, mengalokasikan `total_aktual_terkumpul` **berurutan** terhadap `harga` tiap item → `[{nama, jumlah, status: tercapai|berjalan|belum, persen}]` + persen keseluruhan. Bila ada item tanpa `harga` → jatuh ke bar persen keseluruhan saja (jangan menebak). Dua keluaran: `untukAdmin()` (dengan harga & sisa) dan `untukNasabah()` (tanpa `harga`, kecuali `tampilkan_harga_ke_nasabah`).
3. **Anti-bocor harga (kritis):**
   - `Nasabah\ProgresPaket` & dashboard nasabah: hitung di `render()`, **jangan** simpan array berisi harga di properti publik Livewire (properti publik ikut terkirim ke browser).
   - `LandingController` saat ini mem-`select` kolom `isi_paket` mentah ke halaman publik dan men-cache-nya → pakai aksesori `ProdukTabungan::isiPaketPublik()` yang membuang `harga`; naikkan kunci cache (`landing:produk:v2`) agar cache lama terhapus.
   - Notifikasi WA/in-app, struk, dan ekspor untuk nasabah tidak boleh memuat `harga`.
   - Tes: `assertDontSee` angka harga pada respons nasabah dan landing; `Livewire::test(...)->assertDontSee`/cek payload snapshot tidak memuat kunci `harga`.
4. Laporan "Kebutuhan barang" tetap memakai `jumlah`; tambah kolom estimasi biaya (harga × peserta) **hanya untuk admin**.

### 9.3 Pilih paket & komitmen (D16)
1. **Hapus pembuatan otomatis:** `HitungTunggakanAction::kepesertaanAktif(..., buatJikaBelumAda: true)` dipanggil `CatatSetoranAction`. Ubah: setoran ke produk tipe `paket` **mensyaratkan kepesertaan aktif** (`whereNull('keputusan_akhir')`); jika tidak ada → `DomainException('Nasabah belum terdaftar di paket ini. Daftarkan terlebih dahulu.')`. Perbarui semua pemanggil (setoran admin, setoran kolektor, koreksi/batal) dan tes lama.
2. **Halaman nasabah** `Nasabah\PilihPaket` (route `nasabah.paket.pilih`, entri di navigasi "Paket"): daftar paket aktif yang masih bisa diikuti (`status='aktif'`, belum lewat `periode_selesai`, `batas_daftar_hingga` null atau ≥ hari ini, belum diikuti), menampilkan harga per hari, periode, tanggal cair, isi paket (nama + jumlah saja), aturan tunggakan. Tombol **"Ikuti Paket"** → modal berisi teks komitmen + centang wajib + **konfirmasi PIN** (ikuti pola ber-rate-limit di `VerifikasiPenarikanOfflineAction`). Dalam transaksi: buat `KepesertaanPaket` (unik per nasabah+produk aktif), buat `SaldoProduk` bila belum ada, simpan `komitmen_*` (`via='mandiri'`, salinan teks), log `ikut_paket`, notifikasi.
3. **Pendaftaran oleh kolektor/admin** (untuk nasabah offline atau yang dibantu): aksi "Daftarkan ke Paket" di `Admin\DetailNasabah` dan `Kolektor\NasabahBinaan`; wajib centang "Nasabah telah menyatakan setuju" + catatan; `via='kolektor'|'admin'`, `komitmen_dicatat_oleh`. Kolektor hanya untuk nasabah binaannya.
4. **Gabung terlambat:** tambah `KepesertaanPaket::totalHariKepesertaan()` = hari dari `tanggal_mulai_ikut` s/d `periode_selesai`; `hitungHariBerjalan()` dibatasi oleh nilai ini (bukan seluruh periode), dan target kepesertaan = `harga_per_hari × totalHariKepesertaan()`. Tes: gabung hari ke-10 → target & tunggakan benar.
5. **Keluar:** nasabah tidak punya tombol keluar; tampilkan "Anda terikat komitmen paket ini". Admin memproses lewat `ProsesKegagalanPaketAction` yang ada (jangan diubah perilakunya kecuali bila terbukti bentrok).
6. Tampilkan status komitmen (tanggal, via) di halaman progres nasabah dan detail admin.

### 9.4 Gerbang pencairan tunggal (D17)
1. Tambah `KepesertaanPaket::statusPencairan(): array{boleh: bool, alasan: ?string, kode: string}` (kode: `belum_komitmen`, `sudah_diserahkan`, `ada_tunggakan`, `belum_waktunya`, `ok`) dengan rumus D17; selalu panggil `hitungUlangKepesertaan()` dulu.
2. Ganti semua pengecekan tersebar dengan fungsi ini: `AjukanPenarikanAction` (blok `tanggal_boleh_cair`/tunggakan), `Admin\SerahTerimaPaket`, `Nasabah\ProgresPaket::pilihMetodePengambilan` (kini cek `tanggal_boleh_cair` sendiri), tampilan countdown di dashboard. Pesan error identik di semua tempat.
3. Pengecekan diulang **di dalam transaksi** pada aksi serah terima (bukan hanya di UI).
4. Form produk: opsi "Boleh cair lebih awal saat target tabungan tercapai" (`boleh_cair_saat_target`) dan `batas_daftar_hingga` (picker P4).
5. Tes: kombinasi (komitmen ya/tidak) × (sebelum/sesudah tanggal) × (tunggakan 0/>0) × (target tercapai, toggle on/off) × (sudah diserahkan).

### 9.5 Serah terima & foto bukti (#5, sudah ada → perkuat)
Sudah ada: foto wajib (`buktiFoto` `required|image|max:2048`, disimpan disk `local` privat, `SerahTerimaFotoController` untuk menampilkan). Tugas:
1. Pastikan jalur **kolektor** (`/serah-terima-paket` untuk `admin|kolektor`) juga memaksa foto dan memeriksa kepemilikan nasabah (IDOR); tambah tes untuk kolektor.
2. Naikkan batas ke **5 MB**, izinkan `jpg,jpeg,png,webp`, kompres di klien (canvas, lebar maks ±1600 px) agar foto kamera HP tidak gagal; dokumentasikan `upload_max_filesize`/`post_max_size`.
3. Tampilkan foto bukti ke nasabah pemilik dan ke admin di detail; akses lewat controller berotorisasi (jangan URL publik).
4. Simpan metadata: siapa, kapan, metode (ambil sendiri/diantar), dan catat `ActivityLogger` dengan path foto (sudah ada). Jangan bisa serah terima tanpa foto lewat jalur mana pun (tes).

---

## 10. P7 — Admin Mobile (keuangan & persetujuan)  [catatan #9]

Seluruh 19 komponen `Admin\*` memakai `#[Layout('layouts.app')]`. **Jangan menggandakan halaman**; jadikan shell admin responsif.

1. **Shell admin responsif:** `< lg` → topbar ringkas + **bottom nav 5 tab**: *Beranda*, *Persetujuan* (lencana jumlah), *Keuangan*, *Nasabah*, *Menu* (sheet berisi semua halaman lain). `≥ lg` → sidebar yang ada. Gunakan `viewport-fit=cover` dan padding `env(safe-area-inset-*)`.
2. **Hub Persetujuan** (`admin.persetujuan`): satu halaman berisi hitungan + tautan dalam ke antrian: penarikan `pending` (approve/reject, fase-1/2 jelas), verifikasi nasabah baru, setoran kantor `pending`, komplain baru, izin kolektor, nasabah bermasalah. Query hitungan sedikit (agregasi tunggal), tanpa cache agar real-time.
3. **Keuangan:** Kas Kolektor, Rekonsiliasi (input nominal diterima + selisih + keterangan), Komisi, Laporan ringkas — semua layak dipakai di 360 px.
4. **Audit responsif** untuk komponen: `ApprovalPenarikan`, `VerifikasiNasabah`, `RekonsiliasiKas`, `KasKolektor`, `Komisi`, `Laporan`, `HandoverKolektor`, `MonitoringSetoran`, `SerahTerimaPaket`, `ManajemenNasabah`, `ManajemenProduk`, `AntrianKomplain`. Pola: tabel → daftar kartu di `< md` (`hidden md:table` + `md:hidden`), form dalam modal layar penuh, tombol aksi ≥ 44 px, tombol uang tetap `wire:confirm` (jangan hilangkan; `KonfirmasiAksiUangTest`), tidak ada scroll horizontal pada body.
5. **Keamanan:** aksi uang di perangkat seluler tetap memerlukan role `admin`, tetap lewat approval ganda (D11) dan log. Pertimbangkan pengaturan opsional "sesi admin pendek" (`SESSION_LIFETIME` terpisah) bila mudah; bila tidak, catat sebagai rekomendasi.
6. **Tes:** semua rute admin merespons 200 untuk admin dan 403/redirect untuk role lain; markup bottom-nav ada; tidak ada regresi pada approval ganda; checklist uji manual 360/390/414 px dicatat di plan-log.

**Selesai bila:** admin dapat menyetujui penarikan, memverifikasi nasabah, merekonsiliasi kas, dan membaca komisi/laporan penuh dari HP.

---

## 11. P8 — APK Nasabah, Kolektor, Admin (D20)  [catatan #8]

> Sandbox agent mungkin tidak punya Android SDK dan jaringan terbatas. **Deliverable wajib adalah proyek + workflow CI**; APK dihasilkan oleh GitHub Actions. Bila lingkungan agent memang punya SDK, boleh build lokal juga.

### 11.1 Prasyarat web (kerjakan lebih dulu)
1. Produksi wajib **HTTPS** (`SESSION_SECURE_COOKIE=true`, `APP_URL` benar).
2. `layouts/mobile.blade.php` memuat Leaflet dan `signature_pad` dari **unpkg/jsdelivr (CDN)** → pasang via npm dan bundel lewat Vite agar aplikasi tidak bergantung CDN (penting untuk sinyal buruk dan WebView). Pastikan absen (selfie, GPS, tanda tangan) tetap berfungsi.
3. Service worker: tambahkan **halaman offline** statis (`/offline`) yang ditampilkan saat navigasi gagal. **Jangan** meng-cache halaman terautentikasi atau data uang.
4. Aksesibilitas kamera/lokasi: input foto memakai `accept="image/*" capture` agar WebView membuka kamera; geolokasi memakai API web standar.

### 11.2 Proyek `mobile/` (Capacitor, bukan TWA)
- Alasan: aplikasi server-rendered (Livewire); WebView sederhana, tanpa Digital Asset Links. TWA/Bubblewrap ditolak karena butuh Chrome + verifikasi domain.
- Struktur: `mobile/package.json`, `mobile/template/` (konfigurasi dasar), `mobile/flavors.json` (3 varian), `mobile/scripts/build-flavor.sh`, `mobile/assets/<flavor>/icon.png` & `splash.png`, `mobile/README.md`.
- `flavors.json`: `nasabah` (`id.tabungan.nasabah`, nama "Tabungan", aksen hijau, `startPath=/login`), `kolektor` (`id.tabungan.kolektor`, "Tabungan Kolektor", biru, `/login/kolektor`), `admin` (`id.tabungan.admin`, "Tabungan Admin", gelap, `/login/admin`). `applicationId` berbeda agar bisa terpasang bersamaan.
- `capacitor.config`: `server.url = https://<APP_DOMAIN><startPath>`, `server.cleartext=false`, `server.allowNavigation=[<APP_DOMAIN>]` (tolak domain lain), `server.errorPath` → halaman offline lokal, `android.appendUserAgent = "TabunganApp/<flavor>"`.
- `build-flavor.sh <flavor>`: salin `template` → `mobile/build/<flavor>`, tulis config dari `flavors.json`, `npx cap add android`, set `applicationId`/`app_name`/ikon (`@capacitor/assets`), tambahkan izin `INTERNET`, `CAMERA`, `ACCESS_FINE_LOCATION`, `ACCESS_COARSE_LOCATION`, atur tombol Back (`@capacitor/app`: kembali di riwayat, keluar di halaman awal), `./gradlew assembleRelease`.
- Cookie sesi bekerja karena domain sama; uji login, "ingat saya", unggah foto, selfie absen, GPS, tanda tangan di perangkat nyata.

### 11.3 CI: `.github/workflows/build-apk.yml`
- Trigger manual (`workflow_dispatch`) + tag `apk-*`; matriks tiga varian; setup Java 17 + Node; panggil `build-flavor.sh`; `versionCode = github.run_number`.
- Penandatanganan release: keystore dari secret (`ANDROID_KEYSTORE_BASE64`, `ANDROID_KEYSTORE_PASSWORD`, `ANDROID_KEY_ALIAS`, `ANDROID_KEY_PASSWORD`) di-decode saat build. Tanpa secret → hasilkan **debug APK** untuk uji. **Jangan pernah commit keystore**; tambahkan ke `.gitignore` (`*.jks`, `*.keystore`, `mobile/build/`, `mobile/**/node_modules`).
- Unggah APK sebagai artifact (`tabungan-<flavor>-<versi>.apk`).
- Variabel `APP_DOMAIN` sebagai GitHub Actions variable.

### 11.4 Dokumen
`mobile/README.md` + `docs/apk.md`: cara membuat keystore (`keytool`), mengisi secrets, menjalankan workflow, memasang APK (izinkan sumber tidak dikenal), memperbarui ikon, dan catatan kebijakan Play Store (WebView murni berisiko ditolak "minimum functionality"; untuk distribusi langsung/internal tidak masalah).

**Selesai bila:** workflow menghasilkan tiga APK yang bisa dipasang dan berhasil login ke portal masing-masing; uji manual di perangkat Android nyata tercatat.

---

## 12. P9 — Penutup, Dokumentasi, Serah Terima

1. **PRD:** tambahkan FR baru ke `prd.md` (mis. FR-34 nasabah offline, FR-35 kas dikurangi penarikan tunai, FR-36 komitmen paket, FR-37 portal login terpisah, FR-38 ekspor komisi, FR-39 pembatalan penarikan, FR-40 APK/PWA) dan centang yang terverifikasi tes.
2. **README:** perbarui bagian deploy (HTTPS, domain APK), "Catatan Fitur & Risiko Terbuka" (D4 → selesai; D13–D22), perintah build APK, backfill `mempengaruhi_kas`.
3. **Seeder demo** (`DatabaseSeeder`/seeder terpisah): satu kolektor dengan kas, satu nasabah offline, satu paket dengan `harga` per barang, satu kepesertaan berkomitmen, satu penarikan `approved` yang bisa dibatalkan.
4. **Regresi penuh:** `composer ci:check`; `php artisan schedule:list`; jalankan semua migrasi di salinan DB produksi (naik dan turun); `route:list` bebas rute yatim.
5. **Uji manual end-to-end** (catat hasil di plan-log):
   1. Admin membuat paket dengan harga barang; nasabah (digital) memilih paket + PIN; progres tampil tanpa harga.
   2. Kolektor mendaftarkan nasabah offline; admin memverifikasi; kolektor mencatat setoran harian.
   3. Penarikan tunai nasabah offline → kas kolektor berkurang → setor kantor → admin rekonsiliasi cocok.
   4. Nasabah digital mengajukan penarikan → membatalkan (saat `pending` dan `approved`) → saldo benar.
   5. Paket lunas, waktunya tiba → serah terima dengan foto → nasabah melihat bukti.
   6. Login tiap portal; role salah ditolak; APK ketiga varian.
   7. Admin dari HP: persetujuan, rekonsiliasi, ekspor komisi, laporan.
6. **Serah terima ke pemilik** (tulis di plan-log): daftar "Keputusan yang diambil agent" (D13–D22 + default yang dipakai), pertanyaan terbuka (D21 arti "penarikan komisi"; apakah paket boleh cair lebih awal saat target tercapai; apakah penarikan offline di atas ambang memang harus tetap lewat admin), dan risiko yang diketahui.

---

## 13. Lampiran A — Peta Berkas yang Kemungkinan Berubah

| Area | Berkas |
|---|---|
| Kas & penarikan | `Actions/Penarikan/*`, `Livewire/Kolektor/{SetorKantor,PenarikanOffline,VerifikasiPenarikan}`, `Livewire/Admin/{KasKolektor,RekonsiliasiKas,HandoverKolektor,ApprovalPenarikan}`, `Models/{TransaksiSetoran,SetoranKolektorKantor,TransaksiPenarikan,User}`, `Http/Controllers/DashboardController`, `Livewire/Nasabah/{RiwayatPenarikan,AjukanPenarikan}`, `Console/Commands/KedaluwarsakanPenarikan` |
| Offline | `Models/User`, `Providers/FortifyServiceProvider`, `Livewire/Admin/RegistrasiNasabah`, `Livewire/Kolektor/DaftarNasabah`, `Support/NomorHp`, `Services/WhatsAppService`, `Jobs/*`, `Helpers/ActivityLogger`, `Actions/Pin/*` |
| Login | `Providers/FortifyServiceProvider`, `routes/web.php`, `pages/auth/login.blade.php`, `layouts/*`, `Http/Middleware/EnsureAccountIsActive`, `public/manifest-*.webmanifest`, `Http/Controllers/LandingController` |
| Kalender | `resources/js/app.js`, `resources/css/app.css`, `components/ui/tanggal.blade.php`, 14 view pada 7.2 |
| Komisi/Laporan | `Livewire/Admin/{Komisi,Laporan}`, `Support/CsvSafe` (baru), view terkait |
| Paket | `Models/{ProdukTabungan,KepesertaanPaket}`, `Actions/Tabungan/HitungTunggakanAction`, `Actions/Setoran/CatatSetoranAction`, `Actions/Paket/*`, `Livewire/Admin/{ManajemenProduk,SerahTerimaPaket,DetailNasabah}`, `Livewire/Nasabah/{ProgresPaket,PilihPaket}`, `Livewire/Kolektor/NasabahBinaan`, `Http/Controllers/{LandingController,SerahTerimaFotoController}` |
| Admin mobile | `layouts/app*.blade.php`, `components/admin/*`, 12 komponen `Admin\*` |
| APK | `mobile/`, `.github/workflows/build-apk.yml`, `public/service-worker.js`, `layouts/mobile.blade.php`, `package.json` |

## 14. Lampiran B — Risiko Utama & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Backfill kas menghitung mundur | Kas semua kolektor mendadak minus | `mempengaruhi_kas=false` untuk historis; tes backfill |
| Harga barang bocor ke nasabah | Pelanggaran D15 | `isiPaketPublik()`, tidak ada harga di properti publik Livewire, tes `assertDontSee` |
| Setoran paket tanpa kepesertaan setelah D16 | Setoran gagal di lapangan | Pesan jelas + tombol "Daftarkan ke Paket" langsung di form setoran |
| `no_hp` NULL merusak halaman/job | Error 500, WA gagal | Audit `grep` P2.2 + tes render semua halaman dengan nasabah offline |
| Balapan batal vs approve/verifikasi | Saldo ganda/hilang | `lockForUpdate` urut baris penarikan → saldo + tes balapan |
| Login portal membocorkan akun | Enumerasi | Pesan generik, `Hash::check` dummy, rate limit |
| Picker kalender merusak `wire:model` | Filter/formulir mati | Komponen tunggal teruji, nilai server tetap `Y-m-d`/`Y-m` |
| APK tak bisa dibuild di sandbox | Deliverable kosong | CI GitHub Actions + dokumentasi build |
| WebView ditolak Play Store | Distribusi terhambat | Distribusi langsung/internal; catat di docs |

## 15. Lampiran C — Definition of Done (berlaku untuk setiap fase)

- [ ] Tes RED→GREEN ditulis dan lulus; tes lama yang berubah dijelaskan di plan-log.
- [ ] `composer ci:check` hijau; PHPStan tanpa entri baseline baru.
- [ ] Migrasi naik/turun diuji; backfill idempoten.
- [ ] Semua aksi uang: transaksi + lock + bcmath + log; tes IDOR untuk input ber-ID.
- [ ] Teks UI Bahasa Indonesia; tampilan dicek di 360 px dan mode gelap.
- [ ] `docs/plan-log.md` diperbarui; README/PRD diperbarui bila perilaku berubah.
- [ ] Tidak ada rahasia (keystore, `.env`) ter-commit.