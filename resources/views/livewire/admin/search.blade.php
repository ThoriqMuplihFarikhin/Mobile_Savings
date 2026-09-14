<div class="relative" wire:click.away="closeResults">
    <div class="relative">
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-text-muted dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
        </svg>
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nasabah..."
            class="h-10 w-full rounded-lg border border-border bg-white pl-10 pr-4 text-sm text-text placeholder:text-text-muted focus:border-navy-950 focus:outline-none focus:ring-2 focus:ring-navy-950/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-400 dark:focus:border-white"
        />
    </div>

    @if($showResults && count($results) > 0)
        <div class="absolute left-0 right-0 top-full z-50 mt-1 overflow-hidden rounded-lg border border-border bg-white shadow-lg dark:border-slate-700 dark:bg-slate-800">
            @foreach($results as $result)
                <a href="{{ route('admin.nasabah.index') }}?search={{ urlencode($result['name']) }}"
                   class="flex items-center gap-3 px-4 py-2.5 text-sm transition hover:bg-zinc-50 dark:hover:bg-slate-700">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-navy-950 text-xs font-semibold text-white dark:bg-white dark:text-navy-950">
                        {{ strtoupper(substr($result['name'], 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium text-text dark:text-white">{{ $result['name'] }}</p>
                        <p class="truncate text-xs text-text-muted dark:text-slate-400">{{ $result['no_hp'] }}</p>
                    </div>
                </a>
            @endforeach
            <a href="{{ route('admin.nasabah.index') }}?search={{ urlencode($search) }}"
               class="block border-t border-border px-4 py-2.5 text-center text-xs font-medium text-navy-950 transition hover:bg-zinc-50 dark:border-slate-700 dark:text-white dark:hover:bg-slate-700">
                Lihat semua hasil
            </a>
        </div>
    @endif
</div>
