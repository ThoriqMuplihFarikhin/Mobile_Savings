@props([
    'panjang' => 6,
])

@php
    /**
     * DESIGN.md §7 "Titik PIN": lingkaran 20px berjarak 12px.
     * Terisi = bulatan penuh ink/primary dengan animasi pop;
     * Error = animasi shake horizontal.
     */
@endphp

<div data-test="pin-dots"
     class="flex items-center justify-center gap-3 my-6"
     :class="{ 'animate-shake text-danger': hasError }">
    @for ($i = 0; $i < $panjang; $i++)
        <div class="h-5 w-5 rounded-full border-2 transition-all duration-200 flex items-center justify-center"
             :class="{
                 'border-primary bg-primary text-white scale-105': pin.length > {{ $i }},
                 'border-hairline-strong bg-transparent': pin.length <= {{ $i }}
             }">
            <template x-if="pin.length > {{ $i }} && showPin">
                <span class="text-xs font-bold leading-none" x-text="pin[{{ $i }}]"></span>
            </template>
            <template x-if="pin.length > {{ $i }} && !showPin">
                <span class="h-2 w-2 rounded-full bg-white block"></span>
            </template>
        </div>
    @endfor
</div>
