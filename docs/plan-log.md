# Plan Log — Perbaikan Mobile_Savings (branch `fix/audit-2026-09`)

Kolom: task · status · catatan · commit.

Status: `berjalan` · `selesai` · `tidak reproduksi` · `ditunda` · `dibatalkan`

| Task | Status | Catatan | Commit |
|------|--------|---------|--------|
| F0 Persiapan | selesai | Branch `fix/audit-2026-09`. Baseline `php artisan test --compact`: 130 tes, 126 lulus, 4 skip, 0 gagal (MySQL 127.0.0.1, DB `tabungan_digital`). Keputusan K1 = MySQL (PHP mesin ini tidak punya `pdo_sqlite`, jadi suite tak bisa jalan di SQLite in-memory). WIP sesi sebelumnya (54 modifikasi + 19 file baru) di-commit dulu sebagai baseline. | `chore: baseline WIP sebelum audit` |
| T1.4 Status `dikoreksi` | selesai | Scope `masihAktif()`/`belumDisetor()` di `TransaksiSetoran` dipakai di SetorKantor, RekonsiliasiKas, HandoverKolektor, InputSetoran, `User::hasUnsettledCash`, dan `DashboardController` (semua filter `whereIn status tercatat/dikoreksi`). `'tercatat'` tersisa hanya pada guard koreksi/batal, default insert, dan definisi scope. Tes `KasStatusDikoreksiTest` (4). | `e9b738c` |
| T1.1 Handover kolektor | selesai | `#[Locked]` untuk kas; validasi `Rule::exists` (lama = role kolektor, baru = `status_akun aktif`); rekap kas & id nasabah dihitung ulang dengan `lockForUpdate` di dalam transaksi (ada kas → rollback + pesan generik, `report()`); kolektor lama → `status_akun = terkunci` (K7); `LogHandoverKolektor` dibuat dulu agar `ActivityLogger` memakai id-nya. Tes `HandoverKolektorTest` (6). | `cfd2ba4` |
| T1.2 Setoran paket berulang | selesai | Blok `$existingActive` dihapus dari `InputSetoran::submit()`; `minimal_setor` tetap berlaku. Tes `PaketDepositTest` (3). | `c11da92` |
| T1.3 Setor kantor & rekon | selesai | `#[Locked]` total di `SetorKantor`, total dihitung ulang dari DB (`lockForUpdate`) di `submit`, notice "menunggu verifikasi". `RekonsiliasiKas::submit()` & `processSubmission()` dibungkus transaksi: tolak pengajuan pending ganda, rekap `total_seharusnya` dihitung ulang dari transaksi terikat, `selisih` di-`round(...,2)`, status lewat `match`, `DomainException` → flash generik. Tes `SetorKantorTest` (5). Catatan: `TestResponse::assertSessionHas` tidak reliabel untuk respons Livewire → asersi berbasis DB/state. | `016fc9c` |
| T1.5 Hitung tunggakan paket | selesai | `hitungUlangKepesertaan()` kini: hari berjalan = `(int) diffInDays(now()->startOfDay()) + 1` dari `tanggal_mulai_ikut` mulai awal hari (bilangan bulat, jam berapa pun), di-clamp `min(hariBerjalan, totalHariPaket())`, mulai ikut masa depan → 0 (tanpa `total_seharusnya` negatif), `hariTerbayar = floor(aktual/harga)` float, `tunggakan` & `tunggakan_rupiah` di-`round(...,2)`. `totalHariPaket()` di-`(int)` + `max(0,…)`. 6 tes baru (dataset jam, nabung lebih, cap periode, mulai masa depan). | `499978b` |
| T2.4 Zona waktu | selesai | `config/app.timezone` dari `UTC` → `Asia/Jakarta`. Dampak: `now()->toDateString()` untuk absen, setor, rekon, tunggakan, dan filter dashboard kini mengikuti tanggal Jakarta (sebelumnya 7 jam tiap hari memakai tanggal UTC yang salah). Tidak ada pemakaian `UTC` eksplisit lain di kode aplikasi. Tes `ZonaWaktuTest` (3). | `5bd67f9` |


## Keputusan owner

| # | Pertanyaan | Jawaban | Dampak |
|---|-----------|---------|--------|
| K1 | Database produksi MySQL atau SQLite? | **MySQL** (pdo_sqlite tidak tersedia di mesin dev; phpunit.xml tetap MySQL) | T5.1, T3.7 |
| K2 | Sudah ada data produksi? | (belum dijawab) | T2.4, T3.4 |
| K3 | Otorisasi pencairan offline: PIN atau OTP? | (belum dijawab) | T3.3 |
| K4 | Batas backdate setoran `susulan` (hari)? | (belum dijawab) | T2.1 |
| K5 | `produk.minimal_setor` wajib ditegakkan? | (belum dijawab) | T2.1 |
| K6 | Koreksi/batal setelah kas disetor ke kantor? | (belum dijawab) | T1.4 |
| K7 | Kolektor lama saat handover: `terkunci` atau status baru? | **`terkunci`** (dipakai pada T1.1) | T1.1 |
