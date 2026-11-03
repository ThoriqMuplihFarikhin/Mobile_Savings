<div class="space-y-6">
    {{-- Header Halaman --}}
    <x-admin.page-header
        breadcrumb="Keuangan"
        title="Rekonsiliasi Kas Kolektor"
        subtitle="Proses pengajuan setoran dari kolektor dan cocokkan kas fisik."
    >
        <x-slot:actions>
            <button type="button" wire:click="openInputManual"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-white dark:bg-zinc-800 px-4 text-sm font-semibold text-zinc-800 dark:text-zinc-100 shadow-sm hover:shadow-md hover:bg-zinc-50 dark:hover:bg-zinc-700/80 transition-all cursor-pointer active:scale-[0.98]">
                <div class="flex size-5 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
                <span>Input Manual Setoran Kas</span>
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Feedback Alerts --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-2xl bg-primary-soft px-5 py-3.5 text-sm text-on-primary-soft shadow-sm">
            <div class="flex size-6 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            </div>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 rounded-2xl bg-danger-soft px-5 py-3.5 text-sm text-danger shadow-sm">
            <div class="flex size-6 shrink-0 items-center justify-center rounded-full bg-danger text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </div>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Card: Menunggu Pemrosesan (Clean & Borderless) --}}
    <div class="rounded-2xl bg-white dark:bg-surface p-6 shadow-sm shadow-zinc-950/5 transition-all">
        <div class="flex items-center justify-between pb-4">
            <div class="flex items-center gap-2.5">
                <span class="flex size-2 rounded-full bg-warning-deep"></span>
                <h2 class="text-base font-bold text-zinc-900 dark:text-white tracking-tight">Menunggu pemrosesan</h2>
            </div>
            @if($pendingSubmissions->count() > 0)
                <span class="rounded-full bg-warning-soft px-2.5 py-0.5 text-xs font-semibold text-warning-deep">
                    {{ $pendingSubmissions->count() }} antrean
                </span>
            @endif
        </div>

        @if($pendingSubmissions->count() > 0)
            <div class="space-y-3 mt-1">
                @foreach($pendingSubmissions as $item)
                    <div data-test="baris-pengajuan" class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-xl bg-zinc-50/80 dark:bg-zinc-800/50 hover:bg-zinc-100/80 dark:hover:bg-zinc-800/80 transition-all duration-150">
                        <div class="min-w-[200px] flex-1 flex items-center gap-3">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary font-bold text-xs uppercase">
                                {{ Str::substr($item->kolektor->name ?? 'K', 0, 2) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-zinc-900 dark:text-white">{{ $item->kolektor->name ?? '-' }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                    {{ $item->tanggal_setor->isToday() ? 'Hari ini' : ($item->tanggal_setor->isYesterday() ? 'Kemarin' : $item->tanggal_setor->translatedFormat('d M')) }}
                                    @if($item->keterangan_selisih)
                                        &middot; {{ $item->keterangan_selisih }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="text-right sm:px-4">
                            <span class="block text-[11px] font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Total seharusnya</span>
                            <b class="text-base font-bold tabular-nums text-zinc-900 dark:text-white">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</b>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="startProcess({{ $item->id }})"
                                class="inline-flex h-9 items-center justify-center rounded-xl bg-primary hover:bg-primary/90 px-4 text-xs font-semibold text-primary-foreground shadow-sm transition-all cursor-pointer active:scale-[0.97]">
                                Proses
                            </button>
                            <button type="button" wire:click="startReject({{ $item->id }})"
                                class="inline-flex h-9 items-center justify-center rounded-xl bg-danger-soft hover:bg-danger/20 px-3.5 text-xs font-semibold text-danger transition-all cursor-pointer active:scale-[0.97]">
                                Tolak
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-8 text-center">
                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-zinc-50 dark:bg-zinc-800/60 text-zinc-400 mb-2.5">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <b class="block text-sm font-semibold text-zinc-800 dark:text-zinc-200">Tidak ada pengajuan setoran</b>
                <span class="mt-0.5 block text-xs text-zinc-400 dark:text-zinc-500">Semua pengajuan sudah diproses.</span>
            </div>
        @endif
    </div>

    {{-- Card: Riwayat Rekonsiliasi (Clean & Borderless) --}}
    <div class="rounded-2xl bg-white dark:bg-surface p-6 shadow-sm shadow-zinc-950/5 transition-all">
        <div class="flex items-center justify-between pb-4">
            <div class="flex items-center gap-2.5">
                <span class="flex size-2 rounded-full bg-primary"></span>
                <h2 class="text-base font-bold text-zinc-900 dark:text-white tracking-tight">Riwayat Rekonsiliasi</h2>
            </div>
            <span class="text-xs text-zinc-400 dark:text-zinc-500 font-medium hidden sm:inline-block">Klik baris untuk rincian</span>
        </div>

        {{-- Tabel Desktop Tanpa Garis Kaku --}}
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left text-sm">
                <thead>
                    <tr class="bg-zinc-50/80 dark:bg-zinc-800/40 text-zinc-400 dark:text-zinc-400">
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider rounded-l-xl">Tanggal</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider">Kolektor</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Seharusnya</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Diterima</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider">Selisih</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider rounded-r-xl">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100/80 dark:divide-zinc-800/60">
                    @forelse($riwayat as $item)
                        <tr wire:key="rekon-row-{{ $item->id }}" wire:click="bukaDetail({{ $item->id }})"
                            class="cursor-pointer transition-colors duration-150 hover:bg-zinc-50/70 dark:hover:bg-zinc-800/50 group"
                            title="Klik untuk melihat rincian rekonsiliasi">
                            <td class="px-4 py-4 text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                                {{ $item->tanggal_setor->translatedFormat('d M') }}
                            </td>
                            <td class="px-4 py-4 font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-primary transition-colors">
                                {{ $item->kolektor->name ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-right tabular-nums text-zinc-600 dark:text-zinc-400 font-medium">
                                Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-right tabular-nums text-zinc-900 dark:text-white font-semibold">
                                @if($item->total_diterima !== null)
                                    Rp {{ number_format($item->total_diterima, 0, ',', '.') }}
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right tabular-nums">
                                @if($item->selisih !== null)
                                    @if($item->selisih > 0)
                                        <span class="font-semibold text-warning-deep">+Rp {{ number_format($item->selisih, 0, ',', '.') }}</span>
                                    @elseif($item->selisih < 0)
                                        <span class="font-semibold text-danger">Rp -{{ number_format(abs($item->selisih), 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-zinc-400 font-medium">Rp 0</span>
                                    @endif
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
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
                            <td colspan="6" class="py-10 text-center">
                                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-zinc-50 dark:bg-zinc-800/60 text-zinc-400 mb-2.5">
                                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                                <b class="block text-sm font-semibold text-zinc-800 dark:text-zinc-200">Belum ada riwayat rekonsiliasi</b>
                                <span class="mt-0.5 block text-xs text-zinc-400 dark:text-zinc-500">Semua setoran kantor yang telah diverifikasi akan tercatat di sini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Fallback Kartu Mobile (Sleek & Clean) --}}
        <div data-test="kartu-riwayat" class="space-y-3 md:hidden">
            @forelse($riwayat as $item)
                <div wire:key="rekon-card-{{ $item->id }}" wire:click="bukaDetail({{ $item->id }})"
                    class="p-4 rounded-xl bg-zinc-50/80 dark:bg-zinc-800/50 space-y-3 cursor-pointer active:scale-[0.99] transition-all">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="font-bold text-zinc-900 dark:text-white">{{ $item->kolektor->name ?? '-' }}</p>
                            <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">{{ $item->tanggal_setor->translatedFormat('d M Y') }}</p>
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
                    <div class="grid grid-cols-3 gap-2 text-xs pt-2 border-t border-zinc-200/50 dark:border-zinc-700/50">
                        <div>
                            <span class="text-zinc-400 block text-[11px]">Seharusnya:</span>
                            <div class="font-medium tabular-nums text-zinc-700 dark:text-zinc-300 mt-0.5">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-zinc-400 block text-[11px]">Diterima:</span>
                            <div class="font-bold tabular-nums text-zinc-900 dark:text-white mt-0.5">
                                @if($item->total_diterima !== null)
                                    Rp {{ number_format($item->total_diterima, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div>
                            <span class="text-zinc-400 block text-[11px]">Selisih:</span>
                            <div class="font-bold tabular-nums mt-0.5">
                                @if($item->selisih !== null)
                                    @if($item->selisih > 0)
                                        <span class="text-warning-deep">+Rp {{ number_format($item->selisih, 0, ',', '.') }}</span>
                                    @elseif($item->selisih < 0)
                                        <span class="text-danger">Rp -{{ number_format(abs($item->selisih), 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-zinc-400">Rp 0</span>
                                    @endif
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center">
                    <b class="block text-sm font-semibold text-zinc-800 dark:text-zinc-200">Belum ada riwayat rekonsiliasi</b>
                    <span class="mt-0.5 block text-xs text-zinc-400 dark:text-zinc-500">Semua setoran kantor yang telah diverifikasi akan tercatat di sini.</span>
                </div>
            @endforelse
        </div>

        @if($riwayat->hasPages())
            <div class="pt-5 mt-4 border-t border-zinc-100 dark:border-zinc-800/80">
                {{ $riwayat->links() }}
            </div>
        @endif
    </div>

    {{-- Modal: Proses Setoran (Floating Elevation & Modern) --}}
    @php
        $selectedItem = $this->selectedSubmission;
    @endphp
    @if($processingId && $selectedItem)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-label="Proses setoran {{ $selectedItem->kolektor->name ?? 'Kolektor' }}">
            <div class="fixed inset-0 bg-black/45 transition-opacity cursor-pointer" wire:click="cancelProcess"></div>

            <div class="relative w-full max-w-[460px] rounded-3xl bg-white dark:bg-surface p-6 shadow-2xl shadow-zinc-950/20 transition-all z-10 my-auto">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white leading-tight">
                            Proses setoran {{ $selectedItem->kolektor->name ?? 'Kolektor' }}
                        </h3>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Total seharusnya <span class="font-bold text-zinc-900 dark:text-white tabular-nums">Rp {{ number_format($selectedItem->total_seharusnya, 0, ',', '.') }}</span>
                        </p>
                    </div>
                    <button type="button" wire:click="cancelProcess" class="flex size-8 shrink-0 items-center justify-center rounded-xl text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-700 dark:hover:text-zinc-200 cursor-pointer" aria-label="Tutup dialog">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="mt-5 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Total Diterima (Fisik)</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 tabular-nums text-xs font-semibold text-zinc-400">Rp</span>
                            <input type="number" wire:model.live="processTotalDiterima"
                                class="h-11 w-full rounded-xl bg-zinc-50 dark:bg-zinc-800/80 pl-10 pr-4 text-sm font-semibold text-zinc-900 dark:text-white ring-1 ring-zinc-200/80 dark:ring-zinc-700/80 focus:ring-2 focus:ring-primary outline-none transition"
                                placeholder="0" />
                        </div>
                        @error('processTotalDiterima') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    @if($processTotalDiterima !== '' && $processTotalDiterima != $selectedItem->total_seharusnya)
                        <div class="rounded-xl p-3.5 {{ $processTotalDiterima > $selectedItem->total_seharusnya ? 'bg-warning-soft text-warning-deep' : 'bg-danger-soft text-danger' }}">
                            <p class="text-xs font-semibold">
                                Selisih: Rp {{ number_format(abs($processTotalDiterima - $selectedItem->total_seharusnya), 0, ',', '.') }} ({{ $processTotalDiterima > $selectedItem->total_seharusnya ? 'Lebih' : 'Kurang' }})
                            </p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Keterangan Selisih (Wajib)</label>
                            <textarea wire:model.live="processKeterangan" rows="2"
                                class="w-full rounded-xl bg-zinc-50 dark:bg-zinc-800/80 px-4 py-3 text-sm text-zinc-900 dark:text-white placeholder-zinc-400 ring-1 ring-zinc-200/80 dark:ring-zinc-700/80 focus:ring-2 focus:ring-primary outline-none transition"
                                placeholder="Jelaskan penyebab selisih..."></textarea>
                            @error('processKeterangan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex items-center justify-end gap-2.5 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" wire:click="cancelProcess"
                        class="h-10 rounded-xl px-4 text-xs font-semibold text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="processSubmission({{ $selectedItem->id }})" wire:loading.attr="disabled"
                        wire:confirm="Yakin mengonfirmasi dan menyimpan hasil rekonsiliasi kas ini?"
                        class="h-10 rounded-xl bg-primary hover:bg-primary/90 px-5 text-xs font-semibold text-primary-foreground shadow-sm transition cursor-pointer disabled:opacity-50">
                        Konfirmasi &amp; Simpan
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Tolak Pengajuan --}}
    @if($rejectingId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-label="Tolak pengajuan?">
            <div class="fixed inset-0 bg-black/45 transition-opacity cursor-pointer" wire:click="cancelReject"></div>

            <div class="relative w-full max-w-[460px] rounded-3xl bg-white dark:bg-surface p-6 shadow-2xl shadow-zinc-950/20 transition-all z-10 my-auto">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white leading-tight">
                            Tolak pengajuan?
                        </h3>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Menolak pengajuan akan mengembalikan seluruh setoran terkait ke status belum disetor.
                        </p>
                    </div>
                    <button type="button" wire:click="cancelReject" class="flex size-8 shrink-0 items-center justify-center rounded-xl text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-700 dark:hover:text-zinc-200 cursor-pointer" aria-label="Tutup dialog">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="mt-5 space-y-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Alasan Penolakan (Wajib)</label>
                        <textarea wire:model="rejectAlasan" rows="3"
                            class="w-full rounded-xl bg-zinc-50 dark:bg-zinc-800/80 px-4 py-3 text-sm text-zinc-900 dark:text-white placeholder-zinc-400 ring-1 ring-zinc-200/80 dark:ring-zinc-700/80 focus:ring-2 focus:ring-danger outline-none transition"
                            placeholder="Jelaskan alasan penolakan..."></textarea>
                        @error('rejectAlasan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-2.5 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" wire:click="cancelReject"
                        class="h-10 rounded-xl px-4 text-xs font-semibold text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="rejectSubmission" wire:loading.attr="disabled"
                        wire:confirm="Yakin menolak pengajuan setoran ini?"
                        class="h-10 rounded-xl bg-danger hover:bg-danger/90 px-5 text-xs font-semibold text-white shadow-sm transition cursor-pointer disabled:opacity-50">
                        Tolak Pengajuan
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Input Manual Setoran Kas --}}
    @if($showManualModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-label="Input Manual Setoran Kas">
            <div class="fixed inset-0 bg-black/45 transition-opacity cursor-pointer" wire:click="closeInputManual"></div>

            <div class="relative w-full max-w-[500px] rounded-3xl bg-white dark:bg-surface p-6 shadow-2xl shadow-zinc-950/20 transition-all z-10 my-auto">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white leading-tight">
                            Input Manual Setoran Kas
                        </h3>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                            Catat setoran kas kolektor yang diserahkan langsung ke kantor.
                        </p>
                    </div>
                    <button type="button" wire:click="closeInputManual" class="flex size-8 shrink-0 items-center justify-center rounded-xl text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-700 dark:hover:text-zinc-200 cursor-pointer" aria-label="Tutup dialog">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="mt-5 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Kolektor</label>
                        <select wire:model.live="kolektorId"
                            class="h-11 w-full rounded-xl bg-zinc-50 dark:bg-zinc-800/80 px-4 text-sm font-medium text-zinc-900 dark:text-white ring-1 ring-zinc-200/80 dark:ring-zinc-700/80 focus:ring-2 focus:ring-primary outline-none transition">
                            <option value="">Pilih Kolektor</option>
                            @foreach($kolektorList as $k)
                                <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->no_hp }})</option>
                            @endforeach
                        </select>
                        @error('kolektorId') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    @if($showForm && $kolektorId)
                        <div class="rounded-2xl bg-zinc-50/80 dark:bg-zinc-800/60 p-4 space-y-2.5">
                            <div class="flex justify-between text-xs">
                                <span class="text-zinc-500 dark:text-zinc-400">Setoran Masuk</span>
                                <span class="tabular-nums font-semibold text-zinc-900 dark:text-white">Rp {{ number_format($totalSetoranMasuk, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-zinc-500 dark:text-zinc-400">Penarikan Tunai</span>
                                <span class="tabular-nums font-semibold text-zinc-900 dark:text-white">Rp {{ number_format($totalPenarikanTunai, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between border-t border-zinc-200/60 dark:border-zinc-700/60 pt-2 font-bold text-sm">
                                <span class="text-zinc-800 dark:text-zinc-200">Total Seharusnya</span>
                                <span class="tabular-nums text-primary font-bold">Rp {{ number_format($totalSeharusnya, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        @if(($rincianLangsung['belum']['jumlah'] ?? 0) + ($rincianLangsung['sesuai']['jumlah'] ?? 0) + ($rincianLangsung['bermasalah']['jumlah'] ?? 0) > 0)
                            <div class="rounded-2xl bg-warning-soft p-4 space-y-1.5 text-xs text-warning-deep border border-warning-deep/20">
                                <p class="font-bold">Rincian penarikan offline langsung kolektor ini</p>
                                <p class="font-mono text-xs">
                                    belum ditinjau {{ $rincianLangsung['belum']['jumlah'] }} (Rp {{ number_format($rincianLangsung['belum']['nominal'], 0, ',', '.') }})
                                    &middot; sesuai {{ $rincianLangsung['sesuai']['jumlah'] }} (Rp {{ number_format($rincianLangsung['sesuai']['nominal'], 0, ',', '.') }})
                                    &middot; bermasalah {{ $rincianLangsung['bermasalah']['jumlah'] }} (Rp {{ number_format($rincianLangsung['bermasalah']['nominal'], 0, ',', '.') }})
                                </p>
                            </div>
                        @endif

                        @if($detailTransaksi->count() > 0)
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Total Diterima (Fisik)</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 tabular-nums text-xs font-semibold text-zinc-400">Rp</span>
                                    <input type="number" wire:model="totalDiterima"
                                        class="h-11 w-full rounded-xl bg-zinc-50 dark:bg-zinc-800/80 pl-10 pr-4 text-sm font-semibold text-zinc-900 dark:text-white ring-1 ring-zinc-200/80 dark:ring-zinc-700/80 focus:ring-2 focus:ring-primary outline-none transition"
                                        placeholder="0" />
                                </div>
                                @error('totalDiterima') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>

                            @if($totalDiterima && $totalDiterima != $totalSeharusnya)
                                <div class="rounded-xl p-3.5 {{ $totalDiterima > $totalSeharusnya ? 'bg-warning-soft text-warning-deep' : 'bg-danger-soft text-danger' }}">
                                    <p class="text-xs font-semibold">
                                        Selisih: Rp {{ number_format(abs($totalDiterima - $totalSeharusnya), 0, ',', '.') }} ({{ $totalDiterima > $totalSeharusnya ? 'Lebih' : 'Kurang' }})
                                    </p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Keterangan Selisih (Wajib)</label>
                                    <textarea wire:model="keterangan" rows="2"
                                        class="w-full rounded-xl bg-zinc-50 dark:bg-zinc-800/80 px-4 py-3 text-sm text-zinc-900 dark:text-white placeholder-zinc-400 ring-1 ring-zinc-200/80 dark:ring-zinc-700/80 focus:ring-2 focus:ring-primary outline-none transition"
                                        placeholder="Jelaskan penyebab selisih..."></textarea>
                                    @error('keterangan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                </div>
                            @endif
                        @else
                            <p class="text-xs text-zinc-400 py-2 text-center">Tidak ada transaksi yang perlu disetor untuk kolektor ini.</p>
                        @endif
                    @endif
                </div>

                <div class="mt-6 flex items-center justify-end gap-2.5 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <button type="button" wire:click="closeInputManual"
                        class="h-10 rounded-xl px-4 text-xs font-semibold text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer">
                        Batal
                    </button>
                    @if($showForm && $kolektorId && $detailTransaksi->count() > 0)
                        <button type="button" wire:click="submit" wire:loading.attr="disabled"
                            class="h-10 rounded-xl bg-primary hover:bg-primary/90 px-5 text-xs font-semibold text-primary-foreground shadow-sm transition cursor-pointer disabled:opacity-50">
                            Simpan Rekonsiliasi
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Drawer Slide-over: Detail Rekonsiliasi Kas (Clean & Borderless) --}}
    @if($detailId && $detailSetoran)
        <div class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true" aria-label="Detail Rekonsiliasi Kas">
            <div class="fixed inset-0 bg-black/45 transition-opacity cursor-pointer" wire:click="tutupDetail"></div>

            <div class="relative z-10 flex h-full w-full max-w-[480px] flex-col bg-white dark:bg-surface shadow-2xl shadow-zinc-950/20 transition-all">
                {{-- Header Drawer --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-zinc-100 dark:border-zinc-800/80">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h3 class="text-base font-bold text-zinc-900 dark:text-white">Detail Rekonsiliasi Kas</h3>
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
                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">
                            Setoran Kantor #{{ $detailSetoran->id }} &middot; {{ $detailSetoran->tanggal_setor->translatedFormat('d F Y') }}
                        </p>
                    </div>
                    <button type="button" wire:click="tutupDetail"
                        class="flex size-8 shrink-0 items-center justify-center rounded-xl text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-700 dark:hover:text-zinc-200 cursor-pointer"
                        aria-label="Tutup panel detail">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Content Drawer --}}
                <div class="flex-1 overflow-y-auto p-6 space-y-5">
                    {{-- Kolektor & Penerima --}}
                    <div class="rounded-2xl bg-zinc-50/80 dark:bg-zinc-800/60 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-zinc-400">Kolektor Penyetor</span>
                            <span class="text-xs font-bold text-zinc-900 dark:text-white">{{ $detailSetoran->kolektor->name ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-zinc-200/50 dark:border-zinc-700/50 pt-2.5">
                            <span class="text-xs text-zinc-400">Kontak Kolektor</span>
                            <span class="text-xs font-mono text-zinc-700 dark:text-zinc-300">{{ $detailSetoran->kolektor->no_hp ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-zinc-200/50 dark:border-zinc-700/50 pt-2.5">
                            <span class="text-xs text-zinc-400">Diterima / Diverifikasi Oleh</span>
                            <span class="text-xs font-semibold text-zinc-900 dark:text-white">{{ $detailSetoran->diterimaOleh->name ?? 'Admin Kantor' }}</span>
                        </div>
                    </div>

                    {{-- Ringkasan Finansial --}}
                    <div class="rounded-2xl bg-zinc-50/80 dark:bg-zinc-800/60 p-4 space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400">Ringkasan Kas</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">Total Seharusnya</span>
                                <span class="font-bold tabular-nums text-zinc-900 dark:text-white">Rp {{ number_format($detailSetoran->total_seharusnya, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">Total Diterima Fisik</span>
                                <span class="font-bold tabular-nums text-zinc-900 dark:text-white">
                                    @if($detailSetoran->total_diterima !== null)
                                        Rp {{ number_format($detailSetoran->total_diterima, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between border-t border-zinc-200/50 dark:border-zinc-700/50 pt-2.5">
                                <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Selisih Kas</span>
                                <span class="font-bold tabular-nums">
                                    @if($detailSetoran->selisih !== null)
                                        @if($detailSetoran->selisih > 0)
                                            <span class="text-warning-deep">+Rp {{ number_format($detailSetoran->selisih, 0, ',', '.') }} (Lebih)</span>
                                        @elseif($detailSetoran->selisih < 0)
                                            <span class="text-danger">Rp -{{ number_format(abs($detailSetoran->selisih), 0, ',', '.') }} (Kurang)</span>
                                        @else
                                            <span class="text-primary font-semibold">Rp 0 (Cocok)</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            @if($detailSetoran->keterangan_selisih)
                                <div class="rounded-xl bg-white dark:bg-surface p-3 text-xs text-zinc-800 dark:text-zinc-200 mt-2">
                                    <span class="font-semibold text-zinc-400 block mb-0.5">Catatan / Keterangan:</span>
                                    {{ $detailSetoran->keterangan_selisih }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Daftar Transaksi Setoran Nasabah Terkait --}}
                    @if($detailSetoran->transaksiSetorans && $detailSetoran->transaksiSetorans->count() > 0)
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400">Rincian Setoran Nasabah</h4>
                                <span class="text-xs font-medium text-zinc-400">{{ $detailSetoran->transaksiSetorans->count() }} transaksi</span>
                            </div>
                            <div class="rounded-2xl bg-zinc-50/80 dark:bg-zinc-800/60 divide-y divide-zinc-200/50 dark:divide-zinc-700/50 overflow-hidden">
                                @foreach($detailSetoran->transaksiSetorans as $trx)
                                    <div class="p-3.5 text-xs flex items-center justify-between hover:bg-zinc-100/60 dark:hover:bg-zinc-800/80 transition">
                                        <div>
                                            <p class="font-bold text-zinc-900 dark:text-white">{{ $trx->nasabah->name ?? 'Nasabah' }}</p>
                                            <p class="text-[11px] text-zinc-400 mt-0.5">{{ $trx->produk->nama ?? 'Tabungan' }} &middot; {{ $trx->tanggal_input_sistem ? $trx->tanggal_input_sistem->format('H:i') : '-' }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold tabular-nums text-zinc-900 dark:text-white">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</p>
                                            <span class="text-[10px] uppercase font-semibold text-zinc-400">{{ $trx->sumber_input }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Footer Drawer --}}
                <div class="border-t border-zinc-100 dark:border-zinc-800/80 p-5 flex justify-end">
                    <button type="button" wire:click="tutupDetail"
                        class="h-10 w-full rounded-xl bg-zinc-100 dark:bg-zinc-800 text-xs font-semibold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
