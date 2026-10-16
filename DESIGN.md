---
version: alpha
name: Tabungan-Digital-design-system
description: Sistem desain Tabungan Digital (tabungan harian dengan kolektor keliling). Nasabah dan kolektor memakai bahasa visual yang hangat, ramah, dan berpusat pada angka. Kanvas hijau muda, hero hijau tua dengan ilustrasi datar, ubin ikon berwarna per kategori, cincin progres, dan penanda tahap. Admin memakai varian padat, datar, dan berhairline. Tanpa gradien. Semua token lolos kontras WCAG AA.

colors:
  primary: "#15803d"
  primary-hover: "#166534"
  primary-soft: "#dcfce7"
  primary-soft-2: "#f0fdf4"
  on-primary: "#ffffff"
  on-primary-soft: "#14532d"
  ink: "#171717"
  body: "#404040"
  mute: "#525252"
  subtle: "#737373"
  hairline: "#e5e5e5"
  hairline-strong: "#858585"
  canvas: "#fafafa"
  surface: "#ffffff"
  surface-inset: "#f5f5f5"
  success: "#15803d"
  success-soft: "#dcfce7"
  success-deep: "#14532d"
  warning: "#d97706"
  warning-soft: "#fef3c7"
  warning-deep: "#92400e"
  danger: "#b23a2e"
  danger-soft: "#f6e5e2"
  danger-deep: "#8f2b21"
  info: "#1d4ed8"
  info-soft: "#dbeafe"
  portal-nasabah: "#15803d"
  portal-kolektor: "#2563eb"
  portal-admin: "#171717"
  canvas-warm: "#f3f7f1"
  hairline-warm: "#e1eadd"
  primary-deep: "#166534"
  on-hero: "#ecfdf5"
  on-hero-mute: "#bbf7d0"
  decor-gold: "#fbbf24"
  soft-amber: "#fffbeb"
  soft-amber-border: "#fde68a"
  soft-green-border: "#bbf7d0"
  tile-teal-bg: "#ccfbf1"
  tile-teal-fg: "#0f766e"
  dark-canvas: "#0a0a0a"
  dark-surface: "#171717"
  dark-raised: "#262626"
  dark-hairline: "#2e2e2e"
  dark-hairline-strong: "#808080"
  dark-ink: "#fafafa"
  dark-body: "#d4d4d4"
  dark-mute: "#a3a3a3"
  dark-primary: "#22c55e"
  dark-on-primary: "#052e16"
  dark-primary-text: "#4ade80"
  dark-primary-soft: "#0f2e1a"
  dark-success-text: "#86efac"
  dark-warning-text: "#fcd34d"
  dark-warning-soft: "#422006"
  dark-danger-text: "#fca5a5"
  dark-danger-soft: "#450a0a"
  dark-info-text: "#bfdbfe"
  dark-info-soft: "#172554"
  dark-tile-teal-bg: "#042f2e"
  dark-tile-teal-fg: "#5eead4"

typography:
  money-hero:
    fontFamily: Instrument Sans, ui-sans-serif, system-ui, sans-serif
    fontSize: 34px
    fontWeight: 600
    lineHeight: 40px
    letterSpacing: -0.64px
  money-lg:
    fontFamily: Instrument Sans, ui-sans-serif, system-ui, sans-serif
    fontSize: 24px
    fontWeight: 600
    lineHeight: 28px
    letterSpacing: -0.48px
  money-md:
    fontFamily: Instrument Sans, ui-sans-serif, system-ui, sans-serif
    fontSize: 16px
    fontWeight: 600
    lineHeight: 24px
  money-input:
    fontFamily: Instrument Sans, ui-sans-serif, system-ui, sans-serif
    fontSize: 28px
    fontWeight: 600
    lineHeight: 34px
    letterSpacing: -0.56px
  title-lg:
    fontFamily: Instrument Sans, ui-sans-serif, system-ui, sans-serif
    fontSize: 24px
    fontWeight: 600
    lineHeight: 32px
    letterSpacing: -0.48px
  title-md:
    fontFamily: Instrument Sans, ui-sans-serif, system-ui, sans-serif
    fontSize: 20px
    fontWeight: 600
    lineHeight: 28px
    letterSpacing: -0.4px
  title-sm:
    fontFamily: Instrument Sans, ui-sans-serif, system-ui, sans-serif
    fontSize: 16px
    fontWeight: 600
    lineHeight: 24px
  body-lg:
    fontFamily: Instrument Sans, ui-sans-serif, system-ui, sans-serif
    fontSize: 16px
    fontWeight: 400
    lineHeight: 24px
  body-md:
    fontFamily: Instrument Sans, ui-sans-serif, system-ui, sans-serif
    fontSize: 14px
    fontWeight: 400
    lineHeight: 20px
  label:
    fontFamily: Instrument Sans, ui-sans-serif, system-ui, sans-serif
    fontSize: 14px
    fontWeight: 500
    lineHeight: 20px
  caption:
    fontFamily: Instrument Sans, ui-sans-serif, system-ui, sans-serif
    fontSize: 12px
    fontWeight: 400
    lineHeight: 16px
  chip:
    fontFamily: Instrument Sans, ui-sans-serif, system-ui, sans-serif
    fontSize: 12px
    fontWeight: 500
    lineHeight: 16px
  button-lg:
    fontFamily: Instrument Sans, ui-sans-serif, system-ui, sans-serif
    fontSize: 16px
    fontWeight: 500
    lineHeight: 24px
  button-md:
    fontFamily: Instrument Sans, ui-sans-serif, system-ui, sans-serif
    fontSize: 14px
    fontWeight: 500
    lineHeight: 20px
  table-head-admin:
    fontFamily: ui-monospace, SFMono-Regular, Menlo, monospace
    fontSize: 12px
    fontWeight: 500
    lineHeight: 16px
    letterSpacing: 0.6px
  code:
    fontFamily: ui-monospace, SFMono-Regular, Menlo, monospace
    fontSize: 13px
    fontWeight: 400
    lineHeight: 20px

rounded:
  sm: 6px
  md: 8px
  lg: 12px
  xl: 16px
  2xl: 24px
  card: 20px
  tile: 14px
  hero: 24px
  panel: 40px
  pill: 9999px

spacing:
  xxs: 4px
  xs: 8px
  sm: 12px
  md: 16px
  lg: 20px
  xl: 24px
  2xl: 32px
  3xl: 48px

