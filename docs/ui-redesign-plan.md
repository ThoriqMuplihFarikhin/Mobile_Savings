# IMPLEMENTATION PLAN: Redesain UI/UX Portal Nasabah & Kolektor (Login sampai semua layar)

> Dokumen ini adalah instruksi kerja untuk AI agent. Taruh di `docs/ui-redesign-plan.md`.
> Kerjakan **berurutan per fase**, satu task = satu commit. Format mengikuti `docs/implementation-plan.md` (Fase, ID task, keputusan `D#`).
> Stack terkunci: Laravel 13, Livewire 4, Flux 2, Fortify, Tailwind 4 (`@tailwindcss/vite`), Vite 8, Pest 4. **Tidak ada dependency baru.**
> Acuan visual: prototipe `docs/design/redesain-nasabah-kolektor.html` (12 layar nasabah, 4 layar kolektor) dan `docs/design/login-tabungan-digital.html` (login). Keduanya hanya acuan tampilan, **bukan sumber kebenaran**. Sumber kebenaran: `DESIGN.md`, kode, dan tes yang ada.

---

## 0. ATURAN KERJA (WAJIB untuk semua task)

1. **Baca dulu** `DESIGN.md` (seluruhnya), `CLAUDE.md`, `AGENTS.md`, `prd.md`, `docs/plan-log.md`, dan `docs/implementation-plan.md`. Jangan membalik keputusan lama (K1–K6, D1–D32).
2. Branch `feat/ui-redesign-2026-10`. Satu task = satu commit: `feat(U3.2): <ringkasan>`. Jangan menggabungkan dua task.
3. **Desain yang sudah ada di project lebih tinggi derajatnya daripada prototipe.** `DESIGN.md` sudah mendefinisikan kanvas hangat `#f3f7f1`, hero hijau tua, kartu radius 20px, ubin kategori, cincin progres, stepper, layar sukses, empty state, dan panel login. Prototipe hanya menunjukkan arah. Bila prototipe bertentangan dengan `DESIGN.md`, **ikuti `DESIGN.md`** dan catat penyimpangan di plan-log. Perubahan pada `DESIGN.md` hanya lewat task U1.1 dan harus tercatat sebagai keputusan `D#`.
4. **Red → Green** untuk perilaku (Pest). Untuk perubahan murni visual, tulis tes struktur (atribut `data-test`, kelas token, teks), bukan tes piksel.
5. Setiap task lolos sebelum commit: `vendor/bin/pint --dirty`, `php artisan test --compact` (seluruh suite), PHPStan tidak menambah error pada file yang disentuh, dan `npm run build` sukses.
6. **Dilarang mengubah**: migrasi, model, kebijakan PIN, middleware, rute, rate limiter, logika uang/kas/rekonsiliasi, `FortifyServiceProvider`, `PortalLogin`. Redesain ini **hanya lapisan tampilan** (Blade, CSS, JS Alpine) dan komponen Blade pembungkus. Jika sebuah layar butuh data yang belum ada, **jangan membuat kolom atau query baru diam-diam**: tandai `[BUTUH KEPUTUSAN]`, pakai default di bagian 1, dan catat.
7. **Pertahankan semua atribut `data-test`** yang dipakai tes `GayaUi*`, `BottomNavMobileTest`, `PembedaVisualPortalD19Test`, dan lainnya: `rupiah`, `status-chip`, `hero-card`, `tile`, `stepper`, `progress-ring`, `label-portal`, `login-button`, `tautan-portal-lain`, plus teks `Rp&nbsp;`, `Mode Offline`, `canvas-ttd`, `Ajukan izin / cuti`. Sebelum mengubah sebuah view, `grep` tes yang menyentuhnya (`grep -rn "<nama-view>" tests`).
8. **Dilarang di portal nasabah/kolektor** (sesuai `DESIGN.md`): gradien, bayangan tebal, font monospasi, warna kategori di teks/chip/banner, tombol non-pil. Warna aksi, tautan, dan fokus tetap hijau `primary`. Warna identitas portal (hijau/biru) hanya untuk chip, logo, panel, dan indikator.
9. Font: Instrument Sans lewat `@fonts` di `partials/head.blade.php`. **Dilarang** menambah `<link>` CDN atau Google Fonts. Aplikasi berjalan sebagai PWA/Capacitor dan harus bisa offline (lihat `HalamanOfflineP82Test`).
10. Teks UI Bahasa Indonesia, kalimat pendek, tidak menyalahkan pengguna. Angka uang selalu lewat komponen `rupiah` (`Rp&nbsp;`), format `Rp 1.250.000`. Kalimat penyemangat hanya boleh berisi angka yang dihitung dari data, bukan angka tetap.
11. Setelah tiap task, tambahkan satu baris ke `docs/plan-log.md` (ID, status, file utama, hasil tes). Asumsi ditulis eksplisit `ASUMSI D#`. Cek nomor `D` terakhir sebelum menambah (saat ini **D32**, keputusan baru mulai **D33**).
12. Jika nama file, kolom, atau komponen di dokumen ini tidak cocok dengan kode, **verifikasi dengan `grep`/baca file dulu**, catat di plan-log, lalu sesuaikan. Jangan menebak.
13. Admin **tidak ikut** redesain. Jangan menyentuh `layouts/admin.blade.php`, `components/admin/*`, atau `admin-bottom-nav`.

