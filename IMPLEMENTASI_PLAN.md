# Implementation Plan P10 — Hardening Keamanan & Kesiapan Produksi

**Lanjutan dari:** `IMPLEMENTATION_PLAN_CATATAN_OKT_2026.md` (P0–P9, sudah selesai: 720 tes hijau, branch `feat/okt26`).
**Untuk:** AI agent pelaksana. Aturan kerja Bagian 1 plan awal (TDD RED→GREEN, `composer ci:check`, bcmath + `lockForUpdate`, `ActivityLogger`, tes IDOR, Bahasa Indonesia, tanpa entri baru di baseline PHPStan, catat di `docs/plan-log.md` sebagai **P10.x**) **tetap berlaku sepenuhnya**.
**Dasar:** audit kode pasca-P9 (temuan di Bagian 1). Dokumen ini hanya memuat yang harus diperbaiki; jangan menyentuh perilaku lain.

---

## 1. Temuan Audit

| # | Temuan | Tingkat | Fase |
|---|---|---|---|
| F1 | Penarikan nasabah offline langsung `selesai` oleh kolektor sendiri: tanpa bukti, tanpa pihak kedua, saldo **dan kas kolektor** langsung berkurang. Kolektor yang kasnya kurang bisa mencatat penarikan fiktif untuk menutupnya. Ambang `penarikan_batas_dua_approver` default `0` = tidak ada batas, dan tidak berlaku bila admin < 3 (`ApprovalPenarikan::melewatiAmbangDuaApprover`). `catatan` hanya masuk log aktivitas, tidak ke `transaksi_penarikan`. Jalur lokasi `kantor` juga ikut langsung selesai tanpa ada admin yang membayar. | **Tinggi** | P10.1 |
| F2 | Login admin memakai form yang sama dengan checkbox "Ingat saya"; tidak ada batas idle sesi admin (`SESSION_LIFETIME=120`). Admin mobile + APK membuat HP hilang = akses persetujuan uang. | Sedang | P10.2 |
| F3 | `.env.example` `SESSION_SECURE_COOKIE=false`; zip memuat `public/hot` (`http://[::1]:5173`) dan `fonts-manifest.dev.json`. Tidak ada pemeriksaan otomatis kesiapan produksi. | Sedang | P10.3 |
| F4 | Bug landing 500 (P9.7) lolos karena semua tes memakai `CACHE_STORE=array`. Perbaikannya melonggarkan `serializable_classes` dan menyimpan **model penuh** (termasuk `isi_paket` ber-`harga`) di tabel `cache`. | Sedang | P10.4 |
| F5 | Nasabah offline tidak punya saluran untuk memeriksa/membantah catatan mereka. | Rendah | P10.5 |

---

## 2. Keputusan Desain (D23–D26)

**D23 — Penarikan offline langsung (menggantikan perilaku D14 bagian "langsung selesai").**
Langsung `selesai` **hanya bila semua** terpenuhi: nasabah `mode_akses='offline'`; `lokasi_pengambilan='rumah_kolektor'` (kolektor membayar tunai dari kasnya; untuk `kantor` **tidak pernah** langsung, karena yang membayar admin dan harus lewat approval/penyelesaian manual yang sudah ada); **bukti terlampir**; nominal ≤ `offline_penarikan_batas_langsung`; total langsung kolektor hari itu + nominal ≤ `offline_penarikan_batas_harian` (bila > 0); kolektor tidak sedang dibekukan (`izin_penarikan_langsung=true`); `offline_penarikan_langsung_selesai='true'`; kas cukup (D13); tidak melewati ambang dua approver. Selain itu → antrian approval admin seperti biasa.
**Default aman:** `offline_penarikan_batas_langsung = 0` (**0 = penarikan langsung dinonaktifkan**, semua offline lewat approval). Admin mengaktifkan dengan mengisi nominal di Pengaturan setelah mengambil keputusan. Jangan menebak angka rupiah.

