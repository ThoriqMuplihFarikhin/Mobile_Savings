<div x-data="{
    signaturePad: null,
    async mintaLokasi() {
        $wire.resetLokasiError();
        navigator.geolocation.getCurrentPosition(
            async (pos) => {
                await $wire.setLokasi(pos.coords.latitude, pos.coords.longitude);
                $nextTick(() => tampilkanPeta(pos.coords.latitude, pos.coords.longitude));
            },
            (err) => $wire.setLokasiError(err.message, err.code)
        );
    },
    kameraAktif: false,
    streamKamera: null,
    errorKamera: null,
    async bukaKamera() {
        this.errorKamera = null;
        try {
            this.streamKamera = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' },
                audio: false,
            });
            this.kameraAktif = true;
            this.$nextTick(() => {
                this.$refs.videoSelfie.srcObject = this.streamKamera;
            });
        } catch (e) {
            this.errorKamera = 'Tidak bisa mengakses kamera. Pastikan izin kamera diaktifkan.';
        }
    },
    ambilFoto() {
        const video = this.$refs.videoSelfie;
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const base64 = canvas.toDataURL('image/jpeg', 0.85);
        this.tutupKamera();
        $wire.setSelfieBase64(base64);
    },
    tutupKamera() {
        if (this.streamKamera) {
            this.streamKamera.getTracks().forEach((t) => t.stop());
            this.streamKamera = null;
        }
        this.kameraAktif = false;
    },
    kirimTandaTangan() {
        if (this.signaturePad && this.signaturePad.isEmpty()) {
            alert('Mohon isi tanda tangan terlebih dahulu');
            return false;
        }
        if (this.signaturePad) {
            $wire.setTandaTanganBase64(this.signaturePad.toDataURL());
        }
        return true;
    },
    initTtd() {
        const canvas = document.getElementById('canvas-ttd');
        if (!canvas) return;
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
        this.signaturePad = new SignaturePad(canvas);
    }
}" x-init="
    if (!@js($sudahAbsenHariIni)) { mintaLokasi(); $nextTick(() => initTtd()); }
