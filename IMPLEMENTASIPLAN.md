# Implementation Plan — Perbaikan Sistem Tabungan Digital (Kolektor Keliling)

Dokumen ini merangkum rencana perbaikan dari hasil audit kode terhadap 5 temuan prioritas. Disusun berurutan berdasarkan risiko produksi, bukan berdasarkan kemudahan pengerjaan — kerjakan dari atas ke bawah.

---

## Fase 1 — Kritis (kerjakan duluan, risiko produksi nyata)

### 1.1 Pindahkan notifikasi keluar dari `DB::transaction()` di `ApprovalPenarikan`

**Masalah:** `ActivityLogger::notify(..., 'both')` dipanggil di dalam transaction yang meng-hold lock pada `saldo_produk` dan `transaksi_penarikan`. HTTP call sinkron ke gateway WhatsApp bisa memperpanjang durasi lock atau membatalkan approval yang sebenarnya valid jika API WA gagal.

**File:** `app/Livewire/Admin/ApprovalPenarikan.php`

**Langkah:**
1. Refactor `approve()`: pisahkan bagian yang butuh lock (update saldo, update status transaksi) dari bagian notify.
2. Struktur baru:
   ```php
   public function approve($id): void
   {
       $transaksi = DB::transaction(function () use ($id) {
           // semua lockForUpdate() + update di sini
           // return $transaksi setelah commit implicit di akhir closure
       });

       // notify di LUAR closure, setelah transaction commit
       ActivityLogger::notify($transaksi->nasabah_id, ..., 'both');
       ActivityLogger::log(...);
   }
   ```
3. Terapkan pola yang sama di `HandoverKolektor::processHandover()` — pindahkan loop `ActivityLogger::notify()` ke setelah `DB::commit()`, walau saat ini jenisnya `in_app` (risiko lebih rendah), demi konsistensi pola di seluruh codebase.
4. Tambahkan try/catch di sekitar pemanggilan `WhatsAppService` supaya kegagalan kirim WA tidak pernah bisa mempengaruhi status transaksi yang sudah committed (harusnya sudah aman begitu dipindah ke luar transaction, tapi pastikan exception dari WA service tidak menggagalkan response ke user).

**Testing:**
- Test baru: simulasikan `WhatsAppService` melempar exception (mock/fake), pastikan `TransaksiPenarikan` tetap berstatus `disetujui` di DB walau notifikasi gagal.
- Regression test pada `WithdrawalTest.php` yang sudah ada — pastikan masih hijau.

**Estimasi:** 2-3 jam termasuk test.

---

## Fase 2 — Penting (kesenjangan PRD vs implementasi)

### 2.1 Implementasi formula tunggakan 2x lipat (FR-17)

**File:** model/lokasi tempat `refreshTunggakan()` didefinisikan (kemungkinan trait/helper yang dipakai `InputSetoran`).

**Langkah:**
1. Konfirmasi dulu ke pemilik bisnis: apakah "2x lipat" berlaku per-hari-bolong (kumulatif tiap hari terlewat dilipatgandakan) atau flat 2x dari cicilan normal saat pertama kali bayar setelah bolong. ⚠️ **Jangan mulai coding sebelum ini jelas** — ini keputusan bisnis, bukan teknis.
2. Setelah rule dikonfirmasi, ubah `refreshTunggakan()` dari `max(0, seharusnya - aktual)` menjadi formula yang mengalikan porsi yang di-skip.
3. Tambahkan kolom/field log jika perlu untuk audit trail (berapa hari bolong, berapa pengali yang diterapkan) — supaya nasabah bisa complain dengan basis yang jelas kalau merasa salah hitung.

**Testing:**
- Test case: nasabah bolong 1 hari → tunggakan berikutnya 2x cicilan normal.
- Test case: nasabah bolong 3 hari berturut-turut → verifikasi sesuai formula final yang disepakati (kumulatif atau flat, sesuai poin 1).
- Test case: nasabah bayar pas → tunggakan tetap 0.

**Estimasi:** 1 jam diskusi bisnis + 2-3 jam implementasi & test (tergantung kompleksitas formula final).

### 2.2 Lengkapi fitur komplain sesuai FR-28 dan FR-30

**File:** `app/Livewire/Nasabah/Komplain.php`, `resources/views/livewire/nasabah/komplain.blade.php`, `app/Livewire/Admin/AntrianKomplain.php`