---

## 1. KEPUTUSAN OWNER (agent memakai DEFAULT bila belum dijawab)

Beberapa isi prototipe **tidak diverifikasi terhadap domain aplikasi** dan sebagian bertentangan dengan keputusan lama. Itu sebabnya ada daftar ini.

| ID | Pertanyaan | DEFAULT yang dipakai agent |
|----|-----------|----------------------------|
| D33 | Prototipe menampilkan "kode serah terima 4 digit" di pengajuan aktif. Domain Anda memakai verifikasi **PIN nasabah oleh kolektor** (D2) | **Jangan dibuat.** Ganti dengan catatan "Kolektor akan meminta PIN Anda saat serah terima" dan pakai stepper yang ada (Diajukan → Disetujui → Selesai) |
| D34 | Chip "Bonus Rp 50.000" dan "Biaya admin" di Pilih paket | Tampilkan **hanya bila** field/atribut produk itu ada dan bernilai > 0. Jika tidak ada, jangan tampilkan. Komisi tetap 0% (D6) |
| D35 | Tombol "Kirim struk ke WhatsApp" di layar sukses setoran kolektor | Pakai mekanisme WA yang sudah ada bila ada (lihat D3). Jika tidak ada, pakai tautan `wa.me` berisi teks struk, atau Web Share API. **Jangan** membuat integrasi gateway baru |
| D36 | Layar sukses setelah kolektor mencatat setoran | Ya. Pakai komponen layar sukses yang sudah didefinisikan `DESIGN.md`, aksen hijau |
| D37 | Tombol "A+" (teks besar) | Ya, untuk seluruh portal nasabah dan kolektor. Kunci `localStorage` `td-text-size`, try/catch, mengikuti pola kunci `td-theme` |
| D38 | Login dua langkah (HP lalu PIN) dengan tombol angka layar | Ya, dengan **fallback tanpa JavaScript** (dua input biasa) dan input PIN asli tetap ada di DOM |
| D39 | Animasi dekoratif (koin jatuh, toples) | **Hanya** di login dan layar sukses. Tidak di layar kerja harian. Wajib patuh `prefers-reduced-motion` |
| D40 | Warna tombol di portal kolektor: prototipe memakai biru | **Ikuti `DESIGN.md`**: tombol tetap hijau. Biru hanya untuk chip, logo, panel, indikator langkah |
| D41 | Sumber nomor WhatsApp admin untuk tombol bantuan login | Konfigurasi (`config/services.php` atau `AdminSetting`, pilih yang cocok pola project). Kosong = tombol disembunyikan |
| D42 | Mode gelap di halaman login | Login **selalu terang** (sesuai `DESIGN.md`). Layar lain tetap mengikuti `.dark` yang sudah ada |

---

## FASE 0: Persiapan dan baseline

**U0.1 Baseline.** `composer install`, `npm ci`, siapkan DB tes, jalankan `php artisan test --compact`, `vendor/bin/pint --test`, `vendor/bin/phpstan analyse --memory-limit=1G`, `npm run build`. Catat jumlah tes lulus dan error PHPStan di plan-log sebagai "Baseline Redesain". Jika ada tes merah di baseline, jangan diperbaiki diam-diam: catat dan laporkan.

