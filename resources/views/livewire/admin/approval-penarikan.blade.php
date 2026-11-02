<div class="space-y-5">
    {{-- Header Halaman --}}
    <x-admin.page-header
        title="Approval Penarikan"
        subtitle="Setujui atau tolak pengajuan penarikan nasabah."
    />

    {{-- Banner Notifikasi Flash --}}
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-primary/20 bg-primary-soft px-4 py-3 text-xs font-medium text-on-primary-soft">
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-danger/20 bg-danger-soft px-4 py-3 text-xs font-medium text-danger">
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- KPI Cards sesuai prototipe PG.penarikan --}}
    <x-admin.kpi-grid cols="3">
        <x-admin.kpi-card
            label="Menunggu persetujuan"
            :value="$ringkasanStatus['pending'] ?? 0"
            sub="Perlu tindakan"
            :variant="($ringkasanStatus['pending'] ?? 0) > 0 ? 'warn' : 'default'"
        />
        <x-admin.kpi-card
            label="Disetujui"
            :value="$ringkasanStatus['approved'] ?? 0"
            sub="Menunggu serah terima"
        />
        <x-admin.kpi-card
            label="Selesai"
            :value="$ringkasanStatus['selesai'] ?? 0"
            sub="Sudah diverifikasi PIN"
        />
    </x-admin.kpi-grid>

    {{-- Card Container Tabel & Filter --}}
    <div class="rounded-[14px] border border-hairline bg-surface shadow-xs overflow-hidden">
        {{-- Toolbar Filter Segmented Tab --}}
        <div class="p-3 border-b border-hairline flex flex-wrap items-center gap-2">
            <div class="inline-flex items-center gap-1 rounded-[10px] border border-hairline bg-surface-2 p-[3px]" role="group" aria-label="Status Penarikan">
                @php
                    $filterTabs = [
                        'semua' => 'Semua',
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                        'kedaluwarsa' => 'Kedaluwarsa',
                        'ditolak' => 'Ditolak',
                    ];
                @endphp
                @foreach($filterTabs as $val => $label)
                    <button
                        type="button"
                        wire:click="$set('statusFilter', '{{ $val }}')"
                        class="min-h-11 inline-flex items-center justify-center rounded-[7px] px-3 py-1 text-xs transition cursor-pointer {{ $statusFilter === $val ? 'bg-surface text-ink font-semibold shadow-xs' : 'text-mute hover:text-ink font-medium' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Tabel Desktop --}}
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-surface-2 text-xs uppercase tracking-[0.04em] text-mute font-medium border-b border-hairline">
                        <th class="px-4 py-2.5">Nasabah</th>
                        <th class="px-4 py-2.5">Produk</th>
                        <th class="px-4 py-2.5 text-right">Diminta</th>
                        <th class="px-4 py-2.5 text-right">Diterima</th>
                        <th class="px-4 py-2.5">Lokasi</th>
                        <th class="px-4 py-2.5 text-center">Status</th>
                        <th class="px-4 py-2.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($penarikan as $item)
                        <tr class="hover:bg-surface-2 transition text-ink">
                            <td class="px-4 py-3">
                                <div class="font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-body">
                                {{ $item->produk->nama ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-xs font-medium text-ink tabular-nums">
                                Rp {{ number_format($item->nominal_diminta, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-xs font-medium text-ink tabular-nums">
                                Rp {{ number_format($item->nominal_diterima, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-body">
                                {{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                <x-admin.status-chip domain="penarikan" :status="$item->status" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($item->bukti_path)
                                        <a
                                            href="{{ route('penarikan.bukti', $item) }}"
                                            @click.prevent="$dispatch('buka-foto-bukti', { url: '{{ route('penarikan.bukti', $item) }}', judul: 'Bukti Penarikan Offline', keterangan: '{{ $item->nasabah?->name ?? 'Nasabah' }}' })"
                                            class="min-h-11 h-8 inline-flex items-center justify-center rounded-[8px] border border-hairline bg-surface px-2.5 text-xs font-medium text-ink transition hover:bg-surface-2 active:scale-95 cursor-pointer shadow-xs"
                                        >
                                            Lihat bukti
                                        </a>
                                    @endif
                                    <button
                                        type="button"
                                        wire:click="bukaDetail({{ $item->id }})"
                                        class="min-h-11 h-8 inline-flex items-center justify-center rounded-[8px] border border-hairline bg-surface px-2.5 text-xs font-medium text-ink transition hover:bg-surface-2 active:scale-95 cursor-pointer shadow-xs"
                                    >
                                        Detail
                                    </button>
                                    @if($item->status === 'pending')
                                        @if($item->disetujui_oleh === null || (int) $item->disetujui_oleh !== (int) auth()->id())
                                            <button
                                                type="button"
                                                wire:click="approve({{ $item->id }})"
                                                wire:confirm="Yakin menyetujui penarikan ini? Saldo nasabah akan dikurangi dan penarikan diproses."
                                                class="min-h-11 h-8 inline-flex items-center justify-center rounded-[8px] bg-primary px-3 text-xs font-semibold text-white transition hover:bg-primary-hover active:scale-95 cursor-pointer shadow-xs"
                                            >
                                                Setuju
                                            </button>
                                        @else
                                            <span class="inline-flex items-center rounded-full border border-warning/20 bg-warning-soft px-2.5 py-1 text-xs font-medium text-warning-deep">
                                                Menunggu admin kedua
                                            </span>
                                        @endif
                                        <button
                                            type="button"
                                            wire:click="reject({{ $item->id }})"
                                            wire:confirm="Yakin menolak pengajuan penarikan ini?"
                                            class="min-h-11 h-8 inline-flex items-center justify-center rounded-[8px] bg-danger-soft px-3 text-xs font-semibold text-danger transition hover:bg-danger hover:text-white active:scale-95 cursor-pointer"
                                        >
                                            Tolak
                                        </button>
                                    @elseif($item->status === 'approved')
                                        @if($item->disetujui_oleh_2 !== null && in_array((int) auth()->id(), array_map('intval', [$item->disetujui_oleh, $item->disetujui_oleh_2]), true))
                                            <span class="inline-flex items-center rounded-full border border-warning/20 bg-warning-soft px-2.5 py-1 text-xs font-medium text-warning-deep">
                                                Menunggu admin lain
                                            </span>
                                        @elseif($item->jalur_pengajuan === 'offline' && $item->lokasi_pengambilan === 'rumah_kolektor')
                                            <span class="inline-flex items-center rounded-full border border-warning/20 bg-warning-soft px-2.5 py-1 text-xs font-medium text-warning-deep">
                                                Menunggu verifikasi kolektor
                                            </span>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="toggleSelesai({{ $item->id }})"
                                                class="min-h-11 h-8 inline-flex items-center justify-center rounded-[8px] border border-hairline bg-surface px-3 text-xs font-medium text-ink transition hover:bg-surface-2 active:scale-95 cursor-pointer shadow-xs"
                                            >
                                                Tandai selesai
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-0">
                                <x-admin.empty-state
                                    title="Tidak ada data penarikan"
                                    description="Belum ada penarikan dalam filter ini."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Fallback Kartu Mobile --}}
        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($penarikan as $item)
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-ink">{{ $item->nasabah->name ?? '-' }}</p>
                            <p class="text-xs text-mute mt-0.5">{{ $item->produk->nama ?? '-' }}</p>
                        </div>
                        <x-admin.status-chip domain="penarikan" :status="$item->status" />
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs py-2 border-y border-hairline">
                        <div>
                            <span class="text-mute block">Lokasi:</span>
                            <span class="text-ink font-medium">{{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}</span>
                        </div>
                        <div>
                            <span class="text-mute block">Diminta:</span>
                            <span class="text-ink font-medium tabular-nums">Rp {{ number_format($item->nominal_diminta, 0, ',', '.') }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-mute block">Diterima:</span>
                            <span class="text-ink font-semibold tabular-nums">Rp {{ number_format($item->nominal_diterima, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-2 pt-1">
                        @if($item->bukti_path)
                            <a
                                href="{{ route('penarikan.bukti', $item) }}"
                                @click.prevent="$dispatch('buka-foto-bukti', { url: '{{ route('penarikan.bukti', $item) }}', judul: 'Bukti Penarikan Offline', keterangan: '{{ $item->nasabah?->name ?? 'Nasabah' }}' })"
                                class="min-h-11 h-9 inline-flex items-center justify-center rounded-[8px] border border-hairline bg-surface px-3 text-xs font-medium text-ink transition hover:bg-surface-2 active:scale-95 cursor-pointer shadow-xs"
                            >
                                Lihat bukti
                            </a>
                        @endif
                        <button
                            type="button"
                            wire:click="bukaDetail({{ $item->id }})"
                            class="min-h-11 h-9 inline-flex items-center justify-center rounded-[8px] border border-hairline bg-surface px-3 text-xs font-medium text-ink transition hover:bg-surface-2 active:scale-95 cursor-pointer shadow-xs"
                        >
                            Detail
                        </button>
                        @if($item->status === 'pending')
                            @if($item->disetujui_oleh === null || (int) $item->disetujui_oleh !== (int) auth()->id())
                                <button
                                    type="button"
                                    wire:click="approve({{ $item->id }})"
                                    wire:confirm="Yakin menyetujui penarikan ini? Saldo nasabah akan dikurangi dan penarikan diproses."
                                    class="min-h-11 flex-1 h-9 inline-flex items-center justify-center rounded-[8px] bg-primary px-3 text-xs font-semibold text-white transition hover:bg-primary-hover active:scale-95 cursor-pointer shadow-xs"
                                >
                                    Setuju
                                </button>
                            @else
                                <span class="inline-flex items-center rounded-full border border-warning/20 bg-warning-soft px-2.5 py-1 text-xs font-medium text-warning-deep">
                                    Menunggu admin kedua
                                </span>
                            @endif
                            <button
                                type="button"
                                wire:click="reject({{ $item->id }})"
                                wire:confirm="Yakin menolak pengajuan penarikan ini?"
                                class="min-h-11 flex-1 h-9 inline-flex items-center justify-center rounded-[8px] bg-danger-soft px-3 text-xs font-semibold text-danger transition hover:bg-danger hover:text-white active:scale-95 cursor-pointer"
                            >
                                Tolak
                            </button>
                        @elseif($item->status === 'approved')
                            @if($item->disetujui_oleh_2 !== null && in_array((int) auth()->id(), array_map('intval', [$item->disetujui_oleh, $item->disetujui_oleh_2]), true))
                                <span class="inline-flex items-center rounded-full border border-warning/20 bg-warning-soft px-2.5 py-1 text-xs font-medium text-warning-deep">
                                    Menunggu admin lain
                                </span>
                            @elseif($item->jalur_pengajuan === 'offline' && $item->lokasi_pengambilan === 'rumah_kolektor')
                                <span class="inline-flex items-center rounded-full border border-warning/20 bg-warning-soft px-2.5 py-1 text-xs font-medium text-warning-deep">
                                    Menunggu verifikasi kolektor
                                </span>
                            @else
                                <button
                                    type="button"
                                    wire:click="toggleSelesai({{ $item->id }})"
                                    class="min-h-11 flex-1 h-9 inline-flex items-center justify-center rounded-[8px] border border-hairline bg-surface px-3 text-xs font-medium text-ink transition hover:bg-surface-2 active:scale-95 cursor-pointer shadow-xs"
                                >
                                    Tandai selesai
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6">
                    <x-admin.empty-state
                        title="Tidak ada data penarikan"
                        description="Belum ada penarikan dalam filter ini."
                    />
                </div>
            @endforelse
        </div>

        @if($penarikan->count() > 0)
            <div class="flex items-center justify-between border-t border-hairline px-4 py-2.5 text-xs text-mute bg-surface">
                <span>Menampilkan {{ $penarikan->count() }} dari {{ $penarikan->total() }} data</span>
                @if($penarikan->hasPages())
                    <div>{{ $penarikan->links() }}</div>
                @else
                    <span>Halaman 1 dari 1</span>
                @endif
            </div>
        @endif
    </div>

    {{-- Drawer Detail Penarikan --}}
    @if($showDetail && $detailPenarikan)
        {{-- Backdrop Scrim --}}
        <div class="fixed inset-0 bg-black/40 z-40 transition-opacity" wire:click="tutupDetail"></div>

        {{-- Drawer Slide-over Panel --}}
        <aside class="fixed inset-y-0 right-0 z-50 w-full max-w-[500px] bg-surface border-l border-hairline shadow-2xl flex flex-col transition-transform">
            {{-- Header Drawer --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-hairline">
                <div>
                    <h3 class="text-base font-semibold text-ink">Detail Penarikan</h3>
                    <p class="text-xs text-mute mt-0.5">ID Transaksi: #{{ $detailPenarikan->id }}</p>
                </div>
                <button
                    type="button"
                    wire:click="tutupDetail"
                    class="flex size-8 items-center justify-center rounded-[8px] border border-hairline text-mute hover:text-ink hover:bg-surface-2 transition cursor-pointer"
                    aria-label="Tutup"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Isi Drawer --}}
            <div class="p-5 overflow-y-auto flex-1 space-y-5 text-sm">
                {{-- 1. Status Penarikan --}}
                <div class="flex items-center justify-between p-3.5 rounded-[12px] bg-surface-2 border border-hairline">
                    <div>
                        <span class="text-xs text-mute block">Status Pengajuan</span>
                        <b class="text-sm font-semibold text-ink capitalize">{{ $detailPenarikan->status }}</b>
                    </div>
                    <x-admin.status-chip domain="penarikan" :status="$detailPenarikan->status" />
                </div>

                {{-- 2. Rincian Nasabah --}}
                <div class="space-y-1">
                    <h4 class="text-xs uppercase tracking-[0.05em] text-mute font-medium mb-1">Data Nasabah</h4>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Nama</span>
                        <b class="font-medium text-ink">{{ $detailPenarikan->nasabah->name ?? '-' }}</b>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">No. HP</span>
                        <b class="font-medium text-ink tabular-nums">{{ $detailPenarikan->nasabah->no_hp ?? '— (offline)' }}</b>
                    </div>
                    @if($detailPenarikan->nasabah?->nasabahProfil?->alamat)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">Alamat</span>
                            <span class="font-medium text-ink text-right text-xs max-w-[240px]">{{ $detailPenarikan->nasabah->nasabahProfil->alamat }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between gap-3 py-2 border-b-0 text-sm">
                        <span class="text-mute">Mode Akses</span>
                        <x-admin.status-chip domain="mode_akses" :status="$detailPenarikan->nasabah?->mode_akses ?? 'digital'" :teks="ucfirst($detailPenarikan->nasabah?->mode_akses ?? 'Digital')" />
                    </div>
                </div>

                {{-- 3. Rincian Nominal & Keuangan --}}
                <div class="space-y-1">
                    <h4 class="text-xs uppercase tracking-[0.05em] text-mute font-medium mb-1">Rincian Penarikan</h4>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Produk Tabungan</span>
                        <b class="font-medium text-ink">{{ $detailPenarikan->produk->nama ?? '-' }}</b>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Nominal Diminta</span>
                        <b class="font-medium text-ink tabular-nums">Rp {{ number_format($detailPenarikan->nominal_diminta, 0, ',', '.') }}</b>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Potongan Komisi ({{ $detailPenarikan->persen_komisi_terpakai }}%)</span>
                        <span class="text-danger font-medium tabular-nums">- Rp {{ number_format($detailPenarikan->nominal_komisi, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b-0 text-sm">
                        <span class="text-mute font-medium">Bersih Diterima</span>
                        <b class="font-semibold text-primary text-base tabular-nums">Rp {{ number_format($detailPenarikan->nominal_diterima, 0, ',', '.') }}</b>
                    </div>
                </div>

                {{-- 4. Pengiriman & Operasional --}}
                <div class="space-y-1">
                    <h4 class="text-xs uppercase tracking-[0.05em] text-mute font-medium mb-1">Operasional & Lokasi</h4>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Lokasi Pengambilan</span>
                        <b class="font-medium text-ink">{{ $detailPenarikan->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}</b>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Jalur Pengajuan</span>
                        <span class="font-medium text-ink capitalize">{{ $detailPenarikan->jalur_pengajuan ?? 'Online' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Waktu Diajukan</span>
                        <span class="text-ink font-medium tabular-nums">{{ $detailPenarikan->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($detailPenarikan->disetujuiOleh)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">Disetujui Oleh (1)</span>
                            <span class="text-ink font-medium">{{ $detailPenarikan->disetujuiOleh->name }}</span>
                        </div>
                    @endif
                    @if($detailPenarikan->disetujuiOleh2)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">Disetujui Oleh (2)</span>
                            <span class="text-ink font-medium">{{ $detailPenarikan->disetujuiOleh2->name }}</span>
                        </div>
                    @endif
                    @if($detailPenarikan->waktu_approval)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">Waktu Approval</span>
                            <span class="text-ink font-medium tabular-nums">{{ $detailPenarikan->waktu_approval->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                    @if($detailPenarikan->waktu_pencairan)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">Waktu Pencairan</span>
                            <span class="text-ink font-medium tabular-nums">{{ $detailPenarikan->waktu_pencairan->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                    @if($detailPenarikan->dibayarOleh)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">Kolektor Pembayar</span>
                            <span class="text-ink font-medium">{{ $detailPenarikan->dibayarOleh->name }}</span>
                        </div>
                    @endif
                    @if($detailPenarikan->dibatalkanOleh)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">Dibatalkan Oleh</span>
                            <span class="text-danger font-medium">{{ $detailPenarikan->dibatalkanOleh->name }}</span>
                        </div>
                    @endif
                    @if($detailPenarikan->alasan_batal)
                        <div class="flex flex-col gap-1 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">Alasan Pembatalan:</span>
                            <p class="text-xs text-danger bg-danger-soft p-2.5 rounded-[8px]">{{ $detailPenarikan->alasan_batal }}</p>
                        </div>
                    @endif
                    @if($detailPenarikan->catatan_kolektor)
                        <div class="flex flex-col gap-1 py-2 border-b-0 text-sm">
                            <span class="text-mute">Catatan Kolektor:</span>
                            <p class="text-xs text-ink bg-surface-2 p-2.5 rounded-[8px]">{{ $detailPenarikan->catatan_kolektor }}</p>
                        </div>
                    @endif
                </div>

                {{-- 5. Bukti Penarikan (Bila Ada) --}}
                @if($detailPenarikan->bukti_path)
                    <div class="space-y-2 pt-2 border-t border-hairline">
                        <h4 class="text-xs uppercase tracking-[0.05em] text-mute font-medium">Foto Bukti Penarikan</h4>
                        <a
                            href="{{ route('penarikan.bukti', $detailPenarikan) }}"
                            @click.prevent="$dispatch('buka-foto-bukti', { url: '{{ route('penarikan.bukti', $detailPenarikan) }}', judul: 'Bukti Penarikan Offline', keterangan: '{{ $detailPenarikan->nasabah?->name ?? 'Nasabah' }}' })"
                            class="inline-flex items-center gap-1.5 rounded-[10px] border border-hairline bg-surface px-3 py-2 text-xs font-medium text-ink transition hover:bg-surface-2 cursor-pointer shadow-xs"
                        >
                            <svg class="size-4 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            Buka Foto Bukti Penarikan
                        </a>
                    </div>
                @endif
            </div>

            {{-- Footer Drawer Aksi --}}
            <div class="p-4 border-t border-hairline bg-surface flex items-center justify-end gap-2">
                @if($detailPenarikan->status === 'pending')
                    @if($detailPenarikan->disetujui_oleh === null || (int) $detailPenarikan->disetujui_oleh !== (int) auth()->id())
                        <button
                            type="button"
                            wire:click="approve({{ $detailPenarikan->id }})"
                            wire:confirm="Yakin menyetujui penarikan ini? Saldo nasabah akan dikurangi dan penarikan diproses."
                            class="min-h-11 h-9 inline-flex items-center justify-center rounded-[8px] bg-primary px-4 text-xs font-semibold text-white transition hover:bg-primary-hover active:scale-95 cursor-pointer shadow-xs"
                        >
                            Setuju
                        </button>
                    @endif
                    <button
                        type="button"
                        wire:click="reject({{ $detailPenarikan->id }})"
                        wire:confirm="Yakin menolak pengajuan penarikan ini?"
                        class="min-h-11 h-9 inline-flex items-center justify-center rounded-[8px] bg-danger-soft px-4 text-xs font-semibold text-danger transition hover:bg-danger hover:text-white active:scale-95 cursor-pointer"
                    >
                        Tolak
                    </button>
                @elseif($detailPenarikan->status === 'approved')
                    @if(!($detailPenarikan->disetujui_oleh_2 !== null && in_array((int) auth()->id(), array_map('intval', [$detailPenarikan->disetujui_oleh, $detailPenarikan->disetujui_oleh_2]), true)) && !($detailPenarikan->jalur_pengajuan === 'offline' && $detailPenarikan->lokasi_pengambilan === 'rumah_kolektor'))
                        <button
                            type="button"
                            wire:click="toggleSelesai({{ $detailPenarikan->id }})"
                            class="min-h-11 h-9 inline-flex items-center justify-center rounded-[8px] bg-primary px-4 text-xs font-semibold text-white transition hover:bg-primary-hover active:scale-95 cursor-pointer shadow-xs"
                        >
                            Tandai selesai
                        </button>
                    @endif
                @endif
                <button
                    type="button"
                    wire:click="tutupDetail"
                    class="min-h-11 h-9 inline-flex items-center justify-center rounded-[8px] border border-hairline bg-surface px-4 text-xs font-medium text-ink transition hover:bg-surface-2 active:scale-95 cursor-pointer shadow-xs"
                >
                    Tutup
                </button>
            </div>
        </aside>
    @endif

    {{-- Modal Tandai Selesai --}}
    @if($showSelesai)
        <x-admin.dialog
            title="Tandai Penarikan Selesai"
            message="Pencairan manual admin wajib mencatat alasan."
            confirm-text="Tandai Selesai"
            cancel-text="Batal"
            confirm-action="selesai({{ $selesaiId }})"
            cancel-action="$set('showSelesai', false)"
        >
            <form wire:submit.prevent="selesai({{ $selesaiId }})" class="space-y-3 text-left">
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Alasan Pencairan</label>
                    <textarea
                        wire:model="alasan"
                        rows="3"
                        class="w-full rounded-[10px] border border-hairline bg-surface p-2.5 text-xs text-ink placeholder-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        placeholder="Tulis alasan pencairan manual..."
                    ></textarea>
                    @error('alasan')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>
            </form>
        </x-admin.dialog>
    @endif
</div>