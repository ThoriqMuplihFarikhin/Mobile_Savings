# SOP Lapangan — Operasional Nasabah Offline

Berlaku untuk kolektor lapangan dan admin pendamping. Dokumen ini meringkas cara
menangani **nasabah offline** — nasabah yang didaftarkan tanpa aplikasi
(`mode_akses = offline`), sehingga seluruh transaksi dilakukan lewat HP kolektor
dan dicatat pada buku Tabungan fisik.

---

## 1. Alur nasabah offline

### 1.1 Pendaftaran

1. Kolektor membuka **Daftar Nasabah**, mengisi nama, alamat, dan nomor HP bila
   ada (nasabah offline boleh tanpa nomor HP), lalu mengirim.
2. Akun berstatus **Pending Verifikasi**. Admin membuka **Verifikasi Nasabah**
   lalu menyetujui. Status berubah menjadi **Aktif**.
3. Admin memastikan **Mode Akses** akun tercatat *Offline* pada *Manajemen
   Nasabah*.

### 1.2 Setoran

1. Kolektor membuka **Input Setoran**, memilih nasabah, produk, nominal, dan
   tanggal.
2. Nasabah **menandatangani tanda tangan di layar** HP kolektor sebagai bukti
   penerimaan; kolektor menyimpan.
3. Setoran tunai yang belum sempat disetor di kantor dicatat lewat **Setor
   Kantor**, lalu direkonsiliasi admin di *Rekonsiliasi Kas*.

### 1.3 Penarikan

1. Nasabah mengajukan penarikan kepada kolektor; nominal dan produk dipilih di
   halaman **Penarikan Offline**.
2. Agar penarikan bisa **langsung selesai** di tempat, seluruh syarat berikut
   harus terpenuhi (lihat §2):
   - pengambilan di **rumah kolektor**, bukan kantor;
   - nominal tidak melebihi batas langsung maupun batas harian;
   - kolektor tidak sedang dibekukan;
   - **foto bukti wajib** diunggah;
   - kas di tangan mencukupi;
   - tidak melewati ambang persetujuan dua pengguna.
3. Nasabah **menandatangani tanda tangan di layar**; penarikan selesai dan
   otomatis masuk **antrean tinjauan admin**.
4. Bila salah satu syarat tidak terpenuhi, sistem menolak jalur langsung dan
   permintaan diproses lewat **pengambilan di kantor** (disetujui dan dibayar
   admin) atau masuk antrean biasa.

---

## 2. Aturan kas (D13)

- **Kas tidak boleh minus.** Selama pengaturan *Izin Kas Minus* berada pada
  nilai aman (`false`), penarikan yang membuat kas kolektor menjadi minus akan
  ditolak dengan pesan **kas di tangan tidak mencukupi**.
- Bila kas kurang dan nasabah tetap membutuhkan uang, pilih **pengambilan di
  kantor**: uang disiapkan dan dibayar admin, sehingga kas kolektor tidak
  berkurang.
- **Batas langsung `0` menonaktifkan penarikan langsung** (nilai aman bawaan
  sistem). Pada kondisi ini semua permintaan masuk antrean dan diselesaikan di
  kantor.
- **Batas harian** membatasi total penarikan langsung kolektor per hari
  (zona Asia/Jakarta). Melebihi batas → kembali ke jalur kantor.
- **Kolektor dibekukan** (`izin_penarikan_langsung` mati) tidak dapat menyelesaikan
  penarikan langsung apa pun sampai admin membuka kembali lewat *Kelola
  Kolektor*.
- Pengambilan di kantor **tidak pernah** dihitung sebagai penarikan langsung,
  karena dibayar oleh admin.

---

## 3. Mencetak dan menandatangani rekap berkala

1. Admin atau kolektor penanggung jawab membuka **Rekap Mutasi** nasabah
   terkait (hanya admin dan kolektor yang menangani nasabah tersebut yang boleh
   membuka).
2. Klik tombol **Cetak Rekap**. Sistem melakukan dua hal sekaligus:
   - mencatat waktu cetak pada log aktivitas `cetak_rekap_nasabah` dan
     memperbarui **Rekap Terakhir Dicetak** pada data nasabah;
   - langsung membuka dialog cetak peramban.
