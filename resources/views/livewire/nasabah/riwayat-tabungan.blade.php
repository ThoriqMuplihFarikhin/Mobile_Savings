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
            <h2 id="label-grafik-7-hari" class="text-xs font-semibold text-body">Setoran 7 hari terakhir</h2>
            <span class="text-xs font-semibold"><x-ui.rupiah :nilai="$total7Hari" tanda="+" /></span>
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
            <h2 id="label-filter-produk" class="text-xs font-semibold text-body">Produk tabungan</h2>
            @if($produkFilter)
                <button type="button" wire:click="$set('produkFilter', '')"
                    class="text-xs font-medium text-primary underline underline-offset-4">
                    Lihat semua
                </button>
            @endif
        </div>

        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
            <button type="button" wire:click="$set('produkFilter', '')"
                class="inline-flex shrink-0 items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium transition {{ !$produkFilter ? 'border-primary-soft bg-primary-soft text-on-primary-soft' : 'border-hairline-warm bg-white text-body dark:bg-surface' }}">
                <span>Semua produk</span>
                <span class="rounded-full px-2 py-0.5 text-xs {{ !$produkFilter ? 'bg-white text-on-primary-soft' : 'bg-surface-inset text-mute' }}">
                    {{ $grandCount }}
                </span>
            </button>

            @foreach($produkSummary as $summary)
                @php
                    $isMatched = (string)$produkFilter === (string)($produkList->firstWhere('nama', $summary['nama'])->id ?? '');
                @endphp
                <button type="button" wire:click="$set('produkFilter', '{{ $produkList->firstWhere('nama', $summary['nama'])->id ?? '' }}')"
                    class="inline-flex shrink-0 items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium transition {{ $isMatched ? 'border-primary-soft bg-primary-soft text-on-primary-soft' : 'border-hairline-warm bg-white text-body dark:bg-surface' }}">
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
                    class="rounded-full px-3 py-1.5 text-xs font-medium transition {{ !$dariTanggal && !$sampaiTanggal ? 'bg-primary-soft text-on-primary-soft' : 'border border-hairline-warm bg-white text-body dark:bg-surface' }}">
                    Semua
                </button>
                <button type="button" wire:click="filterThisMonth"
                    class="rounded-full px-3 py-1.5 text-xs font-medium transition {{ $dariTanggal === now()->startOfMonth()->format('Y-m-d') ? 'bg-primary-soft text-on-primary-soft' : 'border border-hairline-warm bg-white text-body dark:bg-surface' }}">
                    Bulan ini
                </button>
                <button type="button" wire:click="filterLastMonth"
                    class="rounded-full px-3 py-1.5 text-xs font-medium transition {{ $dariTanggal === now()->subMonth()->startOfMonth()->format('Y-m-d') ? 'bg-primary-soft text-on-primary-soft' : 'border border-hairline-warm bg-white text-body dark:bg-surface' }}">
                    Bulan lalu
                </button>
            </div>

            <button type="button" x-on:click="showDatePicker = !showDatePicker"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:bg-surface-inset dark:bg-surface">
                <flux:icon.calendar class="size-3.5 text-mute" />
                <span>Filter tanggal</span>
            </button>
        </div>

        <div x-show="showDatePicker" x-collapse class="mt-3 border-t border-hairline-warm pt-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-body">Dari tanggal</label>
                    <x-ui.tanggal wire:model.live="dariTanggal"
                        class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-2 text-xs text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-body">Sampai tanggal</label>
                    <x-ui.tanggal wire:model.live="sampaiTanggal" :min="$dariTanggal"
                        class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-2 text-xs text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
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
                    <h2 class="text-xs font-semibold text-mute">{{ $dateLabel }}</h2>
                    <span class="text-xs font-semibold">
                        <x-ui.rupiah :nilai="$items->where('status', '!=', 'dibatalkan')->sum('nominal')" tanda="+" />
                    </span>
                </div>

                <div class="overflow-hidden rounded-[20px] border border-hairline-warm bg-white p-2 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                    @foreach($items as $item)
                        @php
                            $tanda = $item->status === 'dibatalkan' ? null : '+';
                            $kategori = $item->status === 'tercatat' ? 'hijau' : ($item->status === 'dikoreksi' ? 'kuning' : 'netral');
                            $waktu = $item->created_at ? $item->created_at->format('H:i').' WIB' : 'Setoran harian';
                            $keterangan = $waktu . ($item->sumber_input === 'real_time' ? ' · Real-time' : ' · Susulan');
                        @endphp
                        <x-ui.baris-riwayat
                            :judul="$item->produk->nama ?? 'Setoran tabungan'"
                            :keterangan="$keterangan"
                            :nominal="$item->nominal"
                            :tanda="$tanda"
                            :kategori="$kategori"
                            status-domain="setoran"
                            :status-nilai="$item->status">
                            @if($item->status === 'tercatat')
                                <flux:icon.arrow-down-left class="size-4" />
                            @elseif($item->status === 'dikoreksi')
                                <flux:icon.pencil-square class="size-4" />
                            @else
                                <flux:icon.x-mark class="size-4" />
                            @endif
                        </x-ui.baris-riwayat>
                        <div class="flex justify-end px-3 pb-2 -mt-1">
                            <a href="{{ route('struk.setoran', $item) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline">
                                <flux:icon.document-text class="size-3" />
                                <span>Lihat struk</span>
                            </a>
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
                <p class="mt-1 text-xs text-mute">Setoran tabungan Anda pada periode ini belum ditemukan.</p>
            </div>
        @endforelse
    </div>

    @if($setoran->hasPages())
        <div>{{ $setoran->links() }}</div>
    @endif
</div>
