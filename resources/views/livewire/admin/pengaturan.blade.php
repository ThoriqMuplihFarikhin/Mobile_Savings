<div class="mx-auto max-w-4xl">
    <x-admin.page-header breadcrumb="Sistem" title="Pengaturan Sistem" subtitle="Konfigurasi aplikasi yang hanya bisa diubah oleh admin." />

    @if (session('status'))
        <div class="mb-6 flex items-center gap-2.5 rounded-[12px] border border-primary/30 bg-primary-soft px-4 py-3 text-xs font-semibold text-primary-deep">
            <flux:icon.check-circle class="size-4 shrink-0" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 flex items-center gap-2.5 rounded-[12px] border border-danger/30 bg-danger-soft px-4 py-3 text-xs font-semibold text-danger">
            <flux:icon.exclamation-circle class="size-4 shrink-0" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Tab Navigation --}}
    <div class="mb-6 flex gap-1 rounded-[12px] border border-hairline-warm bg-surface-alt/70 p-1 dark:border-hairline/60 dark:bg-zinc-800/60 overflow-x-auto">
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
            <button type="button" wire:click="setTab('{{ $key }}')"
                    class="flex-1 min-w-[120px] rounded-[9px] px-4 py-2 text-xs font-semibold transition active:scale-95 cursor-pointer
                           {{ $activeTab === $key
                              ? 'bg-white text-ink shadow-xs dark:bg-zinc-800 dark:text-zinc-100'
                              : 'text-mute hover:text-ink dark:text-zinc-400 dark:hover:text-zinc-100' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Tab 1: Umum --}}
    @if ($activeTab === 'umum')
        <form wire:submit="simpanKonfigurasi" class="space-y-6">
            <div class="rounded-[14px] border border-hairline-warm bg-white p-6 shadow-xs space-y-4 dark:border-hairline/60 dark:bg-surface">
                <div>
                    <h2 class="text-base font-semibold text-ink dark:text-zinc-100">Konfigurasi Umum</h2>
                    <p class="text-xs text-mute dark:text-zinc-400 mt-0.5">Kelola batasan transaksi, toleransi hari, dan pengawasan kas.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-ink dark:text-zinc-200 mb-1.5">Nama Koperasi / Instansi</label>
                        <input type="text" wire:model="namaProdukDefault" placeholder="Contoh: Koperasi Sejahtera Bersama"
                            class="h-[38px] w-full rounded-[10px] border border-hairline-warm bg-white px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100" />
                        @error('namaProdukDefault') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-ink dark:text-zinc-200 mb-1.5">Batas Toleransi Keterlambatan Setoran (hari)</label>
                        <input type="number" wire:model="batasToleransiHari" placeholder="3"
                            class="h-[38px] w-full rounded-[10px] border border-hairline-warm bg-white px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100" />
                        @error('batasToleransiHari') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-ink dark:text-zinc-200 mb-1.5">Nominal Penarikan Minimal (Rp)</label>
                        <input type="number" wire:model="penarikanMinimal" placeholder="10000"
                            class="h-[38px] w-full rounded-[10px] border border-hairline-warm bg-white px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100" />
                        @error('penarikanMinimal') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-ink dark:text-zinc-200 mb-1.5">Batas Persetujuan Ganda Penarikan (Rp, 0 = nonaktif)</label>
                        <input type="number" wire:model="penarikanBatasDuaApprover" placeholder="0"
                            class="h-[38px] w-full rounded-[10px] border border-hairline-warm bg-white px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100" />
                        @error('penarikanBatasDuaApprover') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    @if ($peringatanDuaApprover)
                        <div class="sm:col-span-2 flex items-start gap-2.5 rounded-[12px] bg-warning-soft px-4 py-3 text-xs text-warning-deep border border-warning/30">
                            <flux:icon.exclamation-triangle class="size-4 shrink-0 mt-0.5" />
                            <span>Fitur persetujuan ganda tidak berlaku karena jumlah admin aktif belum memenuhi syarat. Minimal {{ \App\Livewire\Admin\ApprovalPenarikan::MINIMAL_ADMIN_PERSETUJUAN_GANDA }} admin diperlukan agar persetujuan ganda dapat berjalan.</span>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-medium text-ink dark:text-zinc-200 mb-1.5">Batas Kas Kolektor (Rp, 0 = nonaktif)</label>
                        <input type="number" wire:model="batasKasKolektor" placeholder="0"
                            class="h-[38px] w-full rounded-[10px] border border-hairline-warm bg-white px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100" />
                        @error('batasKasKolektor') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-ink dark:text-zinc-200 mb-1.5">Batas Umur Kas Kolektor (hari, 0 = nonaktif)</label>
                        <input type="number" wire:model="batasHariKas" placeholder="0"
                            class="h-[38px] w-full rounded-[10px] border border-hairline-warm bg-white px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100" />
                        @error('batasHariKas') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-ink dark:text-zinc-200 mb-1.5">Izinkan Kas Kolektor Minus (D13)</label>
                        <select wire:model="izinkanKasMinus"
                            class="h-[38px] w-full rounded-[10px] border border-hairline-warm bg-white px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100">
                            <option value="false">Tidak — penarikan tunai ditolak bila kas kurang</option>
                            <option value="true">Ya — penarikan tunai tetap boleh meski kas minus</option>
                        </select>
                        @error('izinkanKasMinus') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-ink dark:text-zinc-200 mb-1.5">Batas Penarikan Offline Langsung per Transaksi (Rp, 0 = nonaktif)</label>
                        <input type="number" wire:model="offlineBatasLangsung" placeholder="0"
                            class="h-[38px] w-full rounded-[10px] border border-hairline-warm bg-white px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100" />
                        @error('offlineBatasLangsung') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-ink dark:text-zinc-200 mb-1.5">Batas Penarikan Offline Langsung per Hari per Kolektor (Rp, 0 = nonaktif)</label>
                        <input type="number" wire:model="offlineBatasHarian" placeholder="0"
                            class="h-[38px] w-full rounded-[10px] border border-hairline-warm bg-white px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100" />
                        @error('offlineBatasHarian') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    @if ($peringatanOfflineLangsung)
                        <div class="sm:col-span-2 flex items-start gap-2.5 rounded-[12px] bg-warning-soft px-4 py-3 text-xs text-warning-deep border border-warning/30">
                            <flux:icon.exclamation-triangle class="size-4 shrink-0 mt-0.5" />
                            <span>Penarikan offline langsung aktif, tetapi batas persetujuan ganda nonaktif atau jumlah admin kurang dari {{ \App\Livewire\Admin\ApprovalPenarikan::MINIMAL_ADMIN_PERSETUJUAN_GANDA }}. Penarikan besar bisa selesai tanpa pengawasan ganda.</span>
                        </div>
                    @endif

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-ink dark:text-zinc-200 mb-1.5">Nomor WhatsApp Bantuan</label>
                        <input type="text" wire:model="nomorWaBantuan" placeholder="6281234567890"
                            class="h-[38px] w-full rounded-[10px] border border-hairline-warm bg-white px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100" />
                        @error('nomorWaBantuan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-[10px] bg-primary hover:bg-primary-hover px-5 h-[38px] text-xs font-semibold text-on-primary shadow-xs transition active:scale-95 cursor-pointer">
                        <flux:icon.check class="size-4" />
                        Simpan Konfigurasi
                    </button>
                </div>
            </div>
        </form>
    @endif

    {{-- Tab 2: Admin & Peran --}}
    @if ($activeTab === 'admin')
        <div class="rounded-[14px] border border-hairline-warm bg-white p-6 shadow-xs space-y-4 dark:border-hairline/60 dark:bg-surface">
            <div>
                <h2 class="text-base font-semibold text-ink dark:text-zinc-100">Akun Admin</h2>
                <p class="text-xs text-mute dark:text-zinc-400 mt-0.5">Daftar pengguna dengan peran admin di sistem ini.</p>
            </div>

            <div class="overflow-hidden rounded-[12px] border border-hairline-warm dark:border-hairline/60 divide-y divide-hairline">
                @foreach ($daftarAdmin as $admin)
                    <div class="flex items-center justify-between px-5 py-3.5 bg-white dark:bg-surface">
                        <div>
                            <p class="text-xs font-semibold text-ink dark:text-white">{{ $admin->name }}</p>
                            <p class="text-[11px] text-mute dark:text-zinc-400">{{ $admin->no_hp }}</p>
                        </div>
                        @if ($admin->id === auth()->id())
                            <span class="inline-flex items-center rounded-full bg-primary-soft text-primary-deep border border-primary/30 px-2.5 py-0.5 text-[11px] font-semibold">Anda</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tab 3: Integrasi WhatsApp --}}
    @if ($activeTab === 'whatsapp')
        @if (! $waApiKeyTersimpan)
            <div class="mb-6 flex items-center gap-2.5 rounded-[12px] border border-danger/30 bg-danger-soft px-4 py-3 text-xs font-semibold text-danger">
                <flux:icon.exclamation-triangle class="size-4 shrink-0" />
                <span><strong>Belum terhubung</strong> — notifikasi WhatsApp ke nasabah/kolektor belum aktif.</span>
            </div>
        @else
            <div class="mb-6 flex items-center gap-2.5 rounded-[12px] border border-primary/30 bg-primary-soft px-4 py-3 text-xs font-semibold text-primary-deep">
                <flux:icon.check-circle class="size-4 shrink-0" />
                <span>WhatsApp terhubung — notifikasi aktif.</span>
            </div>
        @endif

        <form wire:submit="simpanWhatsApp" class="space-y-6">
            <div class="rounded-[14px] border border-hairline-warm bg-white p-6 shadow-xs space-y-4 dark:border-hairline/60 dark:bg-surface">
                <div>
                    <h2 class="text-base font-semibold text-ink dark:text-zinc-100">Konfigurasi WhatsApp</h2>
                    <p class="text-xs text-mute dark:text-zinc-400 mt-0.5">Pengaturan provider gateway notifikasi WhatsApp.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-ink dark:text-zinc-200 mb-1.5">Provider WhatsApp</label>
                        <select wire:model="waProvider"
                            class="h-[38px] w-full rounded-[10px] border border-hairline-warm bg-white px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100">
                            <option value="">Pilih provider...</option>
                            <option value="fonnte">Fonnte</option>
                            <option value="wablas">Wablas</option>
                            <option value="official">WhatsApp Business API (Official)</option>
                        </select>
                        @error('waProvider') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-ink dark:text-zinc-200 mb-1.5">API Key</label>
                        <input
                            type="password"
                            wire:model="waApiKey"
                            placeholder="{{ $waApiKeyTersimpan ? '•••••••• — isi untuk mengganti' : 'Masukkan API Key dari provider Anda' }}"
                            class="h-[38px] w-full rounded-[10px] border border-hairline-warm bg-white px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100" />
                        @error('waApiKey') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-[10px] bg-primary hover:bg-primary-hover px-5 h-[38px] text-xs font-semibold text-on-primary shadow-xs transition active:scale-95 cursor-pointer">
                        <flux:icon.check class="size-4" />
                        Simpan Konfigurasi WhatsApp
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-6 rounded-[14px] border border-hairline-warm bg-white p-6 shadow-xs space-y-4 dark:border-hairline/60 dark:bg-surface">
            <div>
                <h2 class="text-base font-semibold text-ink dark:text-zinc-100">Uji Coba</h2>
                <p class="text-xs text-mute dark:text-zinc-400 mt-0.5">Kirim pesan uji coba untuk memastikan integrasi berfungsi.</p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-ink dark:text-zinc-200 mb-1.5">Nomor WhatsApp Tujuan</label>
                    <input type="text" wire:model="waTestNumber" placeholder="6281234567890"
                        class="h-[38px] w-full rounded-[10px] border border-hairline-warm bg-white px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100" />
                    @error('waTestNumber') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <button type="button" wire:click="kirimPesanUjiCoba"
                    class="inline-flex items-center justify-center gap-1.5 rounded-[10px] border border-hairline-strong bg-white px-4 h-[38px] text-xs font-semibold text-body hover:bg-inset transition active:scale-95 cursor-pointer dark:border-hairline dark:bg-surface dark:text-zinc-200">
                    <flux:icon.paper-airplane class="size-4" />
                    Kirim Uji Coba
                </button>
            </div>
        </div>
    @endif

    {{-- Tab 4: Backup & Keamanan --}}
    @if ($activeTab === 'backup')
        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-[14px] border border-hairline-warm bg-white p-6 shadow-xs space-y-4 dark:border-hairline/60 dark:bg-surface">
                <div>
                    <h2 class="text-base font-semibold text-ink dark:text-zinc-100">Backup Database</h2>
                    <p class="text-xs text-mute dark:text-zinc-400 mt-0.5">Cadangkan data sistem dalam format .sql.</p>
                </div>

                <div class="rounded-[10px] bg-surface-alt/70 p-3.5 border border-hairline dark:bg-zinc-800/40">
                    <p class="text-xs text-mute dark:text-zinc-400">
                        @if ($backupTerakhir)
                            Backup terakhir: <span class="font-semibold text-ink dark:text-white">{{ $backupTerakhir }}</span>
                        @else
                            Belum pernah backup
                        @endif
                    </p>
                </div>

                <div>
                    <button type="button" wire:click="backupSekarang"
                        class="inline-flex items-center gap-1.5 rounded-[10px] bg-primary hover:bg-primary-hover px-4 h-[38px] text-xs font-semibold text-on-primary shadow-xs transition active:scale-95 cursor-pointer">
                        <flux:icon.arrow-down-tray class="size-4" />
                        Backup Sekarang
                    </button>
                </div>

                <p class="text-[11px] text-mute dark:text-zinc-400">
                    File backup akan diunduh dalam format .sql. Pastikan mysqldump tersedia di server.
                </p>
            </div>

            <div class="rounded-[14px] border border-hairline-warm bg-white p-6 shadow-xs space-y-4 dark:border-hairline/60 dark:bg-surface">
                <div>
                    <h2 class="text-base font-semibold text-ink dark:text-zinc-100">Retensi Log Aktivitas</h2>
                    <p class="text-xs text-mute dark:text-zinc-400 mt-0.5">Log yang lebih lama dari periode ini akan dihapus secara otomatis.</p>
                </div>

                <form wire:submit="simpanRetensiLog" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 pt-2">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-ink dark:text-zinc-200 mb-1.5">Retensi Log</label>
                        <select wire:model="retensiLogBulan"
                            class="h-[38px] w-full rounded-[10px] border border-hairline-warm bg-white px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:border-hairline/60 dark:bg-zinc-800 dark:text-zinc-100">
                            <option value="3">3 bulan</option>
                            <option value="6">6 bulan</option>
                            <option value="12">12 bulan</option>
                            <option value="24">24 bulan</option>
                        </select>
                    </div>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-1.5 rounded-[10px] bg-primary hover:bg-primary-hover px-4 h-[38px] text-xs font-semibold text-on-primary shadow-xs transition active:scale-95 cursor-pointer">
                        Simpan
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Tab 5: Akun Saya --}}
    @if ($activeTab === 'akun')
        <div class="rounded-[14px] border border-hairline-warm bg-white p-6 shadow-xs space-y-4 dark:border-hairline/60 dark:bg-surface">
            <div>
                <h2 class="text-base font-semibold text-ink dark:text-zinc-100">Akun Saya</h2>
                <p class="text-xs text-mute dark:text-zinc-400 mt-0.5">Kelola informasi akun pribadi Anda.</p>
            </div>

            <div class="space-y-3">
                <a href="{{ route('admin.settings.profile') }}" wire:navigate
                   class="flex items-center gap-4 rounded-[12px] border border-hairline-warm p-4 transition hover:bg-surface-alt/50 dark:border-hairline/60 dark:hover:bg-zinc-800/40">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-info-soft text-info border border-info/20">
                        <flux:icon.user class="size-5" />
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-ink dark:text-white">Edit Profil Saya</p>
                        <p class="text-[11px] text-mute dark:text-zinc-400">Ubah nama dan foto profil</p>
                    </div>
                    <flux:icon.chevron-right class="size-4 text-mute dark:text-zinc-400" />
                </a>

                <a href="{{ route('admin.settings.security') }}" wire:navigate
                   class="flex items-center gap-4 rounded-[12px] border border-hairline-warm p-4 transition hover:bg-surface-alt/50 dark:border-hairline/60 dark:hover:bg-zinc-800/40">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-warning-soft text-warning-deep border border-warning/20">
                        <flux:icon.lock-closed class="size-5" />
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-ink dark:text-white">Ganti PIN Saya</p>
                        <p class="text-[11px] text-mute dark:text-zinc-400">Perbarui PIN keamanan akun</p>
                    </div>
                    <flux:icon.chevron-right class="size-4 text-mute dark:text-zinc-400" />
                </a>

                <a href="{{ route('admin.settings.appearance') }}" wire:navigate
                   class="flex items-center gap-4 rounded-[12px] border border-hairline-warm p-4 transition hover:bg-surface-alt/50 dark:border-hairline/60 dark:hover:bg-zinc-800/40">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-primary-deep border border-primary/20">
                        <flux:icon.swatch class="size-5" />
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-ink dark:text-white">Tampilan (Terang/Gelap)</p>
                        <p class="text-[11px] text-mute dark:text-zinc-400">Ubah tema tampilan aplikasi</p>
                    </div>
                    <flux:icon.chevron-right class="size-4 text-mute dark:text-zinc-400" />
                </a>
            </div>
        </div>
    @endif

    <p class="mt-8 text-center text-xs text-mute dark:text-zinc-400">Tabungan Digital · versi alpha</p>
</div>
