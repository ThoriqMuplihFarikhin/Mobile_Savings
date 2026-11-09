# Laporan Audit Keamanan & Kesiapan Deploy — Mobile_Savings (KawalDuit)

Tanggal audit: 8 November 2026
Stack: Laravel 13, Livewire 4, Flux, Fortify, Spatie Permission, Capacitor (Android), MySQL

## Cakupan dan batasan

Diperiksa (pembacaan kode dan pencarian pola, bukan menjalankan aplikasi):
- routes, middleware, controller file, Fortify/login, session, semua file `config/`, model (mass assignment semua 17 model), seeder, service worker, folder `mobile/`, workflow CI, struktur zip, `npm audit` (root dan `mobile/`).
- Alur PIN (ubah, reset admin, kirim PIN awal), `PortalLogin`, `ActivityLogger`, `WhatsAppService` dan job.
- Komponen Livewire: semua yang punya properti ID publik di Kolektor/Nasabah, `Absen`, `SerahTerimaPaket`, `ApprovalPenarikan`, `Pengaturan`, `KelolaKolektor`, serta sampel `ManajemenNasabah`.
- Output Blade `{!! !!}`, tautan dinamis, pencarian rahasia/kunci tertanam, isi log, dan dokumen `docs/`.

Belum dibaca baris demi baris: sebagian komponen admin besar (`Laporan`, `DetailNasabah`, `ManajemenProduk`, `HandoverKolektor`, dll.), file JS di `resources/js`, isi tiap migrasi (selain constraint unik), dan suite tes. Komponen admin dilindungi `role:admin` di tingkat route.

Tidak bisa dilakukan di sini:
- Kerentanan paket PHP (tidak ada `vendor/`, Packagist tidak terjangkau). Jalankan `composer audit`.
- Pengujian dinamis (aplikasi tidak dijalankan, tes tidak dieksekusi).
- Konfigurasi server (Nginx/Apache, PHP, MySQL).

## Koreksi dari laporan sementara

- Upload SVG BUKAN celah: di Laravel 13 aturan `image` tidak menerima SVG secara default. Jadi hanya saran penguatan.
- `Password::defaults` tidak dipakai di mana pun, jadi tidak bentrok dengan PIN 6 digit.
- Ganti PIN paksa lewat Livewire tidak terbukti bermasalah (middleware persisten Livewire dijalankan terhadap route halaman asal).

---

## A. Penyebab error saat deploy

### A1. Lima file view rusak di dalam zip (KRITIS)
Lokasi: `resources/views/pages/settings/`

```
#U26a1profile.blade.php
#U26a1security.blade.php
#U26a1appearance.blade.php
#U26a1delete-user-form.blade.php
#U26a1delete-user-modal.blade.php
```

Nama aslinya berawalan emoji ⚡ (`⚡profile.blade.php`, dst). Zip Windows merusak nama itu; README sudah memperingatkan.

Dampak: halaman profil, keamanan, dan tampilan kolektor/nasabah error. Semua akun baru berstatus `harus_ganti_pin`, lalu dialihkan ke halaman keamanan yang rusak, sehingga kolektor dan nasabah baru tidak bisa melewati login.

Catatan: nama rusak ini ada di zip yang diaudit. Bila folder asli di komputer Anda sudah berawalan ⚡, masalahnya hanya muncul jika deploy atau berbagi lewat zip Windows. Periksa dengan `ls resources/views/pages/settings/`.

Perbaikan: ganti nama kelima file ke nama aslinya. Deploy memakai `git clone` atau `git archive`, bukan zip Windows.

### A2. File mode-dev ikut terbawa
- `public/hot` berisi `http://[::1]:5173`, membuat CSS/JS tidak termuat di produksi.
- `public/fonts-manifest.dev.json` hanya untuk dev.
- `public/build/` harus dibangun ulang di server (`npm run build`).

`php artisan deploy:check` sudah mendeteksi butir ini.

### A3. Integrasi WhatsApp gagal diam-diam
Alamat API WhatsApp tidak bisa diisi dari halaman Pengaturan (hanya provider dan API key). Alamatnya wajib datang dari `WHATSAPP_API_URL` di `.env`, padahal tidak ada di `.env.example`, dan `config/services.php` tidak punya kunci `provider`. Akibatnya `WhatsAppService` mencatat "not configured" dan PIN awal nasabah baru tidak terkirim.

