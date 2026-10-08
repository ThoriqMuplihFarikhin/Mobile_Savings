# Mobile_Savings

## Deployment

Pastikan `.env` di production memiliki setting berikut:

```
APP_ENV=production
APP_DEBUG=false
APP_KEY=<your-app-key>
```

`APP_DEBUG=false` wajib diaktifkan di production agar detail error tidak ditampilkan ke pengguna.

Checklist `.env` production (HTTPS):

- `SESSION_SECURE_COOKIE=true` — wajib, agar cookie sesi hanya dikirim lewat HTTPS.
- `APP_URL=https://<domain-produksi>` — wajib memakai HTTPS dan domain final; menentukan seluruh URL yang dibuat aplikasi (rute, aset, manifest PWA) serta rujukan `server.url` pada APK Capacitor (P8).
- `TRUSTED_PROXIES=<ip-load-balancer>` — isi hanya jika di belakang proxy tepercaya; kosong = tidak percaya proxy sama sekali. `*` hanya bila seluruh trafik melewati load balancer tepercaya.

Setelah deploy, jalankan:
```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Proses daemon yang wajib berjalan di produksi:

- **Scheduler** — cron tiap menit agar seluruh tugas terjadwal berjalan (daftar lengkap via `php artisan schedule:list`, dapat diuji satu per satu dengan `php artisan schedule:test --name "<perintah>"`):
  - `paket:hitung-ulang` — 00:10 (hitung ulang progres & tunggakan paket harian)
  - `jadwal:generate` — 00:30 (hasilkan jadwal kunjungan kolektor harian)
  - `penarikan:kedaluwarsakan` — 00:40 (tandai pengajuan penarikan yang kedaluwarsa)
  - `paket:kirim-pengingat` — 08:00 (pengingat tunggakan via WhatsApp + in-app)
  - `queue:prune-failed --hours=168` — 00:00 (bersihkan job gagal lebih dari 7 hari)

  ```
  * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
  ```

- **Queue worker** — notifikasi WhatsApp dikirim lewat antrian job; jalankan worker persisten di bawah Supervisor/systemd, contoh unit systemd (`/etc/systemd/system/mobile-savings-worker.service`):

  ```ini
  [Unit]
  Description=Mobile Savings queue worker
  After=network.target

  [Service]
  User=www-data
  WorkingDirectory=/path/to/app
  ExecStart=/usr/bin/php artisan queue:work --tries=3 --timeout=60
  Restart=always
  RestartSec=5

  [Install]
  WantedBy=multi-user.target
  ```

  ```bash
  systemctl enable --now mobile-savings-worker
  ```

- **Database** — MySQL wajib (SQLite tidak mendukung `lockForUpdate` yang dipakai seluruh transaksi uang; lihat guard di `AppServiceProvider`).

Catatan deploy:

- **Jangan deploy dari zip Windows** — kompresi zip merusak nama file ber-emoji (mis. `⚡security.blade.php` menjadi `#U26a1security.blade.php`) sehingga halaman Volt tidak ditemukan. Gunakan `git clone` di server atau `git archive` untuk membuat artefak.
- **Jangan sertakan `bootstrap/cache/*.php`** dari mesin development — file cache config/routes/views lama akan bentrok dengan `config:cache`/`route:cache` di server.

## Pengujian

Test selalu berjalan pada database terpisah `tabungan_digital_test` (di-`force` di `phpunit.xml`); buat dulu satu kali:

```bash
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS tabungan_digital_test CHARACTER SET utf8mb4"
```

## Integrasi Berkelanjutan (CI)

Workflow `.github/workflows/tests.yml` menjalankan `composer ci:check` pada setiap push ke `main` dan setiap pull request:

1. `composer setup` — install dependensi, salin `.env.example` → `.env`, `key:generate`, `migrate --force`, `npm run build`.
2. `composer ci:check` — `config:clear` → `pint --parallel --test` → `phpstan analyse` → `php artisan test`.

GitHub Actions menyediakan layanan `mysql:8.0` dengan database `tabungan_digital_test`; nilai env `DB_*` di workflow sengaja disamakan dengan `phpunit.xml`.

Pemeriksaan yang sama dapat dijalankan lokal:

```bash
composer ci:check
```

PHPStan memakai baseline `phpstan-baseline.neon`, sehingga error lama tidak menggagalkan CI tetapi **error baru tetap gagal**. Regenerasi baseline hanya setelah error lama diperbaiki:

```bash
vendor/bin/phpstan analyse --generate-baseline phpstan-baseline.neon --memory-limit=1G
```

## Build APK (Capacitor)

Tiga varian APK (`nasabah`, `kolektor`, `admin`) dibangun dari proyek `mobile/` dan memuat aplikasi lewat HTTPS (`server.url` dirender dari domain produksi — samakan dengan `APP_URL` tanpa skema `https://`):

- **Lokal** (butuh Node 20, Java 17, Android SDK):

  ```bash
  APP_DOMAIN=<domain-produksi> bash mobile/scripts/build-flavor.sh <flavor>
  ```

  Tanpa keystore hasilnya APK debug; dengan `ANDROID_KEYSTORE_*` hasilnya APK release terpasang tanda tangan.
