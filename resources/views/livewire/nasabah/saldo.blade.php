<div class="space-y-6">
    {{-- Judul halaman --}}
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Saldo saya</h1>
        <p class="mt-1 text-sm text-mute">Rincian saldo per produk tabungan aktif Anda.</p>
    </div>

    {{-- Kartu saldo utama (hero-card §7) --}}
    <x-ui.kartu-saldo-hero :total-saldo="$totalSaldo" label="Total saldo">
        <p class="mt-1 text-sm text-on-hero-mute">Dari seluruh produk tabungan aktif Anda.</p>

        <a href="{{ route('nasabah.penarikan.index') }}" wire:navigate
            class="mt-5 flex h-12 items-center justify-center gap-2 rounded-full bg-white text-base font-medium text-on-primary-soft transition hover:bg-white/90 active:scale-[0.98] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white dark:text-primary-deep"
            data-test="tombol-ajukan-penarikan">
            <flux:icon.arrow-up-tray class="size-5" />
            Ajukan penarikan
        </a>
        <a href="{{ route('nasabah.riwayat-tabungan.index') }}" wire:navigate
            class="mt-2 flex h-11 items-center justify-center gap-2 rounded-full text-sm font-medium text-on-hero underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
            <flux:icon.clock class="size-4" />
            Lihat riwayat setoran
        </a>
    </x-ui.kartu-saldo-hero>

    {{-- Rincian per produk --}}
    <section aria-labelledby="label-rincian" class="space-y-3">
        <div class="flex items-baseline justify-between">
            <h2 id="label-rincian" class="text-base font-semibold text-ink">Rincian tabungan</h2>
            <span class="text-sm text-mute">{{ count($saldo) }} produk</span>
        </div>

        @forelse($saldo as $item)
            @php
                // Bar capaian teal (DESIGN §2 "cincin dan bar paket") hanya untuk
                // produk paket, dihitung dari saldo produk dan target akhir yang
                // sudah ada di view ini; tanpa query baru.
                $targetProduk = $item->produk->isPaket() ? $item->produk->targetAkhir() : null;
                $capaianPersen = $targetProduk !== null && $targetProduk > 0
                    ? (int) min(100, round((float) $item->saldo / $targetProduk * 100))
                    : null;
            @endphp
            <div class="rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface" data-test="kartu-produk-saldo">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <x-ui.tile :kategori="$item->produk->isPaket() ? 'teal' : 'hijau'" ukuran="44" aria-hidden="true">
                            @if($item->produk->isPaket())
                                <flux:icon.shopping-bag class="size-5" />
                            @else
                                <flux:icon.banknotes class="size-5" />
                            @endif
                        </x-ui.tile>

                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-base font-semibold text-ink">{{ $item->produk->nama }}</p>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $item->produk->isPaket() ? 'bg-tile-teal-bg text-tile-teal-fg' : 'bg-primary-soft text-primary' }}">
                                    {{ $item->produk->isPaket() ? 'Paket' : 'Bebas' }}
                                </span>
                            </div>
                            <p class="text-xs text-mute mt-0.5">
                                @if($item->produk->isPaket() && $item->produk->tanggal_boleh_cair)
                                    Cair {{ \Carbon\Carbon::parse($item->produk->tanggal_boleh_cair)->translatedFormat('d M Y') }}
                                @else
                                    Bebas ditarik kapan saja
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <p class="text-base font-semibold text-ink">
                            <x-ui.rupiah :nilai="$item->saldo" />
                        </p>
                    </div>
                </div>

                @if($capaianPersen !== null)
                    <div class="mt-3">
                        <div class="h-2 overflow-hidden rounded-full bg-tile-teal-bg" aria-hidden="true">
                            <div class="h-full rounded-full bg-tile-teal-fg transition-all duration-500" style="width: {{ $capaianPersen }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-mute tabular-nums">
                            Capaian {{ $capaianPersen }}% dari target <x-ui.rupiah :nilai="$targetProduk" />
                        </p>
                    </div>
                @endif

                <div class="mt-3.5 pt-3 border-t border-hairline-warm flex items-center justify-between">
                    <span class="text-xs text-mute">Tersedia untuk ditarik</span>
                    <a href="{{ route('nasabah.penarikan.index', ['produk_id' => $item->produk_id]) }}" wire:navigate
                       class="inline-flex items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3.5 py-1.5 text-xs font-medium text-ink transition hover:bg-surface-inset active:scale-95 dark:bg-surface"
                       data-test="tombol-tarik-produk">
                        <flux:icon.arrow-up-tray class="size-3.5 text-primary" />
                        Tarik
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-[20px] border border-hairline-warm bg-white p-8 text-center shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                <span class="mx-auto flex size-32 items-center justify-center rounded-full bg-primary-soft" aria-hidden="true">
                    <x-ui.ilustrasi nama="tunas" :ukuran="96" />
                </span>
                <h3 class="mt-4 text-base font-semibold text-ink">Belum ada tabungan aktif</h3>
                <p class="mt-1 text-sm text-mute">Hubungi kolektor Anda untuk mulai menabung.</p>
            </div>
        @endforelse
    </section>
</div>
