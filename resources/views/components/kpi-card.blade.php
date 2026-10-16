@props(['label', 'value', 'delta' => null, 'trend' => 'neutral'])

<div class="rounded-lg border border-border bg-surface p-4 transition-colors">
    <div class="mb-3 flex items-start justify-between">
        <div class="flex h-8 w-8 items-center justify-center rounded-md
                    bg-inset text-ink">
            {{ $slot }}
        </div>
        @if($delta)
            <span @class([
                'text-xs font-semibold px-2 py-0.5 rounded-full',
                'text-success bg-success-soft' => $trend === 'up',
                'text-danger bg-danger-soft' => $trend === 'down',
                'text-mute bg-inset' => $trend === 'neutral',
            ])>{{ $delta }}</span>
        @endif
    </div>
    <div class="mb-1 text-xs text-mute">{{ $label }}</div>
    <div class="text-2xl font-semibold text-ink">{{ $value }}</div>
</div>