### A4. Konfigurasi produksi
`.env.example` bernilai `APP_ENV=local`, `LOG_LEVEL=debug`, `SESSION_SECURE_COOKIE=false`, `DB_USERNAME=root`, password kosong. Di server wajib: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...`, `SESSION_SECURE_COOKIE=true`, `LOG_LEVEL=warning`, user DB khusus dengan password kuat, `SEED_ADMIN_PIN`, dan `TRUSTED_PROXIES` bila di belakang proxy/load balancer.

### A5. Batas ukuran request di server
Absen mengirim selfie dan tanda tangan sebagai base64 lewat request Livewire (masing-masing hingga 1,5 MB, total bisa beberapa MB), dan serah terima mengunggah foto hingga 5 MB. Bila batas server lebih kecil, absen/serah terima gagal dengan error 413/422 tanpa pesan jelas. Setel `client_max_body_size 8M` (Nginx), `upload_max_filesize=8M` dan `post_max_size=10M` (PHP). `config/livewire.php` belum dipublikasikan, jadi batas unggah sementara Livewire memakai bawaan (12 MB).

---

## B. Celah keamanan

### B1. TINGGI — Kebocoran data via manipulasi ID Livewire (IDOR baca)
Properti publik Livewire bisa diubah dari browser. Dua komponen kolektor mengambil data tanpa membatasi ke nasabah binaan:

1. `app/Livewire/Kolektor/VerifikasiPenarikan.php`
   - `bukaModal(int $id)` menerima ID apa pun.
   - `render()` memakai `TransaksiPenarikan::with(['nasabah','produk'])->find($this->penarikanId)` tanpa scope.
2. `app/Livewire/Kolektor/InputSetoran.php`
   - `render()` memakai `TransaksiSetoran::with(['nasabah','produk'])->find($this->lastSetoranId)` tanpa scope.

3. `app/Livewire/Admin/SerahTerimaPaket.php` (komponen ini dipakai admin dan kolektor lewat route `role:admin|kolektor`)
   - `render()` memuat `KepesertaanPaket::with(['nasabah','produk'])->find($this->konfirmasiId)` dan `find($this->detailBuktiId)` tanpa scope kolektor. Daftar utama sudah di-scope, tetapi dua ID publik ini tidak.
   - `detailBuktiId` juga memuat siapa yang menyerahkan paket (log aktivitas) dan nama kolektor penanggung jawab.
   - Aksi `konfirmasi()` sendiri sudah aman (memeriksa `isNasabahBinaan`, metode pengambilan, dan status pencairan di dalam transaksi).

Dampak: kolektor mana pun bisa melihat nama nasabah, nominal, produk, dan data serah terima milik nasabah yang bukan binaannya. Aksi uang aman: `konfirmasi()` memanggil `isNasabahBinaan()` dan action memeriksa tanggung jawab kolektor.

Perbaikan: batasi query ke nasabah binaan aktif (`kolektor_nasabah` dengan `kolektor_id = Auth::id()` dan `status = 'aktif'`), atau gunakan `isNasabahBinaan()` sebelum memuat. Tambahkan tes di `tests/Feature/KolektorIdorTest.php`.

### B2. SEDANG — Tidak ada header keamanan
Tidak ada CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy. Aplikasi memakai kamera dan geolokasi (absen), sehingga `Permissions-Policy` harus mengizinkan `self`. CSP mulai dari mode Report-Only karena Livewire/Alpine memakai skrip inline.

### B3. SEDANG — PIN 6 digit dan penguncian akun
Sudah ada: rate limit 5/menit per nomor+IP dan 20/menit per IP, penguncian bertahap 15/60/360 menit, waktu respons disamakan. Sisa risiko: penyerang bisa sengaja mengunci akun orang lain (5 PIN salah). Seeder membuat admin dengan nomor tetap `081234567890`, mudah ditargetkan. Ganti nomor setelah login pertama dan pantau log `akun_terkunci_otomatis`.

### B4. RENDAH — Penguatan upload
Foto profil hanya divalidasi `image|max:2048` dan disimpan di disk `public`:
- `app/Livewire/Admin/Settings/Profile.php` (`updatedFotoBaru`)
- `app/Livewire/Admin/Pengaturan.php` (`simpanProfil`)

Ganti menjadi `image|mimes:jpg,jpeg,png,webp|max:2048`. Periksa komponen profil kolektor/nasabah (`pages/settings/⚡profile.blade.php`) dengan aturan yang sama.

### B5. SEDANG (keputusan bisnis) — Persetujuan ganda bisa nonaktif tanpa disadari
Di `ApprovalPenarikan::melewatiAmbangDuaApprover()`, persetujuan oleh dua admin berbeda tidak berlaku bila:
- pengaturan `penarikan_batas_dua_approver` bernilai 0 (nilai bawaan), atau
- jumlah admin kurang dari 3 (`MINIMAL_ADMIN_PERSETUJUAN_GANDA`), lalu penarikan jatuh ke mode satu admin.

Ini sesuai desain (README D11, dan dicatat sebagai `fallback_admin_kurang`), tetapi berarti kontrol ganda gagal-terbuka. Saat go-live: isi ambang sesuai kebijakan, pastikan minimal 3 admin aktif, dan pantau log `fallback_admin_kurang`. Pertimbangkan membatasi penghapusan atau penonaktifan admin.

### B6. INFO — Batas bawaan absensi
`Kolektor/Absen` memvalidasi gambar dengan ketat (hanya JPEG/PNG, `getimagesizefromstring`, `finfo`, maksimal 1,5 MB, disimpan di disk privat dengan nama UUID) dan ada constraint unik `(kolektor_id, tanggal)`. Namun lokasi GPS dan selfie berasal dari klien: aplikasi tidak bisa membuktikan GPS asli (fake GPS) atau foto dari kamera langsung, bukan dari galeri. Jika absensi dipakai untuk penilaian atau penggajian, pertimbangkan tinjauan manual oleh admin (`MonitoringAbsensi`) dan kebijakan SOP.

---

## C. Data sensitif di dalam zip

Zip berisi selfie dan tanda tangan kolektor (`storage/app/private/absensi/`), foto profil dan banner (`storage/app/public/`), file sementara `storage/app/private/livewire-tmp/`, `storage/logs/laravel.log` (76 MB) dan `browser.log` (17 MB). Jangan ikut ke server atau dibagikan. `laravel.log` juga memuat hash bcrypt PIN admin (4 kali, dari log SQL error lama), path lokal Windows (`C:/Users/User/Documents/Magang/...`), dan nomor HP uji. Kosongkan juga `database/database.sqlite`. Folder `.mcp.json`, `opencode.json`, `.codex`, `.codebase-memory`, `.claude`, `.agents` tidak perlu di server.

---

## D. Aplikasi mobile dan CI

Sudah baik: `cleartext:false`, `webContentsDebuggingEnabled:false`, `allowMixedContent:false`, `allowNavigation` dibatasi ke domain aplikasi.

Perlu diperbaiki:
1. `mobile/scripts/build-flavor.sh`: `apksigner` memakai `--key-pass "pass:$ANDROID_KEYSTORE_PASSWORD"`, bukan `ANDROID_KEY_PASSWORD`. Penandatanganan gagal bila keduanya berbeda.
2. Android `allowBackup` bawaan Capacitor aktif. Untuk aplikasi keuangan setel `android:allowBackup="false"` di `AndroidManifest.xml` setelah `npx cap add android`.
3. `.github/workflows/build-apk.yml`: tidak ada blok `permissions:`; action tidak dikunci ke SHA (berbeda dengan `tests.yml`); `${{ inputs.flavor }}` disisipkan langsung ke skrip shell (dibatasi tipe `choice`, risiko rendah, tapi lebih baik lewat variabel lingkungan).
4. Tanpa secret keystore, workflow menghasilkan APK debug. Jangan dibagikan ke pengguna.

---

## E. Dependensi

- Root: 11 kerentanan (5 kritis: `shell-quote`, `tinypool`; 2 tinggi: Vite 8.2.0–8.2.2 dan `launch-editor`). Semuanya toolchain build, tidak berjalan di produksi. Jalankan `npm audit fix`. Jangan menjalankan dev server Vite di produksi.
- `mobile/`: 10 kerentanan (`tar`, `uuid` via `@capacitor/assets`/`xcode`), hanya di mesin build. `npm audit fix` aman; perbaikan `--force` menaikkan `@capacitor/assets` ke 4.0.0 (breaking), uji dulu.
- PHP: belum diperiksa. Jalankan `composer audit`.

---

## F. Yang sudah baik

- Rute dilindungi `auth`, `active`, `idle`, `role`; middleware dipasang sebagai persistent middleware Livewire.
- Foto absensi, bukti penarikan, bukti serah terima: disk privat, akses dicek per pengguna.
- Login: rate limit, penguncian bertahap, timing disamakan, admin tanpa cookie remember, sesi admin kedaluwarsa saat idle (30 menit).
- API key WhatsApp terenkripsi; PIN awal lewat job `ShouldBeEncrypted`; nomor di log dimasker.
- Ekspor CSV memakai `CsvSafe` (anti formula injection).
- Aksi saldo memakai `lockForUpdate`; ada guard tolak SQLite di produksi; `DB::prohibitDestructiveCommands` di produksi.
- Query mentah tidak memakai input pengguna (kolom Komisi di-whitelist).
- Tidak ada `eval`/`exec`/`unserialize`/`dd()` di kode aplikasi.
- Akun demo tidak dibuat di produksi; 164 file tes; `deploy:check` tersedia.
- Alur PIN: ganti PIN memakai rate limit dan mencabut sesi lain; reset admin menghasilkan PIN acak non-lemah, mencabut semua sesi, dan log tidak menyimpan PIN; PIN awal hanya lewat job terenkripsi.
- Pembatalan penarikan oleh nasabah dikunci (pemilik saja, `lockForUpdate`, saldo dikembalikan di transaksi yang sama); approval ganda mencegah admin yang sama menyetujui dua kali.
- Semua 17 model memiliki perlindungan mass assignment; tidak ada `request()->all()` atau `forceFill`.
- Output `{!! !!}` di Blade hanya berisi string statis (ikon SVG, tanda rupiah); tautan `wa.me` hanya menerima digit; cookie `portal_login` divalidasi terhadap daftar putih (tidak ada open redirect).
- Tidak ditemukan kunci API, token, atau private key tertanam di repo. Fortify tidak mengaktifkan fitur reset password, jadi tidak ada jalur reset lewat email.
- `docs/plan-log.md` mencatat banyak perbaikan keamanan sebelumnya (backup tanpa password di argumen proses, token WA terenkripsi, seeder aman produksi) dan 674 tes hijau di commit terakhir yang tercatat.

---

## G. Urutan kerja sebelum deploy

1. Perbaiki lima nama file `#U26a1…`; deploy lewat git.
2. Hapus `public/hot`, file dev, log, dan data selfie/tanda tangan/livewire-tmp dari artefak.
3. Siapkan `.env` produksi (termasuk `WHATSAPP_API_URL`, `WHATSAPP_API_TOKEN`).
4. Tutup IDOR B1 (tiga komponen, termasuk `SerahTerimaPaket`) dan tambahkan tesnya.
5. Pasang header keamanan; setel `allowBackup=false`; perbaiki `--key-pass`; setel batas ukuran request server (A5); isi ambang persetujuan ganda dan pastikan minimal 3 admin (B5).
6. `npm audit fix`, `composer audit`.
7. Jalankan `composer lint:check`, `composer types:check`, `php artisan test`, `composer deploy:check`.
8. Di server: `php artisan migrate --force`, `storage:link`, `config:cache`, `route:cache`, `view:cache`; pasang cron `schedule:run` dan `queue:work` (lihat README).

