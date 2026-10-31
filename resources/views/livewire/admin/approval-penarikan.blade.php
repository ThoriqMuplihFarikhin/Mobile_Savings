<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Keuangan"
        title="Approval Penarikan"
        subtitle="Setujui atau tolak pengajuan penarikan nasabah."
    />

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-primary/20 bg-primary-soft px-4 py-3 text-sm text-on-primary-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-danger/20 bg-danger-soft px-4 py-3 text-sm text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- KPI Cards sesuai prototipe PG.penarikan --}}
    <x-admin.kpi-grid cols="3">
        <x-admin.kpi-card
            label="Menunggu persetujuan"
            :value="$ringkasanStatus['pending'] ?? 0"
            :sub="($ringkasanStatus['pending'] ?? 0) > 0 ? 'Perlu tindakan' : 'Semua beres'"
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

    {{-- Filter Segment --}}
    <div class="flex flex-wrap gap-2">
        @foreach(['pending' => 'Pending', 'approved' => 'Disetujui', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan', 'kedaluwarsa' => 'Kedaluwarsa'] as $value => $label)
            <button type="button" wire:click="$set('statusFilter', '{{ $value }}')"
                class="inline-flex h-[38px] items-center gap-2 rounded-[10px] px-3.5 text-xs font-medium transition {{ $statusFilter === $value ? 'bg-[var(--color-accent)] text-white shadow-xs' : 'border border-[var(--color-admin-border)] bg-[var(--color-surface)] text-[var(--color-admin-text)] hover:bg-[var(--color-surface-2)]' }}">
                <span>{{ $label }}</span>
                @if(isset($ringkasanStatus[$value]) && $ringkasanStatus[$value] > 0)
                    <span class="rounded-full px-1.5 py-0.5 text-[10px] font-semibold {{ $statusFilter === $value ? 'bg-white/20 text-white' : 'bg-[var(--color-surface-2)] text-[var(--color-admin-mute)]' }}">
                        {{ $ringkasanStatus[$value] }}
                    </span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Tabel Desktop --}}
    <div class="overflow-hidden rounded-[14px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] shadow-xs">
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-[var(--color-admin-border)] bg-[var(--color-surface-2)]">
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Nasabah</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Produk</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Diminta</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Diterima</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Lokasi</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-admin-border)]">
                    @forelse($penarikan as $item)
                        <tr class="transition hover:bg-[var(--color-surface-2)]">
                            <td class="px-4 py-3">
                                <div class="font-medium text-[var(--color-admin-text)]">{{ $item->nasabah->name ?? '-' }}</div>
                                <div class="text-xs text-[var(--color-admin-mute)]">{{ $item->nasabah->no_hp ?? '— (offline)' }}</div>
                            </td>
                            <td class="px-4 py-3 text-[var(--color-admin-text)]">{{ $item->produk->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-medium text-[var(--color-admin-text)]">Rp {{ number_format($item->nominal_diminta, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-medium text-[var(--color-accent)]">Rp {{ number_format($item->nominal_diterima, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-[var(--color-admin-mute)]">{{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $mapStatus = [
                                        'pending' => 'Pending',
                                        'approved' => 'Disetujui',
                                        'selesai' => 'Selesai',
                                        'dibatalkan' => 'Dibatalkan',
                                        'kedaluwarsa' => 'Kedaluwarsa',
                                        'ditolak' => 'Ditolak',
                                    ];
                                @endphp
                                <x-admin.status-chip :status="$mapStatus[$item->status] ?? ucfirst($item->status)" />
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($item->bukti_path)
                                        <a href="{{ route('penarikan.bukti', $item) }}"
                                            @click.prevent="$dispatch('buka-foto-bukti', { url: '{{ route('penarikan.bukti', $item) }}', judul: 'Bukti Penarikan Offline', keterangan: '{{ $item->nasabah?->name ?? 'Nasabah' }}' })"
                                            class="inline-flex h-8 items-center rounded-[8px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-2.5 text-xs font-medium text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)] cursor-pointer">Lihat bukti</a>
                                    @endif
                                    @if($item->status === 'pending')
                                        @if($item->disetujui_oleh === null || (int) $item->disetujui_oleh !== (int) auth()->id())
                                            <button type="button" wire:click="approve({{ $item->id }})" wire:confirm="Yakin menyetujui penarikan ini? Saldo nasabah akan dikurangi dan penarikan diproses."
                                                class="inline-flex h-8 items-center rounded-[8px] bg-[var(--color-accent)] px-3 text-xs font-medium text-white transition hover:opacity-90">Setuju</button>
                                        @else
                                            <span class="inline-flex items-center rounded-full border border-warning/20 bg-warning-soft px-2.5 py-1 text-xs font-medium text-warning-deep">Menunggu admin kedua</span>
                                        @endif
                                        <button type="button" wire:click="reject({{ $item->id }})" wire:confirm="Yakin menolak pengajuan penarikan ini?"
                                            class="inline-flex h-8 items-center rounded-[8px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-xs font-medium text-red-600 transition hover:bg-red-50 dark:hover:bg-red-950/30">Tolak</button>
                                        @if($item->disetujui_oleh !== null && (int) $item->disetujui_oleh !== (int) auth()->id())
                                            <div class="text-[11px] text-[var(--color-admin-mute)]">Disetujui pertama: {{ $item->disetujuiOleh->name ?? '-' }}</div>
                                        @endif
                                    @elseif($item->status === 'approved')
                                        @if($item->disetujui_oleh_2 !== null && in_array((int) auth()->id(), array_map('intval', [$item->disetujui_oleh, $item->disetujui_oleh_2]), true))
                                            <span class="inline-flex items-center rounded-full border border-warning/20 bg-warning-soft px-2.5 py-1 text-xs font-medium text-warning-deep">Menunggu admin lain</span>
                                        @elseif($item->jalur_pengajuan === 'offline' && $item->lokasi_pengambilan === 'rumah_kolektor')
                                            <span class="inline-flex items-center rounded-full border border-warning/20 bg-warning-soft px-2.5 py-1 text-xs font-medium text-warning-deep">Menunggu verifikasi kolektor</span>
                                        @else
                                            <button type="button" wire:click="toggleSelesai({{ $item->id }})"
                                                class="inline-flex h-8 items-center rounded-[8px] bg-[var(--color-accent)] px-3 text-xs font-medium text-white transition hover:opacity-90">Tandai Selesai</button>
                                        @endif
                                    @elseif($item->status === 'selesai' && $item->metode_verifikasi === 'pin_nasabah')
                                        <span class="text-xs text-[var(--color-accent)] font-medium">Diverifikasi PIN nasabah</span>
                                    @else
                                        <span class="text-xs text-[var(--color-admin-mute)]">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-0">
                                <x-admin.empty-state
                                    title="Tidak ada data penarikan"
                                    description="Belum ada penarikan dalam status ini."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Fallback Kartu Mobile --}}
        <div data-test="kartu-tabel" class="divide-y divide-[var(--color-admin-border)] md:hidden">
            @forelse($penarikan as $item)
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-medium text-[var(--color-admin-text)]">{{ $item->nasabah->name ?? '-' }}</p>
                            <p class="text-xs text-[var(--color-admin-mute)]">{{ $item->nasabah->no_hp ?? '— (offline)' }}</p>
                        </div>
                        @php
                            $mapStatus = [
                                'pending' => 'Pending',
                                'approved' => 'Disetujui',
                                'selesai' => 'Selesai',
                                'dibatalkan' => 'Dibatalkan',
                                'kedaluwarsa' => 'Kedaluwarsa',
                                'ditolak' => 'Ditolak',
                            ];
                        @endphp
                        <x-admin.status-chip :status="$mapStatus[$item->status] ?? ucfirst($item->status)" />
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs border-t border-b border-[var(--color-admin-border)] py-2">
                        <div>
                            <span class="text-[var(--color-admin-mute)]">Produk:</span>
                            <div class="font-medium text-[var(--color-admin-text)]">{{ $item->produk->nama ?? '-' }}</div>
                        </div>
                        <div>
                            <span class="text-[var(--color-admin-mute)]">Lokasi:</span>
                            <div class="font-medium text-[var(--color-admin-text)]">{{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}</div>
                        </div>
                        <div>
                            <span class="text-[var(--color-admin-mute)]">Diminta:</span>
                            <div class="font-medium tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($item->nominal_diminta, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-[var(--color-admin-mute)]">Diterima:</span>
                            <div class="font-medium tabular-nums text-[var(--color-accent)]">Rp {{ number_format($item->nominal_diterima, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-2 pt-1">
                        @if($item->bukti_path)
                            <a href="{{ route('penarikan.bukti', $item) }}"
                                @click.prevent="$dispatch('buka-foto-bukti', { url: '{{ route('penarikan.bukti', $item) }}', judul: 'Bukti Penarikan Offline', keterangan: '{{ $item->nasabah?->name ?? 'Nasabah' }}' })"
                                class="inline-flex h-8 items-center rounded-[8px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-2.5 text-xs font-medium text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)] cursor-pointer">Lihat bukti</a>
                        @endif
                        @if($item->status === 'pending')
                            @if($item->disetujui_oleh === null || (int) $item->disetujui_oleh !== (int) auth()->id())
                                <button type="button" wire:click="approve({{ $item->id }})" wire:confirm="Yakin menyetujui penarikan ini? Saldo nasabah akan dikurangi dan penarikan diproses."
                                    class="inline-flex h-8 items-center rounded-[8px] bg-[var(--color-accent)] px-3 text-xs font-medium text-white transition hover:opacity-90">Setuju</button>
                            @else
                                <span class="inline-flex items-center rounded-full border border-warning/20 bg-warning-soft px-2.5 py-1 text-xs font-medium text-warning-deep">Menunggu admin kedua</span>
                            @endif
                            <button type="button" wire:click="reject({{ $item->id }})" wire:confirm="Yakin menolak pengajuan penarikan ini?"
                                class="inline-flex h-8 items-center rounded-[8px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-xs font-medium text-red-600 transition hover:bg-red-50 dark:hover:bg-red-950/30">Tolak</button>
                        @elseif($item->status === 'approved')
                            @if($item->disetujui_oleh_2 !== null && in_array((int) auth()->id(), array_map('intval', [$item->disetujui_oleh, $item->disetujui_oleh_2]), true))
                                <span class="inline-flex items-center rounded-full border border-warning/20 bg-warning-soft px-2.5 py-1 text-xs font-medium text-warning-deep">Menunggu admin lain</span>
                            @elseif($item->jalur_pengajuan === 'offline' && $item->lokasi_pengambilan === 'rumah_kolektor')
                                <span class="inline-flex items-center rounded-full border border-warning/20 bg-warning-soft px-2.5 py-1 text-xs font-medium text-warning-deep">Menunggu verifikasi kolektor</span>
                            @else
                                <button type="button" wire:click="toggleSelesai({{ $item->id }})"
                                    class="inline-flex h-8 items-center rounded-[8px] bg-[var(--color-accent)] px-3 text-xs font-medium text-white transition hover:opacity-90">Tandai Selesai</button>
                            @endif
                        @elseif($item->status === 'selesai' && $item->metode_verifikasi === 'pin_nasabah')
                            <span class="text-xs text-[var(--color-accent)] font-medium">Diverifikasi PIN nasabah</span>
                        @else
                            <span class="text-xs text-[var(--color-admin-mute)]">-</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6">
                    <x-admin.empty-state
                        title="Tidak ada data penarikan"
                        description="Belum ada penarikan dalam status ini."
                    />
                </div>
            @endforelse
        </div>

        @if($penarikan->hasPages())
            <div class="border-t border-[var(--color-admin-border)] px-4 py-3">
                {{ $penarikan->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Tandai Selesai --}}
    @if($showSelesai)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modal-selesai-penarikan-title">
            <div class="fixed inset-0 bg-black/60 transition-opacity" wire:click="$set('showSelesai', false)" aria-hidden="true"></div>

            <div class="relative w-full max-w-md rounded-[16px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-[var(--color-admin-border)]">
                    <div>
                        <h3 id="modal-selesai-penarikan-title" class="text-base font-semibold text-[var(--color-admin-text)]">Tandai Penarikan Selesai</h3>
                        <p class="mt-0.5 text-xs text-[var(--color-admin-mute)]">Pencairan manual admin wajib mencatat alasan.</p>
                    </div>
                    <button type="button" wire:click="$set('showSelesai', false)" aria-label="Tutup"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-[var(--color-admin-mute)] transition hover:bg-[var(--color-surface-2)] hover:text-[var(--color-admin-text)]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="selesai({{ $selesaiId }})" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Alasan</label>
                        <textarea wire:model="alasan" rows="3"
                            class="w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 py-2 text-sm text-[var(--color-admin-text)] placeholder-[var(--color-admin-mute)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                            placeholder="Alasan pencairan manual..."></textarea>
                        @error('alasan') <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-[var(--color-admin-border)]">
                        <button type="button" wire:click="$set('showSelesai', false)"
                            class="h-[38px] rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-4 text-sm font-medium text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)]">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="selesai"
                            class="h-[38px] rounded-[10px] bg-[var(--color-accent)] px-4 text-sm font-medium text-white transition hover:opacity-90 disabled:opacity-50">
                            Tandai Selesai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>