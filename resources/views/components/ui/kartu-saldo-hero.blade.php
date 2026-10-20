@props([
    'totalSaldo' => 0,
    'label' => 'Total saldo Anda',
])

<x-ui.hero-card data-test="kartu-saldo-hero" {{ $attributes }}>
    <p class="text-sm text-on-hero-mute">{{ $label }}</p>
    <p class="mt-1 text-[34px] font-semibold leading-10 tracking-[-0.64px]">
        <x-ui.rupiah :nilai="$totalSaldo" />
    </p>

    {{ $slot }}
</x-ui.hero-card>
