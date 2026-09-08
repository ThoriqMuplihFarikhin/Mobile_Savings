# Design Document — Sistem Tabungan Digital

**Versi:** 1.0 (Draft)
**Terkait:** PRD.md
**Tujuan dokumen:** menerjemahkan kebutuhan di PRD menjadi struktur data, relasi antar entitas, alur status (state machine), dan daftar halaman per role — sebagai acuan sebelum implementasi.

---

## 1. Gambaran Arsitektur (High-Level)

```
[ Nasabah ]        [ Kolektor ]        [ Admin ]
     |                   |                  |
     +----------- Web App (Responsive) -----+
                         |
                  Backend / API
                         |
              +----------+----------+
              |                     |
          Database              Integrasi
        (data transaksi,       Eksternal
         nasabah, dll)         (WhatsApp API
                                untuk notifikasi)
```

- **Framework**: Laravel (PHP) — dipakai untuk backend sekaligus rendering halaman (bisa Blade untuk tampilan sederhana, atau dikombinasikan dengan Livewire/Inertia+Vue jika butuh interaktivitas lebih dinamis tanpa membangun API terpisah)
- **Database**: MySQL/MariaDB (default Laravel), relasional — sesuai kebutuhan karena banyak relasi antar entitas (nasabah-kolektor, nasabah-produk, transaksi-produk, dll)
- **ORM**: Eloquent — memudahkan mendefinisikan relasi (1:N, N:M) sesuai skema di bagian 2
- **Auth**: Laravel bisa pakai auth bawaan (Breeze/Fortify) dimodifikasi untuk login berbasis No. HP + PIN, dengan role/permission memakai package seperti Spatie Laravel-Permission untuk membedakan akses nasabah/kolektor/admin
- **Integrasi eksternal**: WhatsApp Business API (langsung atau lewat provider pihak ketiga seperti Fonnte/Wablas) untuk notifikasi, dipanggil dari backend Laravel (job/queue agar pengiriman tidak memblokir proses utama)

*(Catatan: pemilihan antara Blade murni, Livewire, atau Inertia+Vue/React untuk sisi tampilan masih bisa didiskusikan tergantung seberapa interaktif UI yang diinginkan — misalnya update saldo real-time tanpa reload halaman.)*

---

## 2. Model Data (Entity Overview)

### 2.1 Daftar Entitas

| Entitas | Ringkasan |
|---|---|
| `users` | Akun login (nasabah/kolektor/admin) |
| `nasabah_profil` | Data diri nasabah |
| `produk_tabungan` | Jenis produk (bebas/paket) beserta aturan |
| `transaksi_setoran` | Catatan setiap setoran |
| `transaksi_penarikan` | Catatan setiap pengajuan & pencairan |
| `saldo_produk` | Saldo nasabah per produk |
| `kepesertaan_paket` | Progres & tunggakan nasabah di suatu paket |
| `kolektor_nasabah` | Relasi penugasan kolektor ↔ nasabah |
| `jadwal_kunjungan` | Rencana kunjungan harian kolektor |
| `setoran_kolektor_kantor` | Rekonsiliasi kas kolektor ke kantor |
| `log_handover_kolektor` | Riwayat perpindahan tanggung jawab kolektor |
| `komplain` | Pengajuan komplain nasabah |
| `log_notifikasi` | Riwayat notifikasi terkirim |
| `log_aktivitas` | Audit trail seluruh aksi penting |

### 2.2 Detail Skema per Entitas

#### `users`
| Field | Tipe | Keterangan |
|---|---|---|
| id | PK | |
| no_hp | string, unique | dipakai sebagai username |
| pin_hash | string | PIN 6 digit, disimpan ter-hash |
| role | enum | `nasabah`, `kolektor`, `admin` |
| status_akun | enum | `aktif`, `terkunci` |
| percobaan_gagal | integer | counter anti brute-force |
| created_at, updated_at | timestamp | |

#### `nasabah_profil`
| Field | Tipe | Keterangan |
|---|---|---|
| id | PK | |
| user_id | FK → users | |
| nama, alamat | string | |
| didaftarkan_oleh | FK → users | kolektor/admin yang input |
| status_pendaftaran | enum | `pending_verifikasi`, `aktif`, `ditolak` |
| diverifikasi_oleh | FK → users, nullable | |
| tanggal_verifikasi | timestamp, nullable | |

#### `produk_tabungan`
| Field | Tipe | Keterangan |
|---|---|---|
| id | PK | |
| nama | string | mis. "Tabungan Bebas", "Paket A" |
| tipe | enum | `bebas`, `paket` |
| persen_komisi | decimal | % potongan saat penarikan |
| minimal_setor | decimal, nullable | |
| harga_per_hari | decimal, nullable | khusus tipe paket |
| isi_paket | text/JSON, nullable | daftar barang & jumlah |
| periode_mulai, periode_selesai | date, nullable | |
| tanggal_boleh_cair | date, nullable | khusus paket, diset manual admin |
| batas_toleransi_tunggakan_hari | integer, nullable | khusus paket |
| status | enum | `aktif`, `nonaktif` |