---

## H. Server dan infrastruktur (digabung dari audit agent, dengan koreksi)

### H1. Persyaratan server (penyebab error 500 bila terlewat)
- **MySQL 8 atau MariaDB 10.2+.** Dengan `APP_ENV=production` dan `DB_CONNECTION=sqlite`, `AppServiceProvider` melempar `RuntimeException` pada setiap request.
- **Ekstensi PHP:** `bcmath`, `mbstring`, `pdo_mysql`, `curl`, `xml`, `zip`. `composer.json` mewajibkan `ext-bcmath`, sehingga `composer install` menolak berjalan tanpa itu. Risiko sisa: CLI punya ekstensi, PHP-FPM tidak. Periksa keduanya dengan `php -m` dan `php-fpm8.3 -m`. Di Ubuntu/Debian `fileinfo` sudah ada di `php8.3-common`; jangan menginstalnya sebagai paket terpisah.
- **Klien MySQL (`mysqldump`)** di server aplikasi, karena fitur Backup di panel admin memanggilnya lewat `Process`. Pakai klien yang sesuai versi server (mis. `mysql-client` untuk MySQL 8) agar plugin autentikasi cocok.
- **Queue worker** (Supervisor) untuk `QUEUE_CONNECTION=database`. Tanpa worker, PIN awal dan notifikasi WhatsApp menumpuk dan tidak pernah terkirim.
- **Cron** `schedule:run` tiap menit. Tanpa itu `penarikan:kedaluwarsakan`, `jadwal:generate`, dan `paket:hitung-ulang` tidak jalan.

