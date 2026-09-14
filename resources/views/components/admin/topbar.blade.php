@props(['title' => '', 'subtitle' => ''])

<header class="flex items-center justify-between gap-3 px-4 md:px-7 py-3 md:py-4
               bg-surface dark:bg-surface-alt border-b border-border transition-colors">

    {{-- Hamburger (mobile) --}}
    <button @click="$store.ui.toggleSidebar()" class="lg:hidden flex h-9 w-9 items-center justify-center rounded-md
            bg-zinc-100 dark:bg-slate-900 text-zinc-600 dark:text-slate-300 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    <div class="min-w-0">
        <h1 class="font-serif text-base md:text-xl font-semibold truncate text-text dark:text-white">{{ $title }}</h1>
        @if($subtitle)
            <p class="hidden sm:block text-xs text-text-muted dark:text-slate-400">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="flex items-center gap-2 md:gap-3">
        {{-- Search: icon on mobile, input on desktop --}}
        <button class="md:hidden flex h-9 w-9 items-center justify-center rounded-md border border-border
                       text-text-muted dark:text-slate-400 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
        </button>
        <div class="hidden md:flex items-center gap-2 bg-zinc-50 dark:bg-slate-900
                    border border-border rounded-md px-3 py-2 w-56 lg:w-64 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input type="text" placeholder="Cari nasabah, kolektor..."
                   class="bg-transparent text-sm outline-none w-full placeholder:text-zinc-400 dark:placeholder:text-slate-500 text-text dark:text-white" />
        </div>

        <x-admin.theme-toggle />

        {{-- Notification --}}
        <button class="relative flex h-9 w-9 items-center justify-center rounded-md border border-border
                       text-text-muted dark:text-slate-400 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <span class="absolute top-1.5 right-1.5 h-1.5 w-1.5 rounded-full bg-danger"></span>
        </button>

        {{-- Tambah Nasabah (desktop only) --}}
        <a href="/admin/nasabah" wire:navigate
           class="hidden lg:flex items-center gap-2 bg-navy-950 text-white
                  rounded-md px-4 py-2 text-sm font-medium transition hover:opacity-90">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Nasabah
        </a>

        {{-- Profile chip (desktop only) --}}
        <div class="hidden lg:flex items-center gap-2.5">
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-navy-850 text-sm font-semibold text-white">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <span class="text-sm font-medium text-text dark:text-white">{{ auth()->user()->name }}</span>
        </div>
    </div>
</header>
