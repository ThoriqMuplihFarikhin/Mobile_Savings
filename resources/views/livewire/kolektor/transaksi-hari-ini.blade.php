<div class="mx-auto max-w-2xl space-y-4 pb-20">
    {{-- Top Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" wire:navigate
           class="flex size-10 shrink-0 items-center justify-center rounded-full bg-white border border-hairline-strong text-ink transition hover:bg-inset active:scale-95 dark:bg-surface dark:text-zinc-200"
           aria-label="Kembali ke beranda">
            <flux:icon.arrow-left class="size-5" />
        </a>
        <div class="min-w-0 flex-1">
            <h1 class="text-xl font-semibold tracking-tight text-ink dark:text-zinc-100">Transaksi hari ini</h1>
            <p class="text-xs text-mute truncate">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
    </div>

    {{-- Ringkasan 2 Kolom --}}
    <div class="grid grid-cols-2 gap-2.5">
        <div class="rounded-[20px] border border-hairline-warm bg-white p-3.5 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:border-hairline/60 dark:bg-surface">
            <span class="text-xs text-mute">Setoran masuk</span>
            <p class="mt-1 text-base font-semibold text-primary tabular-nums">
                +<x-ui.rupiah :nilai="$totalSetoranMasuk" />
            </p>
        </div>

        <div class="rounded-[20px] border border-hairline-warm bg-white p-3.5 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:border-hairline/60 dark:bg-surface">
            <span class="text-xs text-mute">Penarikan tunai</span>
            <p class="mt-1 text-base font-semibold text-danger tabular-nums">
                &minus;<x-ui.rupiah :nilai="$totalPenarikanTunai" />
            </p>
        </div>
    </div>

    {{-- Filter Tab Pills --}}
    <div class="flex gap-2">
        <button type="button" wire:click="setFilter('semua')"
            class="rounded-full px-3.5 py-1.5 text-xs font-medium transition {{ $filter === 'semua' ? 'bg-primary text-on-primary' : 'bg-inset text-body hover:bg-hairline dark:bg-zinc-800 dark:text-zinc-300' }}">
            Semua
        </button>
        <button type="button" wire:click="setFilter('setoran')"
            class="rounded-full px-3.5 py-1.5 text-xs font-medium transition {{ $filter === 'setoran' ? 'bg-primary text-on-primary' : 'bg-inset text-body hover:bg-hairline dark:bg-zinc-800 dark:text-zinc-300' }}">
            Setoran
        </button>
        <button type="button" wire:click="setFilter('penarikan')"
            class="rounded-full px-3.5 py-1.5 text-xs font-medium transition {{ $filter === 'penarikan' ? 'bg-primary text-on-primary' : 'bg-inset text-body hover:bg-hairline dark:bg-zinc-800 dark:text-zinc-300' }}">
            Penarikan
        </button>
    </div>

    {{-- Daftar Transaksi --}}
    <div class="overflow-hidden rounded-[20px] border border-hairline-warm bg-white shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:border-hairline/60 dark:bg-surface">
        @forelse($transaksi as $item)
            <div class="flex items-center gap-3 border-b border-hairline-warm/60 p-3.5 last:border-0 dark:border-hairline/60">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full {{ $item['jenis'] === 'setoran' ? 'bg-primary-soft text-on-primary-soft' : 'bg-danger-soft text-danger' }}" aria-hidden="true">
                    @if($item['jenis'] === 'setoran')
                        <flux:icon.arrow-down-tray class="size-5" />
                    @else
                        <flux:icon.arrow-up-tray class="size-5" />
                    @endif
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5">
                        <p class="truncate text-sm font-semibold text-ink dark:text-zinc-100">{{ $item['nasabah_nama'] }}</p>
                        @if($item['status'] === 'dikoreksi')
                            <span class="rounded-full bg-warning-soft px-1.5 py-0.5 text-xs font-medium text-warning-deep">dikoreksi</span>
                        @endif
                    </div>
                    <p class="mt-0.5 truncate text-xs text-mute">
                        {{ $item['waktu'] }} · #{{ $item['no_bukti'] }} · {{ $item['produk_nama'] }}
                    </p>
                </div>

                <div class="text-right shrink-0">
                    <p class="text-sm font-semibold tabular-nums {{ $item['jenis'] === 'setoran' ? 'text-primary' : 'text-danger' }}">
                        {{ $item['jenis'] === 'setoran' ? '+' : "\u{2212}" }}<x-ui.rupiah :nilai="$item['nominal']" />
                    </p>
                    @if($item['jenis'] === 'setoran')
                        <a href="{{ route('struk.setoran', $item['id']) }}"
                           @click.prevent="$dispatch('buka-struk', { url: '{{ route('struk.setoran', $item['id']) }}' })"
                           class="text-xs font-medium text-primary hover:underline cursor-pointer">
                            Struk &rarr;
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-inset text-mute dark:bg-zinc-800" aria-hidden="true">
                    <flux:icon.document-text class="size-6" />
                </div>
                <p class="mt-3 text-sm font-semibold text-ink dark:text-zinc-100">Belum ada transaksi</p>
                <p class="mt-1 text-xs text-mute">Transaksi setoran dan penarikan hari ini akan tercatat di sini.</p>
            </div>
        @endforelse
    </div>
</div>
