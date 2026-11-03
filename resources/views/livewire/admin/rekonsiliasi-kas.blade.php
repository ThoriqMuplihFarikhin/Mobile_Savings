<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Keuangan"
        title="Rekonsiliasi Kas Kolektor"
        subtitle="Proses pengajuan setoran dari kolektor dan cocokkan kas fisik."
    >
        <x-slot:actions>
            <button type="button" wire:click="openInputManual"
                class="inline-flex h-[38px] items-center justify-center gap-2 rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-4 text-sm font-medium text-[var(--color-admin-text)] shadow-xs transition hover:bg-[var(--color-surface-2)] cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 -ml-0.5 text-[var(--color-admin-mute)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Input Manual Setoran Kas</span>
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-primary/20 bg-primary-soft px-4 py-3 text-sm text-on-primary-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-danger/20 bg-danger-soft px-4 py-3 text-sm text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Card: Menunggu Pemrosesan --}}
    <div class="rounded-[14px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] shadow-xs overflow-hidden">
        <div class="px-5 pt-4 pb-0">
            <h2 class="text-base font-semibold text-[var(--color-admin-text)]">Menunggu pemrosesan</h2>
        </div>

        @if($pendingSubmissions->count() > 0)
            <div class="divide-y divide-[var(--color-admin-border)] mt-3">
                @foreach($pendingSubmissions as $item)
                    <div data-test="baris-pengajuan" class="flex flex-wrap items-center justify-between gap-4 p-4 sm:px-5 sm:py-3.5 transition hover:bg-[var(--color-surface-2)]">
                        <div class="min-w-[200px] flex-1">
                            <p class="text-sm font-semibold text-[var(--color-admin-text)]">{{ $item->kolektor->name ?? '-' }}</p>
                            <p class="text-xs text-[var(--color-admin-mute)] mt-0.5">
                                {{ $item->tanggal_setor->isToday() ? 'Hari ini' : ($item->tanggal_setor->isYesterday() ? 'Kemarin' : $item->tanggal_setor->translatedFormat('d M')) }}
                                @if($item->keterangan_selisih)
                                    &middot; {{ $item->keterangan_selisih }}
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="block text-xs text-[var(--color-admin-mute)]">Total seharusnya</span>
                            <b class="text-base font-semibold tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</b>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="startProcess({{ $item->id }})"
                                class="inline-flex h-8 items-center justify-center rounded-[8px] bg-[var(--color-accent)] px-3.5 text-xs font-medium text-white transition hover:opacity-90 cursor-pointer">
                                Proses
                            </button>
                            <button type="button" wire:click="startReject({{ $item->id }})"
                                class="inline-flex h-8 items-center justify-center rounded-[8px] border border-red-500/20 bg-red-500/10 px-3.5 text-xs font-medium text-red-600 dark:text-red-400 transition hover:bg-red-500/20 cursor-pointer">
                                Tolak
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8">
                <x-admin.empty-state
                    title="Tidak ada pengajuan setoran"
                    description="Semua pengajuan sudah diproses."
                />
            </div>
        @endif
    </div>

    {{-- Card: Riwayat Rekonsiliasi --}}
    <div class="overflow-hidden rounded-[14px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] shadow-xs">
        <div class="px-5 pt-4 pb-0">
            <h2 class="text-base font-semibold text-[var(--color-admin-text)]">Riwayat Rekonsiliasi</h2>
        </div>

        {{-- Tabel Desktop --}}
        <div class="overflow-x-auto mt-3">
            <table class="hidden md:table w-full text-left text-sm">
                <thead>
                    <tr class="border-y border-[var(--color-admin-border)] bg-[var(--color-surface-2)]">
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Tanggal</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Kolektor</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Seharusnya</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Diterima</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Selisih</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-admin-border)]">
                    @forelse($riwayat as $item)
                        <tr wire:key="rekon-row-{{ $item->id }}" wire:click="bukaDetail({{ $item->id }})"
                            class="cursor-pointer transition hover:bg-[var(--color-surface-2)] group"
                            title="Klik untuk melihat rincian rekonsiliasi">
                            <td class="px-5 py-3.5 text-xs text-[var(--color-admin-mute)] whitespace-nowrap">
                                {{ $item->tanggal_setor->translatedFormat('d M') }}
                            </td>
                            <td class="px-5 py-3.5 font-medium text-[var(--color-admin-text)]">
                                <span class="group-hover:text-[var(--color-accent)] transition-colors">{{ $item->kolektor->name ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right tabular-nums text-[var(--color-admin-text)]">
                                Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-right tabular-nums text-[var(--color-admin-text)]">
                                @if($item->total_diterima !== null)
                                    Rp {{ number_format($item->total_diterima, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right tabular-nums">
                                @if($item->selisih !== null)
                                    @if($item->selisih > 0)
                                        <span class="font-medium text-warning-deep">+Rp {{ number_format($item->selisih, 0, ',', '.') }}</span>
                                    @elseif($item->selisih < 0)
                                        <span class="font-medium text-red-600 dark:text-red-400">Rp -{{ number_format(abs($item->selisih), 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-[var(--color-admin-text)] font-normal">Rp 0</span>
                                    @endif
                                @else
                                    <span class="text-[var(--color-admin-mute)]">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                @php
                                    $mapStatus = [
                                        'pending' => 'Menunggu',
                                        'cocok' => 'Cocok',
                                        'lebih' => 'Lebih',
                                        'kurang' => 'Kurang',
                                        'dibatalkan' => 'Ditolak',
                                    ];
                                @endphp
                                <x-admin.status-chip :status="$mapStatus[$item->status] ?? ucfirst($item->status)" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8">
                                <x-admin.empty-state
                                    title="Belum ada riwayat rekonsiliasi"
                                    description="Semua setoran kantor yang telah diverifikasi akan tercatat di sini."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Fallback Kartu Mobile --}}
        <div data-test="kartu-riwayat" class="divide-y divide-[var(--color-admin-border)] md:hidden">
            @forelse($riwayat as $item)
                <div wire:key="rekon-card-{{ $item->id }}" wire:click="bukaDetail({{ $item->id }})"
                    class="p-4 space-y-2.5 cursor-pointer active:bg-[var(--color-surface-2)] transition">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="font-medium text-[var(--color-admin-text)]">{{ $item->kolektor->name ?? '-' }}</p>
                            <p class="text-xs text-[var(--color-admin-mute)]">{{ $item->tanggal_setor->translatedFormat('d M') }}</p>
                        </div>
                        @php
                            $mapStatus = [
                                'pending' => 'Menunggu',
                                'cocok' => 'Cocok',
                                'lebih' => 'Lebih',
                                'kurang' => 'Kurang',
                                'dibatalkan' => 'Ditolak',
                            ];
                        @endphp
                        <x-admin.status-chip :status="$mapStatus[$item->status] ?? ucfirst($item->status)" />
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-xs border-t border-[var(--color-admin-border)] pt-2.5">
                        <div>
                            <span class="text-[var(--color-admin-mute)]">Seharusnya:</span>
                            <div class="font-medium tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-[var(--color-admin-mute)]">Diterima:</span>
                            <div class="font-medium tabular-nums text-[var(--color-admin-text)]">
                                @if($item->total_diterima !== null)
                                    Rp {{ number_format($item->total_diterima, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div>
                            <span class="text-[var(--color-admin-mute)]">Selisih:</span>
                            <div class="font-medium tabular-nums">
                                @if($item->selisih !== null)
                                    @if($item->selisih > 0)
                                        <span class="text-warning-deep">+Rp {{ number_format($item->selisih, 0, ',', '.') }}</span>
                                    @elseif($item->selisih < 0)
                                        <span class="text-red-600 dark:text-red-400">Rp -{{ number_format(abs($item->selisih), 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-[var(--color-admin-text)]">Rp 0</span>
                                    @endif
                                @else
                                    <span class="text-[var(--color-admin-mute)]">-</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8">
                    <x-admin.empty-state
                        title="Belum ada riwayat rekonsiliasi"
                        description="Semua setoran kantor yang telah diverifikasi akan tercatat di sini."
                    />
                </div>
            @endforelse
        </div>

        @if($riwayat->hasPages())
            <div class="border-t border-[var(--color-admin-border)] px-5 py-3">
                {{ $riwayat->links() }}
            </div>
        @endif
    </div>

    {{-- Modal: Proses Setoran --}}
    @php
        $selectedItem = $this->selectedSubmission;
    @endphp
    @if($processingId && $selectedItem)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-label="Proses setoran {{ $selectedItem->kolektor->name ?? 'Kolektor' }}">
            <div class="fixed inset-0 bg-black/45 backdrop-blur-[2px] transition-opacity cursor-pointer" wire:click="cancelProcess"></div>

            <div class="relative w-full max-w-[460px] rounded-[16px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] p-5 shadow-2xl transition-colors z-10 my-auto">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-[var(--color-admin-text)] leading-tight">
                            Proses setoran {{ $selectedItem->kolektor->name ?? 'Kolektor' }}
                        </h3>
                        <p class="mt-1 text-xs text-[var(--color-admin-mute)]">
                            Total seharusnya <span class="font-semibold text-[var(--color-admin-text)] tabular-nums">Rp {{ number_format($selectedItem->total_seharusnya, 0, ',', '.') }}</span>
                        </p>
                    </div>
                    <button type="button" wire:click="cancelProcess" class="flex size-7 shrink-0 items-center justify-center rounded-[8px] text-[var(--color-admin-mute)] hover:bg-[var(--color-surface-2)] hover:text-[var(--color-admin-text)] cursor-pointer" aria-label="Tutup dialog">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="mt-4 space-y-3.5">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Total Diterima (Fisik)</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 tabular-nums text-xs font-medium text-[var(--color-admin-mute)]">Rp</span>
                            <input type="number" wire:model.live="processTotalDiterima"
                                class="h-[38px] w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] pl-9 pr-3 text-sm text-[var(--color-admin-text)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                                placeholder="0" />
                        </div>
                        @error('processTotalDiterima') <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    @if($processTotalDiterima !== '' && $processTotalDiterima != $selectedItem->total_seharusnya)
                        <div class="rounded-[10px] border p-3 {{ $processTotalDiterima > $selectedItem->total_seharusnya ? 'bg-warning-soft text-warning-deep border-warning/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20' }}">
                            <p class="text-xs font-medium">
                                Selisih: Rp {{ number_format(abs($processTotalDiterima - $selectedItem->total_seharusnya), 0, ',', '.') }} ({{ $processTotalDiterima > $selectedItem->total_seharusnya ? 'Lebih' : 'Kurang' }})
                            </p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Keterangan Selisih (Wajib)</label>
                            <textarea wire:model.live="processKeterangan" rows="2"
                                class="w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 py-2 text-sm text-[var(--color-admin-text)] placeholder-[var(--color-admin-mute)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                                placeholder="Jelaskan penyebab selisih..."></textarea>
                            @error('processKeterangan') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div class="mt-5 flex items-center justify-end gap-2 pt-3 border-t border-[var(--color-admin-border)]">
                    <button type="button" wire:click="cancelProcess"
                        class="h-[38px] rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-4 text-xs font-medium text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)] cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="processSubmission({{ $selectedItem->id }})" wire:loading.attr="disabled"
                        wire:confirm="Yakin mengonfirmasi dan menyimpan hasil rekonsiliasi kas ini?"
                        class="h-[38px] rounded-[10px] bg-[var(--color-accent)] px-4 text-xs font-semibold text-white transition hover:opacity-90 cursor-pointer disabled:opacity-50">
                        Konfirmasi &amp; Simpan
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Tolak Pengajuan --}}
    @if($rejectingId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-label="Tolak pengajuan?">
            <div class="fixed inset-0 bg-black/45 backdrop-blur-[2px] transition-opacity cursor-pointer" wire:click="cancelReject"></div>

            <div class="relative w-full max-w-[460px] rounded-[16px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] p-5 shadow-2xl transition-colors z-10 my-auto">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-[var(--color-admin-text)] leading-tight">
                            Tolak pengajuan?
                        </h3>
                        <p class="mt-1 text-xs text-[var(--color-admin-mute)]">
                            Menolak pengajuan akan mengembalikan seluruh setoran terkait ke status belum disetor.
                        </p>
                    </div>
                    <button type="button" wire:click="cancelReject" class="flex size-7 shrink-0 items-center justify-center rounded-[8px] text-[var(--color-admin-mute)] hover:bg-[var(--color-surface-2)] hover:text-[var(--color-admin-text)] cursor-pointer" aria-label="Tutup dialog">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Alasan Penolakan (Wajib)</label>
                        <textarea wire:model="rejectAlasan" rows="3"
                            class="w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 py-2 text-sm text-[var(--color-admin-text)] placeholder-[var(--color-admin-mute)] focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                            placeholder="Jelaskan alasan penolakan..."></textarea>
                        @error('rejectAlasan') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-2 pt-3 border-t border-[var(--color-admin-border)]">
                    <button type="button" wire:click="cancelReject"
                        class="h-[38px] rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-4 text-xs font-medium text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)] cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="rejectSubmission" wire:loading.attr="disabled"
                        wire:confirm="Yakin menolak pengajuan setoran ini?"
                        class="h-[38px] rounded-[10px] bg-red-600 px-4 text-xs font-semibold text-white transition hover:bg-red-700 cursor-pointer disabled:opacity-50">
                        Tolak Pengajuan
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Input Manual Setoran Kas --}}
    @if($showManualModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-label="Input Manual Setoran Kas">
            <div class="fixed inset-0 bg-black/45 backdrop-blur-[2px] transition-opacity cursor-pointer" wire:click="closeInputManual"></div>

            <div class="relative w-full max-w-[500px] rounded-[16px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] p-5 shadow-2xl transition-colors z-10 my-auto">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-[var(--color-admin-text)] leading-tight">
                            Input Manual Setoran Kas
                        </h3>
                        <p class="mt-1 text-xs text-[var(--color-admin-mute)]">
                            Catat setoran kas kolektor yang diserahkan langsung ke kantor.
                        </p>
                    </div>
                    <button type="button" wire:click="closeInputManual" class="flex size-7 shrink-0 items-center justify-center rounded-[8px] text-[var(--color-admin-mute)] hover:bg-[var(--color-surface-2)] hover:text-[var(--color-admin-text)] cursor-pointer" aria-label="Tutup dialog">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="mt-4 space-y-3.5">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Kolektor</label>
                        <select wire:model.live="kolektorId"
                            class="h-[38px] w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-sm text-[var(--color-admin-text)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]">
                            <option value="">Pilih Kolektor</option>
                            @foreach($kolektorList as $k)
                                <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->no_hp }})</option>
                            @endforeach
                        </select>
                        @error('kolektorId') <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    @if($showForm && $kolektorId)
                        <div class="rounded-[12px] border border-[var(--color-admin-border)] bg-[var(--color-surface-2)] p-3.5 space-y-2">
                            <div class="flex justify-between text-xs">
                                <span class="text-[var(--color-admin-mute)]">Setoran Masuk</span>
                                <span class="tabular-nums font-medium text-[var(--color-admin-text)]">Rp {{ number_format($totalSetoranMasuk, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-[var(--color-admin-mute)]">Penarikan Tunai</span>
                                <span class="tabular-nums font-medium text-[var(--color-admin-text)]">Rp {{ number_format($totalPenarikanTunai, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between border-t border-[var(--color-admin-border)] pt-2 font-semibold text-sm">
                                <span class="text-[var(--color-admin-text)]">Total Seharusnya</span>
                                <span class="tabular-nums text-[var(--color-accent)]">Rp {{ number_format($totalSeharusnya, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        @if($detailTransaksi->count() > 0)
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Total Diterima (Fisik)</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 tabular-nums text-xs font-medium text-[var(--color-admin-mute)]">Rp</span>
                                    <input type="number" wire:model="totalDiterima"
                                        class="h-[38px] w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] pl-9 pr-3 text-sm text-[var(--color-admin-text)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                                        placeholder="0" />
                                </div>
                                @error('totalDiterima') <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>

                            @if($totalDiterima && $totalDiterima != $totalSeharusnya)
                                <div class="rounded-[10px] border p-3 {{ $totalDiterima > $totalSeharusnya ? 'bg-warning-soft text-warning-deep border-warning/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20' }}">
                                    <p class="text-xs font-medium">
                                        Selisih: Rp {{ number_format(abs($totalDiterima - $totalSeharusnya), 0, ',', '.') }} ({{ $totalDiterima > $totalSeharusnya ? 'Lebih' : 'Kurang' }})
                                    </p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Keterangan Selisih (Wajib)</label>
                                    <textarea wire:model="keterangan" rows="2"
                                        class="w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 py-2 text-sm text-[var(--color-admin-text)] placeholder-[var(--color-admin-mute)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                                        placeholder="Jelaskan penyebab selisih..."></textarea>
                                    @error('keterangan') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                            @endif
                        @else
                            <p class="text-xs text-[var(--color-admin-mute)] py-2 text-center">Tidak ada transaksi yang perlu disetor untuk kolektor ini.</p>
                        @endif
                    @endif
                </div>

                <div class="mt-5 flex items-center justify-end gap-2 pt-3 border-t border-[var(--color-admin-border)]">
                    <button type="button" wire:click="closeInputManual"
                        class="h-[38px] rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-4 text-xs font-medium text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)] cursor-pointer">
                        Batal
                    </button>
                    @if($showForm && $kolektorId && $detailTransaksi->count() > 0)
                        <button type="button" wire:click="submit" wire:loading.attr="disabled"
                            class="h-[38px] rounded-[10px] bg-[var(--color-accent)] px-4 text-xs font-semibold text-white transition hover:opacity-90 cursor-pointer disabled:opacity-50">
                            Simpan Rekonsiliasi
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Drawer Slide-over: Detail Rekonsiliasi Kas --}}
    @if($detailId && $detailSetoran)
        <div class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true" aria-label="Detail Rekonsiliasi Kas">
            <div class="fixed inset-0 bg-black/45 backdrop-blur-[2px] transition-opacity cursor-pointer" wire:click="tutupDetail"></div>

            <div class="relative z-10 flex h-full w-full max-w-[480px] flex-col border-l border-[var(--color-admin-border)] bg-[var(--color-surface)] shadow-2xl transition-all">
                {{-- Header Drawer --}}
                <div class="flex items-center justify-between border-b border-[var(--color-admin-border)] px-5 py-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-semibold text-[var(--color-admin-text)]">Detail Rekonsiliasi Kas</h3>
                            @php
                                $mapStatus = [
                                    'pending' => 'Menunggu',
                                    'cocok' => 'Cocok',
                                    'lebih' => 'Lebih',
                                    'kurang' => 'Kurang',
                                    'dibatalkan' => 'Ditolak',
                                ];
                            @endphp
                            <x-admin.status-chip :status="$mapStatus[$detailSetoran->status] ?? ucfirst($detailSetoran->status)" />
                        </div>
                        <p class="text-xs text-[var(--color-admin-mute)] mt-0.5">
                            Setoran Kantor #{{ $detailSetoran->id }} &middot; {{ $detailSetoran->tanggal_setor->translatedFormat('d F Y') }}
                        </p>
                    </div>
                    <button type="button" wire:click="tutupDetail"
                        class="flex size-8 shrink-0 items-center justify-center rounded-[8px] text-[var(--color-admin-mute)] hover:bg-[var(--color-surface-2)] hover:text-[var(--color-admin-text)] cursor-pointer"
                        aria-label="Tutup panel detail">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Content Drawer --}}
                <div class="flex-1 overflow-y-auto p-5 space-y-5">
                    {{-- Kolektor & Penerima --}}
                    <div class="rounded-[12px] border border-[var(--color-admin-border)] bg-[var(--color-surface-2)] p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-[var(--color-admin-mute)]">Kolektor Penyetor</span>
                            <span class="text-xs font-semibold text-[var(--color-admin-text)]">{{ $detailSetoran->kolektor->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-[var(--color-admin-border)] pt-2.5">
                            <span class="text-xs text-[var(--color-admin-mute)]">Kontak Kolektor</span>
                            <span class="text-xs text-[var(--color-admin-text)] font-mono">{{ $detailSetoran->kolektor->no_hp ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-[var(--color-admin-border)] pt-2.5">
                            <span class="text-xs text-[var(--color-admin-mute)]">Diterima / Diverifikasi Oleh</span>
                            <span class="text-xs font-medium text-[var(--color-admin-text)]">{{ $detailSetoran->diterimaOleh->name ?? 'Admin Kantor' }}</span>
                        </div>
                    </div>

                    {{-- Ringkasan Finansial --}}
                    <div class="rounded-[12px] border border-[var(--color-admin-border)] p-4 space-y-3">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Ringkasan Kas</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-xs text-[var(--color-admin-mute)]">Total Seharusnya</span>
                                <span class="font-semibold tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($detailSetoran->total_seharusnya, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-[var(--color-admin-mute)]">Total Diterima Fisik</span>
                                <span class="font-semibold tabular-nums text-[var(--color-admin-text)]">
                                    @if($detailSetoran->total_diterima !== null)
                                        Rp {{ number_format($detailSetoran->total_diterima, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between border-t border-[var(--color-admin-border)] pt-2.5">
                                <span class="text-xs font-medium text-[var(--color-admin-mute)]">Selisih Kas</span>
                                <span class="font-semibold tabular-nums">
                                    @if($detailSetoran->selisih !== null)
                                        @if($detailSetoran->selisih > 0)
                                            <span class="text-warning-deep">+Rp {{ number_format($detailSetoran->selisih, 0, ',', '.') }} (Lebih)</span>
                                        @elseif($detailSetoran->selisih < 0)
                                            <span class="text-red-600 dark:text-red-400">Rp -{{ number_format(abs($detailSetoran->selisih), 0, ',', '.') }} (Kurang)</span>
                                        @else
                                            <span class="text-[var(--color-accent)] font-semibold">Rp 0 (Cocok)</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            @if($detailSetoran->keterangan_selisih)
                                <div class="rounded-[8px] bg-[var(--color-surface-2)] p-2.5 text-xs text-[var(--color-admin-text)] mt-2">
                                    <span class="font-medium text-[var(--color-admin-mute)] block mb-0.5">Catatan / Keterangan:</span>
                                    {{ $detailSetoran->keterangan_selisih }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Daftar Transaksi Setoran Nasabah Terkait --}}
                    @if($detailSetoran->transaksiSetorans && $detailSetoran->transaksiSetorans->count() > 0)
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Rincian Setoran Nasabah</h4>
                                <span class="text-xs text-[var(--color-admin-mute)]">{{ $detailSetoran->transaksiSetorans->count() }} transaksi</span>
                            </div>
                            <div class="rounded-[12px] border border-[var(--color-admin-border)] divide-y divide-[var(--color-admin-border)] overflow-hidden">
                                @foreach($detailSetoran->transaksiSetorans as $trx)
                                    <div class="p-3 text-xs flex items-center justify-between hover:bg-[var(--color-surface-2)] transition">
                                        <div>
                                            <p class="font-semibold text-[var(--color-admin-text)]">{{ $trx->nasabah->name ?? 'Nasabah' }}</p>
                                            <p class="text-[11px] text-[var(--color-admin-mute)]">{{ $trx->produk->nama ?? 'Tabungan' }} &middot; {{ $trx->tanggal_input_sistem ? $trx->tanggal_input_sistem->format('H:i') : '-' }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-semibold tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</p>
                                            <span class="text-[10px] uppercase text-[var(--color-admin-mute)]">{{ $trx->sumber_input }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Footer Drawer --}}
                <div class="border-t border-[var(--color-admin-border)] p-4 flex justify-end">
                    <button type="button" wire:click="tutupDetail"
                        class="h-[38px] w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-4 text-xs font-medium text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)] cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