components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    typography: "{typography.button-lg}"
    rounded: "{rounded.pill}"
    height: 48px
    padding: "0px {spacing.xl}"
  button-primary-pressed:
    backgroundColor: "{colors.primary-hover}"
    textColor: "{colors.on-primary}"
    rounded: "{rounded.pill}"
  button-secondary:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    borderColor: "{colors.hairline-strong}"
    typography: "{typography.button-lg}"
    rounded: "{rounded.pill}"
    height: 48px
    padding: "0px {spacing.xl}"
  button-ghost:
    textColor: "{colors.primary}"
    typography: "{typography.button-md}"
    rounded: "{rounded.pill}"
    height: 44px
    padding: "0px {spacing.md}"
  button-danger:
    backgroundColor: "{colors.danger}"
    textColor: "{colors.on-primary}"
    typography: "{typography.button-lg}"
    rounded: "{rounded.pill}"
    height: 48px
    padding: "0px {spacing.xl}"
  button-admin-primary:
    backgroundColor: "{colors.portal-admin}"
    textColor: "{colors.on-primary}"
    typography: "{typography.button-md}"
    rounded: "{rounded.sm}"
    height: 36px
    padding: "0px {spacing.sm}"
  button-admin-secondary:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    borderColor: "{colors.hairline-strong}"
    typography: "{typography.button-md}"
    rounded: "{rounded.sm}"
    height: 36px
    padding: "0px {spacing.sm}"
  card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    borderColor: "{colors.hairline-warm}"
    typography: "{typography.body-lg}"
    rounded: "{rounded.card}"
    padding: "{spacing.md}"
  hero-card:
    backgroundColor: "{colors.primary-deep}"
    textColor: "{colors.on-hero}"
    typography: "{typography.money-hero}"
    rounded: "{rounded.hero}"
    padding: "{spacing.lg}"
  list-row:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    borderColor: "{colors.hairline}"
    typography: "{typography.body-lg}"
    height: 64px
    padding: "{spacing.sm} {spacing.md}"
  chip-neutral:
    backgroundColor: "{colors.surface-inset}"
    textColor: "{colors.body}"
    typography: "{typography.chip}"
    rounded: "{rounded.pill}"
    padding: "{spacing.xxs} {spacing.xs}"
  chip-success:
    backgroundColor: "{colors.success-soft}"
    textColor: "{colors.success-deep}"
    typography: "{typography.chip}"
    rounded: "{rounded.pill}"
    padding: "{spacing.xxs} {spacing.xs}"
  chip-warning:
    backgroundColor: "{colors.warning-soft}"
    textColor: "{colors.warning-deep}"
    typography: "{typography.chip}"
    rounded: "{rounded.pill}"
    padding: "{spacing.xxs} {spacing.xs}"
  chip-danger:
    backgroundColor: "{colors.danger-soft}"
    textColor: "{colors.danger-deep}"
    typography: "{typography.chip}"
    rounded: "{rounded.pill}"
    padding: "{spacing.xxs} {spacing.xs}"
  chip-info:
    backgroundColor: "{colors.info-soft}"
    textColor: "{colors.info}"
    typography: "{typography.chip}"
    rounded: "{rounded.pill}"
    padding: "{spacing.xxs} {spacing.xs}"
  chip-portal-nasabah:
    backgroundColor: "{colors.portal-nasabah}"
    textColor: "{colors.on-primary}"
    typography: "{typography.chip}"
    rounded: "{rounded.pill}"
    padding: "{spacing.xxs} {spacing.xs}"
  chip-portal-kolektor:
    backgroundColor: "{colors.portal-kolektor}"
    textColor: "{colors.on-primary}"
    typography: "{typography.chip}"
    rounded: "{rounded.pill}"
    padding: "{spacing.xxs} {spacing.xs}"
  chip-portal-admin:
    backgroundColor: "{colors.portal-admin}"
    textColor: "{colors.on-primary}"
    typography: "{typography.chip}"
    rounded: "{rounded.pill}"
    padding: "{spacing.xxs} {spacing.xs}"
  input:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    borderColor: "{colors.hairline-strong}"
    typography: "{typography.body-lg}"
    rounded: "{rounded.lg}"
    height: 48px
    padding: "0px {spacing.md}"
  input-amount:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    borderColor: "{colors.hairline-strong}"
    typography: "{typography.money-input}"
    rounded: "{rounded.lg}"
    height: 68px
    padding: "0px {spacing.md}"
  input-admin:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    borderColor: "{colors.hairline-strong}"
    typography: "{typography.body-md}"
    rounded: "{rounded.sm}"
    height: 40px
    padding: "0px {spacing.sm}"
  pin-cell:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    borderColor: "{colors.hairline-strong}"
    typography: "{typography.money-lg}"
    rounded: "{rounded.lg}"
    height: 56px
  bottom-nav:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.mute}"
    borderColor: "{colors.hairline}"
    typography: "{typography.chip}"
    height: 64px
  bottom-nav-item-active:
    textColor: "{colors.primary}"
    typography: "{typography.chip}"
  fab-absen:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    rounded: "{rounded.pill}"
    height: 56px
  sheet:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.2xl}"
    padding: "{spacing.xl} {spacing.md}"
  banner-warning:
    backgroundColor: "{colors.warning-soft}"
    textColor: "{colors.warning-deep}"
    typography: "{typography.body-md}"
    rounded: "{rounded.lg}"
    padding: "{spacing.sm} {spacing.md}"
  banner-danger:
    backgroundColor: "{colors.danger-soft}"
    textColor: "{colors.danger-deep}"
    typography: "{typography.body-md}"
    rounded: "{rounded.lg}"
    padding: "{spacing.sm} {spacing.md}"
  banner-info:
    backgroundColor: "{colors.info-soft}"
    textColor: "{colors.info}"
    typography: "{typography.body-md}"
    rounded: "{rounded.lg}"
    padding: "{spacing.sm} {spacing.md}"
  empty-state:
    backgroundColor: "{colors.surface-inset}"
    textColor: "{colors.mute}"
    typography: "{typography.body-md}"
    rounded: "{rounded.xl}"
    padding: "{spacing.2xl} {spacing.md}"
  admin-sidebar:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.mute}"
    borderColor: "{colors.hairline}"
    typography: "{typography.body-md}"
  admin-sidebar-item-active:
    backgroundColor: "{colors.surface-inset}"
    textColor: "{colors.ink}"
    typography: "{typography.label}"
    rounded: "{rounded.sm}"
  admin-table-head:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.mute}"
    typography: "{typography.table-head-admin}"
    padding: "{spacing.xs} {spacing.sm}"
  admin-table-cell:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    borderColor: "{colors.hairline}"
    typography: "{typography.body-md}"
    height: 44px
    padding: "{spacing.xs} {spacing.sm}"
  kpi-card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    borderColor: "{colors.hairline}"
    typography: "{typography.money-lg}"
    rounded: "{rounded.md}"
    padding: "{spacing.md}"
  tile-green:
    backgroundColor: "{colors.primary-soft}"
    textColor: "{colors.on-primary-soft}"
    rounded: "{rounded.tile}"
    height: 44px
  tile-teal:
    backgroundColor: "{colors.tile-teal-bg}"
    textColor: "{colors.tile-teal-fg}"
    rounded: "{rounded.tile}"
    height: 44px
  tile-blue:
    backgroundColor: "{colors.info-soft}"
    textColor: "{colors.info}"
    rounded: "{rounded.tile}"
    height: 44px
  tile-amber:
    backgroundColor: "{colors.warning-soft}"
    textColor: "{colors.warning-deep}"
    rounded: "{rounded.tile}"
    height: 44px
  tile-neutral:
    backgroundColor: "{colors.surface-inset}"
    textColor: "{colors.body}"
    rounded: "{rounded.tile}"
    height: 44px
  tile-rose:
    backgroundColor: "{colors.danger-soft}"
    textColor: "{colors.danger-deep}"
    rounded: "{rounded.tile}"
    height: 44px
  note-amber:
    backgroundColor: "{colors.soft-amber}"
    textColor: "{colors.body}"
    borderColor: "{colors.soft-amber-border}"
    rounded: "{rounded.card}"
    padding: "{spacing.md}"
  note-green:
    backgroundColor: "{colors.primary-soft-2}"
    textColor: "{colors.on-primary-soft}"
    borderColor: "{colors.soft-green-border}"
    rounded: "{rounded.card}"
    padding: "{spacing.md}"
  progress-ring:
    backgroundColor: "{colors.primary-soft}"
    textColor: "{colors.primary}"
    typography: "{typography.title-sm}"
  stepper-dot-done:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    typography: "{typography.caption}"
    rounded: "{rounded.pill}"
    height: 24px
  stepper-dot-todo:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.mute}"
    borderColor: "{colors.hairline-strong}"
    typography: "{typography.caption}"
    rounded: "{rounded.pill}"
    height: 24px
  stepper-dot-bad:
    backgroundColor: "{colors.danger}"
    textColor: "{colors.on-primary}"
    typography: "{typography.caption}"
    rounded: "{rounded.pill}"
    height: 24px
  chip-amount:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    borderColor: "{colors.hairline-strong}"
    typography: "{typography.body-md}"
    rounded: "{rounded.pill}"
    height: 40px
    padding: "0px {spacing.md}"
  chip-amount-active:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    typography: "{typography.body-md}"
    rounded: "{rounded.pill}"
    height: 40px
    padding: "0px {spacing.md}"
  login-panel:
    backgroundColor: "{colors.primary-deep}"
    textColor: "{colors.on-hero}"
    typography: "{typography.title-lg}"
    rounded: "{rounded.panel}"
    height: 300px
