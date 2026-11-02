@props(['title' => '', 'subtitle' => ''])

<header class="admin-topbar sticky top-0 z-20 flex items-center gap-2.5 px-4 py-2.5 border-b border-hairline transition-colors">
    {{-- Hamburger (mobile < lg) --}}
    <button
        type="button"
        @click="$store.ui.toggleSidebar()"
        aria-label="Buka menu"
        class="lg:hidden flex size-[38px] items-center justify-center rounded-[10px] border border-hairline bg-surface text-mute hover:bg-surface-2 hover:text-ink transition-colors cursor-pointer shrink-0"
    >
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    {{-- Pencarian Global --}}
    <div class="flex-1 max-w-[460px]">
        <livewire:admin.search wire:key="topbar-search" />
    </div>

    <div class="flex items-center gap-2 md:gap-3 ml-auto">
        @php
            $totalAntreanTopbar = \App\Support\AntreanPersetujuan::total();
        @endphp

        {{-- Tombol Notifikasi --}}
        <a
            href="{{ route('admin.persetujuan.index') }}"
            wire:navigate
            aria-label="{{ $totalAntreanTopbar > 0 ? $totalAntreanTopbar . ' antrean notifikasi' : 'Notifikasi' }}"
            class="relative flex size-[38px] items-center justify-center rounded-[10px] border border-hairline bg-surface text-mute hover:bg-surface-2 hover:text-ink transition-colors cursor-pointer shrink-0"
        >
            <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            @if($totalAntreanTopbar > 0)
                <span class="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-accent px-1 text-[10px] font-bold text-accent-foreground shadow-xs">
                    {{ $totalAntreanTopbar > 99 ? '99+' : $totalAntreanTopbar }}
                </span>
            @else
                <span class="sr-only bg-accent">Notifikasi</span>
            @endif
        </a>

        {{-- Tombol Tema --}}
        <x-admin.theme-toggle />

        {{-- Chip peran portal --}}
        <x-peran-chip class="hidden sm:inline-flex lg:hidden" />

        {{-- Chip Profil --}}
        <div class="flex items-center gap-2 shrink-0">
            <div class="flex size-[34px] items-center justify-center rounded-full bg-primary-soft text-xs font-semibold text-on-primary-soft select-none border border-primary/20">
                {{ auth()->user()?->initials() ?? 'AD' }}
            </div>
            <span class="hidden md:inline-block text-xs font-medium text-ink max-w-[120px] truncate">
                {{ auth()->user()?->name ?? 'Admin' }}
            </span>
        </div>
    </div>
</header>
