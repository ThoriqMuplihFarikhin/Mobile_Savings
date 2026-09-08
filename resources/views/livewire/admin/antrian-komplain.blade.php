<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Antrian Komplain</h1>
        <p class="mt-1 text-sm text-[#888888]">Tinjau dan selesaikan komplain dari nasabah.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-[#d3e5ff] px-4 py-3 text-sm text-[#0761d1]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4 flex gap-2">
        @foreach(['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $value => $label)
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
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Tanggal</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Nasabah</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Kategori</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Deskripsi</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($komplains as $item)
                        <tr class="transition hover:bg-[#fafafa]">
                            <td class="px-4 py-3 text-sm text-[#4d4d4d]">{{ $item->tanggal_dibuat->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-[#171717]">{{ $item->nasabah->name ?? '-' }}</div>
                                <div class="text-xs text-[#888888]">{{ $item->nasabah->no_hp ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $color = match($item->kategori) {
                                        'saldo' => 'bg-[#d3e5ff] text-[#0761d1]',
                                        'barang_paket' => 'bg-[#d8ccf1] text-[#4c2889]',
                                        'penarikan' => 'bg-[#ffefcf] text-[#ab570a]',
                                        default => 'bg-[#fafafa] text-[#4d4d4d]'
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 font-mono text-xs {{ $color }}">
                                    {{ ucfirst(str_replace('_', ' ', $item->kategori)) }}
                                </span>
                            </td>
                            <td class="max-w-[200px] truncate px-4 py-3 text-sm text-[#4d4d4d]">{{ $item->deskripsi }}</td>
                            <td class="px-4 py-3">
                                @if($item->status === 'baru')
                                    <span class="inline-flex items-center rounded-full bg-[#d3e5ff] px-2.5 py-0.5 font-mono text-xs text-[#0761d1]">Baru</span>
                                @elseif($item->status === 'diproses')
                                    <span class="inline-flex items-center rounded-full bg-[#ffefcf] px-2.5 py-0.5 font-mono text-xs text-[#ab570a]">Diproses</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-[#d3e5ff] px-2.5 py-0.5 font-mono text-xs text-[#0070f3]">Selesai</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($item->status === 'baru')
                                    <div class="flex items-center gap-2">
                                        <button wire:click="proses({{ $item->id }})" class="inline-flex items-center gap-1 rounded-full bg-[#171717] px-3 py-1.5 text-xs font-medium text-white transition hover:opacity-90">Proses</button>
                                        <button wire:click="showDetail({{ $item->id }})" class="inline-flex items-center gap-1 rounded-full border border-[#ebebeb] bg-white px-3 py-1.5 text-xs font-medium text-[#171717] transition hover:bg-[#fafafa]">Detail</button>
                                    </div>
                                @elseif($item->status === 'diproses')
                                    <button wire:click="showDetail({{ $item->id }})" class="inline-flex items-center gap-1 rounded-full bg-[#171717] px-3 py-1.5 text-xs font-medium text-white transition hover:opacity-90">Selesaikan</button>
                                @else
                                    <span class="text-xs text-[#888888]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                    <p class="text-sm text-[#888888]">Tidak ada komplain.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#ebebeb] px-4 py-3">{{ $komplains->links() }}</div>
    </div>

    @if($showDetail && $selectedKomplain)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-[#171717]">Detail Komplain</h3>

                <div class="mt-4 space-y-3 rounded-lg bg-[#fafafa] p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <div class="flex justify-between text-sm">
                        <span class="text-[#888888]">Nasabah</span>
                        <span class="font-medium text-[#171717]">{{ $selectedKomplain->nasabah->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[#888888]">Kategori</span>
                        <span class="text-[#4d4d4d]">{{ ucfirst(str_replace('_', ' ', $selectedKomplain->kategori)) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[#888888]">Tanggal</span>
                        <span class="text-[#4d4d4d]">{{ $selectedKomplain->tanggal_dibuat->translatedFormat('d M Y H:i') }}</span>
                    </div>
                    @if($selectedKomplain->transaksiTerkait)
                        <div class="border-t border-[#ebebeb] pt-3">
                            <span class="text-sm font-medium text-[#171717]">Transaksi Terkait</span>
                            <div class="mt-2 space-y-1 rounded-md bg-white p-3 shadow-[inset_0_0_0_1px_#ebebeb]">
                                <div class="flex justify-between text-sm">
                                    <span class="text-[#888888]">Tanggal Transaksi</span>
                                    <span class="text-[#4d4d4d]">{{ $selectedKomplain->transaksiTerkait->tanggal_transaksi->translatedFormat('d M Y') }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-[#888888]">Produk</span>
                                    <span class="text-[#4d4d4d]">{{ $selectedKomplain->transaksiTerkait->produk->nama ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-[#888888]">Nominal</span>
                                    <span class="text-[#4d4d4d]">Rp {{ number_format($selectedKomplain->transaksiTerkait->nominal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="border-t border-[#ebebeb] pt-3">
                        <span class="text-sm text-[#888888]">Deskripsi</span>
                        <p class="mt-1 text-sm text-[#4d4d4d]">{{ $selectedKomplain->deskripsi }}</p>
                    </div>
                </div>

                <form wire:submit="selesai" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Catatan Penyelesaian</label>
                        <textarea wire:model="catatan" rows="3"
                            class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2.5 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                            placeholder="Jelaskan tindakan yang dilakukan..."></textarea>
                        @error('catatan') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showDetail', false)"
                            class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-[#171717] transition hover:bg-[#fafafa]">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-full bg-[#171717] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">
                            Selesaikan Komplain
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>