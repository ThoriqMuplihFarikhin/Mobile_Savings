<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-xs font-medium text-mute dark:text-zinc-400">Operasional</p>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-ink dark:text-white mt-0.5">Antrian Komplain</h1>
            <p class="mt-1 text-xs sm:text-sm text-mute dark:text-zinc-400">Tinjau dan selesaikan komplain dari nasabah.</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-hairline-warm bg-primary-soft p-3.5 text-xs font-semibold text-on-primary-soft shadow-xs dark:border-primary/40 dark:bg-primary-soft/30 dark:text-primary">
            <flux:icon.check-circle class="size-4 shrink-0 text-primary" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-danger-soft bg-danger-soft p-3.5 text-xs font-semibold text-danger-deep shadow-xs dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.exclamation-triangle class="size-4 shrink-0 text-danger dark:text-dark-danger-text" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- KPI Cards (3 Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="rounded-2xl border border-hairline/80 bg-white p-5 shadow-xs dark:bg-surface">
            <span class="text-xs text-mute dark:text-zinc-400 font-medium">Baru</span>
            <div class="text-2xl font-bold text-ink dark:text-white mt-1.5 tabular-nums">
                {{ $ringkasanStatus['baru'] ?? 0 }}
            </div>
            @if(($ringkasanStatus['baru'] ?? 0) > 0)
                <span class="inline-block text-[11px] font-medium text-warning-deep dark:text-warning-deep mt-1">
                    Perlu tindakan
                </span>
            @endif
        </div>

        <div class="rounded-2xl border border-hairline/80 bg-white p-5 shadow-xs dark:bg-surface">
            <span class="text-xs text-mute dark:text-zinc-400 font-medium">Diproses</span>
            <div class="text-2xl font-bold text-ink dark:text-white mt-1.5 tabular-nums">
                {{ $ringkasanStatus['diproses'] ?? 0 }}
            </div>
        </div>

        <div class="rounded-2xl border border-hairline/80 bg-white p-5 shadow-xs dark:bg-surface">
            <span class="text-xs text-mute dark:text-zinc-400 font-medium">Selesai</span>
            <div class="text-2xl font-bold text-ink dark:text-white mt-1.5 tabular-nums">
                {{ $ringkasanStatus['selesai'] ?? 0 }}
            </div>
        </div>
    </div>

    {{-- Main Table Card Container --}}
    <div class="rounded-2xl border border-hairline/80 bg-white shadow-xs dark:bg-surface overflow-hidden">
        {{-- Toolbar / Segmented Tabs --}}
        <div class="p-4 border-b border-hairline/60">
            <div class="inline-flex rounded-xl bg-surface-inset p-1 border border-hairline/60 dark:bg-zinc-800/60">
                @foreach(['semua' => 'Semua', 'baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $val => $lbl)
                    <button type="button" wire:click="setStatusFilter('{{ $val }}')"
                        @class([
                            'px-4 py-2 min-h-11 rounded-lg text-xs font-semibold transition cursor-pointer text-center',
                            'bg-white shadow-xs text-ink dark:bg-surface dark:text-zinc-100' => $statusFilter === $val,
                            'text-mute hover:text-ink dark:text-zinc-400 dark:hover:text-zinc-200' => $statusFilter !== $val,
                        ])>
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Desktop Table (Must include exact string 'hidden md:table' for AntreanTabelKeKartuP74bTest) --}}
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-hairline/60 bg-surface-inset/40 text-[11px] font-semibold uppercase tracking-wider text-mute">
                        <th class="px-5 py-3">TANGGAL</th>
                        <th class="px-5 py-3">NASABAH</th>
                        <th class="px-5 py-3">KATEGORI</th>
                        <th class="px-5 py-3">DESKRIPSI</th>
                        <th class="px-5 py-3">STATUS</th>
                        <th class="px-5 py-3 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline/60">
                    @forelse($komplains as $item)
                        @php
                            $labelKategori = match($item->kategori) {
                                'penarikan', 'penarikan_tabungan' => 'Penarikan tabungan',
                                'saldo', 'permasalahan_saldo' => 'Permasalahan saldo',
                                'pencairan_paket', 'paket' => 'Pencairan paket / barang',
                                'setoran' => 'Setoran tabungan',
                                'lainnya' => 'Lainnya',
                                default => ucfirst(str_replace('_', ' ', $item->kategori)),
                            };
                        @endphp
                        <tr class="hover:bg-surface-inset/30 transition text-xs">
                            <td class="whitespace-nowrap px-5 py-3.5 text-body tabular-nums">
                                {{ $item->tanggal_dibuat ? $item->tanggal_dibuat->translatedFormat('d M') : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 font-medium text-ink dark:text-white">
                                {{ $item->nasabah?->name ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-ink dark:text-zinc-200">
                                {{ $labelKategori }}
                            </td>
                            <td class="max-w-[260px] truncate px-5 py-3.5 text-body">
                                {{ $item->deskripsi }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                <x-admin.status-chip domain="komplain" :status="$item->status" />
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                @if($item->status === 'baru')
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <button type="button" wire:click="proses({{ $item->id }})"
                                            class="min-h-11 inline-flex items-center justify-center rounded-lg bg-primary hover:bg-primary-hover px-3.5 py-1.5 text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer">
                                            Proses
                                        </button>
                                        <button type="button" wire:click="showDetail({{ $item->id }})"
                                            class="min-h-11 inline-flex items-center justify-center rounded-lg border border-hairline bg-surface text-ink hover:bg-surface-inset px-3.5 py-1.5 text-xs font-medium shadow-xs transition active:scale-95 cursor-pointer dark:bg-zinc-800 dark:text-zinc-200">
                                            Detail
                                        </button>
                                    </div>
                                @elseif($item->status === 'diproses')
                                    <button type="button" wire:click="showDetail({{ $item->id }})"
                                        class="min-h-11 inline-flex items-center justify-center rounded-lg bg-primary hover:bg-primary-hover px-4 py-1.5 text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer">
                                        Selesaikan
                                    </button>
                                @else
                                    <button type="button" wire:click="showDetail({{ $item->id }})"
                                        class="min-h-11 inline-flex items-center justify-center rounded-lg border border-hairline bg-surface text-ink hover:bg-surface-inset px-3.5 py-1.5 text-xs font-medium shadow-xs transition active:scale-95 cursor-pointer dark:bg-zinc-800 dark:text-zinc-200">
                                        Detail
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="text-sm font-semibold text-ink dark:text-white">Tidak ada komplain.</div>
                                <div class="mt-1 text-xs text-mute dark:text-zinc-400">Belum ada komplain pada kategori status ini.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile View (Must include exact string 'data-test="kartu-tabel" class="divide-y divide-hairline md:hidden' for AntreanTabelKeKartuP74bTest) --}}
        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($komplains as $item)
                @php
                    $labelKategori = match($item->kategori) {
                        'penarikan', 'penarikan_tabungan' => 'Penarikan tabungan',
                        'saldo', 'permasalahan_saldo' => 'Permasalahan saldo',
                        'pencairan_paket', 'paket' => 'Pencairan paket / barang',
                        'setoran' => 'Setoran tabungan',
                        'lainnya' => 'Lainnya',
                        default => ucfirst(str_replace('_', ' ', $item->kategori)),
                    };
                @endphp
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-ink dark:text-white">{{ $item->nasabah?->name ?? '-' }}</p>
                            <p class="text-xs text-mute dark:text-zinc-400 mt-0.5">{{ $item->tanggal_dibuat ? $item->tanggal_dibuat->translatedFormat('d M Y') : '-' }} &middot; {{ $labelKategori }}</p>
                        </div>
                        <x-admin.status-chip domain="komplain" :status="$item->status" />
                    </div>

                    <div class="p-3 rounded-xl bg-surface-inset text-xs text-body dark:bg-zinc-800/60 leading-relaxed">
                        {{ $item->deskripsi }}
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1">
                        @if($item->status === 'baru')
                            <button type="button" wire:click="proses({{ $item->id }})"
                                class="min-h-11 flex-1 inline-flex items-center justify-center rounded-lg bg-primary hover:bg-primary-hover px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer">
                                Proses
                            </button>
                            <button type="button" wire:click="showDetail({{ $item->id }})"
                                class="min-h-11 flex-1 inline-flex items-center justify-center rounded-lg border border-hairline bg-surface text-ink hover:bg-surface-inset px-3.5 py-2 text-xs font-medium shadow-xs transition active:scale-95 cursor-pointer dark:bg-zinc-800 dark:text-zinc-200">
                                Detail
                            </button>
                        @elseif($item->status === 'diproses')
                            <button type="button" wire:click="showDetail({{ $item->id }})"
                                class="min-h-11 w-full inline-flex items-center justify-center rounded-lg bg-primary hover:bg-primary-hover px-4 py-2 text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer">
                                Selesaikan
                            </button>
                        @else
                            <button type="button" wire:click="showDetail({{ $item->id }})"
                                class="min-h-11 w-full inline-flex items-center justify-center rounded-lg border border-hairline bg-surface text-ink hover:bg-surface-inset px-3.5 py-2 text-xs font-medium shadow-xs transition active:scale-95 cursor-pointer dark:bg-zinc-800 dark:text-zinc-200">
                                Detail
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <div class="text-sm font-semibold text-ink dark:text-white">Tidak ada komplain.</div>
                    <div class="mt-1 text-xs text-mute dark:text-zinc-400">Belum ada komplain pada kategori status ini.</div>
                </div>
            @endforelse
        </div>

        {{-- Pagination & Footer --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-t border-hairline/60 px-5 py-3 text-xs text-mute dark:text-zinc-400">
            <span>Menampilkan {{ $komplains->count() }} dari {{ $komplains->total() }} data</span>
            <div>
                @if($komplains->hasPages())
                    {{ $komplains->links() }}
                @else
                    <span>Halaman {{ $komplains->currentPage() }} dari {{ $komplains->lastPage() }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Drawer Detail Komplain (Matching drw in HTML design) --}}
    @if($tampilDetail && !empty($selectedKomplain))
        <div class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true" aria-labelledby="drawer-komplain-title">
            <div class="fixed inset-0 bg-black/45 transition-opacity cursor-pointer" wire:click="$set('tampilDetail', false)"></div>

            <div class="relative z-10 flex h-full w-full max-w-[480px] flex-col border-l border-hairline bg-surface shadow-2xl transition-all">
                {{-- Header Drawer --}}
                <div class="flex items-center justify-between border-b border-hairline px-5 py-4">
                    <div>
                        <h3 id="drawer-komplain-title" class="text-base font-semibold text-ink dark:text-white">Detail Komplain</h3>
                        <p class="text-xs text-mute dark:text-zinc-400 mt-0.5">
                            {{ $selectedKomplain->nasabah?->name ?? '-' }} &middot; {{ $selectedKomplain->tanggal_dibuat?->translatedFormat('d M Y') }}
                        </p>
                    </div>
                    <button type="button" wire:click="$set('tampilDetail', false)"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-mute transition hover:bg-surface-inset hover:text-ink cursor-pointer dark:hover:text-white"
                        aria-label="Tutup">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                {{-- Content Drawer --}}
                <div class="flex-1 overflow-y-auto p-5 space-y-5">
                    {{-- Section 1: Komplain (Matching sec('Komplain', kv(...)) in HTML) --}}
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-mute dark:text-zinc-400 mb-2">
                            Komplain
                        </h4>
                        <div class="rounded-xl border border-hairline bg-surface p-4 text-xs space-y-2.5">
                            <div class="flex items-center justify-between gap-3 border-b border-dashed border-hairline pb-2">
                                <span class="text-mute dark:text-zinc-400">Nasabah</span>
                                <b class="font-medium text-ink dark:text-white text-right">{{ $selectedKomplain->nasabah?->name ?? '-' }}</b>
                            </div>
                            <div class="flex items-center justify-between gap-3 border-b border-dashed border-hairline pb-2">
                                <span class="text-mute dark:text-zinc-400">Kategori</span>
                                <b class="font-medium text-ink dark:text-white text-right">
                                    {{ ucfirst(str_replace('_', ' ', $selectedKomplain->kategori)) }}
                                </b>
                            </div>
                            <div class="flex items-center justify-between gap-3 border-b border-dashed border-hairline pb-2">
                                <span class="text-mute dark:text-zinc-400">Tanggal</span>
                                <b class="font-medium text-ink dark:text-white text-right">
                                    {{ $selectedKomplain->tanggal_dibuat?->translatedFormat('d M Y H:i') ?? '-' }}
                                </b>
                            </div>
                            <div class="flex items-center justify-between gap-3 pt-0.5">
                                <span class="text-mute dark:text-zinc-400">Status</span>
                                <div>
                                    <x-admin.status-chip domain="komplain" :status="$selectedKomplain->status" />
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 p-3.5 rounded-xl border border-hairline bg-surface-inset text-xs text-body dark:bg-zinc-800/60 leading-relaxed">
                            <p class="font-medium text-ink dark:text-white mb-1">Deskripsi Masalah:</p>
                            {{ $selectedKomplain->deskripsi }}
                        </div>
                    </div>

                    {{-- Section 2: Transaksi Terkait (Matching sec('Transaksi terkait', kv(...)) in HTML) --}}
                    @if($selectedKomplain->transaksiTerkait)
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-mute dark:text-zinc-400 mb-2">
                                Transaksi Terkait
                            </h4>
                            <div class="rounded-xl border border-hairline bg-surface p-4 text-xs space-y-2.5">
                                <div class="flex items-center justify-between gap-3 border-b border-dashed border-hairline pb-2">
                                    <span class="text-mute dark:text-zinc-400">Tanggal Transaksi</span>
                                    <b class="font-medium text-ink dark:text-white text-right">
                                        {{ $selectedKomplain->transaksiTerkait->tanggal_transaksi?->translatedFormat('d M Y') ?? '-' }}
                                    </b>
                                </div>
                                <div class="flex items-center justify-between gap-3 border-b border-dashed border-hairline pb-2">
                                    <span class="text-mute dark:text-zinc-400">Produk</span>
                                    <b class="font-medium text-ink dark:text-white text-right">
                                        {{ $selectedKomplain->transaksiTerkait->produk?->nama ?? '-' }}
                                    </b>
                                </div>
                                <div class="flex items-center justify-between gap-3 pt-0.5">
                                    <span class="text-mute dark:text-zinc-400">Nominal</span>
                                    <b class="font-semibold text-primary dark:text-primary tabular-nums text-right">
                                        Rp {{ number_format($selectedKomplain->transaksiTerkait->nominal, 0, ',', '.') }}
                                    </b>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Section 3: Penyelesaian (Matching sec('Penyelesaian', ...) in HTML) --}}
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-mute dark:text-zinc-400 mb-2">
                            Penyelesaian
                        </h4>
                        @if($selectedKomplain->status !== 'selesai')
                            <div class="space-y-3">
                                <div>
                                    <label for="catatan" class="block text-xs font-medium text-ink dark:text-zinc-200 mb-1.5">
                                        Catatan Penyelesaian
                                    </label>
                                    <textarea id="catatan" wire:model="catatan" rows="3"
                                        class="w-full rounded-xl border border-hairline bg-white px-3.5 py-2.5 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline dark:bg-surface dark:text-white"
                                        placeholder="Jelaskan tindakan yang dilakukan..."></textarea>
                                    @error('catatan')
                                        <p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="button" wire:click="selesai({{ $selectedKomplain->id }})"
                                    class="w-full min-h-11 inline-flex items-center justify-center rounded-lg bg-primary hover:bg-primary-hover px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer">
                                    Selesaikan
                                </button>
                            </div>
                        @else
                            <div class="rounded-xl border border-hairline bg-surface p-4 text-xs space-y-2.5">
                                <div class="flex items-center justify-between gap-3 border-b border-dashed border-hairline pb-2">
                                    <span class="text-mute dark:text-zinc-400">Tanggal Selesai</span>
                                    <b class="font-medium text-ink dark:text-white text-right">
                                        {{ $selectedKomplain->tanggal_selesai?->translatedFormat('d M Y H:i') ?? '-' }}
                                    </b>
                                </div>
                                <div class="flex items-center justify-between gap-3 border-b border-dashed border-hairline pb-2">
                                    <span class="text-mute dark:text-zinc-400">Ditangani oleh</span>
                                    <b class="font-medium text-ink dark:text-white text-right">
                                        {{ $selectedKomplain->ditanganiOleh?->name ?? 'Admin' }}
                                    </b>
                                </div>
                                <div class="pt-1">
                                    <span class="text-mute dark:text-zinc-400 block mb-1">Catatan:</span>
                                    <p class="p-2.5 rounded-lg bg-surface-inset text-body leading-relaxed">
                                        {{ $selectedKomplain->catatan_penyelesaian ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Footer Drawer --}}
                <div class="border-t border-hairline p-4 flex justify-end">
                    <button type="button" wire:click="$set('tampilDetail', false)"
                        class="min-h-11 h-9 w-full rounded-lg border border-hairline bg-surface text-xs font-medium text-ink hover:bg-surface-inset transition cursor-pointer shadow-xs dark:bg-zinc-800 dark:text-zinc-200">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
