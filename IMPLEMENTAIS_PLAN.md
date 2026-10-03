# IMPLEMENTATION PLAN — Perbaikan & Fitur Mobile_Savings (Audit Oktober 2026)

> Dokumen ini adalah instruksi kerja untuk AI agent. Kerjakan **berurutan per fase**, satu task = satu commit.
> Sumber temuan: audit statis 3 Okt 2026 (`php -l` 212 file lolos; proxy `$wire` Livewire v4.4.4 diuji di Node).
> Stack terkunci: Laravel 13.30, Livewire 4.4.4, Flux 2.19, Fortify 1.39, spatie/permission 8.3, Pest, MySQL (produksi & test).

---

## 0. ATURAN KERJA (WAJIB, berlaku untuk semua task)

1. **Baca dulu** `CLAUDE.md`, `prd.md`, `docs/plan-log.md` (terutama bagian K1–K6, T2.x–T4.x). Jangan membalik keputusan K1–K6 tanpa persetujuan owner.
2. Buat branch `fix/audit-2026-10`. Satu task = satu commit: `fix(P1.1): <ringkasan>` / `feat(P6.2): <ringkasan>`.
3. **Red → Green**: tulis tes yang gagal dulu (Pest), baru perbaiki. Tes baru ditaruh di `tests/Feature` / `tests/Unit` mengikuti penamaan yang ada.
4. Setiap task selesai harus lolos: `vendor/bin/pint --dirty`, `php artisan test --compact` (seluruh suite), dan **PHPStan tidak menambah error** pada file yang disentuh (`vendor/bin/phpstan analyse <file> --memory-limit=1G`).
5. Gunakan `php artisan make:*` dengan `--no-interaction` untuk file baru. Tidak membuat folder dasar baru; `app/Actions/<Domain>` sudah ada.
6. **Jangan ubah dependency** (`composer.json`/`package.json`) tanpa persetujuan owner. Task yang butuh paket baru ditandai `[BUTUH APPROVAL]`.
7. Pola yang wajib diikuti (sudah dipakai di kode existing):
   - Operasi uang: `DB::transaction` + `lockForUpdate()` + guard status (lihat `ApprovalPenarikan::approve`).
   - Log audit lewat `ActivityLogger::log()` dan notifikasi lewat `ActivityLogger::notify()` **setelah commit**, dibungkus try/catch + `report()`.
   - Komponen Livewire baru memakai trait `AuthorizesRole` dan route di grup middleware yang sesuai di `routes/web.php`.
   - Aksi uang/destruktif di view memakai `wire:confirm`.
   - Teks UI Bahasa Indonesia; pesan error ke user generik (jangan bocorkan `$e->getMessage()` kecuali `InvalidArgumentException` yang disengaja).
   - **Dilarang**: properti publik dan method publik dengan nama sama di komponen Livewire (lihat P1.1).
8. Tes **tidak boleh** berjalan di DB dev. Pertahankan guard `tests/TestCase.php` dan `phpunit.xml` (MySQL `tabungan_digital_test`).
9. Setelah tiap task, tambahkan satu baris ke `docs/plan-log.md` (ID, status, file utama, hasil tes). Asumsi untuk keputusan owner ditulis eksplisit sebagai `ASUMSI D#`.
10. Jika menemukan ketidakcocokan dengan deskripsi di dokumen ini (nama kolom, path), **verifikasi dengan `grep`/baca file dulu**, catat di plan-log, lalu sesuaikan. Jangan menebak.

### Keputusan owner yang masih terbuka (agent memakai DEFAULT bila belum dijawab)

| ID | Pertanyaan | DEFAULT yang dipakai agent |
|----|-----------|----------------------------|
| D1 | Arti keputusan `lanjut` / `gagal_dikembalikan` / `gagal_dialihkan` | `lanjut` = kelonggaran sementara (kepesertaan tetap aktif, alert ditunda). `gagal_dikembalikan` = membuat penarikan pending berkomisi 0 untuk seluruh saldo paket. `gagal_dialihkan` = transfer saldo ke produk tujuan pilihan admin |
| D2 | Penarikan online lokasi `rumah_kolektor` | Kolektor penanggung jawab mengantar dan memverifikasi PIN nasabah, sama seperti jalur offline |
| D3 | Pengiriman PIN awal | PIN awal dikirim ke WA nasabah, tidak ditampilkan ke kolektor. Fallback tampil ke admin saja bila WA tidak aktif |
| D4 | Penarikan tunai di rumah memakai kas siapa | Belum diputuskan, **jangan ubah perhitungan rekonsiliasi** (P3.3 hanya menyiapkan kolom opsional) |
| D5 | Hapus nasabah | Pertahankan fitur, tapi log audit tidak boleh dihapus (FK `nullOnDelete` + log `hapus_nasabah`) |
| D6 | Komisi paket | Pertahankan 0% (perilaku UI saat ini), tampilkan penjelasan di form produk |
| D7 | Minimal penarikan | Default Rp10.000 lewat `AdminSetting`, boleh menarik **seluruh sisa saldo** walau di bawah minimal |
| D8 | Paket pihak ketiga (PDF, XLSX, backup) | Tidak dipasang. Pakai CSV + halaman cetak (`window.print`) |
| D9 | Persetujuan ganda penarikan besar | Fitur dibuat nonaktif secara default (threshold 0 = mati) |

