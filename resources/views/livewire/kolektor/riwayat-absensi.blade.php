<div class="mx-auto max-w-2xl pb-8">
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Riwayat absensi</h1>
        <p class="mt-1 text-sm text-mute">Histori kehadiran Anda selama bertugas.</p>
    </div>

    {{-- List --}}
    <div class="space-y-3">
        @forelse($riwayat as $absen)
            <div class="overflow-hidden rounded-2xl border border-hairline bg-white dark:bg-surface">
                <button wire:click="selectAbsen({{ $absen->id }})"
                        class="flex min-h-16 w-full items-center gap-4 px-4 py-4 text-left transition hover:bg-inset">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft" aria-hidden="true">
                        <flux:icon.calendar-days class="size-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-base font-medium text-ink">{{ $absen->tanggal->translatedFormat('l, d M Y') }}</p>
                        <p class="mt-0.5 text-sm tabular-nums text-mute">
                            Masuk {{ \Carbon\Carbon::parse($absen->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        @if($absen->latitude && $absen->longitude)
                            <span class="inline-flex items-center gap-1 rounded-full bg-primary-soft px-2.5 py-1 text-xs font-medium text-on-primary-soft">
                                <flux:icon.map-pin class="size-3.5" aria-hidden="true" />
                                GPS
                            </span>
                        @endif
                        <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition {{ $selectedAbsenId === $absen->id ? 'rotate-90' : '' }}" aria-hidden="true" />
                    </div>
                </button>

                {{-- Detail Panel --}}
                @if($selectedAbsenId === $absen->id && $selectedAbsen)
                    <div class="border-t border-hairline bg-inset px-4 py-4">
                        <div class="grid grid-cols-2 gap-4">
                            {{-- Foto Selfie --}}
                            <div>
                                <p class="mb-2 text-sm font-medium text-body">Foto selfie</p>
                                @if($selectedAbsen->foto_selfie_path)
                                    <img src="{{ route('absensi.foto', [$selectedAbsen, 'selfie']) }}"
                                         alt="Selfie"
                                         class="h-36 w-36 rounded-xl border border-hairline object-cover" />
                                @else
                                    <div class="flex h-36 w-36 items-center justify-center rounded-xl border border-hairline bg-white text-sm text-mute dark:bg-surface">
                                        Tidak ada selfie
                                    </div>
                                @endif
                            </div>

                            {{-- Tanda Tangan --}}
                            <div>
                                <p class="mb-2 text-sm font-medium text-body">Tanda tangan</p>
                                @if($selectedAbsen->tanda_tangan_path)
                                    <img src="{{ route('absensi.foto', [$selectedAbsen, 'tanda-tangan']) }}"
                                         alt="Tanda tangan"
                                         class="h-36 w-36 rounded-xl border border-hairline bg-white object-contain p-2 dark:bg-surface" />
                                @else
                                    <div class="flex h-36 w-36 items-center justify-center rounded-xl border border-hairline bg-white text-sm text-mute dark:bg-surface">
                                        Tidak ada tanda tangan
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Detail Info --}}
                        <div class="mt-4 space-y-2 rounded-xl border border-hairline bg-white p-4 dark:bg-surface">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm text-mute">Waktu masuk</span>
                                <span class="text-sm font-semibold tabular-nums text-ink">{{ \Carbon\Carbon::parse($selectedAbsen->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i:s') }} WIB</span>
                            </div>
                            @if($selectedAbsen->latitude && $selectedAbsen->longitude)
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm text-mute">Koordinat GPS</span>
                                    <a href="https://www.google.com/maps?q={{ $selectedAbsen->latitude }},{{ $selectedAbsen->longitude }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline">
                                        Lihat di Maps
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-hairline bg-white p-12 text-center dark:bg-surface">
                <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-inset text-mute" aria-hidden="true">
                    <flux:icon.calendar-days class="size-7" />
                </div>
                <h2 class="mt-4 text-base font-semibold text-ink">Belum ada riwayat absensi</h2>
                <p class="mt-1 text-sm text-mute">Riwayat kehadiran Anda akan tampil di sini.</p>
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
