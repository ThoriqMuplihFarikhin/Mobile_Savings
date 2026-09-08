<div class="mx-auto max-w-2xl">
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">Pengaturan</h1>
        <p class="mt-1 text-sm text-[#888888] dark:text-zinc-400">Kelola akun dan preferensi Anda.</p>
    </div>

    {{-- Profile Card --}}
    <div class="mb-6 rounded-2xl bg-[#171717] dark:bg-zinc-700 p-5 shadow-[0px_2px_2px_#0000000a,0px_8px_16px_-4px_#0000000a]">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white/20 text-lg font-semibold text-white">
                {{ auth()->user()->initials() }}
            </div>
            <div>
                <p class="text-base font-semibold text-white">{{ auth()->user()->name }}</p>
                <p class="mt-0.5 text-sm text-white/60">{{ auth()->user()->no_hp }}</p>
            </div>
        </div>
    </div>

    {{-- Section: Akun --}}
    <div class="mb-6">
        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-[#888888] dark:text-zinc-400">Akun</p>
        <div class="rounded-2xl bg-[#fafafa] dark:bg-zinc-700/50 shadow-[inset_0_0_0_1px_#ebebeb] dark:shadow-[inset_0_0_0_1px_#3f3f46]">
            {{-- Edit Profil --}}
            <a href="{{ route('profile.edit') }}" wire:navigate
               class="flex items-center gap-3 px-5 py-4 transition hover:bg-black/[0.02] dark:hover:bg-white/[0.05] border-b border-[#ebebeb] dark:border-zinc-600">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#d3e5ff] text-[#0761d1]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </div>
                <span class="flex-1 text-sm font-medium text-[#171717] dark:text-white">Edit Profil</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#a1a1a1] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </a>

            {{-- Ganti PIN --}}
            <a href="{{ route('security.edit') }}" wire:navigate
               class="flex items-center gap-3 px-5 py-4 transition hover:bg-black/[0.02] dark:hover:bg-white/[0.05]">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#ffefcf] text-[#ab570a]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                </div>
                <span class="flex-1 text-sm font-medium text-[#171717] dark:text-white">Ganti PIN</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#a1a1a1] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </a>
        </div>
    </div>

    {{-- Section: Preferensi --}}
    <div class="mb-6">
        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-[#888888] dark:text-zinc-400">Preferensi</p>
        <div class="rounded-2xl bg-[#fafafa] dark:bg-zinc-700/50 shadow-[inset_0_0_0_1px_#ebebeb] dark:shadow-[inset_0_0_0_1px_#3f3f46]">
            {{-- Notifikasi WhatsApp --}}
            <div class="flex items-center gap-3 px-5 py-4 border-b border-[#ebebeb] dark:border-zinc-600">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#dcf5e3] text-[#0a7a3d]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                </div>
                <span class="flex-1 text-sm font-medium text-[#171717] dark:text-white">Notifikasi WhatsApp</span>
                <button wire:click="toggleNotifikasiWa" type="button"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#171717]/20 dark:focus:ring-white/20 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 {{ $notifikasiWaAktif ? 'bg-[#0a7a3d]' : 'bg-[#d4d4d4]' }}">
                    <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out {{ $notifikasiWaAktif ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>

            {{-- Mode Gelap --}}
            <a href="{{ route('appearance.edit') }}" wire:navigate
               class="flex items-center gap-3 px-5 py-4 transition hover:bg-black/[0.02] dark:hover:bg-white/[0.05]">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#d3e5ff] text-[#0761d1]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                </div>
                <span class="flex-1 text-sm font-medium text-[#171717] dark:text-white">Mode Gelap</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#a1a1a1] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </a>
        </div>
    </div>

    {{-- Section: Lainnya --}}
    <div class="mb-6">
        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-[#888888] dark:text-zinc-400">Lainnya</p>
        <div class="rounded-2xl bg-[#fafafa] dark:bg-zinc-700/50 shadow-[inset_0_0_0_1px_#ebebeb] dark:shadow-[inset_0_0_0_1px_#3f3f46]">
            {{-- Bantuan --}}
            <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer"
               class="flex items-center gap-3 px-5 py-4 transition hover:bg-black/[0.02] dark:hover:bg-white/[0.05] border-b border-[#ebebeb] dark:border-zinc-600">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#d3e5ff] text-[#0761d1]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="flex-1 text-sm font-medium text-[#171717] dark:text-white">Bantuan</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#a1a1a1] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </a>

            {{-- Keluar --}}
            <button wire:click="logout" type="button"
                    class="flex w-full items-center gap-3 px-5 py-4 transition hover:bg-black/[0.02] dark:hover:bg-white/[0.05] text-left">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#f7d4d6] text-[#c50000]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                </div>
                <span class="flex-1 text-sm font-medium text-[#c50000]">Keluar</span>
            </button>
        </div>
    </div>
</div>
