@props([
    'label' => null,
    'title' => null,
    'value',
    'sub' => null,
    'change' => null,
    'variant' => 'default', // 'default', 'warn', 'error'
    'trend' => null,
    'href' => null,
])

@php
    $displayLabel = $label ?? $title ?? '';
    $displaySub = $sub ?? $change ?? null;
    $valueColor = match($variant) {
        'warn', 'warning', 'w' => 'text-warning-deep dark:text-warning-deep',
        'error', 'danger', 'e' => 'text-danger dark:text-danger',
        default => 'text-ink dark:text-ink',
    };
    $cardBase = 'rounded-[14px] border border-hairline bg-surface p-3.5 shadow-xs transition-colors block h-full';
    if ($href) {
        $cardBase .= ' hover:border-hairline-strong focus:outline-none focus:ring-2 focus:ring-primary/20';
    }
@endphp

@if($href)
    <a href="{{ $href }}" wire:navigate {{ $attributes->merge(['class' => $cardBase]) }}>
        <span class="block text-[13px] text-mute dark:text-subtle">{{ $displayLabel }}</span>
        <b class="mt-0.5 block text-[1.4rem] font-bold tracking-[-0.5px] tabular-nums leading-tight {{ $valueColor }}">
            {{ $value }}
        </b>
        @if($displaySub)
            <small class="mt-0.5 block text-xs text-mute dark:text-subtle">{{ $displaySub }}</small>
        @endif
    </a>
@else
    <div {{ $attributes->merge(['class' => $cardBase]) }}>
        <span class="block text-[13px] text-mute dark:text-subtle">{{ $displayLabel }}</span>
        <b class="mt-0.5 block text-[1.4rem] font-bold tracking-[-0.5px] tabular-nums leading-tight {{ $valueColor }}">
            {{ $value }}
        </b>
        @if($displaySub)
            <small class="mt-0.5 block text-xs text-mute dark:text-subtle">{{ $displaySub }}</small>
        @endif
    </div>
@endif

