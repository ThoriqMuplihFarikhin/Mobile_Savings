@props([])

@php
    /**
     * DESIGN.md §7 "Kartu, hero, dan catatan": hero-card berlatar primary-deep,
     * radius 24px, padding 20px, teks on-hero. Dekor datar (ilustrasi dekor-hero)
     * hanya di pojok kanan atas, aria-hidden, dan selalu berada di bawah lapisan
     * isi (z-10) sehingga teks tidak pernah menimpa koin atau lingkaran (§13).
     */
@endphp

<div data-test="hero-card" {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-3xl bg-primary-deep p-5 text-on-hero']) }}>
    <x-ui.ilustrasi nama="dekor-hero" :ukuran="156" class="pointer-events-none absolute -right-5 -top-6 z-0" />

    <div class="relative z-10">
        {{ $slot }}
    </div>
</div>
