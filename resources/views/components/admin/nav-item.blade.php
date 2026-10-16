@props(['href', 'active' => false])

<a href="{{ $href }}" wire:navigate
   @class([
       'flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13.5px] border-l-2 transition-colors',
       'bg-inset text-ink border-gold dark:bg-inset dark:text-white' => $active,
       'text-mute dark:text-subtle border-transparent hover:bg-inset dark:hover:bg-white/[.04]' => !$active,
   ])>
    {{ $slot }}
</a>
