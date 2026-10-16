<div class="mx-auto max-w-2xl pb-24">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-ink">Setoran kolektor</h1>
                <p class="mt-1 text-sm text-mute">Catat setoran nasabah dan kelola kas fisik.</p>
            </div>

            @if($totalBelumDisetor > 0)
                <a href="{{ route('kolektor.setor-kantor.index') }}" wire:navigate
                   class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-warning-soft bg-warning-soft px-3 py-1.5 text-xs font-medium text-warning-deep transition hover:opacity-90">
                    <span>Kas: <x-ui.rupiah :nilai="$totalBelumDisetor" /></span>
                    <flux:icon.arrow-right class="size-3.5" aria-hidden="true" />
                </a>
            @endif
        </div>

        {{-- Segmented tab switcher --}}
        <div class="mt-4 flex rounded-2xl bg-inset p-1">
            <span class="flex-1 rounded-xl border border-hairline bg-white py-2 text-center text-sm font-medium text-ink dark:bg-surface">
                Input Setoran
            </span>
            <a href="{{ route('kolektor.setor-kantor.index') }}" wire:navigate
               class="flex-1 rounded-xl py-2 text-center text-sm font-medium text-mute transition hover:text-ink">
                Setor ke Kantor
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-xl border border-hairline bg-primary-soft px-4 py-3 text-sm font-medium text-on-primary-soft">
            <flux:icon.check-circle class="size-5 shrink-0" aria-hidden="true" />
            <span class="flex-1">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-5 flex items-center gap-3 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm font-medium text-danger-deep dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.x-circle class="size-5 shrink-0" aria-hidden="true" />
            <span class="flex-1">{{ session('error') }}</span>
        </div>
    @endif

    <form wire:submit="submit" class="space-y-5">
        {{-- Section 1: Pilih Nasabah --}}
        <div class="rounded-2xl border border-hairline bg-white p-4 dark:bg-surface">
            <div class="mb-3 flex items-center justify-between gap-2">
                <span class="text-sm font-medium text-mute">
                    1. Pilih nasabah binaan
                </span>
                @if($selectedNasabah && !$showPickerNasabah)
                    <button type="button" wire:click="$set('showPickerNasabah', true)"
                            class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline">
                        <flux:icon.pencil-square class="size-4" aria-hidden="true" />
                        Ganti nasabah
                    </button>
                @endif
            </div>

            @if($selectedNasabah && !$showPickerNasabah)
                <div class="flex items-center gap-3 rounded-xl bg-inset p-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-soft text-sm font-semibold text-on-primary-soft" aria-hidden="true">
                        {{ strtoupper(substr($selectedNasabah->user->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="truncate text-sm font-semibold text-ink">{{ $selectedNasabah->user->name }}</p>
                            <x-ui.status-chip domain="pendaftaran" status="{{ $selectedNasabah->status_pendaftaran }}" />
                        </div>
                        <p class="mt-0.5 truncate text-sm text-mute">{{ $selectedNasabah->alamat ?? 'Alamat belum diisi' }}</p>
                    </div>
                </div>
            @else
                <div class="relative mb-3">
                    <div class="flex items-center rounded-xl border border-hairline-strong bg-white px-3 py-3 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition dark:bg-surface">
                        <flux:icon.magnifying-glass class="mr-2 size-4 shrink-0 text-mute" aria-hidden="true" />
                        <input type="text" wire:model.live.debounce.300ms="searchNasabah"
                               placeholder="Ketik nama nasabah untuk mencari"
                               class="w-full border-none bg-transparent p-0 text-sm text-ink placeholder:text-subtle focus:outline-none">
                        @if($searchNasabah)
                            <button type="button" wire:click="$set('searchNasabah', '')" aria-label="Hapus pencarian" class="text-mute hover:text-ink">
                                <flux:icon.x-mark class="size-4" />
                            </button>
                        @endif
                    </div>
                </div>

                <div class="max-h-[220px] space-y-1.5 overflow-y-auto pr-1">
                    @foreach($nasabahList as $n)
                        <button type="button" wire:click="pilihNasabah({{ $n->user_id }})"
                            class="group flex w-full items-center gap-3 rounded-xl p-2.5 text-left transition hover:bg-inset">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-inset text-xs font-semibold text-body transition group-hover:bg-primary-soft group-hover:text-on-primary-soft" aria-hidden="true">
                                {{ strtoupper(substr($n->user->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-ink transition group-hover:text-primary">{{ $n->user->name }}</p>
                                <p class="truncate text-xs text-mute">{{ $n->alamat }}</p>
                            </div>
                            <flux:icon.chevron-right class="size-4 text-mute" aria-hidden="true" />
                        </button>
                    @endforeach
                    @if($nasabahList->isEmpty())
                        <div class="py-6 text-center">
                            <flux:icon.user-minus class="mx-auto mb-2 size-8 text-mute" aria-hidden="true" />
                            <p class="text-sm text-mute">Nasabah tidak ditemukan</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Alert Tunggakan & Quick Fill --}}
        @if($tunggakanInfo && $tunggakanInfo['tunggakan'] > 0)
            <div class="rounded-xl border border-warning-soft bg-warning-soft p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex gap-3">
                        <div class="shrink-0 rounded-full bg-inset p-2" aria-hidden="true">
                            <flux:icon.exclamation-triangle class="size-5 text-warning-deep" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-warning-deep">
                                Ada tunggakan {{ $tunggakanInfo['hari'] }} hari
                            </p>
                            <p class="mt-0.5 text-sm text-warning-deep">
                                Seharusnya disetor hari ini:
                                <strong class="font-semibold"><x-ui.rupiah :nilai="$tunggakanInfo['tunggakan']" /></strong>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="setNominalTunggakan"
                            class="shrink-0 rounded-full border border-hairline-strong bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:bg-inset active:scale-95">
                        Setor <x-ui.rupiah :nilai="$tunggakanInfo['tunggakan']" />
                    </button>
                </div>
            </div>
        @endif

        {{-- Section 2: Saldo & Produk Tabungan --}}
        @if($selectedNasabah)
            <div class="rounded-2xl border border-hairline bg-white p-4 dark:bg-surface">
                <span class="mb-3 block text-sm font-medium text-mute">
                    2. Saldo dan pilih produk
                </span>

                @if($selectedNasabah->user->saldoProduks->count() > 0)
                    <div class="mb-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach($selectedNasabah->user->saldoProduks as $saldo)
                            @php($terpilih = (int)$produkId === $saldo->produk_id)
                            <button type="button" wire:click="$set('produkId', {{ $saldo->produk_id }})"
                                class="flex items-center justify-between gap-2 rounded-xl border p-3 text-left transition {{ $terpilih ? 'border-primary bg-primary-soft text-on-primary-soft' : 'border-hairline bg-inset text-ink hover:border-hairline-strong' }}">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium">{{ $saldo->produk->nama }}</p>
                                    <p class="text-xs opacity-80">Saldo saat ini</p>
                                </div>
                                <span class="shrink-0 text-sm font-semibold">
                                    <x-ui.rupiah :nilai="$saldo->saldo" />
                                </span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <select wire:model.live="produkId"
                    class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                    <option value="">-- Pilih produk tabungan --</option>
                    @foreach($produkList as $produk)
                        <option value="{{ $produk->id }}">
                            {{ $produk->nama }} ({{ $produk->tipe === 'paket' ? 'Paket Sembako' : 'Bebas' }})
                        </option>
                    @endforeach
                </select>
                @error('produkId') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
            </div>
        @endif

        {{-- Section 3: Nominal Setoran --}}
        <div class="rounded-2xl border border-hairline bg-white p-4 dark:bg-surface">
            <div class="mb-3 flex items-center justify-between gap-2">
                <span class="text-sm font-medium text-mute">
                    3. Nominal setoran
                </span>
                @if($nominal)
                    <button type="button" wire:click="resetNominal" class="text-sm font-medium text-body hover:text-ink hover:underline">
                        Reset nominal
                    </button>
                @endif
            </div>

            <div class="relative">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-2xl font-semibold text-mute">Rp</span>
                <input type="text" inputmode="numeric" wire:model.live="nominal"
                       placeholder="0" aria-label="Total setoran"
                       class="h-16 w-full rounded-xl border border-hairline-strong bg-white pr-4 pl-16 text-2xl font-semibold tabular-nums text-ink outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
            </div>
            @if((int)$nominal > 0)
                <p class="mt-2 text-sm font-semibold">
                    <x-ui.rupiah :nilai="(int)$nominal" tanda="+" />
                </p>
            @endif

            {{-- Quick Presets --}}
            <div class="mt-3 grid grid-cols-4 gap-2">
                @foreach([5000, 10000, 20000, 50000, 100000, 200000] as $preset)
                    <button type="button" wire:click="tambahNominal({{ $preset }})"
                        class="rounded-xl border border-hairline bg-white py-2 text-xs font-medium text-body transition hover:bg-inset active:scale-95 dark:bg-surface">
                        <x-ui.rupiah :nilai="$preset" tanda="+" />
                    </button>
                @endforeach
            </div>
            @error('nominal') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
        </div>

        {{-- Section 4: Details (Sumber Input & Tanggal) --}}
        <div class="space-y-4 rounded-2xl border border-hairline bg-white p-4 dark:bg-surface">
            <span class="block text-sm font-medium text-mute">
                4. Detail transaksi
            </span>

            {{-- Sumber Input Segmented Control --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-body">Sumber input setoran</label>
                <div class="grid grid-cols-2 gap-2 rounded-xl border border-hairline bg-inset p-1">
                    <button type="button" wire:click="$set('sumber_input', 'real_time')"
                        class="rounded-lg py-2 text-sm font-medium transition {{ $sumber_input === 'real_time' ? 'border border-hairline bg-white text-ink dark:bg-surface' : 'text-mute' }}">
                        Real-time (kunjungan)
                    </button>
                    <button type="button" wire:click="$set('sumber_input', 'susulan')"
                        class="rounded-lg py-2 text-sm font-medium transition {{ $sumber_input === 'susulan' ? 'border border-hairline bg-white text-ink dark:bg-surface' : 'text-mute' }}">
                        Susulan (catatan)
                    </button>
                </div>
            </div>

            {{-- Tanggal Transaksi --}}
            <div>
                <div class="mb-1.5 flex items-center justify-between gap-2">
                    <label class="block text-sm font-medium text-body">Tanggal transaksi</label>
                    <div class="flex gap-2">
                        <button type="button" wire:click="setTanggalToday" class="text-sm font-medium text-primary hover:underline">
                            Hari ini
                        </button>
                        <span class="text-mute" aria-hidden="true">&middot;</span>
                        <button type="button" wire:click="setTanggalYesterday" class="text-sm font-medium text-primary hover:underline">
                            Kemarin
                        </button>
                    </div>
                </div>
                <x-ui.tanggal wire:model="tanggal_transaksi" :max="now()->toDateString()"
                    class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                @error('tanggal_transaksi') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Submit Action Bar --}}
        <div class="pt-2">
            <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                class="flex h-12 w-full items-center justify-center gap-2 rounded-full bg-primary text-base font-medium text-on-primary transition hover:bg-primary-hover active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50">
                <span wire:loading.remove wire:target="submit" class="flex items-center gap-2">
                    <flux:icon.check class="size-5" aria-hidden="true" />
                    Catat setoran
                </span>
                <span wire:loading wire:target="submit" class="flex items-center gap-2">
                    <svg class="size-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Mencatat setoran...
                </span>
            </button>
        </div>
    </form>

    {{-- Section 5: Setoran Dicatat Hari Ini --}}
    @if($riwayatHariIni->isNotEmpty())
        <div class="mt-6 rounded-2xl border border-hairline bg-white p-4 dark:bg-surface">
            <div class="mb-3 flex items-center justify-between gap-2 border-b border-hairline pb-3">
                <div class="flex items-center gap-2">
                    <flux:icon.clock class="size-4 text-mute" aria-hidden="true" />
                    <h2 class="text-sm font-medium text-ink">Setoran dicatat hari ini</h2>
                </div>
                <span class="rounded-full bg-inset px-2 py-0.5 text-xs font-medium text-body">
                    {{ $riwayatHariIni->count() }} transaksi
                </span>
            </div>

            <div class="divide-y divide-hairline">
                @foreach($riwayatHariIni as $r)
                    <div class="flex items-center justify-between gap-3 py-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft" aria-hidden="true">
                                <flux:icon.check class="size-4" />
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-ink">
                                    {{ $r->nasabah->name }}
                                </p>
                                <p class="truncate text-xs text-mute">
                                    {{ $r->produk->nama ?? '-' }} &middot; {{ $r->tanggal_input_sistem->format('H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-sm font-semibold">
                                <x-ui.rupiah :nilai="$r->nominal" tanda="+" />
                            </p>
                            <a href="{{ route('struk.setoran', $r) }}" class="mt-0.5 inline-block text-xs font-medium text-primary hover:underline">
                                Lihat struk
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
