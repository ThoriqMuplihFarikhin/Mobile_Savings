<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Approval Penarikan</h1>
        <p class="mt-1 text-sm text-[#888888]">Setujui atau tolak pengajuan penarikan nasabah.</p>
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

    <div class="mb-4 flex gap-2">
        @foreach(['pending' => 'Pending', 'approved' => 'Disetujui', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'] as $value => $label)
            <button wire:click="$set('statusFilter', '{{ $value }}')"
                class="rounded-full px-4 py-2 text-sm font-medium transition {{ $statusFilter === $value ? 'bg-[#171717] text-white' : 'bg-[#fafafa] text-[#4d4d4d] shadow-[inset_0_0_0_1px_#ebebeb] hover:bg-white' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-[#fafafa]">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Nasabah</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Produk</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Diminta</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Diterima</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Lokasi</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($penarikan as $item)
                        <tr class="transition hover:bg-[#fafafa]">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-[#171717]">{{ $item->nasabah->name ?? '-' }}</div>
                                <div class="text-xs text-[#888888]">{{ $item->nasabah->no_hp ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-[#4d4d4d]">{{ $item->produk->nama ?? '-' }}</td>
                            <td class="px-4 py-3 font-mono text-sm font-medium text-[#171717]">Rp {{ number_format($item->nominal_diminta, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 font-mono text-sm text-[#0070f3]">Rp {{ number_format($item->nominal_diterima, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-[#4d4d4d]">{{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}</td>
                            <td class="px-4 py-3">
                                @if($item->status === 'pending')
                                    <span class="inline-flex items-center rounded-full bg-[#ffefcf] px-2.5 py-0.5 font-mono text-xs text-[#ab570a]">Pending</span>
                                @elseif($item->status === 'approved')
                                    <span class="inline-flex items-center rounded-full bg-[#d3e5ff] px-2.5 py-0.5 font-mono text-xs text-[#0761d1]">Disetujui</span>
                                @elseif($item->status === 'selesai')
                                    <span class="inline-flex items-center rounded-full bg-[#d3e5ff] px-2.5 py-0.5 font-mono text-xs text-[#0070f3]">Selesai</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2.5 py-0.5 font-mono text-xs text-[#c50000]">Ditolak</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($item->status === 'pending')
                                    <div class="flex items-center gap-2">
                                        <button wire:click="approve({{ $item->id }})" class="inline-flex items-center gap-1 rounded-full bg-[#171717] px-3 py-1.5 text-xs font-medium text-white transition hover:opacity-90">Setuju</button>
                                        <button wire:click="reject({{ $item->id }})" class="inline-flex items-center gap-1 rounded-full border border-[#ebebeb] bg-white px-3 py-1.5 text-xs font-medium text-[#171717] transition hover:bg-[#fafafa]">Tolak</button>
                                    </div>
                                @elseif($item->status === 'approved')
                                    <button wire:click="selesai({{ $item->id }})" class="inline-flex items-center gap-1 rounded-full bg-[#171717] px-3 py-1.5 text-xs font-medium text-white transition hover:opacity-90">Tandai Selesai</button>
                                @else
                                    <span class="text-xs text-[#888888]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    <p class="text-sm text-[#888888]">Tidak ada data penarikan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#ebebeb] px-4 py-3">{{ $penarikan->links() }}</div>
    </div>
</div>