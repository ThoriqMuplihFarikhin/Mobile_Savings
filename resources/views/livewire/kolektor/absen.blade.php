<div x-data="{
    signaturePad: null,
    mintaLokasi() {
        $wire.resetLokasiError();
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                $wire.setLokasi(pos.coords.latitude, pos.coords.longitude);
                $nextTick(() => tampilkanPeta(pos.coords.latitude, pos.coords.longitude));
            },
            (err) => $wire.setLokasiError(err.message, err.code)
        );
    },
    previewSelfie(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('preview-selfie').src = e.target.result;
            document.getElementById('preview-selfie').classList.remove('hidden');
            document.getElementById('placeholder-selfie').classList.add('hidden');
            $wire.setSelfieBase64(e.target.result);
        };
        reader.readAsDataURL(file);
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
    if (!@js($sudahAbsenHariIni)) { mintaLokasi() }
    $nextTick(() => { if (!@js($sudahAbsenHariIni)) initTtd() })
    $wire.on('lokasi-didapat', () => { $nextTick(() => { if (!@js($sudahAbsenHariIni)) initTtd() }) })
" class="mx-auto max-w-2xl">
    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-xl bg-[#dcf5e3] px-4 py-3 text-sm text-[#0a7a3d] dark:bg-[#0a7a3d]/20 dark:text-[#4ade80]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-xl bg-[#f7d4d6] px-4 py-3 text-sm text-[#c50000] dark:bg-[#c50000]/20 dark:text-[#f87171]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Lokasi Error Card --}}
    @if($lokasiError)
        <div class="mb-4 rounded-2xl bg-[#f7d4d6] p-5 text-center dark:bg-[#c50000]/20">
            <div class="mb-2 flex justify-center">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#c50000]/10 dark:bg-[#c50000]/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#c50000] dark:text-[#f87171]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </div>
            </div>
            <p class="text-sm font-semibold text-[#c50000] dark:text-[#f87171]">
                {{ $lokasiDitolak ? 'Akses lokasi ditolak' : 'Gagal mendapatkan lokasi' }}
            </p>
            <p class="mt-1 text-xs text-[#c50000]/80 dark:text-[#f87171]/80">
                Absen wajib menyalakan lokasi supaya admin bisa verifikasi kunjungan kamu
            </p>
            <button type="button" x-on:click="mintaLokasi()"
                    class="mt-4 w-full rounded-xl bg-[#171717] py-2.5 text-sm font-medium text-white transition hover:opacity-90 dark:bg-white dark:text-zinc-900">
                Coba Lagi
            </button>
            @if($lokasiDitolak)
                <div x-data="{ showPanduan: false }" class="mt-3">
                    <button type="button" x-on:click="showPanduan = !showPanduan"
                            class="text-xs text-[#c50000] underline dark:text-[#f87171]">
                        Cara mengaktifkan lokasi
                    </button>
                    <div x-show="showPanduan" x-collapse class="mt-3 rounded-xl bg-white/50 p-4 text-left dark:bg-zinc-800/50">
                        <p class="mb-2 text-xs font-semibold text-[#c50000] dark:text-[#f87171]">Lokasi kamu diblokir oleh browser.</p>
                        <p class="mb-2 text-xs text-[#c50000]/80 dark:text-[#f87171]/80">Untuk mengaktifkan kembali:</p>

                        <p class="mt-2 text-xs font-semibold text-[#171717] dark:text-white">Chrome / Edge (Android & Desktop):</p>
                        <ol class="mb-2 list-inside list-decimal text-xs text-[#888888] dark:text-zinc-400">
                            <li>Tap ikon gembok/info di sebelah kiri address bar</li>
                            <li>Cari "Lokasi" atau "Location", ubah jadi "Izinkan"</li>
                            <li>Refresh halaman ini</li>
                        </ol>

                        <p class="mt-2 text-xs font-semibold text-[#171717] dark:text-white">Safari (iPhone):</p>
                        <ol class="list-inside list-decimal text-xs text-[#888888] dark:text-zinc-400">
                            <li>Buka Pengaturan HP &gt; Safari &gt; Lokasi</li>
                            <li>Pilih "Tanya" atau "Izinkan"</li>
                            <li>Kembali ke aplikasi dan refresh halaman</li>
                        </ol>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">Absen Masuk</h1>
        <p class="mt-1 text-sm text-[#888888] dark:text-zinc-400">Catat kehadiran Anda hari ini.</p>
    </div>

    {{-- Kartu Waktu --}}
    <div class="mb-6 rounded-2xl bg-[#171717] p-6 text-center shadow-[0px_2px_2px_#0000000a,0px_8px_16px_-4px_#0000000a] dark:bg-zinc-700">
        @if($sudahAbsenHariIni)
            <div class="mb-3 flex justify-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[#dcf5e3] dark:bg-[#0a7a3d]/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#0a7a3d] dark:text-[#4ade80]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                </div>
            </div>
            <p class="text-sm text-[#a1a1a1] dark:text-zinc-400">Anda sudah absen hari ini</p>
            <p class="mt-1 text-2xl font-bold text-white">{{ \Carbon\Carbon::parse($waktuAbsenHariIni)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</p>
        @else
            <p class="text-sm text-[#a1a1a1] dark:text-zinc-400">Waktu Sekarang</p>
            <p class="mt-1 text-4xl font-bold tracking-tight text-white" x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:false, timeZone: 'Asia/Jakarta'}), 1000)">
                <span x-text="time"></span>
            </p>
            <p class="mt-1 text-xs text-[#a1a1a1] dark:text-zinc-500">{{ now()->translatedFormat('l, d F Y') }}</p>
        @endif
    </div>

    @if(!$sudahAbsenHariIni)
        {{-- Peta Lokasi --}}
        @if($latitude && $longitude)
            <div class="mb-4">
                <h3 class="mb-2 text-sm font-semibold text-[#171717] dark:text-white">Lokasi Anda</h3>
                <div id="peta-lokasi" style="height:150px;border-radius:16px;" class="overflow-hidden shadow-[inset_0_0_0_1px_#ebebeb] dark:shadow-[inset_0_0_0_1px_#3f3f46]"></div>
            </div>
        @endif

        {{-- Foto Selfie --}}
        <div class="mb-4">
            <h3 class="mb-2 text-sm font-semibold text-[#171717] dark:text-white">Foto Selfie</h3>
            <input type="file" id="input-selfie" accept="image/*" capture="user" class="hidden"
                   x-on:change="previewSelfie($event)">
            <label for="input-selfie"
                   class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[#ebebeb] bg-[#fafafa] p-6 transition hover:border-[#0761d1] hover:bg-[#0761d1]/5 cursor-pointer dark:border-zinc-600 dark:bg-zinc-700/50 dark:hover:border-[#3b82f6]">
                <img id="preview-selfie" src="" alt="Preview Selfie" class="hidden mb-3 h-40 w-40 rounded-2xl object-cover">
                <div id="placeholder-selfie" class="flex flex-col items-center">
                    <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-[#e5e5e5] dark:bg-zinc-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#888888] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    </div>
                    <p class="text-xs text-[#888888] dark:text-zinc-400">Tap untuk ambil selfie</p>
                </div>
            </label>
        </div>

        {{-- Tanda Tangan --}}
        <div class="mb-4">
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-[#171717] dark:text-white">Tanda Tangan</h3>
                <button type="button" x-on:click="if(signaturePad) signaturePad.clear()"
                        class="text-xs text-[#0761d1] dark:text-[#60a5fa]">Hapus</button>
            </div>
            <canvas id="canvas-ttd"
                    style="width:100%;height:90px;background:white;border:1.5px dashed #ebebeb;border-radius:12px;"
                    class="dark:bg-zinc-700 dark:border-zinc-600"></canvas>
        </div>

        {{-- Tombol Absen --}}
        <button type="button"
                x-on:click="if (kirimTandaTangan()) $wire.absenMasuk()"
                @disabled(!$latitude || !$selfieBase64)
                class="mb-3 w-full rounded-xl bg-[#0761d1] py-3.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-[#3b82f6]">
            Absen Masuk
        </button>

        {{-- Status Syarat --}}
        <div class="mb-6 flex items-center justify-center gap-4 text-xs text-[#888888] dark:text-zinc-500">
            <span class="flex items-center gap-1">
                @if($latitude && $longitude)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#0a7a3d]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#a1a1a1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" /></svg>
                @endif
                Lokasi
            </span>
            <span class="flex items-center gap-1">
                @if($selfieBase64)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#0a7a3d]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#a1a1a1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" /></svg>
                @endif
                Selfie
            </span>
            <span class="flex items-center gap-1">
                @if($tandaTanganBase64)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#0a7a3d]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#a1a1a1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" /></svg>
                @endif
                Tanda Tangan
            </span>
        </div>
    @endif

    {{-- Riwayat 7 Hari Terakhir --}}
    <div>
        <h2 class="mb-3 text-sm font-semibold text-[#171717] dark:text-white">Riwayat Absensi</h2>
        <div class="space-y-2">
            @forelse($riwayat as $item)
                <div class="flex items-center gap-3 rounded-xl bg-[#fafafa] px-4 py-3 shadow-[inset_0_0_0_1px_#ebebeb] dark:bg-zinc-700/50 dark:shadow-[inset_0_0_0_1px_#3f3f46]">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#dcf5e3] text-[#0a7a3d] dark:bg-[#0a7a3d]/30 dark:text-[#4ade80]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-[#171717] dark:text-white">{{ $item->tanggal->translatedFormat('d M Y') }}</p>
                        <p class="text-xs text-[#888888] dark:text-zinc-400">Masuk {{ \Carbon\Carbon::parse($item->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</p>
                    </div>
                    @if($item->latitude && $item->longitude)
                        <a href="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}" target="_blank" rel="noopener noreferrer"
                           class="text-xs text-[#0761d1] hover:underline dark:text-[#60a5fa]">
                            Lihat Lokasi
                        </a>
                    @endif
                </div>
            @empty
                <div class="rounded-xl bg-[#fafafa] p-8 text-center shadow-[inset_0_0_0_1px_#ebebeb] dark:bg-zinc-700/50 dark:shadow-[inset_0_0_0_1px_#3f3f46]">
                    <p class="text-sm text-[#888888] dark:text-zinc-400">Belum ada riwayat absensi.</p>
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
