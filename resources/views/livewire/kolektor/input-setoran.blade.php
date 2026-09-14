<div class="mx-auto max-w-2xl pb-24">
    {{-- Header Navigation Tabs --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-2xl">Setoran Kolektor</h1>
                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Catat setoran nasabah & kelola kas fisik.</p>
            </div>
            
            @if($totalBelumDisetor > 0)
                <a href="{{ route('kolektor.setor-kantor.index') }}" wire:navigate
                   class="group flex items-center gap-2 rounded-xl bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-500/20 transition hover:bg-amber-500/20">
                    <span>Kas: <strong>Rp {{ number_format($totalBelumDisetor, 0, ',', '.') }}</strong></span>
                    <flux:icon.arrow-right class="size-3.5 transition-transform group-hover:translate-x-0.5" />
                </a>
            @endif
        </div>

        {{-- Segmented Tab Switcher --}}
        <div class="mt-4 flex rounded-2xl bg-zinc-100 p-1 dark:bg-zinc-800/80">
            <span class="flex-1 rounded-xl bg-white py-2 text-center text-xs font-semibold text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white">
                📥 Input Setoran
            </span>
            <a href="{{ route('kolektor.setor-kantor.index') }}" wire:navigate
               class="flex-1 rounded-xl py-2 text-center text-xs font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white transition">
                🏢 Setor ke Kantor
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
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

    <form wire:submit="submit" class="space-y-5">
        {{-- Section 1: Pilih Nasabah --}}
        <div class="rounded-2xl bg-white p-4 shadow-sm border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                    1. Pilih Nasabah Binaan
                </span>
                @if($selectedNasabah && !$showPickerNasabah)
                    <button type="button" wire:click="$set('showPickerNasabah', true)"
                            class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 flex items-center gap-1">
                        <flux:icon.pencil-square class="size-3.5" />
                        Ganti Nasabah
                    </button>
                @endif
            </div>

            @if($selectedNasabah && !$showPickerNasabah)
                <div class="flex items-center gap-3 rounded-xl bg-zinc-50 p-3.5 dark:bg-zinc-700/50 border border-zinc-100 dark:border-zinc-700">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 text-sm font-bold shadow-xs">
                        {{ strtoupper(substr($selectedNasabah->user->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-bold text-zinc-900 dark:text-white truncate">{{ $selectedNasabah->user->name }}</p>
                            <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-950/60 px-2 py-0.5 text-[10px] font-medium text-emerald-800 dark:text-emerald-300">
                                Aktif
                            </span>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate mt-0.5">{{ $selectedNasabah->alamat ?? 'Alamat belum diisi' }}</p>
                    </div>
                </div>
            @else
                <div class="relative mb-3">
                    <div class="flex items-center rounded-xl border border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-900/60 px-3 py-2.5 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/10 transition">
                        <flux:icon.magnifying-glass class="size-4 text-zinc-400 shrink-0 mr-2" />
                        <input type="text" wire:model.live.debounce.300ms="searchNasabah"
                               placeholder="Ketik nama nasabah untuk mencari..."
                               class="w-full bg-transparent text-xs text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none border-none p-0">
                        @if($searchNasabah)
                            <button type="button" wire:click="$set('searchNasabah', '')" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                                <flux:icon.x-mark class="size-4" />
                            </button>
                        @endif
                    </div>
                </div>

                <div class="max-h-[220px] overflow-y-auto space-y-1.5 pr-1">
                    @foreach($nasabahList as $n)
                        <button type="button" wire:click="pilihNasabah({{ $n->user_id }})"
                            class="w-full flex items-center gap-3 p-2.5 rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-700/60 transition text-left group">
                            <div class="h-9 w-9 rounded-full bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 group-hover:bg-blue-100 group-hover:text-blue-700 dark:group-hover:bg-blue-900/60 dark:group-hover:text-blue-300 text-xs font-semibold flex items-center justify-center shrink-0 transition">
                                {{ strtoupper(substr($n->user->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-zinc-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">{{ $n->user->name }}</p>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">{{ $n->alamat }}</p>
                            </div>
                            <flux:icon.chevron-right class="size-4 text-zinc-400 group-hover:translate-x-0.5 transition-transform" />
                        </button>
                    @endforeach
                    @if($nasabahList->isEmpty())
                        <div class="py-6 text-center">
                            <flux:icon.user-minus class="size-8 text-zinc-300 dark:text-zinc-600 mx-auto mb-2" />
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Nasabah tidak ditemukan</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Alert Tunggakan & Quick Fill --}}
        @if($tunggakanInfo && $tunggakanInfo['tunggakan'] > 0)
            <div class="rounded-2xl bg-amber-50 p-4 border border-amber-200 dark:bg-amber-950/40 dark:border-amber-800/80 shadow-xs">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex gap-3">
                        <div class="rounded-xl bg-amber-100 p-2 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 shrink-0">
                            <flux:icon.exclamation-triangle class="size-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-amber-900 dark:text-amber-200">
                                Ada tunggakan {{ $tunggakanInfo['hari'] }} hari
                            </p>
                            <p class="mt-0.5 text-xs text-amber-700 dark:text-amber-400">
                                Total seharusnya disetor hari ini: <strong class="font-bold">Rp {{ number_format($tunggakanInfo['tunggakan'], 0, ',', '.') }}</strong>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="setNominalTunggakan"
                            class="shrink-0 rounded-xl bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-amber-700 active:scale-95 transition">
                        Setor Rp {{ number_format($tunggakanInfo['tunggakan'], 0, ',', '.') }}
                    </button>
                </div>
            </div>
        @endif

        {{-- Section 2: Saldo & Produk Tabungan --}}
        @if($selectedNasabah)
            <div class="rounded-2xl bg-white p-4 shadow-sm border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-3 block">
                    2. Saldo & Pilih Produk
                </span>

                @if($selectedNasabah->user->saldoProduks->count() > 0)
                    <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($selectedNasabah->user->saldoProduks as $saldo)
                            <button type="button" wire:click="$set('produkId', {{ $saldo->produk_id }})"
                                class="flex items-center justify-between p-3 rounded-xl border text-left transition {{ (int)$produkId === $saldo->produk_id ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-950/40 dark:border-blue-600 ring-2 ring-blue-500/10' : 'border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/40 hover:border-zinc-300' }}">
                                <div>
                                    <p class="text-xs font-semibold text-zinc-900 dark:text-white">{{ $saldo->produk->nama }}</p>
                                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Saldo saat ini</p>
                                </div>
                                <span class="text-xs font-bold text-blue-600 dark:text-blue-400 font-mono">
                                    Rp {{ number_format($saldo->saldo, 0, ',', '.') }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <select wire:model.live="produkId"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-900/60 px-3 py-2.5 text-xs text-zinc-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/10">
                    <option value="">-- Pilih Produk Tabungan --</option>
                    @foreach($produkList as $produk)
                        <option value="{{ $produk->id }}">
                            {{ $produk->nama }} ({{ $produk->tipe === 'paket' ? 'Paket Sembako' : 'Bebas' }})
                        </option>
                    @endforeach
                </select>
                @error('produkId') <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p> @enderror
            </div>
        @endif

        {{-- Section 3: Nominal Setoran --}}
        <div class="rounded-2xl bg-white p-4 shadow-sm border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                    3. Nominal Setoran
                </span>
                @if($nominal)
                    <button type="button" wire:click="resetNominal" class="text-xs font-semibold text-rose-500 hover:text-rose-600 transition">
                        Reset Nominal
                    </button>
                @endif
            </div>

            <div class="relative rounded-2xl bg-zinc-50 dark:bg-zinc-900/70 p-4 border border-zinc-200 dark:border-zinc-700/80 text-center mb-3 focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition">
                <span class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest block mb-1">Total Setoran (Rp)</span>
                <input type="text" inputmode="numeric" wire:model.live="nominal"
                       placeholder="0"
                       class="w-full bg-transparent text-center text-3xl font-extrabold text-zinc-900 dark:text-white outline-none border-none p-0 placeholder-zinc-300 dark:placeholder-zinc-700 font-mono tracking-tight">
                @if((int)$nominal > 0)
                    <p class="mt-1.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                        Rp {{ number_format((int)$nominal, 0, ',', '.') }}
                    </p>
                @endif
            </div>

            {{-- Quick Presets --}}
            <div class="grid grid-cols-4 gap-2">
                @foreach([5000, 10000, 20000, 50000, 100000, 200000] as $preset)
                    <button type="button" wire:click="tambahNominal({{ $preset }})"
                        class="rounded-xl border border-zinc-200 bg-white py-2 text-xs font-semibold text-zinc-700 shadow-2xs hover:bg-zinc-50 active:scale-95 transition dark:bg-zinc-700 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-600">
                        +{{ number_format($preset, 0, ',', '.') }}
                    </button>
                @endforeach
            </div>
            @error('nominal') <p class="mt-1.5 text-[11px] text-rose-500 font-medium">{{ $message }}</p> @enderror
        </div>

        {{-- Section 4: Details (Sumber Input & Tanggal) --}}
        <div class="rounded-2xl bg-white p-4 shadow-sm border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80 space-y-4">
            <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block">
                4. Detail Transaksi
            </span>

            {{-- Sumber Input Segmented Control --}}
            <div>
                <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Sumber Input Setoran</label>
                <div class="grid grid-cols-2 gap-2 p-1 rounded-xl bg-zinc-100 dark:bg-zinc-900/60 border border-zinc-200 dark:border-zinc-700">
                    <button type="button" wire:click="$set('sumber_input', 'real_time')"
                        class="rounded-lg py-2 text-xs font-semibold transition {{ $sumber_input === 'real_time' ? 'bg-white text-zinc-900 shadow-2xs dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 dark:text-zinc-400' }}">
                        ⚡ Real-time (Kunjungan)
                    </button>
                    <button type="button" wire:click="$set('sumber_input', 'susulan')"
                        class="rounded-lg py-2 text-xs font-semibold transition {{ $sumber_input === 'susulan' ? 'bg-white text-zinc-900 shadow-2xs dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 dark:text-zinc-400' }}">
                        📝 Susulan (Catatan)
                    </button>
                </div>
            </div>

            {{-- Tanggal Transaksi --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">Tanggal Transaksi</label>
                    <div class="flex gap-1.5">
                        <button type="button" wire:click="setTanggalToday" class="text-[11px] font-medium text-blue-600 dark:text-blue-400 hover:underline">
                            Hari ini
                        </button>
                        <span class="text-zinc-300 dark:text-zinc-600">&middot;</span>
                        <button type="button" wire:click="setTanggalYesterday" class="text-[11px] font-medium text-blue-600 dark:text-blue-400 hover:underline">
                            Kemarin
                        </button>
                    </div>
                </div>
                <input type="date" wire:model="tanggal_transaksi"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-900/60 px-3 py-2 text-xs text-zinc-900 dark:text-white focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/10" />
                @error('tanggal_transaksi') <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Submit Action Bar --}}
        <div class="pt-2">
            <button type="submit" wire:loading.attr="disabled"
                class="w-full rounded-2xl bg-zinc-900 dark:bg-white py-3.5 text-xs font-bold text-white dark:text-zinc-900 shadow-md hover:bg-zinc-800 dark:hover:bg-zinc-100 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center justify-center gap-2">
                <span wire:loading.remove wire:target="submit" class="flex items-center gap-2">
                    <flux:icon.check class="size-4" />
                    Simpan & Catat Setoran
                </span>
                <span wire:loading wire:target="submit" class="flex items-center gap-2">
                    <svg class="animate-spin size-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Menyimpan Setoran...
                </span>
            </button>
        </div>
    </form>

    {{-- Section 5: Setoran Dicatat Hari Ini --}}
    @if($riwayatHariIni->isNotEmpty())
        <div class="mt-8 rounded-2xl bg-white p-4 shadow-sm border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-zinc-100 dark:border-zinc-700/60">
                <div class="flex items-center gap-2">
                    <flux:icon.clock class="size-4 text-zinc-400" />
                    <h3 class="text-xs font-bold text-zinc-900 dark:text-white uppercase tracking-wider">Setoran Dicatat Hari Ini</h3>
                </div>
                <span class="rounded-full bg-zinc-100 dark:bg-zinc-700 px-2 py-0.5 text-[10px] font-bold text-zinc-600 dark:text-zinc-300">
                    {{ $riwayatHariIni->count() }} Transaksi
                </span>
            </div>

            <div class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                @foreach($riwayatHariIni as $r)
                    <div class="py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="h-8 w-8 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 text-xs font-bold flex items-center justify-center shrink-0">
                                ✓
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-zinc-900 dark:text-white truncate">
                                    {{ $r->nasabah->name }}
                                </p>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                                    {{ $r->produk->nama ?? '-' }} &middot; {{ $r->tanggal_input_sistem->format('H:i') }}
                                </p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 font-mono shrink-0">
                            +Rp {{ number_format($r->nominal, 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
