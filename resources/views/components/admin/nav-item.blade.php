@props(['href', 'active' => false, 'badge' => null])

<a href="{{ $href }}" wire:navigate
   @class([
       'flex items-center gap-2.5 px-2.5 py-2 rounded-[10px] text-[13.5px] min-h-[38px] transition-colors',
       'bg-primary-soft text-on-primary-soft font-semibold dark:text-[#bbf7d0]' => $active,
       'text-ink hover:bg-surface-2 dark:hover:bg-surface-2' => ! $active,
   ])>
    {{ $slot }}
    @if($badge && $badge > 0)
        <span class="ml-auto inline-flex items-center justify-center rounded-full bg-warning-soft px-2 py-0.5 text-[11px] font-semibold text-warning-deep">
            {{ $badge }}
        </span>
    @endif
</a>
