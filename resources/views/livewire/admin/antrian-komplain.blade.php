<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Operasional"
        title="Antrian Komplain"
        subtitle="Tinjau dan selesaikan komplain dari nasabah."
    />

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-primary/20 bg-primary-soft p-3 text-xs font-medium text-on-primary-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- KPI Cards --}}
    <x-admin.kpi-grid columns="3">
        <x-admin.kpi-card
            label="Baru"
            value="{{ $ringkasanStatus['baru'] ?? 0 }}"
            sub="{{ ($ringkasanStatus['baru'] ?? 0) > 0 ? 'Perlu tindakan' : 'Semua beres' }}"
            variant="{{ ($ringkasanStatus['baru'] ?? 0) > 0 ? 'warn' : 'default' }}"
        />
        <x-admin.kpi-card
            label="Diproses"
            value="{{ $ringkasanStatus['diproses'] ?? 0 }}"
            sub="Sedang ditangani"
        />
        <x-admin.kpi-card
            label="Selesai"
            value="{{ $ringkasanStatus['selesai'] ?? 0 }}"
            sub="Terselesaikan"
        />
    </x-admin.kpi-grid>

    {{-- Filter Segment --}}
    <div class="flex flex-wrap gap-2">
        @foreach(['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $value => $label)
            <button wire:click="$set('statusFilter', '{{ $value }}')"
                class="min-h-11 inline-flex h-[38px] items-center gap-2 rounded-[10px] px-4 text-xs font-semibold transition {{ $statusFilter === $value ? 'bg-primary text-on-primary shadow-xs' : 'border border-hairline bg-surface text-ink hover:bg-inset' }}">
                <span>{{ $label }}</span>
                @if(isset($ringkasanStatus[$value]) && $ringkasanStatus[$value] > 0)
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $statusFilter === $value ? 'bg-white/20 text-on-primary' : 'bg-inset text-mute' }}">
                        {{ $ringkasanStatus[$value] }}
                    </span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Tabel Komplain --}}
    <div class="overflow-hidden rounded-[14px] border border-hairline bg-white shadow-xs dark:bg-surface">
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-hairline bg-inset/50">
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Tanggal</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Nasabah</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Kategori</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Deskripsi</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-mute">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($komplains as $item)
                        <tr class="transition hover:bg-inset/30">
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-body">{{ $item->tanggal_dibuat->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="text-xs font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</div>
                                <div class="text-[11px] text-mute">{{ $item->nasabah->no_hp ?? '— (offline)' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full border border-hairline bg-inset/60 px-2.5 py-0.5 text-[11px] font-medium text-ink">
                                    {{ ucfirst(str_replace('_', ' ', $item->kategori)) }}
                                </span>
                            </td>
                            <td class="max-w-[240px] truncate px-4 py-3 text-xs text-body">{{ $item->deskripsi }}</td>
                            <td class="px-4 py-3">
                                @if($item->status === 'baru')
                                    <x-admin.status-chip label="Baru" variant="info" />
                                @elseif($item->status === 'diproses')
                                    <x-admin.status-chip label="Diproses" variant="warn" />
                                @else
                                    <x-admin.status-chip label="Selesai" variant="success" />
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($item->status === 'baru')
                                    <div class="inline-flex items-center gap-1.5">
                                        <button wire:click="proses({{ $item->id }})"
                                            class="min-h-11 inline-flex h-8 items-center rounded-[8px] bg-primary px-3 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover">Proses</button>
                                        <button wire:click="showDetail({{ $item->id }})"
                                            class="min-h-11 inline-flex h-8 items-center rounded-[8px] border border-hairline bg-white px-3 text-xs font-semibold text-ink shadow-xs transition hover:bg-inset dark:bg-surface">Detail</button>
                                    </div>
                                @elseif($item->status === 'diproses')
                                    <button wire:click="showDetail({{ $item->id }})"
                                        class="min-h-11 inline-flex h-8 items-center rounded-[8px] bg-primary px-3 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover">Selesaikan</button>
                                @else
                                    <span class="text-xs text-mute">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                    <p class="text-xs text-mute">Tidak ada komplain.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($komplains as $item)
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-ink">{{ $item->nasabah->name ?? '-' }}</p>
                            <p class="text-[11px] text-mute">{{ $item->nasabah->no_hp ?? '— (offline)' }}</p>
                        </div>
                        @if($item->status === 'baru')
                            <x-admin.status-chip label="Baru" variant="info" />
                        @elseif($item->status === 'diproses')
                            <x-admin.status-chip label="Diproses" variant="warn" />
                        @else
                            <x-admin.status-chip label="Selesai" variant="success" />
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-x-3 gap-y-1.5 rounded-[10px] bg-inset/40 p-2.5 text-[11px]">
                        <div>
                            <span class="block text-mute">Tanggal</span>
                            <span class="tabular-nums font-medium text-body">{{ $item->tanggal_dibuat->translatedFormat('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-mute">Kategori</span>
                            <span class="font-medium text-body">{{ ucfirst(str_replace('_', ' ', $item->kategori)) }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="block text-mute">Deskripsi</span>
                            <span class="line-clamp-2 text-body">{{ $item->deskripsi }}</span>
                        </div>
                    </div>
                    @if($item->status === 'baru')
                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button wire:click="proses({{ $item->id }})"
                                class="min-h-11 inline-flex h-8 items-center rounded-[8px] bg-primary px-3 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover">Proses</button>
                            <button wire:click="showDetail({{ $item->id }})"
                                class="min-h-11 inline-flex h-8 items-center rounded-[8px] border border-hairline bg-white px-3 text-xs font-semibold text-ink shadow-xs transition hover:bg-inset dark:bg-surface">Detail</button>
                        </div>
                    @elseif($item->status === 'diproses')
                        <div class="flex items-center justify-end pt-1">
                            <button wire:click="showDetail({{ $item->id }})"
                                class="min-h-11 inline-flex h-8 items-center rounded-[8px] bg-primary px-3 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover">Selesaikan</button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <p class="text-xs text-mute">Tidak ada komplain.</p>
                </div>
            @endforelse
        </div>
        @if($komplains->hasPages())
            <div class="border-t border-hairline px-4 py-3">{{ $komplains->links() }}</div>
        @endif
    </div>

    @if($tampilDetail && $selectedKomplain)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modal-detail-komplain-title">
            <div wire:click="$set('tampilDetail', false)" class="fixed inset-0 bg-black/50 transition-opacity" aria-hidden="true"></div>
            <div class="relative w-full max-w-lg rounded-[16px] border border-hairline bg-surface p-6 shadow-2xl dark:bg-surface">
                <div class="flex items-start justify-between border-b border-hairline pb-3">
                    <div>
                        <h3 id="modal-detail-komplain-title" class="text-sm font-semibold text-ink">Detail Tiket Komplain</h3>
                        <p class="mt-0.5 text-xs text-mute">Informasi laporan dan penyelesaian komplain nasabah.</p>
                    </div>
                    <button type="button" wire:click="$set('tampilDetail', false)"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-mute transition hover:bg-inset hover:text-ink"
                        aria-label="Tutup modal">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-3 rounded-[10px] border border-hairline bg-inset/40 p-4 text-xs">
                    <div class="flex justify-between">
                        <span class="text-mute">Nasabah</span>
                        <span class="font-semibold text-ink">{{ $selectedKomplain->nasabah->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-mute">Kategori</span>
                        <span class="font-medium text-body">{{ ucfirst(str_replace('_', ' ', $selectedKomplain->kategori)) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-mute">Tanggal Laporan</span>
                        <span class="text-body">{{ $selectedKomplain->tanggal_dibuat->translatedFormat('d M Y H:i') }}</span>
                    </div>
                    @if($selectedKomplain->transaksiTerkait)
                        <div class="border-t border-hairline pt-3">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-mute">Transaksi Terkait</span>
                            <div class="mt-2 space-y-1.5 rounded-[8px] border border-hairline bg-surface p-3 text-[11px]">
                                <div class="flex justify-between">
                                    <span class="text-mute">Tanggal Transaksi</span>
                                    <span class="text-body">{{ $selectedKomplain->transaksiTerkait->tanggal_transaksi->translatedFormat('d M Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-mute">Produk</span>
                                    <span class="text-body">{{ $selectedKomplain->transaksiTerkait->produk->nama ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-mute">Nominal</span>
                                    <span class="tabular-nums font-semibold text-ink">Rp {{ number_format($selectedKomplain->transaksiTerkait->nominal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="border-t border-hairline pt-3">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-mute">Deskripsi Masalah</span>
                        <p class="mt-1 leading-relaxed text-body">{{ $selectedKomplain->deskripsi }}</p>
                    </div>
                </div>

                <form wire:submit="selesai" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink">Catatan Penyelesaian</label>
                        <textarea wire:model="catatan" rows="3"
                            class="w-full rounded-[10px] border border-hairline bg-white px-3 py-2 text-xs text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"
                            placeholder="Jelaskan tindakan yang dilakukan..."></textarea>
                        @error('catatan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('tampilDetail', false)"
                            class="h-[38px] rounded-[10px] border border-hairline bg-white px-4 text-xs font-semibold text-ink transition hover:bg-inset dark:bg-surface">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex h-[38px] items-center gap-2 rounded-[10px] bg-primary px-5 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover">
                            Selesaikan Komplain
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>