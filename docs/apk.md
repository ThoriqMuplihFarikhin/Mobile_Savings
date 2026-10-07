# APK Android — Panduan Lengkap

Tiga APK dibangun dari satu proyek Capacitor (`mobile/`) oleh GitHub Actions. Setiap APK adalah WebView yang membuka portal web yang sudah ada; login, sesi, unggah foto, selfie absen, GPS, dan tanda tangan berjalan persis seperti di peramban. Detail teknis proyek: [`../mobile/README.md`](../mobile/README.md).

## 1. Membuat keystore (sekali saja)

Keystore menandatangani APK release. Buat **di komputer tepercaya**, jangan pernah masuk git (`*.jks` sudah di-`.gitignore`):

```bash
keytool -genkeypair -v \
  -keystore tabungan-release.jks \
  -keyalg RSA -keysize 2048 -validity 10000 \
  -alias tabungan
```

Catat dengan aman: file keystore, password keystore, alias (`tabungan`), dan password kunci. Hilang = tidak bisa memperbarui aplikasi yang sudah terpasang.

Encode ke base64 (Linux/macOS/Git Bash):

```bash
base64 -w0 tabungan-release.jks        # tanpa -w0 di macOS: base64 -i tabungan-release.jks -o -
```

## 2. Mengisi konfigurasi GitHub

Di repositori: **Settings → Secrets and variables → Actions**.

**Secrets** (tab *Secrets*):

| Nama                          | Isi                                              |
|-------------------------------|--------------------------------------------------|
| `ANDROID_KEYSTORE_BASE64`     | hasil base64 file keystore (langkah 1)           |
| `ANDROID_KEYSTORE_PASSWORD`   | password keystore                                |
| `ANDROID_KEY_ALIAS`           | alias (mis. `tabungan`)                          |
| `ANDROID_KEY_PASSWORD`        | password kunci/alias                             |

**Variables** (tab *Variables*):

| Nama         | Isi                                                  |
|--------------|------------------------------------------------------|
| `APP_DOMAIN` | domain produksi HTTPS tanpa skema, mis. `app.contoh.com` — portal harus sudah aktif di domain ini karena WebView langsung membukanya |

Keempat secret bersifat **opsional**: tanpa secret, workflow tetap berjalan dan menghasilkan **APK debug** untuk uji internal. Dengan secret, hasilnya APK release bertanda tangan.

## 3. Menjalankan workflow

Workflow: `.github/workflows/build-apk.yml` (tab *Actions → build-apk*).

- **Manual (workflow_dispatch):** *Run workflow* → pilihan `flavor`: `all` (default, ketiga varian) atau satu flavor saja.
- **Tag `apk-*`:** push tag, mis. `apk-1.2.0` → membangun ketiga varian dengan `VERSION_NAME=1.2.0`:

  ```bash
  git tag apk-1.2.0
  git push origin apk-1.2.0
  ```

`versionCode` diambil dari `github.run_number` (otomatis naik tiap build). Job `plan` menentukan daftar flavor + versi; job `build` berjalan matriks per flavor (Java 17 + Node 20), memanggil `mobile/scripts/build-flavor.sh`, lalu mengunggah artefak.

## 4. Mengunduh & memasang APK

1. Di halaman run workflow → bagian *Artifacts* → unduh `tabungan-<flavor>-<versi>` (zip).
2. Salin `tabungan-<flavor>-<versi>.apk` ke perangkat Android (atau lewat `adb install file.apk`).
3. Di perangkat: **Setelan → Keamanan/Privasi → Install aplikasi dari sumber tidak dikenal** → izinkan untuk pengelola berkas/peramban yang dipakai → buka APK → pasang.
4. Buka aplikasi → halaman login sesuai flavor → login dengan akun portal (kredensial sama dengan web; sesi memakai cookie domain yang sama).

Prasyarat: `APP_DOMAIN` sudah terisi dan portal sudah berjalan HTTPS di domain itu; tanpa itu WebView menampilkan halaman offline (`/offline`).

## 5. Memperbarui ikon / splash / nama

- Ikon & splash: ganti `mobile/assets/<flavor>/icon.png` dan `splash.png` (PNG **1024x1024**), jalankan ulang build — `@capacitor/assets` merender semua ukuran Android.
- Nama aplikasi: ubah `"name"` di `mobile/flavors.json` (otomatis jadi label launcher via `capacitor_label`).
- Warna aksen hanya berdampak pada aset placeholder — ganti PNG dengan desain final dari desainer.

## 6. Catatan kebijakan Play Store

WebView murni (aplikasi yang hanya memuat situs) berisiko **ditolak Google Play** dengan alasan *minimum functionality*. Untuk distribusi langsung (APK ke pengguna), sideload internal, atau penggunaan organisasi sendiri, hal ini tidak masalah. Bila nanti ingin masuk Play Store, siapkan penilaian ulang (mis. penambahan fungsionalitas native) terlebih dahulu.

## 7. Checklist uji di perangkat Android nyata

Kriteria "selesai" P8: tiga APK terpasang dan berhasil login ke portal masing-masing. Daftar ini dijalankan manual setiap rilis:

- [ ] Pasang ketiga APK (atau minimal satu per role) — instal sukses, ikon/nama benar.
- [ ] Login + **ingat saya** di setiap portal (nasabah `/login`, kolektor `/login/kolektor`, admin `/login/admin`).
- [ ] Unggah foto (bukti serah-terima) — kamera terbuka (`accept="image/*" capture`), kompresi jalan.
- [ ] Selfie absen + GPS + tanda tangan (Leaflet & signature_pad termuat dari bundel, tanpa CDN).
- [ ] Tombol **Back Android**: mundur di riwayat; di layar login awal, aplikasi keluar.
- [ ] Matikan data → muat ulang → halaman `/offline` tampil (bukan layar kosong).
- [ ] Aksi uang di portal admin tetap minta konfirmasi + approval ganda.
- [ ] 360 px (layar kecil): bottom-nav kolektor & admin terpakai penuh, tidak ada scroll horizontal.

## 8. Checklist sebelum build produksi

- [ ] `APP_URL=https://<domain>` dan `SESSION_SECURE_COOKIE=true` di `.env` produksi (lihat checklist rilis di README utama).
- [ ] `vars.APP_DOMAIN` = domain yang sama, tanpa skema.
- [ ] 4 secret keystore terisi (release, bukan debug).
- [ ] Tag `apk-<versi>` dibuat dari commit rilis.
