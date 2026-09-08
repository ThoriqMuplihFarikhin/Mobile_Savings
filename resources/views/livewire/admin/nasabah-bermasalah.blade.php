<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Nasabah Bermasalah</h1>
        <p class="mt-1 text-sm text-[#888888]">Daftar nasabah dengan kepesertaan paket yang perlu review.</p>
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

    <div class="mb-4">
        <input type="text" wire:model.live="search" placeholder="Cari nama nasabah atau produk..."
            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-4 text-sm text-[#171717] placeholder-[#a1a1a1] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-[#fafafa]">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Nasabah</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Produk</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Tunggakan</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Seharusnya</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Aktual</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($kepesertaan as $item)
                        <tr class="transition hover:bg-[#fafafa]">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-[#171717]">{{ $item->nasabah->name ?? '-' }}</div>
                                <div class="text-xs text-[#888888]">{{ $item->nasabah->no_hp ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-[#4d4d4d]">{{ $item->produk->nama ?? '-' }}</td>
                            <td class="px-4 py-3 font-mono text-sm font-medium text-[#ee0000]">Rp {{ number_format($item->tunggakan, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 font-mono text-sm text-[#4d4d4d]">Rp {{ number_format($item->total_seharusnya_terkumpul, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 font-mono text-sm text-[#4d4d4d]">Rp {{ number_format($item->total_aktual_terkumpul, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2.5 py-0.5 font-mono text-xs text-[#c50000]">Perlu Review</span>
                            </td>
                            <td class="px-4 py-3">
                                <button wire:click="selectKepesertaan({{ $item->id }})"
                                    class="inline-flex items-center gap-1 rounded-full bg-[#171717] px-3 py-1.5 text-xs font-medium text-white transition hover:opacity-90">
                                    Review
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <p class="text-sm text-[#888888]">Tidak ada nasabah bermasalah.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#ebebeb] px-4 py-3">{{ $kepesertaan->links() }}</div>
    </div>

    @if($showDetail && $selectedKepesertaan)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-[#171717]">Detail Kepesertaan Paket</h3>

                <div class="mt-4 space-y-3 rounded-lg bg-[#fafafa] p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <div class="flex justify-between text-sm">
                        <span class="text-[#888888]">Nasabah</span>
                        <span class="font-medium text-[#171717]">{{ $selectedKepesertaan->nasabah->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[#888888]">Produk</span>
                        <span class="text-[#4d4d4d]">{{ $selectedKepesertaan->produk->nama ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[#888888]">Tanggal Mulai</span>
                        <span class="text-[#4d4d4d]">{{ $selectedKepesertaan->tanggal_mulai_ikut->translatedFormat('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[#888888]">Seharusnya Terkumpul</span>
                        <span class="font-mono text-[#4d4d4d]">Rp {{ number_format($selectedKepesertaan->total_seharusnya_terkumpul, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[#888888]">Aktual Terkumpul</span>
                        <span class="font-mono text-[#4d4d4d]">Rp {{ number_format($selectedKepesertaan->total_aktual_terkumpul, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-[#ebebeb] pt-3 text-sm font-medium">
                        <span class="text-[#888888]">Tunggakan</span>
                        <span class="font-mono text-[#ee0000]">Rp {{ number_format($selectedKepesertaan->tunggakan, 0, ',', '.') }}</span>
                    </div>
                </div>

                <form wire:submit="updateKeputusan" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Keputusan Akhir</label>
                        <select wire:model="keputusan_akhir"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                            <option value="">Pilih Keputusan</option>
                            <option value="lanjut">Lanjut</option>
                            <option value="gagal_dikembalikan">Gagal - Dikembalikan</option>
                            <option value="gagal_dialihkan">Gagal - Dialihkan</option>
                        </select>
                        @error('keputusan_akhir') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Catatan Admin</label>
                        <textarea wire:model="catatan_admin" rows="3"
                            class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2.5 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                            placeholder="Catatan..."></textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Metode Pengambilan</label>
                        <select wire:model="metode_pengambilan"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                            <option value="">Pilih Metode</option>
                            <option value="ambil_sendiri">Ambil Sendiri</option>
                            <option value="diantar_kolektor">Diantar Kolektor</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showDetail', false)"
                            class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-[#171717] transition hover:bg-[#fafafa]">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-full bg-[#171717] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">
                            Simpan Keputusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>