---

# Tabungan Digital — Sistem Desain

> Dokumen ini adalah acuan tampilan untuk seluruh UI aplikasi (nasabah, kolektor, admin, landing, login, struk/rekap cetak). Saat membuat atau mengubah UI, ikuti dokumen ini. Bila ada yang bertentangan dengan view lama, dokumen ini yang benar dan view lamalah yang dirapikan.

## 1. Overview

Tabungan Digital menggantikan buku catatan kolektor keliling dengan aplikasi web/PWA/APK. Uangnya tunai dan nyata, penggunanya awam, dan kepercayaan adalah produknya. Karena itu tampilannya harus:

1. **Jelas sebelum cantik.** Angka saldo, tunggakan, dan status terbaca dalam satu lirikan, juga oleh orang yang kurang familiar dengan aplikasi.
2. **Hangat dan menyemangati.** Nasabah harus merasa ditemani menabung, bukan sedang membuka aplikasi bank yang dingin. Karakter datang dari bentuk, warna kategori, ilustrasi datar, dan kata-kata, bukan dari efek.
3. **Jujur.** Setiap angka punya tanda (+ atau −), waktu, dan sumber. Tidak ada status yang hanya dibedakan oleh warna.
4. **Satu produk, tiga konteks.** Nasabah, kolektor, dan admin memakai token yang sama, dengan kepadatan, tingkat dekorasi, dan warna identitas yang berbeda.

### Karakter visual

- **Nasabah**: paling hangat. Kanvas hijau sangat muda `{colors.canvas-warm}`, kartu putih bulat 20px dengan bayangan halus, **hero hijau tua** `{colors.primary-deep}` berilustrasi datar (tunas dan koin Rp), ubin ikon berwarna per kategori, cincin progres, penanda tahap, sapaan dengan nama depan.
- **Kolektor**: hangat tetapi fungsional. Memakai token yang sama, tetapi layar kerja (setor, jadwal, verifikasi) **tanpa hero dekoratif dan ilustrasi**, karena dipakai cepat, satu tangan, di bawah matahari. Ilustrasi hanya di login dan kondisi kosong.
- **Admin**: tidak berubah. Padat, datar, hairline abu, tombol tinta radius 6px, tanpa ilustrasi dan ubin warna-warni.
- **Prinsip: hangat tanpa berisik.** Satu layar boleh punya satu ilustrasi, dan ilustrasi tidak pernah menutupi angka.

### Dekorasi: yang boleh dan yang dilarang

| Boleh | Dilarang |
|---|---|
| Ilustrasi **datar** (tunas, koin Rp, kado), **satu per layar**, hanya di hero, kondisi kosong, login, layar sukses, dan header paket | Gradien, blur/kaca, glow, bayangan tebal |
| Lingkaran dekoratif datar di hero | Ilustrasi orang atau wajah, emoji |
| Ubin ikon berwarna per kategori (lihat §2) | Warna kategori di teks, chip, atau banner |
| Cincin progres, lencana, penanda tahap, titik konfeti di layar sukses | Animasi yang berulang terus-menerus |
| Sapaan dan kalimat penyemangat (lihat §9) | Dekorasi di layar admin dan layar cetak (struk, rekap) |

### Tiga portal

| | Nasabah | Kolektor | Admin |
|---|---|---|---|
| Perangkat utama | HP (PWA/APK), kolom maks 480px | HP (PWA/APK), kolom maks 480px | Desktop, konten maks 1280px |
| Konteks | Cek saldo, ajukan penarikan | Di lapangan: satu tangan, luar ruangan, input uang | Meja kerja: tabel, approval, rekonsiliasi, laporan |
| Teks dasar | 16px | 16px | 14px |
| Tinggi kontrol | 48px (tombol utama), 44px minimum sentuh | 48–68px (input nominal 68px) | 36–40px |
| Warna aksi | `{colors.primary}` hijau | `{colors.primary}` hijau | `{colors.portal-admin}` tinta |
| Warna identitas portal | `{colors.portal-nasabah}` | `{colors.portal-kolektor}` biru | `{colors.portal-admin}` |
| Tingkat dekorasi | Tinggi (hero, ilustrasi, ubin) | Rendah (hanya login dan kosong) | Tidak ada |
| Bentuk | Pil, kartu 20px, hero 24px | Pil, kartu 20px | Radius 6–8px |
| Navigasi | Bottom nav 4 item | Bottom nav 4 item + tombol Absen | Sidebar + topbar |

Warna identitas portal hanya muncul di **chip peran**, **pita tipis 4px di atas layar** (kolektor dan admin), `theme_color` manifest, dan ikon APK. Warna aksi (tombol, tautan, fokus) tidak mengikuti warna portal. Dengan begitu pembeda portal (keputusan D19) tetap ada tanpa membuat kolektor memiliki palet sendiri.

## 2. Colors

### Aksi dan merek
- **Primary** `{colors.primary}` `#15803d`: tombol utama, tautan, tab/nav aktif, fokus. Putih di atasnya 5,02:1.
- **Primary hover/pressed** `{colors.primary-hover}` `#166534`.
- **Primary soft** `{colors.primary-soft}` `#dcfce7`: latar catatan hijau, chip sukses, area terpilih. Teks di atasnya wajib `{colors.on-primary-soft}` `#14532d` (8,30:1).
- Jangan memakai `#16a34a` (green-600) sebagai latar teks putih. Kontrasnya hanya 3,30:1 dan gagal AA. Pakai `#15803d`.

### Netral
- **Ink** `{colors.ink}` `#171717`: judul, angka, teks utama (17,93:1).
- **Body** `{colors.body}` `#404040`: paragraf (10,37:1).
- **Mute** `{colors.mute}` `#525252`: teks sekunder (7,81:1).
- **Subtle** `{colors.subtle}` `#737373`: placeholder dan metadata. Ini batas terendah yang boleh dipakai untuk teks (4,74:1 di atas putih). Jangan memakai abu lebih terang untuk teks apa pun.
- **Canvas** `{colors.canvas}` `#fafafa`: latar halaman. **Surface** `{colors.surface}` `#ffffff`: kartu. **Inset** `{colors.surface-inset}` `#f5f5f5`: area cekung, chip netral, hover baris.
- **Hairline** `{colors.hairline}` `#e5e5e5`: pembatas dan border kartu. **Hairline strong** `{colors.hairline-strong}` `#858585`: border input dan kontrol interaktif (gelap `{colors.dark-hairline-strong}` `#808080`), dipilih agar pasangan latar terang maupun gelap memegang ≥3:1 — WCAG 1.4.11.

### Semantik (makna, bukan dekorasi)
| Peran | Fill/ikon | Latar lembut | Teks di latar lembut | Dipakai untuk |
|---|---|---|---|---|
| Success | `{colors.success}` | `{colors.success-soft}` | `{colors.success-deep}` | Uang masuk, selesai, cocok, lunas |
| Warning | `{colors.warning}` | `{colors.warning-soft}` | `{colors.warning-deep}` | Menunggu, tunggakan, perlu tindakan |
| Danger | `{colors.danger}` | `{colors.danger-soft}` | `{colors.danger-deep}` | Ditolak, gagal, kurang, bermasalah, terkunci |
| Info | `{colors.info}` | `{colors.info-soft}` | `{colors.info}` | Netral-informatif: disetujui, offline, lebih |

