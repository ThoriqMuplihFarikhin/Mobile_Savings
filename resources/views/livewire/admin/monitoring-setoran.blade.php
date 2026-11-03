<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Operasional"
        title="Monitoring Koreksi Setoran"
        subtitle="Lihat, koreksi, atau batalkan setoran nasabah."
    />

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-primary/20 bg-primary-soft p-3 text-xs font-medium text-on-primary-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-danger/20 bg-danger-soft p-3 text-xs font-medium text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-hairline/80 bg-white shadow-xs dark:bg-surface">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-hairline/80 p-3 sm:px-4">
            <div class="relative w-full max-w-sm sm:w-80">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-mute" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4" />
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama nasabah atau produk..."
                    class="h-[38px] w-full rounded-[10px] border border-hairline bg-surface pl-9 pr-3 text-xs text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" />
            </div>

            <div class="inline-flex items-center gap-1 rounded-[10px] border border-hairline bg-surface-2 p-[3px] overflow-x-auto max-w-full" role="group" aria-label="Status Setoran">
                @foreach([
                    '' => 'Semua Status',
                    'tercatat' => 'Tercatat',
                    'dikoreksi' => 'Dikoreksi',
                    'dibatalkan' => 'Dibatalkan',
                ] as $val => $label)
                    <button
                        type="button"
                        wire:click="$set('statusFilter', '{{ $val }}')"
                        class="min-h-11 inline-flex items-center justify-center rounded-[7px] px-3.5 py-1.5 text-xs transition cursor-pointer {{ $statusFilter === $val ? 'bg-surface text-ink font-semibold shadow-xs' : 'text-mute hover:text-ink font-medium' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-surface-2 text-[11px] font-semibold uppercase tracking-wider text-mute">
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Nasabah</th>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3 text-mute text-right">Nominal</th>
                        <th class="px-4 py-3">Input Oleh</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($setoran as $item)
                        <tr class="transition hover:bg-surface-2">
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-body">
                                {{ $item->tanggal_transaksi ? $item->tanggal_transaksi->translatedFormat('d M') : '-' }}{{ $item->tanggal_input_sistem ? ' ' . $item->tanggal_input_sistem->format('H.i') : '' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</div>
                                @if($item->nasabah?->no_hp)
                                    <div class="text-[11px] text-mute">{{ $item->nasabah->no_hp }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-body">{{ $item->produk->nama ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div class="tabular-nums text-right text-sm font-medium text-ink">Rp {{ number_format($item->nominal, 0, ',', '.') }}</div>
                                @if($item->nominal_asli)
                                    <div class="tabular-nums text-right text-xs text-mute">Asli: Rp {{ number_format($item->nominal_asli, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-body">{{ $item->inputBy->name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <x-admin.status-chip :status="$item->status" domain="setoran" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                @if(in_array($item->status, ['tercatat', 'dikoreksi']))
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        <button type="button" wire:click="toggleKoreksi({{ $item->id }})"
                                            class="inline-flex h-8 items-center rounded-lg border border-hairline/80 bg-white px-3 text-xs font-medium text-ink shadow-2xs transition hover:bg-inset active:scale-[0.98] dark:bg-surface">
                                            Koreksi
                                        </button>
                                        <button type="button" wire:click="toggleBatal({{ $item->id }})"
                                            class="inline-flex h-8 items-center rounded-lg border border-danger/20 bg-danger-soft px-3 text-xs font-medium text-danger transition hover:bg-danger/10 active:scale-[0.98]">
                                            Batalkan
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs text-mute">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" /></svg>
                                    <p class="text-xs text-mute">Tidak ada data setoran.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($setoran as $item)
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-ink">{{ $item->nasabah->name ?? '-' }}</p>
                            <p class="text-[11px] text-mute">{{ $item->nasabah->no_hp ?? '— (offline)' }}</p>
                        </div>
                        <x-admin.status-chip :status="$item->status" domain="setoran" />
                    </div>
                    <div class="grid grid-cols-2 gap-x-3 gap-y-1.5 rounded-[10px] bg-inset/40 p-2.5 text-[11px]">
                        <div>
                            <span class="block text-mute">Tanggal</span>
                            <span class="tabular-nums font-medium text-body">
                                {{ $item->tanggal_transaksi ? $item->tanggal_transaksi->translatedFormat('d M Y') : '-' }}{{ $item->tanggal_input_sistem ? ' ' . $item->tanggal_input_sistem->format('H.i') : '' }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-mute">Produk</span>
                            <span class="font-medium text-body">{{ $item->produk->nama ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-mute">Nominal</span>
                            <span class="tabular-nums font-semibold text-ink">Rp {{ number_format($item->nominal, 0, ',', '.') }}</span>
                            @if($item->nominal_asli)
                                <span class="block tabular-nums text-[10px] text-mute">Asli: Rp {{ number_format($item->nominal_asli, 0, ',', '.') }}</span>
                            @endif
                        </div>
                        <div>
                            <span class="block text-mute">Input Oleh</span>
                            <span class="font-medium text-body">{{ $item->inputBy->name ?? '-' }}</span>
                        </div>
                    </div>
                    @if(in_array($item->status, ['tercatat', 'dikoreksi']))
                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button type="button" wire:click="toggleKoreksi({{ $item->id }})"
                                class="min-h-11 inline-flex items-center justify-center rounded-lg border border-hairline/80 bg-white px-4 text-xs font-medium text-ink shadow-2xs transition hover:bg-inset active:scale-[0.98] dark:bg-surface">
                                Koreksi
                            </button>
                            <button type="button" wire:click="toggleBatal({{ $item->id }})"
                                class="min-h-11 inline-flex items-center justify-center rounded-lg border border-danger/20 bg-danger-soft px-4 text-xs font-medium text-danger transition hover:bg-danger/10 active:scale-[0.98]">
                                Batalkan
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <p class="text-xs text-mute">Tidak ada data setoran.</p>
                </div>
            @endforelse
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-hairline/80 px-4 py-3 sm:px-5">
            <div class="text-xs text-mute">
                Menampilkan {{ $setoran->count() }} dari {{ $setoran->total() }} data
            </div>
            @if($setoran->hasPages())
                <div>{{ $setoran->links() }}</div>
            @else
                <div class="text-xs text-mute">
                    Halaman {{ $setoran->currentPage() }} dari {{ $setoran->lastPage() }}
                </div>
            @endif
        </div>
    </div>

    @if($showKoreksi)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modal-koreksi-setoran-title">
            <div class="fixed inset-0 bg-black/50 transition-opacity" wire:click="$set('showKoreksi', false)" aria-hidden="true"></div>

            <div class="relative w-full max-w-md rounded-2xl border border-hairline bg-surface p-6 shadow-2xl dark:bg-surface" @click.stop>
                <div class="flex items-start justify-between border-b border-hairline pb-3">
                    <div>
                        <h3 id="modal-koreksi-setoran-title" class="text-base font-semibold text-ink">Koreksi Setoran</h3>
                        <p class="mt-0.5 text-xs text-mute">Ubah nominal setoran yang sudah tercatat.</p>
                    </div>
                    <button type="button" wire:click="$set('showKoreksi', false)" aria-label="Tutup"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-mute transition hover:bg-inset hover:text-ink">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @if($sudahDisetorTerpilih)
                    <div class="mt-3 rounded-[10px] border border-warning/20 bg-warning-soft px-3.5 py-2.5 text-xs text-warning-deep">
                        Setoran ini sudah disetor ke kantor. Perubahan hanya penanda audit &mdash; kas tidak diubah otomatis.
                    </div>
                @endif

                <form wire:submit="koreksi" wire:confirm="Yakin menyimpan koreksi setoran ini?" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink">Nominal Baru (Rp)</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 tabular-nums text-xs font-medium text-mute">Rp</span>
                            <input type="number" wire:model="nominalBaru" min="0"
                                class="h-[38px] w-full rounded-[10px] border border-hairline bg-white pl-9 pr-3 text-xs font-semibold tabular-nums text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                        </div>
                        @error('nominalBaru') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink">Alasan Koreksi</label>
                        <textarea wire:model="alasanKoreksi" rows="3"
                            class="w-full rounded-[10px] border border-hairline bg-white px-3 py-2 text-xs text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"
                            placeholder="Alasan koreksi..."></textarea>
                        @error('alasanKoreksi') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-2.5 pt-2">
                        <button type="button" wire:click="$set('showKoreksi', false)"
                            class="h-[38px] rounded-[10px] border border-hairline bg-white px-4 text-xs font-semibold text-ink transition hover:bg-inset dark:bg-surface">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex h-[38px] items-center gap-2 rounded-[10px] bg-primary px-5 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover">
                            Koreksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showBatal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modal-batal-setoran-title">
            <div class="fixed inset-0 bg-black/50 transition-opacity" wire:click="$set('showBatal', false)" aria-hidden="true"></div>

            <div class="relative w-full max-w-md rounded-2xl border border-hairline bg-surface p-6 shadow-2xl dark:bg-surface" @click.stop>
                <div class="flex items-start justify-between border-b border-hairline pb-3">
                    <div>
                        <h3 id="modal-batal-setoran-title" class="text-base font-semibold text-ink">Batalkan Setoran</h3>
                        <p class="mt-0.5 text-xs text-mute">Setoran yang dibatalkan akan dikurangi dari saldo nasabah.</p>
                    </div>
                    <button type="button" wire:click="$set('showBatal', false)" aria-label="Tutup"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-mute transition hover:bg-inset hover:text-ink">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @if($sudahDisetorTerpilih)
                    <div class="mt-3 rounded-[10px] border border-warning/20 bg-warning-soft px-3.5 py-2.5 text-xs text-warning-deep">
                        Setoran ini sudah disetor ke kantor. Perubahan hanya penanda audit &mdash; kas tidak diubah otomatis.
                    </div>
                @endif

                <form wire:submit="batal" wire:confirm="Yakin membatalkan setoran ini? Saldo nasabah akan dikurangi." class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink">Alasan Pembatalan</label>
                        <textarea wire:model="alasanBatal" rows="3"
                            class="w-full rounded-[10px] border border-hairline bg-white px-3 py-2 text-xs text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"
                            placeholder="Alasan pembatalan..."></textarea>
                        @error('alasanBatal') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-2.5 pt-2">
                        <button type="button" wire:click="$set('showBatal', false)"
                            class="h-[38px] rounded-[10px] border border-hairline bg-white px-4 text-xs font-semibold text-ink transition hover:bg-inset dark:bg-surface">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex h-[38px] items-center gap-2 rounded-[10px] bg-danger px-5 text-xs font-semibold text-white shadow-xs transition hover:opacity-90">
                            Batalkan setoran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
