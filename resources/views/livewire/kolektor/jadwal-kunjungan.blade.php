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
                <div class="grid grid-cols-3 gap-2 border-t border-hairline pt-3">
                    <button type="button" wire:click="updateStatus({{ $item['profil']->user_id }}, 'dikunjungi')"
                        class="flex min-h-11 items-center justify-center gap-1.5 rounded-full border px-3 text-sm font-medium transition {{ $item['status'] === 'dikunjungi' ? 'border-primary bg-primary-soft text-on-primary-soft' : 'border-hairline-strong bg-white text-body hover:bg-inset dark:bg-surface' }}">
                        <flux:icon.check class="size-4 shrink-0" aria-hidden="true" />
                        Dikunjungi
                    </button>

                    <button type="button" wire:click="updateStatus({{ $item['profil']->user_id }}, 'dilewati')"
                        class="flex min-h-11 items-center justify-center gap-1.5 rounded-full border px-3 text-sm font-medium transition {{ $item['status'] === 'dilewati' ? 'border-hairline-strong bg-inset text-ink' : 'border-hairline-strong bg-white text-body hover:bg-inset dark:bg-surface' }}">
                        Dilewati
                    </button>

                    <button type="button" wire:click="updateStatus({{ $item['profil']->user_id }}, 'tidak_ada')"
                        class="flex min-h-11 items-center justify-center gap-1.5 rounded-full border px-3 text-sm font-medium transition {{ $item['status'] === 'tidak_ada' ? 'border-warning bg-warning-soft text-warning-deep' : 'border-hairline-strong bg-white text-body hover:bg-inset dark:bg-surface' }}">
                        Tidak ada
                    </button>
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
</div>
