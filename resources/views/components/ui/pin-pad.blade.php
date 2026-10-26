@props([
    'portal' => 'nasabah',
    'locked' => false,
])

@php
    /**
     * DESIGN.md §7 "Keypad PIN": tombol 62px, rounded-2xl, border hairline,
     * latar surface-inset, teks 24px/600 tabular-nums.
     */
    $angkaList = [
        [1, 2, 3],
        [4, 5, 6],
        [7, 8, 9],
    ];
@endphp

<div data-test="pin-pad" {{ $attributes->merge(['class' => 'w-full max-w-[320px] mx-auto select-none']) }}>
    {{-- Titik PIN Masking --}}
    <x-ui.pin-dots />

    {{-- Grid Tombol Angka --}}
    <div class="grid grid-cols-3 gap-3">
        @foreach ($angkaList as $baris)
            @foreach ($baris as $angka)
                <button type="button"
                        @click="appendDigit({{ $angka }})"
                        :disabled="{{ $locked ? 'true' : 'isLocked' }} || isSubmitting"
                        class="h-[62px] rounded-2xl bg-surface-inset border border-hairline hover:bg-surface text-ink text-2xl font-semibold tabular-nums flex items-center justify-center transition active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed">
                    {{ $angka }}
                </button>
            @endforeach
        @endforeach

        {{-- Baris Terakhir: Peek PIN, Angka 0, Hapus --}}
        <button type="button"
                @click="peekPin()"
                :disabled="{{ $locked ? 'true' : 'isLocked' }} || isSubmitting || pin.length === 0"
                aria-label="Lihat PIN sementara"
                class="h-[62px] rounded-2xl bg-surface-inset border border-hairline hover:bg-surface text-mute text-xs font-medium flex items-center justify-center transition active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed">
            <span x-show="!showPin">Lihat</span>
            <span x-show="showPin" class="text-primary font-bold">Tutup</span>
        </button>

        <button type="button"
                @click="appendDigit(0)"
                :disabled="{{ $locked ? 'true' : 'isLocked' }} || isSubmitting"
                class="h-[62px] rounded-2xl bg-surface-inset border border-hairline hover:bg-surface text-ink text-2xl font-semibold tabular-nums flex items-center justify-center transition active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed">
            0
        </button>

        <button type="button"
                @click="deleteDigit()"
                :disabled="{{ $locked ? 'true' : 'isLocked' }} || isSubmitting || pin.length === 0"
                aria-label="Hapus digit terakhir"
                class="h-[62px] rounded-2xl bg-surface-inset border border-hairline hover:bg-surface text-mute hover:text-ink flex items-center justify-center transition active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414-6.414A2 2 0 0110.828 5H20a2 2 0 012 2v10a2 2 0 01-2 2h-9.172a2 2 0 01-1.414-.586L3 12z" />
            </svg>
        </button>
    </div>
</div>