" class="mx-auto max-w-2xl">
    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 px-4 py-3.5 text-sm text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 shadow-sm">
            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            </div>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-3 rounded-2xl bg-rose-50 dark:bg-rose-950/40 px-4 py-3.5 text-sm text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60 shadow-sm">
            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-rose-500/20 text-rose-600 dark:text-rose-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </div>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Lokasi Error Card --}}
    @if($lokasiError)
        <div class="mb-6 rounded-3xl bg-rose-50 dark:bg-rose-950/40 p-6 text-center border border-rose-200 dark:border-rose-800/60 shadow-sm">
            <div class="mb-3 flex justify-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-500/15 text-rose-600 dark:text-rose-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                </div>
            </div>
            <h3 class="text-base font-bold text-rose-900 dark:text-rose-200">
                {{ $lokasiDitolak ? 'Akses Lokasi Ditolak' : 'Gagal Mendapatkan Lokasi GPS' }}
            </h3>
            <p class="mt-1 text-xs text-rose-700/80 dark:text-rose-300/80 max-w-sm mx-auto">
                Absensi wajib menyalakan lokasi GPS agar admin dapat memverifikasi keberadaan Anda di lapangan.
            </p>
            <button type="button" x-on:click="mintaLokasi()"
                    class="mt-4 w-full rounded-2xl bg-rose-600 hover:bg-rose-700 py-3 text-sm font-semibold text-white transition shadow-sm active:scale-95">
                Coba Lagi
            </button>
            @if($lokasiDitolak)
                <div x-data="{ showPanduan: false }" class="mt-3">
                    <button type="button" x-on:click="showPanduan = !showPanduan"
                            class="text-xs font-semibold text-rose-600 underline dark:text-rose-400">
                        Cara mengaktifkan lokasi di HP
                    </button>
                    <div x-show="showPanduan" x-collapse class="mt-3 rounded-2xl bg-white/80 dark:bg-zinc-800/80 p-4 text-left border border-rose-100 dark:border-zinc-700">
                        <p class="mb-2 text-xs font-bold text-rose-700 dark:text-rose-400">Izin lokasi diblokir oleh browser.</p>
                        <p class="mb-2 text-xs text-zinc-600 dark:text-zinc-400">Langkah untuk mengaktifkan:</p>

                        <p class="mt-2 text-xs font-bold text-zinc-900 dark:text-white">Chrome / Edge (Android):</p>
                        <ol class="mb-2 list-inside list-decimal text-xs text-zinc-600 dark:text-zinc-400 space-y-0.5">
                            <li>Tap ikon gembok di sebelah kiri address bar</li>
                            <li>Cari "Lokasi" atau "Location", ubah ke "Izinkan"</li>
                            <li>Refresh/muat ulang halaman ini</li>
                        </ol>

                        <p class="mt-2 text-xs font-bold text-zinc-900 dark:text-white">Safari (iPhone):</p>
                        <ol class="list-inside list-decimal text-xs text-zinc-600 dark:text-zinc-400 space-y-0.5">
                            <li>Buka Pengaturan HP &gt; Safari &gt; Lokasi</li>
                            <li>Pilih "Izinkan"</li>
                            <li>Kembali ke browser dan refresh halaman</li>
                        </ol>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Absensi Harian</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Catat kehadiran kerja Anda setiap hari.</p>
    </div>

    {{-- Kartu Waktu Hero --}}
    <div class="mb-6 relative overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 p-6 text-center shadow-xl dark:from-zinc-950 dark:to-zinc-900 border border-zinc-800">
        <div class="absolute -left-10 -bottom-10 h-40 w-40 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>

        @if($sudahAbsenHariIni && $sudahAbsenKeluarHariIni)
            {{-- Kondisi 3: Sudah masuk & keluar --}}
            <div class="relative flex flex-col items-center">
                <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500/20 text-emerald-400 ring-4 ring-emerald-500/10 shadow-lg shadow-emerald-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Absensi Hari Ini Selesai</p>
                <p class="mt-1 text-lg font-bold text-white">Masuk {{ \Carbon\Carbon::parse($waktuAbsenHariIni)->setTimezone('Asia/Jakarta')->format('H:i') }} - Keluar {{ \Carbon\Carbon::parse($waktuKeluarHariIni)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</p>
                <p class="mt-1 text-xs text-zinc-400">Semua data absensi hari ini sudah tercatat.</p>
            </div>

        @elseif($sudahAbsenHariIni && !$sudahAbsenKeluarHariIni)
            {{-- Kondisi 2: Sudah masuk, belum keluar --}}
            <div class="relative flex flex-col items-center">
                <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-500/20 text-amber-400 ring-4 ring-amber-500/10 shadow-lg shadow-amber-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-400">Sudah Absen Masuk</p>
                <p class="mt-1 text-2xl font-bold text-white">Pukul {{ \Carbon\Carbon::parse($waktuAbsenHariIni)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</p>
                <p class="mt-1 text-xs text-zinc-400 font-medium">Silakan lakukan Absen Keluar saat jam kerja selesai.</p>
            </div>

        @else
            {{-- Kondisi 1: Belum absen --}}
            <div class="relative">
                <p class="text-xs font-bold uppercase tracking-wider text-zinc-400">Waktu Operasional Sekarang</p>
                <p class="mt-2 text-4xl font-bold tracking-tight text-white font-mono" x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false, timeZone: 'Asia/Jakarta'}), 1000)">
                    <span x-text="time">--:--:--</span>
                </p>
                <p class="mt-2 text-xs text-zinc-400 font-medium">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
        @endif
    </div>

    {{-- Kondisi 1: Form Absen Masuk --}}
    @if(!$sudahAbsenHariIni)
        {{-- Peta Lokasi --}}
        @if($latitude && $longitude)
            <div class="mb-5 overflow-hidden rounded-3xl bg-white dark:bg-zinc-800 p-4 shadow-sm border border-zinc-100 dark:border-zinc-700/60">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                    </svg>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Lokasi GPS Terdeteksi</h3>
                </div>
                <div id="peta-lokasi" style="height:160px;border-radius:18px;" class="overflow-hidden border border-zinc-200 dark:border-zinc-700"></div>
            </div>
        @endif

        {{-- Foto Selfie --}}
        <div class="mb-5 rounded-3xl bg-white dark:bg-zinc-800 p-5 shadow-sm border border-zinc-100 dark:border-zinc-700/60">
            <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Foto Selfie Presensi</h3>

            {{-- Kamera aktif (live preview) --}}
            <div x-show="kameraAktif" class="relative overflow-hidden rounded-2xl bg-black" style="aspect-ratio: 3/4;">
                <video x-ref="videoSelfie" autoplay playsinline muted class="h-full w-full object-cover [transform:scaleX(-1)]"></video>
                <button type="button" x-on:click="ambilFoto()"
                        class="absolute bottom-4 left-1/2 -translate-x-1/2 flex h-14 w-14 items-center justify-center rounded-full border-4 border-white bg-white/30 hover:scale-105 transition-transform">
                    <span class="h-11 w-11 rounded-full bg-white shadow-lg"></span>
                </button>
            </div>

            {{-- Belum ada foto & kamera belum dibuka --}}
            <button type="button" x-show="!kameraAktif && !$wire.selfieBase64" x-on:click="bukaKamera()"
                    class="flex w-full flex-col items-center justify-center rounded-2xl border-2 border-dashed border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50 p-8 transition hover:border-emerald-500 hover:bg-emerald-50/20 dark:hover:border-emerald-500">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-zinc-900 dark:text-white">Ambil Foto Selfie</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Tap untuk membuka kamera depan</p>
            </button>

            {{-- Preview hasil foto + tombol ulangi --}}
            <div x-show="!kameraAktif && $wire.selfieBase64" class="flex flex-col items-center">
                <img id="preview-selfie" :src="$wire.selfieBase64" alt="Preview Selfie"
                     class="mb-3 h-44 w-44 rounded-2xl object-cover [transform:scaleX(-1)] shadow-md border-2 border-emerald-500">
                <button type="button" x-on:click="bukaKamera()" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                    Foto Ulang
                </button>
            </div>

            {{-- Error kamera --}}
            <p x-show="errorKamera" x-text="errorKamera" class="mt-2 text-xs text-rose-600 dark:text-rose-400 font-medium"></p>
        </div>

        {{-- Tanda Tangan --}}
        <div class="mb-5 rounded-3xl bg-white dark:bg-zinc-800 p-5 shadow-sm border border-zinc-100 dark:border-zinc-700/60">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300">Tanda Tangan Digital</h3>
                <button type="button" x-on:click="if(signaturePad) signaturePad.clear()"
                        class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline">Hapus / Reset</button>
            </div>
            <canvas id="canvas-ttd"
                    style="width:100%;height:100px;background:white;"
                    class="rounded-2xl border-2 border-dashed border-zinc-200 dark:border-zinc-700 dark:bg-zinc-900"></canvas>
        </div>

        {{-- Tombol Absen Masuk --}}
        <button type="button"
                x-on:click="if (kirimTandaTangan()) $wire.absenMasuk()"
                @disabled(!$latitude || !$selfieBase64)
                class="mb-3 w-full rounded-2xl bg-emerald-600 dark:bg-emerald-500 py-4 text-base font-bold text-white transition hover:bg-emerald-700 dark:hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-50 shadow-lg shadow-emerald-500/20 active:scale-95">
            Simpan Absen Masuk
        </button>

        {{-- Status Syarat Checklist --}}
        <div class="mb-6 flex items-center justify-center gap-5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/60 p-3.5 text-xs font-medium text-zinc-600 dark:text-zinc-400 border border-zinc-100 dark:border-zinc-700/60">
            <span class="flex items-center gap-1.5">
                @if($latitude && $longitude)
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                @else
                    <span class="h-2 w-2 rounded-full bg-zinc-300 dark:bg-zinc-600"></span>
                @endif
                Lokasi GPS
            </span>
            <span class="flex items-center gap-1.5">
                @if($selfieBase64)
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                @else
                    <span class="h-2 w-2 rounded-full bg-zinc-300 dark:bg-zinc-600"></span>
                @endif
                Foto Selfie
            </span>
            <span class="flex items-center gap-1.5">
                @if($tandaTanganBase64)
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                @else
                    <span class="h-2 w-2 rounded-full bg-zinc-300 dark:bg-zinc-600"></span>
                @endif
                Tanda Tangan
            </span>
        </div>
    @endif

    {{-- Kondisi 2: Checkout --}}
    @if($sudahAbsenHariIni && !$sudahAbsenKeluarHariIni)
        <div class="mb-6 rounded-3xl bg-white dark:bg-zinc-800 p-6 shadow-sm border border-zinc-100 dark:border-zinc-700/60 text-center">
            <div class="mb-4 flex justify-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                </div>
            </div>
            <h3 class="text-base font-bold text-zinc-900 dark:text-white">Siap untuk Checkout?</h3>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Jam kerja hari ini akan dicatat sebagai waktu keluar Anda.</p>
            <button type="button"
                    x-on:click="$wire.absenKeluar()"
                    class="mt-5 w-full rounded-2xl bg-amber-600 dark:bg-amber-500 py-4 text-base font-bold text-white transition hover:bg-amber-700 dark:hover:bg-amber-600 shadow-lg shadow-amber-500/20 active:scale-95">
                Checkout / Absen Keluar
            </button>
        </div>
    @endif

    {{-- Riwayat Absensi Terakhir --}}
    <div class="space-y-3">
        <h2 class="text-sm font-bold text-zinc-900 dark:text-white">Riwayat Absensi 7 Hari Terakhir</h2>
        <div class="space-y-2.5">
            @forelse($riwayat as $item)
                <div class="flex items-center justify-between gap-3 rounded-2xl bg-white dark:bg-zinc-800 p-4 shadow-sm border border-zinc-100 dark:border-zinc-700/60">
                    <div class="flex items-center gap-3.5">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ $item->waktu_keluar ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400' }}">
                            @if($item->waktu_keluar)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-bold text-zinc-900 dark:text-white">{{ $item->tanggal->translatedFormat('d M Y') }}</p>
                            <p class="text-xs font-mono text-zinc-500 dark:text-zinc-400">
                                Masuk {{ \Carbon\Carbon::parse($item->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                                @if($item->waktu_keluar)
                                    - Keluar {{ \Carbon\Carbon::parse($item->waktu_keluar)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                                @endif
                            </p>
                        </div>
                    </div>
                    @if($item->latitude && $item->longitude)
                        <a href="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1 rounded-xl bg-zinc-100 dark:bg-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-600 transition">
                            <span>GPS</span>
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                        </a>
                    @endif
                </div>
            @empty
                <div class="rounded-3xl bg-white dark:bg-zinc-800 p-8 text-center border border-zinc-100 dark:border-zinc-700/60 shadow-sm">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Belum ada riwayat absensi minggu ini.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Script Peta --}}
    <script>
        function tampilkanPeta(lat, lng) {
            const container = document.getElementById('peta-lokasi');
            if (!container) return;
            container.innerHTML = '';
            const map = L.map('peta-lokasi', { zoomControl: false, attributionControl: false }).setView([lat, lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            L.marker([lat, lng]).addTo(map);
            setTimeout(() => map.invalidateSize(), 100);
        }

        document.addEventListener('livewire:navigated', () => {
            const canvas = document.getElementById('canvas-ttd');
            if (canvas) {
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
            }
        });
    </script>
</div>
