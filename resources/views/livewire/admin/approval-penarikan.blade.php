<div class="mx-auto max-w-7xl space-y-6">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Approval Penarikan</h1>
        <p class="mt-1 text-sm text-mute">Setujui atau tolak pengajuan penarikan nasabah.</p>
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

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach(['pending' => 'Pending', 'approved' => 'Disetujui', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan', 'kedaluwarsa' => 'Kedaluwarsa'] as $value => $label)
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
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nasabah</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Produk</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Diminta</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Diterima</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Lokasi</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($penarikan as $item)
                        <tr class="transition hover:bg-inset">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</div>
                                <div class="text-xs text-mute">{{ $item->nasabah->no_hp ?? '— (offline)' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-body">{{ $item->produk->nama ?? '-' }}</td>
                            <td class="px-4 py-3 tabular-nums text-sm font-medium text-ink">Rp {{ number_format($item->nominal_diminta, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 tabular-nums text-sm text-info">Rp {{ number_format($item->nominal_diterima, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-body">{{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}</td>
                            <td class="px-4 py-3">
                                @if($item->status === 'pending')
                                    <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-0.5 text-xs text-warning-deep">Pending</span>
                                @elseif($item->status === 'approved')
                                    <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Disetujui</span>
                                @elseif($item->status === 'selesai')
                                    <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Selesai</span>
                                @elseif($item->status === 'dibatalkan')
                                    <span class="inline-flex items-center rounded-full bg-inset px-2.5 py-0.5 text-xs text-body">Dibatalkan</span>
                                @elseif($item->status === 'kedaluwarsa')
                                    <span class="inline-flex items-center rounded-full bg-inset px-2.5 py-0.5 text-xs text-body">Kedaluwarsa</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs text-danger">Ditolak</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($item->bukti_path)
                                    <a href="{{ route('penarikan.bukti', $item) }}" target="_blank" rel="noopener"
                                        class="mb-1.5 inline-flex min-h-11 items-center gap-1 rounded-md border border-hairline bg-white px-3 py-1.5 text-xs font-medium text-info transition hover:bg-inset">Lihat bukti</a>
                                @endif
                                @if($item->status === 'pending')
                                    <div class="flex items-center gap-2">
                                        @if($item->disetujui_oleh === null || (int) $item->disetujui_oleh !== (int) auth()->id())
                                            <button wire:click="approve({{ $item->id }})" wire:confirm="Yakin menyetujui penarikan ini? Saldo nasabah akan dikurangi dan penarikan diproses." class="min-h-11 inline-flex items-center gap-1 rounded-full bg-accent px-3 py-1.5 text-xs font-medium text-accent-foreground transition hover:opacity-90">Setuju</button>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-1 text-xs text-warning-deep">Menunggu persetujuan admin kedua</span>
                                        @endif
                                        <button wire:click="reject({{ $item->id }})" wire:confirm="Yakin menolak pengajuan penarikan ini?" class="min-h-11 inline-flex items-center gap-1 rounded-full border border-hairline bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:bg-inset">Tolak</button>
                                    </div>
                                    @if($item->disetujui_oleh !== null && (int) $item->disetujui_oleh !== (int) auth()->id())
                                        <div class="mt-1 text-xs text-mute">Disetujui pertama oleh {{ $item->disetujuiOleh->name ?? '-' }}</div>
                                    @endif
                                @elseif($item->status === 'approved')
                                    @if($item->disetujui_oleh_2 !== null && in_array((int) auth()->id(), array_map('intval', [$item->disetujui_oleh, $item->disetujui_oleh_2]), true))
                                        <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-1 text-xs text-warning-deep">Menunggu admin lain menyelesaikan</span>
                                    @elseif($item->jalur_pengajuan === 'offline' && $item->lokasi_pengambilan === 'rumah_kolektor')
                                        <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-1 text-xs text-warning-deep">Menunggu verifikasi kolektor</span>
                                    @else
                                        <button wire:click="toggleSelesai({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full bg-accent px-3 py-1.5 text-xs font-medium text-accent-foreground transition hover:opacity-90">Tandai Selesai</button>
                                    @endif
                                @elseif($item->status === 'selesai' && $item->metode_verifikasi === 'pin_nasabah')
                                    <span class="text-xs text-primary font-medium">Diverifikasi PIN nasabah</span>
                                @else
                                    <span class="text-xs text-mute">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    <p class="text-sm text-mute">Tidak ada data penarikan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($penarikan as $item)
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</p>
                            <p class="text-xs text-mute">{{ $item->nasabah->no_hp ?? '— (offline)' }}</p>
                        </div>
                        @if($item->status === 'pending')
                            <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-0.5 text-xs text-warning-deep">Pending</span>
                        @elseif($item->status === 'approved')
                            <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Disetujui</span>
                        @elseif($item->status === 'selesai')
                            <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Selesai</span>
                        @elseif($item->status === 'dibatalkan')
                            <span class="inline-flex items-center rounded-full bg-inset px-2.5 py-0.5 text-xs text-body">Dibatalkan</span>
                        @elseif($item->status === 'kedaluwarsa')
                            <span class="inline-flex items-center rounded-full bg-inset px-2.5 py-0.5 text-xs text-body">Kedaluwarsa</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs text-danger">Ditolak</span>
                        @endif
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                        <div>
                            <span class="block text-xs text-mute">Produk</span>
                            <span class="tabular-nums text-sm text-body">{{ $item->produk->nama ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Diminta</span>
                            <span class="tabular-nums text-sm font-semibold text-ink">Rp {{ number_format($item->nominal_diminta, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Diterima</span>
                            <span class="tabular-nums text-sm font-semibold text-ink">Rp {{ number_format($item->nominal_diterima, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Lokasi</span>
                            <span class="tabular-nums text-sm text-body">{{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}</span>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @if($item->bukti_path)
                            <a href="{{ route('penarikan.bukti', $item) }}" target="_blank" rel="noopener"
                                class="inline-flex min-h-11 items-center gap-1 rounded-md border border-hairline bg-white px-3 py-1.5 text-xs font-medium text-info transition hover:bg-inset">Lihat bukti</a>
                        @endif
                        @if($item->status === 'pending')
                            <div class="flex items-center gap-2">
                                @if($item->disetujui_oleh === null || (int) $item->disetujui_oleh !== (int) auth()->id())
                                    <button wire:click="approve({{ $item->id }})" wire:confirm="Yakin menyetujui penarikan ini? Saldo nasabah akan dikurangi dan penarikan diproses." class="min-h-11 inline-flex items-center gap-1 rounded-full bg-accent px-3 py-1.5 text-xs font-medium text-accent-foreground transition hover:opacity-90">Setuju</button>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-1 text-xs text-warning-deep">Menunggu persetujuan admin kedua</span>
                                @endif
                                <button wire:click="reject({{ $item->id }})" wire:confirm="Yakin menolak pengajuan penarikan ini?" class="min-h-11 inline-flex items-center gap-1 rounded-full border border-hairline bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:bg-inset">Tolak</button>
                            </div>
                            @if($item->disetujui_oleh !== null && (int) $item->disetujui_oleh !== (int) auth()->id())
                                <div class="text-xs text-mute">Disetujui pertama oleh {{ $item->disetujuiOleh->name ?? '-' }}</div>
                            @endif
                        @elseif($item->status === 'approved')
                            @if($item->disetujui_oleh_2 !== null && in_array((int) auth()->id(), array_map('intval', [$item->disetujui_oleh, $item->disetujui_oleh_2]), true))
                                <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-1 text-xs text-warning-deep">Menunggu admin lain menyelesaikan</span>
                            @elseif($item->jalur_pengajuan === 'offline' && $item->lokasi_pengambilan === 'rumah_kolektor')
                                <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-1 text-xs text-warning-deep">Menunggu verifikasi kolektor</span>
                            @else
                                <button wire:click="toggleSelesai({{ $item->id }})" class="min-h-11 inline-flex items-center gap-1 rounded-full bg-accent px-3 py-1.5 text-xs font-medium text-accent-foreground transition hover:opacity-90">Tandai Selesai</button>
                            @endif
                        @elseif($item->status === 'selesai' && $item->metode_verifikasi === 'pin_nasabah')
                            <span class="text-xs text-primary font-medium">Diverifikasi PIN nasabah</span>
                        @else
                            <span class="text-xs text-mute">-</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <p class="text-sm text-mute">Tidak ada data penarikan.</p>
                </div>
            @endforelse
        </div>
        <div class="border-t border-hairline px-4 py-3">{{ $penarikan->links() }}</div>
    </div>

    @if($showSelesai)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-[0_8px_24px_rgb(0_0_0/0.12)]">
                <h3 class="text-lg font-semibold text-ink">Tandai Penarikan Selesai</h3>
                <p class="mt-1 text-sm text-mute">Pencairan manual admin wajib mencatat alasan.</p>
                <form wire:submit="selesai({{ $selesaiId }})" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Alasan</label>
                        <textarea wire:model="alasan" rows="3"
                            class="w-full rounded-md border border-hairline bg-white px-3 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                            placeholder="Alasan pencairan manual..."></textarea>
                        @error('alasan') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showSelesai', false)"
                            class="min-h-11 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="selesai"
                            class="min-h-11 rounded-md bg-accent px-4 py-2 text-sm font-medium text-accent-foreground transition hover:opacity-90 disabled:opacity-50">
                            Tandai Selesai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>