**D24 — Bukti penarikan offline.** Wajib minimal satu: **tanda tangan/cap jempol digambar nasabah di layar** (`signature_pad` sudah dibundel dan dipakai di `kolektor/absen.blade.php`) atau foto (buku tabungan dicoret/nasabah menerima uang). Disimpan di disk privat `local`, nama acak, diakses lewat controller berotorisasi (ikuti pola `SerahTerimaFotoController` dan penanganan gambar base64 di absensi — `AbsenTest` sudah memuat pola tes: bukan-gambar, >batas ukuran, nama acak, tanpa berkas yatim bila DB gagal). `catatan` kolektor disimpan **di kolom `transaksi_penarikan`**, bukan hanya di log.

**D25 — Tinjauan admin pasca-fakta.** Setiap penarikan offline langsung masuk antrian "belum ditinjau". Admin menandai **Sesuai** atau **Bermasalah** (wajib alasan). Bermasalah menawarkan opsi (tercentang default) membekukan penarikan langsung kolektor tsb.

**D26 — Keamanan sesi admin.** Admin tidak boleh memakai "ingat saya"; sesi admin dibatasi idle (default 30 menit, `ADMIN_IDLE_MINUTES`). Kolektor/nasabah tidak berubah.

---

## 3. P10.1 — Penarikan Offline Langsung yang Terkendali (F1, D23–D25)

### 3.1 Skema (satu migrasi, reversible)
`transaksi_penarikan`:
- `bukti_path` string nullable (path relatif disk `local`), `bukti_jenis` enum(`tanda_tangan`,`foto`) nullable
- `catatan_kolektor` text nullable
- `ditinjau_oleh` FK→users nullable, `ditinjau_pada` timestamp nullable, `hasil_tinjauan` enum(`sesuai`,`bermasalah`) nullable, `catatan_tinjauan` string(255) nullable
- indeks `(metode_verifikasi, hasil_tinjauan)`

`users`: `izin_penarikan_langsung` boolean default `true`.

`AdminSetting` (tanpa migrasi, via `AdminSetting::get` default): `offline_penarikan_batas_langsung` default `'0'`, `offline_penarikan_batas_harian` default `'0'` (0 = tanpa batas harian).

**Backfill:** semua baris existing dengan `metode_verifikasi='tanpa_verifikasi_offline'` dibiarkan `hasil_tinjauan = NULL` agar langsung tampil sebagai antrian tinjauan (backlog harus terlihat admin). Idempoten.

### 3.2 Logika (`CatatPenarikanOfflineLangsungAction`, `Kolektor\PenarikanOffline`)
1. Ubah `execute(...)` agar menerima `buktiBase64`/`buktiFile` + `buktiJenis` dan `catatan`; wajib salah satu bukti untuk **semua** penarikan nasabah offline (juga yang masuk antrian approval, supaya admin melihat bukti saat menyetujui).
2. Pindahkan keputusan "langsung atau antrian" ke metode tunggal `alasanTidakLangsung(...)` yang menerapkan seluruh syarat D23 secara berurutan dan mengembalikan kode alasan (`setting_nonaktif`, `batas_nol`, `lokasi_kantor`, `melebihi_batas`, `melebihi_batas_harian`, `kolektor_dibekukan`, `tanpa_bukti`, `kas_kurang`, `melewati_ambang`, `digital`). Total langsung harian dihitung dari `transaksi_penarikan` (`metode_verifikasi='tanpa_verifikasi_offline'`, `dibayar_oleh=kolektor`, tanggal `waktu_pencairan` Asia/Jakarta) **di dalam transaksi berlock** (kunci baris `users` kolektor atau pakai `KasKolektorHitung::kunciBarisKas`) agar dua request paralel tidak melampaui batas harian.
3. Simpan `bukti_*` dan `catatan_kolektor` pada baris penarikan dalam transaksi yang sama; bila transaksi gagal, hapus berkas bukti (tanpa berkas yatim).
4. UI `PenarikanOffline`: untuk nasabah offline tampilkan kanvas tanda tangan + opsi foto + catatan; **sebelum kirim** tampilkan teks hasil keputusan ("akan diproses langsung" / "akan menunggu persetujuan admin: <alasan>") memakai metode yang sama (tanpa menulis ke DB). Jangan menambah properti publik Livewire yang memuat data bukti besar setelah dikirim (reset).
5. Notifikasi in-app ke admin tetap dikirim untuk yang langsung selesai, kini dengan tautan ke antrian tinjauan.