**U0.2 Inventaris komponen dan tes.** Hasilkan `docs/ui-inventory.md` berisi: (a) daftar komponen di `resources/views/components/ui/*` dan `components/kolektor/*` beserta propsnya; (b) daftar kelas/token Tailwind kustom di `resources/css/app.css` (346 baris); (c) tabel "view → tes yang menyentuhnya → atribut `data-test` yang dijaga"; (d) daftar view nasabah dan kolektor yang **belum** memakai token `DESIGN.md`. Task ini tidak mengubah kode produksi.

**U0.3 Pemetaan prototipe ke kode.** Di `docs/ui-inventory.md` tambahkan tabel: layar prototipe → view/komponen Livewire yang sudah ada → data yang tersedia → data yang **tidak** tersedia (diberi tanda `[BUTUH KEPUTUSAN]`). Wajib memeriksa: saldo per produk, progres paket, bonus/biaya admin, jadwal kunjungan dan jumlah kunjungan selesai/total, kas di tangan kolektor, tunggakan per nasabah, ketersediaan WA/struk.

Selesai bila: tiga artefak tercatat, tidak ada perubahan kode produksi.

---

## FASE 1: Fondasi desain (token, komponen, aksesibilitas global)

**U1.1 Pembaruan `DESIGN.md` (dengan keputusan D38, D39, D40, D42).** Tambah/ubah bagian: Panel login (dua kolom ≥ 900px; header berlengkung di ponsel), Komponen "keypad PIN" (tombol 62px, `rounded-2xl`, ikon hapus), "titik PIN" (20px), "kartu toast setoran", "ilustrasi toples", aturan motion (durasi, easing, reduced motion), dan "teks besar (A+)". Tidak menambah warna baru kecuali tak terhindarkan, dan setiap warna baru wajib dilengkapi rasio kontras.

**U1.2 Komponen Blade pembungkus** di `resources/views/components/ui/` (hanya yang belum ada, nama mengikuti pola inventaris U0.2): `pin-pad`, `pin-dots`, `stepper-langkah` (2 titik indikator login), `chip-status` (jika belum ada), `ubin-aksi`, `kartu-saldo-hero`, `baris-riwayat`, `layar-sukses`. Tiap komponen: props terdokumentasi, atribut `data-test` yang konsisten, tanpa logika bisnis.

**U1.3 Motion dan animasi** di `resources/css/app.css`, kelas ber-namespace `td-anim-*`: `fall` (koin), `level` (isi toples), `twinkle`, `rise` (masuk), `pop` (titik PIN), `shake`, `bounce-dots`, `draw` (centang). Blok `@media (prefers-reduced-motion: reduce)` mematikan semuanya (durasi 0.01ms, tanpa loop). Hanya `transform` dan `opacity` yang dianimasikan. Tidak ada library animasi.

**U1.4 Teks besar (D37).** Alpine store kecil di `resources/js/app.js`: kelas `big` pada `<html>` menaikkan ukuran dasar ke 118%, tersimpan di `localStorage` (try/catch). Pastikan layout mobile memakai satuan `rem` di tempat yang relevan. Tombol "A+" ditempatkan di pengaturan nasabah/kolektor dan di login.

**U1.5 Aksesibilitas dasar.** Audit target sentuh ≥ 44px, fokus 2px `primary` offset 2px, `aria-live="polite"` untuk pesan status, `role="alert"` untuk galat, `aria-label` pada tombol ikon, urutan tab logis, label `<label>` asli di semua input. Perbaiki yang kurang di komponen U1.2 (bukan di seluruh app sekaligus).

Selesai bila: komponen terdokumentasi di `docs/ui-inventory.md`, `npm run build` sukses, semua tes hijau, tidak ada view yang berubah tampilannya selain yang disebut.

---

## FASE 2: Login (nasabah dan kolektor)

Admin tetap memakai `layouts/auth/simple.blade.php`. Acuan perilaku lengkap ada di `PROMPT-AGY-login.md` (jika berkas itu tersedia); ringkasannya:

**U2.1 Layout `layouts/auth/portal.blade.php`** untuk portal nasabah/kolektor. Panel brand + form. Dipilih di `pages/auth/login.blade.php` berdasarkan `$portal`. Judul tab tetap `Login {Label} - Tabungan Digital`.

