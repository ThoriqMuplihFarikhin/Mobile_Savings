<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Rekonsiliasi Kas Kolektor</h1>
        <p class="mt-1 text-sm text-gray-500">Proses pengajuan setoran dari kolektor dan cocokkan kas fisik.</p>
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

    @if($pendingSubmissions->count() > 0)
        <div class="mb-6 rounded-xl bg-amber-100 shadow-[inset_0_0_0_1px_#e0c88a]">
            <div class="border-b border-[#e0c88a] px-6 py-4">
                <h3 class="text-sm font-semibold text-[#ab570a]">Pengajuan Setoran Pending ({{ $pendingSubmissions->count() }})</h3>
            </div>
            <div class="divide-y divide-[#e0c88a]">
                @foreach($pendingSubmissions as $item)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $item->kolektor->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $item->tanggal_setor->translatedFormat('d M Y') }} &middot; Seharusnya: Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</p>
                                @if($item->keterangan_selisih)
                                    <p class="mt-1 text-xs text-gray-500">Catatan: {{ $item->keterangan_selisih }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                @if($processingId === $item->id)
                                    <button wire:click="cancelProcess" class="rounded-full border border-[#ebebeb] bg-white px-3 py-1.5 text-xs font-medium text-gray-900 transition hover:bg-gray-50">
                                        Batal
                                    </button>
                                @elseif($rejectingId === $item->id)
                                    <button wire:click="cancelReject" class="rounded-full border border-[#ebebeb] bg-white px-3 py-1.5 text-xs font-medium text-gray-900 transition hover:bg-gray-50">
                                        Batal
                                    </button>
                                @else
                                    <button wire:click="startProcess({{ $item->id }})" class="btn-primary">
                                        Proses
                                    </button>
                                    <button wire:click="startReject({{ $item->id }})" class="rounded-full border border-[#f7d4d6] bg-white px-3 py-1.5 text-xs font-medium text-[#c50000] transition hover:bg-[#f7d4d6]">
                                        Tolak
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if($processingId === $item->id)
                            <div class="mt-4 rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                                <div class="mb-3 rounded-lg bg-gray-50 px-3 py-2">
                                    <p class="text-xs text-gray-500">Total Seharusnya</p>
                                    <p class="font-mono text-lg font-bold text-gray-900">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</p>
                                </div>

                                <div class="mb-3">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-900">Total Diterima (Fisik)</label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 font-mono text-sm text-gray-500">Rp</span>
                                        <input type="number" wire:model.live="processTotalDiterima"
                                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white py-0 pl-10 pr-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                            placeholder="0" />
                                    </div>
                                    @error('processTotalDiterima') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                                </div>

                                @if($processTotalDiterima !== '' && $processTotalDiterima != $item->total_seharusnya)
                                    <div class="mb-3 rounded-lg {{ $processTotalDiterima > $item->total_seharusnya ? 'bg-amber-100' : 'bg-[#f7d4d6]' }} px-4 py-3">
                                        <p class="text-sm font-medium {{ $processTotalDiterima > $item->total_seharusnya ? 'text-[#ab570a]' : 'text-[#c50000]' }}">
                                            Selisih: Rp {{ number_format(abs($processTotalDiterima - $item->total_seharusnya), 0, ',', '.') }} ({{ $processTotalDiterima > $item->total_seharusnya ? 'Lebih' : 'Kurang' }})
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="mb-1.5 block text-sm font-medium text-gray-900">Keterangan Selisih (Wajib)</label>
                                        <textarea wire:model.live="processKeterangan" rows="2"
                                            class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                            placeholder="Jelaskan penyebab selisih..."></textarea>
                                    </div>
                                @endif

                                <button wire:click="processSubmission({{ $item->id }})" wire:loading.attr="disabled"
                                    wire:confirm="Yakin mengonfirmasi dan menyimpan hasil rekonsiliasi kas ini?"
                                    class="btn-primary">
                                    Konfirmasi &amp; Simpan
                                </button>
                            </div>
                        @endif

                        @if($rejectingId === $item->id)
                            <div class="mt-4 rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                                <div class="mb-3 rounded-lg bg-[#f7d4d6] px-4 py-3">
                                    <p class="text-sm font-medium text-[#c50000]">
                                        Menolak pengajuan akan mengembalikan seluruh setoran ke status belum disetor.
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-900">Alasan Penolakan (Wajib)</label>
                                    <textarea wire:model="rejectAlasan" rows="2"
                                        class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                        placeholder="Jelaskan alasan penolakan..."></textarea>
                                    @error('rejectAlasan') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                                </div>

                                <button wire:click="rejectSubmission" wire:loading.attr="disabled"
                                    wire:confirm="Yakin menolak pengajuan setoran ini?"
                                    class="rounded-full bg-[#c50000] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#a30000]">
                                    Tolak Pengajuan
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mb-6 rounded-xl bg-gray-50 shadow-[inset_0_0_0_1px_#ebebeb]">
        <div class="border-b border-[#ebebeb] px-6 py-4">
            <h3 class="text-sm font-semibold text-gray-900">Input Manual Setoran Kas</h3>
        </div>
        <div class="p-6">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-900">Kolektor</label>
                <select wire:model.live="kolektorId"
                    class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                    <option value="">Pilih Kolektor</option>
                    @foreach($kolektorList as $k)
                        <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->no_hp }})</option>
                    @endforeach
                </select>
            </div>

            @if($showForm && $kolektorId)
                <div class="mt-4">
                    <div class="rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                        <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Detail Transaksi yang Belum Disetor</p>
                        @if($detailTransaksi->count() > 0)
                            <div class="mt-3 space-y-2">
                                @foreach($detailTransaksi as $trx)
                                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                                        <span class="text-sm text-gray-600">{{ $trx->nasabah->name ?? '-' }} - {{ $trx->produk->nama }}</span>
                                        <span class="font-mono text-sm font-medium text-gray-900">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 border-t border-[#ebebeb] pt-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Total Seharusnya</span>
                                    <span class="font-mono font-medium text-gray-900">Rp {{ number_format($totalSeharusnya, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @else
                            <p class="mt-2 text-sm text-gray-500">Tidak ada transaksi yang perlu disetor.</p>
                        @endif
                    </div>

                    @if($detailTransaksi->count() > 0)
                        <form wire:submit="submit" class="mt-4 space-y-4">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-900">Total Diterima (Fisik)</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 font-mono text-sm text-gray-500">Rp</span>
                                    <input type="number" wire:model="totalDiterima"
                                        class="h-10 w-full rounded-md border border-[#ebebeb] bg-white py-0 pl-10 pr-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                        placeholder="0" />
                                </div>
                                @error('totalDiterima') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                            </div>

                            @if($totalDiterima && $totalDiterima != $totalSeharusnya)
                                <div class="rounded-lg {{ $totalDiterima > $totalSeharusnya ? 'bg-amber-100' : 'bg-[#f7d4d6]' }} px-4 py-3">
                                    <p class="text-sm font-medium {{ $totalDiterima > $totalSeharusnya ? 'text-[#ab570a]' : 'text-[#c50000]' }}">
                                        Selisih: Rp {{ number_format(abs($totalDiterima - $totalSeharusnya), 0, ',', '.') }} ({{ $totalDiterima > $totalSeharusnya ? 'Lebih' : 'Kurang' }})
                                    </p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-900">Keterangan Selisih (Wajib)</label>
                                    <textarea wire:model="keterangan" rows="2"
                                        class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                        placeholder="Jelaskan penyebab selisih..."></textarea>
                                </div>
                            @endif

                            <button type="submit" wire:loading.attr="disabled"
                                class="btn-primary">
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
            <h3 class="text-sm font-semibold text-gray-900">Riwayat Rekonsiliasi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-gray-50">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Tanggal</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Kolektor</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Seharusnya</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Diterima</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Selisih</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($riwayat as $item)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->tanggal_setor->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->kolektor->name ?? '-' }}</td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-600">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-600">
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
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 font-mono text-xs text-[#ab570a]">Menunggu</span>
                                @elseif($item->status === 'cocok')
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 font-mono text-xs text-indigo-600">Cocok</span>
                                @elseif($item->status === 'lebih')
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 font-mono text-xs text-[#ab570a]">Lebih</span>
                                @elseif($item->status === 'dibatalkan')
                                    <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2.5 py-0.5 font-mono text-xs text-[#c50000]">Ditolak/Dibatalkan</span>
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
                                    <p class="text-sm text-gray-500">Belum ada riwayat rekonsiliasi.</p>
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