### 3.3 Penyajian bukti
- Route `GET /penarikan/{penarikan}/bukti` (nama `penarikan.bukti`) → `BuktiPenarikanController@show`: hanya admin dan kolektor pembuat (`dibayar_oleh`/`diverifikasi_oleh`) — tes IDOR untuk kolektor lain, nasabah, tamu. `404` bila berkas tak ada. Header `Cache-Control: private, no-store`.
- Tampilkan bukti di detail penarikan pada `Admin\ApprovalPenarikan` dan antrian tinjauan.

### 3.4 Tinjauan admin
1. Halaman `Admin\TinjauanPenarikanOffline` (route `admin.tinjauan-penarikan-offline`, role admin, shell responsif P7): daftar penarikan `tanpa_verifikasi_offline` dengan `hasil_tinjauan IS NULL`, terbaru dulu; kolom: waktu, kolektor, nasabah, produk, nominal, lokasi, catatan, bukti (tautan), dampak kas. Filter kolektor, status tinjauan, rentang tanggal (`<x-ui.tanggal>`).
2. Aksi **Sesuai** (`wire:confirm`) dan **Bermasalah** (wajib `catatan_tinjauan` ≥ 10 karakter; centang "bekukan penarikan langsung kolektor ini", default aktif). Semua dalam transaksi berlock dengan pengecekan `hasil_tinjauan IS NULL` (aksi ganda tidak boleh menimpa); log `tinjau_penarikan_offline`; notifikasi ke kolektor bila bermasalah.
3. `Pengaturan` (tab Umum): field `offline_penarikan_batas_langsung` dan `offline_penarikan_batas_harian` (integer ≥ 0, teks bantuan: "0 = nonaktif"), disimpan seperti `izinkan_kas_minus`; tampilkan peringatan kuning bila `penarikan_batas_dua_approver = 0` atau admin < 3 sambil batas langsung > 0 ("ambang dua approver tidak aktif; hanya batas ini yang membatasi").
4. `Admin\KelolaKolektor`: toggle "Izinkan penarikan offline langsung" (`izin_penarikan_langsung`) dengan log.
5. `Admin\HubPersetujuan`: tambah hitungan "Penarikan offline belum ditinjau" + tautan; lencana bottom-nav ikut menghitungnya.

### 3.5 Laporan & rekonsiliasi
- Seksi laporan baru **"Penarikan offline langsung"** (Laporan + CSV via `CsvSafe` + cetak): per kolektor — jumlah, total nominal, **persentase terhadap seluruh pengurangan kas kolektor itu**, belum ditinjau, sesuai, bermasalah; baris detail per penarikan dengan tautan bukti (hanya admin).
- `RekonsiliasiKas` dan `SetorKantor`: pada rincian "Penarikan tunai (−)" beri kolom/penanda "langsung (belum ditinjau/sesuai/bermasalah)" agar admin melihatnya saat menerima setoran.

### 3.6 Tes P10.1 (minimal)
Tiap syarat D23 sebagai kasus tersendiri (default `batas_langsung=0` → semua ke antrian; `kantor` tidak pernah langsung; di atas batas; batas harian termasuk balapan dua request; kolektor dibekukan; tanpa bukti ditolak; kas kurang); bukti: bukan-gambar, terlalu besar, nama acak & disk privat, tanpa berkas yatim saat gagal; IDOR bukti; tinjauan sesuai/bermasalah (ganda tidak menimpa, alasan wajib, pembekuan, notifikasi); backfill meninggalkan backlog; laporan angka = fixture; hub & lencana; aksi uang lain tidak berubah (kas, saldo, `BatalkanPenarikanD18Test`, `KasPenarikanTunaiTest` tetap hijau).

