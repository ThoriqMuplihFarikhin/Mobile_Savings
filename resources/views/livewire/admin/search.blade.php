<div class="relative" wire:click.away="closeResults">
    <div class="relative">
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
        </svg>
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nasabah, kolektor..."
            aria-label="Cari nasabah atau kolektor"
            autocomplete="off"
            class="h-[38px] w-full rounded-[10px] border border-hairline bg-surface pl-9 pr-3 text-xs text-ink placeholder:text-mute focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent transition-colors"
        />
    </div>

    @if($showResults && count($results) > 0)
        <div class="absolute left-0 right-0 top-full z-50 mt-1.5 overflow-hidden rounded-[12px] border border-hairline bg-surface shadow-xl">
            @foreach($results as $result)
                <a href="{{ route('admin.nasabah.index') }}?search={{ urlencode($result['name']) }}"
                   class="flex items-center gap-2.5 px-3.5 py-2.5 text-xs transition hover:bg-surface-2 border-b border-hairline/60 last:border-b-0">
                    <div class="flex size-7 items-center justify-center rounded-full bg-primary-soft text-[11px] font-semibold text-on-primary-soft shrink-0">
                        {{ strtoupper(substr($result['name'], 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium text-ink">{{ $result['name'] }}</p>
                    </div>
                </a>
            @endforeach
            <a href="{{ route('admin.nasabah.index') }}?search={{ urlencode($search) }}"
               class="block border-t border-hairline px-3.5 py-2 text-center text-xs font-medium text-ink hover:bg-surface-2 transition">
                Lihat semua hasil
            </a>
        </div>
    @endif
</div>
