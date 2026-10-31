@props([
    'icon' => null,
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-11 px-4 text-mute dark:text-subtle']) }}>
    <div class="size-14 rounded-full bg-surface-2 border border-hairline/60 flex items-center justify-center mx-auto mb-2.5 text-mute">
        @if($icon)
            <div class="size-6 flex items-center justify-center">{!! $icon !!}</div>
        @else
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        @endif
    </div>
    <b class="block text-sm font-semibold text-ink">{{ $title }}</b>
    @if($description)
        <span class="mt-1 block text-xs text-mute dark:text-subtle max-w-sm mx-auto leading-relaxed">{{ $description }}</span>
    @endif
    @if(!empty(trim((string)$slot)))
        <div class="mt-3 flex justify-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
