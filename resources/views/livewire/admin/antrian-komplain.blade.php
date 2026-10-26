<div class="mx-auto max-w-7xl space-y-6">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Antrian Komplain</h1>
        <p class="mt-1 text-sm text-mute">Tinjau dan selesaikan komplain dari nasabah.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-info-soft px-4 py-3 text-sm text-info">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach(['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $value => $label)
            <button wire:click="$set('statusFilter', '{{ $value }}')"
                class="min-h-11 rounded-md px-4 py-2 text-sm font-medium transition {{ $statusFilter === $value ? 'bg-accent text-accent-foreground' : 'bg-inset text-body shadow-[inset_0_0_0_1px_#e5e5e5] hover:bg-white' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-[inset_0_0_0_1px_#e5e5e5]">
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-inset">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tanggal</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nasabah</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Kategori</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Deskripsi</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($komplains as $item)
                        <tr class="transition hover:bg-inset">
                            <td class="px-4 py-3 text-sm text-body">{{ $item->tanggal_dibuat->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</div>
                                <div class="text-xs text-mute">{{ $item->nasabah->no_hp ?? '— (offline)' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $color = match($item->kategori) {
                                        'saldo' => 'bg-info-soft text-info',
                                        'barang_paket' => 'bg-inset text-body',
                                        'penarikan' => 'bg-warning-soft text-warning-deep',
                                        default => 'bg-inset text-body'
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs {{ $color }}">
                                    {{ ucfirst(str_replace('_', ' ', $item->kategori)) }}
                                </span>
                            </td>
                            <td class="max-w-[200px] truncate px-4 py-3 text-sm text-body">{{ $item->deskripsi }}</td>
                            <td class="px-4 py-3">
                                @if($item->status === 'baru')
                                    <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Baru</span>
                                @elseif($item->status === 'diproses')
                                    <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-0.5 text-xs text-warning-deep">Diproses</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Selesai</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($item->status === 'baru')
                                    <div class="flex items-center gap-2">
                                        <button wire:click="proses({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full bg-accent px-3 py-1.5 text-xs font-medium text-accent-foreground transition hover:opacity-90">Proses</button>
                                        <button wire:click="showDetail({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full border border-hairline bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:bg-inset">Detail</button>
                                    </div>
                                @elseif($item->status === 'diproses')
                                    <button wire:click="showDetail({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full bg-accent px-3 py-1.5 text-xs font-medium text-accent-foreground transition hover:opacity-90">Selesaikan</button>
                                @else
                                    <span class="text-xs text-mute">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                    <p class="text-sm text-mute">Tidak ada komplain.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($komplains as $item)
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</p>
                            <p class="text-xs text-mute">{{ $item->nasabah->no_hp ?? '— (offline)' }}</p>
                        </div>
                        @if($item->status === 'baru')
                            <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Baru</span>
                        @elseif($item->status === 'diproses')
                            <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-0.5 text-xs text-warning-deep">Diproses</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Selesai</span>
                        @endif
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                        <div>
                            <span class="block text-xs text-mute">Tanggal</span>
                            <span class="tabular-nums text-sm text-body">{{ $item->tanggal_dibuat->translatedFormat('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Kategori</span>
                            <span class="tabular-nums text-sm text-body">
                                @php
                                    $color = match($item->kategori) {
                                        'saldo' => 'bg-info-soft text-info',
                                        'barang_paket' => 'bg-inset text-body',
                                        'penarikan' => 'bg-warning-soft text-warning-deep',
                                        default => 'bg-inset text-body'
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs {{ $color }}">
                                    {{ ucfirst(str_replace('_', ' ', $item->kategori)) }}
                                </span>
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Deskripsi</span>
                            <span class="tabular-nums text-sm text-body">{{ $item->deskripsi }}</span>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @if($item->status === 'baru')
                            <div class="flex items-center gap-2">
                                <button wire:click="proses({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full bg-accent px-3 py-1.5 text-xs font-medium text-accent-foreground transition hover:opacity-90">Proses</button>
                                <button wire:click="showDetail({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full border border-hairline bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:bg-inset">Detail</button>
                            </div>
                        @elseif($item->status === 'diproses')
                            <button wire:click="showDetail({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full bg-accent px-3 py-1.5 text-xs font-medium text-accent-foreground transition hover:opacity-90">Selesaikan</button>
                        @else
                            <span class="text-xs text-mute">-</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <p class="text-sm text-mute">Tidak ada komplain.</p>
                </div>
            @endforelse
        </div>
        <div class="border-t border-hairline px-4 py-3">{{ $komplains->links() }}</div>
    </div>

    @if($tampilDetail && $selectedKomplain)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-lg rounded-lg bg-white p-6 shadow-[0_8px_24px_rgb(0_0_0/0.12)]">
                <h3 class="text-lg font-semibold text-ink">Detail Komplain</h3>

                <div class="mt-4 space-y-3 rounded-lg bg-inset p-4 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <div class="flex justify-between text-sm">
                        <span class="text-mute">Nasabah</span>
                        <span class="font-medium text-ink">{{ $selectedKomplain->nasabah->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-mute">Kategori</span>
                        <span class="text-body">{{ ucfirst(str_replace('_', ' ', $selectedKomplain->kategori)) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-mute">Tanggal</span>
                        <span class="text-body">{{ $selectedKomplain->tanggal_dibuat->translatedFormat('d M Y H:i') }}</span>
                    </div>
                    @if($selectedKomplain->transaksiTerkait)
                        <div class="border-t border-hairline pt-3">
                            <span class="text-sm font-medium text-ink">Transaksi Terkait</span>
                            <div class="mt-2 space-y-1 rounded-md bg-white p-3 shadow-[inset_0_0_0_1px_#e5e5e5]">
                                <div class="flex justify-between text-sm">
                                    <span class="text-mute">Tanggal Transaksi</span>
                                    <span class="text-body">{{ $selectedKomplain->transaksiTerkait->tanggal_transaksi->translatedFormat('d M Y') }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-mute">Produk</span>
                                    <span class="text-body">{{ $selectedKomplain->transaksiTerkait->produk->nama ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-mute">Nominal</span>
                                    <span class="text-body">Rp {{ number_format($selectedKomplain->transaksiTerkait->nominal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="border-t border-hairline pt-3">
                        <span class="text-sm text-mute">Deskripsi</span>
                        <p class="mt-1 text-sm text-body">{{ $selectedKomplain->deskripsi }}</p>
                    </div>
                </div>

                <form wire:submit="selesai" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Catatan Penyelesaian</label>
                        <textarea wire:model="catatan" rows="3"
                            class="w-full rounded-md border border-hairline bg-white px-3 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                            placeholder="Jelaskan tindakan yang dilakukan..."></textarea>
                        @error('catatan') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('tampilDetail', false)"
                            class="min-h-11 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
                            Batal
                        </button>
                        <button type="submit"
                            class="min-h-11 rounded-md bg-accent px-4 py-2 text-sm font-medium text-accent-foreground transition hover:opacity-90">
                            Selesaikan Komplain
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>