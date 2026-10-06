<div class="mx-auto max-w-2xl">
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Verifikasi Penarikan</h1>
            <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Serahkan uang dan verifikasi dengan PIN nasabah.</p>
        </div>
        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 p-4 text-sm text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-3 rounded-2xl bg-rose-50 dark:bg-rose-950/40 p-4 text-sm text-rose-700 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/40">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Info Banner --}}
    <div class="mb-4 flex items-start gap-3 rounded-2xl bg-blue-50 dark:bg-blue-950/40 p-4 text-sm text-blue-700 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/40">
        <svg class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
        <span class="font-medium">Serahkan HP ke nasabah agar PIN diketik sendiri. Verifikasi ini membuktikan nasabah telah menerima uang.</span>
    </div>

    {{-- Card List --}}
    <div class="space-y-3">
        @forelse($penarikan as $item)
            <div class="rounded-3xl bg-white dark:bg-zinc-800 shadow-xl shadow-zinc-200/50 dark:shadow-none border border-zinc-100 dark:border-zinc-700/60 overflow-hidden">
                <div class="p-5">
                    <div class="mb-3 flex items-start justify-between">
                        <div>
                            <p class="text-sm font-bold text-zinc-900 dark:text-white">{{ $item->nasabah->name ?? '-' }}</p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $item->nasabah->no_hp ?? '— (offline)' }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/30 px-2.5 py-0.5 font-mono text-xs text-amber-700 dark:text-amber-400">Approved</span>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Produk</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $item->produk->nama ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Nominal Diserahkan</span>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($item->nominal_diterima, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Lokasi</span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Diajukan</span>
                            <span class="text-zinc-600 dark:text-zinc-300">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button wire:click="bukaModal({{ $item->id }})"
                            class="w-full rounded-2xl bg-emerald-500 hover:bg-emerald-600 py-3 text-center text-sm font-bold text-white shadow-lg shadow-emerald-500/25 transition">
                            Serahkan &amp; Verifikasi PIN
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-3xl bg-white dark:bg-zinc-800 shadow-xl shadow-zinc-200/50 dark:shadow-none border border-zinc-100 dark:border-zinc-700/60 p-12">
                <div class="flex flex-col items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Tidak ada penarikan yang perlu diverifikasi.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($penarikan->hasPages())
        <div class="mt-4">{{ $penarikan->links() }}</div>
    @endif

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 dark:bg-black/70" wire:click.self="tutupModal"></div>

            <div class="relative w-full max-w-sm rounded-3xl bg-white dark:bg-zinc-800 shadow-2xl border border-zinc-100 dark:border-zinc-700/60 overflow-hidden">
                <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 p-5 text-center">
                    <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                    </div>
                    <p class="text-sm font-bold text-white">Verifikasi PIN Nasabah</p>
                    <p class="text-xs text-emerald-100 mt-0.5">Minta nasabah mengetik PIN-nya di bawah ini.</p>
                </div>

                <form wire:submit="konfirmasi" class="p-6 space-y-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 text-center">PIN Nasabah (6 digit)</label>
                        <input type="password" inputmode="numeric" maxlength="6" wire:model="pin"
                            class="w-full rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 py-4 text-center font-mono text-2xl font-bold tracking-[0.5em] text-zinc-900 dark:text-white focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                            placeholder="------" autocomplete="off" />
                        @error('pin')
                            <p class="mt-2 text-xs text-rose-500 font-medium text-center">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" wire:click="tutupModal"
                            class="w-1/3 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-3.5 text-center text-sm font-bold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="w-2/3 rounded-2xl bg-emerald-500 hover:bg-emerald-600 py-3.5 text-center text-sm font-bold text-white shadow-lg shadow-emerald-500/25 transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="konfirmasi">Verifikasi</span>
                            <span wire:loading wire:target="konfirmasi">Memverifikasi...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