**Selesai bila:** dengan setelan default, tidak ada penarikan offline yang bisa selesai tanpa admin; setelah admin mengisi batas, penarikan langsung hanya terjadi dengan bukti dan selalu muncul di antrian tinjauan.

---

## 4. P10.2 — Sesi Admin (F2, D26)

1. Halaman login portal admin: **sembunyikan** checkbox "Ingat saya"; di `authenticateUsing`/pipeline Fortify paksa `remember=false` untuk `role='admin'` (jangan hanya mengandalkan UI). Tes: POST dengan `remember=on` pada akun admin tidak membuat cookie `remember_web_*`.
2. Middleware `App\Http\Middleware\AdminIdleTimeout` pada grup rute admin (juga pada permintaan Livewire; daftar sebagai persistent middleware seperti `active`/`role`): simpan `admin_last_activity` di sesi; bila selisih > `config('auth.admin_idle_minutes')` (env `ADMIN_IDLE_MINUTES`, default 30) → logout, invalidasi sesi, arahkan ke `/login/admin` dengan pesan "Sesi berakhir karena tidak aktif". Perbarui timestamp tiap permintaan. Permintaan Livewire yang kedaluwarsa harus mengarahkan ke login (bukan 419 kosong). Log `sesi_admin_kedaluwarsa`.
3. Tambahkan `ADMIN_IDLE_MINUTES=30` ke `.env.example` dan README.
4. Tes: aktif → lolos; idle > batas (pakai `travel()`) → redirect + sesi hilang; kolektor/nasabah tidak terpengaruh; Livewire request kedaluwarsa.

---

## 5. P10.3 — Pemeriksaan Kesiapan Produksi (F3)

1. Artisan command `deploy:check` (`App\Console\Commands\DeployCheck`) dengan keluaran lulus/gagal per butir dan exit code ≠ 0 bila ada yang gagal. Butir: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` berawalan `https://`, `SESSION_SECURE_COOKIE=true`, driver DB `mysql`, `CACHE_STORE` dan `QUEUE_CONNECTION` bukan `array`/`sync`, `public/hot` **tidak ada**, `public/fonts-manifest.dev.json` tidak ada, `public/build/manifest.json` ada, `APP_KEY` terisi, penjadwal terdaftar (`schedule:list` memuat `penarikan:kedaluwarsakan`, `jadwal:generate`, `paket:hitung-ulang`), `storage` dan `bootstrap/cache` dapat ditulis, setelan `izinkan_kas_minus` dan `offline_penarikan_batas_langsung` dicatat (informasi, bukan gagal).
2. Skrip composer `deploy:check` → `@php artisan deploy:check`; panggil di README bagian deploy dan di workflow CI **hanya sebagai langkah dokumentasi** (jangan membuat CI gagal karena `.env` lokal).
3. Verifikasi `.gitignore` memuat `public/hot`; tambahkan `public/fonts-manifest.dev.json` bila belum.
4. Tes untuk command: konfigurasi bagus → exit 0; tiap butir gagal → exit ≠ 0 dengan pesan spesifik (gunakan `config()` override dan file sementara untuk `hot`).

---

## 6. P10.4 — Cache Landing & Uji dengan Store Nyata (F4)

