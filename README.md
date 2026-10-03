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

Catatan deploy:

- **Jangan deploy dari zip Windows** — kompresi zip merusak nama file ber-emoji (mis. `⚡security.blade.php` menjadi `#U26a1security.blade.php`) sehingga halaman Volt tidak ditemukan. Gunakan `git clone` di server atau `git archive` untuk membuat artefak.
- **Jangan sertakan `bootstrap/cache/*.php`** dari mesin development — file cache config/routes/views lama akan bentrok dengan `config:cache`/`route:cache` di server.

## Pengujian

Test selalu berjalan pada database terpisah `tabungan_digital_test` (di-`force` di `phpunit.xml`); buat dulu satu kali:

```bash
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS tabungan_digital_test CHARACTER SET utf8mb4"
```