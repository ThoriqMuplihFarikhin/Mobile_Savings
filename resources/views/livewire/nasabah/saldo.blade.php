<div class="space-y-6">
    {{-- Judul halaman --}}
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Saldo saya</h1>
        <p class="mt-1 text-sm text-mute">Rincian saldo per produk tabungan aktif Anda.</p>
    </div>

    {{-- Kartu saldo: satu angka besar, satu tombol utama berlatar putih, satu tautan (§7) --}}
    <x-ui.hero-card aria-labelledby="label-total-saldo">
        <p id="label-total-saldo" class="text-sm text-on-hero-mute">Total saldo</p>
        <p class="mt-1 text-[34px] font-semibold leading-10 tracking-[-0.64px]">
            <x-ui.rupiah :nilai="$totalSaldo" />
        </p>
        <p class="mt-1 text-sm text-on-hero-mute">Dari seluruh produk tabungan aktif Anda.</p>

        <a href="{{ route('nasabah.penarikan.index') }}" wire:navigate
            class="mt-5 flex h-12 items-center justify-center gap-2 rounded-full bg-white text-base font-medium text-on-primary-soft transition hover:bg-white/90 active:scale-[0.98] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white dark:text-primary-deep">
            <flux:icon.arrow-up-tray class="size-5" />
            Ajukan penarikan
        </a>
        <a href="{{ route('nasabah.riwayat-tabungan.index') }}" wire:navigate
            class="mt-2 flex h-11 items-center justify-center gap-2 rounded-full text-sm font-medium text-on-hero underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
            <flux:icon.clock class="size-4" />
            Lihat riwayat setoran
        </a>
    </x-ui.hero-card>

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
            <div class="rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                <div class="flex items-center gap-3">
                    <x-ui.tile :kategori="$item->produk->isPaket() ? 'teal' : 'hijau'" ukuran="40" aria-hidden="true">
                        @if($item->produk->isPaket())
                            <flux:icon.shopping-bag class="size-5" />
                        @else
                            <flux:icon.banknotes class="size-5" />
                        @endif
                    </x-ui.tile>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-base font-medium text-ink">{{ $item->produk->nama }}</p>
                        <p class="text-sm text-mute">
                            {{ $item->produk->isPaket() ? 'Paket' : 'Bebas' }}
                            @if($item->produk->isPaket() && $item->produk->tanggal_boleh_cair)
                                · Cair {{ \Carbon\Carbon::parse($item->produk->tanggal_boleh_cair)->translatedFormat('d M Y') }}
                            @endif
                        </p>
                    </div>

                    <p class="shrink-0 text-base font-semibold">
                        <x-ui.rupiah :nilai="$item->saldo" />
                    </p>
                </div>

                @if($capaianPersen !== null)
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-tile-teal-bg" aria-hidden="true">
                        <div class="h-full rounded-full bg-tile-teal-fg" style="width: {{ $capaianPersen }}%"></div>
                    </div>
                    <p class="mt-1 text-xs text-mute tabular-nums">
                        Capaian {{ $capaianPersen }}% dari target <x-ui.rupiah :nilai="$targetProduk" />
                    </p>
                @endif
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
