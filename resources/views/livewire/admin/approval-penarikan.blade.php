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