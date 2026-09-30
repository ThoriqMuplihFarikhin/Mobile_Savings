# Plan Log — Perbaikan Mobile_Savings (branch `fix/audit-2026-09`)

Kolom: task · status · catatan · commit.

Status: `berjalan` · `selesai` · `tidak reproduksi` · `ditunda` · `dibatalkan`

| Task | Status | Catatan | Commit |
|------|--------|---------|--------|
| F0 Persiapan | selesai | Branch `fix/audit-2026-09`. Baseline `php artisan test --compact`: 130 tes, 126 lulus, 4 skip, 0 gagal (MySQL 127.0.0.1, DB `tabungan_digital`). Keputusan K1 = MySQL (PHP mesin ini tidak punya `pdo_sqlite`, jadi suite tak bisa jalan di SQLite in-memory). WIP sesi sebelumnya (54 modifikasi + 19 file baru) di-commit dulu sebagai baseline. | `chore: baseline WIP sebelum audit` |

## Keputusan owner

| # | Pertanyaan | Jawaban | Dampak |
|---|-----------|---------|--------|
| K1 | Database produksi MySQL atau SQLite? | **MySQL** (pdo_sqlite tidak tersedia di mesin dev; phpunit.xml tetap MySQL) | T5.1, T3.7 |
| K2 | Sudah ada data produksi? | (belum dijawab) | T2.4, T3.4 |
| K3 | Otorisasi pencairan offline: PIN atau OTP? | (belum dijawab) | T3.3 |
| K4 | Batas backdate setoran `susulan` (hari)? | (belum dijawab) | T2.1 |
| K5 | `produk.minimal_setor` wajib ditegakkan? | (belum dijawab) | T2.1 |
| K6 | Koreksi/batal setelah kas disetor ke kantor? | (belum dijawab) | T1.4 |
| K7 | Kolektor lama saat handover: `terkunci` atau status baru? | (belum dijawab) | T1.1 |
