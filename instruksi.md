# INSTRUKSI — Tambah Peta Lokasi, Selfie, dan Tanda Tangan di Halaman Absen

Halaman Absen (`app/Livewire/Kolektor/Absen.php` dan
`resources/views/livewire/kolektor/absen.blade.php`) yang sudah ada sebelumnya (dengan GPS
dan penanganan lokasi ditolak) sekarang ditambah 2 elemen baru: **preview peta** dari
koordinat yang didapat, **foto selfie**, dan **tanda tangan digital**. Tombol "Absen Masuk"
BARU AKTIF jika ketiganya (lokasi, selfie, tanda tangan) sudah lengkap.

## 1. Migration — Tambah Kolom Baru

Tambahkan ke tabel `absensi_kolektor` (lewat migration baru):
```php
$table->string('foto_selfie_path')->nullable();
$table->text('tanda_tangan_base64')->nullable(); // simpan sebagai base64 PNG, bukan file
```

## 2. Peta Lokasi — Pakai Leaflet.js + OpenStreetMap (GRATIS, Tanpa API Key)

JANGAN pakai Google Maps (butuh API key + billing account, ribet buat setup awal). Pakai
Leaflet.js dengan tile dari OpenStreetMap, ini gratis sepenuhnya dan tidak butuh pendaftaran.

Tambahkan via CDN di `resources/views/layouts/mobile.blade.php` bagian `<head>`:
```html
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
```

Di blade view Absen, setelah koordinat berhasil didapat, render div peta:
```blade
<div id="peta-lokasi" style="height:150px;border-radius:16px;"></div>
```

Tambahkan script (dipanggil setelah `setLokasi` sukses, bisa lewat event listener Livewire
`$wire.on('lokasi-didapat', ...)` yang di-dispatch dari method `setLokasi` di komponen PHP,
atau langsung di dalam callback geolocation Alpine yang sudah ada):

```js
function tampilkanPeta(lat, lng) {
    const map = L.map('peta-lokasi', { zoomControl: false, attributionControl: false }).setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    L.marker([lat, lng]).addTo(map);
}
```

Panggil `tampilkanPeta(lat, lng)` di callback sukses geolocation yang sudah ada sebelumnya
(sebelum atau sesudah memanggil `$wire.setLokasi`).

## 3. Foto Selfie — Pakai Kamera HP Langsung

Gunakan input file dengan atribut `capture="user"` supaya di HP langsung membuka kamera depan
(bukan galeri):

```blade
<input type="file" id="input-selfie" accept="image/*" capture="user" class="hidden"
       x-on:change="previewSelfie($event)">
```

Tambahkan Alpine function untuk preview + kirim ke Livewire sebagai base64 dulu (lebih
sederhana daripada `WithFileUploads` untuk kasus kamera langsung):

```js
previewSelfie(event) {
    const file = event.target.files[0];
    const reader = new FileReader();
    reader.onload = (e) => {
        document.getElementById('preview-selfie').src = e.target.result;
        document.getElementById('preview-selfie').classList.remove('hidden');
        $wire.setSelfieBase64(e.target.result);
    };
    reader.readAsDataURL(file);
}
```

Di komponen PHP, tambahkan:
```php
public $selfieBase64 = null;

public function setSelfieBase64($base64)
{
    $this->selfieBase64 = $base64;
}
```

Saat submit (`absenMasuk()`), decode base64 ini dan simpan sebagai file ke storage:
```php
if ($this->selfieBase64) {
    $data = str_replace('data:image/jpeg;base64,', '', $this->selfieBase64);
    $data = str_replace('data:image/png;base64,', '', $data);
    $data = base64_decode($data);
    $filename = 'selfie/' . Auth::id() . '_' . now()->timestamp . '.jpg';
    \Storage::disk('public')->put($filename, $data);
    $fotoSelfiePath = $filename;
}
```

## 4. Tanda Tangan — Pakai Library `signature_pad` (Ringan, via CDN)

Tambahkan via CDN di layout atau langsung di view Absen:
```html
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4/dist/signature_pad.umd.min.js"></script>
```

Di blade view:
```blade
<canvas id="canvas-ttd" style="width:100%;height:90px;background:white;border:1.5px dashed #ebebeb;border-radius:12px;"></canvas>
<button type="button" x-on:click="signaturePad.clear()" class="text-xs text-[#0761d1]">Hapus</button>
```

Script inisialisasi (jalankan saat halaman dimuat):
```js
let signaturePad;
document.addEventListener('livewire:navigated', initTtd);
function initTtd() {
    const canvas = document.getElementById('canvas-ttd');
    if (!canvas) return;
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;
    signaturePad = new SignaturePad(canvas);
}
```

Saat submit, ambil data tanda tangan sebagai base64 dan kirim ke Livewire:
```js
function kirimTandaTangan() {
    if (signaturePad.isEmpty()) {
        alert('Mohon isi tanda tangan terlebih dahulu');
        return false;
    }
    $wire.setTandaTanganBase64(signaturePad.toDataURL());
    return true;
}
```

Panggil `kirimTandaTangan()` sebelum memanggil `$wire.absenMasuk()` pada tombol submit (bisa
pakai `x-on:click="if (kirimTandaTangan()) $wire.absenMasuk()"`).

Simpan `tanda_tangan_base64` LANGSUNG sebagai teks base64 di kolom database (tidak perlu
didekode jadi file gambar terpisah, supaya lebih sederhana — nanti saat ditampilkan ke admin,
cukup pasang langsung sebagai `src` dari tag `<img>` karena base64 PNG valid untuk itu).

## 5. Validasi — Tombol "Absen Masuk" Disabled Sampai 3 Syarat Terpenuhi

Update kondisi disabled pada tombol utama:
```blade
<button type="button"
    x-on:click="if (kirimTandaTangan()) $wire.absenMasuk()"
    @disabled(!$latitude || !$selfieBase64)
    {{-- tambahan pengecekan tanda tangan dilakukan di sisi JS lewat kirimTandaTangan() --}}
    class="...">
    Absen Masuk
</button>
```

Di method PHP `absenMasuk()`, tambahkan validasi ulang di server (jangan cuma percaya
validasi client-side):
```php
if (! $this->latitude || ! $this->selfieBase64 || ! $this->tandaTanganBase64) {
    session()->flash('error', 'Lengkapi lokasi, foto selfie, dan tanda tangan terlebih dahulu.');
    return;
}
```

## 6. Update Halaman Admin — Tampilkan Bukti Absen

Di `MonitoringAbsensi.php` (admin) yang sudah dibuat sebelumnya, tambahkan kemampuan klik
satu baris kolektor untuk melihat detail: foto selfie (tampilkan sebagai gambar dari
`Storage::url($fotoSelfiePath)`), tanda tangan (tampilkan `<img src="{{ $tandaTanganBase64 }}">`
langsung karena sudah base64), dan link ke peta lokasi.

## Setelah Selesai

1. `php artisan migrate`
2. `php artisan storage:link` (jika belum)
3. `vendor\bin\pint --format agent` pada semua file yang diubah
4. Test manual lengkap: buka halaman Absen, pastikan peta muncul dengan pin di lokasi benar,
   ambil selfie dari kamera HP/webcam, gambar tanda tangan di kanvas, baru tombol Absen Masuk
   aktif dan berhasil tersimpan — cek juga tampilannya di halaman Monitoring Absensi admin.