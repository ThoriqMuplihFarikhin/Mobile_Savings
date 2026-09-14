<div class="relative pb-2 pt-3">
    @if(!empty($isKolektor) && $isKolektor)
    <div class="absolute -top-1 inset-x-0 flex justify-center z-50 pointer-events-none">
        <div class="flex flex-col items-center">
            {{-- Lapisan luar: gap terhadap background --}}
            <div class="w-[58px] h-[58px] rounded-full bg-white dark:bg-zinc-800 flex items-center justify-center shadow-lg transition-colors">
                {{-- Cincin gradient --}}
                <div class="w-[50px] h-[50px] rounded-full p-[2.5px] bg-gradient-to-tr from-emerald-500 via-emerald-400 to-teal-400">
                    {{-- Tombol utama --}}
                    <a href="{{ route('kolektor.absen.index') }}" wire:navigate
                       class="pointer-events-auto w-full h-full rounded-full flex items-center justify-center
                              bg-gradient-to-br from-emerald-500 to-emerald-700
                              shadow-[inset_0_2px_4px_rgba(255,255,255,0.3),inset_0_-2px_4px_rgba(0,0,0,0.2)]
                              transition-transform duration-200 active:scale-90">
                        <svg class="w-[21px] h-[21px] text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 11c1 3 1 5 0 8"/>
                            <path d="M9 8.5c1.5-1 3.5-1 5 0"/>
                            <path d="M7 12c0-1.5.5-3 2-4"/>
                            <path d="M16 12c0-3-2-5-4-5s-4 2-4 5c0 2 0 4 1 6"/>
                            <path d="M18 12c0-4-3-7-6-7s-6 3-6 7c0 1.5.2 3 .6 4"/>
                        </svg>
                    </a>
                </div>
            </div>
            {{-- Label kecil di bawah tombol --}}
            <span class="text-[9.5px] font-bold tracking-wider text-[#171717] dark:text-zinc-200 bg-white/90 dark:bg-zinc-800/90 backdrop-blur-sm px-2.5 py-0.5 rounded-full -mt-1 shadow-md border border-zinc-200/50 dark:border-zinc-700/50">
                ABSEN
            </span>
        </div>
    </div>
    @endif

    <div class="flex items-center gap-1 bg-[#171717]/95 dark:bg-zinc-950/95 backdrop-blur-xl rounded-full p-1.5 shadow-[0_8px_32px_rgba(0,0,0,0.3)] border border-white/10 dark:border-zinc-800">
        @foreach($items as $item)
            @if(!empty($isKolektor) && $isKolektor && $loop->index == 2)
                <div class="w-[58px] shrink-0 pointer-events-none"></div>
            @endif

            <a wire:key="nav-item-{{ $loop->index }}" href="{{ $item['url'] }}" wire:navigate
               class="flex-1 flex items-center justify-center gap-1.5 py-2.5 px-2 rounded-full transition-all duration-300 ease-out active:scale-95
                      {{ request()->routeIs($item['active']) ? 'bg-white text-[#171717] dark:bg-zinc-100 dark:text-zinc-900 shadow-md flex-[1.4]' : 'flex-1 text-white/60 hover:text-white' }}">
                <span class="shrink-0 transition-colors duration-300 {{ request()->routeIs($item['active']) ? 'text-[#171717] dark:text-zinc-900' : 'text-white/60' }}">
                    {!! $item['icon'] !!}
                </span>
                @if(request()->routeIs($item['active']))
                    <span class="text-[11.5px] font-bold text-[#171717] dark:text-zinc-900 whitespace-nowrap">{{ $item['label'] }}</span>
                @endif
            </a>
        @endforeach
    </div>
</div>