**U2.2 Komponen** `components/auth/brand-panel`, `ilustrasi-toples` (SVG inline + animasi `td-anim-*`), `kartu-toast`.

**U2.3 Form dua langkah (Alpine).** Langkah 1: nomor HP (+62, format otomatis, validasi panjang), "Ingat saya di HP ini", Lanjut. Langkah 2: ringkasan nomor disamarkan + "Ganti", 6 titik PIN, keypad, "Lihat" (1,2 detik), hapus, "Lupa PIN?". Aturan keras:
- Form tetap `POST route('login.store')` dengan `no_hp`, `password`, `remember`, `portal`, `@csrf`. Nilai `no_hp` harus diterima `NomorHp::normalize()`.
- Satu `<input type="password" name="password" inputmode="numeric" maxlength="6" autocomplete="current-password">` tetap di DOM; keypad mengisinya. Digit ke-6 memicu `requestSubmit()`.
- `$errors->any()` membuka langkah 2 dengan titik bergetar dan **pesan dari server**; isi ulang `old('no_hp')`.
- `session('locked')` menonaktifkan keypad dan tombol Masuk, tampilkan alasan di bawah tombol (aturan `DESIGN.md`). `session('idle_expired')` menampilkan catatan amber.
- Tanpa JS: dua input terlihat dan form berfungsi.
- Pemilih "Saya nasabah / Saya kolektor" berupa **tautan** ke `route(PortalLogin::routeName(...))`; portal aktif berupa `<span>` (tes `PembedaVisualPortalD19Test` memeriksa tidak ada tautan ke diri sendiri). Tautan admin kecil di bawah.
- Atribut `data-test`: `label-portal`, `login-button`, `tautan-portal-lain`.

**U2.4 Layar berhasil.** Jembatan visual setelah submit: lingkaran dan centang tergambar, sapaan "Selamat datang" (nama depan hanya bila tersedia dari server). **Redirect tetap dilakukan Fortify**, bukan redirect palsu dari klien.

**U2.5 Bantuan.** "Lupa PIN?" membuka penjelasan singkat (PIN dipulihkan admin, selaras D3/P4.1) dan tombol WhatsApp admin (D41; disembunyikan bila kosong, tidak boleh `href="#"`).

**U2.6 Tes.** `tests/Feature/Auth/LoginRedesignTest.php`: field dan hidden `portal` benar; portal aktif tidak menautkan diri; `locked` dan `idle_expired` tampil; `data-test` ada; portal admin tidak memuat komponen baru; `old('no_hp')` terisi saat gagal; tombol WA tersembunyi saat konfigurasi kosong.

Selesai bila: `AuthenticationTest`, `LoginLockoutTest`, `PembedaVisualPortalD19Test`, `NormalisasiNomorHpTest` tetap hijau tanpa dimodifikasi; uji manual di 360, 768, 1280px dan dengan `prefers-reduced-motion`.

---

## FASE 3: Portal nasabah

Untuk setiap task: **baca view dan kelas Livewire-nya dulu**, ubah hanya markup/kelas, jangan menyentuh method, properti, atau `wire:` kecuali terpaksa (dan bila terpaksa, perhatikan larangan P1.1: properti publik dan method publik tidak boleh bernama sama).