1. `LandingController`: cache **array sederhana** berisi hanya kolom publik hasil `isiPaketPublik()` (tanpa `harga`, tanpa objek Eloquent). Naikkan kunci cache (`landing:produk:v3`).
2. Kembalikan `config/cache.php` `serializable_classes` ke `false` (hilangkan allowlist model) — tanpa objek di cache, allowlist tidak diperlukan. Pertahankan tes P9.7 (dua request dengan `cache.default=database`) dan tambahkan: isi baris `cache` untuk kunci landing **tidak** memuat kata `harga`.
3. Tes "penjaga" baru: grep kode aplikasi — `Cache::put/remember/rememberForever` hanya boleh menyimpan scalar/array (tes gagal bila ada pemanggilan baru yang menyimpan objek; jalankan sebagai arch/grep test seperti `RouteBladeSinkronTest`).
4. `tests/Feature/SmokeStoreNyataTest.php`: set `cache.default=database`, `queue.default=database` (tabel `cache`, `jobs` tersedia lewat migrasi) lalu akses dua kali halaman kunci untuk tiga peran (landing, tiga halaman login, dashboard admin/kolektor/nasabah, laporan, hub persetujuan). Tujuannya menangkap kelas bug "lolos di `array` gagal di produksi".
5. Dokumentasikan di README: tes inti memakai `array`; smoke test memakai store database.

---

## 7. P10.5 — Saluran Kontrol untuk Nasabah Offline (F5)

1. Tombol **"Cetak rekap"** (sudah ada di `/rekap/nasabah/{user}`) menambahkan baris tanda tangan nasabah + tanggal cetak + nama kolektor; setiap cetak dicatat `ActivityLogger` (`cetak_rekap_nasabah`) dan `nasabah_profil.rekap_dicetak_pada` (migrasi kecil).
2. Laporan **Nasabah** (seksi yang ada) menambah kolom "rekap terakhir dicetak" untuk nasabah offline, urut terlama dulu. Tanpa ambang angka baru; hanya informasi.
3. Dokumen `docs/SOP_lapangan.md` (Bahasa Indonesia, ≤ 2 halaman): alur nasabah offline (daftar → setoran → penarikan dengan tanda tangan di layar), aturan kas (D13: kas tidak boleh minus; jika kas kurang, pilih pengambilan di kantor), cara cetak dan menandatangani rekap berkala, dan apa yang terjadi bila admin menandai "bermasalah".

---

## 8. Urutan, Dokumentasi, Selesai

**Urutan:** P10.1 → P10.2 → P10.3 → P10.4 → P10.5 (P10.1 tidak boleh ditunda; P10.3 wajib selesai sebelum deploy apa pun).

**Dokumentasi:** tambah FR-41..45 di `prd.md` (bukti & batas penarikan offline, tinjauan admin, sesi admin, `deploy:check`, smoke store nyata); perbarui README (setelan baru, default aman, `ADMIN_IDLE_MINUTES`, `composer deploy:check`); `docs/plan-log.md` entri P10.1–P10.5 dengan RED/GREEN dan "Keputusan yang diambil agent".

**Definition of Done (seluruh P10):**
- [ ] `composer ci:check` hijau; PHPStan tanpa entri baseline baru (idealnya turun).
- [ ] Default setelan: tidak ada penarikan offline selesai tanpa admin; semua tes lama tetap hijau.
- [ ] Migrasi naik/turun/naik (`MigrasiNaikTurunP94Test`) hijau.
- [ ] Tes IDOR untuk `penarikan.bukti`; tidak ada berkas bukti di disk publik.
- [ ] `php artisan deploy:check` pada `.env` produksi contoh menghasilkan exit 0.
- [ ] Tidak ada `public/hot` di artefak yang diserahkan.

**Pertanyaan terbuka untuk pemilik (catat di plan-log, jangan ditunggu):** (1) nilai awal `offline_penarikan_batas_langsung` dan `offline_penarikan_batas_harian`; (2) apakah nasabah offline perlu menerima rekap cetak dalam periode tetap; (3) apakah kolektor boleh membatalkan penarikan offline langsung yang salah catat (saat ini tidak ada jalur pembalikan setelah `selesai`; bila dibutuhkan, rancang sebagai koreksi oleh admin dengan jejak audit — di luar P10).