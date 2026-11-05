<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-xs font-medium text-mute dark:text-zinc-400">Operasional</p>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-ink dark:text-white mt-0.5">Serah Terima Paket</h1>
            <p class="mt-1 text-xs sm:text-sm text-mute dark:text-zinc-400">Konfirmasi penyerahan paket kepada nasabah beserta foto bukti.</p>
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

    {{-- Main Container Card --}}
    <div class="rounded-2xl border border-hairline/80 bg-white shadow-xs dark:bg-surface overflow-hidden">
        {{-- Toolbar / Segmented Tabs --}}
        <div class="p-4 border-b border-hairline/60">
            <div class="inline-flex rounded-xl bg-surface-inset p-1 border border-hairline/60 dark:bg-zinc-800/60">
                <button type="button" wire:click="pilihFilter('siap')"
                    @class([
                        'px-4 py-2 min-h-11 rounded-lg text-xs font-semibold transition cursor-pointer text-center',
                        'bg-white shadow-xs text-ink dark:bg-surface dark:text-zinc-100' => $filter === 'siap',
                        'text-mute hover:text-ink dark:text-zinc-400 dark:hover:text-zinc-200' => $filter !== 'siap',
                    ])>
                    Siap diserahkan ({{ $jumlahSiap }})
                </button>
                <button type="button" wire:click="pilihFilter('belum')"
                    @class([
                        'px-4 py-2 min-h-11 rounded-lg text-xs font-semibold transition cursor-pointer text-center',
                        'bg-white shadow-xs text-ink dark:bg-surface dark:text-zinc-100' => $filter === 'belum',
                        'text-mute hover:text-ink dark:text-zinc-400 dark:hover:text-zinc-200' => $filter !== 'belum',
                    ])>
                    Belum bisa ({{ $jumlahBelum }})
                </button>
                <button type="button" wire:click="pilihFilter('sudah')"
                    @class([
                        'px-4 py-2 min-h-11 rounded-lg text-xs font-semibold transition cursor-pointer text-center',
                        'bg-white shadow-xs text-ink dark:bg-surface dark:text-zinc-100' => $filter === 'sudah',
                        'text-mute hover:text-ink dark:text-zinc-400 dark:hover:text-zinc-200' => $filter !== 'sudah',
                    ])>
                    Sudah diterima ({{ $jumlahSudah }})
                </button>
            </div>
        </div>

        {{-- Desktop View (Div-based Grid, NO <table> tag) --}}
        <div class="hidden md:block">
            <div class="grid grid-cols-12 gap-4 px-5 py-3 border-b border-hairline/60 text-[11px] font-semibold uppercase tracking-wider text-mute">
                <div class="col-span-3">NASABAH</div>
                <div class="col-span-2">PRODUK</div>
                <div class="col-span-3">METODE PENGAMBILAN</div>
                <div class="col-span-2">KETERANGAN</div>
                <div class="col-span-2 text-right">AKSI</div>
            </div>
            <div class="divide-y divide-hairline/60">
                @forelse($kepesertaan as $item)
                    <div class="grid grid-cols-12 gap-4 px-5 py-3.5 items-center text-xs">
                        <div class="col-span-3 font-medium text-ink dark:text-white">
                            {{ $item->nasabah?->name ?? '-' }}
                        </div>
                        <div class="col-span-2 text-ink dark:text-zinc-200">
                            {{ $item->produk?->nama ?? '-' }}
                        </div>
                        <div class="col-span-3 text-ink dark:text-zinc-200">
                            {{ $item->metode_pengambilan === 'diantar_kolektor' ? 'Diantar kolektor' : ($item->metode_pengambilan === 'ambil_sendiri' ? 'Ambil di kantor' : 'Belum dipilih') }}
                        </div>
                        <div class="col-span-2">
                            @if($filter === 'siap')
                                <div class="flex items-center gap-1.5">
                                    <x-admin.status-chip status="Boleh" />
                                    <span class="text-xs text-mute dark:text-zinc-400">Boleh cair</span>
                                </div>
                            @elseif($filter === 'sudah')
                                <span class="text-xs text-mute dark:text-zinc-400">
                                    {{ $item->diterima_oleh ?? '-' }}, {{ $item->tanggal_serah_terima?->translatedFormat('d M Y') }}
                                </span>
                            @else
                                <span class="text-xs text-mute dark:text-zinc-400">
                                    {{ $item->statusPencairan()['alasan'] ?? 'Lunasi tunggakan sebelum mencairkan paket.' }}
                                </span>
                            @endif
                        </div>
                        <div class="col-span-2 text-right">
                            @if($filter === 'siap')
                                <button type="button" wire:click="bukaKonfirmasi({{ $item->id }})"
                                    class="inline-flex items-center justify-center rounded-lg bg-primary hover:bg-primary-hover text-white text-xs font-semibold px-4 py-2 min-h-11 shadow-xs transition active:scale-95 cursor-pointer">
                                    Serah terima
                                </button>
                            @elseif($filter === 'sudah')
                                @if($item->bukti_foto_url)
                                    <a href="{{ route('serah-terima.bukti', $item) }}" target="_blank"
                                        class="inline-flex items-center justify-center rounded-lg border border-hairline bg-surface text-ink hover:bg-surface-inset text-xs font-medium px-3.5 py-1.5 min-h-11 transition active:scale-95 cursor-pointer dark:bg-zinc-800 dark:text-zinc-200">
                                        Lihat bukti
                                    </a>
                                @else
                                    <span class="text-mute">-</span>
                                @endif
                            @else
                                <span class="text-mute">-</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="text-sm font-semibold text-ink dark:text-white">Tidak ada kepesertaan</div>
                        <div class="mt-1 text-xs text-mute dark:text-zinc-400">Belum ada paket di kategori ini.</div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Mobile View (Cards, NO <table> tag) --}}
        <div class="md:hidden divide-y divide-hairline/60">
            @forelse($kepesertaan as $item)
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="text-sm font-semibold text-ink dark:text-white">{{ $item->nasabah?->name ?? '-' }}</div>
                            <div class="text-xs text-mute dark:text-zinc-400 mt-0.5">{{ $item->produk?->nama ?? '-' }}</div>
                        </div>
                        @if($filter === 'siap')
                            <div class="flex items-center gap-1.5">
                                <x-admin.status-chip status="Boleh" />
                                <span class="text-xs text-mute dark:text-zinc-400">Boleh cair</span>
                            </div>
                        @elseif($filter === 'sudah')
                            <span class="inline-flex items-center gap-1 rounded-full bg-primary-soft px-2.5 py-0.5 text-[11px] font-medium text-on-primary-soft dark:bg-primary-soft/50 dark:text-primary">
                                Selesai
                            </span>
                        @endif
                    </div>
                    <div class="text-xs text-body dark:text-zinc-300">
                        <span class="text-mute dark:text-zinc-400">Metode:</span>
                        {{ $item->metode_pengambilan === 'diantar_kolektor' ? 'Diantar kolektor' : ($item->metode_pengambilan === 'ambil_sendiri' ? 'Ambil di kantor' : 'Belum dipilih') }}
                    </div>
                    @if($filter === 'belum')
                        <div class="text-xs text-mute dark:text-zinc-400">
                            {{ $item->statusPencairan()['alasan'] ?? 'Lunasi tunggakan sebelum mencairkan paket.' }}
                        </div>
                    @elseif($filter === 'sudah')
                        <div class="text-xs text-mute dark:text-zinc-400">
                            Diterima: {{ $item->diterima_oleh ?? '-' }}, {{ $item->tanggal_serah_terima?->translatedFormat('d M Y') }}
                        </div>
                    @endif
                    <div class="pt-1 flex items-center justify-end">
                        @if($filter === 'siap')
                            <button type="button" wire:click="bukaKonfirmasi({{ $item->id }})"
                                class="w-full inline-flex items-center justify-center rounded-lg bg-primary hover:bg-primary-hover text-white text-xs font-semibold px-4 py-2.5 min-h-11 shadow-xs transition active:scale-95 cursor-pointer">
                                Serah terima
                            </button>
                        @elseif($filter === 'sudah' && $item->bukti_foto_url)
                            <a href="{{ route('serah-terima.bukti', $item) }}" target="_blank"
                                class="w-full inline-flex items-center justify-center rounded-lg border border-hairline bg-surface text-ink hover:bg-surface-inset text-xs font-medium px-4 py-2.5 min-h-11 transition active:scale-95 cursor-pointer dark:bg-zinc-800 dark:text-zinc-200">
                                Lihat bukti
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <div class="text-sm font-semibold text-ink dark:text-white">Tidak ada kepesertaan</div>
                    <div class="mt-1 text-xs text-mute dark:text-zinc-400">Belum ada paket di kategori ini.</div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modal Konfirmasi Serah Terima --}}
    @if($konfirmasiId && $konfirmasiItem)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink/40 dark:bg-black/60"
             x-data="kompresFoto"
             role="dialog"
             aria-modal="true"
             aria-labelledby="modal-title">
            <div class="w-full max-w-lg rounded-2xl border border-hairline bg-white p-6 shadow-xl dark:bg-surface space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-start justify-between gap-3 border-b border-hairline/60 pb-3">
                    <div>
                        <h2 id="modal-title" class="text-base font-bold text-ink dark:text-white">Serah terima paket</h2>
                        <p class="text-xs text-mute dark:text-zinc-400 mt-0.5">
                            {{ $konfirmasiItem->nasabah?->name ?? '-' }} · {{ $konfirmasiItem->produk?->nama ?? '-' }}
                        </p>
                    </div>
                    <button type="button" wire:click="batalKonfirmasi"
                        class="rounded-lg p-1 text-mute hover:text-ink dark:hover:text-white cursor-pointer"
                        aria-label="Tutup modal">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                {{-- Form fields --}}
                <div class="space-y-4">
                    {{-- Input 1: Diterima Oleh --}}
                    <div>
                        <label for="diterimaOleh" class="mb-1 block text-xs font-semibold text-ink dark:text-zinc-200">Diterima oleh</label>
                        <input id="diterimaOleh" type="text" wire:model="diterimaOleh"
                            class="w-full rounded-xl border border-hairline bg-white px-3.5 py-2.5 text-xs text-ink focus:border-primary focus:ring-1 focus:ring-primary dark:border-hairline dark:bg-surface dark:text-white"
                            placeholder="Contoh: {{ $konfirmasiItem->nasabah?->name }} (Nasabah) / Istri" />

                        {{-- Quick Chips Autofill --}}
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <button type="button" @click="$wire.set('diterimaOleh', '{{ addslashes($konfirmasiItem->nasabah?->name ?? '') }}')"
                                class="rounded-full border border-hairline bg-white px-2.5 py-1 text-xs font-medium text-mute hover:border-primary hover:text-primary transition dark:border-hairline dark:bg-zinc-800 cursor-pointer">
                                + Nasabah Sendiri
                            </button>
                            <button type="button" @click="$wire.set('diterimaOleh', 'Pasangan (Suami/Istri)')"
                                class="rounded-full border border-hairline bg-white px-2.5 py-1 text-xs font-medium text-mute hover:border-primary hover:text-primary transition dark:border-hairline dark:bg-zinc-800 cursor-pointer">
                                + Pasangan
                            </button>
                            <button type="button" @click="$wire.set('diterimaOleh', 'Keluarga Serumah')"
                                class="rounded-full border border-hairline bg-white px-2.5 py-1 text-xs font-medium text-mute hover:border-primary hover:text-primary transition dark:border-hairline dark:bg-zinc-800 cursor-pointer">
                                + Keluarga Serumah
                            </button>
                        </div>
                        @error('diterimaOleh') <p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p> @enderror
                    </div>

                    {{-- Input 2: Tanggal Serah Terima --}}
                    <div>
                        <label for="tanggalSerahTerima" class="mb-1 block text-xs font-semibold text-ink dark:text-zinc-200">Tanggal serah terima</label>
                        <x-ui.tanggal wire:model="tanggalSerahTerima" :max="now()->toDateString()" id="tanggalSerahTerima"
                            class="w-full rounded-xl border border-hairline bg-white px-3.5 py-2.5 text-xs text-ink focus:border-primary focus:ring-1 focus:ring-primary dark:border-hairline dark:bg-surface dark:text-white" />
                        @error('tanggalSerahTerima') <p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p> @enderror
                    </div>

                    {{-- Input 3: Foto Bukti --}}
                    <div>
                        <label for="buktiFoto" class="mb-1 block text-xs font-semibold text-ink dark:text-zinc-200">
                            Foto bukti terlampir (JPG/PNG/WebP, maks. 5 MB)
                        </label>
                        <p class="text-xs text-mute dark:text-zinc-400 mb-2.5">
                            Ambil foto langsung di hadapan penerima atau pilih file dari galeri HP (maks 5 MB).
                        </p>

                        {{-- Hidden inputs --}}
                        <input id="buktiFoto" x-ref="kameraInput" type="file" accept="image/*" capture
                            x-on:change="proses($event.target)"
                            class="hidden" style="display: none;" />

                        <input id="buktiFotoGaleri" x-ref="galeriInput" type="file" accept="image/jpeg,image/png,image/webp,image/*"
                            x-on:change="proses($event.target)"
                            class="hidden" style="display: none;" />

                        {{-- Selection buttons if no preview --}}
                        <div class="flex gap-2" x-show="!pratinjauUrl && !@js((bool)$buktiFoto)">
                            <button type="button" x-on:click="$refs.kameraInput.click()"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-hairline bg-surface-inset py-3 text-xs font-medium text-ink hover:border-primary hover:text-primary transition min-h-11 cursor-pointer dark:bg-zinc-800 dark:text-zinc-200">
                                <flux:icon.camera class="size-4 text-primary" />
                                Ambil Foto Kamera
                            </button>
                            <button type="button" x-on:click="$refs.galeriInput.click()"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-hairline bg-surface-inset py-3 text-xs font-medium text-ink hover:border-primary hover:text-primary transition min-h-11 cursor-pointer dark:bg-zinc-800 dark:text-zinc-200">
                                <flux:icon.photo class="size-4 text-primary" />
                                Pilih dari Galeri
                            </button>
                        </div>

                        {{-- Compression status --}}
                        <p x-show="sedangKompres" style="display:none" class="mt-2 text-xs font-semibold text-info flex items-center gap-1.5">
                            <flux:icon.arrow-path class="size-3.5 animate-spin" />
                            <span>Mengompres foto...</span>
                        </p>

                        {{-- Photo Preview --}}
                        <div x-show="pratinjauUrl || @js((bool)$buktiFoto)" style="display:none" class="mt-2.5">
                            <div class="relative overflow-hidden rounded-xl border border-hairline bg-surface-inset shadow-xs dark:bg-zinc-800">
                                <template x-if="pratinjauUrl">
                                    <img :src="pratinjauUrl" alt="Pratinjau Foto Bukti" class="max-h-56 w-full object-cover">
                                </template>
                                @if ($buktiFoto)
                                    <div x-show="!pratinjauUrl">
                                        <img src="{{ $buktiFoto->temporaryUrl() }}" alt="Pratinjau Foto Bukti" class="max-h-56 w-full object-cover">
                                    </div>
                                @endif
                                <div class="p-2.5 bg-white dark:bg-surface border-t border-hairline flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 text-xs font-semibold text-primary">
                                        <flux:icon.check-circle class="size-4 shrink-0" />
                                        <span>Foto bukti siap diunggah</span>
                                    </div>
                                    <button type="button" x-on:click="hapusFoto()"
                                        class="inline-flex items-center gap-1 rounded-lg border border-danger/40 bg-danger-soft px-2.5 py-1 text-xs font-semibold text-danger hover:bg-danger/20 transition cursor-pointer">
                                        <flux:icon.trash class="size-3.5" />
                                        Ganti
                                    </button>
                                </div>
                            </div>
                        </div>

                        @error('buktiFoto') <p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-hairline/60">
                    <button type="button" wire:click="batalKonfirmasi"
                        class="min-h-11 rounded-lg border border-hairline bg-surface px-4 py-2.5 text-xs font-medium text-mute hover:text-ink hover:bg-surface-inset transition cursor-pointer dark:bg-zinc-800 dark:text-zinc-200">
                        Batal
                    </button>
                    <button type="button" wire:click="konfirmasi({{ $konfirmasiId }})"
                        class="min-h-11 inline-flex items-center gap-1.5 rounded-lg bg-primary hover:bg-primary-hover px-5 py-2.5 text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer disabled:opacity-60"
                        wire:loading.attr="disabled" wire:target="konfirmasi">
                        <flux:icon.check class="size-4" />
                        Konfirmasi serah terima
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
