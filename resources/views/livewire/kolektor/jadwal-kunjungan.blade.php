<div class="mx-auto max-w-2xl pb-24">
    {{-- Header --}}
    <div class="mb-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-2xl">Jadwal Kunjungan</h1>
                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                    {{ \Illuminate\Support\Carbon::hasFormat($tanggal, 'Y-m-d') ? \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') : 'Tanggal tidak valid' }}
                </p>
            </div>
            
            <input type="date" wire:model.live="tanggal"
                class="rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs text-zinc-900 dark:text-white shadow-2xs focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/10" />
        </div>

        {{-- Quick Date Shortcuts --}}
        <div class="mt-3 flex gap-1.5">
            <button type="button" wire:click="setTanggalYesterday"
                class="rounded-lg px-2.5 py-1 text-[11px] font-semibold transition {{ $tanggal === now()->subDay()->format('Y-m-d') ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                Kemarin
            </button>
            <button type="button" wire:click="setTanggalToday"
                class="rounded-lg px-2.5 py-1 text-[11px] font-semibold transition {{ $tanggal === now()->format('Y-m-d') ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                Hari Ini
            </button>
            <button type="button" wire:click="setTanggalTomorrow"
                class="rounded-lg px-2.5 py-1 text-[11px] font-semibold transition {{ $tanggal === now()->addDay()->format('Y-m-d') ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                Besok
            </button>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-2xl bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 shadow-2xs">
            <flux:icon.check-circle class="size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
            <span class="flex-1">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Progress Card --}}
    <div class="mb-5 rounded-2xl bg-white p-4 shadow-sm border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
        <div class="flex items-center justify-between mb-2">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Progres Kunjungan</span>
                <p class="text-sm font-extrabold text-zinc-900 dark:text-white mt-0.5">
                    {{ $dikunjungiCount }} dari {{ $totalNasabahCount }} Selesai ({{ $progressPct }}%)
                </p>
            </div>
            <span class="rounded-full bg-emerald-50 dark:bg-emerald-950/60 px-3 py-1 font-mono text-xs font-bold text-emerald-700 dark:text-emerald-300">
                {{ $progressPct }}%
            </span>
        </div>
        <div class="h-2 w-full rounded-full bg-zinc-100 dark:bg-zinc-700 overflow-hidden">
            <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $progressPct }}%"></div>
        </div>

        <div class="mt-3 grid grid-cols-4 gap-2 text-center pt-2 border-t border-zinc-100 dark:border-zinc-700/50">
            <div>
                <p class="text-[10px] text-zinc-400">Total</p>
                <p class="text-xs font-bold text-zinc-900 dark:text-white">{{ $totalNasabahCount }}</p>
            </div>
            <div>
                <p class="text-[10px] text-emerald-600 dark:text-emerald-400">Selesai</p>
                <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $dikunjungiCount }}</p>
            </div>
            <div>
                <p class="text-[10px] text-blue-600 dark:text-blue-400">Belum</p>
                <p class="text-xs font-bold text-blue-600 dark:text-blue-400">{{ $belumCount }}</p>
            </div>
            <div>
                <p class="text-[10px] text-amber-600 dark:text-amber-400">Lewat/Absen</p>
                <p class="text-xs font-bold text-amber-600 dark:text-amber-400">{{ $dilewatiCount + $tidakAdaCount }}</p>
            </div>
        </div>
    </div>

    {{-- Search & Filter Section --}}
    <div class="mb-5 space-y-3">
        {{-- Search Input --}}
        <div class="flex items-center rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-xs shadow-2xs focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/10">
            <flux:icon.magnifying-glass class="size-4 text-zinc-400 shrink-0 mr-2" />
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Cari nama nasabah atau alamat..."
                   class="w-full bg-transparent text-xs text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none border-none p-0">
            @if($search)
                <button type="button" wire:click="$set('search', '')" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                    <flux:icon.x-mark class="size-4" />
                </button>
            @endif
        </div>

        {{-- Status Filter Pills --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
            @foreach(['semua' => 'Semua', 'belum' => 'Belum', 'dikunjungi' => 'Dikunjungi', 'dilewati' => 'Dilewati', 'tidak_ada' => 'Tidak Ada'] as $key => $label)
                <button type="button" wire:click="$set('filterStatus', '{{ $key }}')"
                    class="shrink-0 rounded-full px-3 py-1 text-[11px] font-semibold transition {{ $filterStatus === $key ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 shadow-2xs' : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Visit List --}}
    <div class="space-y-3">
        @forelse($jadwalHari as $item)
            <div class="rounded-2xl bg-white p-4 shadow-sm border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80 transition hover:border-zinc-300 dark:hover:border-zinc-600">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xs font-bold shadow-2xs
                            {{ $item['status'] === 'dikunjungi' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : '' }}
                            {{ $item['status'] === 'dilewati' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : '' }}
                            {{ $item['status'] === 'tidak_ada' ? 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300' : '' }}
                            {{ $item['status'] === 'belum' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' : '' }}">
                            {{ strtoupper(substr($item['profil']->nama, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-xs font-bold text-zinc-900 dark:text-white truncate">
                                {{ $item['profil']->nama }}
                            </h3>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate mt-0.5">
                                📍 {{ $item['profil']->alamat ?? 'Alamat belum diisi' }}
                            </p>
                            @if($item['profil']->user->no_hp)
                                <a href="tel:{{ $item['profil']->user->no_hp }}" class="inline-flex items-center gap-1 text-[11px] text-blue-600 dark:text-blue-400 hover:underline mt-0.5">
                                    📞 {{ $item['profil']->user->no_hp }}
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Current Status Badge --}}
                    @if($item['status'] === 'dikunjungi')
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 dark:bg-emerald-950/60 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 dark:text-emerald-300 shrink-0">
                            <flux:icon.check class="size-3" />
                            Dikunjungi
                        </span>
                    @elseif($item['status'] === 'dilewati')
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-950/60 px-2.5 py-0.5 text-[10px] font-bold text-amber-800 dark:text-amber-300 shrink-0">
                            <flux:icon.forward class="size-3" />
                            Dilewati
                        </span>
                    @elseif($item['status'] === 'tidak_ada')
                        <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 dark:bg-zinc-700 px-2.5 py-0.5 text-[10px] font-bold text-zinc-600 dark:text-zinc-300 shrink-0">
                            <flux:icon.x-mark class="size-3" />
                            Tidak Ada
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-950/50 px-2.5 py-0.5 text-[10px] font-bold text-blue-700 dark:text-blue-300 shrink-0">
                            Belum
                        </span>
                    @endif
                </div>

                {{-- Action Buttons Bar --}}
                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-700/50">
                    <button type="button" wire:click="updateStatus({{ $item['profil']->user_id }}, 'dikunjungi')"
                        class="flex-1 rounded-xl py-2 px-3 text-xs font-semibold transition flex items-center justify-center gap-1.5 {{ $item['status'] === 'dikunjungi' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 hover:bg-zinc-800' }}">
                        <flux:icon.check class="size-3.5" />
                        Dikunjungi
                    </button>

                    <button type="button" wire:click="updateStatus({{ $item['profil']->user_id }}, 'dilewati')"
                        class="rounded-xl py-2 px-3 text-xs font-semibold border transition flex items-center justify-center gap-1 {{ $item['status'] === 'dilewati' ? 'bg-amber-50 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-700' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50' }}">
                        Dilewati
                    </button>

                    <button type="button" wire:click="updateStatus({{ $item['profil']->user_id }}, 'tidak_ada')"
                        class="rounded-xl py-2 px-3 text-xs font-semibold border transition flex items-center justify-center gap-1 {{ $item['status'] === 'tidak_ada' ? 'bg-zinc-100 text-zinc-800 border-zinc-300 dark:bg-zinc-700 dark:text-zinc-200' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-50' }}">
                        Tidak Ada
                    </button>
                </div>
            </div>
        @empty
            <div class="py-16 text-center rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-700/80">
                <flux:icon.calendar class="size-10 text-zinc-300 dark:text-zinc-600 mx-auto mb-2" />
                <p class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Tidak ada jadwal kunjungan</p>
                <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-0.5">Coba ganti kata kunci pencarian atau tanggal.</p>
            </div>
        @endforelse
    </div>
</div>