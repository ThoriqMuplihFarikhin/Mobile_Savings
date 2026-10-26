@props([
    'totalSaldo' => 0,
    'label' => 'Total saldo Anda',
    'bisaIntip' => true,
])

<x-ui.hero-card data-test="kartu-saldo-hero" x-data="{ intip: false }" {{ $attributes }}>
    <div class="flex items-center justify-between">
        <p class="text-sm text-on-hero-mute">{{ $label }}</p>
        @if($bisaIntip)
            <button
                type="button"
                @click="intip = !intip"
                class="flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 text-xs font-medium text-white/90 hover:bg-white/20 transition active:scale-95"
                :aria-label="intip ? 'Tampilkan saldo' : 'Sembunyikan saldo'"
                data-test="tombol-intip-saldo">
                <span x-show="!intip" class="flex items-center gap-1">
                    <flux:icon.eye class="size-3.5" />
                    <span class="text-xs">Intip</span>
                </span>
                <span x-show="intip" x-cloak class="flex items-center gap-1">
                    <flux:icon.eye-slash class="size-3.5" />
                    <span class="text-xs">Tutup</span>
                </span>
            </button>
        @endif
    </div>

    <p class="mt-1 text-[34px] font-semibold leading-10 tracking-[-0.64px]">
        <span x-show="!intip"><x-ui.rupiah :nilai="$totalSaldo" /></span>
        <span x-show="intip" x-cloak class="tracking-wider">Rp ••••••••</span>
    </p>

    {{ $slot }}
</x-ui.hero-card>