---

## FASE 0 — Persiapan & baseline

**P0.1 Baseline**
- `composer install`, siapkan DB `tabungan_digital_test` (MySQL), jalankan `php artisan test --compact`, `vendor/bin/pint --test`, `vendor/bin/phpstan analyse --memory-limit=1G`.
- Catat hasil (jumlah tes lulus, jumlah error PHPStan, harusnya ±573) di `docs/plan-log.md` sebagai "Baseline Audit Okt 2026".
- Selesai bila: baseline tercatat. Jika ada tes merah di baseline, **jangan diperbaiki diam-diam**; catat dan laporkan.

---

## FASE 1 — Perbaikan kritis cepat (kerjakan paling awal)

### P1.1 [KRITIS] Tombol mati: bentrok nama properti vs method Livewire
**Masalah**: proxy `$wire` mendahulukan properti publik (`state`) sebelum fallback ke method. `wire:click="showDetail(5)"` menjadi `$wire.showDetail(5)` → `TypeError: not a function` (hanya `console.warn`). Tes tidak menangkap karena `Livewire::test()->call()` memanggil method PHP langsung.

**Komponen terdampak (7 tombol)**:

| Komponen (`app/Livewire/Admin/`) | Properti bentrok | Ganti menjadi |
|---|---|---|
| `AntrianKomplain` | `$showDetail` | `$tampilDetail` |
| `ManajemenNasabah` | `$confirmDelete` | `$tampilKonfirmasiHapus` |
| `ManajemenNasabah` | `$confirmResetPin` | `$tampilKonfirmasiResetPin` |
| `ManajemenProduk` | `$confirmDelete` | `$tampilKonfirmasiHapus` |
| `KelolaKolektor` | `$confirmResetPin` | `$tampilKonfirmasiResetPin` |
| `DetailNasabah` | `$confirmResetPin` | `$tampilKonfirmasiResetPin` |

**Langkah**:
1. Rename properti **saja** (nama method tetap `showDetail`, `confirmDelete`, `confirmResetPin`).
2. Update semua referensi di method PHP (`$this->showDetail = ...`) dan di blade `resources/views/livewire/admin/{antrian-komplain,manajemen-nasabah,manajemen-produk,kelola-kolektor,detail-nasabah}.blade.php` (`@if($showDetail)`, `$set('confirmDelete', false)`, dst.).
3. Update tes yang memakai nama properti lama: `tests/Feature/NasabahDeleteTest.php` (`assertSet('confirmDelete', ...)`), `tests/Feature/ResetPinOlehAdminTest.php` (`assertSet('confirmResetPin', ...)`), dan `grep -rn "showDetail" tests`.
4. **Tes penjaga permanen** `tests/Unit/LivewireNamingCollisionTest.php`:
   - Refleksi semua class di `app/Livewire/**`: gagal bila ada nama yang sekaligus properti publik dan method publik.
   - Parse semua `resources/views/livewire/**/*.blade.php` dan `resources/views/pages/**`: ekstrak nama dari `wire:click|wire:submit|wire:change="nama(...)"`; pastikan nama tersebut adalah method publik komponen terkait dan **bukan** properti publik.
5. Tidak perlu Dusk (butuh dependency). Cukup tes penjaga di atas + verifikasi manual satu kali di browser (catat di plan-log).

**Selesai bila**: tes penjaga hijau, seluruh suite hijau, 5 komponen berfungsi di browser (buka detail komplain, hapus produk, hapus nasabah, reset PIN dari 3 halaman).

### P1.2 [KRITIS] Validasi produk paket + guard penarikan
**Masalah**: `tanggal_boleh_cair` opsional → penarikan paket tidak terblokir; `periode_*` tidak divalidasi → tunggakan tak terbatas atau nol; `save()` memaksa `status => 'aktif'`.

**File**: `app/Livewire/Admin/ManajemenProduk.php`, `app/Actions/Penarikan/AjukanPenarikanAction.php`, `resources/views/livewire/admin/manajemen-produk.blade.php`.

**Langkah**:
1. Rules saat `tipe = paket`: `harga_per_hari` required `numeric|min:1`; `periode_mulai`, `periode_selesai` required `date`; `periode_selesai` `after_or_equal:periode_mulai`; `tanggal_boleh_cair` required `date|after_or_equal:periode_selesai`. Untuk tipe bukan paket, kosongkan field-field itu.
2. `save()`: `status` hanya diisi `'aktif'` saat **create**. Saat update jangan menyentuh `status`.
3. `edit()`, `save()` (update), `delete()`: ganti `find()` tanpa null-check menjadi pola aman (`find` + flash error bila null), tidak boleh 500.
4. Defense in depth di `AjukanPenarikanAction`: bila produk `paket` dan `tanggal_boleh_cair` null → `throw new \InvalidArgumentException('Tanggal pencairan paket belum ditetapkan. Hubungi admin.')`.
5. Command audit data lama: `php artisan produk:audit-paket` (make:command) yang mencantumkan produk paket dengan `tanggal_boleh_cair`/`periode_*` kosong. Jalankan dan laporkan hasilnya di plan-log (jangan mengubah data produksi otomatis).
6. UI: tampilkan peringatan bila mengubah `harga_per_hari`/periode produk paket yang sudah punya peserta ("mengubah ini menghitung ulang tunggakan seluruh peserta"). Tambah teks bantuan D6 (komisi paket 0%).

