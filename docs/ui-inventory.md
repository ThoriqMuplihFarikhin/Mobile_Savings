# UI Inventory & Pemetaan Prototipe (U0.2 & U0.3)

> Tanggal: 2026-10-20  
> Branch: `feat/ui-redesign-2026-10`  
> Baseline: 977 tests passed (0 failures)

---

## 1. Daftar Komponen UI

### A. Komponen di `resources/views/components/ui/*`
| Komponen | Props | Keterangan & Penggunaan |
|---|---|---|
| `<x-ui.hero-card>` | `@props([])` | Kartu hero hijau tua `bg-primary-deep`, radius 24px (`rounded-3xl`), padding 20px, teks `on-hero`. Memuat ilustrasi `dekor-hero` di pojok kanan atas (`aria-hidden`). Membawa `data-test="hero-card"`. |
| `<x-ui.ilustrasi>` | `nama` ('tunas'|'kado'|'dekor-hero'), `ukuran` (default: 96) | Ilustrasi SVG datar tanpa gradien. `aria-hidden="true"`. |
| `<x-ui.progress-ring>` | `persen` (0), `ukuran` (88), `lebar` (10), `label` (null), `centang` (false) | Cincin SVG: track `primary-soft`, stroke `primary`. `role="img"`, `data-test="progress-ring"`. |
| `<x-ui.rupiah>` | `nilai` (0), `tanda` ('+'|'-'|'−'|null), `cetak` (false) | Format mata uang `Rp&nbsp;1.250.000` (layar) atau spasi biasa (cetak). Membawa `data-test="rupiah"`. |
| `<x-ui.status-chip>` | `domain` (string), `status` (string), `teks` (opsional) | Pemetaan status domain ke warna token semantik `DESIGN.md`. Membawa `data-test="status-chip"`. |
| `<x-ui.stepper>` | `tahap` (array), `saatIni` (int), `ditolak` (bool) | Stepper proses horizontal. Titik 24px, label 12px. Membawa `data-test="stepper"`. |
| `<x-ui.tanggal>` | `mode` ('tanggal'|'bulan'|'rentang'), `min`, `max`, `placeholder`, `label`, `wajib`, `nilai` | Input tanggal berbasis flatpickr lokal dengan tema hijau `primary`. |
| `<x-ui.tile>` | `kategori` ('hijau'|'biru'|'teal'|'kuning'|'netral'|'mawar'), `ukuran` (44) | Ubin ikon kategori radius 14px (`rounded-[14px]`). Membawa `data-test="tile"`. |

### B. Komponen di `resources/views/components/auth/*`
| Komponen | Props | Keterangan |
|---|---|---|
| `<x-auth.brand-panel>` | `portal` ('nasabah'|'kolektor') | Kolom brand kiri dengan headline, deskripsi, ilustrasi toples, dan link kembali. |
| `<x-auth.ilustrasi-toples>` | `portal` ('nasabah'|'kolektor') | Celengan toples SVG dengan animasi koin jatuh dan level tabungan. |
| `<x-auth.pin-pad>` | `portal`, `locked` (bool) | Keypad PIN interaktif 62px, titik PIN 20px dengan animasi shake/pop. |

### C. Komponen Pembungkus Baru yang Perlu Dibuat (U1.2)
- `<x-ui.pin-pad>` & `<x-ui.pin-dots>`: Komponen modular keypad & titik PIN yang dapat digunakan di luar login (mis. konfirmasi PIN di lembar sheet/modal penarikan).
- `<x-ui.ubin-aksi>`: 4 ubin aksi cepat beranda (Tarik, Riwayat, Paket, Bantuan).
- `<x-ui.kartu-saldo-hero>`: Wrapper kartu saldo hero untuk nasabah/kolektor.
- `<x-ui.baris-riwayat>`: Baris daftar transaksi/riwayat 64px dengan ubin ikon kategori, nominal tabular-nums, dan waktu.
- `<x-ui.layar-sukses>`: Komponen penutup aksi sukses (centang animasi, konfeti datar, rincian nominal, tombol tindakan).

---

## 2. Kelas & Token Tailwind Kustom (`resources/css/app.css`)

- **Tema Warna Terang (Light)**:
  - `--color-primary`: `#15803d`, `--color-primary-hover`: `#166534`, `--color-primary-soft`: `#dcfce7`, `--color-primary-soft-2`: `#f0fdf4`, `--color-primary-deep`: `#166534`
  - `--color-ink`: `#171717`, `--color-body`: `#404040`, `--color-mute`: `#525252`, `--color-subtle`: `#737373`
  - `--color-canvas`: `#fafafa`, `--color-canvas-warm`: `#f3f7f1`, `--color-surface`: `#ffffff`, `--color-surface-inset`: `#f5f5f5`
  - `--color-hairline`: `#e5e5e5`, `--color-hairline-warm`: `#e1eadd`, `--color-hairline-strong`: `#858585`
  - Semantik: `--color-success*`, `--color-warning*`, `--color-danger*`, `--color-info*`
  - Identitas Portal: `--color-portal-nasabah`: `#15803d`, `--color-portal-kolektor`: `#2563eb`, `--color-portal-admin`: `#171717`
  - Tile: `--color-tile-teal-bg`: `#ccfbf1`, `--color-tile-teal-fg`: `#0f766e`