#### `transaksi_setoran`
| Field | Tipe | Keterangan |
|---|---|---|
| id | PK | |
| nasabah_id | FK | |
| produk_id | FK | |
| nominal | decimal | |
| tanggal_transaksi | date | kapan nasabah benar-benar menabung |
| tanggal_input_sistem | timestamp | kapan dicatat ke sistem |
| input_by | FK → users | kolektor/admin |
| sumber_input | enum | `real_time`, `susulan` |
| status | enum | `tercatat`, `dikoreksi`, `dibatalkan` |
| nominal_asli | decimal, nullable | terisi jika pernah dikoreksi |
| dikoreksi_oleh | FK → users, nullable | |
| alasan_koreksi | text, nullable | |
| sudah_disetor_ke_kantor | boolean | default false |
| setoran_kolektor_id | FK, nullable | relasi ke batch rekonsiliasi |

#### `transaksi_penarikan`
| Field | Tipe | Keterangan |
|---|---|---|
| id | PK | |
| nasabah_id | FK | |
| produk_id | FK | |
| nominal_diminta | decimal | |
| persen_komisi_terpakai | decimal | disalin dari produk saat transaksi dibuat |
| nominal_komisi | decimal | dihitung sistem |
| nominal_diterima | decimal | nominal_diminta − nominal_komisi |
| jalur_pengajuan | enum | `online`, `offline` |
| lokasi_pengambilan | enum | `rumah_kolektor`, `kantor` |
| status | enum | `pending`, `approved`, `selesai`, `ditolak` |
| disetujui_oleh | FK → users, nullable | |
| waktu_approval | timestamp, nullable | |
| waktu_pencairan | timestamp, nullable | |

#### `saldo_produk`
| Field | Tipe | Keterangan |
|---|---|---|
| id | PK | |
| nasabah_id | FK | |
| produk_id | FK | |
| saldo | decimal | update real-time dari setoran/penarikan/koreksi |

#### `kepesertaan_paket`
| Field | Tipe | Keterangan |
|---|---|---|
| id | PK | |
| nasabah_id | FK | |
| produk_id | FK | |
| tanggal_mulai_ikut | date | |
| total_seharusnya_terkumpul | decimal | dihitung: hari berjalan × harga_per_hari |
| total_aktual_terkumpul | decimal | dari akumulasi transaksi_setoran terkait |
| tunggakan | decimal | seharusnya − aktual |
| status_alert | enum | `normal`, `peringatan`, `perlu_review` |
| catatan_admin | text, nullable | |
| keputusan_akhir | enum, nullable | `lanjut`, `gagal_dikembalikan`, `gagal_dialihkan` |
| metode_pengambilan | enum, nullable | `ambil_sendiri`, `diantar_kolektor` |
| status_serah_terima | enum | `belum`, `sudah_diterima` |
| diterima_oleh | string, nullable | |
| tanggal_serah_terima | date, nullable | |
| bukti_foto_url | string, nullable | |

#### `kolektor_nasabah`
| Field | Tipe | Keterangan |
|---|---|---|
| id | PK | |
| kolektor_id | FK → users | |
| nasabah_id | FK | |
| tanggal_mulai_ditangani | date | |
| tanggal_selesai_ditangani | date, nullable | |
| status | enum | `aktif`, `nonaktif` |

#### `jadwal_kunjungan`
| Field | Tipe | Keterangan |
|---|---|---|
| id | PK | |
| kolektor_id | FK | |
| nasabah_id | FK | |
| tanggal_jadwal | date | |
| status_kunjungan | enum | `dikunjungi`, `dilewati`, `tidak_ada` |
| catatan | text, nullable | |

#### `setoran_kolektor_kantor`
| Field | Tipe | Keterangan |
|---|---|---|
| id | PK | |
| kolektor_id | FK | |
| tanggal_setor | date | |
| total_seharusnya | decimal | agregat dari transaksi_setoran yang belum disetor |
| total_diterima | decimal | input admin/kasir |
| selisih | decimal | total_diterima − total_seharusnya |
| keterangan_selisih | text, nullable | wajib jika selisih ≠ 0 |
| diterima_oleh | FK → users | |
| status | enum | `cocok`, `lebih`, `kurang` |