Aturan penting untuk uang:
- **Uang masuk (setoran)** = hijau dengan awalan `+`.
- **Uang keluar (penarikan)** = warna **ink**, awalan `−` (tanda minus sebenarnya, U+2212). Penarikan bukan kesalahan, jadi **jangan merahkan**.
- **Merah hanya untuk hal yang salah atau berisiko**: tunggakan melewati batas, selisih kas kurang, penolakan, gagal kirim, akun terkunci.
- **Tidak boleh ada status yang dibedakan hanya oleh warna.** Selalu sertakan teks, dan bila perlu ikon (centang, jam, tanda seru, silang).

### Pemetaan status → chip
| Domain | Nilai (di database) | Chip |
|---|---|---|
| Pendaftaran nasabah | `pending_verifikasi` / `aktif` / `ditolak` | warning / success / danger |
| Akun | `aktif` / `terkunci` | success / danger |
| Produk | `aktif` / `nonaktif` | success / neutral |
| Mode akses | `digital` / `offline` | tanpa chip / neutral dengan ikon |
| Setoran | `tercatat` / `dikoreksi` / `dibatalkan` | neutral dengan centang / warning / neutral (nominal dicoret) |
| Penarikan | `pending` / `approved` / `selesai` / `ditolak` / `dibatalkan` / `kedaluwarsa` | warning / info / success / danger / neutral / neutral |
| Setor kas ke kantor | `pending` / `cocok` / `lebih` / `kurang` / `dibatalkan` | warning / success / info / danger / neutral |
| Persetujuan | `pending` / `disetujui` / `ditolak` | warning / success / danger |
| Tinjauan penarikan offline | `sesuai` / `bermasalah` / belum ditinjau | success / danger / warning |
| Alert tunggakan paket | `normal` / `peringatan` / `perlu_review` | tanpa chip / warning / danger |
| Komplain | `baru` / `diproses` / `selesai` | info / warning / success |
| Kunjungan | `dikunjungi` / `dilewati` / `tidak_ada` | success / neutral / warning |
| Serah terima paket | `belum` / `sudah_diterima` | warning / success |
| Notifikasi | `antri` / `terkirim` / `gagal` | neutral / success / danger |
| Kas saat handover | `lunas` / `masih_tunggakan` | success / danger |

Satu domain, satu pemetaan. Jangan membuat warna baru untuk status baru. Pilih dari tabel ini.

### Kanvas, kartu, dan hero hangat (nasabah dan kolektor)
- **Kanvas** `{colors.canvas-warm}` `#f3f7f1` menggantikan `{colors.canvas}` di portal nasabah dan kolektor. Admin tetap `{colors.canvas}` `#fafafa`.
- **Kartu**: putih, border `{colors.hairline-warm}` `#e1eadd`, radius 20px, bayangan `0 1px 2px rgba(20,83,45,.06)`.
- **Hero**: latar `{colors.primary-deep}` `#166534`, teks utama `{colors.on-hero}` `#ecfdf5` (6,77:1), teks sekunder `{colors.on-hero-mute}` `#bbf7d0` (5,88:1). Chip di hero: latar putih 16% (`rgba(255,255,255,.16)`) dengan teks `#ecfdf5` (4,64:1). Tombol utama di hero: latar putih, teks `{colors.on-primary-soft}`.
- **Dekor hero** (lingkaran `#15803d` dan `#1b8f47`, koin `{colors.decor-gold}` `#fbbf24` dengan teks "Rp" `#92400e`) hanyalah hiasan. Bukan token status, tidak pernah membawa informasi, `aria-hidden`.
- **Catatan lembut**: `{colors.soft-amber}` + border `{colors.soft-amber-border}` untuk "pengajuan sedang menunggu"; `{colors.primary-soft-2}` + border `{colors.soft-green-border}` untuk rincian biaya dan ajakan.

Pasangan warna baru (semuanya sudah dihitung):

| Pasangan | Kontras |
|---|---|
| `#0f766e` di `#ccfbf1` (ikon dan teks teal) | 4,86:1 |
| `#1d4ed8` di `#dbeafe` | 5,49:1 |
| `#92400e` di `#fef3c7` | 6,37:1 |
| `#14532d` di `#f0fdf4` | 8,70:1 |
| `#525252` di kanvas hangat `#f3f7f1` (teks sekunder) | 7,21:1 |
| `#15803d` di kanvas hangat (tautan) | 4,63:1 |
| `#404040` di `#fffbeb` | 10,00:1 |
| `#737373` (subtle) di putih | 4,74:1 |
| `#737373` (subtle) di kanvas hangat | 4,38:1 |

**Aturan akibatnya**: `{colors.subtle}` `#737373` hanya boleh di atas **putih**. Di atas kanvas hangat, pakai `{colors.mute}` `#525252`.

### Ubin kategori

Ubin ikon (kotak 14px, 40–56px) memberi identitas pada kategori supaya daftar mudah dipindai. Ubin **bukan status**: status tetap lewat chip (tabel di atas).

| Kategori | Ubin (latar / ikon) | Dipakai untuk |
|---|---|---|
| Hijau | `{colors.primary-soft}` / `{colors.on-primary-soft}` | Bebas, setoran masuk, Tarik, notifikasi setoran |
| Biru | `{colors.info-soft}` / `{colors.info}` | Riwayat, penarikan, notifikasi penarikan |
| Teal | `{colors.tile-teal-bg}` / `{colors.tile-teal-fg}` | Paket, komplain, cincin dan bar paket |
| Kuning | `{colors.warning-soft}` / `{colors.warning-deep}` | Bantuan, tunggakan, koreksi, bonus |
| Netral | `{colors.surface-inset}` / `{colors.body}` | Dibatalkan, transaksi keluar biasa, pengaturan |
| Mawar | `{colors.danger-soft}` / `{colors.danger-deep}` | Keluar (logout), tindakan merusak |

Aturan: warna kategori **hanya** di ubin ikon dan di cincin/bar paket (teal). Tidak di teks, chip, atau banner. Ubin selalu berpasangan dengan label teks, jadi kuning pada ubin tidak dibaca sebagai peringatan.

### Mode gelap
Mode gelap memakai kelas `.dark` dan kunci `td-theme` yang sudah ada.
- Permukaan netral murni: `{colors.dark-canvas}` → `{colors.dark-surface}` → `{colors.dark-raised}`. **Tidak ada lagi navy** (`#131C2E`, `#0B1220`, `#0A1626`).
- Tombol utama: latar `{colors.dark-primary}` `#22c55e` dengan teks `{colors.dark-on-primary}` (6,54:1). Tautan/teks hijau: `{colors.dark-primary-text}` `#4ade80` (10,29:1 di `#171717`).
- Chip semantik memakai pasangan `*-soft` gelap dengan teks `dark-*-text` (semuanya ≥ 8,5:1).
- Teks sekunder hanya satu tingkat: `{colors.dark-mute}` `#a3a3a3` (7,11:1).

Ubin teal di mode gelap: latar `{colors.dark-tile-teal-bg}` `#042f2e`, ikon `{colors.dark-tile-teal-fg}` `#5eead4` (9,78:1). Ubin lain memakai pasangan `*-soft` gelap yang sudah ada. Hero tetap `{colors.primary-deep}` di mode gelap, dan kanvas hangat menjadi `{colors.dark-canvas}`.

## 3. Typography

### Font
- **Instrument Sans** (sudah dimuat lewat Bunny Fonts, bobot 400/500/600) untuk **semua** teks antarmuka.
- **Monospace sistem** (`ui-monospace`) hanya untuk: kode, ID transaksi, nomor referensi, dan **header kolom tabel admin**. Tidak pernah untuk saldo atau nominal.
- **Source Serif 4 dihapus.** Saat ini hanya dipakai di `kpi-card` (±16 tempat) dan memuat permintaan eksternal ke Google Fonts, yang tidak ideal untuk PWA/APK dengan koneksi tidak stabil.
- Bobot maksimum **600**. Jangan memakai 700 atau 800.