| Task | View (`resources/views/livewire/nasabah/`) | Perubahan visual | Data yang harus ada (verifikasi, U0.3) |
|---|---|---|---|
| **U3.1 Beranda** | view `dashboard` untuk peran nasabah (cari lewat `grep`) | Header sapaan + lonceng (jumlah belum dibaca), `kartu-saldo-hero` menimpa hero, 4 ubin aksi (Tarik, Riwayat, Paket, Bantuan), kartu progres paket aktif (cincin), 2–3 transaksi terbaru, tautan "Lihat semua" | saldo, paket aktif, notifikasi belum dibaca, transaksi terbaru |
| **U3.2 Saldo** | `saldo` | Total di hero, rincian per produk dengan bilah progres teal | saldo per kepesertaan |
| **U3.3 Pilih paket** | `pilih-paket` | Kartu per paket: ubin kategori, setoran harian × durasi, estimasi total; chip bonus/biaya hanya sesuai D34 | atribut produk |
| **U3.4 Progres paket** | `progres-paket` | `progress-ring` besar, chip status, stepper perjalanan paket, estimasi cair | tanggal mulai, hari berjalan, target |
| **U3.5 Ajukan penarikan** | `ajukan-penarikan` | `input-amount` 68px, `chip-amount` (termasuk "Tarik habis"), pilihan metode, catatan amber bila menunggu persetujuan, tombol utama di bawah dengan alasan bila nonaktif ("Saldo belum cukup", "Belum waktunya cair") | aturan penarikan (D7, gerbang tunggakan, gerbang pencairan) |
| **U3.6 Layar sukses penarikan** | bagian dari `ajukan-penarikan` | Komponen `layar-sukses` (cincin lembut, konfeti datar), tombol "Lihat status" dan "Selesai" | hasil pengajuan |
| **U3.7 Pengajuan aktif** | `pengajuan-aktif` | `stepper` (Diajukan → Disetujui → Selesai; ditolak = titik merah tanpa centang), kartu nominal, catatan serah terima sesuai **D33**, tombol "Batalkan pengajuan" dengan `wire:confirm` | status pengajuan |
| **U3.8 Riwayat tabungan** | `riwayat-tabungan` | Chip filter, kelompok per tanggal, `baris-riwayat` dengan ubin kategori, tautan rekap mutasi (rute `rekap.nasabah` yang ada, jangan membuat rute baru), empty state | transaksi, filter |
| **U3.9 Riwayat penarikan** | `riwayat-penarikan` | Kartu per penarikan dengan `status-chip` sesuai tabel pemetaan; biaya admin memakai minus `ink`, bukan merah (diuji `GayaUiLayarNasabahTest`) | status penarikan |
| **U3.10 Notifikasi** | `notifikasi` | Penanda belum dibaca, ubin kategori per jenis (lihat `JenisNotifikasiTest`), tandai dibaca, empty state | jenis notifikasi |
| **U3.11 Komplain** | `komplain` | Chip jenis, textarea, lampiran foto (komponen yang ada, jangan menulis ulang `foto.js`), daftar komplain dengan stepper Dikirim → Diproses → Selesai | jenis, status |
| **U3.12 Pengaturan/Profil** | `pengaturan` | Kartu profil + chip verifikasi, baris pengaturan (data diri, PIN, notifikasi WhatsApp, mode gelap, A+ dari U1.4, kolektor saya), tombol Keluar (danger, `wire:confirm`) | profil, preferensi |
| **U3.13 Bottom nav nasabah** | `layouts/app/bottom-nav.blade.php` | Empat tab (Beranda, Paket, Riwayat, Profil) selalu berlabel; item aktif bergaya hijau | rute aktif |

Selesai bila (semua task Fase 3): tidak ada gradien/mono; semua uang lewat `rupiah`; semua status lewat `status-chip` sesuai tabel pemetaan; tes `GayaUiLayarNasabahTest`, `BottomNavMobileTest`, `ComplaintTest`, `AturanPenarikanTest`, `GateTunggakanPenarikanTest`, `GerbangPencairanD17Test`, `ChipNominalCepatTest` tetap hijau.

---

## FASE 4: Portal kolektor

Aturan sama dengan Fase 3. Tambahan: layar kolektor dipakai di lapangan (satu tangan, sinar terang, sinyal buruk), jadi **target sentuh besar, kontras tinggi, dan jalur offline tidak boleh rusak** (`Mode Offline`, `canvas-ttd`).

