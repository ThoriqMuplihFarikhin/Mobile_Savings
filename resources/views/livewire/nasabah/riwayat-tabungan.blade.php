<div class="mx-auto max-w-2xl space-y-5 pb-24" x-data="{ showDatePicker: false }">
    {{-- Top Title --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Riwayat Tabungan</h1>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Buku tabungan digital & mutasi setoran Anda</p>
        </div>
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 shadow-2xs border border-indigo-100 dark:border-indigo-900/50">
            <flux:icon.receipt-percent class="size-5" />
        </div>
    </div>

    {{-- Grand Total Hero Banner --}}
    @php
        $grandTotal = $produkSummary->sum('total');
        $grandCount = $produkSummary->sum('jumlah');
    @endphp
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900 via-indigo-800 to-zinc-900 p-6 text-white shadow-xl dark:from-zinc-950 dark:to-zinc-900 border border-indigo-700/30 dark:border-zinc-800">
        <div class="absolute -right-12 -top-12 h-44 w-44 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col justify-between gap-4">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-200">Total Saldo Terkumpul</span>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight font-mono text-white">
                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-white/10 pt-3 text-xs text-indigo-100/80">
                <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ $grandCount }} Transaksi Setoran
                </span>
                <span class="font-medium text-white">{{ $produkSummary->count() }} Produk Aktif</span>
            </div>
        </div>
    </div>

    {{-- Product Filter Horizontal Pills --}}
    <div>
        <div class="flex items-center justify-between mb-2 px-1">
            <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Filter Produk Tabungan</span>
            @if($produkFilter)
                <button type="button" wire:click="$set('produkFilter', '')" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                    Lihat Semua
                </button>
            @endif
        </div>
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
            {{-- All Products Pill --}}
            <button type="button" wire:click="$set('produkFilter', '')"
                class="inline-flex shrink-0 items-center gap-2 rounded-2xl px-4 py-2.5 text-xs font-bold transition shadow-2xs border {{ !$produkFilter ? 'bg-indigo-600 text-white border-indigo-600 dark:bg-indigo-500 dark:border-indigo-500' : 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border-zinc-200/80 dark:border-zinc-700/80 hover:bg-zinc-50' }}">
                <span>Semua Produk</span>
                <span class="rounded-full px-2 py-0.5 text-[10px] {{ !$produkFilter ? 'bg-white/20' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400' }}">
                    {{ $grandCount }}
                </span>
            </button>

            {{-- Individual Product Pills --}}
            @foreach($produkSummary as $summary)
                @php
                    $isMatched = (string)$produkFilter === (string)($produkList->firstWhere('nama', $summary['nama'])->id ?? '');
                @endphp
                <button type="button" wire:click="$set('produkFilter', '{{ $produkList->firstWhere('nama', $summary['nama'])->id ?? '' }}')"
                    class="inline-flex shrink-0 items-center gap-2 rounded-2xl px-4 py-2.5 text-xs font-bold transition shadow-2xs border {{ $isMatched ? 'bg-indigo-600 text-white border-indigo-600 dark:bg-indigo-500 dark:border-indigo-500' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border-zinc-200/80 dark:border-zinc-700/80 hover:bg-zinc-50' }}">
                    <span class="h-2 w-2 rounded-full {{ $summary['tipe'] === 'paket' ? 'bg-purple-400' : 'bg-teal-400' }}"></span>
                    <span>{{ $summary['nama'] }}</span>
                    <span class="font-mono text-[11px] opacity-90">
                        (Rp {{ number_format($summary['total'], 0, ',', '.') }})
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Date Filter Toolbar & Quick Pills --}}
    <div class="rounded-3xl bg-white dark:bg-zinc-800 p-4 shadow-xs border border-zinc-200/80 dark:border-zinc-700/80 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-1.5 overflow-x-auto">
                <button type="button" wire:click="resetFilters"
                    class="rounded-xl px-3 py-1.5 text-xs font-bold transition {{ !$dariTanggal && !$sampaiTanggal ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 dark:bg-zinc-700/60 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Semua
                </button>
                <button type="button" wire:click="filterThisMonth"
                    class="rounded-xl px-3 py-1.5 text-xs font-bold transition {{ $dariTanggal === now()->startOfMonth()->format('Y-m-d') ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 dark:bg-zinc-700/60 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Bulan Ini
                </button>
                <button type="button" wire:click="filterLastMonth"
                    class="rounded-xl px-3 py-1.5 text-xs font-bold transition {{ $dariTanggal === now()->subMonth()->startOfMonth()->format('Y-m-d') ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 dark:bg-zinc-700/60 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    Bulan Lalu
                </button>
            </div>

            <button type="button" x-on:click="showDatePicker = !showDatePicker"
                class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200 dark:border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 shrink-0">
                <flux:icon.calendar class="size-3.5 text-zinc-500" />
                <span>Filter Tanggal</span>
            </button>
        </div>

        {{-- Collapsible Custom Date Picker Inputs --}}
        <div x-show="showDatePicker" x-collapse class="pt-2 border-t border-zinc-100 dark:border-zinc-700/60">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">Dari Tanggal</label>
                    <input type="date" wire:model.live="dariTanggal"
                        class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 px-3 py-2 text-xs text-zinc-900 dark:text-white focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" />
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">Sampai Tanggal</label>
                    <input type="date" wire:model.live="sampaiTanggal"
                        class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 px-3 py-2 text-xs text-zinc-900 dark:text-white focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" />
                </div>
            </div>
        </div>
    </div>

    {{-- Passbook Statement Transaction List (Grouped by Date) --}}
    @php
        $groupedSetoran = $setoran->groupBy(function($item) {
            return $item->tanggal_transaksi->translatedFormat('l, d F Y');
        });
    @endphp

    <div class="space-y-5">
        @forelse($groupedSetoran as $dateLabel => $items)
            <div>
                {{-- Date Section Header --}}
                <div class="mb-2 px-1 flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ $dateLabel }}</span>
                    <span class="text-[11px] font-mono font-medium text-emerald-600 dark:text-emerald-400">Total: +Rp {{ number_format($items->where('status', '!=', 'dibatalkan')->sum('nominal'), 0, ',', '.') }}</span>
                </div>

                {{-- Group Passbook Container --}}
                <div class="overflow-hidden rounded-3xl bg-white dark:bg-zinc-800 shadow-xs border border-zinc-200/80 dark:border-zinc-700/80 divide-y divide-zinc-100 dark:divide-zinc-700/50">
                    @foreach($items as $item)
                        <div class="flex items-center justify-between gap-3.5 px-5 py-4 transition hover:bg-zinc-50/60 dark:hover:bg-zinc-700/30">
                            {{-- Icon & Product Details --}}
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl
                                    {{ $item->status === 'tercatat' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40' : '' }}
                                    {{ $item->status === 'dikoreksi' ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-100 dark:border-amber-900/40' : '' }}
                                    {{ $item->status === 'dibatalkan' ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-100 dark:border-rose-900/40' : '' }}">
                                    @if($item->status === 'tercatat')
                                        <flux:icon.arrow-down-left class="size-4" />
                                    @elseif($item->status === 'dikoreksi')
                                        <flux:icon.pencil-square class="size-4" />
                                    @else
                                        <flux:icon.x-mark class="size-4" />
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-xs font-bold text-zinc-900 dark:text-white truncate">
                                            {{ $item->produk->nama ?? 'Setoran Tabungan' }}
                                        </h3>
                                        <span class="inline-flex items-center rounded-md bg-zinc-100 dark:bg-zinc-700/60 px-1.5 py-0.5 text-[10px] font-semibold text-zinc-600 dark:text-zinc-300">
                                            {{ $item->sumber_input === 'real_time' ? '⚡ Real-time' : '📝 Susulan' }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-0.5 font-mono">
                                        {{ $item->created_at ? $item->created_at->format('H:i') . ' WIB' : 'Setoran Harian' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Nominal & Status Badge --}}
                            <div class="text-right shrink-0">
                                <span class="font-mono text-sm font-bold text-emerald-600 dark:text-emerald-400 block">
                                    +Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                </span>
                                @if($item->status === 'tercatat')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Tercatat
                                    </span>
                                @elseif($item->status === 'dikoreksi')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-600 dark:text-amber-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Dikoreksi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-rose-600 dark:text-rose-400 line-through">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                        Dibatalkan
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="rounded-3xl bg-white dark:bg-zinc-800 p-10 text-center border border-zinc-200/80 dark:border-zinc-700/80 shadow-xs">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-700/60 text-zinc-400">
                    <flux:icon.document-text class="size-7 text-zinc-400" />
                </div>
                <h3 class="mt-4 text-sm font-bold text-zinc-900 dark:text-white">Belum Ada Transaksi Setoran</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 max-w-xs mx-auto">Riwayat setoran tabungan Anda pada periode ini belum ditemukan.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($setoran->hasPages())
        <div class="mt-6">
            {{ $setoran->links() }}
        </div>
    @endif
</div>