- **Tema Mode Gelap (.dark)**:
  - `--color-dark-canvas`: `#0a0a0a`, `--color-dark-surface`: `#171717`, `--color-dark-raised`: `#262626`
  - Kontras tinggi AA untuk semua teks gelap dan chip semantik.
- **Utilitas Aksesibilitas**:
  - `html.big`: Skala teks membesar ke 118% (D37, tombol A+).
- **Namespace Animasi (`td-anim-*` / `auth-*`)**:
  - `fall`, `level`, `twinkle`, `rise`, `pop`, `shake`, `bounce-dots`, `draw`.
  - `@media (prefers-reduced-motion: reduce)`: durasi 0.01ms, tanpa loop.

---

## 3. Pemetaan View → Tes Terkait → Atribut `data-test`

| View | Tes yang Menyentuhnya | Atribut & Pola Kritis yang Dijaga |
|---|---|---|
| `resources/views/dashboard.blade.php` | `GayaUiDashboardMobileTest`, `DashboardKolektorTest`, `KasKolektorTest` | `data-test="hero-card"`, `data-test="rupiah"`, `data-test="tile"`, `data-test="progress-ring"`, `Rp&nbsp;`, `data-test="status-chip"` |
| `resources/views/pages/auth/login.blade.php` | `LoginRedesignTest`, `AuthenticationTest`, `LoginLockoutTest`, `PembedaVisualPortalD19Test` | `data-test="label-portal"`, `data-test="login-button"`, `data-test="tautan-portal-lain"`, input `no_hp`, hidden `password` |
| `resources/views/livewire/nasabah/saldo.blade.php` | `GayaUiLayarNasabahTest` | `data-test="hero-card"`, `data-test="rupiah"`, tanpa gradient, tanpa font-mono |
| `resources/views/livewire/nasabah/pilih-paket.blade.php` | `GayaUiLayarNasabahTest`, `KomisiTest` | `data-test="tile"`, `data-test="rupiah"`, `data-test="status-chip"`, komisi 0% (D6) |
| `resources/views/livewire/nasabah/progres-paket.blade.php` | `GayaUiLayarNasabahTest`, `GerbangPencairanD17Test` | `data-test="progress-ring"`, `data-test="stepper"`, `data-test="status-chip"` |
| `resources/views/livewire/nasabah/ajukan-penarikan.blade.php` | `GayaUiLayarNasabahTest`, `AturanPenarikanTest`, `ChipNominalCepatTest` | `data-test="chip-amount"`, `data-test="rupiah"`, tombol hijau `primary` berlabel kerja |
| `resources/views/livewire/nasabah/pengajuan-aktif.blade.php` | `GayaUiLayarNasabahTest` | `data-test="stepper"`, `data-test="rupiah"`, tanpa kode 4 digit serah terima (D33) |
| `resources/views/livewire/nasabah/riwayat-tabungan.blade.php` | `GayaUiLayarNasabahTest` | `data-test="tile"`, `data-test="rupiah"`, `data-test="status-chip"` |
| `resources/views/livewire/nasabah/riwayat-penarikan.blade.php` | `GayaUiLayarNasabahTest` | `data-test="status-chip"`, tanda minus ink `−` (bukan merah) |
| `resources/views/livewire/nasabah/komplain.blade.php` | `ComplaintTest`, `GayaUiLayarNasabahTest` | `data-test="stepper"`, `data-test="status-chip"` |
| `resources/views/livewire/nasabah/pengaturan.blade.php` | `GayaUiEditProfilTest`, `GayaUiLayarNasabahTest` | Tombol A+, toggle mode gelap, tombol keluar danger dengan `wire:confirm` |
| `resources/views/layouts/app/bottom-nav.blade.php` | `BottomNavMobileTest` | `data-test="bottom-nav"`, `data-test="tombol-absen"` (kolektor), teks label permanen |
| `resources/views/livewire/kolektor/jadwal-kunjungan.blade.php` | `GayaUiLayarKolektorTest` | `data-test="status-chip"`, sorotan baris kunjungan berikutnya |
| `resources/views/livewire/kolektor/input-setoran.blade.php` | `GayaUiLayarKolektorTest`, `DepositIdempotencyTest` | `data-test="rupiah"`, `data-test="chip-amount"`, idempotensi submit |
| `resources/views/livewire/kolektor/daftar-nasabah.blade.php` | `DaftarNasabahTest`, `GayaUiLayarKolektorTest` | Teks `Mode Offline`, `data-test="status-chip"` |
| `resources/views/livewire/kolektor/absen.blade.php` | `AbsenTest`, `GayaUiLayarKolektorTest` | `canvas-ttd`, input selfie kamera |
| `resources/views/livewire/kolektor/setor-kantor.blade.php` | `SetorKantorTest`, `GayaUiLayarKolektorTest` | `data-test="rupiah"`, `data-test="stepper"`, `wire:confirm` |
| `resources/views/livewire/kolektor/ajukan-izin.blade.php` | `IzinKolektorTest`, `GayaUiLayarKolektorTest` | Teks `Ajukan izin / cuti`, `data-test="status-chip"` |