### Angka dan uang
- Semua angka uang memakai `font-variant-numeric: tabular-nums` (utilitas Tailwind `tabular-nums`) agar kolom rata.
- Format: **`Rp 1.250.000`**. Pemisah ribuan titik, tanpa desimal, spasi tak-terputus (`&nbsp;`) antara `Rp` dan angka di layar mobile dan admin. Satu helper/komponen untuk semuanya, jangan menulis `number_format` manual di view.
- **Pengecualian: view cetak** (struk, rekap, komisi) tetap memakai spasi biasa. Beberapa tes (`StrukSetoranTest`, `RekapMutasiNasabahTest`, `KomisiTest`) memakai `assertSee('Rp 100.000')` dengan spasi biasa dan akan gagal bila diganti `&nbsp;`. Sebelum memigrasi view mana pun, cari dulu `assertSee('Rp ` di `tests/`.
- Setoran: `+Rp 20.000` (hijau). Penarikan: `−Rp 100.000` (ink). Koreksi: nominal lama dicoret, nominal baru di sebelahnya.
- Angka besar tidak boleh terpotong. Bila `Rp 1.250.000.000` tidak muat, turunkan satu tingkat (`money-hero` → `money-lg`), jangan memotong dengan elipsis.

### Hierarki
| Token | Ukuran/Bobot | Pemakaian |
|---|---|---|
| `money-hero` | 34/600 | Total saldo di hero |
| `money-input` | 28/600 | Nominal di input penarikan (berwarna `primary`) |
| `money-lg` | 24/600 | Nominal di input, KPI admin, PIN |
| `money-md` | 16/600 | Nominal di baris daftar |
| `title-lg` | 24/600 | Judul halaman mobile |
| `title-md` | 20/600 | Judul bagian, judul sheet |
| `title-sm` | 16/600 | Judul kartu |
| `body-lg` | 16/400 | Teks dasar nasabah dan kolektor |
| `body-md` | 14/400 | Teks dasar admin, teks sekunder mobile |
| `label` | 14/500 | Label form, tombol kecil |
| `caption` | 12/400 | Metadata, waktu |
| `chip` | 12/500 | Chip status, label bottom nav |

**Lantai ukuran: 12px.** Tidak ada teks di bawah 12px. Saat ini ada 9,5–11px di bottom nav dan chip, dan itu harus dinaikkan.

Judul memakai huruf kapital biasa (sentence case) dengan sedikit tracking negatif di ukuran besar. **Tidak ada teks huruf kapital semua** di UI nasabah dan kolektor. Satu pengecualian: header kolom tabel admin (`table-head-admin`).

## 4. Layout

- Basis 4px. Skala: 4, 8, 12, 16, 20, 24, 32, 48.
- **Mobile**: kolom satu, lebar maksimum 480px di tengah layar, gutter 16px, jarak antar kartu 12px, jarak antar bagian 24px. Konten diberi ruang bawah ≥ 96px agar tidak tertutup bottom nav. Hormati `safe-area-inset-bottom`.
- **Admin**: sidebar kiri + topbar, area konten maks 1280px, gutter 24px. Grid KPI 1/2/4/5 kolom sesuai lebar.
- **Target sentuh ≥ 44×44px** untuk semua kontrol di mobile. Tombol utama 48px. Input nominal kolektor 68px.
- Satu layar mobile = **satu tindakan utama**. Tombol utama (hijau penuh) maksimal satu per layar; sisanya sekunder atau ghost.

### Responsif
| Lebar | Perilaku |
|---|---|
| < 480px | Kolom penuh, bottom nav menempel ke tepi |
| ≥ 480px (portal mobile) | Kolom 480px di tengah, latar luar `{colors.canvas}` |
| < 1024px (admin) | Sidebar menjadi drawer, tabel boleh digulir horizontal di dalam kontainernya sendiri |
| ≥ 1024px (admin) | Sidebar tetap |

## 5. Elevation & Depth

Hampir datar. Kedalaman dibangun dari **hairline** dan **bayangan sangat halus**, bukan efek.

| Level | Perlakuan | Dipakai untuk |
|---|---|---|
| 0 | Tanpa border | Hero, area inset, catatan lembut |
| 1 | Border `{colors.hairline-warm}` + `0 1px 2px rgba(20,83,45,.06)` | Kartu nasabah dan kolektor |
| 1 (admin) | Border `{colors.hairline}`, tanpa bayangan | Kartu, KPI, tabel admin |
| 2 | Border + `0 1px 2px rgb(0 0 0 / 0.06)` | Bottom nav, dropdown |
| 3 | `0 8px 24px rgb(0 0 0 / 0.12)` | Sheet, modal, toast |

Tidak ada gradien, blur latar, glow, atau cincin warna-warni. Kedalaman visual di hero datang dari lingkaran datar bertingkat, bukan dari gradien.

## 6. Shapes

| Token | Nilai | Pemakaian |
|---|---|---|
| `sm` | 6px | Admin: tombol, input, item sidebar |
| `md` | 8px | Admin: kartu, KPI, dropdown |
| `lg` | 12px | Mobile: input, banner |
| `tile` | 14px | Ubin ikon, banner dan kotak isi dalam kartu |
| `card` | 20px | Mobile: kartu, kartu catatan, empty state (`rounded-[20px]`) |
| `hero` | 24px | Hero (`rounded-3xl`) |
| `2xl` | 24px | Sudut atas sheet dan modal bawah |
| `panel` | 40px | Sudut bawah panel hijau di login |
| `pill` | 9999px | Mobile: tombol, chip, bottom nav, lingkaran |

Aturan: **satu portal, satu keluarga bentuk.** Mobile = pil + 12–24px. Admin = 6–8px. Jangan mencampur tombol pil dengan tombol 6px di layar yang sama.

## 7. Components

### Tombol
- **Primary** (`button-primary`): hijau penuh, pil, 48px. Satu per layar. Label diawali kata kerja: "Ajukan penarikan", "Catat setoran", "Masuk".
- **Secondary**: putih, border `hairline-strong`, pil.
- **Ghost**: teks hijau tanpa latar, untuk tindakan tersier ("Lihat riwayat").
- **Danger**: merah penuh, hanya untuk tindakan merusak dan tidak bisa dibatalkan (tolak, batalkan, nonaktifkan). Selalu lewat konfirmasi.
- **Admin**: `button-admin-primary` hitam-tinta 36px radius 6px; `button-admin-secondary` putih berborder.
- Tombol tidak dinonaktifkan diam-diam. Bila tidak boleh ditekan, tampilkan alasannya di bawah tombol ("Saldo belum cukup", "Belum waktunya cair").
- Fokus: cincin 2px `{colors.primary}` (admin: `{colors.ink}`) dengan offset 2px. Jangan dihapus.

### Kartu, hero, dan catatan
- `card`: putih, border hairline hangat, radius 20px, padding 16px, bayangan halus.
- `hero-card`: hijau tua `{colors.primary-deep}`, radius 24px, padding 20px. Isi: label kecil `on-hero-mute`, **total saldo** `money-hero` `on-hero`, chip produk (putih 16%), satu kalimat hangat dengan ikon centang, dan dekor datar di pojok kanan atas. **Teks selalu di atas lapisan dekor** dan tidak pernah bertumpuk dengan koin. Di Saldo, hero memuat tombol utama berlatar putih ("Ajukan penarikan") dan satu tautan bergaris bawah.
- `note-amber` / `note-green`: kartu catatan lembut untuk pengajuan menunggu dan rincian biaya.
- Header paket: pita `{colors.tile-teal-bg}` setinggi 110px berisi ilustrasi kado datar di kiri dan nama paket di kanan.

### Ubin ikon dan aksi cepat
Ubin `tile-*` (44px; 56px untuk aksi cepat; 40px di baris daftar). Aksi cepat: empat ubin 56px berlabel 12/500 di bawahnya (Tarik hijau, Riwayat biru, Paket teal, Bantuan kuning). Judul bagian aksi cepat ramah dan sopan ("Mau apa hari ini?").

