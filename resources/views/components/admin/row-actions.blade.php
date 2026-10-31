@props([
    'more' => null,
])

<div {{ $attributes->merge(['class' => 'flex items-center justify-end gap-1.5']) }} x-data="{ open: false }">
    {{ $slot }}

    @if(isset($more) && !empty(trim((string)$more)))
        <div class="relative inline-block text-left">
            <button
                type="button"
                @click="open = !open"
                @click.outside="open = false"
                class="flex size-8 items-center justify-center rounded-[10px] border border-hairline bg-surface text-mute transition hover:bg-surface-2 hover:text-ink active:scale-95 cursor-pointer"
                aria-label="Aksi lainnya"
            >
                <svg class="size-4" fill="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="5" r="1.75" />
                    <circle cx="12" cy="12" r="1.75" />
                    <circle cx="12" cy="19" r="1.75" />
                </svg>
            </button>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                style="display: none;"
                class="absolute right-0 z-30 mt-1 min-w-[180px] origin-top-right rounded-[12px] border border-hairline bg-surface p-1 shadow-xl transition-colors"
            >
                {{ $more }}
            </div>
        </div>
    @endif
</div>
