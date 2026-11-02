<div class="mx-auto max-w-2xl pb-24">
    {{-- Top Header with Back button --}}
    <div class="mb-5 flex items-center gap-3">
        <a href="{{ route('kolektor.absen.index') }}" wire:navigate
           class="flex size-10 shrink-0 items-center justify-center rounded-full bg-white border border-hairline-strong text-ink transition hover:bg-inset active:scale-95 dark:bg-surface"
           aria-label="Kembali ke absen masuk">
            <flux:icon.arrow-left class="size-5" />
        </a>
        <div class="min-w-0 flex-1">
            <h1 class="text-xl font-semibold tracking-tight text-ink">Riwayat absensi</h1>
            <p class="text-xs text-mute truncate">Histori kehadiran Anda selama bertugas di lapangan.</p>
        </div>
    </div>

    {{-- List Card (Prototype #ra-l style) --}}
    <div class="overflow-hidden rounded-2xl border border-hairline bg-white shadow-xs dark:bg-surface divide-y divide-hairline">
        @forelse($riwayat as $absen)
            @php
                $jamMasuk = \Carbon\Carbon::parse($absen->waktu_masuk)->setTimezone('Asia/Jakarta');
                $isTerlambat = $jamMasuk->format('H:i') > '08:30';
                $isSelected = $selectedAbsenId === $absen->id;
            @endphp
            <div>
                <button type="button" wire:click="selectAbsen({{ $absen->id }})"
                        class="flex min-h-16 w-full items-center justify-between gap-3 p-3.5 text-left transition hover:bg-inset {{ $isSelected ? 'bg-inset/70' : '' }}">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft" aria-hidden="true">
                            <flux:icon.camera class="size-4" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-ink">
                                {{ $absen->tanggal->translatedFormat('d M Y') }} · masuk {{ $jamMasuk->format('H.i') }}
                            </p>
                            <p class="mt-0.5 truncate text-xs text-mute">
                                GPS {{ $absen->latitude ? number_format($absen->latitude, 4).', '.number_format($absen->longitude, 4) : '-' }} · selfie dan tanda tangan
                            </p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $isTerlambat ? 'bg-warning-soft text-warning-deep' : 'bg-primary-soft text-on-primary-soft' }}">
                            {{ $isTerlambat ? 'Terlambat' : 'Hadir' }}
                        </span>
                        <flux:icon.chevron-down class="size-4 text-mute transition {{ $isSelected ? 'rotate-180 text-primary' : '' }}" />
                    </div>
                </button>

                {{-- Detail Sheet (#sh-f style from prototype) --}}
                @if($isSelected && $selectedAbsen)
                    <div class="border-t border-hairline bg-inset p-4 space-y-3.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-mute">
                                Masuk {{ \Carbon\Carbon::parse($selectedAbsen->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                                @if($selectedAbsen->waktu_keluar)
                                    · Keluar {{ \Carbon\Carbon::parse($selectedAbsen->waktu_keluar)->setTimezone('Asia/Jakarta')->format('H:i') }} WIB
                                @endif
                            </span>
                            <span class="font-semibold {{ $isTerlambat ? 'text-warning-deep' : 'text-primary' }}">
                                {{ $isTerlambat ? 'Terlambat (> 08:30)' : 'Tepat Waktu' }}
                            </span>
                        </div>

                        {{-- 2 Side-by-side Boxes: Foto Selfie & Tanda Tangan --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-hairline bg-white p-2.5 text-center dark:bg-surface">
                                <span class="block text-xs font-medium text-mute mb-1.5">Foto selfie</span>
                                @if($selectedAbsen->foto_selfie_path)
                                    <img src="{{ route('absensi.foto', [$selectedAbsen, 'selfie']) }}"
                                         alt="Foto selfie"
                                         class="mx-auto h-28 w-28 rounded-lg border border-hairline object-cover [transform:scaleX(-1)]" />
                                @else
                                    <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-lg bg-inset text-xs text-mute">
                                        Tidak ada selfie
                                    </div>
                                @endif
                            </div>

                            <div class="rounded-xl border border-hairline bg-white p-2.5 text-center dark:bg-surface">
                                <span class="block text-xs font-medium text-mute mb-1.5">Tanda tangan</span>
                                @if($selectedAbsen->tanda_tangan_path)
                                    <img src="{{ route('absensi.foto', [$selectedAbsen, 'tanda-tangan']) }}"
                                         alt="Tanda tangan"
                                         class="mx-auto h-28 w-28 rounded-lg border border-hairline object-contain p-1 bg-white dark:bg-surface" />
                                @else
                                    <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-lg bg-inset text-xs text-mute">
                                        Tidak ada ttd
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- GPS Info Card --}}
                        <div class="rounded-xl border border-hairline bg-white p-3 space-y-2 text-xs dark:bg-surface">
                            <div class="flex items-center justify-between">
                                <span class="text-mute">Lokasi (GPS)</span>
                                <span class="font-semibold tabular-nums text-ink">
                                    {{ $selectedAbsen->latitude ? $selectedAbsen->latitude . ', ' . $selectedAbsen->longitude : '-' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-mute">Akurasi GPS</span>
                                <span class="font-semibold tabular-nums text-ink">
                                    {{ $selectedAbsen->akurasi ? round($selectedAbsen->akurasi) . ' m' : '-' }}
                                </span>
                            </div>
                            @if($selectedAbsen->latitude && $selectedAbsen->longitude)
                                <div class="pt-2 border-t border-hairline flex justify-end">
                                    <a href="https://www.google.com/maps?q={{ $selectedAbsen->latitude }},{{ $selectedAbsen->longitude }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1 font-semibold text-primary hover:underline">
                                        Buka di Google Maps &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="p-12 text-center">
                <flux:icon.calendar-days class="mx-auto size-8 text-mute mb-2" aria-hidden="true" />
                <p class="text-sm font-semibold text-ink">Belum ada riwayat absensi</p>
                <p class="mt-0.5 text-xs text-mute">Histori kehadiran Anda di lapangan akan muncul di sini.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($riwayat->hasPages())
        <div class="mt-5">
            {{ $riwayat->links() }}
        </div>
    @endif
</div>
