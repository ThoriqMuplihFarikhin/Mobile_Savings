<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Keuangan"
        title="Rekonsiliasi Kas Kolektor"
        subtitle="Proses pengajuan setoran dari kolektor dan cocokkan kas fisik."
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

    {{-- Pengajuan Setoran Pending (Menunggu Pemrosesan) --}}
    @if($pendingSubmissions->count() > 0)
        <div class="rounded-[14px] border border-warning/20 bg-[var(--color-surface)] shadow-xs overflow-hidden">
            <div class="border-b border-[var(--color-admin-border)] bg-warning-soft px-5 py-3.5 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-warning-deep">Menunggu Pemrosesan</h3>
                    <p class="text-xs text-[var(--color-admin-mute)]">Ada {{ $pendingSubmissions->count() }} pengajuan setoran kantor dari kolektor.</p>
                </div>
                <span class="rounded-full border border-warning/20 bg-warning-soft px-2 py-0.5 text-xs font-semibold text-warning-deep">
                    {{ $pendingSubmissions->count() }} antrean
                </span>
            </div>
            <div class="divide-y divide-[var(--color-admin-border)]">
                @foreach($pendingSubmissions as $item)
                    <div class="p-5">
                        <div data-test="baris-pengajuan" class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-[var(--color-admin-text)]">{{ $item->kolektor->name ?? '-' }}</p>
                                <p class="text-xs text-[var(--color-admin-mute)] mt-0.5">
                                    {{ $item->tanggal_setor->translatedFormat('d M Y') }} &middot; Seharusnya: <span class="tabular-nums font-semibold text-[var(--color-admin-text)]">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</span>
                                    @if($item->keterangan_selisih)
                                        &middot; {{ $item->keterangan_selisih }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($processingId === $item->id)
                                    <button type="button" wire:click="cancelProcess" class="inline-flex h-8 items-center rounded-[8px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-xs font-medium text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)]">
                                        Batal
                                    </button>
                                @elseif($rejectingId === $item->id)
                                    <button type="button" wire:click="cancelReject" class="inline-flex h-8 items-center rounded-[8px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-xs font-medium text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)]">
                                        Batal
                                    </button>
                                @else
                                    <button type="button" wire:click="startProcess({{ $item->id }})" class="inline-flex h-8 items-center rounded-[8px] bg-[var(--color-accent)] px-3.5 text-xs font-medium text-white transition hover:opacity-90">
                                        Proses
                                    </button>
                                    <button type="button" wire:click="startReject({{ $item->id }})" class="inline-flex h-8 items-center rounded-[8px] border border-red-500/20 bg-red-500/10 px-3 text-xs font-medium text-red-600 transition hover:bg-red-500/20">
                                        Tolak
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Form Pemrosesan Setoran --}}
                        @if($processingId === $item->id)
                            <div class="mt-4 rounded-[12px] border border-[var(--color-admin-border)] bg-[var(--color-surface-2)] p-4 space-y-3">
                                <div class="flex items-center justify-between text-xs border-b border-[var(--color-admin-border)] pb-2.5">
                                    <span class="text-[var(--color-admin-mute)]">Total Seharusnya</span>
                                    <span class="tabular-nums text-sm font-semibold text-[var(--color-admin-text)]">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</span>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Total Diterima (Fisik)</label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 tabular-nums text-xs font-medium text-[var(--color-admin-mute)]">Rp</span>
                                        <input type="number" wire:model.live="processTotalDiterima"
                                            class="h-[38px] w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] pl-9 pr-3 text-sm text-[var(--color-admin-text)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                                            placeholder="0" />
                                    </div>
                                    @error('processTotalDiterima') <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>

                                @if($processTotalDiterima !== '' && $processTotalDiterima != $item->total_seharusnya)
                                    <div class="rounded-[10px] border border-[var(--color-admin-border)] p-3 {{ $processTotalDiterima > $item->total_seharusnya ? 'bg-warning-soft text-warning-deep border-warning/20' : 'bg-red-500/10 text-red-600 dark:text-red-400' }}">
                                        <p class="text-xs font-medium">
                                            Selisih: Rp {{ number_format(abs($processTotalDiterima - $item->total_seharusnya), 0, ',', '.') }} ({{ $processTotalDiterima > $item->total_seharusnya ? 'Lebih' : 'Kurang' }})
                                        </p>
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Keterangan Selisih (Wajib)</label>
                                        <textarea wire:model.live="processKeterangan" rows="2"
                                            class="w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 py-2 text-sm text-[var(--color-admin-text)] placeholder-[var(--color-admin-mute)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                                            placeholder="Jelaskan penyebab selisih..."></textarea>
                                    </div>
                                @endif

                                <div class="pt-1">
                                    <button type="button" wire:click="processSubmission({{ $item->id }})" wire:loading.attr="disabled"
                                        wire:confirm="Yakin mengonfirmasi dan menyimpan hasil rekonsiliasi kas ini?"
                                        class="h-[38px] rounded-[10px] bg-[var(--color-accent)] px-4 text-xs font-medium text-white transition hover:opacity-90">
                                        Konfirmasi &amp; Simpan
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Form Penolakan Setoran --}}
                        @if($rejectingId === $item->id)
                            <div class="mt-4 rounded-[12px] border border-red-500/20 bg-red-500/5 p-4 space-y-3">
                                <p class="text-xs font-medium text-red-700 dark:text-red-300">
                                    Menolak pengajuan akan mengembalikan seluruh setoran terkait ke status belum disetor.
                                </p>

                                <div>
                                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Alasan Penolakan (Wajib)</label>
                                    <textarea wire:model="rejectAlasan" rows="2"
                                        class="w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 py-2 text-sm text-[var(--color-admin-text)] placeholder-[var(--color-admin-mute)] focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                                        placeholder="Jelaskan alasan penolakan..."></textarea>
                                    @error('rejectAlasan') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>

                                <div class="pt-1">
                                    <button type="button" wire:click="rejectSubmission" wire:loading.attr="disabled"
                                        wire:confirm="Yakin menolak pengajuan setoran ini?"
                                        class="h-[38px] rounded-[10px] bg-red-600 px-4 text-xs font-medium text-white transition hover:bg-red-700">
                                        Tolak Pengajuan
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Input Manual Setoran Kas --}}
    <div class="rounded-[14px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] p-5 shadow-xs">
        <div class="border-b border-[var(--color-admin-border)] pb-3 mb-4">
            <h3 class="text-base font-semibold text-[var(--color-admin-text)]">Input Manual Setoran Kas</h3>
            <p class="text-xs text-[var(--color-admin-mute)] mt-0.5">Catat setoran kas kolektor yang diserahkan langsung ke kantor.</p>
        </div>

        <div>
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Kolektor</label>
            <select wire:model.live="kolektorId"
                class="h-[38px] w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-sm text-[var(--color-admin-text)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]">
                <option value="">Pilih Kolektor</option>
                @foreach($kolektorList as $k)
                    <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->no_hp }})</option>
                @endforeach
            </select>
        </div>

        @if($showForm && $kolektorId)
            <div class="mt-4">
                <div class="rounded-[12px] border border-[var(--color-admin-border)] bg-[var(--color-surface-2)] p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)] mb-3">Detail Transaksi yang Belum Disetor</p>
                    @if($detailTransaksi->count() > 0)
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            @foreach($detailTransaksi as $trx)
                                <div class="flex items-center justify-between rounded-[8px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 py-2 text-xs">
                                    <span class="text-[var(--color-admin-text)]">{{ $trx->nasabah->name ?? '-' }} · {{ $trx->produk->nama }}</span>
                                    <span class="tabular-nums font-medium text-[var(--color-admin-text)]">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3 border-t border-[var(--color-admin-border)] pt-3 space-y-1.5 text-xs">
                            <div class="flex justify-between">
                                <span class="text-[var(--color-admin-mute)]">Setoran Masuk</span>
                                <span class="tabular-nums font-medium text-[var(--color-admin-text)]">Rp {{ number_format($totalSetoranMasuk, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[var(--color-admin-mute)]">Penarikan Tunai</span>
                                <span class="tabular-nums font-medium text-[var(--color-admin-text)]">Rp {{ number_format($totalPenarikanTunai, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between border-t border-[var(--color-admin-border)] pt-2 font-semibold text-sm">
                                <span class="text-[var(--color-admin-text)]">Total Seharusnya</span>
                                <span class="tabular-nums text-[var(--color-accent)]">Rp {{ number_format($totalSeharusnya, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-[var(--color-admin-mute)]">Tidak ada transaksi yang perlu disetor.</p>
                    @endif
                </div>

                @if($rincianLangsung['belum']['jumlah'] + $rincianLangsung['sesuai']['jumlah'] + $rincianLangsung['bermasalah']['jumlah'] > 0)
                    <div class="mt-3 rounded-[10px] border border-warning/20 bg-warning-soft p-3 text-xs text-warning-deep">
                        <p class="font-medium">Rincian penarikan offline langsung kolektor ini:</p>
                        <p class="mt-1 tabular-nums text-[11px]">
                            belum ditinjau {{ $rincianLangsung['belum']['jumlah'] }} (Rp {{ number_format($rincianLangsung['belum']['nominal'], 0, ',', '.') }})
                            &middot; sesuai {{ $rincianLangsung['sesuai']['jumlah'] }} (Rp {{ number_format($rincianLangsung['sesuai']['nominal'], 0, ',', '.') }})
                            &middot; bermasalah {{ $rincianLangsung['bermasalah']['jumlah'] }} (Rp {{ number_format($rincianLangsung['bermasalah']['nominal'], 0, ',', '.') }})
                        </p>
                    </div>
                @endif

                @if($detailTransaksi->count() > 0)
                    <form wire:submit="submit" class="mt-4 space-y-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Total Diterima (Fisik)</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 tabular-nums text-xs font-medium text-[var(--color-admin-mute)]">Rp</span>
                                <input type="number" wire:model="totalDiterima"
                                    class="h-[38px] w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] pl-9 pr-3 text-sm text-[var(--color-admin-text)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                                    placeholder="0" />
                            </div>
                            @error('totalDiterima') <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        @if($totalDiterima && $totalDiterima != $totalSeharusnya)
                            <div class="rounded-[10px] border border-[var(--color-admin-border)] p-3 {{ $totalDiterima > $totalSeharusnya ? 'bg-warning-soft text-warning-deep border-warning/20' : 'bg-red-500/10 text-red-600 dark:text-red-400' }}">
                                <p class="text-xs font-medium">
                                    Selisih: Rp {{ number_format(abs($totalDiterima - $totalSeharusnya), 0, ',', '.') }} ({{ $totalDiterima > $totalSeharusnya ? 'Lebih' : 'Kurang' }})
                                </p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Keterangan Selisih (Wajib)</label>
                                <textarea wire:model="keterangan" rows="2"
                                    class="w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 py-2 text-sm text-[var(--color-admin-text)] placeholder-[var(--color-admin-mute)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                                    placeholder="Jelaskan penyebab selisih..."></textarea>
                            </div>
                        @endif

                        <button type="submit" wire:loading.attr="disabled"
                            class="h-[38px] rounded-[10px] bg-[var(--color-accent)] px-4 text-xs font-medium text-white transition hover:opacity-90">
                            Simpan Rekonsiliasi
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>

    {{-- Riwayat Rekonsiliasi --}}
    <div class="overflow-hidden rounded-[14px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] shadow-xs">
        <div class="border-b border-[var(--color-admin-border)] px-5 py-3.5">
            <h3 class="text-sm font-semibold text-[var(--color-admin-text)]">Riwayat Rekonsiliasi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-[var(--color-admin-border)] bg-[var(--color-surface-2)]">
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Tanggal</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Kolektor</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Seharusnya</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Diterima</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Selisih</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-admin-border)]">
                    @forelse($riwayat as $item)
                        <tr class="transition hover:bg-[var(--color-surface-2)]">
                            <td class="px-4 py-3 text-xs text-[var(--color-admin-mute)]">{{ $item->tanggal_setor->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium text-[var(--color-admin-text)]">{{ $item->kolektor->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-[var(--color-admin-text)]">
                                @if($item->total_diterima !== null)
                                    Rp {{ number_format($item->total_diterima, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums font-medium {{ $item->selisih && $item->selisih != 0 ? ($item->selisih > 0 ? 'text-warning-deep' : 'text-red-600 dark:text-red-400') : 'text-[var(--color-accent)]' }}">
                                @if($item->selisih !== null)
                                    {{ $item->selisih >= 0 ? '+' : '' }} Rp {{ number_format($item->selisih, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
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
                            <td colspan="6" class="p-0">
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
                <div class="p-4 space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <p class="font-medium text-[var(--color-admin-text)]">{{ $item->kolektor->name ?? '-' }}</p>
                            <p class="text-xs text-[var(--color-admin-mute)]">{{ $item->tanggal_setor->translatedFormat('d M Y') }}</p>
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
                    <div class="grid grid-cols-3 gap-2 text-xs border-t border-[var(--color-admin-border)] pt-2">
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
                            <div class="font-medium tabular-nums {{ $item->selisih && $item->selisih != 0 ? ($item->selisih > 0 ? 'text-warning-deep' : 'text-red-600 dark:text-red-400') : 'text-[var(--color-accent)]' }}">
                                @if($item->selisih !== null)
                                    {{ $item->selisih >= 0 ? '+' : '' }} Rp {{ number_format($item->selisih, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6">
                    <x-admin.empty-state
                        title="Belum ada riwayat rekonsiliasi"
                        description="Semua setoran kantor yang telah diverifikasi akan tercatat di sini."
                    />
                </div>
            @endforelse
        </div>

        @if($riwayat->hasPages())
            <div class="border-t border-[var(--color-admin-border)] px-4 py-3">
                {{ $riwayat->links() }}
            </div>
        @endif
    </div>
</div>

