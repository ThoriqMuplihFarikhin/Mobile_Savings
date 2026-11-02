<div class="mx-auto max-w-2xl space-y-4 pb-8">
    {{-- 1. Hero Header Profil (Sesuai Prototipe) --}}
    <div class="relative -mx-4 -mt-4 overflow-hidden bg-primary-deep px-4 pb-12 pt-4 text-on-hero">
        <div class="relative flex items-center gap-3.5">
            <div class="flex size-14 shrink-0 items-center justify-center rounded-full bg-info-soft text-lg font-semibold text-info" aria-hidden="true">
                {{ auth()->user()->initials() }}
            </div>
            <div class="min-w-0 flex-1">
                <h1 class="truncate text-lg font-semibold text-white">
                    {{ auth()->user()->name }}
                </h1>
                <p class="mt-0.5 text-xs text-on-hero-mute tabular-nums">
                    {{ auth()->user()->no_hp ?? '-' }}
                </p>
                <span class="mt-1.5 inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs font-medium text-info">
                    Petugas lapangan aktif
                </span>
            </div>
        </div>

        {{-- Gelombang SVG transisi kanvas --}}
        <svg class="absolute -bottom-px left-0 h-7 w-full text-canvas-warm dark:text-dark-canvas" viewBox="0 0 400 28" preserveAspectRatio="none" fill="currentColor" aria-hidden="true">
            <path d="M0 28V16C70 0 130 0 200 12s130 8 200-6v22z"/>
        </svg>
    </div>

    {{-- 2. Banner Peringatan Kas Belum Disetor (Overlap Hero) --}}
    @if ($adaKasBelumDisetor)
        <div class="relative -mt-5 rounded-[20px] border border-warning-soft bg-warning-soft p-4 text-warning-deep shadow-[0_1px_2px_rgba(20,83,45,.06)]">
            <p class="text-sm font-semibold">Masih ada kas belum disetor</p>
            <p class="mt-0.5 text-xs opacity-90">Segera setorkan kas hasil tagihan ke kantor sebelum pulang.</p>
            <a href="{{ route('kolektor.setor-kantor.index') }}" wire:navigate class="mt-2 inline-flex items-center gap-1 text-xs font-semibold underline underline-offset-2 hover:opacity-80">
                Buka setor ke kantor &rarr;
            </a>
        </div>
    @endif

    {{-- 3. Card Pencapaian (Gamification) --}}
    <div class="rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:border-hairline/60 dark:bg-surface">
        <h2 class="mb-3 text-sm font-semibold text-ink dark:text-zinc-100">Pencapaian</h2>
        <div class="grid grid-cols-3 gap-2.5 text-center">
            <div class="flex flex-col items-center rounded-xl bg-primary-soft-2 p-2.5 dark:bg-zinc-800">
                <div class="mb-1.5 flex size-11 items-center justify-center rounded-full bg-warning-soft text-warning-deep shadow-xs">
                    <flux:icon.fire class="size-5" />
                </div>
                <span class="text-xs font-semibold text-ink dark:text-zinc-100 tabular-nums">{{ $streakTepatWaktu }} hari</span>
                <span class="text-xs text-mute">tepat waktu</span>
            </div>

            <div class="flex flex-col items-center rounded-xl bg-primary-soft-2 p-2.5 dark:bg-zinc-800">
                <div class="mb-1.5 flex size-11 items-center justify-center rounded-full bg-warning-soft text-warning-deep shadow-xs">
                    <flux:icon.star class="size-5" />
                </div>
                <span class="text-xs font-semibold text-ink dark:text-zinc-100 tabular-nums">{{ $totalKunjunganLancar }}</span>
                <span class="text-xs text-mute">kunjungan lancar</span>
            </div>

            <div class="flex flex-col items-center rounded-xl bg-primary-soft-2 p-2.5 dark:bg-zinc-800">
                <div class="mb-1.5 flex size-11 items-center justify-center rounded-full bg-inset text-mute shadow-xs dark:bg-zinc-700">
                    <flux:icon.users class="size-5" />
                </div>
                <span class="text-xs font-semibold text-ink dark:text-zinc-100 tabular-nums">{{ $totalNasabahBinaan }}</span>
                <span class="text-xs text-mute">nasabah binaan</span>
            </div>
        </div>
    </div>

    {{-- 4. Unified Settings Card (Sesuai Prototipe) --}}
    <div class="divide-y divide-hairline overflow-hidden rounded-[20px] border border-hairline-warm bg-white shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:border-hairline/60 dark:bg-surface">
        {{-- Edit Profil --}}
        <a href="{{ route('profile.edit') }}" wire:navigate
           class="group flex min-h-14 items-center gap-3.5 px-4 py-3.5 transition hover:bg-inset">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-on-primary-soft" aria-hidden="true">
                <flux:icon.user class="size-5" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-ink">Edit profil</p>
                <p class="text-xs text-mute">Nama, no HP, dan biodata</p>
            </div>
            <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
        </a>

        {{-- Teks Besar (A+) Toggle --}}
        <div class="flex min-h-14 items-center justify-between gap-3.5 px-4 py-3.5">
            <div class="flex min-w-0 items-center gap-3.5">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-tile-teal-bg text-tile-teal-fg text-sm font-semibold" aria-hidden="true">
                    A+
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-ink">Teks besar</p>
                    <p class="text-xs text-mute">Lebih mudah dibaca di bawah matahari</p>
                </div>
            </div>
            <button @click="$store.textSize.toggle()" type="button" role="switch"
                    :aria-checked="$store.textSize?.isBig ? 'true' : 'false'" aria-label="Teks besar"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    :class="$store.textSize?.isBig ? 'bg-primary' : 'bg-hairline-strong'">
                <span class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow-md transition duration-200 ease-in-out"
                      :class="$store.textSize?.isBig ? 'translate-x-5' : 'translate-x-0'"></span>
            </button>
        </div>

        {{-- Serah Terima Paket --}}
        <a href="{{ route('serah-terima.index') }}" wire:navigate
           class="group flex min-h-14 items-center gap-3.5 px-4 py-3.5 transition hover:bg-inset">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-on-primary-soft" aria-hidden="true">
                <flux:icon.gift class="size-5" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-ink">Serah terima paket</p>
                <p class="text-xs text-mute">Konfirmasi serah terima paket tabungan</p>
            </div>
            <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
        </a>

        {{-- Mode Gelap --}}
        <a href="{{ route('appearance.edit') }}" wire:navigate
           class="group flex min-h-14 items-center gap-3.5 px-4 py-3.5 transition hover:bg-inset">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-inset text-mute" aria-hidden="true">
                <flux:icon.moon class="size-5" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-ink">Mode gelap</p>
                <p class="text-xs text-mute">Nyaman dipakai malam hari</p>
            </div>
            <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
        </a>

        {{-- Ganti PIN --}}
        <a href="{{ route('security.edit') }}" wire:navigate
           class="group flex min-h-14 items-center gap-3.5 px-4 py-3.5 transition hover:bg-inset">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-tile-teal-bg text-tile-teal-fg" aria-hidden="true">
                <flux:icon.shield-check class="size-5" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-ink">Ganti PIN</p>
                <p class="text-xs text-mute">Keamanan akun dan 6 digit PIN</p>
            </div>
            <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
        </a>

        {{-- Riwayat Absensi --}}
        <a href="{{ route('kolektor.riwayat-absensi.index') }}" wire:navigate
           class="group flex min-h-14 items-center gap-3.5 px-4 py-3.5 transition hover:bg-inset">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-info-soft text-info" aria-hidden="true">
                <flux:icon.clock class="size-5" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-ink">Riwayat absensi</p>
                <p class="text-xs text-mute">Jam masuk, GPS, dan selfie presensi</p>
            </div>
            <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
        </a>

        {{-- Ajukan Izin / Cuti --}}
        <a href="{{ route('kolektor.izin.index') }}" wire:navigate
           class="group flex min-h-14 items-center gap-3.5 px-4 py-3.5 transition hover:bg-inset">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-warning-soft text-warning-deep" aria-hidden="true">
                <flux:icon.document-text class="size-5" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-ink">Ajukan izin / cuti</p>
                <p class="text-xs text-mute">Pengajuan sakit, izin, atau cuti kerja</p>
            </div>
            <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
        </a>

        {{-- Verifikasi Penarikan --}}
        <a href="{{ route('kolektor.verifikasi-penarikan.index') }}" wire:navigate
           class="group flex min-h-14 items-center gap-3.5 px-4 py-3.5 transition hover:bg-inset">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-tile-teal-bg text-tile-teal-fg" aria-hidden="true">
                <flux:icon.check-badge class="size-5" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-ink">Verifikasi penarikan</p>
                <p class="text-xs text-mute">Serahkan uang dan verifikasi PIN nasabah</p>
            </div>
            <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
        </a>

        {{-- Penarikan Offline --}}
        <a href="{{ route('kolektor.penarikan-offline.index') }}" wire:navigate
           class="group flex min-h-14 items-center gap-3.5 px-4 py-3.5 transition hover:bg-inset">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-info-soft text-info" aria-hidden="true">
                <flux:icon.arrow-down-tray class="size-5" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-ink">Penarikan offline</p>
                <p class="text-xs text-mute">Pengajuan penarikan langsung di lapangan</p>
            </div>
            <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
        </a>

        {{-- Notifikasi WhatsApp --}}
        <div class="flex min-h-14 items-center justify-between gap-3.5 px-4 py-3.5">
            <div class="flex min-w-0 items-center gap-3.5">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-inset text-mute" aria-hidden="true">
                    <flux:icon.chat-bubble-left-right class="size-5" />
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-ink">Notifikasi WhatsApp</p>
                    <p class="text-xs text-mute">Pengingat setoran & pengumuman</p>
                </div>
            </div>
            <button wire:click="toggleNotifikasiWa" type="button" role="switch"
                    aria-checked="{{ $notifikasiWaAktif ? 'true' : 'false' }}" aria-label="Notifikasi WhatsApp"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 {{ $notifikasiWaAktif ? 'bg-primary' : 'bg-hairline-strong' }}">
                <span class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow-md transition duration-200 ease-in-out {{ $notifikasiWaAktif ? 'translate-x-5' : 'translate-x-0' }}"></span>
            </button>
        </div>
        {{-- Keluar Akun --}}
        <button wire:click="logout" wire:confirm="Yakin ingin keluar dari akun?" type="button"
                class="group flex min-h-14 w-full items-center gap-3.5 px-4 py-3.5 text-left transition hover:bg-inset">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-inset text-body" aria-hidden="true">
                <flux:icon.arrow-right-start-on-rectangle class="size-5" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-ink">Keluar akun</p>
                <p class="text-xs text-mute">Keluar dari aplikasi pada perangkat ini</p>
            </div>
            <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
        </button>
    </div>

    <p class="mt-6 text-center text-xs text-mute">Tabungan Digital · versi alpha</p>
</div>
