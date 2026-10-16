@php
    $sudahAbsen = ! empty($sudahAbsenHariIni);
    $ikonCentang = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="size-6"><path d="M20 6L9 17l-5-5"/></svg>';
@endphp

<nav data-test="bottom-nav" aria-label="Navigasi utama"
     class="border-t border-hairline bg-white pb-[env(safe-area-inset-bottom)] shadow-[0_1px_2px_rgb(0_0_0_/_0.06)] dark:bg-surface">
    <div class="flex h-16 items-stretch">
        @foreach($items as $item)
            @if(!empty($isKolektor) && $isKolektor && $loop->index == 2)
                {{-- Tombol Absen di tengah: hijau datar 56px, tanpa cincin gradien --}}
                <a wire:key="nav-absen" href="{{ route('kolektor.absen.index') }}" wire:navigate
                   data-test="tombol-absen"
                   class="flex w-20 shrink-0 -mt-4 flex-col items-center gap-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                    <span class="flex size-14 items-center justify-center rounded-full bg-primary text-on-primary transition active:scale-95">
                        @if($sudahAbsen)
                            {!! $ikonCentang !!}
                        @else
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 11c1 3 1 5 0 8"/>
                                <path d="M9 8.5c1.5-1 3.5-1 5 0"/>
                                <path d="M7 12c0-1.5.5-3 2-4"/>
                                <path d="M16 12c0-3-2-5-4-5s-4 2-4 5c0 2 0 4 1 6"/>
                                <path d="M18 12c0-4-3-7-6-7s-6 3-6 7c0 1.5.2 3 .6 4"/>
                            </svg>
                        @endif
                    </span>
                    <span class="text-xs font-medium whitespace-nowrap text-ink">{{ $sudahAbsen ? 'Sudah absen' : 'Absen' }}</span>
                </a>
            @endif

            <a wire:key="nav-item-{{ $loop->index }}" href="{{ $item['url'] }}" wire:navigate
               class="flex-1 min-w-0 flex flex-col items-center justify-center gap-1 px-1 text-xs transition
                      {{ request()->routeIs($item['active']) ? 'font-medium text-primary' : 'font-normal text-mute' }}">
                <span class="shrink-0" aria-hidden="true">{!! $item['icon'] !!}</span>
                <span class="truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
