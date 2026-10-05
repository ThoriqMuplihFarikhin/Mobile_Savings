<div class="mx-auto max-w-2xl space-y-6 pb-6">
    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Pengaturan Kolektor</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Kelola profil, tugas harian, dan preferensi akun Anda.</p>
    </div>

    {{-- Profile Card Header --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 p-6 text-white shadow-xl dark:from-zinc-950 dark:to-zinc-900 border border-zinc-800 dark:border-zinc-800/80">
        <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-blue-500/10 blur-2xl pointer-events-none"></div>
        
        <div class="relative flex items-center gap-4">
            <div class="relative">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-xl font-bold text-white shadow-lg shadow-blue-500/20">
                    {{ auth()->user()->initials() }}
                </div>
                <div class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 ring-2 ring-zinc-900" title="Petugas Aktif">
                    <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                </div>
            </div>
            
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <h2 class="truncate text-lg font-bold text-white">{{ auth()->user()->name }}</h2>
                    <span class="inline-flex items-center rounded-md bg-blue-400/10 px-2 py-0.5 text-xs font-semibold text-blue-400 ring-1 ring-inset ring-blue-400/20">
                        Kolektor
                    </span>
                </div>
                <p class="mt-1 text-sm text-zinc-400 font-mono tracking-wide">{{ auth()->user()->no_hp ?? '-' }}</p>
                <p class="mt-0.5 text-xs text-zinc-500">Petugas lapangan aktif</p>
            </div>
        </div>
    </div>

    @if ($adaKasBelumDisetor)
        {{-- Warning Banner: kas belum disetor ke kantor --}}
        <div class="flex items-start gap-3.5 rounded-3xl bg-amber-50 dark:bg-amber-950/40 p-5 border border-amber-200/80 dark:border-amber-800/60 shadow-sm">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-amber-900 dark:text-amber-200">Masih Ada Kas Belum Disetor</p>
                <p class="mt-0.5 text-xs text-amber-700 dark:text-amber-300/80">Segera lakukan penyetoran kas hasil tagihan ke kasir kantor melalui menu Setor Kantor.</p>
                <a href="{{ route('kolektor.setor-kantor.index') }}" wire:navigate class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 dark:text-amber-300 hover:underline">
                    Buka Setor Kantor
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            </div>
        </div>
    @endif

    {{-- Section: Akun --}}
    <div>
        <p class="mb-2.5 px-1 text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Akun & Keamanan</p>
        <div class="overflow-hidden rounded-3xl bg-white dark:bg-zinc-800 shadow-sm border border-zinc-100 dark:border-zinc-700/60 divide-y divide-zinc-100 dark:divide-zinc-700/60">
            {{-- Edit Profil --}}
            <a href="{{ route('profile.edit') }}" wire:navigate
               class="group flex items-center gap-3.5 px-5 py-4 transition hover:bg-zinc-50 dark:hover:bg-zinc-700/40">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400 group-hover:scale-105 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">Edit Profil</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Ubah nama, nomor HP, dan biodata</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-400 dark:text-zinc-500 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>

            {{-- Ganti PIN --}}
            <a href="{{ route('security.edit') }}" wire:navigate
               class="group flex items-center gap-3.5 px-5 py-4 transition hover:bg-zinc-50 dark:hover:bg-zinc-700/40">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 group-hover:scale-105 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">Ganti PIN / Password</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Keamanan akun dan kata sandi login</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-400 dark:text-zinc-500 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>
    </div>

    {{-- Section: Penarikan Offline --}}
    <div>
        <p class="mb-2.5 px-1 text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Penarikan Offline</p>
        <div class="overflow-hidden rounded-3xl bg-white dark:bg-zinc-800 shadow-sm border border-zinc-100 dark:border-zinc-700/60 divide-y divide-zinc-100 dark:divide-zinc-700/60">
            {{-- Verifikasi Penarikan --}}
            <a href="{{ route('kolektor.verifikasi-penarikan.index') }}" wire:navigate
               class="group flex items-center gap-3.5 px-5 py-4 transition hover:bg-zinc-50 dark:hover:bg-zinc-700/40">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 group-hover:scale-105 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">Verifikasi Penarikan</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Serahkan uang &amp; verifikasi PIN nasabah</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-400 dark:text-zinc-500 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>

            {{-- Penarikan Offline --}}
            <a href="{{ route('kolektor.penarikan-offline.index') }}" wire:navigate
               class="group flex items-center gap-3.5 px-5 py-4 transition hover:bg-zinc-50 dark:hover:bg-zinc-700/40">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 group-hover:scale-105 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">Form Penarikan Offline</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Pengajuan penarikan baru untuk nasabah</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-400 dark:text-zinc-500 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>
    </div>

    {{-- Section: Tugas & Kehadiran --}}
    <div>
        <p class="mb-2.5 px-1 text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Tugas & Kehadiran</p>
        <div class="overflow-hidden rounded-3xl bg-white dark:bg-zinc-800 shadow-sm border border-zinc-100 dark:border-zinc-700/60 divide-y divide-zinc-100 dark:divide-zinc-700/60">
            {{-- Ajukan Izin --}}
            <a href="{{ route('kolektor.izin.index') }}" wire:navigate
               class="group flex items-center gap-3.5 px-5 py-4 transition hover:bg-zinc-50 dark:hover:bg-zinc-700/40">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400 group-hover:scale-105 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0121 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">Ajukan Izin / Cuti</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Pengajuan tidak masuk kerja atau ketidakhadiran</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-500 dark:text-zinc-400 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>

            {{-- Riwayat Absensi --}}
            <a href="{{ route('kolektor.riwayat-absensi.index') }}" wire:navigate
               class="group flex items-center gap-3.5 px-5 py-4 transition hover:bg-zinc-50 dark:hover:bg-zinc-700/40">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600 dark:bg-cyan-950/40 dark:text-cyan-400 group-hover:scale-105 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">Riwayat Absensi</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Log jam masuk, lokasi GPS, dan foto selfie</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-400 dark:text-zinc-500 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>
    </div>

    {{-- Section: Preferensi --}}
    <div>
        <p class="mb-2.5 px-1 text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Preferensi & Tampilan</p>
        <div class="overflow-hidden rounded-3xl bg-white dark:bg-zinc-800 shadow-sm border border-zinc-100 dark:border-zinc-700/60 divide-y divide-zinc-100 dark:divide-zinc-700/60">
            {{-- Notifikasi WhatsApp --}}
            <div class="flex items-center justify-between gap-3.5 px-5 py-4">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-1.074-.85c.18-.705.513-1.637.971-2.45A8.83 8.83 0 013 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">Notifikasi WhatsApp</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Terima reminder setoran & pengumuman</p>
                    </div>
                </div>
                <button wire:click="toggleNotifikasiWa" type="button"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 {{ $notifikasiWaAktif ? 'bg-emerald-600 dark:bg-emerald-500' : 'bg-zinc-200 dark:bg-zinc-700' }}">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out {{ $notifikasiWaAktif ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>

            {{-- Mode Gelap --}}
            <a href="{{ route('appearance.edit') }}" wire:navigate
               class="group flex items-center gap-3.5 px-5 py-4 transition hover:bg-zinc-50 dark:hover:bg-zinc-700/40">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 group-hover:scale-105 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">Tampilan & Mode Gelap</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Atur tema aplikasi sesuai kenyamanan</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-400 dark:text-zinc-500 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>
    </div>

    {{-- Section: Bantuan & Sesi --}}
    <div>
        <p class="mb-2.5 px-1 text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Bantuan & Sesi</p>
        <div class="overflow-hidden rounded-3xl bg-white dark:bg-zinc-800 shadow-sm border border-zinc-100 dark:border-zinc-700/60 divide-y divide-zinc-100 dark:divide-zinc-700/60">
            {{-- Bantuan WhatsApp --}}
            <a href="https://wa.me/6289531432845" target="_blank" rel="noopener noreferrer"
               class="group flex items-center gap-3.5 px-5 py-4 transition hover:bg-zinc-50 dark:hover:bg-zinc-700/40">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-teal-50 text-teal-600 dark:bg-teal-950/40 dark:text-teal-400 group-hover:scale-105 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M12 18h.008v.008H12V18zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">Pusat Bantuan CS</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Hubungi admin pusat via WhatsApp</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-zinc-400 dark:text-zinc-500 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
            </a>

            {{-- Keluar --}}
            <button wire:click="logout" type="button"
                    class="group flex w-full items-center gap-3.5 px-5 py-4 transition hover:bg-rose-50/60 dark:hover:bg-rose-950/20 text-left">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 group-hover:scale-105 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-rose-600 dark:text-rose-400">Keluar Akun</p>
                    <p class="text-xs text-rose-500/80 dark:text-rose-400/70">Keluar dari aplikasi pada perangkat ini</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-400 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>
        </div>
    </div>
</div>

