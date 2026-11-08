# Panduan Deploy Server Produksi — Mobile_Savings

Panduan ini ditujukan untuk menyiapkan dan mengoperasikan aplikasi **Mobile_Savings** di server produksi berbasis **Ubuntu 22.04 LTS / 24.04 LTS**.

---

## 1. Kebutuhan Sistem & Spesifikasi Server

### Spesifikasi Minimum
- **CPU**: 2 vCPU
- **RAM**: 2 GB (disarankan 4 GB bila menjalankan kompilasi aset di server)
- **Disk**: 20 GB SSD
- **OS**: Ubuntu 22.04 LTS atau 24.04 LTS 64-bit

### Perangkat Lunak Wajib
1. **PHP 8.3** beserta modul:
   - `php8.3-fpm`
   - `php8.3-cli`
   - `php8.3-mysql` (`pdo_mysql`)
   - `php8.3-mbstring`
   - `php8.3-bcmath` (wajib untuk perhitungan desimal/keuangan)
   - `php8.3-gd` atau `php8.3-imagick` (wajib untuk pemrosesan upload foto profil/bukti)
   - `php8.3-curl` (komunikasi gateway WhatsApp)
   - `php8.3-intl`
   - `php8.3-zip`
   - `php8.3-xml`
2. **MySQL 8.0+**:
   - **Wajib MySQL asli (bukan MariaDB versi lama)** karena sistem memerlukan fitur CTE, `JSON_TABLE`, serta implementasi penuh row-level locking (`SELECT ... FOR UPDATE`) pada engine InnoDB untuk keamanan transaksi saldo.
   - Karakter set database: `utf8mb4` dengan collation `utf8mb4_unicode_ci`.
3. **mysqldump CLI**:
   - Wajib terpasang dan berada pada system PATH (biasanya bawaan paket `mysql-client`).
   - Diperlukan untuk fitur backup basis data manual maupun terjadwal dari panel admin.
4. **Nginx Web Server** (versi 1.18+).
5. **Supervisor** untuk manajemen proses background queue worker.
6. **Composer 2.x**.
7. **Node.js 20 LTS** & **npm** (opsional bila kompilasi aset frontend dilakukan di CI/CD).

### Perintah Instalasi Paket (Ubuntu)
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common curl git unzip supervisor nginx mysql-client

# Tambahkan repositori PHP Ondřej Surý
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Pasang PHP 8.3 dan ekstensi yang dibutuhkan
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-bcmath \
    php8.3-gd php8.3-curl php8.3-intl php8.3-zip php8.3-xml

# Pasang Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

## 2. Konfigurasi Batas Ukuran Request (Nginx & PHP)

Aplikasi menangani unggahan foto profil nasabah dan foto bukti penarikan/setoran dengan batas validasi 2 MB. Agar request tidak terpotong sebelum mencapai validasi aplikasi:

1. **PHP (`/etc/php/8.3/fpm/php.ini`)**:
   ```ini
   upload_max_filesize = 8M
   post_max_size = 10M
   memory_limit = 256M
   max_execution_time = 60
   ```
   Muat ulang layanan PHP-FPM:
   ```bash
   sudo systemctl restart php8.3-fpm
   ```

2. **Nginx (`/etc/nginx/sites-available/mobile-savings`)**:
   - Setel `client_max_body_size 8M;`.
   - **Catatan Keamanan & UX**: Jika ukuran payload unggahan melebihi `client_max_body_size`, Nginx akan langsung memutus koneksi dengan HTTP 413 (*Request Entity Too Large*). Komponen Livewire di sisi klien tidak dapat menangkap pesan validasi bahasa Indonesia dan akan menampilkan galat generik jaringan. Pastikan batas di Nginx selalu lebih besar dari batas validasi aplikasi.

---

## 3. Konfigurasi Nginx Lengkap

Letakkan konfigurasi di `/etc/nginx/sites-available/mobile-savings` dan tautkan ke `sites-enabled`:

```nginx
# Pengalihan HTTP ke HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name tabungan.contoh.com;
    return 301 https://$host$request_uri;
}

# Blok Server HTTPS
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name tabungan.contoh.com;

    root /var/www/mobile-savings/public;
    index index.php index.html;

    # Sertifikat SSL (Let's Encrypt / Certbot)
    ssl_certificate /etc/letsencrypt/live/tabungan.contoh.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tabungan.contoh.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Batas ukuran payload
    client_max_body_size 8M;

    # Log
    access_log /var/log/nginx/mobile_savings_access.log;
    error_log /var/log/nginx/mobile_savings_error.log;

    # Rute Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Pemrosesan PHP-FPM
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Keamanan: Tolak akses langsung ke file tersembunyi (.env, .git, dll)
    location ~ /\.(?!well-known).* {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Keamanan: Cegah eksekusi PHP pada folder storage publik
    location ~ ^/storage/.*\.php$ {
        deny all;
    }

    # Cache aset statis publik
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg|webp)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
        try_files $uri =404;
    }
}
```

