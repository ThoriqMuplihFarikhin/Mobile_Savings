<div>
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Catat Setoran</h1>
            <p class="mt-1 text-sm text-mute">Catat setoran nasabah secara langsung dari kantor (FR-5).</p>
        </div>
        <a href="/admin/monitoring-setoran" wire:navigate
            class="rounded-md border border-hairline bg-white px-4 py-2 text-xs font-medium text-ink transition hover:bg-inset">
            Monitoring Setoran
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-info-soft px-4 py-3 text-sm text-info">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-lg bg-white shadow-[inset_0_0_0_1px_#e5e5e5]">
                <div class="border-b border-hairline px-6 py-4">
                    <h3 class="text-sm font-semibold text-ink">Form Setoran</h3>
                    <p class="mt-0.5 text-xs text-mute">Uang diterima langsung oleh kantor, sehingga tidak menambah kas kolektor mana pun.</p>
                </div>

                <form wire:submit="submit" class="space-y-5 p-6">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Nasabah</label>

                        @if ($selectedNasabah)
                            <div class="flex items-center justify-between gap-3 rounded-lg border border-hairline bg-inset px-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $selectedNasabah['nama'] }}</p>
                                    <p class="truncate text-xs text-mute">{{ $selectedNasabah['alamat'] }}</p>
                                </div>
                                <button type="button" wire:click="gantiNasabah"
                                    class="shrink-0 text-xs font-medium text-info hover:text-ink">
                                    Ganti
                                </button>
                            </div>
                        @else
                            <input type="text" wire:model.live.debounce.300ms="searchNasabah"
                                placeholder="Ketik nama nasabah..."
                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink placeholder:text-subtle focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />

                            <div class="mt-2 max-h-56 overflow-y-auto rounded-lg border border-hairline divide-y divide-hairline">
                                @forelse ($nasabahList as $n)
                                    <button type="button" wire:click="pilihNasabah({{ $n['id'] }})"
                                        class="flex w-full items-center justify-between gap-3 px-4 py-2.5 text-left transition hover:bg-inset">
                                        <span class="truncate text-sm text-ink">{{ $n['nama'] }}</span>
                                        <span class="truncate text-xs text-mute">{{ $n['alamat'] }}</span>
                                    </button>
                                @empty
                                    <p class="px-4 py-6 text-center text-sm text-mute">Nasabah tidak ditemukan.</p>
                                @endforelse
                            </div>
                        @endif

                        @error('nasabahId') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Produk</label>
                            <select wire:model.live="produkId"
                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
                                <option value="">Pilih produk</option>
                                @foreach ($produkList as $p)
                                    <option value="{{ $p['id'] }}">{{ $p['nama'] }}</option>
                                @endforeach
                            </select>
                            @error('produkId') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Nominal (Rp)</label>
                            <input type="number" wire:model="nominal" min="0" step="1"
                                placeholder="0"
                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink placeholder:text-subtle focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                            @error('nominal') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Tanggal Transaksi</label>
                            <div class="flex h-10 items-center rounded-md border border-hairline bg-inset px-3 text-sm text-ink">
                                {{ \Illuminate\Support\Carbon::parse($tanggal_transaksi)->translatedFormat('d F Y') }}
                            </div>
                            <p class="mt-1.5 text-xs text-mute">Setoran admin dicatat pada tanggal hari ini.</p>
                            @error('tanggal_transaksi') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Catatan</label>
                            <textarea wire:model="catatan" rows="2" placeholder="Opsional"
                                class="w-full rounded-md border border-hairline bg-white px-3 py-2 text-sm text-ink placeholder:text-subtle focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"></textarea>
                            @error('catatan') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-hairline pt-4">
                        <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                            class="rounded-md bg-ink px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-black disabled:opacity-60">
                            <span wire:loading.remove wire:target="submit">Catat Setoran</span>
                            <span wire:loading wire:target="submit">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div>
            <div class="overflow-hidden rounded-lg bg-white shadow-[inset_0_0_0_1px_#e5e5e5]">
                <div class="border-b border-hairline px-6 py-4">
                    <h3 class="text-sm font-semibold text-ink">Setoran Hari Ini</h3>
                    <p class="mt-0.5 text-xs text-mute">Total Rp {{ number_format($totalHariIni, 0, ',', '.') }}</p>
                </div>
                <div class="divide-y divide-hairline">
                    @forelse ($riwayatHariIni as $riwayat)
                        <div class="px-6 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="truncate text-sm font-medium text-ink">{{ $riwayat->nasabah?->name ?? '-' }}</p>
                                <p class="shrink-0 tabular-nums text-sm text-ink">Rp {{ number_format((float) $riwayat->nominal, 0, ',', '.') }}</p>
                            </div>
                            <div class="mt-0.5 flex items-center justify-between gap-3">
                                <p class="truncate text-xs text-mute">{{ $riwayat->produk?->nama ?? '-' }}</p>
                                <p class="shrink-0 tabular-nums text-xs text-subtle">{{ $riwayat->created_at?->format('H:i') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-center text-sm text-mute">Belum ada setoran yang dicatat hari ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