---

## 4. Pemetaan Prototipe ke Ketersediaan Data Kode

| Layar Prototipe | View / Komponen Livewire Terkait | Data Tersedia di Kode | Data Tidak Tersedia / `[BUTUH KEPUTUSAN]` | Solusi / Default Rencana |
|---|---|---|---|---|
| **Login (Nasabah & Kolektor)** | `pages/auth/login.blade.php` | No HP, PIN, rate limit, status locked/idle, link WA admin via AdminSetting. | — | Fallback No-JS disediakan, tombol hijau primary (D40), login mode terang (D42). |
| **Beranda Nasabah** | `dashboard.blade.php` (seksi nasabah) | Total saldo, saldo per produk, unread notifikasi, riwayat 5 transaksi, paket aktif. | Streak hari menabung (dihitung dinamis bila ada). | Tampilkan streak jika data tersedia; kalimat penyemangat selalu berbasis data riil. |
| **Saldo Nasabah** | `livewire/nasabah/saldo.blade.php` | Total saldo, saldo per kepesertaan/produk. | — | Bilah progres per produk, tautan ke penarikan. |
| **Pilih Paket** | `livewire/nasabah/pilih-paket.blade.php` | List produk paket aktif, harga per hari, periode, target. | Bonus nominal & biaya admin. | D34: Sembunyikan chip bonus/biaya jika 0 atau tidak ada kolomnya. Komisi tetap 0% (D6). |
| **Progres Paket** | `livewire/nasabah/progres-paket.blade.php` | Target, total terbayar, hari berjalan, tunggakan, tanggal mulai & boleh cair. | — | Ring SVG 132px, stepper status pencairan D17. |
| **Ajukan Penarikan** | `livewire/nasabah/ajukan-penarikan.blade.php` | Saldo produk, minimal penarikan (D7), validasi tunggakan, pilihan metode penyerahan. | Pilihan nominal cepat chip. | Disediakan chip nominal (Rp50rb, Rp100rb, Tarik Habis) sebagai pengisi input amount. |
| **Pengajuan Aktif** | `livewire/nasabah/pengajuan-aktif.blade.php` | Status pengajuan penarikan pending/approved/ditolak. | Kode serah terima 4 digit (prototipe). | D33: Jangan dibuat kode 4 digit; ganti teks instruksi verifikasi PIN nasabah oleh kolektor. |
| **Riwayat Tabungan** | `livewire/nasabah/riwayat-tabungan.blade.php` | Transaksi setoran, filter tanggal, pagination. | Rekap PDF pihak ketiga. | Gunakan route cetak rekap HTML/mutasi yang ada (D8, D30). |
| **Riwayat Penarikan** | `livewire/nasabah/riwayat-penarikan.blade.php` | Penarikan nominal diminta, komisi, waktu approval/cair, status. | — | Format nominal minus U+2212 berwarna ink (bukan merah). |
| **Beranda Kolektor** | `dashboard.blade.php` (seksi kolektor) | Kas di tangan, status absen, daftar kunjungan hari ini, nasabah tunggak parah. | — | Hero kas di tangan fungsional tanpa ilustrasi bertumpuk; tombol setor kantor. |
| **Jadwal Kunjungan** | `livewire/kolektor/jadwal-kunjungan.blade.php` | Jadwal hari ini, status dikunjungi/dilewati/belum, nasabah binaan. | — | Segmen Hari ini/Besok/Minggu ini; chip status kunjungan. |
| **Input Setoran** | `livewire/kolektor/input-setoran.blade.php` | Pilih nasabah binaan, produk aktif, tunggakan berjalan, saldo sebelum/sesudah. | — | Tombol catat setoran hijau primary; layar sukses setoran D36. |
| **Layar Sukses Setoran Kolektor** | Bagian `input-setoran.blade.php` | Transaksi baru tercatat, nomor bukti/struk. | Gateway WhatsApp otomatis baru. | D35: Gunakan tombol share link `wa.me` teks struk atau Web Share API. |
| **Daftar Nasabah** | `livewire/kolektor/daftar-nasabah.blade.php` | List nasabah binaan, mode akses digital/offline. | — | Jaga teks `Mode Offline` untuk nasabah non-aplikasi. |
| **Absen Kolektor** | `livewire/kolektor/absen.blade.php` | Lokasi GPS, kamera selfie, kanvas tanda tangan. | — | Pertahankan `canvas-ttd` dan input capture kamera. |
