<nav class="absolute bottom-0 inset-x-0 z-40 flex items-end bg-white border-t border-[#ebebeb] dark:bg-zinc-800 dark:border-zinc-700 px-2 pb-[calc(0.5rem+env(safe-area-inset-bottom))] pt-2">
    @foreach($items as $item)
        @if($item['floating'] ?? false)
            {{-- Floating Action Button --}}
            <a href="{{ $item['url'] }}" wire:navigate
               class="relative flex flex-col items-center -mt-7 z-10">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#171717] text-white shadow-lg ring-4 ring-white dark:bg-white dark:text-zinc-900 dark:ring-zinc-800 transition hover:scale-105">
                    {!! $item['icon'] !!}
                </div>
                <span class="mt-1 text-[10px] font-medium text-[#171717] dark:text-white">{{ $item['label'] }}</span>
            </a>
        @else
            {{-- Regular Flat Item --}}
            <a href="{{ $item['url'] }}" wire:navigate
               class="flex-1 flex flex-col items-center gap-1 py-1 {{ request()->routeIs($item['active']) ? 'text-[#171717] dark:text-white' : 'text-[#a1a1a1] dark:text-zinc-400' }}">
                {{-- Dot Indicator --}}
                @if(request()->routeIs($item['active']))
                    <span class="mb-0.5 h-1 w-1 rounded-full bg-[#171717] dark:bg-white"></span>
                @else
                    <span class="mb-0.5 h-1 w-1"></span>
                @endif
                {{-- Icon: filled if active, outline if not --}}
                @if(request()->routeIs($item['active']) && isset($item['iconFilled']))
                    {!! $item['iconFilled'] !!}
                @else
                    {!! $item['icon'] !!}
                @endif
                <span class="text-[11px] {{ request()->routeIs($item['active']) ? 'font-medium' : '' }}">{{ $item['label'] }}</span>
            </a>
        @endif
    @endforeach
</nav>
