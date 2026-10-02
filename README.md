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
- `TRUSTED_PROXIES=<ip-load-balancer>` — isi hanya jika di belakang proxy tepercaya; kosong = tidak percaya proxy sama sekali. `*` hanya bila seluruh trafik melewati load balancer tepercaya.

Setelah deploy, jalankan:
```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Pengujian

Test selalu berjalan pada database terpisah `tabungan_digital_test` (di-`force` di `phpunit.xml`); buat dulu satu kali:

```bash
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS tabungan_digital_test CHARACTER SET utf8mb4"
```