3. Cetak **satu lembar**. Pada bagian bawah lembar terdapat baris **Tanda Tangan
   Nasabah**, **Tanggal Cetak**, dan **Nama Kolektor**.
4. Nasabah **menandatangani tanda tangan** pada lembar tersebut di hadapan
   kolektor, lalu lembar ditempel pada buku Tabungan fisik.
5. Admin memantau kepatuhan lewat **Laporan → Nasabah**, kolom **Rekap Terakhir
   Dicetak**. Baris diurutkan dari yang **paling lama**; nilai *Belum pernah*
   berarti nasabah tersebut belum pernah menerima cetakan rekap.
6. Kolom tersebut murni **informasi** — tidak ada ambang angka dan tidak ada
   penalti otomatis; keputusan tindak lanjut ada pada admin.

---

## 4. Bila admin menandai "bermasalah"

Admin membuka **Tinjauan Penarikan Offline**, memilih baris yang sudah
dibayarkan kolektor, lalu menekan **Bermasalah** (disertai catatan bila perlu).

Yang terjadi:

1. Hasil tinjauan tersimpan sebagai `bermasalah` beserta catatan, nama admin
   peninjau, dan waktu peninjauan. **Peninjauan bersifat final** — admin lain
   tidak dapat meninjau ulang baris yang sama.
2. Seluruh proses tercatat pada log aktivitas `tinjau_penarikan_offline`.
3. Secara bawaan kotak **bekukan kolektor** ikut aktif, sehingga:
   - izin penarikan langsung kolektor dinonaktifkan;
   - log `bekukan_penarikan_langsung` ditulis;
   - kolektor menerima notifikasi in-app bahwa penarikannya dibekukan.
4. Kolektor tidak dapat lagi menyelesaikan penarikan langsung sampai admin
   membuka kembali izinnya di **Kelola Kolektor**.
5. Baris tampil sebagai **Bermasalah** pada laporan **Penarikan Offline**
   (rekap per kolektor dan detail per transaksi).

Tindak lanjut uang dan koreksi pencatatan dilakukan admin di luar tombol
tinjauan; jejak audit tetap utuh karena peninjauan tidak dapat diulang.

---

## 5. Batas kewenangan

| Aksi | Kolektor | Admin |
| --- | --- | --- |
| Mendaftarkan nasabah | ✔ | ✔ |
| Input setoran / setor kantor | ✔ | ✔ |
| Penarikan offline langsung (dengan bukti) | ✔ | — |
| Membuka halaman rekap & mencetak | hanya nasabah binaannya | ✔ |
| Tinjauan *sesuai* / *bermasalah* | — | ✔ |
| Membekukan / membuka izin penarikan langsung | — | ✔ |
---

## 6. Akses Sistem Kolektor di Lapangan

### 6.1 Alur Masuk (Login 2 Langkah)
1. Buka tautan portal kolektor (`/login/kolektor`).
2. **Langkah 1 (Nomor HP)**: Masukkan nomor HP aktif yang didaftarkan kantor (misal: `0812 3456 7890`), lalu tekan tombol **Lanjut** (hijau).
3. **Langkah 2 (PIN 6 Digit)**: Masukkan 6 digit PIN akun Anda menggunakan papan angka digital (keypad). Sistem akan secara otomatis memverifikasi PIN saat digit ke-6 dimasukkan, atau tekan tombol **Masuk**.
4. Bila lupa PIN atau akun terkunci akibat 5 kali salah input, hubungi admin kantor melalui tautan WhatsApp bantuan pada halaman login.

### 6.2 Mode Teks Besar (A+)
- Pada kondisi lapangan dengan pencahayaan terang matahari atau bagi petugas yang membutuhkan keterbacaan lebih tinggi, tekan tombol **A+** di sudut atas layar login atau di menu **Pengaturan**.
- Mode teks besar akan memperbesar ukuran teks aplikasi secara proporsional (+18%) dan tersimpan otomatis di perangkat kolektor.

