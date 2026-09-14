@props(['icon' => null, 'title', 'description' => null])

<div class="flex flex-col items-center justify-center gap-2 text-center py-10 text-slate-400 dark:text-slate-500">
    @if($icon)
        <div class="w-8 h-8">{!! $icon !!}</div>
    @endif
    <div class="text-[13px] font-medium text-navy-900 dark:text-slate-200">{{ $title }}</div>
    @if($description)
        <div class="text-[11.5px] max-w-[220px]">{{ $description }}</div>
    @endif
</div>
