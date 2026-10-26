<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink dark:text-white">Serah Terima Paket</h1>
            <p class="mt-0.5 text-xs text-subtle dark:text-subtle">Konfirmasi penyerahan paket kepada nasabah beserta bukti foto.</p>
        </div>
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-soft dark:bg-primary-soft/40 text-primary dark:text-primary border border-primary dark:border-primary/50">
            <flux:icon.hand-raised class="size-5" />
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-lg bg-primary-soft dark:bg-primary-soft/40 p-4 text-xs font-semibold text-primary dark:text-primary border border-primary dark:border-primary/40">
            <flux:icon.check-circle class="size-5 shrink-0 text-primary dark:text-primary" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 rounded-lg bg-danger-soft dark:bg-dark-danger-soft p-4 text-xs font-semibold text-danger-deep dark:text-dark-danger-text border border-danger dark:border-danger/40">
            <flux:icon.exclamation-triangle class="size-5 shrink-0 text-danger dark:text-dark-danger-text" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Filter Status --}}
    <div class="flex flex-wrap gap-2">
        @php
            $daftarFilter = [
                'siap' => 'Siap Diserahkan ('.$jumlahSiap.')',
                'belum' => 'Belum Diserahkan',
                'sudah' => 'Sudah Diterima',
            ];
        @endphp
        @foreach ($daftarFilter as $nilai => $label)
            <button type="button" wire:click="pilihFilter('{{ $nilai }}')"
                class="min-h-11 rounded-md px-4 py-2 text-xs font-semibold border transition cursor-pointer {{ $filter === $nilai
                    ? 'bg-zinc-900 dark:bg-white text-white dark:text-ink border-ink dark:border-white'
                    : 'bg-white dark:bg-inset text-mute dark:text-body border-hairline dark:border-hairline-strong hover:border-hairline-strong dark:hover:border-hairline-strong' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Daftar Kepesertaan --}}
    <div class="space-y-4">
        @forelse($kepesertaan as $item)
            <div class="rounded-lg bg-white dark:bg-inset overflow-hidden border border-hairline/80 dark:border-hairline/80">
                <div class="p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-ink dark:text-white truncate">{{ $item->nasabah?->name ?? '-' }}</h3>
                            <p class="mt-0.5 text-xs text-subtle dark:text-subtle truncate">{{ $item->produk?->nama ?? '-' }} · Mulai {{ $item->tanggal_mulai_ikut?->translatedFormat('d M Y') }}</p>
                        </div>
                        @if ($item->status_serah_terima === 'sudah_diterima')
                            <span class="shrink-0 rounded-full bg-primary-soft dark:bg-primary-soft/50 px-3 py-1 text-xs font-medium text-primary dark:text-primary border border-primary dark:border-primary/60">
                                Sudah Diterima
                            </span>
                        @else
                            <span class="shrink-0 rounded-full bg-warning-soft dark:bg-warning-soft/50 px-3 py-1 text-xs font-medium text-warning-deep dark:text-warning-deep border border-warning dark:border-warning/60">
                                Belum Diserahkan
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        <div class="rounded-lg bg-inset dark:bg-surface/50 p-3 border border-hairline/60 dark:border-hairline-strong/40">
                            <p class="text-xs font-medium text-subtle dark:text-subtle">Metode</p>
                            <p class="mt-0.5 text-xs font-semibold text-ink dark:text-white truncate">
                                {{ $item->metode_pengambilan === 'diantar_kolektor' ? 'Diantar Kolektor' : ($item->metode_pengambilan === 'ambil_sendiri' ? 'Ambil Sendiri' : 'Belum dipilih') }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-inset dark:bg-surface/50 p-3 border border-hairline/60 dark:border-hairline-strong/40">
                            <p class="text-xs font-medium text-subtle dark:text-subtle">Boleh Cair</p>
                            <p class="mt-0.5 text-xs font-semibold text-ink dark:text-white truncate">
                                {{ $item->produk?->tanggal_boleh_cair?->translatedFormat('d M Y') ?? '-' }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-inset dark:bg-surface/50 p-3 border border-hairline/60 dark:border-hairline-strong/40">
                            <p class="text-xs font-medium text-subtle dark:text-subtle">Tunggakan</p>
                            <p class="mt-0.5 text-xs font-semibold {{ $item->tunggakan > 0 ? 'text-danger dark:text-dark-danger-text' : 'text-primary dark:text-primary' }}">
                                {{ $item->tunggakan > 0 ? $item->tunggakan.' hari' : 'Lunas' }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-inset dark:bg-surface/50 p-3 border border-hairline/60 dark:border-hairline-strong/40">
                            <p class="text-xs font-medium text-subtle dark:text-subtle">Serah Terima</p>
                            <p class="mt-0.5 text-xs font-semibold text-ink dark:text-white truncate">
                                {{ $item->status_serah_terima === 'sudah_diterima' ? $item->tanggal_serah_terima?->translatedFormat('d M Y') : 'Menunggu' }}
                            </p>
                        </div>
                    </div>

                    @if ($item->status_serah_terima === 'sudah_diterima')
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-inset dark:bg-surface/50 p-3.5 border border-hairline/60 dark:border-hairline-strong/40">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-subtle dark:text-subtle">Diterima oleh</p>
                                <p class="mt-0.5 text-xs font-semibold text-ink dark:text-white truncate">{{ $item->diterima_oleh ?? '-' }}</p>
                            </div>
                            @if ($item->bukti_foto_url)
                                <a href="{{ route('serah-terima.bukti', $item) }}" target="_blank"
                                   class="rounded-lg border border-hairline-strong dark:border-hairline-strong px-3 py-2 text-xs font-semibold text-body dark:text-body hover:bg-inset dark:hover:bg-inset transition">
                                    Lihat Foto Bukti
                                </a>
                            @endif
                        </div>
                    @elseif ($filter !== 'sudah')
                        <div class="flex justify-end border-t border-inset dark:border-hairline-strong/60 pt-4">
                            <button type="button" wire:click="bukaKonfirmasi({{ $item->id }})"
                                class="min-h-11 rounded-lg bg-primary hover:bg-primary-hover px-4 py-2.5 text-xs font-semibold text-on-primary transition cursor-pointer">
                                Konfirmasi Serah Terima
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Form Konfirmasi --}}
                @if ($konfirmasiId === $item->id)
                    <div class="border-t border-inset dark:border-hairline-strong/60 bg-inset dark:bg-surface/50 p-5 space-y-4">
                        <div wire:loading wire:target="konfirmasi" class="text-xs font-semibold text-info dark:text-info">
                            Memproses konfirmasi...
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <label for="diterimaOleh" class="mb-1.5 block text-xs font-medium text-subtle dark:text-subtle">Diterima Oleh</label>
                                <input id="diterimaOleh" type="text" wire:model="diterimaOleh"
                                    class="w-full rounded-md border border-hairline-strong dark:border-hairline-strong bg-white dark:bg-inset px-3 py-2.5 text-xs text-ink dark:text-white focus:border-ink focus:ring-ink"
                                    placeholder="Nama penerima / petugas" />
                                @error('diterimaOleh') <p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="tanggalSerahTerima" class="mb-1.5 block text-xs font-medium text-subtle dark:text-subtle">Tanggal Serah Terima</label>
                                <x-ui.tanggal wire:model="tanggalSerahTerima" :max="now()->toDateString()" id="tanggalSerahTerima"
                                    class="w-full rounded-lg border border-hairline-strong dark:border-hairline-strong bg-white dark:bg-inset px-3 py-2.5 text-xs text-ink dark:text-white focus:border-ink focus:ring-ink" />
                                @error('tanggalSerahTerima') <p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p> @enderror
                            </div>
                            <div x-data="kompresFoto">
                                <label for="buktiFoto" class="mb-1.5 block text-xs font-medium text-subtle dark:text-subtle">Foto Bukti (JPG/PNG/WebP, maks 5 MB)</label>
                                <input id="buktiFoto" type="file" accept="image/*" capture
                                    x-on:change="proses($event.target)"
                                    class="w-full rounded-md border border-hairline-strong dark:border-hairline-strong bg-white dark:bg-inset px-3 py-2 text-xs text-mute dark:text-body focus:border-ink focus:ring-ink file:mr-3 file:rounded-md file:border-0 file:bg-hairline file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-body dark:file:bg-inset dark:file:text-body" />
                                <p x-show="sedangKompres" style="display:none" class="mt-1 text-xs font-semibold text-info dark:text-info">Mengompres foto...</p>
                                @error('buktiFoto') <p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="batalKonfirmasi"
                                class="min-h-11 rounded-md border border-hairline-strong dark:border-hairline-strong px-4 py-2.5 text-xs font-semibold text-mute dark:text-body hover:bg-inset dark:hover:bg-inset transition cursor-pointer">
                                Batal
                            </button>
                            <button type="button" wire:click="konfirmasi({{ $item->id }})"
                                class="min-h-11 rounded-lg bg-primary hover:bg-primary-hover px-4 py-2.5 text-xs font-semibold text-on-primary transition cursor-pointer disabled:opacity-60"
                                wire:loading.attr="disabled" wire:target="konfirmasi">
                                Simpan Konfirmasi
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-lg bg-white dark:bg-inset p-10 text-center border border-hairline/80 dark:border-hairline/80">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-lg bg-inset dark:bg-inset/60 text-subtle">
                    <flux:icon.hand-raised class="size-7 text-subtle" />
                </div>
                <h3 class="mt-4 text-sm font-semibold text-ink dark:text-white">Tidak Ada Kepesertaan</h3>
                <p class="mt-1 text-xs text-subtle dark:text-subtle">
                    @if ($filter === 'siap')
                        Belum ada paket yang siap diserahkan.
                    @elseif ($filter === 'belum')
                        Semua paket sudah diserahkan kepada nasabah.
                    @else
                        Belum ada paket yang sudah diserahkan.
                    @endif
                </p>
            </div>
        @endforelse
    </div>
</div>
