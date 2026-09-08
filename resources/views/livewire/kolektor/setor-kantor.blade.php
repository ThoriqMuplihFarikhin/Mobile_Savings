<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Setor ke Kantor</h1>
        <p class="mt-1 text-sm text-[#888888]">Ajukan penyerahan kas fisik ke kantor pusat.</p>
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

    <div class="mb-6 rounded-xl bg-[#fafafa] shadow-[inset_0_0_0_1px_#ebebeb] p-6">
        <h3 class="mb-4 text-sm font-semibold text-[#171717]">Ringkasan Setoran</h3>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                <p class="font-mono text-xs uppercase tracking-wider text-[#888888]">Total Belum Disetor</p>
                <p class="mt-1 font-mono text-2xl font-bold text-[#171717]">Rp {{ number_format($totalBelumDisetor, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                <p class="font-mono text-xs uppercase tracking-wider text-[#888888]">Jumlah Transaksi</p>
                <p class="mt-1 font-mono text-2xl font-bold text-[#171717]">{{ $jumlahTransaksi }}</p>
            </div>
        </div>

        @if($totalBelumDisetor > 0)
            <div class="mt-4">
                <label class="mb-1.5 block text-sm font-medium text-[#171717]">Catatan (Opsional)</label>
                <textarea wire:model="catatan" rows="2"
                    class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2 text-sm text-[#171717] placeholder-[#a1a1a1] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                    placeholder="Catatan penyerahan..."></textarea>
            </div>
            <button wire:click="submit" wire:confirm="Yakin ingin mengajukan setoran ke kantor?"
                class="mt-4 rounded-full bg-[#171717] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">
                Ajukan Setoran
            </button>
        @else
            <p class="mt-4 text-sm text-[#888888]">Semua setoran sudah diserahkan ke kantor.</p>
        @endif
    </div>

    <div class="rounded-xl bg-[#fafafa] shadow-[inset_0_0_0_1px_#ebebeb]">
        <div class="border-b border-[#ebebeb] px-6 py-4">
            <h3 class="text-sm font-semibold text-[#171717]">Riwayat Setor ke Kantor</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-[#ebebeb] bg-[#fafafa]">
                    <tr>
                        <th class="px-6 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Tanggal</th>
                        <th class="px-6 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Seharusnya</th>
                        <th class="px-6 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Diterima</th>
                        <th class="px-6 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($riwayat as $item)
                        <tr class="hover:bg-white transition-colors">
                            <td class="px-6 py-4 text-[#4d4d4d]">{{ $item->tanggal_setor->translatedFormat('d M Y') }}</td>
                            <td class="px-6 py-4 font-mono text-[#171717]">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 font-mono text-[#4d4d4d]">
                                @if($item->total_diterima !== null)
                                    Rp {{ number_format($item->total_diterima, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($item->status === 'pending')
                                    <span class="inline-flex items-center rounded-full bg-[#ffefcf] px-2.5 py-0.5 font-mono text-xs text-[#ab570a]">Menunggu</span>
                                @elseif($item->status === 'cocok')
                                    <span class="inline-flex items-center rounded-full bg-[#d3e5ff] px-2.5 py-0.5 font-mono text-xs text-[#0761d1]">Cocok</span>
                                @elseif($item->status === 'lebih')
                                    <span class="inline-flex items-center rounded-full bg-[#ffefcf] px-2.5 py-0.5 font-mono text-xs text-[#ab570a]">Lebih</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2.5 py-0.5 font-mono text-xs text-[#c50000]">Kurang</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-[#888888]">Belum ada riwayat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#ebebeb] px-6 py-3">{{ $riwayat->links() }}</div>
    </div>
</div>