**Tes baru** (`tests/Feature/ManajemenProdukTest.php`): paket tanpa tanggal → validasi gagal; edit produk nonaktif tetap nonaktif; hapus/edit id tidak ada tidak crash; penarikan paket dengan `tanggal_boleh_cair` null ditolak.

### P1.3 Higiene repo & link placeholder
- Sembunyikan tautan "Ajukan Izin" di `resources/views/livewire/kolektor/pengaturan.blade.php` (baris ~141) sampai P6.7 selesai. Biarkan route tetap ada.
- Hapus `replace_admin_colors.php`, `replace_admin_ui.php` (verifikasi tidak direferensikan: `grep -rn "replace_admin" .`).
- Hapus `resources/views/livewire/admin/dashboard.blade.php` **hanya jika** terbukti tidak dirujuk (`grep -rn "admin.dashboard\|livewire.admin.dashboard"`). Catat hasil.
- README: tambah catatan "jangan deploy dari zip Windows (nama file `⚡*.blade.php` rusak jadi `#U26a1`); gunakan `git archive` atau `git clone`" dan "jangan sertakan `bootstrap/cache/*.php`".

---

## FASE 2 — Mesin paket (perhitungan, jadwal, keputusan)

> Urutan wajib: P2.1 → P2.2 → P2.3 → P2.4 (P2.3 dan P2.4 memakai `kepesertaan_id`).

### P2.1 Isolasi setoran per kepesertaan
**Masalah**: `totalAktual` menjumlah **semua** setoran nasabah+produk sepanjang masa; kepesertaan baru (setelah `keputusan_akhir` terisi) ikut menghitung setoran lama → tunggakan tampak nol.

**Langkah**:
1. Migration: `transaksi_setoran.kepesertaan_id` nullable `foreignId` ke `kepesertaan_paket` (`nullOnDelete`), index.
2. Backfill dalam migration: untuk tiap `kepesertaan_paket`, set `kepesertaan_id` pada setoran milik `nasabah_id`+`produk_id` yang `tanggal_transaksi >= tanggal_mulai_ikut - 7 hari`? **Jangan menebak**: gunakan aturan sederhana: jika hanya ada satu kepesertaan untuk pasangan nasabah+produk, semua setoran pasangan itu dikaitkan ke kepesertaan itu; jika lebih dari satu, kaitkan berdasarkan `created_at` setoran terhadap `created_at` kepesertaan (setoran masuk ke kepesertaan terbaru yang `created_at <=` setoran). Catat jumlah baris yang tidak terkait.
3. `InputSetoran::submit` (dan nanti `CatatSetoranAction`, P3.5): bila produk paket, set `kepesertaan_id` dari hasil `HitungTunggakanAction`.
4. `KepesertaanPaket::hitungUlangKepesertaan` / `HitungTunggakanAction`: `totalAktual` = jumlah setoran **dengan `kepesertaan_id` = kepesertaan ini** dan `status != dibatalkan`.
5. Tambah relasi `KepesertaanPaket::setoran()` dan `TransaksiSetoran::kepesertaan()`.

**Tes**: dua kepesertaan berurutan (yang pertama `gagal_dikembalikan`) → setoran lama tidak dihitung di kepesertaan baru; backfill benar untuk satu dan dua kepesertaan.

### P2.2 Koreksi/batal setoran: berulang + sinkron kepesertaan
**File**: `app/Livewire/Admin/MonitoringSetoran.php`.

**Langkah**:
1. `koreksi()` boleh untuk status `tercatat` **dan** `dikoreksi`. `nominal_asli` hanya diisi sekali (nilai awal), jangan ditimpa pada koreksi berikutnya. Log menyimpan `nominal_lama` dan `nominal_baru` di setiap koreksi.
2. `batal()` boleh untuk `tercatat` dan `dikoreksi`.
3. Tetap blokir bila saldo menjadi negatif (pertahankan guard existing).
4. Setelah koreksi/batal pada produk paket: panggil pembaruan kepesertaan **tanpa membuat kepesertaan baru**. Tambah method `HitungTunggakanAction::perbarui(int $nasabahId, int $produkId): void` yang hanya menghitung ulang kepesertaan aktif bila ada.
5. Jika setoran sudah `sudah_disetor_ke_kantor = true`: tampilkan peringatan di modal dan simpan `sudah_disetor = true` di `detail` log (keputusan K6 tetap: tidak mengubah kas, hanya penanda untuk audit).