| Task | View (`resources/views/livewire/kolektor/`) | Perubahan visual | Catatan |
|---|---|---|---|
| **U4.1 Beranda** | view `dashboard` peran kolektor | Hero "Kas di tangan" + progres kunjungan hari ini (bilah teal) + tombol "Setor ke kantor"; 4 ubin (Jadwal, Nasabah, Setoran, Izin); kartu "Kunjungan berikutnya" | Verifikasi data kas (`KasKolektorTest`) dan jumlah kunjungan. Jangan mengubah perhitungan kas (D4) |
| **U4.2 Jadwal kunjungan** | `jadwal-kunjungan` | Segmen Hari ini/Besok/Minggu ini, daftar menurut rute, baris berikutnya disorot, `status-chip` untuk Selesai/Berikutnya/Belum/Perlu dikejar | Diuji `GayaUiLayarKolektorTest` (chip status kunjungan) |
| **U4.3 Input setoran** | `input-setoran` | Kartu nasabah + paket, `input-amount`, `chip-amount`, ringkasan tunggakan dan saldo setelah setor, catatan, tombol "Catat setoran" | Tidak mengubah idempotensi (`DepositIdempotencyTest`) dan isolasi per kepesertaan |
| **U4.4 Layar sukses setoran** | bagian `input-setoran` | `layar-sukses` (D36), ringkasan nasabah/no. struk/waktu, tombol struk sesuai **D35**, "Selesai, kunjungan berikutnya" | Nomor struk dari data nyata |
| **U4.5 Daftar dan nasabah binaan** | `daftar-nasabah`, `nasabah-binaan` | Baris daftar dengan avatar, chip status, label "Mode Offline" | Teks `Mode Offline` wajib tetap ada |
| **U4.6 Absen dan riwayat** | `absen`, `riwayat-absensi` | Tombol Absen datar berlabel (bottom nav), kartu status, canvas tanda tangan tetap | `canvas-ttd`, `AksesibilitasKameraP83Test` |
| **U4.7 Setor kantor, penarikan offline, verifikasi penarikan** | `setor-kantor`, `penarikan-offline`, `verifikasi-penarikan` | Hero kas, stepper, `status-chip` dari tabel pemetaan, verifikasi PIN nasabah tetap | Jangan mengubah alur uang/PIN (D2, P3.1) |
| **U4.8 Izin dan pengaturan** | `ajukan-izin`, `pengaturan` | Chip status persetujuan, profil datar tanpa gradien, tautan "Ajukan izin / cuti" | `chip-peran` tidak boleh muncul di pengaturan kolektor |
| **U4.9 Bottom nav kolektor** | `layouts/app/bottom-nav.blade.php` | Lima item dengan tombol Absen di tengah (label "Absen"/"Sudah absen") | `BottomNavMobileTest` |

Selesai bila: `GayaUiLayarKolektorTest`, `DashboardKolektorTest`, `DaftarNasabahTest`, `AbsenTest`, `KasKolektorTest`, `HandoverKolektorTest`, `IzinKolektorTest`, `DepositTest` hijau.

---

## FASE 5: Motion, performa, dan konsistensi

**U5.1 Audit motion.** Pastikan animasi dekoratif hanya di login dan layar sukses (D39). Layar kerja hanya boleh transisi halus (≤ 200ms) pada tekan tombol dan masuk elemen.

**U5.2 Performa.** Anggaran: CSS tambahan ≤ 12 KB gzip, JS tambahan ≤ 6 KB gzip, SVG ilustrasi inline ≤ 4 KB. Tidak ada gambar raster baru. Ukur `npm run build` sebelum dan sesudah, catat di plan-log.

**U5.3 Konsistensi.** Cari sisa gaya lama: `grep -rn "gradient\|font-mono\|text-rose\|rounded-md" resources/views/livewire/{nasabah,kolektor} resources/views/layouts/mobile.blade.php` dan perbaiki yang melanggar `DESIGN.md`.

**U5.4 Mode gelap.** Verifikasi setiap layar baru di `.dark` (kecuali login, D42): kontras chip, ubin, hero, dan kartu sesuai pasangan gelap di `DESIGN.md`.

---

## FASE 6: QA dan rilis

**U6.1 Tes otomatis.** Tambahkan tes struktur untuk tiap layar yang diubah (pola `GayaUi*`): atribut `data-test`, `Rp&nbsp;`, ketiadaan `gradient`/`font-mono`, keberadaan `status-chip`. Seluruh suite hijau dan `pint --test`, PHPStan, `npm run build` bersih.

**U6.2 Uji manual (matriks).**

| Dimensi | Cakupan |
|---|---|
| Lebar layar | 320, 360, 390, 768, 1280 px |
| Peran | nasabah, kolektor (admin hanya memastikan tidak berubah) |
| Pengaturan | A+ aktif, mode gelap, `prefers-reduced-motion`, pembaca layar (TalkBack/VoiceOver) pada login dan satu layar kerja |
| Jaringan | normal, lambat, offline (halaman offline, mode offline kolektor) |
| Perangkat | Android entry-level, iPhone kecil, desktop Chrome dan Safari |
| Alur kritis | login (benar, PIN salah, terkunci, sesi berakhir), pilih paket, setor, tarik, batalkan, komplain, absen, catat setoran, setor kantor |