### Cincin progres, lencana, dan streak
- **Cincin** (`progress-ring`): lingkaran SVG, track `{colors.primary-soft}`, isi `{colors.primary}`, ujung membulat, persentase di tengah (ukuran teks ¼ diameter, bobot 600). Wajib `role="img"` dan `aria-label="42 persen"`. Ukuran: 132px (halaman progres), 88px (kartu di beranda), 56px (lencana barang).
- **Lencana barang**: cincin 56px, tercapai = centang, lainnya persentase; nama barang 13/500 dan jumlahnya 12px di bawahnya.
- **Streak**: kartu dengan ikon api, "12 hari berturut-turut", dan tujuh lingkaran 30px (S S R K J S M) berisi centang hijau. Hari tanpa setoran = lingkaran kosong berborder `hairline-strong`, bukan merah.
- Kalimat penyemangat berbasis angka nyata ("Tinggal Rp 1.040.000 lagi") boleh dipakai hanya bila angkanya dihitung dari data. Jangan menulis angka tetap.

### Penanda tahap (stepper)
Untuk status yang berurutan: titik 24px dihubungkan garis 2px, label 12px di bawahnya.

| Domain | Tahap | Pemetaan status |
|---|---|---|
| Penarikan | Diajukan → Disetujui → Selesai | `pending` / `approved` / `selesai` |
| Penarikan ditolak | Diajukan → Ditolak | `ditolak` (titik akhir `stepper-dot-bad`, tanpa centang) |
| Komplain | Dikirim → Diproses → Selesai | `baru` / `diproses` / `selesai` |

Tahap selesai = `stepper-dot-done` (hijau + centang), belum = `stepper-dot-todo`, ditolak = `stepper-dot-bad` (merah, **tanpa centang**, label "Ditolak" tetap tertulis). Status `dibatalkan` dan `kedaluwarsa` belum punya tampilan tahap: pakai chip netral saja.

### Input nominal dan pilihan cepat
`input-amount` 68px, `money-input` berwarna `primary`, di bawahnya teks bantu 12px ("Min. Rp 10.000 atau tarik habis"). **Pilihan nominal cepat** (`chip-amount`: Rp 50 rb, Rp 100 rb, Rp 200 rb, Tarik habis; aktif = `chip-amount-active`) adalah **usulan baru**, bukan fitur yang ada di kode (lihat §14).

### Layar sukses
Dipakai setelah pengajuan penarikan terkirim. Lingkaran centang 104px `{colors.primary}` dengan cincin lembut `{colors.primary-soft}` 12px, titik dan batang konfeti datar (emas, hijau, teal) di belakangnya, judul "Pengajuan terkirim", ringkasan nominal dalam kartu, satu catatan hijau yang menunjuk ke Notifikasi, lalu dua tombol (utama dan sekunder). Konfeti statis, tidak beranimasi.

### Kondisi kosong
`empty-state` memakai ilustrasi tunas 96px di dalam lingkaran `{colors.primary-soft}`, judul 16/600 yang menyebut ruangnya, dan satu kalimat ajakan. Selalu ada, tidak pernah hanya ikon kecil.

### Panel login
Panel hijau tua `login-panel` setinggi 300px, sudut bawah 40px, ilustrasi tunas dan koin, chip "Portal Nasabah" berlatar putih di atas judul "Tabungan Digital" dan sapaan "Selamat datang kembali". Kartu form menumpuk 24px ke atas panel. Pesan galat tetap generik.

### Baris daftar (riwayat, produk, nasabah)
Tinggi ≥ 64px. Kiri: ikon dalam lingkaran 36px berwarna `primary-soft` + judul + keterangan satu baris. Kanan: nominal (`money-md`, rata kanan, `tabular-nums`) + chip status di bawahnya bila perlu. Pembatas antar baris memakai hairline. Seluruh baris bisa disentuh.

### Chip
Radius pil, 12px/500, teks selalu dari pasangan `*-deep` di atas `*-soft`. Chip **portal** memakai latar penuh `portal-*` dengan teks putih, **hanya** di kanan atas layar, satu per layar.

### Form
- Label di atas input (14/500), bukan placeholder. Placeholder berisi contoh nyata dan memakai `{colors.subtle}`.
- Input 48px, radius 12px (admin: 40px, radius 6px). Error: border `danger`, teks pesan 14px di bawahnya dengan ikon, sebutkan apa yang salah dan apa yang harus dilakukan.
- **Nominal**: `input-amount` 68px (`money-input`, berwarna `primary`), `inputmode="numeric"`, pemisah ribuan otomatis, awalan `Rp` tetap terlihat.
- **PIN 6 digit**: enam `pin-cell` 56px, `inputmode="numeric"`, karakter disamarkan, tempel dinonaktifkan. Pesan kesalahan login tetap generik (anti-enumerasi, keputusan D19). Jangan diubah menjadi spesifik.
- **Tanggal**: selalu `<x-ui.tanggal>` (flatpickr, locale `id`). Palet kalender mengikuti `primary` (`--color-brand-*` yang sekarang dipakai flatpickr diganti token baru).
- **Tanda tangan**: kanvas putih, border putus-putus hairline-strong, tinggi ≥ 160px, tombol ghost "Hapus", di bawahnya teks "Tanda tangan di dalam kotak".
- **Foto/selfie (absensi, bukti penarikan)**: bingkai 3:4, tombol rana 64px di tengah bawah, tampilan hasil dengan tombol "Ulangi" dan "Pakai foto ini".

### Navigasi mobile
- `bottom-nav`: bar putih menempel ke bawah, tinggi 64px, **empat item dengan ikon 24px dan label 12px yang selalu terlihat** (bukan hanya di item aktif). Aktif = warna `primary` + bobot 500. Tidak ada pil hitam melayang.
- Kolektor: tombol **Absen** `fab-absen` 56px di tengah, hijau datar, tanpa cincin gradien, dengan label "Absen" di bawahnya (12px). Setelah absen masuk, ia berubah menjadi centang dengan label "Sudah absen".

### Navigasi admin
- `admin-sidebar`: putih, border kanan hairline, item 36px, aktif `surface-inset` + teks ink + bobot 500. Bagian dikelompokkan dengan judul 12px `mute` (bukan huruf kapital semua).
- `topbar`: 56px, pencarian global di kiri, chip portal admin dan menu profil di kanan.

### Tabel admin
- Header `admin-table-head`: monospace 12px, huruf kapital, tracking 0,6px, warna `mute`, latar `canvas`.
- Sel: 14px, tinggi baris 44px, border bawah hairline, hover `surface-inset`. **Kolom nominal rata kanan dengan `tabular-nums`**, bukan mono. Kolom status memakai chip dari tabel §2.
- Tabel yang memuat lebih dari 6 kolom digulir di dalam kontainer sendiri. Halaman tidak boleh bergulir ke samping.
- Di bawah 640px, tabel yang dipakai admin di HP berubah menjadi daftar kartu.

### KPI
`kpi-card`: putih, border hairline, radius 8px, label 12px `mute` di atas, nilai `money-lg` di bawah, ikon 32px `surface-inset`. Delta (naik/turun) memakai chip kecil dengan panah, bukan hanya warna.

### Banner, toast, sheet, empty state
- **Banner** (`banner-*`): dalam halaman, untuk kondisi yang butuh perhatian ("Tunggakan 2 hari: Rp 20.000"). Selalu ada ikon dan tindakan jelas bila ada.
- **Toast** (`flux:toast`): 3 detik, tanpa tanda seru, tanpa kata "berhasil" ("Setoran dicatat").
- **Sheet** (`sheet`): panel bawah dengan sudut atas 24px untuk konfirmasi, PIN, dan pilihan. Latar belakang diredupkan 40% hitam.
- **Empty state**: ajakan, bukan permintaan maaf. Judul menyebut ruangnya ("Belum ada setoran"), satu kalimat penjelas, satu tombol bila ada tindakan yang masuk akal.

