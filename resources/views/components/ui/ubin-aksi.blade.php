@props([
    'label' => '',
    'kategori' => 'hijau',
    'href' => null,
])

@php
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" wire:navigate @else type="button" @endif
    data-test="ubin-aksi"
    {{ $attributes->merge(['class' => 'flex flex-col items-center gap-2 group p-2 rounded-2xl hover:bg-surface-inset/60 transition active:scale-95']) }}>
    <x-ui.tile :kategori="$kategori" :ukuran="56" class="shadow-sm group-hover:shadow transition">
        {{ $slot }}
    </x-ui.tile>
    <span class="text-xs font-medium text-ink group-hover:text-primary transition text-center">{{ $label }}</span>
</{{ $tag }}>
