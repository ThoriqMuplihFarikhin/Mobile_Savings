<div>
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Riwayat Tabungan</h1>
        <p class="mt-1 text-sm text-[#888888]">Riwayat seluruh setoran tabungan Anda.</p>
    </div>

    {{-- Summary Cards --}}
    @if($produkSummary->count() > 0)
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($produkSummary as $summary)
                <div class="rounded-xl bg-white p-5 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 font-mono text-xs
                                {{ $summary['tipe'] === 'paket' ? 'bg-[#d8ccf1] text-[#4c2889]' : 'bg-[#d3e5ff] text-[#0761d1]' }}">
                                {{ $summary['tipe'] === 'paket' ? 'Paket' : 'Bebas' }}
                            </span>
                            <p class="mt-2 truncate text-sm text-[#4d4d4d]">{{ $summary['nama'] }}</p>
                            <p class="mt-1 text-xl font-semibold tracking-tight text-[#171717]">
                                Rp {{ number_format($summary['total'], 0, ',', '.') }}
                            </p>
                        </div>
                        <span class="shrink-0 font-mono text-xs text-[#888888]">{{ $summary['jumlah'] }} trx</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Filters --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <select wire:model.live="produkFilter"
            class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
            <option value="">Semua Produk</option>
            @foreach($produkList as $produk)
                <option value="{{ $produk->id }}">{{ $produk->nama }}</option>
            @endforeach
        </select>
        <div class="flex items-center gap-2">
            <input type="date" wire:model.live="dariTanggal"
                class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
            <span class="text-sm text-[#888888]">s/d</span>
            <input type="date" wire:model.live="sampaiTanggal"
                class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-[#fafafa]">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Tanggal</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Produk</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Nominal</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Sumber</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($setoran as $item)
                        <tr class="transition hover:bg-[#fafafa]">
                            <td class="px-4 py-3 text-sm text-[#4d4d4d]">{{ $item->tanggal_transaksi->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm text-[#4d4d4d]">{{ $item->produk->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-[#171717]">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-[#4d4d4d]">{{ $item->sumber_input === 'real_time' ? 'Real-time' : 'Susulan' }}</td>
                            <td class="px-4 py-3">
                                @if($item->status === 'tercatat')
                                    <span class="inline-flex items-center rounded-full bg-[#d3e5ff] px-2.5 py-0.5 font-mono text-xs text-[#0761d1]">Tercatat</span>
                                @elseif($item->status === 'dikoreksi')
                                    <span class="inline-flex items-center rounded-full bg-[#ffefcf] px-2.5 py-0.5 font-mono text-xs text-[#ab570a]">Dikoreksi</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2.5 py-0.5 font-mono text-xs text-[#c50000]">Dibatalkan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                    <p class="text-sm text-[#888888]">Belum ada riwayat setoran.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#ebebeb] px-4 py-3">{{ $setoran->links() }}</div>
    </div>
</div>
