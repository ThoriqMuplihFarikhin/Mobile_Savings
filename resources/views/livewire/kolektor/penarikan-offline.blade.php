<div class="mx-auto max-w-2xl space-y-5">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Penarikan Offline</h1>
            <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Proses permintaan penarikan tabungan nasabah secara langsung.</p>
        </div>
        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 p-4 text-sm text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 rounded-2xl bg-rose-50 dark:bg-rose-950/40 p-4 text-sm text-rose-700 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/40">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Card Verifikasi Penarikan --}}
    <a href="{{ route('kolektor.verifikasi-penarikan.index') }}" wire:navigate
        class="flex items-center justify-between rounded-3xl bg-white dark:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-700/80 p-4.5 shadow-sm hover:border-emerald-300 dark:hover:border-emerald-700 transition group">
        <div class="flex items-center gap-3.5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50 group-hover:scale-105 transition-transform">
                <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <p class="text-sm font-bold text-zinc-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition">Verifikasi penarikan</p>
                    @if ($jumlahMenungguVerifikasi > 0)
                        <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-emerald-500 px-1.5 text-[11px] font-bold text-white">
                            {{ $jumlahMenungguVerifikasi }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Serahkan uang dan verifikasi pin nasabah</p>
            </div>
        </div>
        <svg class="h-5 w-5 text-zinc-400 dark:text-zinc-500 group-hover:translate-x-0.5 group-hover:text-zinc-700 dark:group-hover:text-zinc-300 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
        </svg>
    </a>

    {{-- Form Ajukan Penarikan Baru --}}
    <div class="rounded-3xl bg-white dark:bg-zinc-800 shadow-sm border border-zinc-200/80 dark:border-zinc-700/80 p-6 space-y-5">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-white">Ajukan penarikan baru</h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Isi formulir di bawah ini untuk memproses penarikan tabungan.</p>
        </div>

        <form wire:submit="submit" class="space-y-5">
            {{-- Pilih Nasabah --}}
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Nasabah</label>
                <select wire:model.live="nasabahId"
                    class="w-full rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 px-4 py-3 text-sm font-medium text-zinc-900 dark:text-white focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition">
                    <option value="">-- Pilih Nasabah --</option>
                    @foreach($nasabahList as $nasabah)
                        <option value="{{ $nasabah->user_id }}">{{ $nasabah->nama }} ({{ $nasabah->user->no_hp }})</option>
                    @endforeach
                </select>
                @error('nasabahId') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
            </div>

            {{-- Saldo Saat Ini --}}
            @if($selectedNasabah)
                <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-900/60 p-4 border border-zinc-200/80 dark:border-zinc-700/80">
                    <p class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2">Saldo Nasabah</p>
                    @if($selectedNasabah->user->saldoProduks->count() > 0)
                        <div class="space-y-2">
                            @foreach($selectedNasabah->user->saldoProduks as $s)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $s->produk->nama }}</span>
                                    <span class="font-mono text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($s->saldo, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-zinc-400">Nasabah belum memiliki saldo.</p>
                    @endif
                </div>
            @endif

            {{-- Produk --}}
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Produk Tabungan</label>
                <select wire:model.live="produkId"
                    class="w-full rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 px-4 py-3 text-sm font-medium text-zinc-900 dark:text-white focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($produkList as $produk)
                        <option value="{{ $produk->id }}">{{ $produk->nama }} ({{ $produk->tipe === 'paket' ? 'Paket' : 'Bebas' }}){{ $produk->status !== 'aktif' ? ' (nonaktif)' : '' }}</option>
                    @endforeach
                </select>
                @error('produkId') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
            </div>

            {{-- Nominal --}}
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Nominal Penarikan</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 font-mono text-base font-semibold text-zinc-400">Rp</span>
                    <input type="number" wire:model.live="nominal" min="0" step="0.01"
                        class="w-full rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 py-3.5 pl-12 pr-4 text-lg font-mono font-bold text-zinc-900 dark:text-white focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition"
                        placeholder="0" />
                </div>
                @error('nominal') <p class="mt-1.5 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
            </div>

            {{-- Rincian Penarikan --}}
            @if($selectedNasabah && $nominal > 0)
                <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-900/80 p-4 border border-zinc-200/80 dark:border-zinc-700/80 space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">Nominal ditarik</span>
                        <span class="font-mono font-medium text-zinc-900 dark:text-white">Rp {{ number_format($nominal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">Komisi ({{ $persenKomisi }}%)</span>
                        <span class="font-mono text-rose-500 font-medium">−Rp {{ number_format($nominalKomisi, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-zinc-200 dark:border-zinc-800 pt-2">
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">Nasabah Terima</span>
                        <span class="font-mono text-base font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($nominalDiterima, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endif

            {{-- Lokasi --}}
            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Lokasi Pengambilan</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex flex-col items-center justify-center p-3.5 rounded-2xl border cursor-pointer transition text-center
                        {{ ($lokasi ?? 'kantor') === 'kantor' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 font-bold' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400' }}">
                        <input type="radio" wire:model.live="lokasi" value="kantor" class="sr-only">
                        <span class="text-sm">Kantor Utama</span>
                    </label>
                    <label class="flex flex-col items-center justify-center p-3.5 rounded-2xl border cursor-pointer transition text-center
                        {{ ($lokasi ?? '') === 'rumah_kolektor' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 font-bold' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400' }}">
                        <input type="radio" wire:model.live="lokasi" value="rumah_kolektor" class="sr-only">
                        <span class="text-sm">Rumah Kolektor</span>
                    </label>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="w-1/3 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-3.5 text-center text-sm font-bold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition">
                    Batal
                </a>
                <button type="submit" wire:loading.attr="disabled"
                    class="w-2/3 rounded-2xl bg-amber-500 hover:bg-amber-600 py-3.5 text-center text-sm font-bold text-white shadow-md shadow-amber-500/20 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="submit">Proses Penarikan</span>
                    <span wire:loading wire:target="submit">Memproses...</span>
                </button>
            </div>
        </form>
    </div>
</div>