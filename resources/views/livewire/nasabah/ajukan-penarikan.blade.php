<div class="mx-auto max-w-2xl space-y-6">
    {{-- Header Page --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Ajukan Penarikan</h1>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Tarik saldo tabungan Anda dengan mudah dan fleksibel.</p>
        </div>
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 shadow-2xs border border-indigo-100 dark:border-indigo-900/50">
            <flux:icon.arrow-up-tray class="size-5" />
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 p-4 text-xs font-semibold text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600 dark:text-emerald-400" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 rounded-2xl bg-rose-50 dark:bg-rose-950/40 p-4 text-xs font-semibold text-rose-700 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/40">
            <flux:icon.exclamation-triangle class="size-5 shrink-0 text-rose-600 dark:text-rose-400" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Form Card --}}
    <div class="rounded-3xl bg-white dark:bg-zinc-800 shadow-xs border border-zinc-200/80 dark:border-zinc-700/80 overflow-hidden">
        {{-- Saldo Tersedia Card Header --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-indigo-900 via-indigo-800 to-zinc-900 p-6 text-white dark:from-zinc-950 dark:to-zinc-900">
            <div class="absolute -right-8 -top-8 h-36 w-36 rounded-full bg-indigo-500/20 blur-2xl pointer-events-none"></div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-200">Saldo Tersedia</span>
            <div class="mt-3 divide-y divide-white/10">
                @forelse($saldoList as $s)
                    <div class="flex items-center justify-between py-2 first:pt-0 last:pb-0">
                        <span class="text-xs font-medium text-indigo-100">{{ $s->produk->nama }}</span>
                        <span class="font-mono text-base font-bold text-white">Rp {{ number_format($s->saldo, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-xs text-indigo-200/70 py-1">Belum ada saldo yang tersedia.</p>
                @endforelse
            </div>
        </div>

        {{-- Form Body --}}
        <form wire:submit="submit" class="space-y-5 p-6">
            {{-- Pilih Produk --}}
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Produk Tabungan</label>
                <select wire:model.live="produkId"
                    class="w-full rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 px-4 py-3 text-xs font-bold text-zinc-900 dark:text-white focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                    <option value="">-- Pilih Produk Tabungan --</option>
                    @foreach($produkList as $produk)
                        <option value="{{ $produk->id }}">{{ $produk->nama }}{{ $produk->status !== 'aktif' ? ' (nonaktif)' : '' }}</option>
                    @endforeach
                </select>
                @error('produk_id') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
            </div>

            {{-- Nominal Penarikan --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Nominal Penarikan</label>
                    <span class="text-[11px] text-zinc-400 font-medium">Min: Rp {{ number_format((int) $nominalMinimal, 0, ',', '.') }} atau tarik habis</span>
                </div>
                <div class="relative">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 font-mono text-sm font-bold text-zinc-400">Rp</span>
                    <input type="number" wire:model.live="nominal" min="0" step="0.01"
                        class="w-full rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 py-3 pl-12 pr-4 text-base font-mono font-bold text-zinc-900 dark:text-white focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition"
                        placeholder="0" />
                </div>
                @error('nominal') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
            </div>

            {{-- Komisi Preview --}}
            @if($selectedProduk && $nominal > 0)
                <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-900/80 p-4 border border-zinc-200/80 dark:border-zinc-700/80 space-y-2.5">
                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-zinc-400">
                        <span>Rincian Transaksi</span>
                        <span class="text-indigo-600 dark:text-indigo-400">Estimasi Pencairan</span>
                    </div>
                    <div class="space-y-1.5 pt-1 border-t border-zinc-200/80 dark:border-zinc-800">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-600 dark:text-zinc-400">Nominal ditarik</span>
                            <span class="font-mono font-bold text-zinc-900 dark:text-white">Rp {{ number_format($nominal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-600 dark:text-zinc-400">Biaya Admin ({{ $persenKomisi }}%)</span>
                            <span class="font-mono text-rose-500 font-bold">− Rp {{ number_format($nominalKomisi, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between border-t border-zinc-200/80 dark:border-zinc-800 pt-2.5">
                        <span class="text-xs font-bold text-zinc-900 dark:text-white">Total Diterima</span>
                        <span class="font-mono text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($nominalDiterima, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endif

            {{-- Lokasi Pengambilan --}}
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Lokasi Pengambilan Cash</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative flex flex-col items-center justify-center p-3.5 rounded-2xl border cursor-pointer transition text-center
                        {{ $lokasi_pengambilan === 'kantor' ? 'border-indigo-600 bg-indigo-50/60 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-bold' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400' }}">
                        <input type="radio" wire:model.live="lokasi_pengambilan" value="kantor" class="sr-only">
                        <span class="text-xs font-bold">Kantor Utama</span>
                        <span class="text-[11px] text-zinc-400 mt-0.5 font-normal">Ambil langsung</span>
                    </label>

                    <label class="relative flex flex-col items-center justify-center p-3.5 rounded-2xl border cursor-pointer transition text-center
                        {{ $lokasi_pengambilan === 'rumah_kolektor' ? 'border-indigo-600 bg-indigo-50/60 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-bold' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400' }}">
                        <input type="radio" wire:model.live="lokasi_pengambilan" value="rumah_kolektor" class="sr-only">
                        <span class="text-xs font-bold">Rumah Kolektor</span>
                        <span class="text-[11px] text-zinc-400 mt-0.5 font-normal">Via petugas</span>
                    </label>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-3 pt-2">
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="w-1/3 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-3 text-center text-xs font-bold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition">
                    Batal
                </a>
                <button type="submit" wire:loading.attr="disabled"
                    class="w-2/3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 py-3 text-center text-xs font-bold text-white shadow-lg shadow-indigo-500/20 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="submit">Kirim Pengajuan</span>
                    <span wire:loading wire:target="submit">Memproses...</span>
                </button>
            </div>
        </form>
    </div>
</div>

