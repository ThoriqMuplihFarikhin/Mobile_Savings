# Tabungan Mobile (Capacitor)

Shell Android untuk tiga aplikasi Tabungan — **nasabah**, **kolektor**, dan **admin** — memakai [Capacitor](https://capacitorjs.com). Setiap flavor adalah aplikasi terpisah (`applicationId` berbeda) yang menampilkan aplikasi web yang sudah berjalan (Livewire) di WebView, jadi seluruh logika tetap di sisi server.

Panduan lengkap (keystore, GitHub Actions, instalasi APK, catatan Play Store): lihat [`../docs/apk.md`](../docs/apk.md).

## Struktur

```
mobile/
├── package.json            # dependensi Capacitor (core, android, cli, app, assets)
├── flavors.json            # 3 varian: aplikasiId, nama, startPath, aksen warna
├── template/
│   └── capacitor.config.json   # template konfigurasi (placeholder {{...}})
├── scripts/
│   └── build-flavor.sh     # membangun satu flavor dari nol sampai APK
├── assets/
│   ├── nasabah/icon.png + splash.png    # placeholder 1024x1024
│   ├── kolektor/icon.png + splash.png
│   └── admin/icon.png + splash.png
└── build/<flavor>/         # hasil render (di-gitignore; dibuat oleh skrip)
```

## Flavor

| Flavor   | applicationId         | Nama aplikasi  | Start path     | Aksen  |
|----------|-----------------------|----------------|----------------|--------|
| nasabah  | `id.tabungan.nasabah` | Tabungan       | `/login`       | hijau  |
| kolektor | `id.tabungan.kolektor`| Tabungan Kolektor | `/login/kolektor` | biru |
| admin    | `id.tabungan.admin`   | Tabungan Admin | `/login/admin` | gelap  |

`applicationId` berbeda agar ketiganya bisa terpasang bersamaan di satu perangkat.

## Build lokal

Prasyarat: Node 20+, JDK 17, Android SDK (dengan `ANDROID_HOME` yang sudah diset), Bash.

```bash
APP_DOMAIN=app.contoh.com bash mobile/scripts/build-flavor.sh kolektor
```

Variabel lingkungan:

| Variabel                    | Wajib | Keterangan                                              |
|-----------------------------|-------|---------------------------------------------------------|
| `APP_DOMAIN`                | ya    | Domain produksi HTTPS tanpa skema (mis. `app.contoh.com`) |
| `VERSION_NAME`              | tidak | Nama versi APK (default `0.0.0`)                        |
| `VERSION_CODE`              | tidak | `versionCode` Android (default `1`)                     |
| `ANDROID_KEYSTORE_BASE64`   | tidak | base64 isi keystore release; **tanpa ini hasilnya APK debug** |
| `ANDROID_KEYSTORE_PASSWORD` | tidak |                                                         |
| `ANDROID_KEY_ALIAS`         | tidak |                                                         |
| `ANDROID_KEY_PASSWORD`      | tidak |                                                         |

Hasil: `mobile/build/<flavor>/tabungan-<flavor>-<versi>[-debug].apk`.

Build dijalankan di GitHub Actions lewat `.github/workflows/build-apk.yml` (lihat `docs/apk.md`); lokal biasanya hanya dipakai untuk menguji perubahan skrip.

## Konfigurasi yang dihasilkan

`template/capacitor.config.json` dirender oleh skrip:

- `server.url = https://<APP_DOMAIN><startPath>` — WebView langsung membuka portal login flavor.
- `cleartext = false`, `allowMixedContent = false` — hanya HTTPS.
- `allowNavigation = [<APP_DOMAIN>]` — tolak navigasi ke domain lain.
- `errorPath = /offline` — bila beban awal gagal, tampilkan halaman offline lokal (tanpa koneksi).
- `android.appendUserAgent = TabunganApp/<flavor>` — jejak identitas flavor di server.

Tombol **Back Android** ditangani di `resources/views/partials/head.blade.php`: mundur di riwayat browser, keluar aplikasi di halaman awal (dideteksi via plugin `@capacitor/app`, aman di peramban biasa).

## Ikon & splash

Ganti `assets/<flavor>/icon.png` dan `assets/<flavor>/splash.png` (PNG persegi **1024x1024**), lalu jalankan build ulang — `@capacitor/assets` menghasilkan seluruh ukuran Android. Ikon yang sekarang adalah placeholder geometris berwarna aksen.

## Keamanan

- **Jangan pernah commit keystore** — `*.jks`/`*.keystore` sudah masuk `.gitignore`. Simpan file asli di pengelola kata sandi; di CI cukup isi 4 secret (base64 + kata sandi + alias).
- `mobile/build/` dan `mobile/node_modules/` juga di-gitignore.
- Perangkat lunak dependensi: `npm audit` melaporkan 6 kerentanan bawaan (1 high, 5 critical) dari rantai dependensi build — jalankan `npm audit fix` hanya setelah persetujuan pemilik.
