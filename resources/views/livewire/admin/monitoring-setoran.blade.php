<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Monitoring Koreksi Setoran</h1>
        <p class="mt-1 text-sm text-mute">Lihat, koreksi, atau batalkan setoran nasabah.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-info-soft px-4 py-3 text-sm text-info">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-danger-soft px-4 py-3 text-sm text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <input type="text" wire:model.live="search" placeholder="Cari nama nasabah atau produk..."
            class="h-10 w-full rounded-md border border-hairline bg-white px-4 text-sm text-ink placeholder-[#737373] focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10 sm:w-64" />
        <select wire:model.live="statusFilter"
            class="h-10 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
            <option value="">Semua Status</option>
            <option value="tercatat">Tercatat</option>
            <option value="dikoreksi">Dikoreksi</option>
            <option value="dibatalkan">Dibatalkan</option>
        </select>
        <x-ui.tanggal wire:model.live="tanggalFilter"
            class="h-10 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
        <button type="button" wire:click="$refresh"
            class="min-h-11 inline-flex items-center gap-1.5 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset sm:ml-auto">
            Muat Ulang
        </button>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-[inset_0_0_0_1px_#e5e5e5]">
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-inset">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tanggal</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nasabah</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Produk</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Nominal</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Input Oleh</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($setoran as $item)
                        <tr class="transition hover:bg-inset">
                            <td class="px-4 py-3 text-sm text-body">{{ $item->tanggal_transaksi->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</div>
                                <div class="text-xs text-mute">{{ $item->nasabah->no_hp ?? '— (offline)' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-body">{{ $item->produk->nama ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="tabular-nums text-right text-sm font-medium text-ink">Rp {{ number_format($item->nominal, 0, ',', '.') }}</div>
                                @if($item->nominal_asli)
                                    <div class="tabular-nums text-right text-xs text-mute">Asli: Rp {{ number_format($item->nominal_asli, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-body">{{ $item->inputBy->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($item->status === 'tercatat')
                                    <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Tercatat</span>
                                @elseif($item->status === 'dikoreksi')
                                    <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-0.5 text-xs text-warning-deep">Dikoreksi</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs text-danger">Dibatalkan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if(in_array($item->status, ['tercatat', 'dikoreksi']))
                                    <div class="flex items-center gap-2">
                                        <button wire:click="toggleKoreksi({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full bg-accent px-3 py-1.5 text-xs font-medium text-accent-foreground transition hover:opacity-90">Koreksi</button>
                                        <button wire:click="toggleBatal({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full border border-hairline bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:bg-inset">Batal</button>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" /></svg>
                                    <p class="text-sm text-mute">Tidak ada data setoran.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($setoran as $item)
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</p>
                            <p class="text-xs text-mute">{{ $item->nasabah->no_hp ?? '— (offline)' }}</p>
                        </div>
                        @if($item->status === 'tercatat')
                            <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Tercatat</span>
                        @elseif($item->status === 'dikoreksi')
                            <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-0.5 text-xs text-warning-deep">Dikoreksi</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs text-danger">Dibatalkan</span>
                        @endif
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                        <div>
                            <span class="block text-xs text-mute">Tanggal</span>
                            <span class="tabular-nums text-sm text-body">{{ $item->tanggal_transaksi->translatedFormat('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Produk</span>
                            <span class="tabular-nums text-sm text-body">{{ $item->produk->nama ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Nominal</span>
                            <span class="tabular-nums text-sm font-semibold text-ink">Rp {{ number_format($item->nominal, 0, ',', '.') }}</span>
                            @if($item->nominal_asli)
                                <span class="block tabular-nums text-xs font-medium text-mute">Asli: Rp {{ number_format($item->nominal_asli, 0, ',', '.') }}</span>
                            @endif
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Input Oleh</span>
                            <span class="tabular-nums text-sm text-body">{{ $item->inputBy->name ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @if(in_array($item->status, ['tercatat', 'dikoreksi']))
                            <div class="flex items-center gap-2">
                                <button wire:click="toggleKoreksi({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full bg-accent px-3 py-1.5 text-xs font-medium text-accent-foreground transition hover:opacity-90">Koreksi</button>
                                <button wire:click="toggleBatal({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full border border-hairline bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:bg-inset">Batal</button>
                            </div>
                        @else
                            <span class="text-xs text-mute">-</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <p class="text-sm text-mute">Tidak ada data setoran.</p>
                </div>
            @endforelse
        </div>
        <div class="border-t border-hairline px-4 py-3">{{ $setoran->links() }}</div>
    </div>

    @if($showKoreksi)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-[0_8px_24px_rgb(0_0_0/0.12)]">
                <h3 class="text-lg font-semibold text-ink">Koreksi Setoran</h3>
                <p class="mt-1 text-sm text-mute">Ubah nominal setoran yang sudah tercatat.</p>
                @if($sudahDisetorTerpilih)
                    <div class="mt-3 rounded-lg bg-warning-soft px-3 py-2 text-xs text-warning-deep">
                        Setoran ini sudah disetor ke kantor. Perubahan hanya penanda audit &mdash; kas tidak diubah otomatis.
                    </div>
                @endif
                <form wire:submit="koreksi" wire:confirm="Yakin menyimpan koreksi setoran ini?" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Nominal Baru</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 tabular-nums text-sm text-mute">Rp</span>
                            <input type="number" wire:model="nominalBaru" min="0"
                                class="h-10 w-full rounded-md border border-hairline bg-white py-0 pl-10 pr-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                        </div>
                        @error('nominalBaru') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Alasan Koreksi</label>
                        <textarea wire:model="alasanKoreksi" rows="3"
                            class="w-full rounded-md border border-hairline bg-white px-3 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                            placeholder="Alasan koreksi..."></textarea>
                        @error('alasanKoreksi') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showKoreksi', false)"
                            class="min-h-11 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
                            Batal
                        </button>
                        <button type="submit"
                            class="min-h-11 rounded-md bg-accent px-4 py-2 text-sm font-medium text-accent-foreground transition hover:opacity-90">
                            Simpan Koreksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showBatal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-[0_8px_24px_rgb(0_0_0/0.12)]">
                <h3 class="text-lg font-semibold text-ink">Batalkan Setoran</h3>
                <p class="mt-1 text-sm text-mute">Setoran yang dibatalkan akan dikurangi dari saldo nasabah.</p>
                @if($sudahDisetorTerpilih)
                    <div class="mt-3 rounded-lg bg-warning-soft px-3 py-2 text-xs text-warning-deep">
                        Setoran ini sudah disetor ke kantor. Perubahan hanya penanda audit &mdash; kas tidak diubah otomatis.
                    </div>
                @endif
                <form wire:submit="batal" wire:confirm="Yakin membatalkan setoran ini? Saldo nasabah akan dikurangi." class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Alasan Pembatalan</label>
                        <textarea wire:model="alasanBatal" rows="3"
                            class="w-full rounded-md border border-hairline bg-white px-3 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                            placeholder="Alasan pembatalan..."></textarea>
                        @error('alasanBatal') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showBatal', false)"
                            class="min-h-11 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
                            Batal
                        </button>
                        <button type="submit"
                            class="min-h-11 rounded-md bg-danger px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">
                            Batalkan Setoran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>