<div class="mx-auto max-w-4xl">
    <flux:heading size="xl">Pengaturan Sistem</flux:heading>
    <flux:subheading class="mb-6">Konfigurasi aplikasi yang hanya bisa diubah oleh admin.</flux:subheading>

    @if (session('status'))
        <flux:callout variant="success" class="mb-6" icon="check-circle">
            {{ session('status') }}
        </flux:callout>
    @endif

    @if (session('error'))
        <flux:callout variant="danger" class="mb-6" icon="exclamation-circle">
            {{ session('error') }}
        </flux:callout>
    @endif

    {{-- Tab Navigation --}}
    <div class="mb-6 flex gap-1 rounded-xl bg-gray-50 dark:bg-zinc-800 p-1 shadow-[inset_0_0_0_1px_#ebebeb] dark:shadow-[inset_0_0_0_1px_#3f3f46]">
        @php
            $tabs = [
                'umum' => 'Umum',
                'admin' => 'Admin & Peran',
                'whatsapp' => 'WhatsApp',
                'backup' => 'Backup & Keamanan',
                'akun' => 'Akun Saya',
            ];
        @endphp
        @foreach ($tabs as $key => $label)
            <button wire:click="setTab('{{ $key }}')"
                    class="flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition
                           {{ $activeTab === $key
                              ? 'bg-indigo-800 text-white shadow-sm dark:bg-zinc-600'
                              : 'text-gray-500 hover:text-gray-900 dark:text-zinc-400 dark:hover:text-white' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Tab 1: Umum --}}
    @if ($activeTab === 'umum')
        <form wire:submit="simpanKonfigurasi" class="space-y-6">
            <flux:card class="space-y-4">
                <flux:heading size="lg">Konfigurasi Umum</flux:heading>

                <flux:input
                    wire:model="namaProdukDefault"
                    label="Nama Koperasi / Instansi"
                    placeholder="Contoh: Koperasi Sejahtera Bersama"
                />

                <flux:input
                    type="number"
                    wire:model="batasToleransiHari"
                    label="Batas Toleransi Keterlambatan Setoran (hari)"
                    placeholder="3"
                />

                <flux:input
                    type="number"
                    wire:model="penarikanMinimal"
                    label="Nominal Penarikan Minimal (Rp)"
                    placeholder="10000"
                />

                <flux:input
                    type="number"
                    wire:model="batasKasKolektor"
                    label="Batas Kas Kolektor (Rp, 0 = nonaktif)"
                    placeholder="0"
                />

                <flux:input
                    type="number"
                    wire:model="batasHariKas"
                    label="Batas Umur Kas Kolektor (hari, 0 = nonaktif)"
                    placeholder="0"
                />

                <flux:input
                    wire:model="nomorWaBantuan"
                    label="Nomor WhatsApp Bantuan"
                    placeholder="6281234567890"
                />

                <div>
                    <flux:button type="submit" variant="primary">Simpan Konfigurasi</flux:button>
                </div>
            </flux:card>
        </form>
    @endif

    {{-- Tab 2: Admin & Peran --}}
    @if ($activeTab === 'admin')
        <flux:card class="space-y-4">
            <flux:heading size="lg">Akun Admin</flux:heading>
            <flux:subheading>Daftar pengguna dengan peran admin di sistem ini.</flux:subheading>

            <div class="rounded-2xl shadow-[inset_0_0_0_1px_#ebebeb] dark:shadow-[inset_0_0_0_1px_#3f3f46]">
                @foreach ($daftarAdmin as $admin)
                    <div class="flex items-center justify-between px-5 py-3 border-b border-[#ebebeb] dark:border-zinc-600 last:border-b-0">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $admin->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-zinc-400">{{ $admin->no_hp }}</p>
                        </div>
                        @if ($admin->id === auth()->id())
                            <flux:badge size="sm">Anda</flux:badge>
                        @endif
                    </div>
                @endforeach
            </div>
        </flux:card>
    @endif

    {{-- Tab 3: Integrasi WhatsApp --}}
    @if ($activeTab === 'whatsapp')
        @if (! $waApiKeyTersimpan)
            <flux:callout variant="danger" class="mb-6" icon="exclamation-triangle">
                <strong>Belum terhubung</strong> — notifikasi WhatsApp ke nasabah/kolektor belum aktif.
            </flux:callout>
        @else
            <flux:callout variant="success" class="mb-6" icon="check-circle">
                WhatsApp terhubung — notifikasi aktif.
            </flux:callout>
        @endif

        <form wire:submit="simpanWhatsApp" class="space-y-6">
            <flux:card class="space-y-4">
                <flux:heading size="lg">Konfigurasi WhatsApp</flux:heading>

                <flux:select wire:model="waProvider" label="Provider WhatsApp">
                    <flux:select.option value="">Pilih provider...</flux:select.option>
                    <flux:select.option value="fonnte">Fonnte</flux:select.option>
                    <flux:select.option value="wablas">Wablas</flux:select.option>
                    <flux:select.option value="official">WhatsApp Business API (Official)</flux:select.option>
                </flux:select>

                <flux:input
                    wire:model="waApiKey"
                    label="API Key"
                    placeholder="{{ $waApiKeyTersimpan ? '•••••••• — isi untuk mengganti' : 'Masukkan API Key dari provider Anda' }}"
                    type="password"
                />

                <div>
                    <flux:button type="submit" variant="primary">Simpan Konfigurasi WhatsApp</flux:button>
                </div>
            </flux:card>
        </form>

        <flux:card class="mt-6 space-y-4">
            <flux:heading size="lg">Uji Coba</flux:heading>
            <flux:subheading>Kirim pesan uji coba untuk memastikan integrasi berfungsi.</flux:subheading>

            <div class="flex items-end gap-3">
                <div class="flex-1">
                    <flux:input
                        wire:model="waTestNumber"
                        label="Nomor WhatsApp Tujuan"
                        placeholder="6281234567890"
                    />
                </div>
                <flux:button wire:click="kirimPesanUjiCoba" variant="primary" icon="paper-airplane">
                    Kirim Uji Coba
                </flux:button>
            </div>
        </flux:card>
    @endif

    {{-- Tab 4: Backup & Keamanan --}}
    @if ($activeTab === 'backup')
        <flux:card class="space-y-4">
            <flux:heading size="lg">Backup Database</flux:heading>

            <div class="rounded-xl bg-gray-50 dark:bg-zinc-700/50 p-4 shadow-[inset_0_0_0_1px_#ebebeb] dark:shadow-[inset_0_0_0_1px_#3f3f46]">
                <p class="text-sm text-gray-500 dark:text-zinc-400">
                    @if ($backupTerakhir)
                        Backup terakhir: <span class="font-medium text-gray-900 dark:text-white">{{ $backupTerakhir }}</span>
                    @else
                        Belum pernah backup
                    @endif
                </p>
            </div>

            <div>
                <flux:button wire:click="backupSekarang" variant="primary" icon="arrow-down-tray">
                    Backup Sekarang
                </flux:button>
            </div>

            <p class="text-xs text-gray-500 dark:text-zinc-500">
                File backup akan diunduh dalam format .sql. Pastikan mysqldump tersedia di server.
            </p>
        </flux:card>

        <flux:card class="mt-6 space-y-4">
            <flux:heading size="lg">Retensi Log Aktivitas</flux:heading>
            <flux:subheading>Log yang lebih lama dari periode ini akan dihapus secara otomatis (jadwal terpisah).</flux:subheading>

            <form wire:submit="simpanRetensiLog" class="flex items-end gap-3">
                <div class="flex-1">
                    <flux:select wire:model="retensiLogBulan" label="Retensi Log">
                        <flux:select.option value="3">3 bulan</flux:select.option>
                        <flux:select.option value="6">6 bulan</flux:select.option>
                        <flux:select.option value="12">12 bulan</flux:select.option>
                        <flux:select.option value="24">24 bulan</flux:select.option>
                    </flux:select>
                </div>
                <flux:button type="submit" variant="primary">Simpan</flux:button>
            </form>
        </flux:card>
    @endif

    {{-- Tab 5: Akun Saya --}}
    @if ($activeTab === 'akun')
        <flux:card class="space-y-4">
            <flux:heading size="lg">Akun Saya</flux:heading>
            <flux:subheading>Kelola informasi akun pribadi Anda.</flux:subheading>

            <div class="space-y-3">
                <a href="{{ route('admin.settings.profile') }}" wire:navigate
                   class="flex items-center gap-4 rounded-xl border border-[#ebebeb] dark:border-zinc-600 p-4 transition hover:bg-black/[0.02] dark:hover:bg-white/[0.05]">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Edit Profil Saya</p>
                        <p class="text-xs text-gray-500 dark:text-zinc-400">Ubah nama dan foto profil</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#a1a1a1] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </a>

                <a href="{{ route('admin.settings.security') }}" wire:navigate
                   class="flex items-center gap-4 rounded-xl border border-[#ebebeb] dark:border-zinc-600 p-4 transition hover:bg-black/[0.02] dark:hover:bg-white/[0.05]">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-[#ab570a]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Ganti PIN Saya</p>
                        <p class="text-xs text-gray-500 dark:text-zinc-400">Perbarui PIN keamanan akun</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#a1a1a1] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </a>

                <a href="{{ route('admin.settings.appearance') }}" wire:navigate
                   class="flex items-center gap-4 rounded-xl border border-[#ebebeb] dark:border-zinc-600 p-4 transition hover:bg-black/[0.02] dark:hover:bg-white/[0.05]">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#e8dff5] text-[#7c3aed]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Tampilan (Terang/Gelap)</p>
                        <p class="text-xs text-gray-500 dark:text-zinc-400">Ubah tema tampilan aplikasi</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#a1a1a1] dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </a>
            </div>
        </flux:card>
    @endif
</div>
