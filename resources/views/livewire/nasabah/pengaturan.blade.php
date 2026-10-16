<div class="space-y-6">
    {{-- Judul halaman --}}
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Pengaturan</h1>
        <p class="mt-1 text-sm text-mute">Kelola akun, preferensi, dan keamanan Anda.</p>
    </div>

    {{-- Kartu profil --}}
    <section aria-labelledby="label-profil" class="rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
        <h2 id="label-profil" class="sr-only">Profil</h2>

        <div class="flex items-center gap-4">
            <div class="flex size-14 shrink-0 items-center justify-center rounded-full bg-primary-soft text-xl font-semibold text-on-primary-soft" aria-hidden="true">
                {{ auth()->user()->initials() }}
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="truncate text-xl font-semibold tracking-tight text-ink">{{ auth()->user()->name }}</p>
                    <x-ui.status-chip domain="akun" status="aktif" />
                </div>
                <p class="mt-0.5 text-sm text-mute">{{ auth()->user()->no_hp ?? '-' }}</p>
                <p class="mt-0.5 text-sm text-mute">Terdaftar sejak {{ auth()->user()->created_at?->translatedFormat('F Y') ?? '-' }}</p>
            </div>
        </div>
    </section>

    {{-- Akun dan keamanan --}}
    <section aria-labelledby="label-akun">
        <h2 id="label-akun" class="mb-2 px-1 text-sm font-medium text-mute">Akun dan keamanan</h2>
        <div class="divide-y divide-hairline-warm overflow-hidden rounded-[20px] border border-hairline-warm bg-white shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
            <a href="{{ route('profile.edit') }}" wire:navigate
               class="flex min-h-16 items-center gap-3 px-4 py-3 transition hover:bg-inset">
                <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </x-ui.tile>
                <span class="min-w-0 flex-1">
                    <span class="block text-base font-medium text-ink">Edit profil</span>
                    <span class="block text-sm text-mute">Ubah nama, nomor HP, dan informasi pribadi</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>

            <a href="{{ route('security.edit') }}" wire:navigate
               class="flex min-h-16 items-center gap-3 px-4 py-3 transition hover:bg-inset">
                <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </x-ui.tile>
                <span class="min-w-0 flex-1">
                    <span class="block text-base font-medium text-ink">Ganti PIN / password</span>
                    <span class="block text-sm text-mute">Keamanan akun dan kata sandi transaksi</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>
    </section>

    {{-- Preferensi --}}
    <section aria-labelledby="label-preferensi">
        <h2 id="label-preferensi" class="mb-2 px-1 text-sm font-medium text-mute">Preferensi dan tampilan</h2>
        <div class="divide-y divide-hairline-warm overflow-hidden rounded-[20px] border border-hairline-warm bg-white shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
            <div class="flex min-h-16 items-center justify-between gap-3 px-4 py-3">
                <div class="flex min-w-0 items-center gap-3">
                    <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-1.074-.85c.18-.705.513-1.637.971-2.45A8.83 8.83 0 013 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                        </svg>
                    </x-ui.tile>
                    <span class="min-w-0 flex-1">
                        <span class="block text-base font-medium text-ink">Notifikasi WhatsApp</span>
                        <span class="block text-sm text-mute">Terima bukti setoran dan info via WA</span>
                    </span>
                </div>

                <button wire:click="toggleNotifikasiWa" type="button" role="switch" aria-checked="{{ $notifikasiWaAktif ? 'true' : 'false' }}"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-zinc-900 {{ $notifikasiWaAktif ? 'bg-primary' : 'bg-hairline-strong' }}">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white transition duration-200 ease-in-out {{ $notifikasiWaAktif ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>

            <a href="{{ route('appearance.edit') }}" wire:navigate
               class="flex min-h-16 items-center gap-3 px-4 py-3 transition hover:bg-inset">
                <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </x-ui.tile>
                <span class="min-w-0 flex-1">
                    <span class="block text-base font-medium text-ink">Tampilan dan mode gelap</span>
                    <span class="block text-sm text-mute">Atur tema aplikasi sesuai kenyamanan</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>
    </section>

    {{-- Bantuan dan sesi --}}
    <section aria-labelledby="label-bantuan">
        <h2 id="label-bantuan" class="mb-2 px-1 text-sm font-medium text-mute">Bantuan dan sesi</h2>
        <div class="divide-y divide-hairline-warm overflow-hidden rounded-[20px] border border-hairline-warm bg-white shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
            <a href="{{ route('nasabah.komplain.index') }}" wire:navigate
               class="flex min-h-16 items-center gap-3 px-4 py-3 transition hover:bg-inset">
                <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                    </svg>
                </x-ui.tile>
                <span class="min-w-0 flex-1">
                    <span class="block text-base font-medium text-ink">Riwayat komplain</span>
                    <span class="block text-sm text-mute">Lihat status keluhan dan tanggapan admin</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>

            <a href="https://wa.me/6289531432845" target="_blank" rel="noopener noreferrer"
               class="flex min-h-16 items-center gap-3 px-4 py-3 transition hover:bg-inset">
                <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M12 18h.008v.008H12V18zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </x-ui.tile>
                <span class="min-w-0 flex-1">
                    <span class="block text-base font-medium text-ink">Pusat bantuan dan CS</span>
                    <span class="block text-sm text-mute">Hubungi customer service via WhatsApp</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
            </a>

            <button wire:click="logout" type="button"
                    class="flex min-h-16 w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-inset">
                <x-ui.tile kategori="netral" ukuran="40" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                </x-ui.tile>
                <span class="min-w-0 flex-1">
                    <span class="block text-base font-medium text-ink">Keluar akun</span>
                    <span class="block text-sm text-mute">Keluar dari aplikasi pada perangkat ini</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>
        </div>
    </section>

    <p class="mt-6 text-center text-xs text-mute">Tabungan Digital · versi alpha</p>
</div>
