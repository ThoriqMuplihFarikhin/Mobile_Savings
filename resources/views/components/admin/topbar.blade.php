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
        {{-- Tombol Tambah Nasabah Cepat --}}
        <a
            href="{{ route('admin.registrasi.index') }}"
            wire:navigate
            class="hidden sm:inline-flex items-center gap-1.5 h-[38px] px-3.5 rounded-[10px] bg-accent text-accent-foreground text-xs font-semibold hover:opacity-90 transition shadow-xs shrink-0 cursor-pointer"
        >
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>Tambah Nasabah</span>
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