Aktifkan konfigurasi:
```bash
sudo ln -s /etc/nginx/sites-available/mobile-savings /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 4. Konfigurasi Lingkungan (`.env`) & Keamanan Kunci

Salin dari contoh produksi:
```bash
cp .env.production.example .env
```

### Parameter Kunci yang Wajib Diperiksa:
1. `APP_ENV=production` dan `APP_DEBUG=false` (Wajib untuk mencegah kebocoran stack trace dan kredensial).
2. `APP_URL=https://tabungan.contoh.com` (Gunakan skema HTTPS resmi; rujukan URL aplikasi, PWA manifest, dan WebView Capacitor).
3. `APP_KEY`:
   > [!CAUTION]
   > **Peringatan Rotasi `APP_KEY`**:
   > Kunci enkripsi ini digunakan untuk mengenkripsi cookie sesi, payload token, dan data rahasia lainnya. Mengubah atau me-generate ulang `APP_KEY` (`php artisan key:generate`) pada basis data yang sudah berjalan akan menyebabkan seluruh sesi pengguna terputus dan data yang terenkripsi tidak dapat didekripsi kembali. Jangan ubah `APP_KEY` setelah aplikasi aktif digunakan di produksi.
4. `SESSION_SECURE_COOKIE=true` dan `SESSION_ENCRYPT=true` (Menjamin cookie sesi hanya dikirim melalui jalur SSL terenkripsi).
5. `TRUSTED_PROXIES`:
   - **Langsung ke server Nginx (tanpa proxy luar)**: Kosongkan (`TRUSTED_PROXIES=`).
   - **Di balik Cloudflare**: Gunakan `TRUSTED_PROXIES="**"` atau daftar CIDR resmi Cloudflare agar IP asli nasabah/kolektor terbaca untuk keperluan rate-limiting dan log audit.
   - **Di balik Load Balancer AWS/GCP/Nginx internal**: Isi dengan subnet IP privat load balancer, mis. `10.0.0.0/8` atau `172.16.0.0/12`.
6. `QUEUE_CONNECTION=database` dan `CACHE_STORE=database`.
7. `WHATSAPP_API_URL`, `WHATSAPP_PROVIDER`, `WHATSAPP_API_TOKEN`:
   - `WHATSAPP_API_URL` wajib disetel di file `.env` (tidak dapat disetel melalui UI admin demi keamanan).
8. `SEED_ADMIN_PIN`:
   - PIN awal admin untuk seeding database. **Wajib segera diganti melalui menu profil admin setelah login pertama**.

---

## 5. Supervisor Worker (Background Queue)

Aplikasi mengirimkan notifikasi WhatsApp dan pengingat tunggakan melalui queue database. Pasang worker daemon dengan Supervisor di `/etc/supervisor/conf.d/mobile-savings-worker.conf`:

```ini
[program:mobile-savings-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/mobile-savings/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/mobile-savings-worker.log
stopwaitsecs=3600
```

Aktifkan worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start mobile-savings-worker:*
```

---

## 6. Penjadwal Tugas (Cron Scheduler)

Aplikasi memiliki serangkaian tugas penting harian (penghitungan tunggakan paket, generate jadwal kunjungan kolektor, kedaluwarsa penarikan, pengingat WhatsApp). Tambahkan entri crontab untuk pengguna web server (`www-data`):

```bash
sudo crontab -u www-data -e
```

Tambahkan baris berikut:
```cron
* * * * * cd /var/www/mobile-savings && php artisan schedule:run >> /dev/null 2>&1
```

### Rincian Tugas yang Dijalankan Scheduler:
- **00:00** — `queue:prune-failed --hours=168` (membersihkan job antrean yang gagal > 7 hari)
- **00:10** — `paket:hitung-ulang` (menghitung ulang status, progres, dan tunggakan kepesertaan paket)
- **00:30** — `jadwal:generate` (membuat jadwal kunjungan kolektor harian)
- **00:40** — `penarikan:kedaluwarsakan` (membatalkan dan menandai pengajuan penarikan yang telah lewat batas waktu)
- **08:00** — `paket:kirim-pengingat` (mengirimkan notifikasi tagihan/tunggakan via WhatsApp & in-app)

---

## 7. Runbook Rilis (Deployment & Pembaruan Aplikasi)

Gunakan alur rilis berurutan berikut setiap kali memperbarui kode di server produksi:

```bash
# 1. Pindah ke direktori proyek
cd /var/www/mobile-savings

# 2. Ambil perubahan terbaru dari git (JANGAN ekstrak zip dari Windows!)
git pull origin main

# 3. Pasang dependensi PHP tanpa paket pengembangan
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# 4. Jalankan migrasi basis data (aman & idempoten)
php artisan migrate --force

# 5. Pasang dependensi frontend dan kompilasi aset
npm ci
npm run build

# 6. Bersihkan dan segarkan cache konfigurasi, rute, dan tampilan
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Restart queue worker agar memuat kode PHP terbaru
php artisan queue:restart

# 8. Verifikasi kesiapan rilis produksi
php artisan deploy:check
```

---

## 8. Checklist Verifikasi Pasca-Deploy

Jalankan pemeriksaan berkala:
```bash
# Cek kepatuhan setelan produksi
php artisan deploy:check

# Periksa status worker Supervisor
sudo supervisorctl status

# Periksa status log queue dan error
tail -n 50 storage/logs/laravel.log
```