**Tes**: koreksi dua kali (nominal_asli tetap), batal setelah koreksi, tunggakan paket ikut berubah, setoran sudah disetor memberi penanda di log.

### P2.3 Scheduler, perhitungan harian, pengingat
**Masalah**: tidak ada scheduler → `status_alert` hanya berubah saat ada setoran; nasabah yang berhenti menabung tak pernah terdeteksi.

**Langkah**:
1. `php artisan make:command HitungUlangPaket --no-interaction` → signature `paket:hitung-ulang`. Iterasi `KepesertaanPaket::whereNull('keputusan_akhir')->chunkById(200)` dan panggil `hitungUlangKepesertaan(simpan: true)`. Hormati `ditunda_hingga` (P2.4).
2. `php artisan make:command KirimPengingatTunggakan` → `paket:kirim-pengingat`: untuk kepesertaan dengan tunggakan hari > 0, kirim `ActivityLogger::notify` ke nasabah **maksimal sekali per hari per nasabah-produk** (dedupe lewat cek `log_notifikasi` hari ini dengan judul sama atau `Cache::add`). Kirim juga ke WA bila aktif.
3. `routes/console.php`: 
   ```php
   Schedule::command('paket:hitung-ulang')->dailyAt('00:10')->withoutOverlapping()->onOneServer();
   Schedule::command('paket:kirim-pengingat')->dailyAt('08:00')->withoutOverlapping();
   Schedule::command('queue:prune-failed --hours=168')->daily();
   ```
   Pastikan timezone aplikasi `Asia/Jakarta` (cek `config/app.php`).
4. README bagian "Deploy produksi": cron `* * * * * php /path/artisan schedule:run`, worker `php artisan queue:work --tries=3 --timeout=60` di bawah Supervisor/systemd (contoh file), `php artisan storage:link`, MySQL wajib.
5. Dashboard admin: tambah kartu "Perlu review: N" (jumlah `status_alert = perlu_review`) linking ke `admin.nasabah-bermasalah`.

**Tes**: dengan `Carbon::setTestNow` — nasabah berhenti menabung 10 hari → setelah command, `status_alert` naik; pengingat tidak terkirim dua kali dalam sehari; command idempoten.

### P2.4 Keputusan akhir paket yang benar-benar berefek (D1)
**File**: `app/Livewire/Admin/NasabahBermasalah.php`, view terkait, `app/Models/KepesertaanPaket.php`.

**Langkah**:
1. Migration: `kepesertaan_paket.ditunda_hingga` date nullable.
2. Redefinisi (D1):
   - `lanjut` → **jangan** mengisi `keputusan_akhir`; isi `ditunda_hingga` (input tanggal wajib, maksimal 90 hari ke depan) + `catatan_admin`. Selama `ditunda_hingga >= today`, perhitungan tidak menaikkan alert ke `perlu_review` (tunggakan tetap dihitung dan ditampilkan). Migrasi data: kepesertaan lama berstatus `lanjut` → set `keputusan_akhir = null`, `ditunda_hingga = today + 30`, catat jumlahnya.
   - `gagal_dikembalikan` → `Action` baru `app/Actions/Paket/ProsesKegagalanPaketAction`: dalam transaksi + lock, buat `TransaksiPenarikan` pending (jalur `offline`, lokasi pilihan admin, `persen_komisi_terpakai = 0`, nominal = seluruh saldo produk) lalu isi `keputusan_akhir`. Admin menyelesaikan lewat alur approval biasa.
   - `gagal_dialihkan` → admin memilih produk tujuan (aktif, bukan paket yang sama); transfer saldo atomik (decrement + increment dengan lock kedua baris berurutan berdasarkan id untuk menghindari deadlock), buat dua log.
3. Semua keputusan: guard idempoten (hanya bila `keputusan_akhir` null, `lockForUpdate`), `ActivityLogger::log('keputusan_paket', ...)` dengan detail lengkap, `ActivityLogger::notify` ke nasabah.
4. `selectKepesertaan`: null-safe (flash error, bukan 500). `updateKeputusan`: guard `selectedKepesertaan` null.

**Tes** (`tests/Feature/NasabahBermasalahTest.php`, belum ada): tiap keputusan, idempotensi (klik ganda), log tercatat, notifikasi terkirim, saldo konsisten setelah alih, tidak bisa keputusan ganda.

---

## FASE 3 — Penarikan & kas

### P3.1 Penarikan online ke rumah kolektor punya jalur kolektor (D2)
**Masalah**: `Kolektor/VerifikasiPenarikan` hanya memuat `jalur_pengajuan = offline`; online + `rumah_kolektor` hanya bisa ditutup admin dengan alasan teks, tanpa penanda risiko, dan kolektor tak tahu.

