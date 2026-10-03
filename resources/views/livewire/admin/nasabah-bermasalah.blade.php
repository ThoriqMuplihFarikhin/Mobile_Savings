<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Nasabah Bermasalah</h1>
        <p class="mt-1 text-sm text-gray-500">Daftar nasabah dengan kepesertaan paket yang perlu review.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-indigo-100 px-4 py-3 text-sm text-indigo-600">
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
            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-4 text-sm text-gray-900 placeholder-[#a1a1a1] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-gray-50">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Nasabah</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Produk</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Tunggakan</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Seharusnya</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Aktual</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($kepesertaan as $item)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $item->nasabah->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $item->nasabah->no_hp ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->produk->nama ?? '-' }}</td>
                            <td class="px-4 py-3 font-mono text-sm font-medium text-[#ee0000]">{{ $item->tunggakan }} hari</td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-600">Rp {{ number_format($item->total_seharusnya_terkumpul, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-600">Rp {{ number_format($item->total_aktual_terkumpul, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2.5 py-0.5 font-mono text-xs text-[#c50000]">Perlu Review</span>
                            </td>
                            <td class="px-4 py-3">
                                <button wire:click="selectKepesertaan({{ $item->id }})"
                                    class="inline-flex items-center gap-1 rounded-full bg-indigo-800 px-3 py-1.5 text-xs font-medium text-white transition hover:opacity-90">
                                    Review
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <p class="text-sm text-gray-500">Tidak ada nasabah bermasalah.</p>
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
                <h3 class="text-lg font-semibold text-gray-900">Detail Kepesertaan Paket</h3>

                <div class="mt-4 space-y-3 rounded-lg bg-gray-50 p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Nasabah</span>
                        <span class="font-medium text-gray-900">{{ $selectedKepesertaan->nasabah->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Produk</span>
                        <span class="text-gray-600">{{ $selectedKepesertaan->produk->nama ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Tanggal Mulai</span>
                        <span class="text-gray-600">{{ $selectedKepesertaan->tanggal_mulai_ikut->translatedFormat('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Seharusnya Terkumpul</span>
                        <span class="font-mono text-gray-600">Rp {{ number_format($selectedKepesertaan->total_seharusnya_terkumpul, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Aktual Terkumpul</span>
                        <span class="font-mono text-gray-600">Rp {{ number_format($selectedKepesertaan->total_aktual_terkumpul, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-[#ebebeb] pt-3 text-sm font-medium">
                        <span class="text-gray-500">Tunggakan</span>
                        <span class="font-mono text-[#ee0000]">{{ $selectedKepesertaan->tunggakan }} hari</span>
                    </div>
                </div>

                <form wire:submit="updateKeputusan" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-900">Keputusan Akhir</label>
                        <select wire:model="keputusan_akhir"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                            <option value="">Pilih Keputusan</option>
                            <option value="lanjut">Lanjut</option>
                            <option value="gagal_dikembalikan">Gagal - Dikembalikan</option>
                            <option value="gagal_dialihkan">Gagal - Dialihkan</option>
                        </select>
                        @error('keputusan_akhir') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    @if($keputusan_akhir === 'lanjut')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Tunda Hingga (maks. 90 hari)</label>
                            <input type="date" wire:model="ditunda_hingga"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                            @error('ditunda_hingga') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    @if($keputusan_akhir === 'gagal_dialihkan')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Produk Tujuan</label>
                            <select wire:model="produkTujuanId"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                                <option value="">Pilih Produk</option>
                                @foreach($produkTujuan as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->tipe }})</option>
                                @endforeach
                            </select>
                            @error('produkTujuanId') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-900">Catatan Admin</label>
                        <textarea wire:model="catatan_admin" rows="3"
                            class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                            placeholder="Catatan..."></textarea>
                    </div>
                    @if($keputusan_akhir === 'gagal_dikembalikan')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Metode Pengambilan</label>
                            <select wire:model="metode_pengambilan"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                                <option value="">Pilih Metode</option>
                                <option value="ambil_sendiri">Ambil Sendiri</option>
                                <option value="diantar_kolektor">Diantar Kolektor</option>
                            </select>
                            @error('metode_pengambilan') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showDetail', false)"
                            class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-full bg-indigo-800 px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">
                            Simpan Keputusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>