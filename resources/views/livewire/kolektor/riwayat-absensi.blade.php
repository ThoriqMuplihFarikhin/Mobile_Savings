<div class="mx-auto max-w-2xl">
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Riwayat Absensi</h1>
        <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Histori kehadiran Anda selama bertugas.</p>
    </div>

    {{-- List --}}
    <div class="space-y-3">
        @forelse($riwayat as $absen)
            <div class="rounded-3xl bg-white dark:bg-zinc-800 shadow-sm border border-zinc-100 dark:border-zinc-700/60 overflow-hidden">
                <button wire:click="selectAbsen({{ $absen->id }})"
                        class="flex w-full items-center gap-4 px-5 py-4 text-left transition hover:bg-zinc-50 dark:hover:bg-zinc-700/40">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-zinc-900 dark:text-white">{{ $absen->tanggal->translatedFormat('l, d M Y') }}</p>
                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                            Masuk {{ \Carbon\Carbon::parse($absen->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($absen->latitude && $absen->longitude)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                GPS
                            </span>
                        @endif
                        <svg class="h-4 w-4 shrink-0 text-zinc-400 transition {{ $selectedAbsenId === $absen->id ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </button>

                {{-- Detail Panel --}}
                @if($selectedAbsenId === $absen->id && $selectedAbsen)
                    <div class="border-t border-zinc-100 dark:border-zinc-700 px-5 py-4 bg-zinc-50 dark:bg-zinc-900/50">
                        <div class="grid grid-cols-2 gap-4">
                            {{-- Foto Selfie --}}
                            <div>
                                <p class="mb-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Foto Selfie</p>
                                @if($selectedAbsen->foto_selfie_path)
                                    <img src="{{ Storage::url($selectedAbsen->foto_selfie_path) }}"
                                         alt="Selfie"
                                         class="h-36 w-36 rounded-2xl object-cover border border-zinc-200 dark:border-zinc-700" />
                                @else
                                    <div class="flex h-36 w-36 items-center justify-center rounded-2xl bg-zinc-200 dark:bg-zinc-700 text-xs text-zinc-500 dark:text-zinc-400">
                                        Tidak ada selfie
                                    </div>
                                @endif
                            </div>

                            {{-- Tanda Tangan --}}
                            <div>
                                <p class="mb-2 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tanda Tangan</p>
                                @if($selectedAbsen->tanda_tangan_path)
                                    <img src="{{ Storage::url($selectedAbsen->tanda_tangan_path) }}"
                                         alt="Tanda Tangan"
                                         class="h-36 w-36 rounded-2xl bg-white dark:bg-zinc-700 object-contain p-2 border border-zinc-200 dark:border-zinc-700" />
                                @else
                                    <div class="flex h-36 w-36 items-center justify-center rounded-2xl bg-zinc-200 dark:bg-zinc-700 text-xs text-zinc-500 dark:text-zinc-400">
                                        Tidak ada TTD
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Detail Info --}}
                        <div class="mt-4 rounded-2xl bg-white dark:bg-zinc-800 p-4 border border-zinc-200 dark:border-zinc-700 space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Waktu Masuk</span>
                                <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ \Carbon\Carbon::parse($selectedAbsen->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i:s') }} WIB</span>
                            </div>
                            @if($selectedAbsen->latitude && $selectedAbsen->longitude)
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Koordinat GPS</span>
                                    <a href="https://www.google.com/maps?q={{ $selectedAbsen->latitude }},{{ $selectedAbsen->longitude }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                        Lihat di Maps
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-3xl bg-white dark:bg-zinc-800 p-12 text-center border border-zinc-100 dark:border-zinc-700/60">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-700 text-zinc-400">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-zinc-900 dark:text-white">Belum Ada Riwayat Absensi</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Riwayat kehadiran Anda akan tampil di sini.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($riwayat->hasPages())
        <div class="mt-6">
            {{ $riwayat->links() }}
        </div>
    @endif
</div>

