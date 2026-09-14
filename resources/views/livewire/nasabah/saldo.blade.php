<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Saldo Saya</h1>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Detail saldo per produk tabungan aktif Anda.</p>
        </div>
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 shadow-2xs border border-indigo-100 dark:border-indigo-900/50">
            <flux:icon.wallet class="size-5" />
        </div>
    </div>

    {{-- Total Saldo Hero Card --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900 via-indigo-800 to-zinc-900 p-6 text-white shadow-xl border border-indigo-700/30 dark:from-zinc-950 dark:to-zinc-900 dark:border-zinc-800">
        <div class="absolute -right-10 -top-10 h-44 w-44 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-200">Total Akumulasi Saldo</span>
                <span class="rounded-full bg-white/15 backdrop-blur-md px-2.5 py-0.5 text-[10px] font-semibold text-white border border-white/15">
                    Aktif
                </span>
            </div>
            <p class="mt-3 font-mono text-4xl font-extrabold tracking-tight text-white">
                Rp {{ number_format($totalSaldo, 0, ',', '.') }}
            </p>
            <p class="mt-1.5 text-xs text-indigo-200/80">Dari seluruh produk tabungan aktif Anda.</p>

            {{-- Quick Action Buttons --}}
            <div class="mt-6 grid grid-cols-2 gap-3">
                <a href="{{ route('nasabah.riwayat-tabungan.index') }}" wire:navigate
                    class="flex items-center justify-center gap-2 rounded-2xl bg-white/10 hover:bg-white/20 active:scale-95 border border-white/20 py-3 text-center text-xs font-bold text-white transition backdrop-blur-md">
                    <flux:icon.clock class="size-4 text-white" />
                    Riwayat Setoran
                </a>
                <a href="{{ route('nasabah.penarikan.index') }}" wire:navigate
                    class="flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 hover:bg-indigo-500 active:scale-95 py-3 text-center text-xs font-bold text-white transition shadow-lg shadow-indigo-900/50">
                    <flux:icon.arrow-up-tray class="size-4 text-white" />
                    Ajukan Penarikan
                </a>
            </div>
        </div>
    </div>

    {{-- Per-Product Cards --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Rincian Tabungan</span>
            <span class="text-xs text-zinc-400 font-medium">{{ count($saldo) }} Produk</span>
        </div>

        @forelse($saldo as $item)
            <div class="rounded-3xl bg-white dark:bg-zinc-800/90 p-4.5 shadow-xs border border-zinc-200/80 dark:border-zinc-700/80 flex items-center gap-4 transition hover:border-zinc-300 dark:hover:border-zinc-600">
                {{-- Icon --}}
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl
                    {{ $item->produk->isPaket() ? 'bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-900/40' : 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/40' }}">
                    @if($item->produk->isPaket())
                        <flux:icon.shopping-bag class="size-5" />
                    @else
                        <flux:icon.banknotes class="size-5" />
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-semibold
                            {{ $item->produk->isPaket() ? 'bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/50' : 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/50' }}">
                            {{ $item->produk->tipe === 'paket' ? 'Paket' : 'Bebas' }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs font-bold text-zinc-900 dark:text-white truncate">{{ $item->produk->nama }}</p>
                    @if($item->produk->isPaket() && $item->produk->tanggal_boleh_cair)
                        <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-0.5">
                            Cair: {{ \Carbon\Carbon::parse($item->produk->tanggal_boleh_cair)->translatedFormat('d M Y') }}
                        </p>
                    @endif
                </div>

                <div class="text-right shrink-0">
                    <p class="font-mono text-base font-bold text-zinc-900 dark:text-white">
                        Rp {{ number_format($item->saldo, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        @empty
            <div class="rounded-3xl bg-white dark:bg-zinc-800 p-10 text-center border border-zinc-200/80 dark:border-zinc-700/80 shadow-xs">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-700/60 text-zinc-400">
                    <flux:icon.wallet class="size-7 text-zinc-400" />
                </div>
                <h3 class="mt-4 text-sm font-bold text-zinc-900 dark:text-white">Belum Ada Tabungan Aktif</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Hubungi kolektor Anda untuk mulai menabung.</p>
            </div>
        @endforelse
    </div>
</div>

