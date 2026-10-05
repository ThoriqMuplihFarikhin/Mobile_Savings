<div>
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Catat Setoran</h1>
            <p class="mt-1 text-sm text-gray-500">Catat setoran nasabah secara langsung dari kantor (FR-5).</p>
        </div>
        <a href="/admin/monitoring-setoran" wire:navigate
            class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-xs font-medium text-gray-900 transition hover:bg-gray-50">
            Monitoring Setoran
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-indigo-100 px-4 py-3 text-sm text-indigo-600">
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
            <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <div class="border-b border-[#ebebeb] px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">Form Setoran</h3>
                    <p class="mt-0.5 text-xs text-gray-500">Uang diterima langsung oleh kantor, sehingga tidak menambah kas kolektor mana pun.</p>
                </div>

                <form wire:submit="submit" class="space-y-5 p-6">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-900">Nasabah</label>

                        @if ($selectedNasabah)
                            <div class="flex items-center justify-between gap-3 rounded-lg border border-[#ebebeb] bg-gray-50 px-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-900">{{ $selectedNasabah['nama'] }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ $selectedNasabah['alamat'] }}</p>
                                </div>
                                <button type="button" wire:click="gantiNasabah"
                                    class="shrink-0 text-xs font-medium text-indigo-600 hover:text-indigo-500">
                                    Ganti
                                </button>
                            </div>
                        @else
                            <input type="text" wire:model.live.debounce.300ms="searchNasabah"
                                placeholder="Ketik nama nasabah..."
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 placeholder-gray-400 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />

                            <div class="mt-2 max-h-56 overflow-y-auto rounded-lg border border-[#ebebeb] divide-y divide-[#ebebeb]">
                                @forelse ($nasabahList as $n)
                                    <button type="button" wire:click="pilihNasabah({{ $n['id'] }})"
                                        class="flex w-full items-center justify-between gap-3 px-4 py-2.5 text-left transition hover:bg-gray-50">
                                        <span class="truncate text-sm text-gray-900">{{ $n['nama'] }}</span>
                                        <span class="truncate text-xs text-gray-500">{{ $n['alamat'] }}</span>
                                    </button>
                                @empty
                                    <p class="px-4 py-6 text-center text-sm text-gray-500">Nasabah tidak ditemukan.</p>
                                @endforelse
                            </div>
                        @endif

                        @error('nasabahId') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Produk</label>
                            <select wire:model.live="produkId"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                                <option value="">Pilih produk</option>
                                @foreach ($produkList as $p)
                                    <option value="{{ $p['id'] }}">{{ $p['nama'] }}</option>
                                @endforeach
                            </select>
                            @error('produkId') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Nominal (Rp)</label>
                            <input type="number" wire:model="nominal" min="0" step="1"
                                placeholder="0"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 placeholder-gray-400 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                            @error('nominal') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Tanggal Transaksi</label>
                            <div class="flex h-10 items-center rounded-md border border-[#ebebeb] bg-gray-50 px-3 text-sm text-gray-900">
                                {{ \Illuminate\Support\Carbon::parse($tanggal_transaksi)->translatedFormat('d F Y') }}
                            </div>
                            <p class="mt-1.5 text-xs text-gray-500">Setoran admin dicatat pada tanggal hari ini.</p>
                            @error('tanggal_transaksi') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Catatan</label>
                            <textarea wire:model="catatan" rows="2" placeholder="Opsional"
                                class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"></textarea>
                            @error('catatan') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-[#ebebeb] pt-4">
                        <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                            class="rounded-full bg-[#171717] px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-black disabled:opacity-60">
                            <span wire:loading.remove wire:target="submit">Catat Setoran</span>
                            <span wire:loading wire:target="submit">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div>
            <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <div class="border-b border-[#ebebeb] px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">Setoran Hari Ini</h3>
                    <p class="mt-0.5 text-xs text-gray-500">Total Rp {{ number_format($totalHariIni, 0, ',', '.') }}</p>
                </div>
                <div class="divide-y divide-[#ebebeb]">
                    @forelse ($riwayatHariIni as $riwayat)
                        <div class="px-6 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="truncate text-sm font-medium text-gray-900">{{ $riwayat->nasabah?->name ?? '-' }}</p>
                                <p class="shrink-0 font-mono text-sm text-gray-900">Rp {{ number_format((float) $riwayat->nominal, 0, ',', '.') }}</p>
                            </div>
                            <div class="mt-0.5 flex items-center justify-between gap-3">
                                <p class="truncate text-xs text-gray-500">{{ $riwayat->produk?->nama ?? '-' }}</p>
                                <p class="shrink-0 font-mono text-xs text-gray-400">{{ $riwayat->created_at?->format('H:i') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-center text-sm text-gray-500">Belum ada setoran yang dicatat hari ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