### H2. Risiko server
- **DocumentRoot harus `public/`.** Bila mengarah ke folder induk, `/.env` dan `/.git/` bisa diunduh.
- **`TRUSTED_PROXIES` salah membuat login terkena DoS global.** Limiter login 20 percobaan per IP memakai `request()->ip()`. Di belakang Cloudflare atau load balancer tanpa pengaturan ini, semua pengguna terlihat berasal dari IP proxy dan berbagi satu kuota. Sebaliknya, `*` tanpa proxy yang membersihkan `X-Forwarded-For` memungkinkan pemalsuan IP. Bila Nginx langsung ke PHP-FPM di server yang sama, IP klien sudah benar dan variabel ini dibiarkan kosong. Isi hanya bila ada proxy di depan, dengan IP atau range proxy itu.
- **Jangan menjalankan `php artisan key:generate` setelah produksi berjalan.** `wa_api_key` (`AdminSetting`) dan job PIN awal (`ShouldBeEncrypted`) memakai `APP_KEY`; nilainya tidak bisa didekripsi lagi dan sesi aktif putus. Simpan `APP_KEY` di tempat aman.
- **Blokir eksekusi PHP selain `index.php`** (lihat konfigurasi Nginx di bawah), sehingga file di `public/storage/` tidak pernah bisa dijalankan.
- `APP_DEBUG=true` di produksi membuka halaman error yang menampilkan konfigurasi dan variabel lingkungan.

