<div class="space-y-4">
    {{-- Header --}}
    <x-admin.page-header
        breadcrumb="Operasional"
        title="Antrian Komplain"
        subtitle="Tinjau dan selesaikan komplain dari nasabah."
    />

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-primary/20 bg-primary-soft p-3 text-xs font-semibold text-on-primary-soft shadow-xs">
            <svg class="size-4 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-danger/20 bg-danger-soft p-3 text-xs font-semibold text-danger shadow-xs">
            <svg class="size-4 shrink-0 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- KPI Cards (3 Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="rounded-[14px] border border-hairline bg-surface p-3.5 shadow-xs">
            <span class="text-xs text-mute font-normal block">Baru</span>
            <b class="text-[1.4rem] font-bold text-warning-deep tracking-[-0.5px] tabular-nums mt-0.5 block">
                {{ $ringkasanStatus['baru'] ?? 0 }}
            </b>
            @if(($ringkasanStatus['baru'] ?? 0) > 0)
                <small class="text-[11px] text-mute block mt-0.5">Perlu tindakan</small>
            @endif
        </div>

        <div class="rounded-[14px] border border-hairline bg-surface p-3.5 shadow-xs">
            <span class="text-xs text-mute font-normal block">Diproses</span>
            <b class="text-[1.4rem] font-bold text-ink tracking-[-0.5px] tabular-nums mt-0.5 block">
                {{ $ringkasanStatus['diproses'] ?? 0 }}
            </b>
        </div>

        <div class="rounded-[14px] border border-hairline bg-surface p-3.5 shadow-xs">
            <span class="text-xs text-mute font-normal block">Selesai</span>
            <b class="text-[1.4rem] font-bold text-ink tracking-[-0.5px] tabular-nums mt-0.5 block">
                {{ $ringkasanStatus['selesai'] ?? 0 }}
            </b>
        </div>
    </div>

    {{-- Main Table Card Container --}}
    <div class="rounded-[14px] border border-hairline bg-surface shadow-xs overflow-hidden">
        {{-- Toolbar / Segmented Tabs --}}
        <div class="p-3 sm:px-3.5 border-b border-hairline flex items-center gap-2">
            <div class="inline-flex gap-1 bg-surface-2 border border-hairline rounded-[10px] p-[3px]" role="group" aria-label="Status">
                @foreach(['semua' => 'Semua', 'baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $val => $lbl)
                    <button type="button" wire:click="setStatusFilter('{{ $val }}')"
                        class="min-h-11 sm:min-h-0 h-7 px-3 rounded-[7px] text-xs transition cursor-pointer {{ $statusFilter === $val ? 'bg-surface text-ink font-semibold shadow-xs' : 'text-mute hover:text-ink font-medium' }}">
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Desktop Table (Must include exact string 'hidden md:table' for AntreanTabelKeKartuP74bTest) --}}
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-surface-2 text-xs uppercase tracking-[0.04em] text-mute font-medium border-b border-hairline">
                        <th class="px-3.5 py-2.5 whitespace-nowrap">TANGGAL</th>
                        <th class="px-3.5 py-2.5 whitespace-nowrap">NASABAH</th>
                        <th class="px-3.5 py-2.5 whitespace-nowrap">KATEGORI</th>
                        <th class="px-3.5 py-2.5 whitespace-nowrap">DESKRIPSI</th>
                        <th class="px-3.5 py-2.5 whitespace-nowrap">STATUS</th>
                        <th class="px-3.5 py-2.5 whitespace-nowrap text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
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
                        <tr class="hover:bg-surface-2 transition text-sm text-ink">
                            <td class="px-3.5 py-3 whitespace-nowrap tabular-nums text-sm text-ink">
                                {{ $item->tanggal_dibuat ? $item->tanggal_dibuat->translatedFormat('d M') : '-' }}
                            </td>
                            <td class="px-3.5 py-3 whitespace-nowrap font-medium text-sm text-ink">
                                {{ $item->nasabah?->name ?? '-' }}
                            </td>
                            <td class="px-3.5 py-3 whitespace-nowrap text-sm text-ink">
                                {{ $labelKategori }}
                            </td>
                            <td class="px-3.5 py-3 text-sm text-ink max-w-[280px] truncate">
                                {{ $item->deskripsi }}
                            </td>
                            <td class="px-3.5 py-3 whitespace-nowrap">
                                <x-admin.status-chip domain="komplain" :status="$item->status" />
                            </td>
                            <td class="px-3.5 py-3 whitespace-nowrap text-right">
                                @if($item->status === 'baru')
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <button type="button" wire:click="proses({{ $item->id }})"
                                            class="h-8 px-3 rounded-[8px] bg-primary hover:bg-primary-hover text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer">
                                            Proses
                                        </button>
                                        <button type="button" wire:click="showDetail({{ $item->id }})"
                                            class="h-8 px-3 rounded-[8px] border border-hairline bg-surface text-ink hover:bg-surface-2 text-xs font-medium shadow-xs transition active:scale-95 cursor-pointer">
                                            Detail
                                        </button>
                                    </div>
                                @elseif($item->status === 'diproses')
                                    <button type="button" wire:click="showDetail({{ $item->id }})"
                                        class="h-8 px-3.5 rounded-[8px] bg-primary hover:bg-primary-hover text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer">
                                        Selesaikan
                                    </button>
                                @else
                                    <button type="button" wire:click="showDetail({{ $item->id }})"
                                        class="h-8 px-3 rounded-[8px] border border-hairline bg-surface text-ink hover:bg-surface-2 text-xs font-medium shadow-xs transition active:scale-95 cursor-pointer">
                                        Detail
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-mute text-xs">
                                <p class="font-medium text-ink">Tidak ada komplain.</p>
                                <p class="mt-1 text-mute">Belum ada komplain pada kategori status ini.</p>
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
                            <p class="text-sm font-semibold text-ink">{{ $item->nasabah?->name ?? '-' }}</p>
                            <p class="text-xs text-mute mt-0.5">{{ $item->tanggal_dibuat ? $item->tanggal_dibuat->translatedFormat('d M Y') : '-' }} &middot; {{ $labelKategori }}</p>
                        </div>
                        <x-admin.status-chip domain="komplain" :status="$item->status" />
                    </div>

                    <div class="p-3 rounded-xl bg-surface-2 text-xs text-body leading-relaxed">
                        {{ $item->deskripsi }}
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1">
                        @if($item->status === 'baru')
                            <button type="button" wire:click="proses({{ $item->id }})"
                                class="min-h-11 flex-1 inline-flex items-center justify-center rounded-lg bg-primary hover:bg-primary-hover px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer">
                                Proses
                            </button>
                            <button type="button" wire:click="showDetail({{ $item->id }})"
                                class="min-h-11 flex-1 inline-flex items-center justify-center rounded-lg border border-hairline bg-surface text-ink hover:bg-surface-2 px-3.5 py-2 text-xs font-medium shadow-xs transition active:scale-95 cursor-pointer">
                                Detail
                            </button>
                        @elseif($item->status === 'diproses')
                            <button type="button" wire:click="showDetail({{ $item->id }})"
                                class="min-h-11 w-full inline-flex items-center justify-center rounded-lg bg-primary hover:bg-primary-hover px-4 py-2 text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer">
                                Selesaikan
                            </button>
                        @else
                            <button type="button" wire:click="showDetail({{ $item->id }})"
                                class="min-h-11 w-full inline-flex items-center justify-center rounded-lg border border-hairline bg-surface text-ink hover:bg-surface-2 px-3.5 py-2 text-xs font-medium shadow-xs transition active:scale-95 cursor-pointer">
                                Detail
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-mute">
                    <p class="font-medium text-ink">Tidak ada komplain.</p>
                    <p class="mt-1 text-mute">Belum ada komplain pada kategori status ini.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination & Footer --}}
        <div class="flex items-center justify-between border-t border-hairline px-4 py-2.5 text-xs text-mute bg-surface">
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

            <div class="relative z-10 flex h-full w-full max-w-[560px] flex-col border-l border-hairline bg-surface shadow-2xl transition-all">
                {{-- Header Drawer (.dh) --}}
                <div class="flex items-center justify-between border-b border-hairline px-5 py-4">
                    <div>
                        <h3 id="drawer-komplain-title" class="text-base font-semibold text-ink">Detail Komplain</h3>
                    </div>
                    <button type="button" wire:click="$set('tampilDetail', false)"
                        class="flex size-8 items-center justify-center rounded-lg border border-hairline bg-surface text-mute transition hover:bg-surface-2 hover:text-ink cursor-pointer"
                        aria-label="Tutup">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Content Drawer (.db) --}}
                <div class="flex-1 overflow-y-auto p-5 space-y-4">
                    {{-- Section: Komplain (.sc) --}}
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-mute mb-2">
                            Komplain
                        </h4>
                        <div class="divide-y divide-dashed divide-hairline text-sm">
                            <div class="flex items-center justify-between gap-3 py-2">
                                <span class="text-mute">Nasabah</span>
                                <b class="font-medium text-ink text-right">{{ $selectedKomplain->nasabah?->name ?? '-' }}</b>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2">
                                <span class="text-mute">Kategori</span>
                                <b class="font-medium text-ink text-right">
                                    {{ ucfirst(str_replace('_', ' ', $selectedKomplain->kategori)) }}
                                </b>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2">
                                <span class="text-mute">Tanggal</span>
                                <b class="font-medium text-ink text-right">
                                    {{ $selectedKomplain->tanggal_dibuat?->translatedFormat('d M Y H:i') ?? '-' }}
                                </b>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2">
                                <span class="text-mute">Status</span>
                                <div>
                                    <x-admin.status-chip domain="komplain" :status="$selectedKomplain->status" />
                                </div>
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-ink leading-relaxed">
                            {{ $selectedKomplain->deskripsi }}
                        </p>
                    </div>

                    {{-- Section: Transaksi Terkait (.sc) --}}
                    @if($selectedKomplain->transaksiTerkait)
                        <div class="pt-3 border-t border-hairline">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-mute mb-2">
                                Transaksi Terkait
                            </h4>
                            <div class="divide-y divide-dashed divide-hairline text-sm">
                                <div class="flex items-center justify-between gap-3 py-2">
                                    <span class="text-mute">Tanggal Transaksi</span>
                                    <b class="font-medium text-ink text-right">
                                        {{ $selectedKomplain->transaksiTerkait->tanggal_transaksi?->translatedFormat('d M Y') ?? '-' }}
                                    </b>
                                </div>
                                <div class="flex items-center justify-between gap-3 py-2">
                                    <span class="text-mute">Produk</span>
                                    <b class="font-medium text-ink text-right">
                                        {{ $selectedKomplain->transaksiTerkait->produk?->nama ?? '-' }}
                                    </b>
                                </div>
                                <div class="flex items-center justify-between gap-3 py-2">
                                    <span class="text-mute">Nominal</span>
                                    <b class="font-semibold text-ink tabular-nums text-right">
                                        Rp {{ number_format($selectedKomplain->transaksiTerkait->nominal, 0, ',', '.') }}
                                    </b>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Section: Penyelesaian (.sc) --}}
                    @if($selectedKomplain->status !== 'selesai')
                        <div class="pt-3 border-t border-hairline space-y-3">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-mute mb-1">
                                Penyelesaian
                            </h4>
                            <div>
                                <label for="catatan" class="block text-xs font-medium text-ink mb-1">
                                    Catatan Penyelesaian
                                </label>
                                <textarea id="catatan" wire:model="catatan" rows="3"
                                    class="w-full rounded-[10px] border border-hairline bg-surface p-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    placeholder="Jelaskan tindakan yang dilakukan..."></textarea>
                                @error('catatan')
                                    <p class="mt-1 text-xs font-semibold text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex justify-end pt-1">
                                <button type="button" wire:click="selesai({{ $selectedKomplain->id }})"
                                    class="h-9 px-4 rounded-[10px] bg-primary hover:bg-primary-hover text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer">
                                    Selesaikan
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="pt-3 border-t border-hairline">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-mute mb-2">
                                Catatan Penyelesaian
                            </h4>
                            <div class="divide-y divide-dashed divide-hairline text-sm">
                                <div class="flex items-center justify-between gap-3 py-2">
                                    <span class="text-mute">Tanggal Selesai</span>
                                    <b class="font-medium text-ink text-right">
                                        {{ $selectedKomplain->tanggal_selesai?->translatedFormat('d M Y H:i') ?? '-' }}
                                    </b>
                                </div>
                                <div class="flex items-center justify-between gap-3 py-2">
                                    <span class="text-mute">Ditangani oleh</span>
                                    <b class="font-medium text-ink text-right">
                                        {{ $selectedKomplain->ditanganiOleh?->name ?? 'Admin' }}
                                    </b>
                                </div>
                            </div>
                            <p class="mt-2.5 p-3 rounded-lg bg-surface-2 text-xs text-body leading-relaxed">
                                {{ $selectedKomplain->catatan_penyelesaian ?? '-' }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