- **CI** — workflow `.github/workflows/build-apk.yml`: jalankan manual lewat *Actions → build-apk* (pilih satu flavor atau `all`), atau buat tag `apk-<versi>` untuk ketiga flavor sekaligus. Wajib set repository **variable** `APP_DOMAIN`; 4 **secrets** keystore (`ANDROID_KEYSTORE_BASE64`, `ANDROID_KEYSTORE_PASSWORD`, `ANDROID_KEY_ALIAS`, `ANDROID_KEY_PASSWORD`) opsional — tanpa secret workflow menghasilkan APK debug.
- Dokumen lengkap: `mobile/README.md` (arsitektur, konfigurasi, ganti ikon) dan `docs/apk.md` (membuat keystore, mengisi secrets, mengunduh artefak, checklist uji perangkat, catatan Play Store).

## Catatan Fitur & Risiko Terbuka

- **Landing page (D10)** — `/` dirender `LandingController` dengan cache 10 menit, tanpa angka bisnis yang dipetik per request; kartu statistik "Real-time/Aman/Transparan" bersifat statis dan tidak disuplai data.
- **Persetujuan penarikan ganda (D11)** — penarikan besar butuh 3 admin berbeda (`ApprovalPenarikan::MINIMAL_ADMIN_PERSETUJUAN_GANDA`); bila admin aktif kurang dari 3, fitur jatuh ke mode satu-admin dan setiap selesai dicatat `fallback_admin_kurang` pada detail log.
- **Kas penarikan tunai di rumah (D13, menggantikan & menutup D4)** — `kas_di_tangan(k) = Σ setoran belum disetor − Σ nominal_diterima penarikan tunai yang dibayarkan kolektor k dan belum direkonsiliasi` (komisi tetap di kas; sumber tunggal `App\Support\KasKolektorHitung`). Kas tidak cukup → penarikan tunai ditolak, kecuali `AdminSetting izinkan_kas_minus = true` (default `false`). `total_seharusnya` pengajuan setor = setoran tertaut − penarikan tunai tertaut. **Backfill historis (idempoten):** penarikan `selesai` di `rumah_kolektor` sebelum fitur diberi `dibayar_oleh = diverifikasi_oleh` tetapi `mempengaruhi_kas = false` — kolektor sudah menyetor penuh pada periode lalu, menghitung mundur akan membuat kas mendadak minus.
- **Nasabah mode offline (D14)** — `users.mode_akses = offline`: tidak bisa login (respons sama dengan PIN salah), tidak menerima notifikasi WA/in-app, tetap punya akun/saldo/riwayat/kepesertaan; seluruh pencatatan oleh kolektor/admin. Penarikan tunai nasabah offline bisa dicatat langsung `selesai` selama di bawah ambang persetujuan ganda (`AdminSetting offline_penarikan_langsung_selesai`, default `true`); konversi mode dua arah oleh admin (offline→digital mengirim PIN awal via WA, digital→offline menghapus sesi).
- **Harga barang paket (D15)** — `harga` per item `isi_paket` hanya terlihat admin; nasabah memakai `isiPaketPublik()`/`untukNasabah()` yang membuang kunci `harga` kecuali produk menyalakan `tampilkan_harga_ke_nasabah` (default `false`).
- **Komitmen paket (D16)** — kepesertaan **tidak lagi dibuat otomatis oleh setoran**: nasabah mendaftar sendiri lewat `Nasabah\PilihPaket` (konfirmasi PIN + teks komitmen), atau didaftarkan admin/kolektor (`DaftarkanKePaketAction`, wajib catatan persetujuan). Keluar di tengah jalan hanya lewat admin (`ProsesKegagalanPaketAction`).
- **Gerbang pencairan tunggal (D17)** — semua pengecekan pencairan memakai `KepesertaanPaket::statusPencairan()`: boleh cair bila komitmen ada, belum diserahkan, tunggakan 0, dan (`today ≥ tanggal_boleh_cair` **atau** `produk.boleh_cair_saat_target` + target tercapai; default opsi `false`).
- **Pembatalan penarikan (D18)** — nasabah dapat membatalkan pengajuan `pending` (termasuk fase-1) dan `approved` (saldo dikembalikan dalam transaksi berlock) lewat `BatalkanPenarikanAction`; `selesai`/`ditolak` tidak bisa dibatalkan.
- **Login portal terpisah (D19)** — `/login` (nasabah), `/login/kolektor`, `/login/admin`; role tidak cocok portal ditolak dengan pesan generik + log `login_portal_salah`; cookie `portal_login` menentukan halaman tujuan logout.
- **APK Capacitor (D20)** — WebView tiga flavor, lihat bagian "Build APK" di atas; `APP_URL`/`APP_DOMAIN` wajib HTTPS sebelum build.
- **Ekspor komisi (D21)** — "penarikan data komisi" ditafsirkan sebagai **ekspor** CSV + cetak (tanpa paket PDF/XLSX pihak ketiga — D8); bila ternyata bermaksud pencairan komisi ke seseorang, itu pekerjaan terpisah (pertanyaan terbuka tercatat di `docs/plan-log.md`).
- **Kalender (D22)** — seluruh input tanggal memakai `<x-ui.tanggal>` (flatpickr lokal via npm, locale `id`); `flux:date-picker` tidak dipakai karena termasuk Flux Pro.
