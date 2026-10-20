@props([
    'portal' => 'nasabah',
    'locked' => false,
])

@php
    $aksen = \App\Support\PortalLogin::aksen($portal);
@endphp

<div class="flex flex-col items-center gap-6 w-full max-w-xs mx-auto">
    <!-- Indikator 6 Titik PIN -->
    <div class="flex items-center justify-center gap-3 sm:gap-4 py-2"
         :class="{ 'auth-pin-shake': hasError }"
         role="status"
         aria-live="polite">
        <template x-for="i in 6" :key="i">
            <div class="relative flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-2xl border transition-all duration-150"
                 :class="{
                     'border-primary bg-primary text-white font-bold text-lg auth-pin-pop': pin.length >= i && !hasError,
                     'border-danger bg-danger-soft text-danger-deep font-bold text-lg': hasError && pin.length >= i,
                     'border-hairline-strong bg-white text-transparent': pin.length < i
                 }">
                <!-- Tampilkan angka saat mode Lihat aktif, atau titik peluru saat terisi -->
                <span x-show="pin.length >= i && showPin"
                      x-text="pin[i - 1]"
                      class="text-base sm:text-lg font-semibold tabular-nums"></span>
                <span x-show="pin.length >= i && !showPin"
                      class="inline-block h-3.5 w-3.5 rounded-full bg-current"></span>
            </div>
        </template>
    </div>

    <!-- Papan Tombol Angka (3x4 Keypad) -->
    <div class="grid grid-cols-3 gap-3 sm:gap-4 w-full" role="group" aria-label="Papan tombol angka PIN">
        @foreach (['1', '2', '3', '4', '5', '6', '7', '8', '9'] as $digit)
            <button type="button"
                    @click="appendDigit('{{ $digit }}')"
                    :disabled="isLocked || isSubmitting"
                    aria-label="Angka {{ $digit }}"
                    class="flex h-14 w-full sm:h-16 items-center justify-center rounded-2xl border border-hairline bg-surface text-xl sm:text-2xl font-semibold tabular-nums text-ink transition hover:bg-surface-inset active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-primary/20">
                {{ $digit }}
            </button>
        @endforeach

        <!-- Tombol Lihat (Sekilas 1.2 detik) -->
        <button type="button"
                @click="peekPin()"
                :disabled="isLocked || pin.length === 0 || isSubmitting"
                aria-label="Lihat PIN sekilas"
                class="flex h-14 w-full sm:h-16 flex-col items-center justify-center rounded-2xl border border-hairline bg-surface text-xs font-medium text-mute transition hover:bg-surface-inset hover:text-ink active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-primary/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span class="text-[11px]">Lihat</span>
        </button>

        <!-- Angka 0 -->
        <button type="button"
                @click="appendDigit('0')"
                :disabled="isLocked || isSubmitting"
                aria-label="Angka 0"
                class="flex h-14 w-full sm:h-16 items-center justify-center rounded-2xl border border-hairline bg-surface text-xl sm:text-2xl font-semibold tabular-nums text-ink transition hover:bg-surface-inset active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-primary/20">
            0
        </button>

        <!-- Tombol Hapus (Backspace) -->
        <button type="button"
                @click="deleteDigit()"
                :disabled="isLocked || pin.length === 0 || isSubmitting"
                aria-label="Hapus satu angka"
                class="flex h-14 w-full sm:h-16 flex-col items-center justify-center rounded-2xl border border-hairline bg-surface text-xs font-medium text-mute transition hover:bg-surface-inset hover:text-danger active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-primary/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414-6.414A2 2 0 0110.828 5H20a2 2 0 012 2v10a2 2 0 01-2 2h-9.172a2 2 0 01-1.414-.586L3 12z" />
            </svg>
            <span class="text-[11px]">Hapus</span>
        </button>
    </div>
</div>