**Langkah:**
1. Tambah field opsional di form komplain nasabah untuk memilih transaksi terkait (dropdown dari riwayat transaksi nasabah tsb, 30 hari terakhir misalnya). Simpan ke `transaksi_terkait_id` yang sudah ada di skema.
2. Tampilkan info transaksi terkait (jika ada) di halaman detail komplain admin (`AntrianKomplain`), biar admin nggak perlu cari manual.
3. Tambahkan `ActivityLogger::notify()` di method `proses()`, dengan pesan seperti "Komplain Anda sedang diproses oleh tim kami."

**Testing:**
- Test: submit komplain dengan `transaksi_terkait_id` terisi → tersimpan dengan benar.
- Test: admin klik "proses" → nasabah menerima 1 notifikasi baru dengan status `diproses`.

**Estimasi:** 3-4 jam termasuk UI.

---

## Fase 3 — Konsolidasi (kurangi risiko drift ke depan)

### 3.1 Ekstrak logic penarikan ke satu Action class

**File baru:** `app/Actions/Penarikan/AjukanPenarikanAction.php`

**Langkah:**
1. Buat class dengan method `execute(User $nasabah, ProdukTabungan $produk, float $nominal, string $jalur): TransaksiPenarikan` yang berisi:
   - Hitung saldo tersedia (saldo - total pending).
   - Cek blokir tanggal cair untuk produk paket.
   - Hitung komisi berdasarkan `persen_komisi` produk saat itu.
   - Create `TransaksiPenarikan` dengan status `pending`.
2. Refactor `AjukanPenarikan::submit()` dan `PenarikanOffline::submit()` supaya keduanya cuma memanggil Action ini, lalu handle UI-specific concern (flash message, reset form, redirect) di masing-masing.
3. Pastikan validasi input (nominal minimum, dsb) tetap dilakukan di layer Livewire masing-masing sebelum manggil Action — Action fokus ke business rule, bukan input validation.

**Testing:**
- Pindahkan/duplikasi test yang relevan dari `WithdrawalTest.php` supaya jalan lewat kedua jalur (nasabah & kolektor) dan hasilnya identik untuk skenario yang sama.
- Tambahkan unit test langsung ke `AjukanPenarikanAction` tanpa lewat Livewire, supaya business rule bisa dites terisolasi.

**Estimasi:** 4-5 jam (perubahan struktural, butuh regression test menyeluruh).

---

## Fase 4 — Cleanup (boleh dikerjakan kapan saja, low-risk)

| # | Item | File | Estimasi |
|---|------|------|----------|
| 4.1 | Hapus duplikasi `orWhereIn` di `loadNasabah()` | `app/Livewire/Kolektor/InputSetoran.php` | 10 menit |
| 4.2 | Recalculate `totalSeharusnya` di dalam transaction + `lockForUpdate()` saat `submit()` | `app/Livewire/Admin/RekonsiliasiKas.php` | 30-45 menit |
| 4.3 | Hapus dead code `$statusKas` (selalu `'lunas'`) atau perjelas maksudnya | `app/Livewire/Admin/HandoverKolektor.php` | 15 menit |
| 4.4 | Klarifikasi ke bisnis: apakah "pindah wilayah" harus menonaktifkan akun kolektor | `app/Livewire/Admin/HandoverKolektor.php` | diskusi dulu, baru kode |
| 4.5 | Try/catch `QueryException` di `absenMasuk()` untuk pesan error yang ramah | `app/Livewire/Kolektor/Absen.php` | 20 menit |
| 4.6 | Simpan tanda tangan sebagai file (konsisten dengan selfie), bukan base64 di kolom `text` | `app/Livewire/Kolektor/Absen.php` + migration baru | 1 jam (perlu migration data lama) |
| 4.7 | Bungkus `DB::transaction()` di `VerifikasiNasabah::approve()`/`reject()` | `app/Livewire/Admin/VerifikasiNasabah.php` | 20 menit |

---

## Urutan pengerjaan yang disarankan

1. **Fase 1** dulu (1.1) — ini yang paling berisiko kalau dibiarkan di produksi.
2. **Fase 2.1** — tapi mulai dari diskusi bisnis dulu, jangan nunggu approval baru mulai riset formula.
3. **Fase 2.2** dan **Fase 4** bisa dikerjakan paralel/di sela-sela, ukurannya kecil-kecil.
4. **Fase 3** terakhir karena sifatnya refactor struktural — lebih aman dikerjakan setelah Fase 1 & 2 stabil, supaya nggak nyampur perubahan behavior dengan perubahan struktur di PR yang sama.

Total estimasi kasar: **~2-3 hari kerja** kalau dikerjakan sendiri secara berurutan, atau **1.5-2 hari** kalau Fase 2 dan Fase 4 dikerjakan paralel dengan orang lain.