**Langkah**:
1. `VerifikasiPenarikan` (daftar & aksi) dan `VerifikasiPenarikanOfflineAction`: filter berdasarkan `lokasi_pengambilan = 'rumah_kolektor'` dan status `approved`, **bukan** `jalur_pengajuan`. Kepemilikan tetap dicek lewat penugasan aktif kolektor-nasabah (`ValidatesKolektorNasabah`). Pertahankan throttle PIN dan guard `harus_ganti_pin`.
2. `ApprovalPenarikan::selesai`: `isOverrideRumah` = `lokasi_pengambilan === 'rumah_kolektor'` (semua jalur). Override tetap wajib alasan minimal 10 karakter, dicatat dengan penanda `override_rumah = true`, dan **dibatasi** agar admin tidak bisa override bila kolektor aktif sudah punya penarikan itu di antriannya kurang dari 24 jam (opsional; bila rumit, cukup penanda + log).
3. Notifikasi kolektor: tambah badge hitungan "penarikan menunggu diantar" di dashboard kolektor dan nav (pola `PenarikanOfflineNavCardTest`). Untuk notifikasi in-app kolektor, **jangan** menyalahgunakan `log_notifikasi.nasabah_id`; cukup badge + daftar.
4. Saat admin approve penarikan rumah_kolektor, log `approve` memuat `kolektor_id` penanggung jawab.

**Tes**: penarikan online rumah_kolektor muncul di daftar kolektor yang benar dan tidak di kolektor lain; verifikasi PIN sukses mengubah status `selesai`; override admin tercatat dengan penanda.

### P3.2 Aturan penarikan, presisi uang
**File**: `AjukanPenarikanAction`, `Nasabah/AjukanPenarikan`, `Kolektor/PenarikanOffline`, `Admin/Pengaturan`.

**Langkah**:
1. D7: `AdminSetting` kunci `penarikan_minimal` (default 10000) + UI di `Admin\Pengaturan`. Aturan: `nominal >= minimal` **atau** `nominal == saldo tersedia` (tarik habis).
2. Dropdown produk penarikan menyertakan produk **nonaktif yang masih punya saldo > 0** (label "(nonaktif)").
3. Presisi uang: ubah `AjukanPenarikanAction` menghitung dengan `bcmath` (`bcmul`, `bcdiv`, `bcsub`, skala 2, pembulatan half-up) dengan jaminan `nominal_diterima + komisi == nominal_diminta`. Terima `float|string` namun normalisasi ke string. Nominal wajib `> 0` dan maksimal 2 desimal (validasi di ketiga pemanggil). Lihat apakah `ext-bcmath` ada di `composer.json` `require`; bila belum, tambahkan `ext-bcmath` (ini bukan paket baru; konfirmasi di plan-log).
4. Tes dataset: komisi 2,5%, 3%, nominal ganjil → penjumlahan selalu persis; nominal ≤ 0 ditolak; tarik habis di bawah minimal diizinkan.

### P3.3 Rekonsiliasi: tolak/batal pengajuan, akumulasi selisih
**File**: `Kolektor/SetorKantor`, `Admin/RekonsiliasiKas`, migration baru.

**Langkah**:
1. Migration: enum `setoran_kolektor_kantor.status` tambah `dibatalkan` (ikuti pola migration enum `add_status_pending_...` / `add_antri_status_...` yang sudah ada; MySQL).
2. Kolektor dapat **membatalkan** pengajuan miliknya yang `pending`; admin dapat **menolak** dengan alasan. Efek: status `dibatalkan`, `TransaksiSetoran::where('setoran_kolektor_id', $id)->update(['setoran_kolektor_id' => null])`, log + notifikasi. Dalam transaksi + lock, guard hanya dari `pending`.
3. Ringkasan akumulasi selisih per kolektor (SUM selisih status `kurang`/`lebih`, dan saldo berjalan) — dipakai di P3.4.
4. `SetorKantor::$catatan`: validasi `nullable|string|max:500`.
5. D4: **jangan** mengubah rumus `total_seharusnya`. Tambahkan komentar `// TODO(D4)` dan catat di plan-log bahwa penarikan tunai tidak masuk rekonsiliasi.

**Tes**: batal/tolak melepas keterkaitan, tidak bisa dua kali, tidak bisa untuk status selain pending.

### P3.4 Dashboard admin "Kas di tangan kolektor"
**Langkah**:
1. Komponen `Admin\KasKolektor` (`make:livewire`) + route `admin.kas-kolektor.index` + item sidebar. Tabel per kolektor aktif: total belum disetor (rumus sama dengan `User::hasUnsettledCash`/scope `belumDisetor`), jumlah transaksi, umur transaksi tertua (hari), pengajuan pending, akumulasi selisih (P3.3), tautan ke `RekonsiliasiKas`.
2. `AdminSetting`: `batas_kas_kolektor` (rupiah) dan `batas_hari_kas` (hari); baris melewati batas diberi penanda. Nilai 0 = nonaktif. UI di `Admin\Pengaturan`.
3. Kartu ringkas di dashboard admin: total kas di tangan seluruh kolektor + jumlah kolektor melewati batas.
4. Satu query teragregasi (GROUP BY `input_by`), bukan N+1.

**Tes**: angka sesuai fixture, batas menandai baris, kolektor lain tidak bocor, hanya admin yang bisa akses.

