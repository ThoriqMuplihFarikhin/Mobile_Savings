<div class="mx-auto max-w-2xl pb-24">
    {{-- Header --}}
    <div class="mb-5">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-ink">Jadwal kunjungan</h1>
                <p class="mt-1 text-sm text-mute">
                    {{ \Illuminate\Support\Carbon::hasFormat($tanggal, 'Y-m-d') ? \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') : 'Tanggal tidak valid' }}
                </p>
            </div>

            <x-ui.tanggal wire:model.live="tanggal"
                class="rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
        </div>

        {{-- Quick Date Shortcuts --}}
        <div class="mt-3 flex gap-2">
            <button type="button" wire:click="setTanggalYesterday"
                class="rounded-full px-3 py-1.5 text-sm font-medium transition {{ $tanggal === now()->subDay()->format('Y-m-d') ? 'bg-primary-soft text-on-primary-soft' : 'bg-inset text-body hover:bg-hairline' }}">
                Kemarin
            </button>
            <button type="button" wire:click="setTanggalToday"
                class="rounded-full px-3 py-1.5 text-sm font-medium transition {{ $tanggal === now()->format('Y-m-d') ? 'bg-primary-soft text-on-primary-soft' : 'bg-inset text-body hover:bg-hairline' }}">
                Hari ini
            </button>
            <button type="button" wire:click="setTanggalTomorrow"
                class="rounded-full px-3 py-1.5 text-sm font-medium transition {{ $tanggal === now()->addDay()->format('Y-m-d') ? 'bg-primary-soft text-on-primary-soft' : 'bg-inset text-body hover:bg-hairline' }}">
                Besok
            </button>
        </div>

        {{-- Tombol Lihat Rute Hari Ini --}}
        <div class="mt-3">
            <button type="button" wire:click="bukaRuteModal"
                class="flex w-full items-center justify-center gap-2 rounded-full border border-primary bg-primary-soft py-2.5 text-xs font-semibold text-on-primary-soft shadow-xs transition hover:bg-primary hover:text-on-primary active:scale-95">
                <flux:icon.map-pin class="size-4" />
                Lihat rute hari ini
            </button>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-xl border border-hairline bg-primary-soft px-4 py-3 text-sm font-medium text-on-primary-soft">
            <flux:icon.check-circle class="size-5 shrink-0" aria-hidden="true" />
            <span class="flex-1">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Progress Card --}}
    <div class="mb-5 rounded-2xl border border-hairline bg-white p-4 dark:bg-surface">
        <div class="mb-2 flex items-center justify-between gap-3">
            <div>
                <span class="text-xs text-mute">Progres kunjungan</span>
                <p class="mt-0.5 text-sm font-semibold text-ink">
                    {{ $dikunjungiCount }} dari {{ $totalNasabahCount }} selesai ({{ $progressPct }}%)
                </p>
            </div>
            <span class="shrink-0 rounded-full bg-primary-soft px-3 py-1 text-xs font-semibold tabular-nums text-on-primary-soft">
                {{ $progressPct }}%
            </span>
        </div>
        <div class="h-2 w-full overflow-hidden rounded-full bg-inset">
            <div class="h-full rounded-full bg-primary transition-all duration-500" style="width: {{ $progressPct }}%"></div>
        </div>

        <div class="mt-3 grid grid-cols-4 gap-2 border-t border-hairline pt-3 text-center">
            <div>
                <p class="text-xs text-mute">Total</p>
                <p class="mt-0.5 text-sm font-semibold tabular-nums text-ink">{{ $totalNasabahCount }}</p>
            </div>
            <div>
                <p class="text-xs text-mute">Selesai</p>
                <p class="mt-0.5 text-sm font-semibold tabular-nums text-primary">{{ $dikunjungiCount }}</p>
            </div>
            <div>
                <p class="text-xs text-mute">Belum</p>
                <p class="mt-0.5 text-sm font-semibold tabular-nums text-info">{{ $belumCount }}</p>
            </div>
            <div>
                <p class="text-xs text-mute">Lewat/absen</p>
                <p class="mt-0.5 text-sm font-semibold tabular-nums text-warning-deep">{{ $dilewatiCount + $tidakAdaCount }}</p>
            </div>
        </div>
    </div>

    {{-- Search & Filter Section --}}
    <div class="mb-5 space-y-3">
        {{-- Search Input --}}
        <div class="flex items-center rounded-xl border border-hairline-strong bg-white px-3 py-3 transition focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 dark:bg-surface">
            <flux:icon.magnifying-glass class="mr-2 size-4 shrink-0 text-mute" aria-hidden="true" />
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Cari nama nasabah atau alamat"
                   class="w-full border-none bg-transparent p-0 text-sm text-ink placeholder:text-subtle focus:outline-none">
            @if($search)
                <button type="button" wire:click="$set('search', '')" aria-label="Hapus pencarian" class="text-mute hover:text-ink">
                    <flux:icon.x-mark class="size-4" />
                </button>
            @endif
        </div>

        {{-- Status Filter Pills --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
            @foreach(['semua' => 'Semua', 'belum' => 'Belum', 'dikunjungi' => 'Dikunjungi', 'dilewati' => 'Dilewati', 'tidak_ada' => 'Tidak ada'] as $key => $label)
                <button type="button" wire:click="$set('filterStatus', '{{ $key }}')"
                    class="shrink-0 rounded-full px-3 py-1.5 text-sm font-medium transition {{ $filterStatus === $key ? 'bg-primary-soft text-on-primary-soft' : 'border border-hairline bg-white text-body hover:bg-inset dark:bg-surface' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Visit List --}}
    <div class="space-y-3">
        @forelse($jadwalHari as $item)
            <div class="rounded-2xl border border-hairline bg-white p-4 transition hover:border-hairline-strong dark:bg-surface">
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-soft text-xs font-semibold text-on-primary-soft" aria-hidden="true">
                            {{ strtoupper(substr($item['profil']->nama, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <h2 class="truncate text-base font-medium text-ink">
                                {{ $item['profil']->nama }}
                            </h2>
                            <p class="mt-0.5 flex items-center gap-1 truncate text-sm text-mute">
                                <flux:icon.map-pin class="size-4 shrink-0" aria-hidden="true" />
                                <span class="truncate">{{ $item['profil']->alamat ?? 'Alamat belum diisi' }}</span>
                            </p>
                            @if($item['profil']->user->no_hp)
                                <a href="tel:{{ $item['profil']->user->no_hp }}" class="mt-0.5 inline-flex items-center gap-1 text-sm text-primary hover:underline">
                                    <flux:icon.device-phone-mobile class="size-4" aria-hidden="true" />
                                    {{ $item['profil']->user->no_hp }}
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Current Status --}}
                    @if($item['status'] === 'belum')
                        <span class="shrink-0 inline-flex items-center rounded-full bg-inset px-2.5 py-1 text-xs font-medium text-body">
                            Belum
                        </span>
                    @else
                        <x-ui.status-chip class="shrink-0" domain="kunjungan" status="{{ $item['status'] }}" />
                    @endif
                </div>

                {{-- Action Buttons Bar --}}
                <div class="border-t border-hairline pt-3">
                    @if($item['status'] === 'dikunjungi')
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary">
                                <flux:icon.check-circle class="size-4" />
                                Kunjungan selesai tercatat
                            </span>
                            <a href="{{ route('kolektor.setoran.index', ['nasabah_id' => $item['profil']->user_id]) }}" wire:navigate
                               class="rounded-full border border-hairline-strong bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:bg-inset dark:bg-surface">
                                Catat lagi
                            </a>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <a href="{{ route('kolektor.setoran.index', ['nasabah_id' => $item['profil']->user_id]) }}" wire:navigate
                               class="flex min-h-11 flex-1 items-center justify-center gap-1.5 rounded-full bg-primary px-3 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-95">
                                Catat setoran
                            </a>

                            <button type="button" wire:click="updateStatus({{ $item['profil']->user_id }}, 'dikunjungi')"
                                class="flex min-h-11 items-center justify-center gap-1 rounded-full border border-hairline-strong bg-white px-3 text-xs font-medium text-body transition hover:bg-inset dark:bg-surface"
                                title="Tandai selesai">
                                <flux:icon.check class="size-4 shrink-0 text-primary" aria-hidden="true" />
                                Selesai
                            </button>

                            <button type="button" wire:click="bukaLewatiModal({{ $item['profil']->user_id }}, '{{ addslashes($item['profil']->nama) }}')"
                                class="flex min-h-11 items-center justify-center rounded-full border border-hairline-strong bg-white px-3 text-xs font-medium text-mute transition hover:bg-inset dark:bg-surface"
                                title="Lewati / Tidak di rumah">
                                Lewati
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-hairline bg-white py-16 text-center dark:bg-surface">
                <flux:icon.calendar class="mx-auto mb-2 size-10 text-mute" aria-hidden="true" />
                <p class="text-sm font-semibold text-ink">Tidak ada jadwal kunjungan</p>
                <p class="mt-1 text-sm text-mute">Coba ganti kata kunci pencarian atau tanggal.</p>
            </div>
        @endforelse
    </div>

    {{-- Modal Sheet Rute Hari Ini --}}
    @if ($showRuteModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-0 sm:items-center sm:p-4"
             role="dialog" aria-modal="true" aria-labelledby="modal-rute-title">
            <div class="fixed inset-0" wire:click="tutupRuteModal" aria-hidden="true"></div>

            <div class="relative w-full max-w-lg rounded-t-[28px] border-t border-hairline bg-surface p-5 sm:p-6 shadow-md sm:rounded-[24px] sm:border dark:bg-surface max-h-[92vh] overflow-y-auto" @click.stop>
                {{-- Mobile drag handle pill --}}
                <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-hairline-strong/60 sm:hidden" aria-hidden="true"></div>

                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-3 border-b border-hairline/80">
                    <h3 id="modal-rute-title" class="text-base font-semibold text-ink dark:text-zinc-100">Rute hari ini</h3>
                    <button type="button" wire:click="tutupRuteModal" aria-label="Tutup"
                            class="flex size-8 shrink-0 items-center justify-center rounded-full bg-surface-inset text-mute transition hover:bg-hairline hover:text-ink active:scale-95 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-400">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </div>

                {{-- Peta Rute SVG --}}
                <div class="my-3 overflow-hidden rounded-2xl border border-hairline bg-primary-soft/30 p-2">
                    <svg viewBox="0 0 320 190" class="block w-full" role="img" aria-label="Peta rute kunjungan">
                        {{-- Grid lines --}}
                        @for ($x = 0; $x <= 320; $x += 40)
                            <line x1="{{ $x }}" y1="0" x2="{{ $x }}" y2="190" stroke="#d8e6d3" stroke-width="1" />
                        @endfor
                        @for ($y = 0; $y <= 190; $y += 38)
                            <line x1="0" y1="{{ $y }}" x2="320" y2="{{ $y }}" stroke="#d8e6d3" stroke-width="1" />
                        @endfor

                        @php
                            $presetPoints = [
                                [30, 150], [80, 108], [140, 136], [196, 80],
                                [132, 48], [248, 58], [292, 112], [260, 160],
                                [190, 165], [100, 165],
                            ];
                            $pointsData = [];
                            $pathD = '';
                            $nextIndex = -1;
                            foreach ($jadwalHari as $idx => $item) {
                                if ($nextIndex === -1 && $item['status'] === 'belum') {
                                    $nextIndex = $idx;
                                }
                                $coord = $presetPoints[$idx % count($presetPoints)];
                                $pointsData[] = [
                                    'x' => $coord[0],
                                    'y' => $coord[1],
                                    'status' => $item['status'],
                                    'nama' => $item['profil']->nama ?? 'Nasabah',
                                ];
                                $pathD .= ($idx === 0 ? 'M' : 'L') . $coord[0] . ' ' . $coord[1];
                            }
                        @endphp

                        @if ($pathD)
                            {{-- Connecting Path --}}
                            <path d="{{ $pathD }}" fill="none" stroke="#15803d" stroke-width="3" stroke-dasharray="7 6" stroke-linecap="round" />
                        @endif

                        {{-- Nodes --}}
                        @foreach ($pointsData as $idx => $pt)
                            @php
                                $isDone = $pt['status'] === 'dikunjungi';
                                $isSkip = in_array($pt['status'], ['dilewati', 'tidak_ada'], true);
                                $fillColor = $isDone ? '#15803d' : ($isSkip ? '#f59e0b' : '#ffffff');
                                $textColor = $isDone ? '#ffffff' : '#14532d';
                            @endphp

                            @if ($idx === $nextIndex)
                                <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="18" fill="none" stroke="#15803d" stroke-width="2" opacity="0.4" />
                            @endif

                            <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="12" fill="{{ $fillColor }}" stroke="#15803d" stroke-width="2.5" />
                            <text x="{{ $pt['x'] }}" y="{{ $pt['y'] + 4 }}" text-anchor="middle" font-size="12" font-weight="600" fill="{{ $textColor }}">
                                {{ $idx + 1 }}
                            </text>
                        @endforeach
                    </svg>
                </div>

                {{-- Status Info --}}
                <div class="flex items-center justify-between text-xs text-mute">
                    <span>
                        @if ($nextIndex >= 0 && isset($pointsData[$nextIndex]))
                            Berikutnya: <strong class="font-medium text-ink">{{ $pointsData[$nextIndex]['nama'] }}</strong>
                        @else
                            <span class="font-medium text-primary">Semua titik selesai</span>
                        @endif
                    </span>
                    <span class="font-semibold tabular-nums text-ink">{{ count($jadwalHari) }} titik</span>
                </div>

                {{-- Aksi Maps --}}
                <div class="mt-4 flex gap-2">
                    <a href="https://www.google.com/maps" target="_blank" rel="noopener noreferrer"
                       class="flex flex-1 items-center justify-center gap-1.5 rounded-full bg-primary py-2.5 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-deep active:scale-95">
                        <flux:icon.map-pin class="size-4" />
                        Buka di Maps
                    </a>
                    <button type="button" wire:click="tutupRuteModal"
                            class="rounded-full border border-hairline-strong bg-white px-5 py-2.5 text-xs font-semibold text-body transition hover:bg-inset dark:bg-surface">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Sheet Nasabah Tidak di Rumah (Lewati Kunjungan) --}}
    @if ($showLewatiModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-0 sm:items-center sm:p-4"
             role="dialog" aria-modal="true" aria-labelledby="modal-lewati-title">
            <div class="fixed inset-0" wire:click="tutupLewatiModal" aria-hidden="true"></div>

            <div class="relative w-full max-w-lg rounded-t-[28px] border-t border-hairline bg-surface p-5 sm:p-6 shadow-md sm:rounded-[24px] sm:border dark:bg-surface max-h-[92vh] overflow-y-auto" @click.stop>
                {{-- Mobile drag handle pill --}}
                <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-hairline-strong/60 sm:hidden" aria-hidden="true"></div>

                <div class="flex items-center justify-between pb-3 border-b border-hairline/80">
                    <h3 id="modal-lewati-title" class="text-base font-semibold text-ink dark:text-zinc-100">
                        Nasabah tidak di rumah?
                    </h3>
                    <button type="button" wire:click="tutupLewatiModal" aria-label="Tutup"
                            class="flex size-8 shrink-0 items-center justify-center rounded-full bg-surface-inset text-mute transition hover:bg-hairline hover:text-ink active:scale-95 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-400">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </div>

                @if($selectedNasabahNama)
                    <p class="mt-2.5 text-xs font-semibold text-primary dark:text-emerald-400">Nasabah: {{ $selectedNasabahNama }}</p>
                @endif
                <p class="mt-1 text-xs text-mute dark:text-zinc-400">
                    Pilih alasannya. Kunjungan akan ditandai dilewati dan dapat dijadwalkan ulang besok.
                </p>

                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach(['Tidak di rumah', 'Minta ditunda', 'Alamat tidak ditemukan'] as $alasan)
                        <button type="button" wire:click="$set('alasanLewati', '{{ $alasan }}')"
                                class="rounded-full px-4 py-2 text-xs font-medium transition {{ $alasanLewati === $alasan ? 'border border-primary bg-primary-soft text-on-primary-soft font-semibold dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-700' : 'border border-hairline bg-surface text-body hover:bg-surface-inset dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700' }}">
                            {{ $alasan }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-6 flex gap-2.5">
                    <button type="button" wire:click="simpanLewati"
                            class="flex h-11 min-h-[44px] flex-1 items-center justify-center rounded-full bg-primary px-4 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-95">
                        Simpan alasan
                    </button>
                    <button type="button" wire:click="tutupLewatiModal"
                            class="h-11 min-h-[44px] rounded-full border border-hairline-strong bg-surface px-5 text-xs font-semibold text-body transition hover:bg-surface-inset active:scale-95 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-700">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
