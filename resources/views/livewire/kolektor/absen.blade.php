<div x-data="{
    signaturePad: null,
    ttdTerisi: false,
    kameraAktif: false,
    streamKamera: null,
    errorKamera: null,
    detailItem: null,
    tampilPeta: false,
    async mintaLokasi() {
        $wire.resetLokasiError();
        if (!navigator.geolocation) {
            $wire.setLokasiError('Browser tidak mendukung geolokasi GPS.');
            return;
        }
        navigator.geolocation.getCurrentPosition(
            async (pos) => {
                await $wire.setLokasi(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy ?? null);
                this.$nextTick(() => {
                    if (typeof tampilkanPeta === 'function') {
                        tampilkanPeta(pos.coords.latitude, pos.coords.longitude);
                    }
                });
            },
            (err) => $wire.setLokasiError(err.message, err.code),
            { enableHighAccuracy: true, timeout: 10000 }
        );
    },
    async bukaKamera() {
        this.errorKamera = null;
        try {
            if (this.streamKamera) {
                this.streamKamera.getTracks().forEach((t) => t.stop());
                this.streamKamera = null;
            }
            this.streamKamera = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false,
            });
            this.kameraAktif = true;
            this.$nextTick(() => {
                if (this.$refs.videoSelfie) {
                    this.$refs.videoSelfie.srcObject = this.streamKamera;
                    this.$refs.videoSelfie.play().catch(() => {});
                }
            });
        } catch (e) {
            this.kameraAktif = false;
            this.errorKamera = 'Tidak bisa mengakses kamera. Pastikan izin kamera telah diaktifkan di browser/HP.';
        }
    },
    ambilFoto() {
        const video = this.$refs.videoSelfie;
        if (!video) return;
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
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
    pilihFile(e) {
        const file = e.target.files && e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (event) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const maxDim = 800;
                let w = img.width;
                let h = img.height;
                if (w > h && w > maxDim) {
                    h = Math.round((h * maxDim) / w);
                    w = maxDim;
                } else if (h > maxDim) {
                    w = Math.round((w * maxDim) / h);
                    h = maxDim;
                }
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, w, h);
                const base64 = canvas.toDataURL('image/jpeg', 0.85);
                this.tutupKamera();
                $wire.setSelfieBase64(base64);
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    },
    initTtd() {
        const canvas = document.getElementById('canvas-ttd');
        if (!canvas) return;
        canvas.width = canvas.offsetWidth || 300;
        canvas.height = canvas.offsetHeight || 120;
        if (typeof SignaturePad !== 'undefined') {
            this.signaturePad = new SignaturePad(canvas, {
                penColor: '#14532d',
                backgroundColor: 'rgba(255, 255, 255, 0)'
            });
            this.signaturePad.addEventListener('endStroke', () => {
                this.ttdTerisi = !this.signaturePad.isEmpty();
                if (this.ttdTerisi) {
                    $wire.setTandaTanganBase64(this.signaturePad.toDataURL('image/png'));
                }
            });
        }
    },
    hapusTtd() {
        if (this.signaturePad) {
            this.signaturePad.clear();
        }
        this.ttdTerisi = false;
        $wire.setTandaTanganBase64(null);
    },
    kirimTandaTangan() {
        if (this.signaturePad && !this.signaturePad.isEmpty()) {
            $wire.setTandaTanganBase64(this.signaturePad.toDataURL('image/png'));
            return true;
        }
        if ($wire.tandaTanganBase64) return true;
        return false;
    },
    get langkahSelesai() {
        let count = 0;
        if ($wire.latitude) count++;
        if ($wire.selfieBase64) count++;
        if ($wire.tandaTanganBase64 || this.ttdTerisi) count++;
        return count;
    },
    get bolehKirim() {
        return !!($wire.latitude && $wire.selfieBase64 && ($wire.tandaTanganBase64 || this.ttdTerisi));
    }
}" x-init="
    if (!@js($sudahAbsenHariIni)) {
        mintaLokasi();
        $nextTick(() => initTtd());
    }
