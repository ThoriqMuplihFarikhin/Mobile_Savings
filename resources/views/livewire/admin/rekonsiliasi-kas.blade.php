<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Rekonsiliasi Kas Kolektor</h1>
        <p class="mt-1 text-sm text-[#888888]">Proses pengajuan setoran dari kolektor dan cocokkan kas fisik.</p>
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

    @if($pendingSubmissions->count() > 0)
        <div class="mb-6 rounded-xl bg-[#ffefcf] shadow-[inset_0_0_0_1px_#e0c88a]">
            <div class="border-b border-[#e0c88a] px-6 py-4">
                <h3 class="text-sm font-semibold text-[#ab570a]">Pengajuan Setoran Pending ({{ $pendingSubmissions->count() }})</h3>
            </div>
            <div class="divide-y divide-[#e0c88a]">
                @foreach($pendingSubmissions as $item)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-[#171717]">{{ $item->kolektor->name ?? '-' }}</p>
                                <p class="text-xs text-[#888888]">{{ $item->tanggal_setor->translatedFormat('d M Y') }} &middot; Seharusnya: Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</p>
                                @if($item->keterangan_selisih)
                                    <p class="mt-1 text-xs text-[#888888]">Catatan: {{ $item->keterangan_selisih }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                @if($processingId === $item->id)
                                    <button wire:click="cancelProcess" class="rounded-full border border-[#ebebeb] bg-white px-3 py-1.5 text-xs font-medium text-[#171717] transition hover:bg-[#fafafa]">
                                        Batal
                                    </button>
                                @else
                                    <button wire:click="startProcess({{ $item->id }})" class="rounded-full bg-[#171717] px-3 py-1.5 text-xs font-medium text-white transition hover:opacity-90">
                                        Proses
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if($processingId === $item->id)
                            <div class="mt-4 rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                                <div class="mb-3 rounded-lg bg-[#fafafa] px-3 py-2">
                                    <p class="text-xs text-[#888888]">Total Seharusnya</p>
                                    <p class="font-mono text-lg font-bold text-[#171717]">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</p>
                                </div>

                                <div class="mb-3">
                                    <label class="mb-1.5 block text-sm font-medium text-[#171717]">Total Diterima (Fisik)</label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 font-mono text-sm text-[#888888]">Rp</span>
                                        <input type="number" wire:model.live="processTotalDiterima"
                                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white py-0 pl-10 pr-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                            placeholder="0" />
                                    </div>
                                    @error('processTotalDiterima') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                                </div>

                                @if($processTotalDiterima !== '' && $processTotalDiterima != $item->total_seharusnya)
                                    <div class="mb-3 rounded-lg {{ $processTotalDiterima > $item->total_seharusnya ? 'bg-[#ffefcf]' : 'bg-[#f7d4d6]' }} px-4 py-3">
                                        <p class="text-sm font-medium {{ $processTotalDiterima > $item->total_seharusnya ? 'text-[#ab570a]' : 'text-[#c50000]' }}">
                                            Selisih: Rp {{ number_format(abs($processTotalDiterima - $item->total_seharusnya), 0, ',', '.') }} ({{ $processTotalDiterima > $item->total_seharusnya ? 'Lebih' : 'Kurang' }})
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Keterangan Selisih (Wajib)</label>
                                        <textarea wire:model.live="processKeterangan" rows="2"
                                            class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2.5 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                            placeholder="Jelaskan penyebab selisih..."></textarea>
                                    </div>
                                @endif

                                <button wire:click="processSubmission({{ $item->id }})" wire:loading.attr="disabled"
                                    class="rounded-full bg-[#171717] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
                                    Konfirmasi & Simpan
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mb-6 rounded-xl bg-[#fafafa] shadow-[inset_0_0_0_1px_#ebebeb]">
        <div class="border-b border-[#ebebeb] px-6 py-4">
            <h3 class="text-sm font-semibold text-[#171717]">Input Manual Setoran Kas</h3>
        </div>
        <div class="p-6">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-[#171717]">Kolektor</label>
                <select wire:model.live="kolektorId"
                    class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                    <option value="">Pilih Kolektor</option>
                    @foreach($kolektorList as $k)
                        <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->no_hp }})</option>
                    @endforeach
                </select>
            </div>

            @if($showForm && $kolektorId)
                <div class="mt-4">
                    <div class="rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                        <p class="font-mono text-xs uppercase tracking-wider text-[#888888]">Detail Transaksi yang Belum Disetor</p>
                        @if($detailTransaksi->count() > 0)
                            <div class="mt-3 space-y-2">
                                @foreach($detailTransaksi as $trx)
                                    <div class="flex items-center justify-between rounded-lg bg-[#fafafa] px-3 py-2">
                                        <span class="text-sm text-[#4d4d4d]">{{ $trx->nasabah->name ?? '-' }} - {{ $trx->produk->nama }}</span>
                                        <span class="font-mono text-sm font-medium text-[#171717]">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 border-t border-[#ebebeb] pt-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-[#4d4d4d]">Total Seharusnya</span>
                                    <span class="font-mono font-medium text-[#171717]">Rp {{ number_format($totalSeharusnya, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @else
                            <p class="mt-2 text-sm text-[#888888]">Tidak ada transaksi yang perlu disetor.</p>
                        @endif
                    </div>

                    @if($detailTransaksi->count() > 0)
                        <form wire:submit="submit" class="mt-4 space-y-4">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-[#171717]">Total Diterima (Fisik)</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 font-mono text-sm text-[#888888]">Rp</span>
                                    <input type="number" wire:model="totalDiterima"
                                        class="h-10 w-full rounded-md border border-[#ebebeb] bg-white py-0 pl-10 pr-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                        placeholder="0" />
                                </div>
                                @error('totalDiterima') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                            </div>

                            @if($totalDiterima && $totalDiterima != $totalSeharusnya)
                                <div class="rounded-lg {{ $totalDiterima > $totalSeharusnya ? 'bg-[#ffefcf]' : 'bg-[#f7d4d6]' }} px-4 py-3">
                                    <p class="text-sm font-medium {{ $totalDiterima > $totalSeharusnya ? 'text-[#ab570a]' : 'text-[#c50000]' }}">
                                        Selisih: Rp {{ number_format(abs($totalDiterima - $totalSeharusnya), 0, ',', '.') }} ({{ $totalDiterima > $totalSeharusnya ? 'Lebih' : 'Kurang' }})
                                    </p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-[#171717]">Keterangan Selisih (Wajib)</label>
                                    <textarea wire:model="keterangan" rows="2"
                                        class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2.5 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                        placeholder="Jelaskan penyebab selisih..."></textarea>
                                </div>
                            @endif

                            <button type="submit" wire:loading.attr="disabled"
                                class="rounded-full bg-[#171717] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
                                Simpan Rekonsiliasi
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        <div class="border-b border-[#ebebeb] px-6 py-4">
            <h3 class="text-sm font-semibold text-[#171717]">Riwayat Rekonsiliasi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-[#fafafa]">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Tanggal</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Kolektor</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Seharusnya</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Diterima</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Selisih</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-[#888888]">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($riwayat as $item)
                        <tr class="transition hover:bg-[#fafafa]">
                            <td class="px-4 py-3 text-sm text-[#4d4d4d]">{{ $item->tanggal_setor->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-[#171717]">{{ $item->kolektor->name ?? '-' }}</td>
                            <td class="px-4 py-3 font-mono text-sm text-[#4d4d4d]">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 font-mono text-sm text-[#4d4d4d]">
                                @if($item->total_diterima !== null)
                                    Rp {{ number_format($item->total_diterima, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-sm {{ $item->selisih && $item->selisih != 0 ? 'text-[#ee0000]' : 'text-[#0070f3]' }}">
                                @if($item->selisih !== null)
                                    {{ $item->selisih >= 0 ? '+' : '' }} Rp {{ number_format($item->selisih, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3">
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
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <p class="text-sm text-[#888888]">Belum ada riwayat rekonsiliasi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#ebebeb] px-4 py-3">{{ $riwayat->links() }}</div>
    </div>
</div>
