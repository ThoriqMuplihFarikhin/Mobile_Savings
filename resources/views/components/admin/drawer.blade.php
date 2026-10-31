@props([
    'name',
    'title' => '',
    'wide' => false,
])

<div
    x-data="{ open: false }"
    x-on:buka-drawer-{{ $name }}.window="open = true"
    x-on:tutup-drawer-{{ $name }}.window="open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    style="display: none;"
    class="fixed inset-0 z-50 overflow-hidden"
    role="dialog"
    aria-modal="true"
    aria-label="{{ $title }}"
>
    {{-- Scrim / Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-black/45 transition-opacity cursor-pointer"
        aria-hidden="true"
    ></div>

    <div class="fixed inset-y-0 right-0 flex max-w-full pl-6 sm:pl-10">
        <div
            x-show="open"
            x-transition:enter="transform transition ease-out duration-250"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            @class([
                'w-screen flex flex-col bg-surface border-l border-hairline shadow-2xl transition-colors',
                'max-w-2xl lg:max-w-3xl' => $wide,
                'max-w-md lg:max-w-[560px]' => ! $wide,
            ])
        >
            {{-- Drawer Header --}}
            <div class="flex items-center justify-between border-b border-hairline px-5 py-4 shrink-0 bg-surface">
                <h3 class="text-base font-semibold text-ink truncate">
                    {{ $title }}
                </h3>
                <button
                    type="button"
                    @click="open = false"
                    class="flex size-8 shrink-0 items-center justify-center rounded-[10px] border border-hairline bg-surface-2 text-mute transition hover:bg-hairline hover:text-ink active:scale-95 cursor-pointer"
                    aria-label="Tutup panel"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Drawer Content --}}
            <div class="flex-1 overflow-y-auto p-5 space-y-4 bg-surface">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
