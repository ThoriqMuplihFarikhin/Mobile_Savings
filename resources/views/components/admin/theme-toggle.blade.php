<button x-data @click="$store.ui.toggleTheme()" type="button"
        aria-label="Ganti tema"
        class="flex size-[38px] items-center justify-center rounded-[10px] border border-hairline
               bg-surface text-mute hover:bg-surface-2 hover:text-ink transition-colors cursor-pointer shrink-0">
    {{-- Sun icon (shown in dark mode) --}}
    <svg x-show="$store.ui.isDark" xmlns="http://www.w3.org/2000/svg" class="size-[18px] text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
    {{-- Moon icon (shown in light mode) --}}
    <svg x-show="!$store.ui.isDark" xmlns="http://www.w3.org/2000/svg" class="size-[18px] text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
    </svg>
</button>
