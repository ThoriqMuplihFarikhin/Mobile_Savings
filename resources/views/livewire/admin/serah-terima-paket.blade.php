<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Serah Terima Paket</h1>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Konfirmasi penyerahan paket kepada nasabah beserta bukti foto.</p>
        </div>
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 shadow-2xs border border-emerald-100 dark:border-emerald-900/50">
            <flux:icon.hand-raised class="size-5" />
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 p-4 text-xs font-semibold text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600 dark:text-emerald-400" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 rounded-2xl bg-rose-50 dark:bg-rose-950/40 p-4 text-xs font-semibold text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40">
            <flux:icon.exclamation-triangle class="size-5 shrink-0 text-rose-600 dark:text-rose-400" />
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
                class="rounded-full px-4 py-2 text-[11px] font-bold border transition cursor-pointer {{ $filter === $nilai
                    ? 'bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 border-zinc-900 dark:border-white'
                    : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-500' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Daftar Kepesertaan --}}
    <div class="space-y-4">
        @forelse($kepesertaan as $item)
            <div class="rounded-3xl bg-white dark:bg-zinc-800 overflow-hidden shadow-xs border border-zinc-200/80 dark:border-zinc-700/80">
                <div class="p-5 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white truncate">{{ $item->nasabah?->name ?? '-' }}</h3>
                            <p class="mt-0.5 text-[11px] text-zinc-500 dark:text-zinc-400 truncate">{{ $item->produk?->nama ?? '-' }} · Mulai {{ $item->tanggal_mulai_ikut?->translatedFormat('d M Y') }}</p>
                        </div>
                        @if ($item->status_serah_terima === 'sudah_diterima')
                            <span class="shrink-0 rounded-full bg-emerald-50 dark:bg-emerald-950/50 px-3 py-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                Sudah Diterima
                            </span>
                        @else
                            <span class="shrink-0 rounded-full bg-amber-50 dark:bg-amber-950/50 px-3 py-1 text-[10px] font-bold text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                Belum Diserahkan
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-900/50 p-3 border border-zinc-200/60 dark:border-zinc-700/40">
                            <p class="text-[10px] font-bold uppercase text-zinc-400 dark:text-zinc-500">Metode</p>
                            <p class="mt-0.5 text-xs font-bold text-zinc-900 dark:text-white truncate">
                                {{ $item->metode_pengambilan === 'diantar_kolektor' ? 'Diantar Kolektor' : ($item->metode_pengambilan === 'ambil_sendiri' ? 'Ambil Sendiri' : 'Belum dipilih') }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-900/50 p-3 border border-zinc-200/60 dark:border-zinc-700/40">
                            <p class="text-[10px] font-bold uppercase text-zinc-400 dark:text-zinc-500">Boleh Cair</p>
                            <p class="mt-0.5 text-xs font-bold text-zinc-900 dark:text-white truncate">
                                {{ $item->produk?->tanggal_boleh_cair?->translatedFormat('d M Y') ?? '-' }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-900/50 p-3 border border-zinc-200/60 dark:border-zinc-700/40">
                            <p class="text-[10px] font-bold uppercase text-zinc-400 dark:text-zinc-500">Tunggakan</p>
                            <p class="mt-0.5 text-xs font-bold {{ $item->tunggakan > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ $item->tunggakan > 0 ? $item->tunggakan.' hari' : 'Lunas' }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-900/50 p-3 border border-zinc-200/60 dark:border-zinc-700/40">
                            <p class="text-[10px] font-bold uppercase text-zinc-400 dark:text-zinc-500">Serah Terima</p>
                            <p class="mt-0.5 text-xs font-bold text-zinc-900 dark:text-white truncate">
                                {{ $item->status_serah_terima === 'sudah_diterima' ? $item->tanggal_serah_terima?->translatedFormat('d M Y') : 'Menunggu' }}
                            </p>
                        </div>
                    </div>

                    @if ($item->status_serah_terima === 'sudah_diterima')
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-zinc-50 dark:bg-zinc-900/50 p-3.5 border border-zinc-200/60 dark:border-zinc-700/40">
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase text-zinc-400 dark:text-zinc-500">Diterima oleh</p>
                                <p class="mt-0.5 text-xs font-bold text-zinc-900 dark:text-white truncate">{{ $item->diterima_oleh ?? '-' }}</p>
                            </div>
                            @if ($item->bukti_foto_url)
                                <a href="{{ route('serah-terima.bukti', $item) }}" target="_blank"
                                   class="rounded-xl border border-zinc-300 dark:border-zinc-600 px-3 py-2 text-[11px] font-bold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                                    Lihat Foto Bukti
                                </a>
                            @endif
                        </div>
                    @elseif ($filter !== 'sudah')
                        <div class="flex justify-end border-t border-zinc-100 dark:border-zinc-700/60 pt-4">
                            <button type="button" wire:click="bukaKonfirmasi({{ $item->id }})"
                                class="rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 text-xs font-bold text-white transition cursor-pointer">
                                Konfirmasi Serah Terima
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Form Konfirmasi --}}
                @if ($konfirmasiId === $item->id)
                    <div class="border-t border-zinc-100 dark:border-zinc-700/60 bg-zinc-50 dark:bg-zinc-900/50 p-5 space-y-4">
                        <div wire:loading wire:target="konfirmasi" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400">
                            Memproses konfirmasi...
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <label for="diterimaOleh" class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1.5">Diterima Oleh</label>
                                <input id="diterimaOleh" type="text" wire:model="diterimaOleh"
                                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2.5 text-xs text-zinc-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500"
                                    placeholder="Nama penerima / petugas" />
                                @error('diterimaOleh') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="tanggalSerahTerima" class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1.5">Tanggal Serah Terima</label>
                                <x-ui.tanggal wire:model="tanggalSerahTerima" :max="now()->toDateString()" id="tanggalSerahTerima"
                                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2.5 text-xs text-zinc-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500" />
                                @error('tanggalSerahTerima') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="buktiFoto" class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1.5">Foto Bukti (JPG/PNG, maks 2 MB)</label>
                                <input id="buktiFoto" type="file" wire:model="buktiFoto" accept="image/*"
                                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-xs text-zinc-600 dark:text-zinc-300 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-200 file:px-3 file:py-1.5 file:text-[11px] file:font-bold file:text-zinc-700 dark:file:bg-zinc-700 dark:file:text-zinc-200" />
                                @error('buktiFoto') <p class="mt-1 text-[11px] font-semibold text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="batalKonfirmasi"
                                class="rounded-xl border border-zinc-300 dark:border-zinc-600 px-4 py-2.5 text-xs font-bold text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition cursor-pointer">
                                Batal
                            </button>
                            <button type="button" wire:click="konfirmasi({{ $item->id }})"
                                class="rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 text-xs font-bold text-white transition cursor-pointer disabled:opacity-60"
                                wire:loading.attr="disabled" wire:target="konfirmasi">
                                Simpan Konfirmasi
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-3xl bg-white dark:bg-zinc-800 p-10 text-center border border-zinc-200/80 dark:border-zinc-700/80 shadow-xs">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-700/60 text-zinc-400">
                    <flux:icon.hand-raised class="size-7 text-zinc-400" />
                </div>
                <h3 class="mt-4 text-sm font-bold text-zinc-900 dark:text-white">Tidak Ada Kepesertaan</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
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