### P3.5 Admin dapat mencatat setoran (FR-5)
**Langkah**:
1. Refactor: ekstrak inti `InputSetoran::submit` ke `app/Actions/Setoran/CatatSetoranAction` (idempotency key, lock saldo, `kepesertaan_id`, log, notifikasi). `InputSetoran` memanggil Action; semua tes existing harus tetap hijau.
2. Komponen `Admin\InputSetoran` + route `admin.setoran.create` + sidebar. Admin memilih nasabah mana pun yang `aktif`. Setoran admin: `input_by = admin`, `sudah_disetor_ke_kantor = true` (uang langsung diterima kantor, tidak masuk kas kolektor), `sumber_input = real_time`.
3. Pastikan `belumDisetor` scope dan rekonsiliasi tidak ikut menghitung setoran admin (cek `input_by` role).

**Tes**: saldo bertambah, tidak muncul di kas kolektor manapun, idempotensi, log `input_by` admin, notifikasi ke nasabah.

---

## FASE 4 — Keamanan & operasional

### P4.1 PIN awal tidak terlihat kolektor (D3)
**File**: `Kolektor/DaftarNasabah`, `Admin/RegistrasiNasabah`, `Admin/ManajemenNasabah::approve/verifikasi`, `ResetPinOlehAdminAction`, job baru.

**Langkah**:
1. Pendaftaran oleh kolektor: simpan PIN acak yang tidak ditampilkan (hash saja). Saat **admin memverifikasi** (menyetujui) nasabah, jalankan alur seperti reset PIN: buat PIN baru (hindari PIN lemah `Pin::lemah`), `harus_ganti_pin = true`, dan kirim lewat WA ke nasabah.
2. Job `KirimPinAwalWhatsApp` mengimplementasikan `Illuminate\Contracts\Queue\ShouldBeEncrypted` (payload di tabel `jobs` terenkripsi). `log_notifikasi` hanya menyimpan pesan tersamar ("PIN awal dikirim"), **tidak** menyimpan PIN.
3. Fallback: jika WA tidak terkonfigurasi atau `notifikasi_wa_aktif` false, PIN ditampilkan **hanya ke admin** lewat flash sekali pakai (kolektor tidak pernah melihatnya). Registrasi langsung oleh admin (`RegistrasiNasabah`) mengikuti pola yang sama.
4. PIN acak untuk pendaftaran juga harus lolos `Pin::lemah`.

**Tes**: kolektor tidak menerima PIN di respons/flash; job terenkripsi; log tidak memuat PIN; fallback admin berfungsi; `harus_ganti_pin` aktif.

### P4.2 Hapus nasabah tidak menghapus jejak audit (D5)
**File**: `Admin/ManajemenNasabah::delete`, migration baru, `ActivityLogger`.

**Langkah**:
1. Migration: `log_aktivitas.user_id` jadi nullable + FK `nullOnDelete`. Periksa FK lain yang mendorong penghapusan manual log (`log_notifikasi`, `komplain`, dll.) dan tangani sebaiknya dengan cascade yang sesuai.
2. `delete()`: **hapus** baris `DB::table('log_aktivitas')->where('user_id', ...)->delete()`. Sebelum menghapus user, catat `ActivityLogger::log('hapus_nasabah', ...)` dengan `user_id = admin` dan snapshot `{nama, no_hp_masked, id_lama}` di `detail`.
3. Pertahankan guard riwayat keuangan (`punyaRiwayatKeuangan`) dan saldo ≠ 0.

**Tes**: log lama tetap ada dengan `user_id` null; log `hapus_nasabah` tercatat; guard keuangan tetap menolak.

### P4.3 CI, environment, guard produksi
**Langkah**:
1. `.github/workflows/tests.yml`: tambah `services: mysql` (image `mysql:8.0`, health-check), env `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_DATABASE=tabungan_digital_test`, `DB_USERNAME=root`, `DB_PASSWORD`; buat database sebelum tes. Pastikan nilai sama dengan `phpunit.xml`.
2. Buat baseline PHPStan: `vendor/bin/phpstan analyse --generate-baseline phpstan-baseline.neon --memory-limit=1G`, include di `phpstan.neon`. Tujuannya CI hijau dan error baru terblokir. Tambahkan task rutin "kurangi baseline" di plan-log (tidak wajib sekarang).
3. `.env.example`: default `DB_CONNECTION=mysql` (blok DB MySQL aktif; sqlite dikomentari dengan peringatan "tidak mendukung lockForUpdate").
4. `AppServiceProvider::boot`: bila `app()->isProduction()` dan driver DB default `sqlite` → `throw new \RuntimeException('Produksi wajib MySQL: SQLite mengabaikan lockForUpdate.')`.
5. Pastikan `composer ci:check` hijau di mesin bersih; catat langkahnya di README.

