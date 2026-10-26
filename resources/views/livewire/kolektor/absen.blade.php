<div x-data="{
    signaturePad: null,
    async mintaLokasi() {
        $wire.resetLokasiError();
        navigator.geolocation.getCurrentPosition(
            async (pos) => {
                await $wire.setLokasi(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy ?? null);
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
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-hairline bg-primary-soft px-4 py-3 text-sm font-medium text-on-primary-soft">
            <flux:icon.check-circle class="size-5 shrink-0" aria-hidden="true" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm font-medium text-danger-deep dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.x-circle class="size-5 shrink-0" aria-hidden="true" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Lokasi Error Card --}}
    @if($lokasiError)
        <div class="mb-6 rounded-2xl border border-danger-soft bg-danger-soft p-6 text-center dark:border-dark-danger-soft dark:bg-dark-danger-soft">
            <div class="mb-3 flex justify-center">
                <div class="flex size-12 items-center justify-center rounded-full bg-inset" aria-hidden="true">
                    <flux:icon.map-pin class="size-6 text-danger-deep dark:text-dark-danger-text" />
                </div>
            </div>
            <h2 class="text-base font-semibold text-danger-deep dark:text-dark-danger-text">
                {{ $lokasiDitolak ? 'Akses lokasi ditolak' : 'Gagal mendapatkan lokasi GPS' }}
            </h2>
            <p class="mx-auto mt-1 max-w-sm text-sm text-danger-deep dark:text-dark-danger-text">
                Absensi wajib menyalakan lokasi GPS agar admin dapat memverifikasi keberadaan Anda di lapangan.
            </p>
            <button type="button" x-on:click="mintaLokasi()"
                    class="mt-4 flex h-12 w-full items-center justify-center rounded-full bg-white text-sm font-medium text-danger-deep transition hover:bg-inset active:scale-[0.98]">
                Coba lagi
            </button>
            @if($lokasiDitolak)
                <div x-data="{ showPanduan: false }" class="mt-3">
                    <button type="button" x-on:click="showPanduan = !showPanduan"
                            class="text-sm font-medium text-danger-deep underline underline-offset-4 dark:text-dark-danger-text">
                        Cara mengaktifkan lokasi di HP
                    </button>
                    <div x-show="showPanduan" x-collapse class="mt-3 rounded-xl bg-white p-4 text-left dark:bg-surface">
                        <p class="mb-2 text-sm font-semibold text-danger-deep dark:text-dark-danger-text">Izin lokasi diblokir oleh browser.</p>
                        <p class="mb-2 text-sm text-body">Langkah untuk mengaktifkan:</p>

                        <p class="mt-2 text-sm font-semibold text-ink">Chrome / Edge (Android):</p>
                        <ol class="mb-2 list-inside list-decimal space-y-1 text-sm text-body">
                            <li>Tap ikon gembok di sebelah kiri address bar</li>
                            <li>Cari "Lokasi" atau "Location", ubah ke "Izinkan"</li>
                            <li>Refresh/muat ulang halaman ini</li>
                        </ol>

                        <p class="mt-2 text-sm font-semibold text-ink">Safari (iPhone):</p>
                        <ol class="list-inside list-decimal space-y-1 text-sm text-body">
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
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Absensi harian</h1>
        <p class="mt-1 text-sm text-mute">Catat kehadiran kerja Anda setiap hari.</p>
    </div>

    {{-- Kartu Waktu --}}
    <div class="relative mb-6 rounded-2xl p-6 text-center {{ $sudahAbsenHariIni && !$sudahAbsenKeluarHariIni ? 'border border-warning-soft bg-warning-soft text-warning-deep' : ($sudahAbsenHariIni ? 'border border-hairline bg-primary-soft text-on-primary-soft' : 'border border-hairline bg-inset text-ink') }}">
        @if($sudahAbsenHariIni && $sudahAbsenKeluarHariIni)
            {{-- Kondisi 3: Sudah masuk & keluar --}}
            <div class="flex flex-col items-center">
                <div class="mb-3 flex size-14 items-center justify-center rounded-full bg-inset" aria-hidden="true">
                    <flux:icon.check-circle class="size-8" />
                </div>
                <p class="text-sm font-medium">Absensi hari ini selesai</p>
                <p class="mt-1 text-lg font-semibold tabular-nums">Masuk {{ \Carbon\Carbon::parse($waktuAbsenHariIni)->setTimezone('Asia/Jakarta')->format('H:i') }} &ndash; Keluar {{ \Carbon\Carbon::parse($waktuKeluarHariIni)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</p>
                <p class="mt-1 text-sm opacity-80">Semua data absensi hari ini sudah tercatat.</p>
            </div>

        @elseif($sudahAbsenHariIni && !$sudahAbsenKeluarHariIni)
            {{-- Kondisi 2: Sudah masuk, belum keluar --}}
            <div class="flex flex-col items-center">
                <div class="mb-3 flex size-14 items-center justify-center rounded-full bg-inset" aria-hidden="true">
                    <flux:icon.clock class="size-8" />
                </div>
                <p class="text-sm font-medium">Sudah absen masuk</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums">Pukul {{ \Carbon\Carbon::parse($waktuAbsenHariIni)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</p>
                <p class="mt-1 text-sm opacity-80">Silakan lakukan absen keluar saat jam kerja selesai.</p>
            </div>

        @else
            {{-- Kondisi 1: Belum absen --}}
            <div>
                <p class="text-sm font-medium">Waktu operasional sekarang</p>
                <p class="mt-2 text-4xl font-semibold tabular-nums" x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false, timeZone: 'Asia/Jakarta'}), 1000)">
                    <span x-text="time">--:--:--</span>
                </p>
                <p class="mt-2 text-sm text-mute">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
        @endif
    </div>

    {{-- Kondisi 1: Form Absen Masuk --}}
    @if(!$sudahAbsenHariIni)
        {{-- Peta Lokasi --}}
        @if($latitude && $longitude)
            <div class="mb-5 rounded-2xl border border-hairline bg-white p-4 dark:bg-surface">
                <div class="mb-3 flex items-center gap-2">
                    <flux:icon.map-pin class="size-4 text-primary" aria-hidden="true" />
                    <h2 class="text-sm font-medium text-ink">Lokasi GPS terdeteksi</h2>
                </div>
                <div id="peta-lokasi" style="height:160px;" class="overflow-hidden rounded-xl border border-hairline"></div>
            </div>
        @endif

        {{-- Foto Selfie --}}
        <div class="mb-5 rounded-2xl border border-hairline bg-white p-5 dark:bg-surface">
            <h2 class="mb-3 text-sm font-medium text-ink">Foto selfie presensi</h2>

            {{-- Kamera aktif (live preview) --}}
            <div x-show="kameraAktif" class="relative overflow-hidden rounded-xl bg-black" style="aspect-ratio: 3/4;">
                <video x-ref="videoSelfie" autoplay playsinline muted class="h-full w-full object-cover [transform:scaleX(-1)]"></video>
                <button type="button" x-on:click="ambilFoto()" aria-label="Ambil foto"
                        class="absolute bottom-4 left-1/2 flex h-16 w-16 -translate-x-1/2 items-center justify-center rounded-full border-4 border-white bg-white/30 transition-transform hover:scale-105">
                    <span class="size-11 rounded-full bg-white"></span>
                </button>
            </div>

            {{-- Belum ada foto & kamera belum dibuka --}}
            <button type="button" x-show="!kameraAktif && !$wire.selfieBase64" x-on:click="bukaKamera()"
                    class="flex w-full flex-col items-center justify-center rounded-xl border-2 border-dashed border-hairline-strong bg-inset p-8 transition hover:border-primary">
                <div class="mb-3 flex size-12 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft" aria-hidden="true">
                    <flux:icon.camera class="size-6" />
                </div>
                <p class="text-sm font-semibold text-ink">Ambil foto selfie</p>
                <p class="mt-1 text-sm text-mute">Tap untuk membuka kamera depan</p>
            </button>

            {{-- Preview hasil foto --}}
            <div x-show="!kameraAktif && $wire.selfieBase64" class="flex flex-col items-center">
                <img id="preview-selfie" :src="$wire.selfieBase64" alt="Pratinjau selfie"
                     class="mb-3 h-44 w-44 rounded-xl border-2 border-hairline-strong object-cover [transform:scaleX(-1)]">
                <button type="button" x-on:click="bukaKamera()" class="inline-flex h-11 items-center rounded-full border border-hairline-strong bg-white px-5 text-sm font-medium text-ink transition hover:bg-inset dark:bg-surface">
                    Ulangi
                </button>
                <p class="mt-2 text-sm text-mute">Foto ini dipakai untuk presensi.</p>
            </div>

            {{-- Error kamera --}}
            <p x-show="errorKamera" x-text="errorKamera" class="mt-2 text-sm font-medium text-danger"></p>
        </div>

        {{-- Tanda Tangan --}}
        <div class="mb-5 rounded-2xl border border-hairline bg-white p-5 dark:bg-surface">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 class="text-sm font-medium text-ink">Tanda tangan digital</h2>
                <button type="button" x-on:click="if(signaturePad) signaturePad.clear()"
                        class="text-sm font-medium text-primary underline underline-offset-4 hover:opacity-80">Hapus</button>
            </div>
            <canvas id="canvas-ttd"
                    style="width:100%;height:176px;background:white;"
                    class="rounded-xl border-2 border-dashed border-hairline-strong"></canvas>
            <p class="mt-2 text-sm text-mute">Tanda tangan di dalam kotak.</p>
        </div>

        {{-- Tombol Absen Masuk --}}
        <button type="button"
                x-on:click="if (kirimTandaTangan()) $wire.absenMasuk()"
                @disabled(!$latitude || !$selfieBase64)
                class="mb-3 flex h-12 w-full items-center justify-center rounded-full bg-primary text-base font-medium text-on-primary transition hover:bg-primary-hover active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50">
            Simpan absen masuk
        </button>

        @if(!$latitude || !$selfieBase64 || !$tandaTanganBase64)
            <p class="mb-4 text-center text-sm text-mute">Lengkapi lokasi GPS, foto selfie, dan tanda tangan dulu.</p>
        @endif

        {{-- Status Syarat Checklist --}}
        <div class="mb-6 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 rounded-xl border border-hairline bg-inset p-3.5 text-sm font-medium text-body">
            <span class="flex items-center gap-1.5">
                @if($latitude && $longitude)
                    <flux:icon.check class="size-4 text-primary" aria-hidden="true" />
                @else
                    <span class="size-2 rounded-full bg-hairline-strong" aria-hidden="true"></span>
                @endif
                Lokasi GPS
            </span>
            <span class="flex items-center gap-1.5">
                @if($selfieBase64)
                    <flux:icon.check class="size-4 text-primary" aria-hidden="true" />
                @else
                    <span class="size-2 rounded-full bg-hairline-strong" aria-hidden="true"></span>
                @endif
                Foto selfie
            </span>
            <span class="flex items-center gap-1.5">
                @if($tandaTanganBase64)
                    <flux:icon.check class="size-4 text-primary" aria-hidden="true" />
                @else
                    <span class="size-2 rounded-full bg-hairline-strong" aria-hidden="true"></span>
                @endif
                Tanda tangan
            </span>
        </div>
    @endif

    {{-- Kondisi 2: Checkout --}}
    @if($sudahAbsenHariIni && !$sudahAbsenKeluarHariIni)
        <div class="mb-6 rounded-2xl border border-hairline bg-white p-6 text-center dark:bg-surface">
            <div class="mb-4 flex justify-center">
                <div class="flex size-14 items-center justify-center rounded-full bg-warning-soft text-warning-deep" aria-hidden="true">
                    <flux:icon.arrow-right-end-on-rectangle class="size-7" />
                </div>
            </div>
            <h2 class="text-base font-semibold text-ink">Siap absen keluar?</h2>
            <p class="mt-1 text-sm text-mute">Jam kerja hari ini akan dicatat sebagai waktu keluar Anda.</p>
            <button type="button"
                    x-on:click="$wire.absenKeluar()"
                    class="mt-5 flex h-12 w-full items-center justify-center rounded-full bg-primary text-base font-medium text-on-primary transition hover:bg-primary-hover active:scale-[0.98]">
                Absen keluar
            </button>
        </div>
    @endif

    {{-- Riwayat Absensi Terakhir --}}
    <div class="space-y-3">
        <h2 class="text-base font-semibold text-ink">Riwayat absensi 7 hari terakhir</h2>
        <div class="space-y-3">
            @forelse($riwayat as $item)
                <div class="flex min-h-16 items-center justify-between gap-3 rounded-2xl border border-hairline bg-white p-4 dark:bg-surface">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full {{ $item->waktu_keluar ? 'bg-primary-soft text-on-primary-soft' : 'bg-warning-soft text-warning-deep' }}" aria-hidden="true">
                            @if($item->waktu_keluar)
                                <flux:icon.check class="size-4" />
                            @else
                                <flux:icon.clock class="size-4" />
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink">{{ $item->tanggal->translatedFormat('d M Y') }}</p>
                            <p class="text-xs tabular-nums text-mute">
                                Masuk {{ \Carbon\Carbon::parse($item->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                                @if($item->waktu_keluar)
                                    &ndash; Keluar {{ \Carbon\Carbon::parse($item->waktu_keluar)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                                @endif
                            </p>
                        </div>
                    </div>
                    @if($item->latitude && $item->longitude)
                        <a href="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-inset px-3 py-1.5 text-xs font-medium text-body transition hover:bg-hairline">
                            <span>GPS</span>
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                        </a>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border border-hairline bg-white p-8 text-center dark:bg-surface">
                    <p class="text-sm text-mute">Belum ada riwayat absensi minggu ini.</p>
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