**U6.3 Aksesibilitas.** Jalankan Lighthouse (Accessibility ≥ 95) pada login, beranda nasabah, beranda kolektor, input setoran. Perbaiki temuan, catat sisanya.

**U6.4 PWA/Capacitor.** Verifikasi manifest/ikon tidak berubah, halaman offline masih tampil (`HalamanOfflineP82Test`), dan build Capacitor (`docs/apk.md`) tetap berjalan.

**U6.5 Rilis dan rollback.** Merge ke branch utama lewat PR kecil per fase. Karena perubahan hanya tampilan, rollback = `git revert` PR fase terkait. Tidak ada migrasi untuk di-rollback. Update `docs/SOP_lapangan.md` bagian login dan setoran bila langkah pengguna berubah (login dua langkah, keypad, A+).

---

## Urutan dan estimasi

| Fase | Isi | Estimasi (hari-orang) | Bergantung pada |
|---|---|---|---|
| 0 | Baseline, inventaris, pemetaan | 1 | — |
| 1 | Fondasi (token, komponen, motion, A+) | 2–3 | 0 |
| 2 | Login | 2–3 | 1 |
| 3 | Nasabah (13 task) | 5–7 | 1 (dan 2 untuk konsistensi) |
| 4 | Kolektor (9 task) | 4–6 | 1 |
| 5 | Motion, performa, konsistensi | 1–2 | 3, 4 |
| 6 | QA dan rilis | 2–3 | semua |
| **Total** | | **17–25** | |

Fase 3 dan 4 boleh berjalan paralel setelah Fase 1 selesai.

## Daftar risiko

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Tes `GayaUi*` gagal karena `data-test` atau teks berubah | Suite merah | Aturan 7; `grep` tes sebelum mengubah view; jangan mengubah tes demi lolos kecuali perubahan teks disengaja dan dicatat |
| Prototipe memuat fitur yang tidak ada di domain (kode serah terima, bonus, struk) | Fitur palsu di UI | Bagian 1 (D33–D35); verifikasi U0.3; jangan mengarang data |
| Konflik nama properti/method Livewire (P1.1) | Tombol mati | Jangan menyentuh sisi PHP; bila terpaksa, ikuti aturan P1.1 |
| Animasi memberatkan HP entry-level atau mengganggu pengguna sensitif gerak | UX buruk | D39, anggaran U5.2, hanya `transform`/`opacity`, reduced motion |
| Login dua langkah merusak pengelola kata sandi atau alur tanpa JS | Gagal login | D38: input asli tetap di DOM, fallback tanpa JS, tes U2.6 |
| Penyimpangan dari `DESIGN.md` (biru pada tombol, mode gelap di login) | Inkonsistensi | D40, D42; prototipe bukan sumber kebenaran |
| Jalur offline kolektor rusak oleh perubahan markup | Kerja lapangan terhenti | Teks `Mode Offline`, `canvas-ttd`, uji offline di U6.2 |

## Definition of Done (seluruh program)

- Semua layar nasabah dan kolektor di bagian Fase 3 dan 4 memakai token dan komponen `DESIGN.md`; tidak ada gradien, mono, atau warna kategori di teks/chip.
- Seluruh suite Pest hijau, `pint --test` bersih, PHPStan tidak menambah error, `npm run build` sukses, anggaran U5.2 terpenuhi.
- Lighthouse Accessibility ≥ 95 pada empat halaman kunci; target sentuh ≥ 44px; kontras AA.
- Perilaku autentikasi dan uang **identik** dengan sebelum redesain (tidak ada perubahan migrasi, model, rute, middleware).
- `DESIGN.md`, `docs/ui-inventory.md`, `docs/plan-log.md`, dan `docs/SOP_lapangan.md` diperbarui.
- Laporan akhir berisi: daftar file diubah per fase, penyimpangan dari prototipe beserta alasannya, hasil tes/lint/analisis, keputusan owner yang masih terbuka (D33–D42), dan langkah uji manual.