" class="mx-auto max-w-2xl pb-24">
    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-2xl border border-hairline bg-primary-soft px-4 py-3 text-xs font-semibold text-on-primary-soft shadow-xs">
            <flux:icon.check-circle class="size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-2xl border border-danger-soft bg-danger-soft px-4 py-3 text-xs font-semibold text-danger-deep shadow-xs dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.x-circle class="size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Lokasi Error Alert --}}
    @if($lokasiError)
        <div class="mb-4 rounded-[20px] border border-danger-soft bg-danger-soft p-4 text-center dark:border-dark-danger-soft dark:bg-dark-danger-soft">
            <div class="mx-auto mb-2 flex size-10 items-center justify-center rounded-full bg-white dark:bg-surface" aria-hidden="true">
                <flux:icon.map-pin class="size-5 text-danger-deep dark:text-dark-danger-text" />
            </div>
            <h2 class="text-sm font-semibold text-danger-deep dark:text-dark-danger-text">
                {{ $lokasiDitolak ? 'Izin akses lokasi ditolak' : 'Gagal mendapatkan lokasi GPS' }}
            </h2>
            <p class="mx-auto mt-1 max-w-sm text-xs text-danger-deep dark:text-dark-danger-text">
                Absensi harian mewajibkan lokasi GPS untuk verifikasi kehadiran di wilayah tugas lapangan.
            </p>
            <button type="button" x-on:click="mintaLokasi()"
                    class="mt-3 inline-flex h-9 items-center justify-center rounded-full bg-white px-4 text-xs font-semibold text-danger-deep shadow-xs transition hover:bg-inset active:scale-95">
                Coba lagi
            </button>
            @if($lokasiDitolak)
                <div x-data="{ showPanduan: false }" class="mt-2.5">
                    <button type="button" x-on:click="showPanduan = !showPanduan"
                            class="text-xs font-semibold text-danger-deep underline underline-offset-4 dark:text-dark-danger-text">
                        Cara mengaktifkan izin lokasi di browser
                    </button>
                    <div x-show="showPanduan" x-collapse class="mt-2 rounded-xl bg-white p-3 text-left text-xs text-body dark:bg-surface">
                        <p class="font-semibold text-ink">Chrome / Edge (Android):</p>
                        <p class="mt-0.5">Ketuk ikon gembok di sebelah kiri address bar &gt; Izin &gt; Lokasi &gt; Izinkan, lalu muat ulang halaman.</p>
                        <p class="mt-2 font-semibold text-ink">Safari (iPhone):</p>
                        <p class="mt-0.5">Buka Pengaturan HP &gt; Safari &gt; Lokasi &gt; Izinkan, lalu refresh browser.</p>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-4 flex items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Absen masuk</h1>
            <p class="mt-0.5 text-xs text-mute">Selesaikan tiga langkah. Ketuk tiap langkah.</p>
        </div>
        <a href="{{ route('kolektor.riwayat-absensi.index') }}" wire:navigate
           class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3.5 py-1.5 text-xs font-semibold text-ink transition hover:bg-inset active:scale-95 dark:bg-surface">
            <flux:icon.clock class="size-3.5" aria-hidden="true" />
            <span>Riwayat</span>
        </a>
    </div>

    {{-- Status Banner Card --}}
    <div class="mb-4 rounded-[20px] border border-hairline bg-white p-3.5 shadow-xs dark:bg-surface">
        @if($sudahAbsenHariIni && $sudahAbsenKeluarHariIni)
            {{-- Status: Sudah Selesai Masuk & Keluar --}}
            <div class="flex items-center gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft" aria-hidden="true">
                    <flux:icon.check-circle class="size-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-primary-soft px-2 py-0.5 text-xs font-semibold text-on-primary-soft">
                            Selesai bertugas
                        </span>
                    </div>
                    <p class="mt-1 text-sm font-semibold text-ink tabular-nums">
                        Masuk {{ \Carbon\Carbon::parse($waktuAbsenHariIni)->setTimezone('Asia/Jakarta')->format('H:i') }} &ndash; Keluar {{ \Carbon\Carbon::parse($waktuKeluarHariIni)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                    </p>
                    <p class="mt-0.5 text-xs text-mute">Seluruh data absensi kerja hari ini telah tersimpan.</p>
                </div>
            </div>

        @elseif($sudahAbsenHariIni && !$sudahAbsenKeluarHariIni)
            {{-- Status: Sudah Masuk, Siap Absen Keluar --}}
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-warning-soft text-warning-deep" aria-hidden="true">
                        <flux:icon.clock class="size-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="inline-flex items-center rounded-full bg-warning-soft px-2 py-0.5 text-xs font-semibold text-warning-deep">
                            Sedang bertugas di lapangan
                        </span>
                        <p class="mt-1 text-sm font-semibold text-ink tabular-nums">
                            Masuk pukul {{ \Carbon\Carbon::parse($waktuAbsenHariIni)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                        </p>
                        <p class="mt-0.5 text-xs text-mute">Lakukan absen keluar saat jam kerja lapangan selesai.</p>
                    </div>
                </div>

                <button type="button" wire:click="absenKeluar" wire:loading.attr="disabled"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-full bg-primary text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-95 disabled:opacity-50">
                    <flux:icon.arrow-right-end-on-rectangle class="size-4" aria-hidden="true" />
                    <span>Catat absen keluar</span>
                </button>
            </div>

        @else
            {{-- Status: Belum Absen --}}
            <div class="flex items-center justify-between gap-3">
                <div>
                    <span class="text-xs text-mute">{{ now()->translatedFormat('l, d F Y') }}</span>
                    <p class="mt-0.5 text-base font-semibold tracking-tight tabular-nums text-ink"
                       x-data="{ time: '' }"
                       x-init="setInterval(() => time = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false, timeZone: 'Asia/Jakarta'}), 1000)">
                        <span x-text="time">--:--:--</span> <span class="text-xs font-normal text-mute">WIB</span>
                    </p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-warning-soft px-2.5 py-1 text-xs font-semibold text-warning-deep">
                        <span class="size-1.5 rounded-full bg-warning"></span>
                        Belum absen
                    </span>
                    <p class="mt-1 text-xs font-medium text-mute" x-text="langkahSelesai + ' dari 3 siap'"></p>
                </div>
            </div>
        @endif
    </div>

    {{-- The 3 Steps Form (Prototype .step style: compact, 8px gap) --}}
    @if(!$sudahAbsenHariIni)
        <div class="mb-4 space-y-2">
            {{-- Langkah 1: Lokasi GPS --}}
            <div class="flex items-center justify-between gap-3 rounded-[18px] border transition {{ $latitude && $longitude ? 'border-primary bg-primary-soft/30 dark:bg-primary-soft/15' : 'border-hairline bg-white dark:bg-surface' }} p-3">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full {{ $latitude && $longitude ? 'bg-primary text-on-primary shadow-xs' : 'bg-inset text-mute' }}" aria-hidden="true">
                        @if($latitude && $longitude)
                            <flux:icon.check class="size-4.5" />
                        @else
                            <flux:icon.map-pin class="size-4.5" />
                        @endif
                    </div>
                    <div class="min-w-0">
                        <b class="block text-sm font-semibold text-ink">1. Lokasi GPS</b>
                        <p class="text-xs text-mute truncate">
                            @if($latitude && $longitude)
                                Terdeteksi, akurasi {{ $akurasi ? round($akurasi).' m' : '149 m' }}
                            @else
                                Menunggu deteksi lokasi GPS
                            @endif
                        </p>
                    </div>
                </div>

                <div class="shrink-0">
                    @if($latitude && $longitude)
                        <button type="button" x-on:click="mintaLokasi()" title="Perbarui lokasi GPS"
                                class="inline-flex items-center gap-1 rounded-full bg-inset px-2.5 py-1 text-xs font-medium text-body transition hover:bg-hairline active:scale-95">
                            <flux:icon.arrow-path class="size-3" />
                            <span>Perbarui</span>
                        </button>
                    @else
                        <button type="button" x-on:click="mintaLokasi()"
                                class="inline-flex h-8 items-center rounded-full bg-primary px-3 text-xs font-semibold text-on-primary transition hover:bg-primary-hover active:scale-95">
                            Deteksi
                        </button>
                    @endif
                </div>
            </div>

            {{-- Langkah 2: Foto Selfie --}}
            <div class="rounded-[18px] border transition {{ $selfieBase64 ? 'border-primary bg-primary-soft/30 dark:bg-primary-soft/15' : 'border-hairline bg-white dark:bg-surface' }} p-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-full {{ $selfieBase64 ? 'bg-primary text-on-primary shadow-xs' : 'bg-inset text-mute' }}" aria-hidden="true">
                            @if($selfieBase64)
                                <flux:icon.check class="size-4.5" />
                            @else
                                <flux:icon.camera class="size-4.5" />
                            @endif
                        </div>
                        <div class="min-w-0">
                            <b class="block text-sm font-semibold text-ink">2. Foto selfie</b>
                            <p class="text-xs text-mute truncate">
                                @if($selfieBase64)
                                    Foto selfie tersimpan
                                @else
                                    Kamera depan untuk presensi
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0">
                        @if($selfieBase64)
                            <button type="button" x-on:click="bukaKamera()"
                                    class="text-xs font-semibold text-primary hover:underline">
                                Foto ulang
                            </button>
                        @else
                            <button type="button" x-show="kameraAktif" x-cloak x-on:click="tutupKamera()"
                                    class="text-xs font-semibold text-mute hover:text-ink">
                                Batal
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Area Kamera & Pratinjau Foto --}}
                <div class="mt-2.5">
                    {{-- Kondisi A: Kamera Sedang Aktif (Live Viewfinder) --}}
                    <div x-show="kameraAktif" x-cloak class="relative overflow-hidden rounded-xl bg-black">
                        <video x-ref="videoSelfie" autoplay playsinline muted class="aspect-4/3 w-full object-cover [transform:scaleX(-1)]"></video>

                        {{-- Panduan Posisi Wajah --}}
                        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center p-3">
                            <div class="size-36 rounded-full border-2 border-dashed border-white/60"></div>
                            <span class="mt-2 rounded-full bg-black/60 px-2.5 py-0.5 text-xs font-medium text-white">
                                Posisikan wajah di tengah
                            </span>
                        </div>

                        {{-- Tombol Shutter Ambil Foto --}}
                        <div class="absolute inset-x-0 bottom-3 flex items-center justify-center">
                            <button type="button" x-on:click="ambilFoto()" aria-label="Ambil foto"
                                    class="flex size-14 items-center justify-center rounded-full border-4 border-white bg-white/40 shadow-md transition hover:scale-105 active:scale-95">
                                <span class="size-10 rounded-full bg-white shadow-xs"></span>
                            </button>
                        </div>

                        {{-- Tombol Tutup Kamera --}}
                        <button type="button" x-on:click="tutupKamera()" aria-label="Tutup kamera"
                                class="absolute top-2.5 right-2.5 flex size-8 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/80">
                            <flux:icon.x-mark class="size-4" />
                        </button>
                    </div>

                    {{-- Kondisi B: Foto Sudah Diambil (Pratinjau Rapi) --}}
                    <div x-show="!kameraAktif && $wire.selfieBase64" class="flex items-center gap-3 rounded-xl border border-hairline bg-white p-2.5 dark:bg-surface">
                        <div class="size-20 shrink-0 overflow-hidden rounded-lg border border-primary bg-black">
                            <img :src="$wire.selfieBase64" alt="Pratinjau selfie" class="h-full w-full object-cover [transform:scaleX(-1)]" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 text-xs font-semibold text-primary">
                                <flux:icon.check-circle class="size-4 shrink-0" />
                                <span>Foto siap untuk presensi</span>
                            </div>
                            <p class="mt-0.5 text-xs text-mute">Ketuk foto ulang jika gambar kurang jelas atau buram.</p>
                            <button type="button" x-on:click="bukaKamera()"
                                    class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline">
                                <flux:icon.arrow-path class="size-3" />
                                <span>Ambil ulang foto</span>
                            </button>
                        </div>
                    </div>

                    {{-- Kondisi C: Belum Ada Foto & Kamera Tidak Aktif (Placeholder Bersih) --}}
                    <div x-show="!kameraAktif && !$wire.selfieBase64">
                        <button type="button" x-on:click="bukaKamera()"
                                class="flex h-24 w-full flex-col items-center justify-center rounded-xl border border-dashed border-hairline-strong bg-white transition hover:border-primary hover:bg-inset active:scale-[0.99] dark:bg-surface">
                            <div class="flex size-9 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft" aria-hidden="true">
                                <flux:icon.camera class="size-4.5" />
                            </div>
                            <span class="mt-1.5 text-xs font-semibold text-ink">Ketuk untuk buka kamera selfie</span>
                            <span class="text-xs text-mute">Pastikan pencahayaan cukup dan wajah terlihat jelas</span>
                        </button>

                        {{-- Fallback File Upload jika kamera browser terkendala --}}
                        <input type="file" accept="image/*" capture="user" x-ref="inputFoto" class="hidden" x-on:change="pilihFile($event)">
                        <div class="mt-1.5 text-center">
                            <button type="button" x-on:click="$refs.inputFoto.click()"
                                    class="text-xs text-mute hover:text-primary hover:underline">
                                Atau pilih foto dari perangkat
                            </button>
                        </div>
                    </div>

                    {{-- Pesan Error Akses Kamera --}}
                    <div x-show="errorKamera" x-cloak class="mt-2 rounded-xl border border-danger-soft bg-danger-soft p-2.5 text-xs text-danger-deep dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
                        <p class="font-semibold" x-text="errorKamera"></p>
                        <p class="mt-1">Gunakan tautan &ldquo;Atau pilih foto dari perangkat&rdquo; di atas untuk mengambil foto melalui aplikasi kamera bawaan HP.</p>
                    </div>
                </div>
            </div>

            {{-- Langkah 3: Tanda Tangan Digital --}}
            <div class="rounded-[18px] border transition {{ $tandaTanganBase64 ? 'border-primary bg-primary-soft/30 dark:bg-primary-soft/15' : 'border-hairline bg-white dark:bg-surface' }} p-3">
                <div class="mb-2 flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-full {{ $tandaTanganBase64 ? 'bg-primary text-on-primary shadow-xs' : 'bg-inset text-mute' }}" aria-hidden="true">
                            @if($tandaTanganBase64)
                                <flux:icon.check class="size-4.5" />
                            @else
                                <flux:icon.pencil class="size-4.5" />
                            @endif
                        </div>
                        <div class="min-w-0">
                            <b class="block text-sm font-semibold text-ink">3. Tanda tangan</b>
                            <p class="text-xs text-mute truncate">
                                @if($tandaTanganBase64)
                                    Tanda tangan digital tersimpan
                                @else
                                    Goreskan tanda tangan di dalam kotak
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0">
                        <button type="button" x-on:click="hapusTtd()"
                                x-show="ttdTerisi || $wire.tandaTanganBase64"
                                class="text-xs font-semibold text-primary hover:underline">
                            Hapus
                        </button>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-xl border border-hairline bg-white dark:bg-surface">
                    <canvas id="canvas-ttd" style="width:100%; height:110px; display:block;" class="touch-none"></canvas>
                    <p x-show="!ttdTerisi && !$wire.tandaTanganBase64"
                       class="pointer-events-none absolute inset-0 flex items-center justify-center text-xs text-subtle">
                        Goreskan tanda tangan di sini
                    </p>
                </div>
            </div>
        </div>

        {{-- Tombol Aksi Submit Absen Masuk --}}
        <div class="mb-5">
            <button type="button"
                    x-on:click="if (kirimTandaTangan()) $wire.absenMasuk()"
                    :disabled="!bolehKirim"
                    wire:loading.attr="disabled"
                    class="flex h-12 w-full items-center justify-center gap-2 rounded-full bg-primary text-sm font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-95 disabled:cursor-not-allowed disabled:opacity-50">
                <flux:icon.finger-print class="size-4.5" aria-hidden="true" />
                <span>Absen masuk</span>
            </button>
            <div x-show="!bolehKirim" class="mt-2 text-center text-xs text-mute">
                Foto dan tanda tangan dulu.
            </div>
        </div>
    @endif

    {{-- Riwayat Absensi 7 Hari Terakhir (Prototype #ra-l style) --}}
    <div class="space-y-2.5">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-ink">Riwayat absensi 7 hari terakhir</h2>
            <a href="{{ route('kolektor.riwayat-absensi.index') }}" wire:navigate class="text-xs font-medium text-mute hover:text-primary">
                Lihat semua &rarr;
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-hairline bg-white shadow-xs dark:bg-surface divide-y divide-hairline">
            @forelse($riwayat as $item)
                @php
                    $jamMasuk = \Carbon\Carbon::parse($item->waktu_masuk)->setTimezone('Asia/Jakarta');
                    $isTerlambat = $jamMasuk->format('H:i') > '08:30';
                @endphp
                <button type="button"
                        x-on:click="detailItem = {
                            tanggal: '{{ $item->tanggal->translatedFormat('d M Y') }}',
                            masuk: '{{ $jamMasuk->format('H.i') }} WIB',
                            keluar: '{{ $item->waktu_keluar ? \Carbon\Carbon::parse($item->waktu_keluar)->setTimezone('Asia/Jakarta')->format('H.i') . ' WIB' : '-' }}',
                            lat: '{{ $item->latitude }}',
                            lng: '{{ $item->longitude }}',
                            akurasi: '{{ $item->akurasi ? round($item->akurasi) . ' m' : '-' }}',
                            fotoUrl: '{{ $item->foto_selfie_path ? route('absensi.foto', [$item, 'selfie']) : '' }}',
                            ttdUrl: '{{ $item->tanda_tangan_path ? route('absensi.foto', [$item, 'tanda-tangan']) : '' }}',
                            terlambat: {{ $isTerlambat ? 'true' : 'false' }}
                        }"
                        class="flex min-h-14 w-full items-center justify-between gap-3 p-3 text-left transition hover:bg-inset active:scale-[0.99]">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft" aria-hidden="true">
                            <flux:icon.camera class="size-4" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-semibold text-ink sm:text-sm">
                                {{ $item->tanggal->translatedFormat('d M') }} · masuk {{ $jamMasuk->format('H.i') }}
                            </p>
                            <p class="mt-0.5 truncate text-xs text-mute">
                                GPS {{ $item->latitude ? number_format($item->latitude, 4).', '.number_format($item->longitude, 4) : '-' }} · selfie dan tanda tangan
                            </p>
                        </div>
                    </div>

                    <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $isTerlambat ? 'bg-warning-soft text-warning-deep' : 'bg-primary-soft text-on-primary-soft' }}">
                        {{ $isTerlambat ? 'Terlambat' : 'Hadir' }}
                    </span>
                </button>
            @empty
                <div class="p-8 text-center text-xs text-mute">
                    Belum ada riwayat absensi minggu ini.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Detail Sheet Modal (#sh-f style from prototype) --}}
    <div x-show="detailItem" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-0 sm:items-center sm:p-4"
         role="dialog" aria-modal="true">
        <div class="fixed inset-0" @click="detailItem = null" aria-hidden="true"></div>

        <div class="relative w-full max-w-md rounded-t-[28px] border-t border-hairline bg-surface p-5 sm:p-6 shadow-md dark:bg-surface sm:rounded-[24px] sm:border max-h-[92vh] overflow-y-auto" @click.stop>
            {{-- Mobile drag handle pill --}}
            <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-hairline-strong/60 sm:hidden" aria-hidden="true"></div>

            <div class="flex items-center justify-between pb-3 border-b border-hairline/80">
                <div>
                    <h3 class="text-base font-semibold text-ink dark:text-zinc-100" x-text="'Absensi · ' + detailItem?.tanggal"></h3>
                    <p class="text-xs text-mute dark:text-zinc-400" x-text="'Masuk ' + detailItem?.masuk + (detailItem?.keluar !== '-' ? ' · Keluar ' + detailItem?.keluar : '')"></p>
                </div>
                <button type="button" x-on:click="detailItem = null" aria-label="Tutup"
                        class="flex size-8 shrink-0 items-center justify-center rounded-full bg-surface-inset text-mute transition hover:bg-hairline hover:text-ink active:scale-95 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-400">
                    <flux:icon.x-mark class="size-4" />
                </button>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-hairline bg-inset p-2.5 text-center">
                    <span class="block text-xs font-medium text-mute mb-1.5">Foto selfie</span>
                    <template x-if="detailItem?.fotoUrl">
                        <img :src="detailItem.fotoUrl" alt="Selfie" class="mx-auto h-28 w-28 rounded-lg object-cover border border-hairline" />
                    </template>
                    <template x-if="!detailItem?.fotoUrl">
                        <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-lg bg-white text-xs text-mute dark:bg-surface">
                            Tidak ada foto
                        </div>
                    </template>
                </div>

                <div class="rounded-xl border border-hairline bg-inset p-2.5 text-center">
                    <span class="block text-xs font-medium text-mute mb-1.5">Tanda tangan</span>
                    <template x-if="detailItem?.ttdUrl">
                        <img :src="detailItem.ttdUrl" alt="Tanda tangan" class="mx-auto h-28 w-28 rounded-lg object-contain bg-white p-1 border border-hairline dark:bg-surface" />
                    </template>
                    <template x-if="!detailItem?.ttdUrl">
                        <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-lg bg-white text-xs text-mute dark:bg-surface">
                            Tidak ada ttd
                        </div>
                    </template>
                </div>
            </div>

            <div class="mt-4 rounded-xl border border-hairline bg-white p-3 space-y-2 text-xs dark:bg-surface">
                <div class="flex items-center justify-between">
                    <span class="text-mute">Lokasi (GPS)</span>
                    <span class="font-semibold tabular-nums text-ink" x-text="detailItem?.lat ? detailItem.lat + ', ' + detailItem.lng : '-'"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-mute">Akurasi GPS</span>
                    <span class="font-semibold tabular-nums text-ink" x-text="detailItem?.akurasi || '-'"></span>
                </div>
                <template x-if="detailItem?.lat">
                    <div class="pt-2 border-t border-hairline flex justify-end">
                        <a :href="'https://www.google.com/maps?q=' + detailItem.lat + ',' + detailItem.lng" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1 font-semibold text-primary hover:underline">
                            Buka di Google Maps &rarr;
                        </a>
                    </div>
                </template>
            </div>

            <div class="mt-5">
                <button type="button" x-on:click="detailItem = null"
                        class="w-full rounded-full border border-hairline-strong bg-white py-2.5 text-xs font-semibold text-ink transition hover:bg-inset dark:bg-surface">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- Script Peta Leaflet --}}
    <script>
        function tampilkanPeta(lat, lng) {
            const container = document.getElementById('peta-lokasi');
            if (!container) return;
            container.innerHTML = '';
            if (typeof L !== 'undefined') {
                const map = L.map('peta-lokasi', { zoomControl: false, attributionControl: false }).setView([lat, lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                L.marker([lat, lng]).addTo(map);
                setTimeout(() => map.invalidateSize(), 150);
            }
        }

        document.addEventListener('livewire:navigated', () => {
            const canvas = document.getElementById('canvas-ttd');
            if (canvas) {
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight || 110;
            }
        });
    </script>
</div>