### H3. Contoh konfigurasi Nginx
Ganti domain dan path. Butuh Nginx 1.25.1+ untuk `http2 on;` (pada versi lama pakai `listen 443 ssl http2;`). Aktifkan HSTS hanya setelah HTTPS terbukti berjalan.

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name tabungan.contoh.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    http2 on;
    server_name tabungan.contoh.com;

    root /var/www/mobile-savings/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/tabungan.contoh.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tabungan.contoh.com/privkey.pem;

    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "camera=(self), geolocation=(self), microphone=()" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    client_max_body_size 8M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Hanya index.php yang boleh dieksekusi
    location = /index.php {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }
    location ~ \.php$ { return 404; }

    # File tersembunyi (.env, .git), kecuali .well-known
    location ~ /\.(?!well-known) { deny all; }
}
```

Catatan: `X-XSS-Protection` sudah usang dan tidak disertakan. Jika header keamanan dipasang di Nginx, jangan menduplikasinya di middleware Laravel (T3 di prompt).

### H4. Supervisor (queue worker)
```ini
[program:mobile-savings-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/mobile-savings/artisan queue:work database --sleep=3 --tries=3 --timeout=60 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/mobile-savings-worker.log
stopwaitsecs=70
```
Aktifkan: `sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start mobile-savings-worker:*`.

### H5. Cron
`crontab -e -u www-data`:
```
* * * * * cd /var/www/mobile-savings && php artisan schedule:run >> /dev/null 2>&1
```

### H6. `.env` produksi (contoh)
```
APP_NAME="Tabungan Digital"
APP_ENV=production
APP_KEY=                      # dibuat sekali dengan key:generate, lalu jangan diubah lagi
APP_DEBUG=false
APP_URL=https://tabungan.contoh.com
APP_TIMEZONE=Asia/Jakarta
LOG_LEVEL=warning
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tabungan_produksi
DB_USERNAME=user_produksi     # bukan root
DB_PASSWORD=                  # isi password kuat dan unik
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=database
CACHE_STORE=database
TRUSTED_PROXIES=              # kosong bila Nginx langsung ke PHP-FPM; isi IP proxy bila ada Cloudflare/LB
WHATSAPP_PROVIDER=
WHATSAPP_API_URL=
WHATSAPP_API_TOKEN=
SEED_ADMIN_PIN=               # PIN awal admin (6 digit, tidak lemah); wajib diganti saat login pertama
```

### H7. Runbook rilis
```bash
composer install --no-dev --optimize-autoloader
# Bangun aset di CI atau mesin build lalu kirim public/build ke server.
# Bila membangun di server: npm ci && npm run build
rm -f public/hot public/fonts-manifest.dev.json
php artisan migrate --force
php artisan db:seed --force          # HANYA pada rilis pertama; membuat role dan admin
php artisan storage:link
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache
php artisan queue:restart
php artisan deploy:check
```
`migrate` tidak membuat role maupun admin. Tanpa `db:seed`, tidak ada akun yang bisa login. Menjalankan seeder dua kali gagal karena nomor HP admin unik.

### H8. Keterbatasan klaim agent
- Klaim "bebas IDOR" tidak tepat; lihat B1 (tiga komponen).
- Klaim "1.012 tes lulus" tidak bisa saya verifikasi. Pastikan tes dijalankan di MySQL (bukan SQLite), karena proyek bergantung pada `lockForUpdate`.
- `TRUSTED_PROXIES=127.0.0.1` pada contoh agent hanya benar bila ada proxy lokal yang meneruskan header `X-Forwarded-For`; lihat H2.