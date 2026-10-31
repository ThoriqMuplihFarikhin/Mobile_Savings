@props([
    'name' => null,
    'open' => false,
    'title',
    'message' => null,
    'tone' => 'default', // 'default' atau 'danger'
    'confirmText' => 'Simpan',
    'cancelText' => 'Batal',
    'confirmAction' => null,
    'cancelAction' => null,
])

@php
    $isDanger = in_array($tone, ['danger', 'error', 'd']);
    $confirmButtonClass = $isDanger
        ? 'bg-danger-soft text-danger border border-danger/20 hover:bg-danger hover:text-white'
        : 'bg-accent text-accent-foreground hover:opacity-90';
@endphp

<div
    @if($name)
        x-data="{ isOpen: false }"
        x-on:buka-dialog-{{ $name }}.window="isOpen = true"
        x-on:tutup-dialog-{{ $name }}.window="isOpen = false"
        x-on:keydown.escape.window="isOpen = false"
        x-show="isOpen"
    @else
        x-data
        x-on:keydown.escape.window="{{ $cancelAction ? $cancelAction : '' }}"
    @endif
    style="{{ $name ? 'display: none;' : '' }}"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-label="{{ $title }}"
>
    {{-- Backdrop --}}
    <div
        @if($cancelAction)
            wire:click="{{ $cancelAction }}"
        @elseif($name)
            @click="isOpen = false"
        @endif
        class="fixed inset-0 bg-black/45 transition-opacity cursor-pointer"
        aria-hidden="true"
    ></div>

    {{-- Panel Dialog --}}
    <div class="relative w-full max-w-[460px] rounded-[16px] border border-hairline bg-surface p-5 shadow-2xl transition-colors z-10 my-auto">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-ink leading-tight">
                    {{ $title }}
                </h3>
                @if($message)
                    <p class="mt-1 text-xs text-mute dark:text-subtle leading-relaxed">
                        {{ $message }}
                    </p>
                @endif
            </div>
            <button
                type="button"
                @if($cancelAction)
                    wire:click="{{ $cancelAction }}"
                @elseif($name)
                    @click="isOpen = false"
                @endif
                class="flex size-7 shrink-0 items-center justify-center rounded-[8px] text-mute transition hover:bg-surface-2 hover:text-ink cursor-pointer"
                aria-label="Tutup dialog"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        @if(!empty(trim((string)$slot)))
            <div class="mt-4 space-y-3">
                {{ $slot }}
            </div>
        @endif

        <div class="mt-5 flex items-center justify-end gap-2 pt-2 border-t border-hairline/60">
            <button
                type="button"
                @if($cancelAction)
                    wire:click="{{ $cancelAction }}"
                @elseif($name)
                    @click="isOpen = false"
                @endif
                class="min-h-11 h-[38px] rounded-[10px] border border-hairline bg-surface px-4 text-xs font-medium text-ink transition hover:bg-surface-2 cursor-pointer"
            >
                {{ $cancelText }}
            </button>
            <button
                type="button"
                @if($confirmAction)
                    wire:click="{{ $confirmAction }}"
                    wire:loading.attr="disabled"
                @endif
                class="min-h-11 h-[38px] rounded-[10px] px-4 text-xs font-semibold transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed {{ $confirmButtonClass }}"
            >
                {{ $confirmText }}
            </button>
        </div>
    </div>
</div>
