<div>
    {{-- Page Header --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Komplain</h1>
            <p class="mt-1 text-sm text-[#888888]">Ajukan keluhan atau pertanyaan terkait tabungan Anda.</p>
        </div>
        <button wire:click="toggleForm"
            class="inline-flex shrink-0 items-center gap-2 rounded-full bg-[#171717] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Ajukan Komplain
        </button>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-[#d3e5ff] px-4 py-3 text-sm text-[#0761d1]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Form --}}
    @if($showForm)
        <div class="mb-6 rounded-xl bg-[#fafafa] shadow-[inset_0_0_0_1px_#ebebeb]">
            <div class="border-b border-[#ebebeb] px-6 py-4">
                <h3 class="text-sm font-semibold text-[#171717]">Form Komplain</h3>
            </div>
            <form wire:submit="submit" class="space-y-4 p-6">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[#171717]">Kategori</label>
                    <select wire:model="kategori"
                        class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                        <option value="saldo">Saldo</option>
                        <option value="barang_paket">Barang Paket</option>
                        <option value="penarikan">Penarikan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[#171717]">Transaksi Terkait <span class="text-[#888888]">(opsional)</span></label>
                    <select wire:model="transaksiTerkaitId"
                        class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                        <option value="">Tidak ada transaksi terkait</option>
                        @foreach($riwayatTransaksi as $trx)
                            <option value="{{ $trx->id }}">
                                {{ $trx->tanggal_transaksi->translatedFormat('d M Y') }} — {{ $trx->produk->nama ?? '-' }} — Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[#171717]">Deskripsi</label>
                    <textarea wire:model="deskripsi" rows="4"
                        class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2.5 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                        placeholder="Jelaskan keluhan Anda secara detail..."></textarea>
                    @error('deskripsi') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-3">
                    <button type="submit"
                        class="rounded-full bg-[#171717] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">
                        Kirim Komplain
                    </button>
                    <button type="button" wire:click="toggleForm"
                        class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-[#171717] transition hover:bg-[#fafafa]">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-[#fafafa]">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Tanggal</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Kategori</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Deskripsi</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Penyelesaian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($komplains as $item)
                        @php
                            $kategoriLabel = match($item->kategori) {
                                'saldo'       => 'Saldo',
                                'barang_paket'=> 'Barang Paket',
                                'penarikan'   => 'Penarikan',
                                default       => 'Lainnya'
                            };
                            $kategoriColor = match($item->kategori) {
                                'saldo'       => 'bg-[#d3e5ff] text-[#0761d1]',
                                'barang_paket'=> 'bg-[#d8ccf1] text-[#4c2889]',
                                'penarikan'   => 'bg-[#ffefcf] text-[#ab570a]',
                                default       => 'bg-[#fafafa] text-[#4d4d4d]'
                            };
                        @endphp
                        <tr class="transition hover:bg-[#fafafa]">
                            <td class="px-4 py-3 text-sm text-[#4d4d4d]">{{ $item->tanggal_dibuat->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 font-mono text-xs {{ $kategoriColor }}">
                                    {{ $kategoriLabel }}
                                </span>
                            </td>
                            <td class="max-w-[240px] truncate px-4 py-3 text-sm text-[#4d4d4d]">{{ $item->deskripsi }}</td>
                            <td class="px-4 py-3">
                                @if($item->status === 'baru')
                                    <span class="inline-flex items-center rounded-full bg-[#d3e5ff] px-2.5 py-0.5 font-mono text-xs text-[#0761d1]">Baru</span>
                                @elseif($item->status === 'diproses')
                                    <span class="inline-flex items-center rounded-full bg-[#ffefcf] px-2.5 py-0.5 font-mono text-xs text-[#ab570a]">Diproses</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-[#d3e5ff] px-2.5 py-0.5 font-mono text-xs text-[#0070f3]">Selesai</span>
                                @endif
                            </td>
                            <td class="max-w-[200px] truncate px-4 py-3 text-xs text-[#888888]">{{ $item->catatan_penyelesaian ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                    <p class="text-sm text-[#888888]">Belum ada komplain yang diajukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#ebebeb] px-4 py-3">{{ $komplains->links() }}</div>
    </div>
</div>
