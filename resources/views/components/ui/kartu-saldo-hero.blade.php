@props([
    'totalSaldo' => 0,
    'label' => 'Total saldo Anda',
    'bisaIntip' => true,
    'variant' => 'hero',
])

@if($variant === 'surface')
    <div data-test="hero-card" {{ $attributes->merge(['class' => 'relative rounded-[20px] border border-hairline bg-surface p-5 shadow-xs text-ink']) }}>
        <div data-test="kartu-saldo-hero" x-data="{ intip: false }">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-mute">{{ $label }}</span>
                @if($bisaIntip)
                    <button
                        type="button"
                        @click="intip = !intip"
                        class="inline-flex items-center gap-1.5 rounded-full bg-primary-soft px-3 py-1 text-xs font-semibold text-primary transition hover:bg-primary-soft/80 active:scale-95"
                        :aria-label="intip ? 'Tampilkan saldo' : 'Sembunyikan saldo'"
                        data-test="tombol-intip-saldo">
                        <span x-show="!intip" class="inline-flex items-center gap-1">
                            <flux:icon.eye class="size-3.5 text-primary" />
                            <span>Intip</span>
                        </span>
                        <span x-show="intip" x-cloak class="inline-flex items-center gap-1">
                            <flux:icon.eye-slash class="size-3.5 text-primary" />
                            <span>Tutup</span>
                        </span>
                    </button>
                @endif
            </div>

            <p class="mt-2 text-[32px] font-semibold leading-tight tracking-tight text-ink tabular-nums">
                <span x-show="!intip"><x-ui.rupiah :nilai="$totalSaldo" /></span>
                <span x-show="intip" x-cloak class="tracking-wider text-mute">Rp ••••••••</span>
            </p>

            {{ $slot }}
        </div>
    </div>
@else
    <x-ui.hero-card data-test="kartu-saldo-hero" x-data="{ intip: false }" {{ $attributes }}>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm text-on-hero-mute">{{ $label }}</span>
                @if($bisaIntip)
                    <button
                        type="button"
                        @click="intip = !intip"
                        class="inline-flex items-center gap-1 rounded-full bg-white/15 px-2.5 py-0.5 text-xs font-medium text-white transition hover:bg-white/25 active:scale-95"
                        :aria-label="intip ? 'Tampilkan saldo' : 'Sembunyikan saldo'"
                        data-test="tombol-intip-saldo">
                        <span x-show="!intip" class="inline-flex items-center gap-1">
                            <flux:icon.eye class="size-3.5" />
                            <span class="text-xs">Intip</span>
                        </span>
                        <span x-show="intip" x-cloak class="inline-flex items-center gap-1">
                            <flux:icon.eye-slash class="size-3.5" />
                            <span class="text-xs">Tutup</span>
                        </span>
                    </button>
                @endif
            </div>
        </div>

        <p class="mt-1 text-[34px] font-semibold leading-10 tracking-[-0.64px]">
            <span x-show="!intip"><x-ui.rupiah :nilai="$totalSaldo" /></span>
            <span x-show="intip" x-cloak class="tracking-wider">Rp ••••••••</span>
        </p>

        {{ $slot }}
    </x-ui.hero-card>
@endif
