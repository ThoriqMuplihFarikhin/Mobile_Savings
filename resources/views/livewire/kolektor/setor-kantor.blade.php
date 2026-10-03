<div class="mx-auto max-w-2xl pb-24">
    {{-- Header Navigation Tabs --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-2xl">Setor ke Kantor</h1>
                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Ajukan penyerahan kas fisik ke kantor pusat.</p>
            </div>
        </div>

        {{-- Segmented Tab Switcher --}}
        <div class="mt-4 flex rounded-2xl bg-zinc-100 p-1 dark:bg-zinc-800/80">
            <a href="{{ route('kolektor.setoran.index') }}" wire:navigate
               class="flex-1 rounded-xl py-2 text-center text-xs font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition">
                📥 Input Setoran
            </a>
            <span class="flex-1 rounded-xl bg-white py-2 text-center text-xs font-semibold text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white">
                🏢 Setor ke Kantor
            </span>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if($menungguVerifikasi)
        <div class="mb-5 flex items-center gap-3 rounded-2xl bg-amber-50 px-4 py-3.5 text-xs font-medium text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 shadow-xs">
            <flux:icon.clock class="size-5 shrink-0 text-amber-600 dark:text-amber-400" />
            <span class="flex-1">Pengajuan setoran kas Anda sedang menunggu verifikasi admin.</span>
            <button wire:click="batalkan({{ $pengajuanPending->id }})"
                wire:confirm="Yakin ingin membatalkan pengajuan setoran ini? Seluruh setoran akan kembali belum disetor."
                class="shrink-0 rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-zinc-900 px-3 py-1.5 text-[11px] font-bold text-amber-800 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-950/60 transition">
                Batalkan
            </button>
        </div>
    @endif
    @if (session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-2xl bg-emerald-50 px-4 py-3.5 text-xs font-medium text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 shadow-xs">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600 dark:text-emerald-400" />
            <span class="flex-1">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-5 flex items-center gap-3 rounded-2xl bg-rose-50 px-4 py-3.5 text-xs font-medium text-rose-800 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60 shadow-xs">
            <flux:icon.x-circle class="size-5 shrink-0 text-rose-600 dark:text-rose-400" />
            <span class="flex-1">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Ringkasan Kas --}}
    <div class="mb-6 rounded-2xl bg-white p-5 shadow-sm border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
        <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
            Ringkasan Kas Belum Disetor
        </h3>
        
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl bg-zinc-50 p-4 border border-zinc-200/60 dark:bg-zinc-900/50 dark:border-zinc-700/60">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Total Belum Disetor</p>
                <p class="mt-1 font-mono text-2xl font-extrabold text-zinc-900 dark:text-white">
                    Rp {{ number_format($totalBelumDisetor, 0, ',', '.') }}
                </p>
            </div>
            <div class="rounded-xl bg-zinc-50 p-4 border border-zinc-200/60 dark:bg-zinc-900/50 dark:border-zinc-700/60">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Jumlah Transaksi</p>
                <p class="mt-1 font-mono text-2xl font-extrabold text-zinc-900 dark:text-white">
                    {{ $jumlahTransaksi }} <span class="text-xs font-sans font-normal text-zinc-400">transaksi</span>
                </p>
            </div>
        </div>

        @if($totalBelumDisetor > 0)
            <div class="mt-4">
                <label class="mb-1.5 block text-xs font-medium text-zinc-700 dark:text-zinc-300">Catatan Penyerahan Kas (Opsional)</label>
                <textarea wire:model="catatan" rows="2"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-900/60 px-3 py-2 text-xs text-zinc-900 dark:text-white placeholder-zinc-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/10"
                    placeholder="Contoh: Diserahkan tunai ke kasir Mbak Rina..."></textarea>
                @error('catatan') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
            </div>
            <button wire:click="submit" wire:confirm="Yakin ingin mengajukan setoran kas sebesar Rp {{ number_format($totalBelumDisetor, 0, ',', '.') }} ke kantor?"
                class="mt-4 w-full rounded-xl bg-zinc-900 dark:bg-white py-3 text-xs font-bold text-white dark:text-zinc-900 shadow-md hover:bg-zinc-800 dark:hover:bg-zinc-100 transition">
                🚀 Ajukan Setoran Ke Kantor
            </button>
        @else
            <div class="mt-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 p-3.5 text-center border border-emerald-200 dark:border-emerald-800/60">
                <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">
                    ✨ Semua setoran kas telah diserahkan dan tidak ada kas tertahan.
                </p>
            </div>
        @endif
    </div>

    {{-- Riwayat Setor ke Kantor --}}
    <div class="rounded-2xl bg-white shadow-sm border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80 overflow-hidden">
        <div class="border-b border-zinc-100 dark:border-zinc-700 px-5 py-4 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-white">Riwayat Setor ke Kantor</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/60 text-zinc-400 dark:text-zinc-500 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Seharusnya</th>
                        <th class="px-5 py-3">Diterima</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                    @forelse($riwayat as $item)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/40 transition-colors">
                            <td class="px-5 py-3.5 text-zinc-900 dark:text-white font-medium">
                                {{ $item->tanggal_setor->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-5 py-3.5 font-mono font-bold text-zinc-900 dark:text-white">
                                Rp {{ number_format($item->total_seharusnya, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-zinc-600 dark:text-zinc-300">
                                @if($item->total_diterima !== null)
                                    Rp {{ number_format($item->total_diterima, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($item->status === 'pending')
                                    <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-950/60 px-2.5 py-0.5 text-[11px] font-semibold text-amber-800 dark:text-amber-300">Menunggu</span>
                                @elseif($item->status === 'cocok')
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-950/60 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-800 dark:text-emerald-300">Cocok</span>
                                @elseif($item->status === 'lebih')
                                    <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-950/60 px-2.5 py-0.5 text-[11px] font-semibold text-blue-800 dark:text-blue-300">Lebih</span>
                                @elseif($item->status === 'dibatalkan')
                                    <span class="inline-flex items-center rounded-full bg-rose-100 dark:bg-rose-950/60 px-2.5 py-0.5 text-[11px] font-semibold text-rose-700 dark:text-rose-300">Dibatalkan</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-rose-100 dark:bg-rose-950/60 px-2.5 py-0.5 text-[11px] font-semibold text-rose-800 dark:text-rose-300">Kurang</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-zinc-400 dark:text-zinc-500">
                                Belum ada riwayat penyerahan kas ke kantor.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($riwayat->hasPages())
            <div class="border-t border-zinc-100 dark:border-zinc-700 px-5 py-3">{{ $riwayat->links() }}</div>
        @endif
    </div>
</div>