### P4.4 Transaksi & null-safety
- Bungkus pembuatan user + role + profil di `Admin/RegistrasiNasabah::submit` dan `Kolektor/DaftarNasabah::submit` dalam `DB::transaction` (gagal sebagian = rollback penuh).
- Audit `find($id)->...` tanpa null-check di `app/Livewire/Admin/*` (`grep -rn "::find(" app/Livewire` lalu cek per kasus); ganti dengan pola aman. Tambah tes untuk id tidak valid pada komponen yang diubah.
- `KelolaKolektor::bukaKunci`: jangan membuka kunci kolektor yang statusnya hasil handover (cek `log_handover_kolektor`); tampilkan pesan jelas.
- Pesan login: bila akun terkunci karena percobaan gagal, tampilkan "Akun terkunci sementara hingga HH:MM" (tidak menambah celah enumerasi: tampilkan hanya setelah no_hp **dan** percobaan melewati batas untuk akun itu). Bila ini dinilai menambah risiko, catat keputusan "tidak dikerjakan" di plan-log.

---

## FASE 5 — Database & performa

### P5.1 Indeks, FK, constraint
Satu migration (`php artisan make:migration harden_core_tables`):
- Index `transaksi_setoran(tanggal_transaksi)` dan komposit `(input_by, status, sudah_disetor_ke_kantor)`.
- `transaksi_setoran.setoran_kolektor_id`: **sebelum** menambah FK, jalankan query orphan (baris dengan id yang tidak ada di `setoran_kolektor_kantor`) dan perbaiki/lapor; lalu FK `nullOnDelete` + index.
- CHECK (hanya bila driver MySQL ≥ 8.0.16; bungkus kondisi driver): `saldo_produk.saldo >= 0`, `transaksi_setoran.nominal > 0`, `transaksi_penarikan.nominal_diminta > 0`. Jalankan seluruh suite untuk memastikan tidak ada fixture yang melanggar.
- Pastikan migration `down()` ada dan aman.

### P5.2 Dashboard admin: satu query tren
Ganti loop 30 query di `DashboardController` (grafik tren harian) dengan satu `groupBy('tanggal_transaksi')`, isi hari kosong di PHP. Tes: hasil identik dengan implementasi lama (bandingkan di fixture), jumlah query ≤ 3 untuk bagian tren (gunakan `DB::enableQueryLog` dalam tes).

---

## FASE 6 — Fitur baru (urut prioritas)

### P6.1 Serah terima paket (FR-20/21)
Kolom sudah ada di `kepesertaan_paket` (`metode_pengambilan`, `status_serah_terima` enum `belum|sudah_diterima`, `diterima_oleh` string, `tanggal_serah_terima`, `bukti_foto_url`). **Tidak perlu migration kolom baru** kecuali diperlukan.
1. Nasabah (`Nasabah/ProgresPaket`): bila `tanggal_boleh_cair` tercapai dan tunggakan 0, nasabah memilih `metode_pengambilan` (`ambil_sendiri` / `diantar_kolektor`). Simpan dengan guard.
2. Komponen `Admin\SerahTerimaPaket` (daftar kepesertaan siap serah terima, filter status). Aksi "Konfirmasi serah terima": `diterima_oleh` (nama penerima), `tanggal_serah_terima`, foto bukti (upload ke disk **private**, dilayani lewat controller berotorisasi mengikuti pola `AbsensiFotoController`; validasi `image|max:2048`). Kolektor penanggung jawab dapat melakukan hal sama bila `diantar_kolektor`.
3. Log `serah_terima_paket` + notifikasi nasabah. Guard idempoten (tidak bisa dikonfirmasi dua kali).
4. Kepesertaan yang sudah `sudah_diterima` keluar dari perhitungan tunggakan dan daftar aktif.

### P6.2 Laporan lengkap (FR-32)
`Admin/Laporan` + view:
- Tab/seksi **Per Kolektor** (total setoran, jumlah transaksi, selisih rekon).
- **Per Paket**: jumlah peserta aktif, total terkumpul, total tunggakan.
- **Kebutuhan Barang**: untuk tiap produk paket, hitung `jumlah peserta aktif × isi_paket`. Karena `jumlah` item berupa teks bebas, parse angka di awal dengan regex; bila tidak terparse, tampilkan "N peserta × <teks asli>". Tidak mengubah skema `isi_paket`.
- Ekspor CSV untuk tiap seksi (pola `streamDownload` existing, dengan proteksi CSV-injection yang sudah ada) dan halaman cetak (`window.print`, CSS `@media print`). PDF/XLSX: `[BUTUH APPROVAL]` (D8), jangan dikerjakan.
- Hindari memuat seluruh baris ke memori: pakai agregasi SQL dan `cursor()`/`chunk` untuk ekspor.

### P6.3 Jadwal kunjungan otomatis
1. Migration: `kolektor_nasabah.hari_kunjungan` JSON nullable (array angka 1–7); unique index pada `jadwal_kunjungan(kolektor_id, nasabah_id, tanggal_jadwal)` — **cek duplikat dulu** dan bersihkan lewat migration data.
2. UI kolektor di `Kolektor/NasabahBinaan`/`JadwalKunjungan`: atur hari kunjungan per nasabah.
3. Command `jadwal:generate` (jalan 00:30 via scheduler) membuat baris `jadwal_kunjungan` status `belum` untuk hari itu, idempoten (`updateOrCreate`/`insertOrIgnore`).
4. `DashboardController` dan `JadwalKunjungan::updateStatus` memakai baris yang sudah dibuat; `kunjungan_total` = jumlah jadwal hari itu.

