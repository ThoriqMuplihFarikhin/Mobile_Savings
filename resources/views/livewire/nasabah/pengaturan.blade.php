<div class="mx-auto max-w-2xl space-y-4 pb-8">
    {{-- 1. Hero Header Profil (Sesuai Layout Kolektor) --}}
    <div class="relative -mx-4 -mt-4 overflow-hidden bg-primary-deep px-4 pb-12 pt-4 text-on-hero">
        {{-- Lingkaran dekoratif sesuai prototipe --}}
        <span class="pointer-events-none absolute -right-6 -top-4 size-28 rounded-full bg-white/5 animate-pulse" aria-hidden="true"></span>

        <div class="relative flex items-center gap-3.5">
            @if(auth()->user()->foto_profil_path)
                <img src="{{ asset('storage/'.auth()->user()->foto_profil_path) }}" alt="Foto Profil"
                     class="size-14 rounded-full border-2 border-white/20 object-cover shadow-xs" />
            @else
                <div class="flex size-14 shrink-0 items-center justify-center rounded-full bg-info-soft text-lg font-semibold text-info" aria-hidden="true">
                    {{ auth()->user()->initials() }}
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <h1 class="truncate text-lg font-semibold text-white">
                    {{ auth()->user()->name }}
                </h1>
                <p class="mt-0.5 text-xs text-on-hero-mute tabular-nums">
                    {{ auth()->user()->no_hp ?? '-' }}
                </p>
                <div class="mt-1.5 inline-flex items-center">
                    <x-ui.status-chip domain="akun" status="aktif" />
                </div>
            </div>
        </div>

        {{-- Gelombang SVG transisi kanvas --}}
        <svg class="absolute -bottom-px left-0 h-7 w-full text-canvas-warm dark:text-dark-canvas" viewBox="0 0 400 28" preserveAspectRatio="none" fill="currentColor" aria-hidden="true">
            <path d="M0 28V16C70 0 130 0 200 12s130 8 200-6v22z"/>
        </svg>
    </div>

    {{-- 2. Card Petugas Kolektor Anda (Overlap Hero -mt-5, Sesuai Kolektor) --}}
    @php
        $kolektorPj = \App\Models\KolektorNasabah::where('nasabah_id', auth()->id())
            ->where('status', 'aktif')
            ->with('kolektor')
            ->first()?->kolektor;
    @endphp
    <div class="relative -mt-5 rounded-[20px] border border-hairline bg-surface p-4 shadow-xs">
        <p class="text-xs text-mute mb-2.5 font-medium">Petugas Kolektor Anda</p>
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-info-soft text-sm font-semibold text-info" aria-hidden="true">
                    {{ $kolektorPj?->initials() ?? 'KS' }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-ink">{{ $kolektorPj?->name ?? 'Kolektor Tabungan' }}</p>
                    <p class="truncate text-xs text-mute">{{ $kolektorPj?->no_hp ?? 'Petugas penanggung jawab' }}</p>
                </div>
            </div>
            @if($kolektorPj?->no_hp)
                @php
                    $waKolektor = preg_replace('/[^0-9]/', '', (string)$kolektorPj->no_hp);
                    if (str_starts_with($waKolektor, '0')) {
                        $waKolektor = '62' . substr($waKolektor, 1);
                    }
                @endphp
                <a href="https://wa.me/{{ $waKolektor }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex h-9 items-center justify-center gap-1.5 rounded-full border border-hairline bg-surface px-4 text-xs font-semibold text-ink shadow-xs transition hover:bg-surface-inset active:scale-95">
                    <flux:icon.chat-bubble-left-right class="size-3.5 text-primary" />
                    <span>Chat</span>
                </a>
            @else
                <a href="{{ route('nasabah.komplain.index') }}" wire:navigate
                   class="inline-flex h-9 items-center justify-center gap-1.5 rounded-full border border-hairline bg-surface px-4 text-xs font-semibold text-ink shadow-xs transition hover:bg-surface-inset active:scale-95">
                    <flux:icon.chat-bubble-left-right class="size-3.5 text-primary" />
                    <span>Bantuan</span>
                </a>
            @endif
        </div>
    </div>

    {{-- 3. Card Ringkasan Tabungan (Pencapaian 3 Kolom Sesuai Kolektor) --}}
    @php
        $totalProdukAktif = \App\Models\SaldoProduk::where('nasabah_id', auth()->id())->where('saldo', '>', 0)->count();
        $totalPaketAktif = \App\Models\KepesertaanPaket::where('nasabah_id', auth()->id())->where('status_serah_terima', 'belum')->count();
    @endphp
    <div class="rounded-[20px] border border-hairline bg-surface p-4 shadow-xs">
        <h2 class="mb-3 text-sm font-semibold text-ink">Ringkasan tabungan</h2>
        <div class="grid grid-cols-3 gap-2.5 text-center">
            <div class="flex flex-col items-center rounded-xl bg-primary-soft-2 p-2.5">
                <div class="mb-1.5 flex size-11 items-center justify-center rounded-full bg-primary-soft text-primary shadow-xs">
                    <flux:icon.banknotes class="size-5" />
                </div>
                <span class="text-xs font-semibold text-ink tabular-nums">{{ $totalProdukAktif }}</span>
                <span class="text-xs text-mute">produk aktif</span>
            </div>

            <div class="flex flex-col items-center rounded-xl bg-primary-soft-2 p-2.5">
                <div class="mb-1.5 flex size-11 items-center justify-center rounded-full bg-tile-teal-bg text-tile-teal-fg shadow-xs">
                    <flux:icon.gift class="size-5" />
                </div>
                <span class="text-xs font-semibold text-ink tabular-nums">{{ $totalPaketAktif }}</span>
                <span class="text-xs text-mute">paket berjalan</span>
            </div>

            <div class="flex flex-col items-center rounded-xl bg-primary-soft-2 p-2.5">
                <div class="mb-1.5 flex size-11 items-center justify-center rounded-full bg-warning-soft text-warning-deep shadow-xs">
                    <flux:icon.shield-check class="size-5" />
                </div>
                <span class="text-xs font-semibold text-ink">Aktif</span>
                <span class="text-xs text-mute">status akun</span>
            </div>
        </div>
    </div>

    {{-- 4. Unified Settings Card (Satu Kartu Gabungan Sesuai Prototipe Kolektor) --}}
    <div class="divide-y divide-hairline overflow-hidden rounded-[20px] border border-hairline bg-surface shadow-xs">
        {{-- Edit Profil --}}
        <a href="{{ route('profile.edit') }}" wire:navigate
           class="group flex min-h-14 items-center gap-3.5 px-4 py-3.5 transition hover:bg-surface-inset">
            <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                <flux:icon.user class="size-5" />
            </x-ui.tile>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-ink">Edit profil</p>
                <p class="text-xs text-mute">Nama, no HP, dan informasi pribadi</p>
            </div>
            <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
        </a>

        {{-- Ganti PIN --}}
        <a href="{{ route('security.edit') }}" wire:navigate
           class="group flex min-h-14 items-center gap-3.5 px-4 py-3.5 transition hover:bg-surface-inset">
            <x-ui.tile kategori="teal" ukuran="40" aria-hidden="true">
                <flux:icon.shield-check class="size-5" />
            </x-ui.tile>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-ink">Ganti PIN</p>
                <p class="text-xs text-mute">Keamanan akun dan 6 digit PIN transaksi</p>
            </div>
            <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
        </a>

        {{-- Teks Besar (A+) Toggle --}}
        <div class="flex min-h-14 items-center justify-between gap-3.5 px-4 py-3.5">
            <div class="flex min-w-0 items-center gap-3.5">
                <x-ui.tile kategori="teal" ukuran="40" aria-hidden="true">
                    <span class="text-xs font-semibold">A+</span>
                </x-ui.tile>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-ink">Teks besar</p>
                    <p class="text-xs text-mute">Perbesar tulisan aplikasi agar lebih nyaman dibaca</p>
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

        {{-- Notifikasi WhatsApp --}}
        <div class="flex min-h-14 items-center justify-between gap-3.5 px-4 py-3.5">
            <div class="flex min-w-0 items-center gap-3.5">
                <x-ui.tile kategori="biru" ukuran="40" aria-hidden="true">
                    <flux:icon.chat-bubble-bottom-center-text class="size-5" />
                </x-ui.tile>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-ink">Notifikasi WhatsApp</p>
                    <p class="text-xs text-mute">Terima bukti setoran & info via WA</p>
                </div>
            </div>
            <button wire:click="toggleNotifikasiWa" type="button" role="switch"
                    aria-checked="{{ $notifikasiWaAktif ? 'true' : 'false' }}" aria-label="Notifikasi WhatsApp"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 {{ $notifikasiWaAktif ? 'bg-primary' : 'bg-hairline-strong' }}">
                <span class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow-md transition duration-200 ease-in-out {{ $notifikasiWaAktif ? 'translate-x-5' : 'translate-x-0' }}"></span>
            </button>
        </div>

        {{-- Pusat Bantuan & Komplain --}}
        <a href="{{ route('nasabah.komplain.index') }}" wire:navigate
           class="group flex min-h-14 items-center gap-3.5 px-4 py-3.5 transition hover:bg-surface-inset">
            <x-ui.tile kategori="kuning" ukuran="40" aria-hidden="true">
                <flux:icon.chat-bubble-left-right class="size-5" />
            </x-ui.tile>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-ink">Pusat bantuan & komplain</p>
                <p class="text-xs text-mute">Status tiket keluhan dan pertanyaan</p>
            </div>
            <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
        </a>

        {{-- Hubungi Customer Service via WhatsApp --}}
        @php
            $nomorWaAdmin = \App\Models\AdminSetting::get('nomor_wa_bantuan', config('services.admin_wa', '081234567890'));
            $waAdminClean = preg_replace('/[^0-9]/', '', (string)$nomorWaAdmin);
            if (str_starts_with($waAdminClean, '0')) {
                $waAdminClean = '62' . substr($waAdminClean, 1);
            }
        @endphp
        @if($waAdminClean)
            <a href="https://wa.me/{{ $waAdminClean }}?text={{ urlencode('Halo Admin, saya nasabah KawalDuit ingin meminta bantuan.') }}"
               target="_blank" rel="noopener noreferrer"
               class="group flex min-h-14 items-center gap-3.5 px-4 py-3.5 transition hover:bg-surface-inset">
                <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                    <flux:icon.question-mark-circle class="size-5" />
                </x-ui.tile>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-ink">Hubungi admin resmi</p>
                    <p class="text-xs text-mute">Chat customer service via WhatsApp</p>
                </div>
                <flux:icon.arrow-up-right class="size-4 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
            </a>
        @endif

        {{-- Mode Gelap --}}
        <a href="{{ route('appearance.edit') }}" wire:navigate
           class="group flex min-h-14 items-center gap-3.5 px-4 py-3.5 transition hover:bg-surface-inset">
            <x-ui.tile kategori="netral" ukuran="40" aria-hidden="true">
                <flux:icon.moon class="size-5" />
            </x-ui.tile>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-ink">Mode gelap</p>
                <p class="text-xs text-mute">Sesuaikan tampilan terang atau gelap</p>
            </div>
            <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
        </a>

        {{-- Keluar Akun --}}
        <button wire:click="logout"
                wire:confirm="Yakin ingin keluar dari akun Anda pada perangkat ini?"
                type="button"
                class="group flex min-h-14 w-full items-center gap-3.5 px-4 py-3.5 text-left transition hover:bg-surface-inset">
            <x-ui.tile kategori="netral" ukuran="40" aria-hidden="true">
                <flux:icon.arrow-right-start-on-rectangle class="size-5" />
            </x-ui.tile>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-ink">Keluar akun</p>
                <p class="text-xs text-mute">Keluar dari aplikasi pada perangkat ini</p>
            </div>
            <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
        </button>
    </div>

    <p class="mt-6 text-center text-xs text-mute">KawalDuit · versi alpha</p>
</div>
