<div class="mx-auto max-w-2xl pb-24">
    {{-- Top Bar & Header --}}
    <div class="mb-5">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('kolektor.jadwal.index') }}" wire:navigate
                   class="flex size-10 shrink-0 items-center justify-center rounded-full border border-hairline bg-white text-ink transition hover:bg-inset active:scale-95 dark:bg-surface"
                   aria-label="Kembali ke jadwal kunjungan">
                    <flux:icon.chevron-left class="size-5" />
                </a>
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-ink">Catat setoran</h1>
                    <p class="mt-0.5 text-xs text-mute">Catat tabungan nasabah di lapangan & kelola kas fisik.</p>
                </div>
            </div>

            @if($totalBelumDisetor > 0)
                <a href="{{ route('kolektor.setor-kantor.index') }}" wire:navigate
                   class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-warning-soft bg-warning-soft px-3 py-1.5 text-xs font-semibold text-warning-deep transition hover:opacity-90">
                    <span>Kas: <x-ui.rupiah :nilai="$totalBelumDisetor" /></span>
                    <flux:icon.arrow-right class="size-3.5" aria-hidden="true" />
                </a>
            @endif
        </div>

        {{-- Segmented Tab Switcher --}}
        <div class="mt-4 flex rounded-2xl bg-inset p-1">
            <span class="flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-hairline bg-white py-2.5 text-center text-sm font-semibold text-ink shadow-xs dark:bg-surface">
                <flux:icon.arrow-down-tray class="size-4 text-primary" />
                Input Setoran
            </span>
            <a href="{{ route('kolektor.setor-kantor.index') }}" wire:navigate
               class="flex flex-1 items-center justify-center gap-1.5 rounded-xl py-2.5 text-center text-sm font-medium text-mute transition hover:text-ink">
                <flux:icon.building-office-2 class="size-4" />
                Setor ke Kantor
                @if($totalBelumDisetor > 0)
                    <span class="size-2 rounded-full bg-warning-deep"></span>
                @endif
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-hairline bg-primary-soft px-4 py-3 text-sm font-medium text-on-primary-soft">
            <flux:icon.check-circle class="size-5 shrink-0" aria-hidden="true" />
            <span class="flex-1">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm font-medium text-danger-deep dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.x-circle class="size-5 shrink-0" aria-hidden="true" />
            <span class="flex-1">{{ session('error') }}</span>
        </div>
    @endif

    <form wire:submit="submit" class="space-y-4">
        {{-- Section 1: Kartu Nasabah Binaan --}}
        <div class="rounded-2xl border border-hairline bg-white p-4.5 dark:bg-surface">
            <div class="mb-3 flex items-center justify-between gap-2">
                <span class="text-xs font-semibold text-mute">
                    1. NASABAH BINAAN
                </span>
                @if($selectedNasabah && !$showPickerNasabah)
                    <button type="button" wire:click="$set('showPickerNasabah', true)"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline">
                        <flux:icon.pencil-square class="size-3.5" aria-hidden="true" />
                        Ganti nasabah
                    </button>
                @endif
            </div>

            @if($selectedNasabah && !$showPickerNasabah)
                <div class="flex items-start gap-3.5 rounded-xl border border-hairline bg-inset p-3.5">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary-soft text-sm font-semibold text-on-primary-soft" aria-hidden="true">
                        {{ strtoupper(substr($selectedNasabah->user->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="truncate text-base font-semibold text-ink">{{ $selectedNasabah->user->name }}</p>
                            <x-ui.status-chip domain="pendaftaran" status="{{ $selectedNasabah->status_pendaftaran }}" />
                            @if($selectedNasabah->user->isOffline())
                                <span class="inline-flex items-center gap-1 rounded-full bg-inset px-2 py-0.5 text-xs font-medium text-body">
                                    <flux:icon.signal-slash class="size-3" aria-hidden="true" />
                                    Mode Offline
                                </span>
                            @endif
                        </div>
                        <p class="mt-1 flex items-center gap-1 truncate text-xs text-mute">
                            <flux:icon.map-pin class="size-3.5 shrink-0" aria-hidden="true" />
                            <span class="truncate">{{ $selectedNasabah->alamat ?? 'Alamat belum diisi' }}</span>
                        </p>
                        @if($selectedNasabah->user->no_hp)
                            <p class="mt-0.5 flex items-center gap-1 text-xs text-mute">
                                <flux:icon.device-phone-mobile class="size-3.5 shrink-0" aria-hidden="true" />
                                <span class="tabular-nums">{{ $selectedNasabah->user->no_hp }}</span>
                            </p>
                        @endif
                    </div>
                </div>
            @else
                <div class="space-y-2.5">
                    <div class="relative">
                        <div class="flex items-center rounded-xl border border-hairline-strong bg-white px-3.5 py-3 transition focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 dark:bg-surface">
                            <flux:icon.magnifying-glass class="mr-2 size-4 shrink-0 text-mute" aria-hidden="true" />
                            <input type="text" wire:model.live.debounce.300ms="searchNasabah"
                                   placeholder="Ketik nama nasabah untuk mencari..."
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
                                <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-inset text-xs font-semibold text-body transition group-hover:bg-primary-soft group-hover:text-on-primary-soft" aria-hidden="true">
                                    {{ strtoupper(substr($n->user->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-ink transition group-hover:text-primary">{{ $n->user->name }}</p>
                                    <p class="truncate text-xs text-mute">{{ $n->alamat }}</p>
                                </div>
                                <flux:icon.chevron-right class="size-4 text-mute transition group-hover:translate-x-0.5" aria-hidden="true" />
                            </button>
                        @endforeach
                        @if($nasabahList->isEmpty())
                            <div class="py-6 text-center">
                                <flux:icon.user-minus class="mx-auto mb-2 size-8 text-mute" aria-hidden="true" />
                                <p class="text-sm text-mute">Nasabah tidak ditemukan</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Alert Tunggakan & Quick Fill --}}
        @if($tunggakanInfo && $tunggakanInfo['tunggakan'] > 0)
            <div class="rounded-xl border border-warning-soft bg-warning-soft p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex gap-3">
                        <div class="shrink-0 rounded-full bg-white/60 p-2 dark:bg-surface/60" aria-hidden="true">
                            <flux:icon.exclamation-triangle class="size-5 text-warning-deep" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-warning-deep">
                                Ada tunggakan {{ $tunggakanInfo['hari'] }} hari
                            </p>
                            <p class="mt-0.5 text-xs text-warning-deep">
                                Seharusnya disetor hari ini:
                                <strong class="font-semibold"><x-ui.rupiah :nilai="$tunggakanInfo['tunggakan']" /></strong>
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="setNominalTunggakan"
                            class="shrink-0 rounded-full border border-hairline-strong bg-white px-3 py-1.5 text-xs font-semibold text-ink transition hover:bg-inset active:scale-95 dark:bg-surface">
                        Setor <x-ui.rupiah :nilai="$tunggakanInfo['tunggakan']" />
                    </button>
                </div>
            </div>
        @endif

        {{-- Section 2: Saldo & Pilih Produk Tabungan --}}
        @if($selectedNasabah)
            <div class="rounded-2xl border border-hairline bg-white p-4.5 dark:bg-surface">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-mute">
                        2. PRODUK TABUNGAN & SALDO
                    </span>
                    @if($selectedNasabah->user->saldoProduks->count() > 0)
                        <span class="text-xs text-subtle">
                            {{ $selectedNasabah->user->saldoProduks->count() }} rekening aktif
                        </span>
                    @endif
                </div>

                @if($selectedNasabah->user->saldoProduks->count() > 0)
                    <div class="mb-3 grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                        @foreach($selectedNasabah->user->saldoProduks as $saldo)
                            @php
                                $terpilih = (int)$produkId === $saldo->produk_id;
                            @endphp
                            <button type="button" wire:click="$set('produkId', {{ $saldo->produk_id }})"
                                class="flex items-center justify-between gap-2 rounded-xl border p-3.5 text-left transition {{ $terpilih ? 'border-primary bg-primary-soft text-on-primary-soft shadow-xs' : 'border-hairline bg-inset text-ink hover:border-hairline-strong' }}">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <p class="truncate text-sm font-semibold">{{ $saldo->produk->nama }}</p>
                                        @if($saldo->produk->tipe === 'paket')
                                            <span class="rounded-full bg-white/60 px-1.5 py-0.5 text-xs text-primary dark:bg-surface">Paket</span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 text-xs opacity-75">Saldo saat ini</p>
                                </div>
                                <span class="shrink-0 text-sm font-semibold">
                                    <x-ui.rupiah :nilai="$saldo->saldo" />
                                </span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <select wire:model.live="produkId"
                    class="w-full rounded-xl border border-hairline-strong bg-white px-3.5 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
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

        {{-- Section 3: Nominal Setoran & Presets --}}
        <div class="rounded-2xl border border-hairline bg-white p-4.5 dark:bg-surface">
            <div class="mb-3 flex items-center justify-between gap-2">
                <span class="text-xs font-semibold text-mute">
                    3. NOMINAL SETORAN
                </span>
                @if($nominal)
                    <button type="button" wire:click="resetNominal" class="text-xs font-semibold text-mute hover:text-ink hover:underline">
                        Reset nominal
                    </button>
                @endif
            </div>

            <div class="relative">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-2xl font-semibold text-mute">Rp</span>
                <input type="text" inputmode="numeric" wire:model.live="nominal"
                       placeholder="0" aria-label="Total setoran"
                       class="h-16 w-full rounded-xl border border-hairline-strong bg-white pr-4 pl-16 text-3xl font-semibold tabular-nums text-ink outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
            </div>

            @if((int)$nominal > 0)
                <p class="mt-2 text-sm font-semibold text-primary">
                    <x-ui.rupiah :nilai="(int)$nominal" tanda="+" />
                </p>
            @endif

            {{-- Quick Presets --}}
            @php
                $produkTerpilih = $this->selectedProduk;
                $isPaket = $produkTerpilih && $produkTerpilih->isPaket() && (int) $produkTerpilih->harga_per_hari > 0;
            @endphp

            @if($isPaket)
                @php
                    $harian = (int) $produkTerpilih->harga_per_hari;
                    $nominalInt = (int) $nominal;
                    $jumlahHari = $harian > 0 ? (int) floor($nominalInt / $harian) : 0;
                    $sisaLebihan = $harian > 0 ? $nominalInt % $harian : 0;
                    $presetsPaket = [
                        ['label' => '1 Hari', 'nilai' => $harian * 1],
                        ['label' => '2 Hari', 'nilai' => $harian * 2],
                        ['label' => '3 Hari', 'nilai' => $harian * 3],
                        ['label' => '1 Minggu', 'nilai' => $harian * 7],
                        ['label' => '2 Minggu', 'nilai' => $harian * 14],
                        ['label' => '1 Bulan', 'nilai' => $harian * 30],
                    ];
                @endphp

                <div class="mt-3.5 rounded-xl border border-hairline bg-inset p-3.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-ink">
                            Pilihan Cepat Paket (Rp {{ number_format($harian, 0, ',', '.') }}/hari)
                        </span>
                        @if($nominalInt > 0 && $jumlahHari > 0)
                            <span class="text-xs font-semibold text-primary">
                                {{ $jumlahHari }} Hari Tabungan{{ $sisaLebihan > 0 ? ' (+Lebihan)' : '' }}
                            </span>
                        @endif
                    </div>
                    <div class="mt-2.5 grid grid-cols-3 gap-2">
                        @foreach($presetsPaket as $item)
                            <button type="button" wire:key="preset-paket-{{ $item['nilai'] }}" wire:click="setNominalAngka({{ $item['nilai'] }})"
                                class="rounded-xl border border-hairline bg-white py-2 px-2 text-center text-xs font-medium text-ink transition hover:border-primary hover:bg-primary-soft hover:text-primary active:scale-95 dark:bg-surface">
                                <span class="block font-semibold">{{ $item['label'] }}</span>
                                <span class="text-mute"><x-ui.rupiah :nilai="$item['nilai']" /></span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach([10000, 20000, 50000, 100000, 200000] as $preset)
                        <button type="button" wire:key="preset-bebas-{{ $preset }}" wire:click="tambahNominal({{ $preset }})"
                            class="rounded-full border border-hairline bg-inset px-3.5 py-1.5 text-xs font-medium text-body transition hover:bg-hairline hover:text-ink active:scale-95">
                            <x-ui.rupiah :nilai="$preset" tanda="+" />
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- Live Saldo Setelah Setor Preview --}}
            @if($selectedNasabah && $produkId && (int)$nominal > 0)
                @php
                    $saldoRekening = $selectedNasabah->user->saldoProduks->firstWhere('produk_id', (int)$produkId);
                    $saldoAwal = $saldoRekening ? (float)$saldoRekening->saldo : 0;
                    $saldoAkhir = $saldoAwal + (float)$nominal;
                @endphp
                <div class="mt-3.5 flex items-center justify-between rounded-xl border border-hairline bg-inset px-4 py-3 text-sm">
                    <span class="text-mute">Saldo setelah setor</span>
                    <span class="text-base font-semibold text-primary">
                        <x-ui.rupiah :nilai="$saldoAkhir" />
                    </span>
                </div>
            @endif

            @error('nominal') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
        </div>

        {{-- Section 4: Detail Transaksi --}}
        <div class="space-y-4 rounded-2xl border border-hairline bg-white p-4.5 dark:bg-surface">
            <span class="block text-xs font-semibold text-mute">
                4. DETAIL TRANSAKSI
            </span>

            {{-- Sumber Input Segmented Control --}}
            <div>
                <label class="mb-1.5 block text-xs font-medium text-body">Sumber input setoran</label>
                <div class="grid grid-cols-2 gap-2 rounded-xl border border-hairline bg-inset p-1">
                    <button type="button" wire:click="$set('sumber_input', 'real_time')"
                        class="rounded-lg py-2 text-sm font-medium transition {{ $sumber_input === 'real_time' ? 'border border-hairline bg-white text-ink shadow-xs dark:bg-surface' : 'text-mute hover:text-ink' }}">
                        Real-time (kunjungan)
                    </button>
                    <button type="button" wire:click="$set('sumber_input', 'susulan')"
                        class="rounded-lg py-2 text-sm font-medium transition {{ $sumber_input === 'susulan' ? 'border border-hairline bg-white text-ink shadow-xs dark:bg-surface' : 'text-mute hover:text-ink' }}">
                        Susulan (catatan)
                    </button>
                </div>
            </div>

            {{-- Tanggal Transaksi --}}
            <div>
                <div class="mb-1.5 flex items-center justify-between gap-2">
                    <label class="block text-xs font-medium text-body">Tanggal transaksi</label>
                    <div class="flex gap-2">
                        <button type="button" wire:click="setTanggalToday" class="text-xs font-semibold text-primary hover:underline">
                            Hari ini
                        </button>
                        <span class="text-mute" aria-hidden="true">&middot;</span>
                        <button type="button" wire:click="setTanggalYesterday" class="text-xs font-semibold text-primary hover:underline">
                            Kemarin
                        </button>
                    </div>
                </div>
                <x-ui.tanggal wire:model="tanggal_transaksi" :max="now()->toDateString()"
                    class="w-full rounded-xl border border-hairline-strong bg-white px-3.5 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                @error('tanggal_transaksi') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Submit Action Bar --}}
        <div class="space-y-2.5 pt-2">
            <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                class="flex h-12 w-full items-center justify-center gap-2 rounded-full bg-primary text-base font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50">
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

            <a href="{{ route('kolektor.jadwal.index') }}" wire:navigate
               class="flex h-12 w-full items-center justify-center gap-2 rounded-full border border-hairline-strong bg-white text-sm font-semibold text-body transition hover:bg-inset active:scale-[0.98] dark:bg-surface">
                <flux:icon.arrow-uturn-left class="size-4 text-mute" />
                Nasabah tidak di rumah
            </a>
        </div>
    </form>

    {{-- Section 5: Setoran Dicatat Hari Ini --}}
    @if($riwayatHariIni->isNotEmpty())
        <div class="mt-6 rounded-2xl border border-hairline bg-white p-4.5 dark:bg-surface">
            <div class="mb-3 flex items-center justify-between gap-2 border-b border-hairline pb-3">
                <div class="flex items-center gap-2">
                    <flux:icon.clock class="size-4 text-mute" aria-hidden="true" />
                    <h2 class="text-sm font-semibold text-ink">Setoran dicatat hari ini</h2>
                </div>
                <span class="rounded-full bg-inset px-2.5 py-0.5 text-xs font-semibold text-body">
                    {{ $riwayatHariIni->count() }} transaksi
                </span>
            </div>

            <div class="divide-y divide-hairline">
                @foreach($riwayatHariIni as $r)
                    <div class="flex items-center justify-between gap-3 py-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft" aria-hidden="true">
                                <flux:icon.check class="size-4" />
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-ink">
                                    {{ $r->nasabah->name }}
                                </p>
                                <p class="truncate text-xs text-mute">
                                    {{ $r->produk->nama ?? '-' }} &middot; {{ $r->tanggal_input_sistem->format('H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-sm font-semibold text-primary">
                                <x-ui.rupiah :nilai="$r->nominal" tanda="+" />
                            </p>
                            <a href="{{ route('struk.setoran', $r) }}"
                               @click.prevent="$dispatch('buka-struk', { url: '{{ route('struk.setoran', $r) }}' })"
                               class="mt-0.5 inline-block text-xs font-semibold text-primary hover:underline cursor-pointer">
                                Lihat struk
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Modal Sukses Setoran --}}
    @if ($showSuccess && $lastSetoran)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-0 sm:items-center sm:p-4"
             role="dialog" aria-modal="true" aria-labelledby="modal-sukses-title">
            <div class="fixed inset-0" wire:click="tutupSuccess" aria-hidden="true"></div>

            <div class="relative w-full max-w-md rounded-t-[28px] border-t border-hairline bg-surface p-5 sm:p-6 shadow-md sm:rounded-[24px] sm:border dark:bg-surface max-h-[92vh] overflow-y-auto" @click.stop>
                {{-- Mobile drag handle pill --}}
                <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-hairline-strong/60 sm:hidden" aria-hidden="true"></div>

                <button type="button" wire:click="tutupSuccess"
                        class="absolute right-4 top-4 flex size-8 items-center justify-center rounded-full bg-surface-inset text-mute transition hover:bg-hairline hover:text-ink active:scale-95 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-400"
                        aria-label="Tutup">
                    <flux:icon.x-mark class="size-4" />
                </button>

                <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-primary-soft text-primary-deep dark:bg-emerald-950/70 dark:text-emerald-300 ring-4 ring-primary-soft/60 dark:ring-emerald-950/40">
                    <flux:icon.check-circle class="size-8" />
                </div>

                <h3 id="modal-sukses-title" class="mt-3 text-center text-lg font-semibold text-ink dark:text-zinc-100">Setoran dicatat</h3>

                <p class="mt-1 text-center text-2xl font-semibold text-primary dark:text-emerald-400 tabular-nums">
                    <x-ui.rupiah :nilai="$lastSetoran->nominal" />
                </p>

                <div class="mt-4 space-y-2 rounded-2xl border border-hairline bg-surface-inset p-4 text-xs dark:bg-zinc-800/50">
                    <div class="flex items-center justify-between">
                        <span class="text-mute dark:text-zinc-400">Nasabah</span>
                        <span class="font-semibold text-ink dark:text-zinc-100">{{ $lastSetoran->nasabah?->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-mute dark:text-zinc-400">No. struk</span>
                        <span class="font-semibold tabular-nums text-ink dark:text-zinc-100">#SET-{{ str_pad((string) $lastSetoran->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-mute dark:text-zinc-400">Kas di tangan</span>
                        <span class="font-semibold text-ink dark:text-zinc-100"><x-ui.rupiah :nilai="$totalBelumDisetor" /></span>
                    </div>
                </div>

                <div class="mt-5 space-y-2.5">
                    <a href="{{ route('struk.setoran', $lastSetoran) }}"
                       @click.prevent="$dispatch('buka-struk', { url: '{{ route('struk.setoran', $lastSetoran) }}' })"
                       class="flex h-11 min-h-[44px] w-full items-center justify-center gap-1.5 rounded-full bg-primary text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-95 cursor-pointer">
                        <flux:icon.receipt-percent class="size-4" />
                        Bagikan struk
                    </a>
                    <div class="flex gap-2.5">
                        <a href="{{ route('kolektor.jadwal.index') }}" wire:navigate
                           class="flex h-11 min-h-[44px] flex-1 items-center justify-center gap-1 rounded-full border border-hairline-strong bg-surface text-xs font-semibold text-body transition hover:bg-surface-inset active:scale-95 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-700">
                            Kunjungan berikutnya
                        </a>
                        <button type="button" wire:click="tutupSuccess"
                                class="h-11 min-h-[44px] rounded-full border border-hairline-strong bg-surface px-5 text-xs font-semibold text-body transition hover:bg-surface-inset active:scale-95 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-700">
                            Selesai
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
