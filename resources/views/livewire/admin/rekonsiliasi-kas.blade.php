<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Rekonsiliasi Kas Kolektor</h1>
        <p class="mt-1 text-sm text-mute">Proses pengajuan setoran dari kolektor dan cocokkan kas fisik.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-info-soft px-4 py-3 text-sm text-info">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-danger-soft px-4 py-3 text-sm text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    @if($pendingSubmissions->count() > 0)
        <div class="mb-6 rounded-lg bg-warning-soft shadow-[inset_0_0_0_1px_#d97706]">
            <div class="border-b border-[#d97706] px-6 py-4">
                <h3 class="text-sm font-semibold text-warning-deep">Pengajuan Setoran Pending ({{ $pendingSubmissions->count() }})</h3>
            </div>
            <div class="divide-y divide-[#d97706]">
                @foreach($pendingSubmissions as $item)
                    <div class="px-6 py-4">
                        <div data-test="baris-pengajuan" class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="text-sm font-medium text-ink">{{ $item->kolektor->name ?? '-' }}</p>
                                <p class="text-xs text-mute">{{ $item->tanggal_setor->translatedFormat('d M Y') }} &middot; Seharusnya: Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</p>
                                @if($item->keterangan_selisih)
                                    <p class="mt-1 text-xs text-mute">Catatan: {{ $item->keterangan_selisih }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                @if($processingId === $item->id)
                                    <button wire:click="cancelProcess" class="rounded-md border border-hairline bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:bg-inset">
                                        Batal
                                    </button>
                                @elseif($rejectingId === $item->id)
                                    <button wire:click="cancelReject" class="rounded-md border border-hairline bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:bg-inset">
                                        Batal
                                    </button>
                                @else
                                    <button wire:click="startProcess({{ $item->id }})" class="btn-primary">
                                        Proses
                                    </button>
                                    <button wire:click="startReject({{ $item->id }})" class="rounded-full border border-danger bg-white px-3 py-1.5 text-xs font-medium text-danger transition hover:bg-danger-soft">
                                        Tolak
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if($processingId === $item->id)
                            <div class="mt-4 rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#e5e5e5]">
                                <div class="mb-3 rounded-lg bg-inset px-3 py-2">
                                    <p class="text-xs text-mute">Total Seharusnya</p>
                                    <p class="tabular-nums text-lg font-semibold text-ink">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</p>
                                </div>

                                <div class="mb-3">
                                    <label class="mb-1.5 block text-sm font-medium text-ink">Total Diterima (Fisik)</label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 tabular-nums text-sm text-mute">Rp</span>
                                        <input type="number" wire:model.live="processTotalDiterima"
                                            class="h-10 w-full rounded-md border border-hairline bg-white py-0 pl-10 pr-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                                            placeholder="0" />
                                    </div>
                                    @error('processTotalDiterima') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                                </div>

                                @if($processTotalDiterima !== '' && $processTotalDiterima != $item->total_seharusnya)
                                    <div class="mb-3 rounded-lg {{ $processTotalDiterima > $item->total_seharusnya ? 'bg-warning-soft' : 'bg-danger-soft' }} px-4 py-3">
                                        <p class="text-sm font-medium {{ $processTotalDiterima > $item->total_seharusnya ? 'text-warning-deep' : 'text-danger' }}">
                                            Selisih: Rp {{ number_format(abs($processTotalDiterima - $item->total_seharusnya), 0, ',', '.') }} ({{ $processTotalDiterima > $item->total_seharusnya ? 'Lebih' : 'Kurang' }})
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="mb-1.5 block text-sm font-medium text-ink">Keterangan Selisih (Wajib)</label>
                                        <textarea wire:model.live="processKeterangan" rows="2"
                                            class="w-full rounded-md border border-hairline bg-white px-3 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
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
                            <div class="mt-4 rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#e5e5e5]">
                                <div class="mb-3 rounded-lg bg-danger-soft px-4 py-3">
                                    <p class="text-sm font-medium text-danger">
                                        Menolak pengajuan akan mengembalikan seluruh setoran ke status belum disetor.
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <label class="mb-1.5 block text-sm font-medium text-ink">Alasan Penolakan (Wajib)</label>
                                    <textarea wire:model="rejectAlasan" rows="2"
                                        class="w-full rounded-md border border-hairline bg-white px-3 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                                        placeholder="Jelaskan alasan penolakan..."></textarea>
                                    @error('rejectAlasan') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                                </div>

                                <button wire:click="rejectSubmission" wire:loading.attr="disabled"
                                    wire:confirm="Yakin menolak pengajuan setoran ini?"
                                    class="rounded-md bg-danger px-4 py-2 text-xs font-semibold text-white transition hover:bg-danger">
                                    Tolak Pengajuan
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mb-6 rounded-lg bg-inset shadow-[inset_0_0_0_1px_#e5e5e5]">
        <div class="border-b border-hairline px-6 py-4">
            <h3 class="text-sm font-semibold text-ink">Input Manual Setoran Kas</h3>
        </div>
        <div class="p-6">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-ink">Kolektor</label>
                <select wire:model.live="kolektorId"
                    class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
                    <option value="">Pilih Kolektor</option>
                    @foreach($kolektorList as $k)
                        <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->no_hp }})</option>
                    @endforeach
                </select>
            </div>

            @if($showForm && $kolektorId)
                <div class="mt-4">
                    <div class="rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#e5e5e5]">
                        <p class="text-xs text-mute">Detail Transaksi yang Belum Disetor</p>
                        @if($detailTransaksi->count() > 0)
                            <div class="mt-3 space-y-2">
                                @foreach($detailTransaksi as $trx)
                                    <div class="flex items-center justify-between rounded-lg bg-inset px-3 py-2">
                                        <span class="text-sm text-body">{{ $trx->nasabah->name ?? '-' }} - {{ $trx->produk->nama }}</span>
                                        <span class="tabular-nums text-sm font-medium text-ink">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 border-t border-hairline pt-3 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-body">Setoran Masuk</span>
                                    <span class="tabular-nums font-medium text-ink">Rp {{ number_format($totalSetoranMasuk, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-body">Penarikan Tunai</span>
                                    <span class="tabular-nums font-medium text-ink">Rp {{ number_format($totalPenarikanTunai, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-sm font-semibold">
                                    <span class="text-body">Total Seharusnya</span>
                                    <span class="tabular-nums font-medium text-ink">Rp {{ number_format($totalSeharusnya, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @else
                            <p class="mt-2 text-sm text-mute">Tidak ada transaksi yang perlu disetor.</p>
                        @endif
                    </div>

                    @if($rincianLangsung['belum']['jumlah'] + $rincianLangsung['sesuai']['jumlah'] + $rincianLangsung['bermasalah']['jumlah'] > 0)
                        <div class="mt-3 rounded-lg bg-warning-soft px-3 py-2 text-sm text-warning-deep shadow-[inset_0_0_0_1px_#fef3c7]">
                            <p class="font-medium">Rincian penarikan offline langsung kolektor ini</p>
                            <p class="mt-1 tabular-nums text-xs">
                                belum ditinjau {{ $rincianLangsung['belum']['jumlah'] }} (Rp {{ number_format($rincianLangsung['belum']['nominal'], 0, ',', '.') }})
                                &middot; sesuai {{ $rincianLangsung['sesuai']['jumlah'] }} (Rp {{ number_format($rincianLangsung['sesuai']['nominal'], 0, ',', '.') }})
                                &middot; bermasalah {{ $rincianLangsung['bermasalah']['jumlah'] }} (Rp {{ number_format($rincianLangsung['bermasalah']['nominal'], 0, ',', '.') }})
                            </p>
                        </div>
                    @endif

                    @if($detailTransaksi->count() > 0)
                        <form wire:submit="submit" class="mt-4 space-y-4">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-ink">Total Diterima (Fisik)</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 tabular-nums text-sm text-mute">Rp</span>
                                    <input type="number" wire:model="totalDiterima"
                                        class="h-10 w-full rounded-md border border-hairline bg-white py-0 pl-10 pr-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                                        placeholder="0" />
                                </div>
                                @error('totalDiterima') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>

                            @if($totalDiterima && $totalDiterima != $totalSeharusnya)
                                <div class="rounded-lg {{ $totalDiterima > $totalSeharusnya ? 'bg-warning-soft' : 'bg-danger-soft' }} px-4 py-3">
                                    <p class="text-sm font-medium {{ $totalDiterima > $totalSeharusnya ? 'text-warning-deep' : 'text-danger' }}">
                                        Selisih: Rp {{ number_format(abs($totalDiterima - $totalSeharusnya), 0, ',', '.') }} ({{ $totalDiterima > $totalSeharusnya ? 'Lebih' : 'Kurang' }})
                                    </p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-ink">Keterangan Selisih (Wajib)</label>
                                    <textarea wire:model="keterangan" rows="2"
                                        class="w-full rounded-md border border-hairline bg-white px-3 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
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

    <div class="overflow-hidden rounded-lg bg-white shadow-[inset_0_0_0_1px_#e5e5e5]">
        <div class="border-b border-hairline px-6 py-4">
            <h3 class="text-sm font-semibold text-ink">Riwayat Rekonsiliasi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-inset">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tanggal</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Kolektor</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Seharusnya</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Diterima</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Selisih</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($riwayat as $item)
                        <tr class="transition hover:bg-inset">
                            <td class="px-4 py-3 text-sm text-body">{{ $item->tanggal_setor->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-ink">{{ $item->kolektor->name ?? '-' }}</td>
                            <td class="px-4 py-3 tabular-nums text-sm text-body">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 tabular-nums text-sm text-body">
                                @if($item->total_diterima !== null)
                                    Rp {{ number_format($item->total_diterima, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 tabular-nums text-sm {{ $item->selisih && $item->selisih != 0 ? 'text-danger' : 'text-info' }}">
                                @if($item->selisih !== null)
                                    {{ $item->selisih >= 0 ? '+' : '' }} Rp {{ number_format($item->selisih, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($item->status === 'pending')
                                    <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-0.5 text-xs text-warning-deep">Menunggu</span>
                                @elseif($item->status === 'cocok')
                                    <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Cocok</span>
                                @elseif($item->status === 'lebih')
                                    <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-0.5 text-xs text-warning-deep">Lebih</span>
                                @elseif($item->status === 'dibatalkan')
                                    <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs text-danger">Ditolak/Dibatalkan</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs text-danger">Kurang</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <p class="text-sm text-mute">Belum ada riwayat rekonsiliasi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div data-test="kartu-riwayat" class="divide-y divide-hairline md:hidden">
            @forelse($riwayat as $item)
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink">{{ $item->kolektor->name ?? '-' }}</p>
                            <p class="text-xs text-mute">{{ $item->tanggal_setor->translatedFormat('d M Y') }}</p>
                        </div>
                        @if($item->status === 'pending')
                            <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-0.5 text-xs text-warning-deep">Menunggu</span>
                        @elseif($item->status === 'cocok')
                            <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Cocok</span>
                        @elseif($item->status === 'lebih')
                            <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-0.5 text-xs text-warning-deep">Lebih</span>
                        @elseif($item->status === 'dibatalkan')
                            <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs text-danger">Ditolak/Dibatalkan</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs text-danger">Kurang</span>
                        @endif
                    </div>
                    <div class="mt-2 grid grid-cols-3 gap-x-3 gap-y-1.5">
                        <div>
                            <span class="block text-xs text-mute">Seharusnya</span>
                            <span class="tabular-nums text-sm text-body">Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Diterima</span>
                            <span class="tabular-nums text-sm text-body">
                                @if($item->total_diterima !== null)
                                    Rp {{ number_format($item->total_diterima, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Selisih</span>
                            <span class="tabular-nums text-sm {{ $item->selisih && $item->selisih != 0 ? 'text-danger' : 'text-info' }}">
                                @if($item->selisih !== null)
                                    {{ $item->selisih >= 0 ? '+' : '' }} Rp {{ number_format($item->selisih, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center gap-2 px-4 py-10 text-center">
                    <p class="text-sm text-mute">Belum ada riwayat rekonsiliasi.</p>
                </div>
            @endforelse
        </div>
        <div class="border-t border-hairline px-4 py-3">{{ $riwayat->links() }}</div>
    </div>
</div>