### P6.4 Nasabah: batalkan pengajuan, kedaluwarsa, struk
1. Migration enum `transaksi_penarikan.status` tambah `dibatalkan` dan `kedaluwarsa` (pola migration enum yang ada).
2. `Nasabah/RiwayatPenarikan`: tombol "Batalkan" untuk status `pending` milik sendiri (lock + guard status + log + `wire:confirm`).
3. Command `penarikan:kedaluwarsakan` (scheduler harian) menandai `pending` lebih tua dari `AdminSetting::penarikan_kedaluwarsa_hari` (default 7) menjadi `kedaluwarsa` + notifikasi. Penarikan `pending` tidak mengurangi saldo, jadi tidak ada refund.
4. Struk setoran: route `struk.setoran` (otorisasi: nasabah pemilik, kolektor penanggung jawab, admin), halaman HTML ramah cetak (`window.print`), memuat nomor transaksi, tanggal, nominal, kolektor, status koreksi bila ada.

### P6.5 PWA dasar
- `public/manifest.webmanifest` (nama, `display: standalone`, `start_url`, tema), ikon 192/512 px (turunkan dari ikon yang ada), tautan di `resources/views/partials/head.blade.php`.
- Service worker **hanya** meng-cache aset statis (`/build/*`, ikon). **Jangan** meng-cache halaman terautentikasi atau respons Livewire.
- Mode offline dan antrean setoran offline **di luar lingkup** (butuh desain terpisah; `idempotency_key` yang sudah ada menjadi fondasinya). Catat sebagai backlog.

### P6.6 Persetujuan ganda penarikan besar (D9, nonaktif default)
- `AdminSetting::penarikan_batas_dua_approver` (0 = mati). Bila > 0 dan `nominal_diminta` melebihi batas: butuh dua admin berbeda (`disetujui_oleh` dan `disetujui_oleh_2`, migration kolom nullable). Admin yang approve tidak boleh sama dengan yang menyetujui kedua, dan tidak boleh sama dengan yang menandai `selesai`.
- Jika hanya ada satu admin aktif, fitur otomatis tidak berlaku (tampilkan peringatan di Pengaturan).

### P6.7 Izin kolektor (menggantikan placeholder)
- Tabel `izin_kolektor` (`kolektor_id`, `tanggal_mulai`, `tanggal_selesai`, `alasan`, `status` `pending|disetujui|ditolak`, `diproses_oleh`, `catatan_admin`).
- `Kolektor/AjukanIzin` (ganti view `pages/kolektor/izin.blade.php`) + persetujuan admin di `Admin/MonitoringAbsensi` (atau halaman terpisah). Izin disetujui dihitung sebagai "izin" (bukan "tidak hadir") di monitoring absensi. Tampilkan kembali tautan yang disembunyikan di P1.3.

### P6.8 Backlog (jangan dikerjakan, hanya dicatat di plan-log)
Tutup rekening; renewal paket; backup terjadwal (butuh approval paket atau command `mysqldump` sendiri); PDF/XLSX; mode offline kolektor; 2FA admin.

---

## FASE 7 — Penutup

**P7.1 Regresi akhir**
- `php artisan test --compact` (semua hijau), `vendor/bin/pint --test`, PHPStan tanpa error baru (relatif baseline P4.3), `composer ci:check` hijau.
- Smoke test manual di browser (catat hasil): 5 komponen P1.1; alur komplain penuh; reset PIN dari 3 halaman; penarikan online rumah_kolektor end-to-end; scheduler lewat `php artisan schedule:test`; input setoran admin; serah terima paket.

**P7.2 Dokumentasi**
- Perbarui `docs/plan-log.md` (tabel status semua task + ASUMSI D#), `README.md` (deploy, scheduler, worker, MySQL), `prd.md` (centang FR yang kini terpenuhi: FR-5, FR-19, FR-20/21, FR-31, FR-32, FR-23).
- Tulis daftar sisa risiko: D4 (kas keluar penarikan), mode offline, 2FA admin.

---

## Ringkasan urutan & dependensi

| Fase | Task | Bergantung pada |
|------|------|-----------------|
| 0 | P0.1 | — |
| 1 | P1.1, P1.2, P1.3 | P0.1 |
| 2 | P2.1 → P2.2 → P2.3 → P2.4 | P1.2 |
| 3 | P3.1, P3.2, P3.3 → P3.4, P3.5 | P2.1 (untuk P3.5), P3.3 (untuk P3.4) |
| 4 | P4.1, P4.2, P4.3, P4.4 | independen (P4.3 sebaiknya lebih awal agar CI hijau) |
| 5 | P5.1, P5.2 | P3.x selesai (hindari konflik skema) |
| 6 | P6.1–P6.7 | P2.x, P3.x |
| 7 | P7.1, P7.2 | semua |

**Saran**: kerjakan **P4.3 tepat setelah P1.1** supaya CI hijau sejak awal dan setiap commit berikutnya tervalidasi otomatis.