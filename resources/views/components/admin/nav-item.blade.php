@props(['href', 'active' => false])

<a href="{{ $href }}" wire:navigate
   @class([
       'flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13.5px] border-l-2 transition-colors',
       'bg-sidebar-active text-sidebar-text-active border-gold dark:bg-slate-800 dark:text-white' => $active,
       'text-sidebar-text dark:text-slate-400 border-transparent hover:bg-slate-50 dark:hover:bg-white/[.04]' => !$active,
   ])>
    {{ $slot }}
</a>
