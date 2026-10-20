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
            @if(auth()->user()->foto_profil_path)
                <img src="{{ asset('storage/'.auth()->user()->foto_profil_path) }}" alt="Foto Profil"
                     class="size-14 rounded-full border border-hairline object-cover" />
            @else
                <div class="flex size-14 shrink-0 items-center justify-center rounded-full bg-primary-soft text-xl font-semibold text-on-primary-soft" aria-hidden="true">
                    {{ auth()->user()->initials() }}
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="truncate text-lg font-semibold tracking-tight text-ink">{{ auth()->user()->name }}</p>
                    <x-ui.status-chip domain="akun" status="aktif" />
                </div>
                <p class="mt-0.5 text-xs text-mute">{{ auth()->user()->no_hp ?? '-' }}</p>
                <p class="mt-0.5 text-xs text-mute">Terdaftar sejak {{ auth()->user()->created_at?->translatedFormat('F Y') ?? '-' }}</p>
            </div>
        </div>
    </section>

    {{-- Informasi Kolektor Penanggung Jawab --}}
    @php
        $kolektorPj = \App\Models\KolektorNasabah::where('nasabah_id', auth()->id())
            ->where('status', 'aktif')
            ->with('kolektor')
            ->first()?->kolektor;
    @endphp
    @if($kolektorPj)
        <section aria-labelledby="label-kolektor" class="rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
            <h2 id="label-kolektor" class="text-xs font-semibold text-body mb-3">Petugas Kolektor Anda</h2>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-full bg-info-soft text-sm font-semibold text-info" aria-hidden="true">
                        {{ $kolektorPj->initials() }}
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-ink">{{ $kolektorPj->name }}</p>
                        <p class="text-xs text-mute">{{ $kolektorPj->no_hp ?? '-' }}</p>
                    </div>
                </div>
                @if($kolektorPj->no_hp)
                    @php
                        $waKolektor = preg_replace('/[^0-9]/', '', (string)$kolektorPj->no_hp);
                        if (str_starts_with($waKolektor, '0')) {
                            $waKolektor = '62' . substr($waKolektor, 1);
                        }
                    @endphp
                    <a href="https://wa.me/{{ $waKolektor }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3.5 py-1.5 text-xs font-medium text-ink transition hover:bg-surface-inset active:scale-95 dark:bg-surface">
                        <flux:icon.chat-bubble-left-right class="size-3.5 text-primary" />
                        <span>Chat</span>
                    </a>
                @endif
            </div>
        </section>
    @endif

    {{-- Akun dan keamanan --}}
    <section aria-labelledby="label-akun">
        <h2 id="label-akun" class="mb-2 px-1 text-xs font-semibold text-mute">Akun &amp; Keamanan</h2>
        <div class="divide-y divide-hairline-warm overflow-hidden rounded-[20px] border border-hairline-warm bg-white shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
            <a href="{{ route('profile.edit') }}" wire:navigate
               class="flex min-h-14 items-center gap-3 px-4 py-3 transition hover:bg-surface-inset">
                <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                    <flux:icon.user class="size-4" />
                </x-ui.tile>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-medium text-ink">Edit profil</span>
                    <span class="block text-xs text-mute">Ubah nama, nomor HP, dan informasi pribadi</span>
                </span>
                <flux:icon.chevron-right class="size-4 shrink-0 text-mute" aria-hidden="true" />
            </a>

            <a href="{{ route('security.edit') }}" wire:navigate
               class="flex min-h-14 items-center gap-3 px-4 py-3 transition hover:bg-surface-inset">
                <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                    <flux:icon.key class="size-4" />
                </x-ui.tile>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-medium text-ink">Ganti PIN / kata sandi</span>
                    <span class="block text-xs text-mute">Keamanan akun dan verifikasi transaksi</span>
                </span>
                <flux:icon.chevron-right class="size-4 shrink-0 text-mute" aria-hidden="true" />
            </a>
        </div>
    </section>

    {{-- Preferensi & Tampilan --}}
    <section aria-labelledby="label-preferensi">
        <h2 id="label-preferensi" class="mb-2 px-1 text-xs font-semibold text-mute">Preferensi &amp; Tampilan</h2>
        <div class="divide-y divide-hairline-warm overflow-hidden rounded-[20px] border border-hairline-warm bg-white shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
            {{-- Notifikasi WhatsApp --}}
            <div class="flex min-h-14 items-center justify-between gap-3 px-4 py-3">
                <div class="flex min-w-0 items-center gap-3">
                    <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                        <flux:icon.chat-bubble-bottom-center-text class="size-4" />
                    </x-ui.tile>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-medium text-ink">Notifikasi WhatsApp</span>
                        <span class="block text-xs text-mute">Terima bukti setoran dan info via WA</span>
                    </span>
                </div>

                <button wire:click="toggleNotifikasiWa" type="button" role="switch" aria-checked="{{ $notifikasiWaAktif ? 'true' : 'false' }}"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-zinc-900 {{ $notifikasiWaAktif ? 'bg-primary' : 'bg-hairline-strong' }}">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white transition duration-200 ease-in-out {{ $notifikasiWaAktif ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>

            {{-- Toggle Ukuran Teks Besar (D37) --}}
            <div class="flex min-h-14 items-center justify-between gap-3 px-4 py-3" x-data="{
                isBig: $store.textSize ? $store.textSize.isBig : false
            }">
                <div class="flex min-w-0 items-center gap-3">
                    <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                        <span class="text-xs font-semibold">A+</span>
                    </x-ui.tile>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-medium text-ink">Ukuran teks besar</span>
                        <span class="block text-xs text-mute">Perbesar tulisan aplikasi agar lebih nyaman dibaca</span>
                    </span>
                </div>

                <button type="button"
                        @click="if (window.toggleUkuranTeks) { window.toggleUkuranTeks(); isBig = $store.textSize.isBig; }"
                        role="switch"
                        :aria-checked="isBig ? 'true' : 'false'"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                        :class="isBig ? 'bg-primary' : 'bg-hairline-strong'">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white transition duration-200 ease-in-out"
                          :class="isBig ? 'translate-x-5' : 'translate-x-0'"></span>
                </button>
            </div>

            {{-- Mode Gelap / Tampilan --}}
            <a href="{{ route('appearance.edit') }}" wire:navigate
               class="flex min-h-14 items-center gap-3 px-4 py-3 transition hover:bg-surface-inset">
                <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                    <flux:icon.moon class="size-4" />
                </x-ui.tile>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-medium text-ink">Tema &amp; mode gelap</span>
                    <span class="block text-xs text-mute">Sesuaikan tampilan terang atau gelap</span>
                </span>
                <flux:icon.chevron-right class="size-4 shrink-0 text-mute" aria-hidden="true" />
            </a>
        </div>
    </section>

    {{-- Bantuan & Sesi --}}
    @php
        $nomorWaAdmin = \App\Models\AdminSetting::get('nomor_wa_bantuan', config('services.admin_wa', '081234567890'));
        $waAdminClean = preg_replace('/[^0-9]/', '', (string)$nomorWaAdmin);
        if (str_starts_with($waAdminClean, '0')) {
            $waAdminClean = '62' . substr($waAdminClean, 1);
        }
    @endphp
    <section aria-labelledby="label-bantuan">
        <h2 id="label-bantuan" class="mb-2 px-1 text-xs font-semibold text-mute">Bantuan &amp; Sesi</h2>
        <div class="divide-y divide-hairline-warm overflow-hidden rounded-[20px] border border-hairline-warm bg-white shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
            <a href="{{ route('nasabah.komplain.index') }}" wire:navigate
               class="flex min-h-14 items-center gap-3 px-4 py-3 transition hover:bg-surface-inset">
                <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                    <flux:icon.chat-bubble-left-right class="size-4" />
                </x-ui.tile>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-medium text-ink">Riwayat komplain</span>
                    <span class="block text-xs text-mute">Lihat status keluhan dan tanggapan admin</span>
                </span>
                <flux:icon.chevron-right class="size-4 shrink-0 text-mute" aria-hidden="true" />
            </a>

            @if($waAdminClean)
                <a href="https://wa.me/{{ $waAdminClean }}" target="_blank" rel="noopener noreferrer"
                   class="flex min-h-14 items-center gap-3 px-4 py-3 transition hover:bg-surface-inset">
                    <x-ui.tile kategori="hijau" ukuran="40" aria-hidden="true">
                        <flux:icon.question-mark-circle class="size-4" />
                    </x-ui.tile>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-medium text-ink">Pusat bantuan &amp; CS</span>
                        <span class="block text-xs text-mute">Hubungi customer service via WhatsApp</span>
                    </span>
                    <flux:icon.arrow-up-right class="size-4 shrink-0 text-mute" aria-hidden="true" />
                </a>
            @endif

            <button wire:click="logout"
                    wire:confirm="Yakin ingin keluar dari akun Anda pada perangkat ini?"
                    type="button"
                    class="flex min-h-14 w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-surface-inset">
                <x-ui.tile kategori="netral" ukuran="40" aria-hidden="true">
                    <flux:icon.arrow-right-start-on-rectangle class="size-4" />
                </x-ui.tile>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-medium text-ink">Keluar akun</span>
                    <span class="block text-xs text-mute">Keluar dari aplikasi pada perangkat ini</span>
                </span>
                <flux:icon.chevron-right class="size-4 shrink-0 text-mute" aria-hidden="true" />
            </button>
        </div>
    </section>

    <p class="mt-6 text-center text-xs text-mute">Tabungan Digital · versi mobile</p>
</div>