#### `log_handover_kolektor`
| Field | Tipe | Keterangan |
|---|---|---|
| id | PK | |
| kolektor_lama_id | FK | |
| kolektor_baru_id | FK | |
| tanggal_handover | date | |
| jumlah_nasabah_dipindah | integer | |
| status_kas_saat_handover | enum | `lunas`, `masih_tunggakan` |
| diproses_oleh | FK → users | |

#### `komplain`
| Field | Tipe | Keterangan |
|---|---|---|
| id | PK | |
| nasabah_id | FK | |
| kategori | enum | `saldo`, `barang_paket`, `penarikan`, `lainnya` |
| transaksi_terkait_id | FK, nullable | |
| deskripsi | text | |
| status | enum | `baru`, `diproses`, `selesai` |
| ditangani_oleh | FK → users, nullable | |
| catatan_penyelesaian | text, nullable | |
| tanggal_dibuat, tanggal_selesai | timestamp | |

#### `log_notifikasi`
| Field | Tipe | Keterangan |
|---|---|---|
| id | PK | |
| nasabah_id | FK | |
| jenis_notifikasi | string | mis. "setoran_tercatat", "penarikan_disetujui" |
| channel | enum | `whatsapp`, `in_app` |
| status_kirim | enum | `terkirim`, `gagal` |
| waktu_kirim | timestamp | |

#### `log_aktivitas`
| Field | Tipe | Keterangan |
|---|---|---|
| id | PK | |
| user_id | FK | siapa melakukan aksi |
| aksi | string | mis. "koreksi_setoran", "approve_penarikan" |
| entitas_terkait | string | nama tabel/entitas |
| entitas_id | integer | |
| detail | text/JSON, nullable | data sebelum/sesudah jika relevan |
| timestamp | timestamp | |

---

## 3. Diagram Relasi (Ringkas)

```
users ──1:1── nasabah_profil ──1:N── saldo_produk ──N:1── produk_tabungan
  │                  │
  │                  ├──1:N── transaksi_setoran ──N:1── produk_tabungan
  │                  ├──1:N── transaksi_penarikan ──N:1── produk_tabungan
  │                  ├──1:N── kepesertaan_paket ──N:1── produk_tabungan
  │                  ├──1:N── komplain
  │                  └──N:M── (via kolektor_nasabah) users(kolektor)
  │
  └── (role=kolektor) ──1:N── jadwal_kunjungan
                       ──1:N── setoran_kolektor_kantor
                       ──1:N── log_handover_kolektor (sebagai lama/baru)
```

---

## 4. State Machine (Alur Status Penting)

### 4.1 Status Pendaftaran Nasabah
```
[input oleh kolektor] → pending_verifikasi → (admin approve) → aktif
                                            → (admin tolak)   → ditolak
[input oleh admin]    → aktif (langsung)
```

### 4.2 Status Transaksi Setoran
```
tercatat → (admin koreksi nominal) → dikoreksi
tercatat → (admin batalkan)        → dibatalkan
```
*Saldo bertambah begitu status awal `tercatat` dibuat. Jika kemudian `dikoreksi`/`dibatalkan`, saldo disesuaikan otomatis mengikuti selisih.*

### 4.3 Status Transaksi Penarikan
```
pending → (admin approve) → approved → (dana dicairkan fisik) → selesai
        → (admin tolak)   → ditolak
```
*Validasi tambahan sebelum status bisa `pending` dibuat: jika produk bertipe paket, tanggal hari ini harus ≥ `tanggal_boleh_cair`.*

### 4.4 Status Alert Tunggakan Paket
```
normal → (tunggakan > 0 tapi < batas_toleransi) → peringatan
peringatan → (tunggakan ≥ batas_toleransi) → perlu_review
perlu_review → (admin putuskan) → keputusan_akhir: lanjut / gagal_dikembalikan / gagal_dialihkan
```

### 4.5 Status Rekonsiliasi Kas Kolektor
```
(input total_diterima oleh admin) →
  jika total_diterima == total_seharusnya → cocok
  jika total_diterima > total_seharusnya  → lebih (wajib keterangan)
  jika total_diterima < total_seharusnya  → kurang (wajib keterangan)
```

### 4.6 Status Komplain
```
baru → (admin mulai tangani) → diproses → (selesai ditindaklanjuti) → selesai
```

---

## 5. Daftar Halaman per Role

### 5.1 Nasabah
1. Login
2. Dashboard (ringkasan saldo semua produk)
3. Detail Riwayat Tabungan (filter per produk, per tanggal)
4. Detail Progres Paket (jika ikut paket — termasuk info tunggakan bila ada)
5. Form Ajukan Penarikan
6. Riwayat Pengajuan Penarikan
7. Form Ajukan Komplain
8. Riwayat & Status Komplain
9. Profile (lihat/edit data diri)

