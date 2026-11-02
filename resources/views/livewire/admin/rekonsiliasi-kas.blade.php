<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Keuangan"
        title="Rekonsiliasi Kas Kolektor"
        subtitle="Proses pengajuan setoran dari kolektor dan cocokkan kas fisik."
    >
        <x-slot:actions>
            <button type="button" wire:click="bukaManualModal"
                class="inline-flex min-h-10 items-center gap-1.5 rounded-xl border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-4 py-2 text-xs font-semibold text-[var(--color-admin-text)] shadow-xs transition hover:bg-[var(--color-surface-2)]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[var(--color-admin-mute)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>Input Manual Setoran Kas</span>
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-xl border border-primary/20 bg-primary-soft px-4 py-3 text-sm text-on-primary-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-xl border border-danger/20 bg-danger-soft px-4 py-3 text-sm text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Card: Menunggu Pemrosesan --}}
    <div class="rounded-2xl border border-[var(--color-admin-border)] bg-[var(--color-surface)] shadow-xs overflow-hidden">
        <div class="border-b border-[var(--color-admin-border)] px-5 py-4">
            <h2 class="text-sm md:text-base font-semibold text-[var(--color-admin-text)]">Menunggu pemrosesan</h2>
        </div>

        @if($pendingSubmissions->count() > 0)
            <div class="divide-y divide-[var(--color-admin-border)]">
                @foreach($pendingSubmissions as $item)
                    <div class="p-5 transition hover:bg-[var(--color-surface-2)]/30">
                        <div data-test="baris-pengajuan" class="flex flex-wrap items-center justify-between gap-4">
                            {{-- Info Kolektor --}}
                            <div class="min-w-[200px] flex-1">
                                <h3 class="font-bold text-sm md:text-base text-[var(--color-admin-text)]">
                                    {{ $item->kolektor->name ?? '-' }}
                                </h3>
                                <p class="text-xs text-[var(--color-admin-mute)] mt-0.5">
                                    {{ $item->tanggal_setor->isToday() ? 'Hari ini' : ($item->tanggal_setor->isYesterday() ? 'Kemarin' : $item->tanggal_setor->translatedFormat('d M Y')) }}
                                    @if($item->keterangan_selisih)
                                        &middot; {{ $item->keterangan_selisih }}
                                    @endif
                                </p>
                            </div>

                            {{-- Total Seharusnya --}}
                            <div class="text-right">
                                <span class="text-xs text-[var(--color-admin-mute)] block">Total seharusnya</span>
                                <span class="tabular-nums font-bold text-sm md:text-base text-[var(--color-admin-text)]">
                                    Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}
                                </span>
                            </div>

                            {{-- Aksi --}}
                            <div class="flex items-center gap-2">
                                @if($processingId === $item->id)
                                    <button type="button" wire:click="cancelProcess" class="inline-flex min-h-9 items-center rounded-lg border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-xs font-semibold text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)]">
                                        Batal
                                    </button>
                                @elseif($rejectingId === $item->id)
                                    <button type="button" wire:click="cancelReject" class="inline-flex min-h-9 items-center rounded-lg border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-xs font-semibold text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)]">
                                        Batal
                                    </button>
                                @else
                                    <button type="button" wire:click="startProcess({{ $item->id }})" class="inline-flex min-h-9 items-center justify-center rounded-lg bg-emerald-600 px-4 text-xs font-semibold text-white shadow-xs transition hover:bg-emerald-700">
                                        Proses
                                    </button>
                                    <button type="button" wire:click="startReject({{ $item->id }})" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-red-500/20 bg-red-500/10 px-4 text-xs font-semibold text-red-600 transition hover:bg-red-500/20">
                                        Tolak
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Form Pemrosesan Setoran Inline --}}
                        @if($processingId === $item->id)
                            <div class="mt-4 rounded-xl border border-[var(--color-admin-border)] bg-[var(--color-surface-2)] p-4 space-y-3">
                                <div class="flex items-center justify-between text-xs border-b border-[var(--color-admin-border)] pb-2.5">
                                    <span class="text-[var(--color-admin-mute)]">Total Seharusnya</span>
                                    <span class="tabular-nums text-sm font-semibold text-[var(--color-admin-text)]">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</span>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Total Diterima (Fisik)</label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 tabular-nums text-xs font-medium text-[var(--color-admin-mute)]">Rp</span>
                                        <input type="number" wire:model.live="processTotalDiterima"
                                            class="h-10 w-full rounded-lg border border-[var(--color-admin-border)] bg-[var(--color-surface)] pl-9 pr-3 text-sm text-[var(--color-admin-text)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                                            placeholder="0" />
                                    </div>
                                    @error('processTotalDiterima') <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>

                                @if($processTotalDiterima !== '' && $processTotalDiterima != $item->total_seharusnya)
                                    <div class="rounded-lg border border-[var(--color-admin-border)] p-3 {{ $processTotalDiterima > $item->total_seharusnya ? 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400' }}">
                                        <p class="text-xs font-medium">
                                            Selisih: Rp {{ number_format(abs($processTotalDiterima - $item->total_seharusnya), 0, ',', '.') }} ({{ $processTotalDiterima > $item->total_seharusnya ? 'Lebih' : 'Kurang' }})
                                        </p>
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Keterangan Selisih (Wajib)</label>
                                        <textarea wire:model.live="processKeterangan" rows="2"
                                            class="w-full rounded-lg border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 py-2 text-sm text-[var(--color-admin-text)] placeholder-[var(--color-admin-mute)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                                            placeholder="Jelaskan penyebab selisih..."></textarea>
                                    </div>
                                @endif

                                <div class="pt-1 flex items-center gap-2">
                                    <button type="button" wire:click="processSubmission({{ $item->id }})" wire:loading.attr="disabled"
                                        wire:confirm="Yakin mengonfirmasi dan menyimpan hasil rekonsiliasi kas ini?"
                                        class="h-9 rounded-lg bg-emerald-600 px-4 text-xs font-semibold text-white shadow-xs transition hover:bg-emerald-700">
                                        Konfirmasi &amp; Simpan
                                    </button>
                                    <button type="button" wire:click="cancelProcess"
                                        class="h-9 rounded-lg border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-xs font-semibold text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)]">
                                        Batal
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Form Penolakan Setoran Inline --}}
                        @if($rejectingId === $item->id)
                            <div class="mt-4 rounded-xl border border-red-500/20 bg-red-500/5 p-4 space-y-3">
                                <p class="text-xs font-medium text-red-700 dark:text-red-300">
                                    Menolak pengajuan akan mengembalikan seluruh setoran terkait ke status belum disetor.
                                </p>

                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Alasan Penolakan (Wajib)</label>
                                    <textarea wire:model="rejectAlasan" rows="2"
                                        class="w-full rounded-lg border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 py-2 text-sm text-[var(--color-admin-text)] placeholder-[var(--color-admin-mute)] focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                                        placeholder="Jelaskan alasan penolakan..."></textarea>
                                    @error('rejectAlasan') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>

                                <div class="pt-1 flex items-center gap-2">
                                    <button type="button" wire:click="rejectSubmission" wire:loading.attr="disabled"
                                        wire:confirm="Yakin menolak pengajuan setoran ini?"
                                        class="h-9 rounded-lg bg-red-600 px-4 text-xs font-semibold text-white transition hover:bg-red-700">
                                        Tolak Pengajuan
                                    </button>
                                    <button type="button" wire:click="cancelReject"
                                        class="h-9 rounded-lg border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-xs font-semibold text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)]">
                                        Batal
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center">
                <p class="text-sm font-medium text-[var(--color-admin-text)]">Tidak ada pengajuan setoran</p>
                <p class="text-xs text-[var(--color-admin-mute)] mt-1">Semua pengajuan sudah diproses.</p>
            </div>
        @endif
    </div>

    {{-- Card: Riwayat Rekonsiliasi --}}
    <div class="overflow-hidden rounded-2xl border border-[var(--color-admin-border)] bg-[var(--color-surface)] shadow-xs">
        <div class="border-b border-[var(--color-admin-border)] px-5 py-4">
            <h2 class="text-sm md:text-base font-semibold text-[var(--color-admin-text)]">Riwayat Rekonsiliasi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-[var(--color-admin-border)] bg-[var(--color-surface-2)]">
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">TANGGAL</th>
                        <th class="px-5 py-3.5 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">KOLEKTOR</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">SEHARUSNYA</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">DITERIMA</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">SELISIH</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">STATUS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-admin-border)]">
                    @forelse($riwayat as $item)
                        <tr class="transition hover:bg-[var(--color-surface-2)]/60">
                            <td class="px-5 py-4 text-xs font-medium text-[var(--color-admin-text)]">
                                {{ $item->tanggal_setor->translatedFormat('d M') }}
                            </td>
                            <td class="px-5 py-4 font-semibold text-sm text-[var(--color-admin-text)]">
                                {{ $item->kolektor->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-right tabular-nums text-sm text-[var(--color-admin-text)]">
                                Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-right tabular-nums text-sm text-[var(--color-admin-text)]">
                                @if($item->total_diterima !== null)
                                    Rp {{ number_format($item->total_diterima, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right tabular-nums text-sm font-medium">
                                @if($item->selisih !== null)
                                    @if($item->selisih == 0)
                                        <span class="text-[var(--color-admin-text)]">Rp 0</span>
                                    @elseif($item->selisih < 0)
                                        <span class="text-rose-600 dark:text-rose-400">Rp -{{ number_format(abs($item->selisih), 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-amber-600 dark:text-amber-400">+Rp {{ number_format($item->selisih, 0, ',', '.') }}</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($item->status === 'cocok')
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                        Cocok
                                    </span>
                                @elseif($item->status === 'kurang')
                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">
                                        Kurang
                                    </span>
                                @elseif($item->status === 'lebih')
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                        Lebih
                                    </span>
                                @elseif($item->status === 'dibatalkan')
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        Ditolak
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-xs text-[var(--color-admin-mute)]">
                                Belum ada riwayat rekonsiliasi kas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Fallback Kartu Mobile --}}
        <div data-test="kartu-riwayat" class="divide-y divide-[var(--color-admin-border)] md:hidden">
            @forelse($riwayat as $item)
                <div class="p-4 space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="font-bold text-sm text-[var(--color-admin-text)]">{{ $item->kolektor->name ?? '-' }}</p>
                            <p class="text-xs text-[var(--color-admin-mute)]">{{ $item->tanggal_setor->translatedFormat('d M Y') }}</p>
                        </div>
                        <div>
                            @if($item->status === 'cocok')
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                    Cocok
                                </span>
                            @elseif($item->status === 'kurang')
                                <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">
                                    Kurang
                                </span>
                            @elseif($item->status === 'lebih')
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                    Lebih
                                </span>
                            @elseif($item->status === 'dibatalkan')
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    Ditolak
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                    {{ ucfirst($item->status) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-xs border-t border-[var(--color-admin-border)] pt-2">
                        <div>
                            <span class="text-[var(--color-admin-mute)]">Seharusnya:</span>
                            <div class="font-semibold tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-[var(--color-admin-mute)]">Diterima:</span>
                            <div class="font-semibold tabular-nums text-[var(--color-admin-text)]">
                                @if($item->total_diterima !== null)
                                    Rp {{ number_format($item->total_diterima, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div>
                            <span class="text-[var(--color-admin-mute)]">Selisih:</span>
                            <div class="font-semibold tabular-nums">
                                @if($item->selisih !== null)
                                    @if($item->selisih == 0)
                                        <span class="text-[var(--color-admin-text)]">Rp 0</span>
                                    @elseif($item->selisih < 0)
                                        <span class="text-rose-600 dark:text-rose-400">Rp -{{ number_format(abs($item->selisih), 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-amber-600 dark:text-amber-400">+Rp {{ number_format($item->selisih, 0, ',', '.') }}</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-[var(--color-admin-mute)]">
                    Belum ada riwayat rekonsiliasi kas.
                </div>
            @endforelse
        </div>

        @if($riwayat->hasPages())
            <div class="border-t border-[var(--color-admin-border)] px-4 py-3">
                {{ $riwayat->links() }}
            </div>
        @endif
    </div>

    {{-- Modal: Input Manual Setoran Kas --}}
    @if($showManualModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs overflow-y-auto"
            role="dialog" aria-modal="true" aria-labelledby="modal-manual-title">
            <div class="relative w-full max-w-lg rounded-2xl border border-[var(--color-admin-border)] bg-[var(--color-surface)] p-6 shadow-xl my-8">
                {{-- Header Modal --}}
                <div class="flex items-start justify-between border-b border-[var(--color-admin-border)] pb-4 mb-4">
                    <div>
                        <h3 id="modal-manual-title" class="text-base font-semibold text-[var(--color-admin-text)]">
                            Input Manual Setoran Kas
                        </h3>
                        <p class="text-xs text-[var(--color-admin-mute)] mt-0.5">
                            Catat setoran kas kolektor yang diserahkan langsung ke kantor.
                        </p>
                    </div>
                    <button type="button" wire:click="tutupManualModal"
                        class="rounded-lg p-1 text-[var(--color-admin-mute)] hover:bg-[var(--color-surface-2)] hover:text-[var(--color-admin-text)] transition"
                        aria-label="Tutup modal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                {{-- Pilih Kolektor --}}
                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Kolektor</label>
                        <select wire:model.live="kolektorId"
                            class="h-10 w-full rounded-xl border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-sm text-[var(--color-admin-text)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]">
                            <option value="">Pilih Kolektor</option>
                            @foreach($kolektorList as $k)
                                <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->no_hp }})</option>
                            @endforeach
                        </select>
                        @error('kolektorId') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    @if($showForm && $kolektorId)
                        <div class="rounded-xl border border-[var(--color-admin-border)] bg-[var(--color-surface-2)] p-4 space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Detail Transaksi Belum Disetor</p>
                            
                            @if($detailTransaksi->count() > 0)
                                <div class="space-y-1.5 max-h-44 overflow-y-auto">
                                    @foreach($detailTransaksi as $trx)
                                        <div class="flex items-center justify-between rounded-lg border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 py-2 text-xs">
                                            <span class="text-[var(--color-admin-text)] font-medium">{{ $trx->nasabah->name ?? '-' }} · {{ $trx->produk->nama }}</span>
                                            <span class="tabular-nums font-semibold text-[var(--color-admin-text)]">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3 border-t border-[var(--color-admin-border)] pt-3 space-y-1.5 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-[var(--color-admin-mute)]">Setoran Masuk:</span>
                                        <span class="tabular-nums font-medium text-[var(--color-admin-text)]">Rp {{ number_format($totalSetoranMasuk, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-[var(--color-admin-mute)]">Penarikan Tunai:</span>
                                        <span class="tabular-nums font-medium text-[var(--color-admin-text)]">Rp {{ number_format($totalPenarikanTunai, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between border-t border-[var(--color-admin-border)] pt-2 font-bold text-sm">
                                        <span class="text-[var(--color-admin-text)]">Total Seharusnya:</span>
                                        <span class="tabular-nums text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalSeharusnya, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @else
                                <p class="text-xs text-[var(--color-admin-mute)]">Tidak ada transaksi setoran aktif untuk kolektor ini.</p>
                            @endif
                        </div>

                        @if($rincianLangsung['belum']['jumlah'] + $rincianLangsung['sesuai']['jumlah'] + $rincianLangsung['bermasalah']['jumlah'] > 0)
                            <div class="rounded-xl border border-amber-500/20 bg-amber-500/10 p-3 text-xs text-amber-800 dark:text-amber-300">
                                <p class="font-semibold">Rincian penarikan offline langsung kolektor ini:</p>
                                <p class="mt-1 tabular-nums text-[11px]">
                                    belum ditinjau {{ $rincianLangsung['belum']['jumlah'] }} (Rp {{ number_format($rincianLangsung['belum']['nominal'], 0, ',', '.') }})
                                    &middot; sesuai {{ $rincianLangsung['sesuai']['jumlah'] }} (Rp {{ number_format($rincianLangsung['sesuai']['nominal'], 0, ',', '.') }})
                                    &middot; bermasalah {{ $rincianLangsung['bermasalah']['jumlah'] }} (Rp {{ number_format($rincianLangsung['bermasalah']['nominal'], 0, ',', '.') }})
                                </p>
                            </div>
                        @endif

                        @if($detailTransaksi->count() > 0)
                            <form wire:submit="submit" class="space-y-4 pt-1">
                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Total Diterima (Fisik)</label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 tabular-nums text-xs font-medium text-[var(--color-admin-mute)]">Rp</span>
                                        <input type="number" wire:model="totalDiterima"
                                            class="h-10 w-full rounded-xl border border-[var(--color-admin-border)] bg-[var(--color-surface)] pl-9 pr-3 text-sm text-[var(--color-admin-text)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                                            placeholder="0" />
                                    </div>
                                    @error('totalDiterima') <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>

                                @if($totalDiterima && $totalDiterima != $totalSeharusnya)
                                    <div class="rounded-xl border border-[var(--color-admin-border)] p-3 {{ $totalDiterima > $totalSeharusnya ? 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20' }}">
                                        <p class="text-xs font-medium">
                                            Selisih: Rp {{ number_format(abs($totalDiterima - $totalSeharusnya), 0, ',', '.') }} ({{ $totalDiterima > $totalSeharusnya ? 'Lebih' : 'Kurang' }})
                                        </p>
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Keterangan Selisih (Wajib)</label>
                                        <textarea wire:model="keterangan" rows="2"
                                            class="w-full rounded-xl border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 py-2 text-sm text-[var(--color-admin-text)] placeholder-[var(--color-admin-mute)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                                            placeholder="Jelaskan penyebab selisih..."></textarea>
                                    </div>
                                @endif

                                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[var(--color-admin-border)]">
                                    <button type="button" wire:click="tutupManualModal"
                                        class="h-10 rounded-xl border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-4 text-xs font-semibold text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)]">
                                        Batal
                                    </button>
                                    <button type="submit" wire:loading.attr="disabled"
                                        class="h-10 rounded-xl bg-emerald-600 px-5 text-xs font-semibold text-white shadow-xs transition hover:bg-emerald-700">
                                        Simpan Rekonsiliasi
                                    </button>
                                </div>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
