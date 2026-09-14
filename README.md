# Mobile_Savings

## Deployment

Pastikan `.env` di production memiliki setting berikut:

```
APP_ENV=production
APP_DEBUG=false
APP_KEY=<your-app-key>
```

`APP_DEBUG=false` wajib diaktifkan di production agar detail error tidak ditampilkan ke pengguna.

Setelah deploy, jalankan:
```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```