### 5.2 Kolektor
1. Login
2. Dashboard (ringkasan kas di tangan, jadwal hari ini)
3. Daftar Nasabah Tanggung Jawab (+ saldo & riwayat singkat)
4. Form Input Setoran (real-time/susulan, dengan indikator tunggakan jika produk paket)
5. Form Proses Penarikan Offline
6. Jadwal Kunjungan Harian (dengan update status kunjungan)
7. Form Setor Kas ke Kantor

### 5.3 Admin
1. Login
2. Dashboard Ringkasan (total saldo, setoran, komisi, kas per kolektor, pengajuan pending)
3. Verifikasi Pendaftaran Nasabah Baru
4. Monitoring & Koreksi Transaksi Setoran
5. Antrian Approval Penarikan
6. Kelola Produk & Paket (CRUD, atur tanggal cair, batas toleransi tunggakan)
7. Kelola Nasabah (cari/filter/detail/reset PIN/aktif-nonaktif)
8. Kelola Kolektor & Penugasan (assign nasabah, proses handover)
9. Rekonsiliasi Kas Kolektor
10. Daftar Nasabah Bermasalah (status `perlu_review`)
11. Antrian & Detail Komplain
12. Laporan (harian/bulanan/per kolektor/per paket, export)
13. Log Aktivitas (audit trail)

---

## 6. Aturan Bisnis Kunci yang Perlu Diimplementasikan di Backend (Bukan Hanya UI)

Poin-poin ini krusial untuk validasi di sisi server, bukan cukup divalidasi di frontend saja:

1. Nasabah tidak boleh memiliki endpoint/akses untuk membuat `transaksi_setoran` — hanya role kolektor/admin
2. Penarikan produk tipe `paket` ditolak otomatis jika tanggal saat ini < `tanggal_boleh_cair`
3. Perhitungan komisi selalu diambil dari `persen_komisi` produk saat transaksi dibuat (disalin ke transaksi, bukan hanya merujuk live ke tabel produk — supaya histori tidak berubah jika admin mengubah % komisi di kemudian hari)
4. Kolektor tidak bisa dinonaktifkan (`status_akun`) selama masih ada kas yang belum disetor (`sudah_disetor_ke_kantor = false`)
5. Perhitungan `tunggakan` pada `kepesertaan_paket` di-refresh setiap ada transaksi setoran baru terkait, bukan hanya dihitung sekali di awal
6. Setiap koreksi/pembatalan `transaksi_setoran` wajib menulis entri baru di `log_aktivitas` dan otomatis menyesuaikan `saldo_produk`

---

## 7. Hal Teknis yang Masih Perlu Didiskusikan

- Pilihan pendekatan tampilan: Blade biasa, Livewire, atau Inertia+Vue/React (tergantung kebutuhan interaktivitas real-time)
- Strategi hosting & backup database (mengingat data ini menyangkut uang nasabah, backup rutin sangat penting) — misalnya VPS (DigitalOcean, Niagahoster, Biznet) dengan backup terjadwal
- Detail integrasi WhatsApp Business API (provider, format pesan, biaya per pesan)
- Kebutuhan skalabilitas (perkiraan jumlah nasabah & kolektor di tahun pertama, untuk estimasi kapasitas server)

## 8. Struktur Proyek Laravel (Usulan Awal)

Pemetaan kasar Model/Controller utama mengikuti entitas di Bagian 2, sebagai titik awal `php artisan make:model` & migration:

```
app/Models/
  User.php                    (role: nasabah/kolektor/admin)
  NasabahProfil.php
  ProdukTabungan.php
  TransaksiSetoran.php
  TransaksiPenarikan.php
  SaldoProduk.php
  KepesertaanPaket.php
  KolektorNasabah.php
  JadwalKunjungan.php
  SetoranKolektorKantor.php
  LogHandoverKolektor.php
  Komplain.php
  LogNotifikasi.php
  LogAktivitas.php

app/Http/Controllers/
  Nasabah/    (DashboardController, PenarikanController, KomplainController, ...)
  Kolektor/   (SetoranController, JadwalController, RekonsiliasiController, ...)
  Admin/      (VerifikasiNasabahController, ProdukController, LaporanController, ...)

app/Policies/ atau menggunakan Spatie Permission
  — untuk membatasi akses tiap controller/route sesuai role
```

Rekomendasi urutan implementasi awal (MVP bertahap):
1. Auth (No. HP + PIN) + role management
2. Model & migration untuk entitas inti: User, NasabahProfil, ProdukTabungan, SaldoProduk
3. Alur setoran (kolektor input → saldo update) — fitur paling sering dipakai harian
4. Alur penarikan + approval admin
5. Kepesertaan paket + logika tunggakan
6. Rekonsiliasi kas kolektor
7. Notifikasi, komplain, handover kolektor, laporan (fitur pelengkap setelah alur inti stabil)