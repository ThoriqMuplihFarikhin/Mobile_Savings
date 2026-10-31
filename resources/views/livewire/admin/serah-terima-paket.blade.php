<div class="mx-auto max-w-3xl space-y-5 pb-8">
    {{-- Page Header --}}
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-ink dark:text-white sm:text-2xl">Serah Terima Paket</h1>
            <p class="mt-0.5 text-xs text-mute dark:text-zinc-400">Konfirmasi fisik penyerahan paket tabungan dan unggah tanda bukti foto di tempat.</p>
        </div>
        <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-primary-soft text-primary shadow-xs dark:bg-primary-soft/40" aria-hidden="true">
            <flux:icon.gift class="size-6" />
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-hairline-warm bg-primary-soft p-3.5 text-xs font-semibold text-on-primary-soft shadow-xs dark:border-primary/40 dark:bg-primary-soft/30 dark:text-primary">
            <flux:icon.check-circle class="size-4 shrink-0 text-primary" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-danger-soft bg-danger-soft p-3.5 text-xs font-semibold text-danger-deep shadow-xs dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.exclamation-triangle class="size-4 shrink-0 text-danger dark:text-dark-danger-text" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Filter Segmented Tabs --}}
    <div class="flex rounded-2xl bg-inset p-1 border border-hairline-warm dark:border-hairline/60 dark:bg-zinc-800/60">
        @php
            $daftarFilter = [
                'siap' => 'Siap Diserahkan ('.$jumlahSiap.')',
                'belum' => 'Belum Diserahkan',
                'sudah' => 'Sudah Diterima',
            ];
        @endphp
        @foreach ($daftarFilter as $nilai => $label)
            <button type="button" wire:click="pilihFilter('{{ $nilai }}')"
                class="flex-1 min-h-11 rounded-xl py-2 px-3 text-xs font-semibold transition cursor-pointer text-center {{ $filter === $nilai
                    ? 'bg-white shadow-xs text-primary dark:bg-surface dark:text-zinc-100'
                    : 'text-mute hover:text-ink dark:text-zinc-400 dark:hover:text-zinc-200' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Daftar Kepesertaan Paket --}}
    <div class="space-y-4">
        @forelse($kepesertaan as $item)
            @php
                $initials = collect(explode(' ', $item->nasabah?->name ?? 'N'))
                    ->map(fn($part) => mb_substr($part, 0, 1))
                    ->take(2)
                    ->join('');
                $isiPaket = $item->produk?->isi_paket ?? [];
                $uangTunai = (float) ($item->produk?->uang_tunai ?? 0);
            @endphp
            <div class="rounded-[20px] border border-hairline-warm bg-white p-4 sm:p-5 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:border-hairline/60 dark:bg-surface transition">
                <div class="space-y-4">
                    {{-- Row 1: Header Nasabah & Status --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-on-primary-soft font-semibold text-xs shadow-xs" aria-hidden="true">
                                {{ $initials ?: 'TD' }}
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-sm font-semibold text-ink dark:text-white truncate">{{ $item->nasabah?->name ?? '-' }}</h2>
                                <p class="mt-0.5 text-xs text-mute dark:text-zinc-400 truncate">
                                    {{ $item->produk?->nama ?? '-' }} · Mulai {{ $item->tanggal_mulai_ikut?->translatedFormat('d M Y') }}
                                </p>
                            </div>
                        </div>

                        <div>
                            @if ($item->status_serah_terima === 'sudah_diterima')
                                <span class="inline-flex items-center gap-1 rounded-full bg-primary-soft px-3 py-1 text-xs font-medium text-on-primary-soft dark:bg-primary-soft/50 dark:text-primary">
                                    <flux:icon.check-circle class="size-3.5" />
                                    Sudah Diterima
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-warning-soft px-3 py-1 text-xs font-medium text-warning-deep dark:bg-warning-soft dark:text-warning-deep">
                                    <flux:icon.clock class="size-3.5" />
                                    Belum Diserahkan
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Row 2: Informasi Pengambilan & Status --}}
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        <div class="rounded-xl bg-inset p-3 dark:bg-zinc-800/60">
                            <p class="text-xs text-mute dark:text-zinc-400">Metode</p>
                            <p class="mt-0.5 text-xs font-semibold text-ink dark:text-white truncate">
                                {{ $item->metode_pengambilan === 'diantar_kolektor' ? 'Diantar Kolektor' : ($item->metode_pengambilan === 'ambil_sendiri' ? 'Ambil Sendiri' : 'Belum dipilih') }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-inset p-3 dark:bg-zinc-800/60">
                            <p class="text-xs text-mute dark:text-zinc-400">Boleh Cair</p>
                            <p class="mt-0.5 text-xs font-semibold text-ink dark:text-white truncate">
                                {{ $item->produk?->tanggal_boleh_cair?->translatedFormat('d M Y') ?? '-' }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-inset p-3 dark:bg-zinc-800/60">
                            <p class="text-xs text-mute dark:text-zinc-400">Tunggakan</p>
                            <p class="mt-0.5 text-xs font-semibold {{ $item->tunggakan > 0 ? 'text-danger dark:text-dark-danger-text' : 'text-primary' }}">
                                {{ $item->tunggakan > 0 ? $item->tunggakan.' hari' : 'Lunas' }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-inset p-3 dark:bg-zinc-800/60">
                            <p class="text-xs text-mute dark:text-zinc-400">Serah Terima</p>
                            <p class="mt-0.5 text-xs font-semibold text-ink dark:text-white truncate">
                                {{ $item->status_serah_terima === 'sudah_diterima' ? $item->tanggal_serah_terima?->translatedFormat('d M Y') : 'Menunggu' }}
                            </p>
                        </div>
                    </div>

                    {{-- Row 3: Checklist Fisik Barang & Uang Tunai --}}
                    @if(count($isiPaket) > 0 || $uangTunai > 0)
                        <div class="rounded-xl border border-hairline-warm bg-primary-soft-2/50 p-3.5 space-y-2.5 dark:border-hairline/60 dark:bg-zinc-800/40">
                            <div class="flex items-center justify-between gap-2 border-b border-hairline pb-2">
                                <span class="text-xs font-semibold text-ink dark:text-zinc-100 flex items-center gap-1.5">
                                    <flux:icon.clipboard-document-check class="size-4 text-primary" />
                                    Manifest Fisik Barang & Uang Yang Diserahkan
                                </span>
                                <span class="text-xs text-mute dark:text-zinc-400 font-medium">
                                    {{ count($isiPaket) }} Barang{{ $uangTunai > 0 ? ' + Uang Tunai' : '' }}
                                </span>
                            </div>

                            @if($uangTunai > 0)
                                <div class="flex items-center justify-between rounded-xl border border-primary/30 bg-primary-soft p-2.5">
                                    <div class="flex items-center gap-2">
                                        <flux:icon.banknotes class="size-4 text-primary shrink-0" />
                                        <span class="text-xs font-semibold text-primary">Wajib Serahkan Uang Tunai</span>
                                    </div>
                                    <span class="text-xs font-semibold text-primary tabular-nums">
                                        Rp {{ number_format($uangTunai, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif

                            @if(count($isiPaket) > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($isiPaket as $b)
                                        <div class="flex items-center justify-between rounded-xl border border-hairline-warm bg-white px-3 py-2 text-xs text-ink dark:border-hairline dark:bg-surface dark:text-zinc-100 shadow-xs">
                                            <span class="font-medium truncate">{{ $b['nama'] ?? '-' }}</span>
                                            <span class="shrink-0 font-semibold text-mute dark:text-zinc-400">{{ $b['jumlah'] ?? '-' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Row 4: Informasi Paket Sudah Diterima & Bukti Foto di Tempat --}}
                    @if ($item->status_serah_terima === 'sudah_diterima')
                        <div class="rounded-xl border border-hairline-warm bg-inset p-3.5 dark:border-hairline/60 dark:bg-zinc-800/40">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="min-w-0 flex items-center gap-3">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary">
                                        <flux:icon.photo class="size-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-mute dark:text-zinc-400">
                                            Diterima oleh <span class="font-semibold text-ink dark:text-white">{{ $item->diterima_oleh ?? '-' }}</span>
                                        </p>
                                        <p class="mt-0.5 text-xs text-primary font-semibold flex items-center gap-1">
                                            <flux:icon.check-circle class="size-3.5" />
                                            Tanda bukti foto di tempat terverifikasi
                                        </p>
                                    </div>
                                </div>

                                @if ($item->bukti_foto_url)
                                    <a href="{{ route('serah-terima.bukti', $item) }}"
                                       @click.prevent="$dispatch('buka-foto-bukti', { url: '{{ route('serah-terima.bukti', $item) }}', judul: 'Foto Bukti Serah Terima', keterangan: '{{ $item->nasabah?->name }} · {{ $item->produk?->nama }}' })"
                                       class="inline-flex items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3.5 py-1.5 text-xs font-semibold text-body hover:bg-inset hover:border-ink transition active:scale-95 dark:border-hairline dark:bg-surface dark:text-zinc-200 cursor-pointer">
                                        <flux:icon.eye class="size-3.5" />
                                        Lihat Foto Bukti
                                    </a>
                                @endif
                            </div>
                        </div>
                    @elseif ($filter !== 'sudah')
                        <div class="flex justify-end border-t border-hairline pt-3.5">
                            <button type="button" wire:click="bukaKonfirmasi({{ $item->id }})"
                                class="inline-flex items-center gap-2 rounded-full bg-primary hover:bg-primary-hover px-5 py-2.5 text-xs font-semibold text-on-primary shadow-xs transition active:scale-95 cursor-pointer">
                                <flux:icon.gift class="size-4" />
                                Konfirmasi Serah Terima
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Form Konfirmasi Serah Terima & Tanda Bukti Foto di Tempat --}}
                @if ($konfirmasiId === $item->id)
                    <div class="mt-4 border-t border-hairline pt-4 space-y-4" x-data="kompresFoto">
                        <div class="rounded-xl bg-inset p-3 border border-hairline-warm dark:border-hairline/60 dark:bg-zinc-800/40">
                            <h3 class="text-xs font-semibold text-ink dark:text-white flex items-center gap-1.5">
                                <flux:icon.camera class="size-4 text-primary" />
                                Formulir Serah Terima & Tanda Bukti Foto di Tempat
                            </h3>
                            <p class="mt-0.5 text-xs text-mute dark:text-zinc-400">
                                Pastikan fisik barang telah diserahkan dan ambil foto bukti langsung di tempat bersama penerima.
                            </p>
                        </div>

                        <div wire:loading wire:target="konfirmasi" class="rounded-xl bg-info-soft p-3 text-xs font-semibold text-info flex items-center gap-2">
                            <flux:icon.arrow-path class="size-4 animate-spin" />
                            <span>Memproses dan mengunggah konfirmasi serah terima...</span>
                        </div>

                        <div class="space-y-4">
                            {{-- Input 1: Diterima Oleh --}}
                            <div>
                                <label for="diterimaOleh" class="mb-1 block text-xs font-semibold text-ink dark:text-zinc-200">Diterima Oleh</label>
                                <input id="diterimaOleh" type="text" wire:model="diterimaOleh"
                                    class="w-full rounded-xl border border-hairline-strong bg-white px-3.5 py-2.5 text-xs text-ink focus:border-primary focus:ring-1 focus:ring-primary dark:border-hairline dark:bg-surface dark:text-white"
                                    placeholder="Contoh: {{ $item->nasabah?->name }} (Nasabah) / Istri" />

                                {{-- Quick Chips Autofill --}}
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    <button type="button" @click="$wire.set('diterimaOleh', '{{ addslashes($item->nasabah?->name ?? '') }}')"
                                        class="rounded-full border border-hairline-warm bg-white px-2.5 py-1 text-xs font-medium text-mute hover:border-primary hover:text-primary transition dark:border-hairline dark:bg-zinc-800">
                                        + Nasabah Sendiri
                                    </button>
                                    <button type="button" @click="$wire.set('diterimaOleh', 'Pasangan (Suami/Istri)')"
                                        class="rounded-full border border-hairline-warm bg-white px-2.5 py-1 text-xs font-medium text-mute hover:border-primary hover:text-primary transition dark:border-hairline dark:bg-zinc-800">
                                        + Pasangan
                                    </button>
                                    <button type="button" @click="$wire.set('diterimaOleh', 'Keluarga Serumah')"
                                        class="rounded-full border border-hairline-warm bg-white px-2.5 py-1 text-xs font-medium text-mute hover:border-primary hover:text-primary transition dark:border-hairline dark:bg-zinc-800">
                                        + Keluarga Serumah
                                    </button>
                                </div>
                                @error('diterimaOleh') <p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Input 2: Tanggal Serah Terima --}}
                            <div>
                                <label for="tanggalSerahTerima" class="mb-1 block text-xs font-semibold text-ink dark:text-zinc-200">Tanggal Serah Terima</label>
                                <x-ui.tanggal wire:model="tanggalSerahTerima" :max="now()->toDateString()" id="tanggalSerahTerima"
                                    class="w-full rounded-xl border border-hairline-strong bg-white px-3.5 py-2.5 text-xs text-ink focus:border-primary focus:ring-1 focus:ring-primary dark:border-hairline dark:bg-surface dark:text-white" />
                                @error('tanggalSerahTerima') <p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Input 3: Tanda Bukti Foto (2 Opsi: Kamera Langsung di APK atau Galeri HP) --}}
                            <div>
                                <label for="buktiFoto" class="mb-1 block text-xs font-semibold text-ink dark:text-zinc-200">
                                    Foto Bukti (JPG/PNG/WebP, maks 5 MB)
                                </label>
                                <p class="text-xs text-mute dark:text-zinc-400 mb-2.5">
                                    Pilih opsi metode bukti: foto langsung di tempat melalui kamera aplikasi, atau ambil dari galeri HP.
                                </p>

                                {{-- Hidden file inputs (disembunyikan agar teks default browser 'No file chosen' tidak tampil) --}}
                                <input id="buktiFoto" x-ref="kameraInput" type="file" accept="image/*" capture
                                    x-on:change="proses($event.target)"
                                    class="hidden" style="display: none;" />

                                <input id="buktiFotoGaleri" x-ref="galeriInput" type="file" accept="image/jpeg,image/png,image/webp,image/*"
                                    x-on:change="proses($event.target)"
                                    class="hidden" style="display: none;" />

                                {{-- Tab Opsi: Kamera Langsung vs Galeri --}}
                                <div class="mb-3 flex rounded-xl bg-inset p-1 border border-hairline-warm dark:border-hairline/60 dark:bg-zinc-800/60" x-show="!pratinjauUrl && !@js((bool)$buktiFoto)">
                                    <button type="button" x-on:click="setTipe('kamera')"
                                        :class="tipeFoto === 'kamera' ? 'bg-white shadow-xs text-primary font-semibold dark:bg-surface dark:text-zinc-100' : 'text-mute hover:text-ink dark:text-zinc-400'"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg py-2 text-xs transition cursor-pointer">
                                        <flux:icon.camera class="size-4" />
                                        Foto Langsung (Kamera)
                                    </button>
                                    <button type="button" x-on:click="setTipe('galeri')"
                                        :class="tipeFoto === 'galeri' ? 'bg-white shadow-xs text-primary font-semibold dark:bg-surface dark:text-zinc-100' : 'text-mute hover:text-ink dark:text-zinc-400'"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg py-2 text-xs transition cursor-pointer">
                                        <flux:icon.photo class="size-4" />
                                        Ambil dari Galeri HP
                                    </button>
                                </div>

                                {{-- Opsi 1: Kamera Langsung di Tempat Melalui APK --}}
                                <div x-show="tipeFoto === 'kamera' && !pratinjauUrl && !@js((bool)$buktiFoto)" class="space-y-2.5">
                                    {{-- Info jika izin kamera ditolak / tidak tersedia --}}
                                    <div x-show="errorKamera" style="display:none" class="rounded-xl border border-warning-soft bg-warning-soft/70 p-3 text-xs text-warning-deep dark:bg-warning-soft dark:text-warning-deep">
                                        <p x-text="errorKamera"></p>
                                        <button type="button" x-on:click="$refs.kameraInput.click()"
                                            class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 font-semibold text-warning-deep shadow-xs dark:bg-surface cursor-pointer">
                                            <flux:icon.camera class="size-3.5" />
                                            Buka Kamera HP Bawaan
                                        </button>
                                    </div>

                                    {{-- Standby Card Sebelum Kamera Dibuka --}}
                                    <div x-show="!kameraAktif" class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-hairline-strong/60 bg-inset p-6 text-center dark:bg-zinc-800/60">
                                        <div class="mb-3 flex size-12 items-center justify-center rounded-2xl bg-white text-primary shadow-xs dark:bg-surface">
                                            <flux:icon.camera class="size-6" />
                                        </div>
                                        <span class="text-xs font-semibold text-ink dark:text-zinc-100">Kamera Langsung di Tempat</span>
                                        <span class="mt-1 max-w-xs text-xs text-mute dark:text-zinc-400">
                                            Buka kamera untuk mengambil foto bukti serah terima secara langsung di hadapan nasabah.
                                        </span>
                                        <div class="mt-3.5 flex flex-wrap items-center justify-center gap-2">
                                            <button type="button" x-on:click="bukaKamera()"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-primary hover:bg-primary-hover px-4 py-2 text-xs font-semibold text-on-primary shadow-xs transition active:scale-95 cursor-pointer">
                                                <flux:icon.camera class="size-3.5" />
                                                Buka Kamera di Tempat
                                            </button>
                                            <button type="button" x-on:click="$refs.kameraInput.click()"
                                                class="inline-flex items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3.5 py-2 text-xs font-semibold text-body hover:bg-inset transition active:scale-95 cursor-pointer dark:border-hairline dark:bg-surface dark:text-zinc-200">
                                                <flux:icon.arrow-up-tray class="size-3.5" />
                                                Kamera Bawaan HP
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Live Viewfinder Kamera (Ketika Kamera Aktif) --}}
                                    <div x-show="kameraAktif" class="relative overflow-hidden rounded-2xl border-2 border-primary/40 bg-black shadow-xs">
                                        <video x-ref="videoKamera" autoplay playsinline muted class="h-64 sm:h-72 w-full object-cover"></video>

                                        {{-- Frame Panduan Foto Serah Terima --}}
                                        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-between p-3.5">
                                            <div class="pointer-events-auto flex w-full items-center justify-between">
                                                <div class="rounded-full bg-black/70 px-3 py-1 text-xs font-medium text-white flex items-center gap-1.5">
                                                    <span class="size-2 rounded-full bg-danger animate-pulse"></span>
                                                    Kamera Langsung di Tempat
                                                </div>
                                                <button type="button" x-on:click="tutupKamera()"
                                                    class="rounded-full bg-black/70 px-2.5 py-1 text-xs font-medium text-white hover:bg-black/90 transition cursor-pointer">
                                                    Tutup Kamera
                                                </button>
                                            </div>
                                            <div class="w-48 h-32 border-2 border-dashed border-white/60 rounded-xl"></div>
                                            <span class="text-xs text-white/90 bg-black/70 px-2.5 py-0.5 rounded-full">
                                                Arahkan ke penerima & fisik paket
                                            </span>
                                        </div>

                                        {{-- Tombol Kontrol Kamera di Bawah --}}
                                        <div class="absolute bottom-3 left-0 right-0 flex items-center justify-center gap-4 px-4">
                                            <button type="button" x-on:click="gantiKamera()"
                                                class="flex size-10 items-center justify-center rounded-full bg-black/70 text-white hover:bg-black/90 transition active:scale-95 cursor-pointer"
                                                title="Ganti kamera depan/belakang">
                                                <flux:icon.arrow-path class="size-4" />
                                            </button>

                                            <button type="button" x-on:click="ambilFotoDariKamera()"
                                                class="flex size-14 items-center justify-center rounded-full border-4 border-white bg-primary text-white shadow-md transition hover:scale-105 active:scale-95 cursor-pointer"
                                                title="Jepret foto serah terima">
                                                <flux:icon.camera class="size-6" />
                                            </button>

                                            <button type="button" x-on:click="$refs.kameraInput.click()"
                                                class="flex size-10 items-center justify-center rounded-full bg-black/70 text-white hover:bg-black/90 transition active:scale-95 cursor-pointer"
                                                title="Gunakan kamera sistem HP">
                                                <flux:icon.arrow-up-tray class="size-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Opsi 2: Ambil dari Galeri HP (Foto dari Luar APK) --}}
                                <div x-show="tipeFoto === 'galeri' && !pratinjauUrl && !@js((bool)$buktiFoto)">
                                    <button type="button" x-on:click="$refs.galeriInput.click()"
                                        class="group flex w-full flex-col items-center justify-center rounded-2xl border-2 border-dashed border-hairline-strong/60 bg-inset p-7 text-center transition hover:border-primary hover:bg-primary-soft/20 active:scale-[0.99] cursor-pointer dark:bg-zinc-800/60 dark:hover:border-primary">
                                        <div class="mb-3 flex size-14 items-center justify-center rounded-2xl bg-white text-primary shadow-xs dark:bg-surface">
                                            <flux:icon.photo class="size-7 transition-transform group-hover:scale-110" />
                                        </div>
                                        <span class="text-xs font-semibold text-ink dark:text-zinc-100">Pilih Foto dari Galeri HP</span>
                                        <span class="mt-1 text-xs text-mute dark:text-zinc-400 max-w-xs">
                                            Ketuk untuk memilih foto tanda bukti yang sudah dipotret di luar aplikasi (JPG, PNG, WebP maks 5 MB)
                                        </span>
                                    </button>
                                </div>

                                {{-- Status Mengompres Foto --}}
                                <p x-show="sedangKompres" style="display:none" class="mt-2 text-xs font-semibold text-info flex items-center gap-1.5">
                                    <flux:icon.arrow-path class="size-3.5 animate-spin" />
                                    <span>Mengompres foto...</span>
                                </p>

                                {{-- Pratinjau Foto Bukti Terpilih (Kamera maupun Galeri) --}}
                                <div x-show="pratinjauUrl || @js((bool)$buktiFoto)" style="display:none" class="mt-2.5">
                                    <div class="relative overflow-hidden rounded-2xl border border-hairline-warm bg-inset shadow-xs dark:border-hairline/60 dark:bg-zinc-800">
                                        <template x-if="pratinjauUrl">
                                            <img :src="pratinjauUrl" alt="Pratinjau Foto Bukti di Tempat" class="max-h-64 w-full object-cover">
                                        </template>
                                        @if ($buktiFoto)
                                            <div x-show="!pratinjauUrl">
                                                <img src="{{ $buktiFoto->temporaryUrl() }}" alt="Pratinjau Foto Bukti di Tempat" class="max-h-64 w-full object-cover">
                                            </div>
                                        @endif
                                        <div class="p-3 bg-white dark:bg-surface border-t border-hairline flex flex-wrap items-center justify-between gap-2">
                                            <div class="flex items-center gap-1.5 text-xs font-semibold text-primary">
                                                <flux:icon.check-circle class="size-4 shrink-0" />
                                                <span>Tanda bukti foto siap diunggah</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button" x-on:click="hapusFoto(); setTipe('kamera'); $nextTick(() => bukaKamera())"
                                                    class="inline-flex items-center gap-1 rounded-full border border-hairline-strong bg-white px-3 py-1.5 text-xs font-semibold text-body hover:bg-inset transition active:scale-95 cursor-pointer dark:border-hairline dark:bg-surface dark:text-zinc-200">
                                                    <flux:icon.camera class="size-3.5" />
                                                    Foto Ulang
                                                </button>
                                                <button type="button" x-on:click="hapusFoto(); setTipe('galeri'); $nextTick(() => $refs.galeriInput.click())"
                                                    class="inline-flex items-center gap-1 rounded-full border border-hairline-strong bg-white px-3 py-1.5 text-xs font-semibold text-body hover:bg-inset transition active:scale-95 cursor-pointer dark:border-hairline dark:bg-surface dark:text-zinc-200">
                                                    <flux:icon.photo class="size-3.5" />
                                                    Pilih Galeri
                                                </button>
                                                <button type="button" x-on:click="hapusFoto()"
                                                    class="inline-flex items-center gap-1 rounded-full border border-danger/40 bg-danger-soft px-3 py-1.5 text-xs font-semibold text-danger hover:bg-danger/20 transition active:scale-95 cursor-pointer">
                                                    <flux:icon.trash class="size-3.5" />
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @error('buktiFoto') <p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Tombol Aksi Formulir --}}
                        <div class="flex justify-end gap-2.5 pt-2">
                            <button type="button" wire:click="batalKonfirmasi"
                                class="rounded-full border border-hairline-strong bg-white px-4 py-2.5 text-xs font-semibold text-mute hover:bg-inset transition cursor-pointer dark:border-hairline dark:bg-surface dark:text-zinc-200">
                                Batal
                            </button>
                            <button type="button" wire:click="konfirmasi({{ $item->id }})"
                                class="inline-flex items-center gap-1.5 rounded-full bg-primary hover:bg-primary-hover px-5 py-2.5 text-xs font-semibold text-on-primary shadow-xs transition active:scale-95 cursor-pointer disabled:opacity-60"
                                wire:loading.attr="disabled" wire:target="konfirmasi">
                                <flux:icon.check class="size-4" />
                                Simpan Konfirmasi
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-[20px] border border-hairline-warm bg-white p-10 text-center shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:border-hairline/60 dark:bg-surface">
                <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-inset text-mute dark:bg-zinc-800 dark:text-zinc-400">
                    <flux:icon.gift class="size-7" />
                </div>
                <h3 class="mt-4 text-sm font-semibold text-ink dark:text-white">Tidak Ada Kepesertaan</h3>
                <p class="mt-1 text-xs text-mute dark:text-zinc-400">
                    @if ($filter === 'siap')
                        Belum ada paket yang siap diserahkan kepada nasabah.
                    @elseif ($filter === 'belum')
                        Semua paket sudah selesai diserahkan.
                    @else
                        Belum ada paket yang sudah diserahkan.
                    @endif
                </p>
            </div>
        @endforelse
    </div>
</div>
