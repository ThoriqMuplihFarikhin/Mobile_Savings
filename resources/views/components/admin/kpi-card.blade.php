@props([
    'label',
    'value',
    'sub' => null,
    'variant' => 'default', // 'default', 'warn', 'error'
])

@php
    $valueColor = match($variant) {
        'warn', 'warning' => 'text-warning-deep dark:text-warning-deep',
        'error', 'danger' => 'text-danger dark:text-danger',
        default => 'text-ink dark:text-ink',
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-[14px] border border-hairline bg-surface p-3.5 shadow-xs transition-colors']) }}>
    <span class="block text-[13px] text-mute dark:text-subtle">{{ $label }}</span>
    <b class="mt-0.5 block text-[1.4rem] font-semibold tracking-tight tabular-nums leading-tight {{ $valueColor }}">
        {{ $value }}
    </b>
    @if($sub)
        <small class="mt-0.5 block text-xs text-mute dark:text-subtle">{{ $sub }}</small>
    @endif
</div>
