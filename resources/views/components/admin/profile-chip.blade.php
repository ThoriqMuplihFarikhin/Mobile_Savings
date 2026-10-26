@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2.5']) }}>
    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-accent text-xs font-semibold text-accent-foreground dark:bg-inset dark:text-ink">
        {{ substr(auth()->user()->name, 0, 1) }}
    </div>
    <div class="min-w-0 flex-1 leading-tight">
        <p class="truncate text-[12.5px] font-medium text-ink dark:text-white">{{ auth()->user()->name }}</p>
        <x-peran-chip />
    </div>
</div>
