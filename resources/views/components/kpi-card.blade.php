@props(['label', 'value', 'delta' => null, 'trend' => 'neutral'])

<div class="rounded-lg border border-border bg-surface p-4 transition-colors">
    <div class="mb-3 flex items-start justify-between">
        <div class="flex h-8 w-8 items-center justify-center rounded-md
                    bg-zinc-100 text-navy-800 dark:bg-slate-800 dark:text-gold-bright">
            {{ $slot }}
        </div>
        @if($delta)
            <span @class([
                'text-[11px] font-semibold px-2 py-0.5 rounded-full',
                'text-success bg-success-soft' => $trend === 'up',
                'text-danger bg-danger-soft' => $trend === 'down',
                'text-text-muted bg-zinc-100 dark:text-slate-400 dark:bg-slate-800' => $trend === 'neutral',
            ])>{{ $delta }}</span>
        @endif
    </div>
    <div class="mb-1 text-xs text-text-muted dark:text-slate-400">{{ $label }}</div>
    <div class="font-serif text-[22px] font-semibold text-text dark:text-white">{{ $value }}</div>
</div>
