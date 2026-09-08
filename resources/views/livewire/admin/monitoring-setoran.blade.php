<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Monitoring Koreksi Setoran</h1>
        <p class="mt-1 text-sm text-[#888888]">Lihat, koreksi, atau batalkan setoran nasabah.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-[#d3e5ff] px-4 py-3 text-sm text-[#0761d1]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-[#f7d4d6] px-4 py-3 text-sm text-[#c50000]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <input type="text" wire:model.live="search" placeholder="Cari nama nasabah atau produk..."
            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-4 text-sm text-[#171717] placeholder-[#a1a1a1] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10 sm:w-64" />
        <select wire:model.live="statusFilter"
            class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
            <option value="">Semua Status</option>
            <option value="tercatat">Tercatat</option>
            <option value="dikoreksi">Dikoreksi</option>
            <option value="dibatalkan">Dibatalkan</option>
        </select>
        <input type="date" wire:model.live="tanggalFilter"
            class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-[#fafafa]">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Tanggal</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Nasabah</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Produk</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Nominal</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Input Oleh</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($setoran as $item)
                        <tr class="transition hover:bg-[#fafafa]">
                            <td class="px-4 py-3 text-sm text-[#4d4d4d]">{{ $item->tanggal_transaksi->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-[#171717]">{{ $item->nasabah->name ?? '-' }}</div>
                                <div class="text-xs text-[#888888]">{{ $item->nasabah->no_hp ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-[#4d4d4d]">{{ $item->produk->nama ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="font-mono text-sm font-medium text-[#171717]">Rp {{ number_format($item->nominal, 0, ',', '.') }}</div>
                                @if($item->nominal_asli)
                                    <div class="font-mono text-xs text-[#888888]">Asli: Rp {{ number_format($item->nominal_asli, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-[#4d4d4d]">{{ $item->inputBy->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($item->status === 'tercatat')
                                    <span class="inline-flex items-center rounded-full bg-[#d3e5ff] px-2.5 py-0.5 font-mono text-xs text-[#0761d1]">Tercatat</span>
                                @elseif($item->status === 'dikoreksi')
                                    <span class="inline-flex items-center rounded-full bg-[#ffefcf] px-2.5 py-0.5 font-mono text-xs text-[#ab570a]">Dikoreksi</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2.5 py-0.5 font-mono text-xs text-[#c50000]">Dibatalkan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($item->status === 'tercatat')
                                    <div class="flex items-center gap-2">
                                        <button wire:click="toggleKoreksi({{ $item->id }})" class="inline-flex items-center gap-1 rounded-full bg-[#171717] px-3 py-1.5 text-xs font-medium text-white transition hover:opacity-90">Koreksi</button>
                                        <button wire:click="toggleBatal({{ $item->id }})" class="inline-flex items-center gap-1 rounded-full border border-[#ebebeb] bg-white px-3 py-1.5 text-xs font-medium text-[#171717] transition hover:bg-[#fafafa]">Batal</button>
                                    </div>
                                @else
                                    <span class="text-xs text-[#888888]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" /></svg>
                                    <p class="text-sm text-[#888888]">Tidak ada data setoran.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#ebebeb] px-4 py-3">{{ $setoran->links() }}</div>
    </div>

    @if($showKoreksi)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-[#171717]">Koreksi Setoran</h3>
                <p class="mt-1 text-sm text-[#888888]">Ubah nominal setoran yang sudah tercatat.</p>
                <form wire:submit="koreksi" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Nominal Baru</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 font-mono text-sm text-[#888888]">Rp</span>
                            <input type="number" wire:model="nominalBaru" min="0"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white py-0 pl-10 pr-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        </div>
                        @error('nominalBaru') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Alasan Koreksi</label>
                        <textarea wire:model="alasanKoreksi" rows="3"
                            class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2.5 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                            placeholder="Alasan koreksi..."></textarea>
                        @error('alasanKoreksi') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showKoreksi', false)"
                            class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-[#171717] transition hover:bg-[#fafafa]">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-full bg-[#171717] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">
                            Simpan Koreksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showBatal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-[#171717]">Batalkan Setoran</h3>
                <p class="mt-1 text-sm text-[#888888]">Setoran yang dibatalkan akan dikurangi dari saldo nasabah.</p>
                <form wire:submit="batal" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Alasan Pembatalan</label>
                        <textarea wire:model="alasanBatal" rows="3"
                            class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2.5 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                            placeholder="Alasan pembatalan..."></textarea>
                        @error('alasanBatal') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showBatal', false)"
                            class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-[#171717] transition hover:bg-[#fafafa]">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-full bg-[#ee0000] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">
                            Batalkan Setoran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>