### Offline dan PWA
Halaman `/offline` dan service worker sudah ada. Saat tidak ada koneksi, tampilkan `banner-info` di atas layar ("Anda sedang offline. Data terakhir ditampilkan.") dan nonaktifkan tindakan yang butuh server dengan alasan terlihat. Input kolektor offline (antrean lokal) masih backlog di PRD, jadi belum ada pola visualnya.

### Cetak (struk setoran dan rekap mutasi)
Hitam-putih, tanpa latar berwarna, tanpa merek hijau. Font sistem, header tabel dengan latar abu sangat muda yang aman dicetak, baris **Tanda Tangan Nasabah / Tanggal Cetak / Nama Kolektor** selalu ada di rekap. Nominal tetap rata kanan dengan angka tabular. Cetak harus terbaca di printer hitam-putih dan di kertas kecil.

## 8. Pola kepercayaan

Karena produk ini memegang uang orang, tampilan ikut menjadi alat audit:
- Setiap transaksi menampilkan **tanggal transaksi** dan, bila beda, **waktu dicatat** ("Dicatat 14 Okt, 09.12").
- Setiap setoran menampilkan **siapa yang mencatat** dan menawarkan **struk**.
- Konfirmasi penarikan menampilkan **nominal, komisi, jumlah diterima, lokasi pengambilan** sebelum PIN diminta.
- Tindakan final (tinjauan "bermasalah", pembatalan, penolakan) memakai sheet dengan **akibat yang ditulis jelas** dan tombol `danger`.
- Selisih kas ditampilkan sebagai angka bertanda dan chip ("Kurang −Rp 20.000").

## 9. Suara dan bahasa (UI)

- Bahasa Indonesia, sapaan **"Anda"**, huruf kapital biasa. Bukan "Saldo Saya" tetapi "Saldo saya"; bukan "Ajukan Penarikan" tetapi "Ajukan penarikan".
- Tombol: kata kerja di depan, 1–3 kata. Tanpa titik di akhir label dan judul; teks penjelas diakhiri titik.
- Kesalahan: sebut apa yang terjadi, lalu apa yang bisa dilakukan, dalam satu kalimat. Tanpa awalan "Error:".
- Hindari "Berhasil!", "Silakan", dan tanda seru pada teks sistem. Kecuali pesan login, yang memang harus generik.
- Istilah bisnis konsisten: **setoran**, **penarikan**, **tunggakan**, **paket**, **kolektor**, **nasabah**. Jangan mengganti dengan sinonim.

### Nada hangat

- Sapaan memakai **nama depan** dan waktu ("Selamat pagi", "Halo, Sari"). Nama berasal dari data, bukan tetap.
- Kalimat penyemangat pendek dan tulus ("Semangat!", "Sedikit lagi, Sari!"), berbasis angka nyata. Tidak lebih dari satu per kartu.
- **Tetap sopan dan konsisten: "Anda", bukan "kamu" atau "lo".** Hindari bahasa gaul ("ngapain"). Hangat bukan berarti santai berlebihan, karena sebagian nasabah lebih nyaman dengan bahasa yang rapi.
- Penolakan dan galat tetap lugas dan tidak dihias. Tidak ada kalimat riang di kartu yang berisi masalah (tunggakan, ditolak, terkunci).

## 10. Aksesibilitas

- Kontras: teks ≥ 4,5:1, komponen non-teks ≥ 3:1. Semua pasangan di dokumen ini sudah dihitung.
- Target sentuh ≥ 44px. Jarak antar target ≥ 8px.
- Status tidak pernah hanya berupa warna.
- Hormati `prefers-reduced-motion`: tanpa animasi selain transisi 150ms.
- Ikon fungsional punya `aria-label`; ikon dekoratif `aria-hidden`.
- Terbaca di bawah sinar matahari: jangan memakai teks abu terang di atas putih untuk informasi penting kolektor (nominal, nama, status).
- Teks di atas hero memakai `on-hero` atau `on-hero-mute` (≥ 5,8:1). Jangan memakai abu atau hijau lain di atas `primary-deep`.
- Cincin progres, grafik batang, dan stepper punya padanan teks (persentase, `aria-label`, label tahap). Ilustrasi dan konfeti `aria-hidden="true"`.
- Penanda tahap tidak hanya mengandalkan warna: tahap selesai berisi centang, ditolak berlabel "Ditolak".
- Kuning pada ubin kategori tidak boleh menjadi satu-satunya pembawa makna "perhatian". Tunggakan tetap memakai banner/chip peringatan.

## 11. Implementasi (Tailwind v4 + Flux)

Tempel di `resources/css/app.css`, menggantikan blok `@theme` dan `.dark` yang lama. Snippet ini **belum dijalankan terhadap build Anda**, jadi jalankan `npm run build` dan periksa hasilnya.

```css
@theme {
    --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;

    --color-primary: #15803d;
    --color-primary-hover: #166534;
    --color-primary-soft: #dcfce7;
    --color-on-primary-soft: #14532d;

    --color-ink: #171717;
    --color-body: #404040;
    --color-mute: #525252;
    --color-subtle: #737373;
    --color-hairline: #e5e5e5;
    --color-hairline-strong: #858585;
    --color-canvas: #fafafa;
    --color-surface: #ffffff;
    --color-inset: #f5f5f5;

    --color-success: #15803d;   --color-success-soft: #dcfce7;  --color-success-deep: #14532d;
    --color-warning: #d97706;   --color-warning-soft: #fef3c7;  --color-warning-deep: #92400e;
    --color-danger: #b23a2e;    --color-danger-soft: #f6e5e2;   --color-danger-deep: #8f2b21;
    --color-info: #1d4ed8;      --color-info-soft: #dbeafe;

    --color-portal-nasabah: #15803d;
    --color-portal-kolektor: #2563eb;
    --color-portal-admin: #171717;

    --color-primary-deep: #166534;
    --color-on-hero: #ecfdf5;
    --color-on-hero-mute: #bbf7d0;
    --color-canvas-warm: #f3f7f1;
    --color-hairline-warm: #e1eadd;
    --color-tile-teal-bg: #ccfbf1;
    --color-tile-teal-fg: #0f766e;

    --color-accent: var(--color-primary);
    --color-accent-content: var(--color-primary);
    --color-accent-foreground: #ffffff;
}

@layer theme {
    .dark {
        --color-primary: #22c55e;
        --color-primary-hover: #4ade80;
        --color-primary-soft: #0f2e1a;
        --color-on-primary-soft: #86efac;
        --color-ink: #fafafa;
        --color-body: #d4d4d4;
        --color-mute: #a3a3a3;
        --color-subtle: #a3a3a3;
        --color-hairline: #2e2e2e;
        --color-hairline-strong: #808080;
        --color-canvas: #0a0a0a;
        --color-surface: #171717;
        --color-inset: #262626;
        --color-success-soft: #0f2e1a;  --color-success-deep: #86efac;
        --color-warning-soft: #422006;  --color-warning-deep: #fcd34d;
        --color-danger-soft: #450a0a;   --color-danger-deep: #fca5a5;
        --color-info-soft: #172554;     --color-info: #bfdbfe;
        --color-canvas-warm: #0a0a0a;
        --color-hairline-warm: #2e2e2e;
        --color-tile-teal-bg: #042f2e;
        --color-tile-teal-fg: #5eead4;
        --color-accent-foreground: #052e16;
    }
}

html[data-portal="admin"] {
    --color-accent: var(--color-portal-admin);
    --color-accent-content: var(--color-portal-admin);
}
html.dark[data-portal="admin"] {
    --color-accent: #fafafa;
    --color-accent-foreground: #171717;
}
```

