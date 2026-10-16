<div class="mx-auto max-w-2xl space-y-6 pb-8">
    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Pengaturan kolektor</h1>
        <p class="mt-1 text-sm text-mute">Kelola profil, tugas harian, dan preferensi akun Anda.</p>
    </div>

    {{-- Profile Card --}}
    <div class="rounded-2xl border border-hairline bg-zinc-900 p-6 text-white">
        <div class="flex items-center gap-4">
            <div class="relative">
                <div class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-primary text-xl font-semibold text-on-primary" aria-hidden="true">
                    {{ auth()->user()->initials() }}
                </div>
                <div class="absolute -bottom-1 -right-1 flex size-5 items-center justify-center rounded-full bg-primary ring-2 ring-zinc-900" title="Petugas aktif" aria-label="Petugas aktif">
                    <svg class="size-3 text-on-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                </div>
            </div>

            <div class="min-w-0 flex-1">
                <h2 class="truncate text-lg font-semibold">{{ auth()->user()->name }}</h2>
                <p class="mt-1 text-sm tabular-nums">{{ auth()->user()->no_hp ?? '-' }}</p>
                <p class="mt-0.5 text-xs opacity-80">Petugas lapangan aktif</p>
            </div>
        </div>
    </div>

    @if ($adaKasBelumDisetor)
        {{-- Warning Banner: kas belum disetor ke kantor --}}
        <div class="flex items-start gap-3 rounded-2xl border border-warning-soft bg-warning-soft p-5">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-inset" aria-hidden="true">
                <flux:icon.exclamation-triangle class="size-5 text-warning-deep" />
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-warning-deep">Masih ada kas belum disetor</p>
                <p class="mt-0.5 text-sm text-warning-deep">Segera lakukan penyetoran kas hasil tagihan ke kasir kantor melalui menu Setor ke kantor.</p>
                <a href="{{ route('kolektor.setor-kantor.index') }}" wire:navigate class="mt-2.5 inline-flex items-center gap-1.5 text-sm font-medium text-warning-deep underline underline-offset-4 hover:opacity-80">
                    Buka setor ke kantor
                    <flux:icon.chevron-right class="size-4" aria-hidden="true" />
                </a>
            </div>
        </div>
    @endif

    {{-- Section: Akun --}}
    <div>
        <p class="mb-2.5 px-1 text-sm font-medium text-mute">Akun dan keamanan</p>
        <div class="divide-y divide-hairline overflow-hidden rounded-2xl border border-hairline bg-white dark:bg-surface">
            {{-- Edit Profil --}}
            <a href="{{ route('profile.edit') }}" wire:navigate
               class="group flex min-h-16 items-center gap-3.5 px-4 py-4 transition hover:bg-inset">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-inset text-body" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-ink">Edit profil</p>
                    <p class="text-xs text-mute">Ubah nama, nomor HP, dan biodata</p>
                </div>
                <flux:icon.chevron-right class="size-5 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
            </a>

            {{-- Ganti PIN --}}
            <a href="{{ route('security.edit') }}" wire:navigate
               class="group flex min-h-16 items-center gap-3.5 px-4 py-4 transition hover:bg-inset">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-inset text-body" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-ink">Ganti PIN / password</p>
                    <p class="text-xs text-mute">Keamanan akun dan kata sandi login</p>
                </div>
                <flux:icon.chevron-right class="size-5 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
            </a>
        </div>
    </div>

    {{-- Section: Penarikan Offline --}}
    <div>
        <p class="mb-2.5 px-1 text-sm font-medium text-mute">Penarikan offline</p>
        <div class="divide-y divide-hairline overflow-hidden rounded-2xl border border-hairline bg-white dark:bg-surface">
            {{-- Verifikasi Penarikan --}}
            <a href="{{ route('kolektor.verifikasi-penarikan.index') }}" wire:navigate
               class="group flex min-h-16 items-center gap-3.5 px-4 py-4 transition hover:bg-inset">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-inset text-body" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-ink">Verifikasi penarikan</p>
                    <p class="text-xs text-mute">Serahkan uang dan verifikasi PIN nasabah</p>
                </div>
                <flux:icon.chevron-right class="size-5 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
            </a>

            {{-- Penarikan Offline --}}
            <a href="{{ route('kolektor.penarikan-offline.index') }}" wire:navigate
               class="group flex min-h-16 items-center gap-3.5 px-4 py-4 transition hover:bg-inset">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-inset text-body" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-ink">Form penarikan offline</p>
                    <p class="text-xs text-mute">Pengajuan penarikan baru untuk nasabah</p>
                </div>
                <flux:icon.chevron-right class="size-5 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
            </a>
        </div>
    </div>

    {{-- Section: Tugas & Kehadiran --}}
    <div>
        <p class="mb-2.5 px-1 text-sm font-medium text-mute">Tugas dan kehadiran</p>
        <div class="divide-y divide-hairline overflow-hidden rounded-2xl border border-hairline bg-white dark:bg-surface">
            {{-- Ajukan Izin --}}
            <a href="{{ route('kolektor.izin.index') }}" wire:navigate
               class="group flex min-h-16 items-center gap-3.5 px-4 py-4 transition hover:bg-inset">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-inset text-body" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0121 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-ink">Ajukan izin / cuti</p>
                    <p class="text-xs text-mute">Pengajuan tidak masuk kerja atau ketidakhadiran</p>
                </div>
                <flux:icon.chevron-right class="size-5 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
            </a>

            {{-- Riwayat Absensi --}}
            <a href="{{ route('kolektor.riwayat-absensi.index') }}" wire:navigate
               class="group flex min-h-16 items-center gap-3.5 px-4 py-4 transition hover:bg-inset">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-inset text-body" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-ink">Riwayat absensi</p>
                    <p class="text-xs text-mute">Log jam masuk, lokasi GPS, dan foto selfie</p>
                </div>
                <flux:icon.chevron-right class="size-5 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
            </a>
        </div>
    </div>

    {{-- Section: Preferensi --}}
    <div>
        <p class="mb-2.5 px-1 text-sm font-medium text-mute">Preferensi dan tampilan</p>
        <div class="divide-y divide-hairline overflow-hidden rounded-2xl border border-hairline bg-white dark:bg-surface">
            {{-- Notifikasi WhatsApp --}}
            <div class="flex min-h-16 items-center justify-between gap-3.5 px-4 py-4">
                <div class="flex min-w-0 items-center gap-3.5">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-inset text-body" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-1.074-.85c.18-.705.513-1.637.971-2.45A8.83 8.83 0 013 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-ink">Notifikasi WhatsApp</p>
                        <p class="text-xs text-mute">Terima reminder setoran dan pengumuman</p>
                    </div>
                </div>
                <button wire:click="toggleNotifikasiWa" type="button" role="switch"
                        aria-checked="{{ $notifikasiWaAktif ? 'true' : 'false' }}" aria-label="Notifikasi WhatsApp"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-zinc-900 {{ $notifikasiWaAktif ? 'bg-primary' : 'bg-hairline-strong' }}">
                    <span class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow-md transition duration-200 ease-in-out {{ $notifikasiWaAktif ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>

            {{-- Mode Gelap --}}
            <a href="{{ route('appearance.edit') }}" wire:navigate
               class="group flex min-h-16 items-center gap-3.5 px-4 py-4 transition hover:bg-inset">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-inset text-body" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-ink">Tampilan dan mode gelap</p>
                    <p class="text-xs text-mute">Atur tema aplikasi sesuai kenyamanan</p>
                </div>
                <flux:icon.chevron-right class="size-5 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
            </a>
        </div>
    </div>

    {{-- Section: Bantuan & Sesi --}}
    <div>
        <p class="mb-2.5 px-1 text-sm font-medium text-mute">Bantuan dan sesi</p>
        <div class="divide-y divide-hairline overflow-hidden rounded-2xl border border-hairline bg-white dark:bg-surface">
            {{-- Bantuan WhatsApp --}}
            <a href="https://wa.me/6289531432845" target="_blank" rel="noopener noreferrer"
               class="group flex min-h-16 items-center gap-3.5 px-4 py-4 transition hover:bg-inset">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-inset text-body" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M12 18h.008v.008H12V18zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-ink">Pusat bantuan CS</p>
                    <p class="text-xs text-mute">Hubungi admin pusat via WhatsApp</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
            </a>

            {{-- Keluar --}}
            <button wire:click="logout" type="button"
                    class="group flex min-h-16 w-full items-center gap-3.5 px-4 py-4 text-left transition hover:bg-inset">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-inset text-body" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-ink">Keluar akun</p>
                    <p class="text-xs text-mute">Keluar dari aplikasi pada perangkat ini</p>
                </div>
                <flux:icon.chevron-right class="size-5 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
            </button>
        </div>
    </div>

    <p class="mt-6 text-center text-xs text-mute">Tabungan Digital · versi alpha</p>
</div>
