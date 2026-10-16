<div class="space-y-6" x-data="{ showDatePicker: false }">
    {{-- Judul halaman --}}
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Riwayat tabungan</h1>
        <p class="mt-1 text-sm text-mute">Buku tabungan digital dan mutasi setoran Anda.</p>
    </div>

    {{-- Total seluruh periode --}}
    @php
        $grandTotal = $produkSummary->sum('total');
        $grandCount = $produkSummary->sum('jumlah');
    @endphp
    <x-ui.hero-card aria-labelledby="label-total-periode">
        <p id="label-total-periode" class="text-sm text-on-hero-mute">Total terkumpul</p>
        <p class="mt-1 text-[32px] leading-9 font-semibold tracking-tight">
            <x-ui.rupiah :nilai="$grandTotal" />
        </p>
        <p class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-on-hero-mute">
            <span>{{ $grandCount }} transaksi setoran</span>
            <span>{{ $produkSummary->count() }} produk aktif</span>
        </p>
    </x-ui.hero-card>

    {{-- Grafik batang setoran 7 hari (DESIGN.md §14: padanan teks via aria-label, §15 baris aksesibilitas) --}}
    @php
        $maks7Hari = max(1, $setoran7Hari->max('total'));
        $total7Hari = $setoran7Hari->sum('total');
        $deskripsiGrafik7Hari = 'Grafik batang setoran tujuh hari terakhir. '
            .$setoran7Hari->map(fn ($batang) => $batang['hari'].', '.$batang['label_tanggal'].': '.number_format($batang['total'], 0, ',', '.').' rupiah')
            ->implode('; ')
            .'. Total '.number_format((float) $total7Hari, 0, ',', '.').' rupiah.';
    @endphp
    <section aria-labelledby="label-grafik-7-hari"
        class="rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
        <div class="flex items-baseline justify-between gap-2">
            <h2 id="label-grafik-7-hari" class="text-sm font-medium text-body">Setoran 7 hari terakhir</h2>
            <span class="text-sm font-medium"><x-ui.rupiah :nilai="$total7Hari" tanda="+" /></span>
        </div>

        <figure class="mt-3" role="img" aria-label="{{ $deskripsiGrafik7Hari }}">
            <div class="flex items-end justify-between gap-2">
                @foreach($setoran7Hari as $batang)
                    @php
                        $tinggi = $batang['total'] > 0 ? max(8, (int) round($batang['total'] / $maks7Hari * 100)) : 0;
                    @endphp
                    <div class="flex flex-1 flex-col items-center gap-1.5">
                        <div class="flex h-24 w-full items-end">
                            <div class="w-full rounded-t-[4px] {{ $tinggi > 0 ? 'bg-primary' : 'bg-hairline-strong' }}"
                                style="height: {{ $tinggi > 0 ? $tinggi : 2 }}%" aria-hidden="true"></div>
                        </div>
                        <span class="text-xs text-mute" aria-hidden="true">{{ $batang['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </figure>
    </section>

    {{-- Filter produk --}}
    <section aria-labelledby="label-filter-produk">
        <div class="mb-2 flex items-center justify-between gap-2">
            <h2 id="label-filter-produk" class="text-sm font-medium text-body">Produk tabungan</h2>
            @if($produkFilter)
                <button type="button" wire:click="$set('produkFilter', '')"
                    class="text-sm font-medium text-primary underline underline-offset-4">
                    Lihat semua
                </button>
            @endif
        </div>

        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
            <button type="button" wire:click="$set('produkFilter', '')"
                class="inline-flex shrink-0 items-center gap-2 rounded-full border px-3 py-1.5 text-sm font-medium transition {{ !$produkFilter ? 'border-primary-soft bg-primary-soft text-on-primary-soft' : 'border-hairline-warm bg-white text-body dark:bg-surface' }}">
                <span>Semua produk</span>
                <span class="rounded-full px-2 py-0.5 text-xs {{ !$produkFilter ? 'bg-white text-on-primary-soft' : 'bg-inset text-mute' }}">
                    {{ $grandCount }}
                </span>
            </button>

            @foreach($produkSummary as $summary)
                @php
                    $isMatched = (string)$produkFilter === (string)($produkList->firstWhere('nama', $summary['nama'])->id ?? '');
                @endphp
                <button type="button" wire:click="$set('produkFilter', '{{ $produkList->firstWhere('nama', $summary['nama'])->id ?? '' }}')"
                    class="inline-flex shrink-0 items-center gap-2 rounded-full border px-3 py-1.5 text-sm font-medium transition {{ $isMatched ? 'border-primary-soft bg-primary-soft text-on-primary-soft' : 'border-hairline-warm bg-white text-body dark:bg-surface' }}">
                    <span>{{ $summary['nama'] }}</span>
                    <span class="text-xs {{ $isMatched ? 'text-on-primary-soft' : 'text-mute' }}">
                        (<x-ui.rupiah :nilai="$summary['total']" />)
                    </span>
                </button>
            @endforeach
        </div>
    </section>

    {{-- Filter tanggal --}}
    <section class="rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 overflow-x-auto">
                <button type="button" wire:click="resetFilters"
                    class="rounded-full px-3 py-1.5 text-sm font-medium transition {{ !$dariTanggal && !$sampaiTanggal ? 'bg-primary-soft text-on-primary-soft' : 'border border-hairline-warm bg-white text-body dark:bg-surface' }}">
                    Semua
                </button>
                <button type="button" wire:click="filterThisMonth"
                    class="rounded-full px-3 py-1.5 text-sm font-medium transition {{ $dariTanggal === now()->startOfMonth()->format('Y-m-d') ? 'bg-primary-soft text-on-primary-soft' : 'border border-hairline-warm bg-white text-body dark:bg-surface' }}">
                    Bulan ini
                </button>
                <button type="button" wire:click="filterLastMonth"
                    class="rounded-full px-3 py-1.5 text-sm font-medium transition {{ $dariTanggal === now()->subMonth()->startOfMonth()->format('Y-m-d') ? 'bg-primary-soft text-on-primary-soft' : 'border border-hairline-warm bg-white text-body dark:bg-surface' }}">
                    Bulan lalu
                </button>
            </div>

            <button type="button" x-on:click="showDatePicker = !showDatePicker"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3 py-1.5 text-sm font-medium text-ink transition dark:bg-surface">
                <flux:icon.calendar class="size-4 text-mute" />
                <span>Filter tanggal</span>
            </button>
        </div>

        <div x-show="showDatePicker" x-collapse class="mt-3 border-t border-hairline-warm pt-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-body">Dari tanggal</label>
                    <x-ui.tanggal wire:model.live="dariTanggal"
                        class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-body">Sampai tanggal</label>
                    <x-ui.tanggal wire:model.live="sampaiTanggal" :min="$dariTanggal"
                        class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                </div>
            </div>
        </div>
    </section>

    {{-- Daftar setoran, dikelompokkan per tanggal --}}
    @php
        $groupedSetoran = $setoran->groupBy(function($item) {
            return $item->tanggal_transaksi->translatedFormat('l, d F Y');
        });
    @endphp

    <div class="space-y-6">
        @forelse($groupedSetoran as $dateLabel => $items)
            <section>
                <div class="mb-2 flex items-center justify-between gap-2 px-1">
                    <h2 class="text-sm font-medium text-mute">{{ $dateLabel }}</h2>
                    <span class="text-sm font-medium">
                        <x-ui.rupiah :nilai="$items->where('status', '!=', 'dibatalkan')->sum('nominal')" tanda="+" />
                    </span>
                </div>

                <div class="divide-y divide-hairline-warm overflow-hidden rounded-[20px] border border-hairline-warm bg-white shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                    @foreach($items as $item)
                        <div class="flex min-h-16 items-center justify-between gap-3 px-4 py-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <x-ui.tile kategori="{{ $item->status === 'tercatat' ? 'hijau' : ($item->status === 'dikoreksi' ? 'kuning' : 'netral') }}" ukuran="40" aria-hidden="true">
                                    @if($item->status === 'tercatat')
                                        <flux:icon.arrow-down-left class="size-4" />
                                    @elseif($item->status === 'dikoreksi')
                                        <flux:icon.pencil-square class="size-4" />
                                    @else
                                        <flux:icon.x-mark class="size-4" />
                                    @endif
                                </x-ui.tile>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="truncate text-base font-medium text-ink">{{ $item->produk->nama ?? 'Setoran tabungan' }}</p>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-inset px-2 py-0.5 text-xs text-body">
                                            @if($item->sumber_input === 'real_time')
                                                <flux:icon.bolt class="size-3.5" aria-hidden="true" />
                                                Real-time
                                            @else
                                                <flux:icon.clock class="size-3.5" aria-hidden="true" />
                                                Susulan
                                            @endif
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-sm text-mute">
                                        {{ $item->created_at ? $item->created_at->format('H:i').' WIB' : 'Setoran harian' }}
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                @if($item->status === 'dibatalkan')
                                    <p class="text-base font-semibold text-mute line-through">
                                        <x-ui.rupiah :nilai="$item->nominal" />
                                    </p>
                                @elseif($item->status === 'dikoreksi' && $item->nominal_asli !== null)
                                    <p class="text-sm text-mute line-through">
                                        <x-ui.rupiah :nilai="$item->nominal_asli" />
                                    </p>
                                    <p class="text-base font-semibold">
                                        <x-ui.rupiah :nilai="$item->nominal" tanda="+" />
                                    </p>
                                @else
                                    <p class="text-base font-semibold">
                                        <x-ui.rupiah :nilai="$item->nominal" tanda="+" />
                                    </p>
                                @endif

                                <div class="mt-1 flex justify-end">
                                    <x-ui.status-chip domain="setoran" status="{{ $item->status }}" />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="rounded-[20px] border border-hairline-warm bg-white p-8 text-center shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                <span class="mx-auto flex size-32 items-center justify-center rounded-full bg-primary-soft" aria-hidden="true">
                    <x-ui.ilustrasi nama="tunas" :ukuran="96" />
                </span>
                <h3 class="mt-4 text-base font-semibold text-ink">Belum ada setoran</h3>
                <p class="mt-1 text-sm text-mute">Setoran tabungan Anda pada periode ini belum ditemukan.</p>
            </div>
        @endforelse
    </div>

    @if($setoran->hasPages())
        <div>{{ $setoran->links() }}</div>
    @endif
</div>