Prasyarat kode:
1. Beri `<html data-portal="{{ role }}">` di `partials/head`/layout agar varian admin aktif.
2. Buat komponen Blade **`<x-ui.rupiah :nilai="..." />`** (belum ada) dan **`<x-ui.status-chip :domain="..." :status="..." />`** (belum ada) yang mengimplementasikan §2 dan §3, supaya pemetaan status dan format uang hanya ada di satu tempat.
3. Satukan tiga nilai hijau nasabah yang sekarang berbeda: `PortalLogin::aksen()` (`#16a34a`), `public/manifest-nasabah.webmanifest` (`#16a34a`), dan `mobile/flavors.json` (`#059669`) → semuanya `#15803d`. Kolektor `#2563eb` dan admin `#171717` sudah konsisten.

## 12. Peta migrasi (dari kode sekarang ke dokumen ini)

Urutan pengerjaan yang disarankan: (1) nasabah: saldo, riwayat, penarikan, paket; (2) kolektor: setoran, daftar nasabah, absen; (3) login dan landing; (4) admin: hanya penyelarasan warna.

| Sekarang | Jadi |
|---|---|
| `indigo-*` (nasabah ±207, admin ±145) | Nasabah/kolektor: token `primary`. Admin: tombol → `portal-admin`, tautan/fokus → `info` |
| `emerald-*` (kolektor ±233, landing, nasabah ±121) | Token `primary` / `primary-soft` |
| `blue-*` di kolektor (±95) | Hanya untuk chip dan pita portal; selain itu `info` atau `primary` |
| `rounded-3xl` / `rounded-2xl` pada kartu | `rounded-[20px]`; `rounded-3xl` hanya untuk hero dan sheet |
| Gradien (kartu saldo, tombol Absen, landing) | Warna datar |
| `font-mono` untuk nominal | `tabular-nums` (mono tinggal untuk ID, kode, header tabel admin) |
| `font-serif` / Source Serif 4 | Hapus (dan hapus tag Google Fonts di `head.blade.php`) |
| `text-[9.5px]`…`text-[11px]` | Minimal 12px |
| Hex Vercel di admin: `#ab570a` → `{colors.warning-deep}`, `#c50000` → `{colors.danger}`, `#ebebeb` → `{colors.hairline}` | Pakai token. `#92400e` (6,37:1) lebih kontras dari `#ab570a` |
| Chip portal `#16a34a` + teks putih (3,30:1) | `#15803d` (5,02:1) |
| `.btn-primary` (`indigo-800`) di `app.css` | Per portal: hijau (mobile) / tinta (admin) |
| Mode gelap navy (`#131C2E`, `#0B1220`, `#0A1626`) | Netral `dark-*` |
| Bottom nav pil hitam, label hanya di item aktif | Bar putih, label selalu terlihat |
| Latar `#fafafa` / `bg-zinc-50` di nasabah dan kolektor | `canvas-warm` `#f3f7f1` |
| Border kartu `border-hairline` abu | `border-hairline-warm` + bayangan halus |
| Kartu saldo hijau muda polos | `hero-card` hijau tua berilustrasi |
| Ikon polos di lingkaran hijau seragam | Ubin kategori `tile-*` (§2) |
| Status progres hanya chip | Cincin progres dan stepper (§7) |
| Kondisi kosong berikon kecil | Ilustrasi tunas + judul + ajakan |

## 13. Do dan Don't

### Do
- Pakai **satu** tombol utama hijau per layar mobile.
- Sapa dengan nama depan dan beri satu kalimat penyemangat yang dihitung dari data nyata.
- Pakai ubin kategori untuk memudahkan memindai daftar, dan chip untuk status.
- Tampilkan nominal dengan tanda, awalan `Rp`, dan angka tabular.
- Sertakan teks (dan ikon) di setiap status, jangan hanya warna.
- Biarkan ruang putih bekerja. Kartu berhairline cukup, tanpa bayangan.
- Pakai token dari dokumen ini. Bila butuh warna baru, tambahkan dulu ke dokumen.
- Uji layar di lebar 360px, mode gelap, dan di bawah cahaya terang.

### Don't
- Jangan memakai gradien, blur, atau cincin warna-warni.
- Jangan menaruh lebih dari satu ilustrasi per layar, atau ilustrasi di layar kerja kolektor, admin, dan cetak.
- Jangan menaruh teks di atas koin atau lingkaran dekor hero.
- Jangan memakai warna kategori (ubin) untuk teks, chip, atau banner.
- Jangan menulis "kamu" atau bahasa gaul, dan jangan memakai kalimat riang di kartu masalah.
- Jangan menaruh `subtle` `#737373` di atas kanvas hangat.
- Jangan merahkan penarikan biasa. Merah berarti masalah.
- Jangan memakai font mono untuk saldo atau nominal.
- Jangan membuat teks di bawah 12px atau abu lebih terang dari `#737373`.
- Jangan memakai bobot 700 atau lebih, atau huruf kapital semua di UI mobile.
- Jangan mencampur tombol pil dan tombol 6px di satu layar.
- Jangan menyalin warna, font, atau logo merek lain. Dokumen ini hanya mengambil prinsip (kejelasan, satu aksen, hampir tanpa bayangan), sedangkan identitasnya milik Tabungan Digital.
- Jangan mengubah pesan kesalahan login menjadi spesifik.

## 14. Peta layar nasabah dan pekerjaan kode

Desain visual ada di kanvas "UI/UX Nasabah — Tabungan Digital" (14 artboard + layar sukses). Angka, nama, dan tanggal di kanvas adalah **data contoh**. Tabel ini memisahkan apa yang sudah didukung kode dari apa yang butuh perubahan perilaku (dan karena itu butuh tes, sesuai `CLAUDE.md`).

| Layar | Rute / view | Status |
|---|---|---|
| Beranda | `dashboard` (cabang nasabah di `dashboard.blade.php`) | Ada. **Sapaan waktu**: baru, dihitung di view. **Titik streak mingguan**: butuh data setoran per hari (controller hanya mengirim jumlah `$streak`). **"Tinggal Rp X lagi"**: bisa dihitung dari target dan `total_aktual_terkumpul` |
| Saldo | `nasabah.saldo.index` | Ada |
| Ajukan penarikan | `nasabah.penarikan.index` | Ada. **Pilihan nominal cepat**: perilaku baru |
| Riwayat penarikan | `nasabah.riwayat.index` | Ada. Stepper dari `status`. `dibatalkan` dan `kedaluwarsa` belum punya desain tahap |
| Riwayat tabungan | `nasabah.riwayat-tabungan.index` | Ada. **Grafik batang 7 hari**: butuh agregat per hari |
| Ikuti paket | `nasabah.paket.pilih` | Ada. Header ilustrasi: baru (visual saja) |
| Progres paket | `nasabah.progres-paket.index` | Ada. Cincin dari `$persentase`, lencana dari `$progres['items']` |
| Notifikasi | `nasabah.notifikasi.index` | Ada. **Warna ubin per jenis**: kolom `jenis_notifikasi` (string) ada, tetapi nilainya belum terdaftar (hanya `pin_awal` ditemukan di kode). Sebelum itu pakai ubin hijau |
| Komplain | `nasabah.komplain.index` | Ada. Stepper dari `baru`/`diproses`/`selesai` |
| Pengaturan | `nasabah.pengaturan.index` | Ada |
| Login | `pages/auth/login.blade.php` | Ada. Panel hijau: visual saja. Teks galat tetap generik |
| Kondisi khusus | offline, kosong, sesi habis, terkunci | Ada. Teks "sesi berakhir" di kanvas adalah contoh, belum diverifikasi |
| Pengajuan terkirim | (baru) | **Baru.** Saat ini submit menampilkan banner `session('success')` di halaman yang sama. Layar sukses butuh state atau rute baru |

Urutan pengerjaan yang disarankan: (1) token dan komponen bersama (`hero-card`, `tile`, `progress-ring`, `stepper`), (2) Beranda dan Saldo, (3) Progres paket dan Riwayat penarikan, (4) sisanya, (5) elemen yang butuh data atau perilaku baru, satu per satu dengan tes.