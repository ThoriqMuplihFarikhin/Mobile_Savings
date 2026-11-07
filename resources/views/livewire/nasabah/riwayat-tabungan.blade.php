<div class="flex flex-col gap-4 pb-6" x-data="{ showDatePicker: false }">
    {{-- Header Halaman Tab Top-Level --}}
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Riwayat tabungan</h1>
        <p class="mt-0.5 text-xs text-mute">Buku tabungan KawalDuit dan mutasi setoran Anda.</p>
    </div>

    {{-- Segmented Switcher Tab --}}
    <div class="flex rounded-full bg-surface-inset p-1 border border-hairline">
        <a href="{{ route('nasabah.riwayat-tabungan.index') }}" wire:navigate
           class="flex-1 rounded-full py-2 text-center text-xs font-semibold bg-white text-primary shadow-xs dark:bg-surface dark:text-primary-soft">
            Tabungan
        </a>
        <a href="{{ route('nasabah.riwayat.index') }}" wire:navigate
           class="flex-1 rounded-full py-2 text-center text-xs font-medium text-mute hover:text-ink transition dark:hover:text-zinc-200">
            Penarikan
        </a>
    </div>

    {{-- Total seluruh periode --}}
    @php
        $grandTotal = $produkSummary->sum('total');
        $grandCount = $produkSummary->sum('jumlah');
    @endphp
    <x-ui.hero-card aria-labelledby="label-total-periode">
        <p id="label-total-periode" class="text-xs font-medium text-on-hero-mute">Total terkumpul</p>
        <p class="mt-1 text-3xl font-semibold tracking-tight text-white">
            <x-ui.rupiah :nilai="$grandTotal" />
        </p>
        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
            <span class="rounded-full bg-white/[0.16] px-2.5 py-1 font-medium text-white">
                {{ $grandCount }} transaksi setoran
            </span>
            <span class="rounded-full bg-white/[0.16] px-2.5 py-1 font-medium text-white">
                {{ $produkSummary->count() }} produk aktif
            </span>
        </div>
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
        class="rounded-2xl border border-hairline bg-white p-5 shadow-xs dark:bg-surface">
        <div class="flex items-baseline justify-between gap-2">
            <h2 id="label-grafik-7-hari" class="text-xs font-semibold text-ink">Setoran 7 hari terakhir</h2>
            <span class="text-xs font-semibold text-primary"><x-ui.rupiah :nilai="$total7Hari" tanda="+" /></span>
        </div>

        <figure class="mt-4" role="img" aria-label="{{ $deskripsiGrafik7Hari }}">
            <div class="flex items-end justify-between gap-2 h-28 pt-2">
                @foreach($setoran7Hari as $batang)
                    @php
                        $tinggi = $batang['total'] > 0 ? max(8, (int) round($batang['total'] / $maks7Hari * 100)) : 0;
                        $isHariIni = $batang['tanggal'] === now()->toDateString();
                    @endphp
                    <div class="flex flex-1 flex-col items-center gap-2 h-full justify-end">
                        <div class="flex h-20 w-full items-end justify-center">
                            <div class="w-full max-w-[24px] rounded-t-[4px] transition {{ $tinggi > 0 ? ($isHariIni ? 'bg-primary' : 'bg-primary/50') : 'bg-hairline-strong' }}"
                                style="height: {{ $tinggi > 0 ? $tinggi : 4 }}%" aria-hidden="true"></div>
                        </div>
                        <span class="text-xs font-medium {{ $isHariIni ? 'font-semibold text-primary' : 'text-mute' }}" aria-hidden="true">{{ $batang['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </figure>
    </section>

    {{-- Filter chips produk tabungan (#rpf di prototipe) --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
        <button type="button" wire:click="$set('produkFilter', '')"
            class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-xs font-medium transition {{ !$produkFilter ? 'border-primary bg-primary text-white font-semibold' : 'border-hairline bg-white text-ink hover:bg-surface-inset dark:bg-surface' }}">
            <span>Semua produk</span>
            <span class="rounded-full px-1.5 py-0.2 text-xs {{ !$produkFilter ? 'bg-white/20 text-white' : 'bg-surface-inset text-mute' }}">
                {{ $grandCount }}
            </span>
        </button>

        @foreach($produkSummary as $summary)
            @php
                $isMatched = (string)$produkFilter === (string)($produkList->firstWhere('nama', $summary['nama'])->id ?? '');
            @endphp
            <button type="button" wire:click="$set('produkFilter', '{{ $produkList->firstWhere('nama', $summary['nama'])->id ?? '' }}')"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-xs font-medium transition {{ $isMatched ? 'border-primary bg-primary text-white font-semibold' : 'border-hairline bg-white text-ink hover:bg-surface-inset dark:bg-surface' }}">
                <span>{{ $summary['nama'] }}</span>
                <span class="text-xs {{ $isMatched ? 'text-white/80' : 'text-mute' }}">
                    (<x-ui.rupiah :nilai="$summary['total']" />)
                </span>
            </button>
        @endforeach
    </div>

    {{-- Filter tanggal preset & rentang (#rdf & #rowf di prototipe) --}}
    <div class="space-y-2.5">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <button type="button" wire:click="resetFilters"
                    class="rounded-full px-3.5 py-1.5 text-xs font-medium transition {{ !$dariTanggal && !$sampaiTanggal ? 'bg-primary-soft text-primary font-semibold border border-primary/20' : 'border border-hairline bg-white text-ink hover:bg-surface-inset dark:bg-surface' }}">
                    Semua
                </button>
                <button type="button" wire:click="filterThisMonth"
                    class="rounded-full px-3.5 py-1.5 text-xs font-medium transition {{ $dariTanggal === now()->startOfMonth()->format('Y-m-d') ? 'bg-primary-soft text-primary font-semibold border border-primary/20' : 'border border-hairline bg-white text-ink hover:bg-surface-inset dark:bg-surface' }}">
                    Bulan ini
                </button>
                <button type="button" wire:click="filterLastMonth"
                    class="rounded-full px-3.5 py-1.5 text-xs font-medium transition {{ $dariTanggal === now()->subMonth()->startOfMonth()->format('Y-m-d') ? 'bg-primary-soft text-primary font-semibold border border-primary/20' : 'border border-hairline bg-white text-ink hover:bg-surface-inset dark:bg-surface' }}">
                    Bulan lalu
                </button>
            </div>

            <button type="button" x-on:click="showDatePicker = !showDatePicker"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3.5 py-1.5 text-xs font-medium text-ink transition hover:bg-surface-inset active:scale-95 dark:bg-surface">
                <flux:icon.calendar class="size-3.5 text-mute" />
                <span>Rentang</span>
            </button>
        </div>

        <div x-show="showDatePicker" x-cloak x-collapse class="rounded-2xl border border-hairline bg-white p-3.5 shadow-xs dark:bg-surface">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-mute">Dari tanggal</label>
                    <x-ui.tanggal wire:model.live="dariTanggal"
                        class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-2 text-xs text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-mute">Sampai tanggal</label>
                    <x-ui.tanggal wire:model.live="sampaiTanggal" :min="$dariTanggal"
                        class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-2 text-xs text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar mutasi setoran --}}
    <div class="rounded-2xl border border-hairline bg-white px-4 py-2 shadow-xs dark:bg-surface divide-y divide-hairline">
        @forelse($setoran as $item)
            @php
                $tanda = $item->status === 'dibatalkan' ? null : '+';
                $kategori = $item->status === 'tercatat' ? 'hijau' : ($item->status === 'dikoreksi' ? 'kuning' : 'netral');
                $waktu = $item->tanggal_transaksi->isToday()
                    ? 'Hari ini, ' . ($item->created_at ? $item->created_at->format('H:i') : '')
                    : ($item->tanggal_transaksi->isYesterday()
                        ? 'Kemarin, ' . ($item->created_at ? $item->created_at->format('H:i') : '')
                        : $item->tanggal_transaksi->translatedFormat('d M') . ($item->created_at ? ', ' . $item->created_at->format('H:i') : ''));
                $keterangan = ($item->produk->nama ?? 'Tabungan') . ' · ' . $waktu;
            @endphp
            <x-ui.baris-riwayat
                class="border-none py-3"
                :judul="'Setoran harian'"
                :keterangan="$keterangan"
                :nominal="$item->nominal"
                :tanda="$tanda"
                :kategori="$kategori"
                status-domain="setoran"
                :status-nilai="$item->status"
                :aksi-href="route('struk.setoran', $item)"
                aksi-label="Lihat struk">
                @if($item->status === 'tercatat')
                    <flux:icon.arrow-down-left class="size-4" />
                @elseif($item->status === 'dikoreksi')
                    <flux:icon.pencil-square class="size-4" />
                @else
                    <flux:icon.x-mark class="size-4" />
                @endif
            </x-ui.baris-riwayat>
        @empty
            <div class="p-8 text-center">
                <span class="mx-auto flex size-32 items-center justify-center rounded-full bg-primary-soft" aria-hidden="true">
                    <x-ui.ilustrasi nama="tunas" :ukuran="96" />
                </span>
                <h3 class="mt-4 text-base font-semibold text-ink">Belum ada setoran</h3>
                <p class="mt-1 text-xs text-mute">Setoran tabungan Anda pada periode ini belum ditemukan.</p>
            </div>
        @endforelse
    </div>

    @if($setoran->hasPages())
        <div class="pt-1">{{ $setoran->links() }}</div>
    @endif
</div>

