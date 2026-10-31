@props([
    'title',
    'subtitle' => null,
    'breadcrumb' => null,
])

<div {{ $attributes->merge(['class' => 'mb-4 flex flex-wrap items-start justify-between gap-3']) }}>
    <div class="min-w-0">
        @if ($breadcrumb)
            <div class="mb-1 text-[13px] text-mute dark:text-subtle">{{ $breadcrumb }}</div>
        @endif
        <h1 class="text-2xl font-semibold tracking-[-0.5px] leading-tight text-ink">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-0.5 text-sm text-mute dark:text-subtle">{{ $subtitle }}</p>
        @endif
    </div>
    @if (isset($actions) || !empty(trim((string)$slot)))
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions ?? $slot }}
        </div>
    @endif
</div>
