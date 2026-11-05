<div>
    <x-admin.page-header breadcrumb="Sistem" title="Pengaturan Sistem" subtitle="Konfigurasi aplikasi yang hanya bisa diubah oleh admin." />

    @if (session('status'))
        <div class="mb-4 flex items-center gap-2.5 rounded-[12px] border border-primary/30 bg-primary-soft px-4 py-3 text-xs font-semibold text-on-primary-soft">
            <flux:icon.check-circle class="size-4 shrink-0" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-[12px] border border-danger/30 bg-danger-soft px-4 py-3 text-xs font-semibold text-danger">
            <flux:icon.exclamation-circle class="size-4 shrink-0" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-[12px] border border-primary/30 bg-primary-soft px-4 py-3 text-xs font-semibold text-on-primary-soft">
            <flux:icon.check-circle class="size-4 shrink-0" />
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Subtabs Navigation --}}
    <div class="tabs flex gap-1 border-b border-hairline mb-4 overflow-x-auto" role="tablist">
        @foreach ([
            'umum' => 'Umum',
            'admin' => 'Admin & Peran',
            'whatsapp' => 'WhatsApp',
            'backup' => 'Backup & Keamanan',
            'akun' => 'Akun Saya',
        ] as $key => $label)
            <button type="button" wire:click="setTab('{{ $key }}')"
                class="px-3.5 py-2.5 text-sm whitespace-nowrap transition cursor-pointer border-b-2 font-medium {{ $activeTab === $key ? 'border-primary text-ink font-semibold' : 'border-transparent text-mute hover:text-ink' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Tab 1: Umum --}}
    @if ($activeTab === 'umum')
        <form wire:submit="simpanKonfigurasi">
            <div class="rounded-[14px] border border-hairline bg-surface p-5 sm:p-6 shadow-xs">
                <h2 class="text-base font-semibold text-ink mb-4">Konfigurasi Umum</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-x-6 sm:gap-y-4">
                    {{-- Row 1: Nama Koperasi (Kiri) & Batas Toleransi Hari (Kanan) --}}
                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">Nama Koperasi / Instansi</label>
                        <input type="text" wire:model="namaProdukDefault" placeholder="Koperasi Sejahtera Bersama"
                            class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                        @error('namaProdukDefault') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">Batas toleransi keterlambatan setoran (hari)</label>
                        <input type="number" wire:model="batasToleransiHari" placeholder="3"
                            class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                        @error('batasToleransiHari') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    {{-- Row 2: Nominal Penarikan Minimal (Kiri) & Batas Persetujuan Ganda (Kanan) --}}
                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">Nominal penarikan minimal (Rp)</label>
                        <input type="number" wire:model="penarikanMinimal" placeholder="10000"
                            class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                        @error('penarikanMinimal') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">Batas Persetujuan Ganda Penarikan (Rp, 0 = nonaktif)</label>
                        <input type="number" wire:model="penarikanBatasDuaApprover" placeholder="400000"
                            class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                        @error('penarikanBatasDuaApprover') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    @if ($peringatanDuaApprover)
                        <div class="sm:col-span-2 flex items-start gap-2.5 rounded-[12px] bg-warning-soft px-4 py-3 text-xs text-warning-deep border border-warning/30">
                            <flux:icon.exclamation-triangle class="size-4 shrink-0 mt-0.5" />
                            <span>Fitur persetujuan ganda tidak berlaku karena jumlah admin aktif belum memenuhi syarat. Minimal {{ \App\Livewire\Admin\ApprovalPenarikan::MINIMAL_ADMIN_PERSETUJUAN_GANDA }} admin diperlukan agar persetujuan ganda dapat berjalan.</span>
                        </div>
                    @endif

                    {{-- Row 3: Batas Kas Kolektor (Kiri) & Batas Umur Kas (Kanan) --}}
                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">Batas kas kolektor (Rp, 0 = nonaktif)</label>
                        <input type="number" wire:model="batasKasKolektor" placeholder="5000000"
                            class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                        @error('batasKasKolektor') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">Batas umur kas kolektor (hari, 0 = nonaktif)</label>
                        <input type="number" wire:model="batasHariKas" placeholder="7"
                            class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                        @error('batasHariKas') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    {{-- Row 4: Izinkan Kas Kolektor Minus (Kiri) & Batas Offline Per Transaksi (Kanan) --}}
                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">Izinkan kas kolektor minus (D13)</label>
                        <select wire:model="izinkanKasMinus"
                            class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="false">Tidak, penarikan tunai ditolak bila kas kurang</option>
                            <option value="true">Ya, penarikan tunai tetap boleh meski kas minus</option>
                        </select>
                        @error('izinkanKasMinus') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">Batas penarikan offline langsung per transaksi (Rp)</label>
                        <input type="number" wire:model="offlineBatasLangsung" placeholder="1000000"
                            class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                        @error('offlineBatasLangsung') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    {{-- Row 5: Batas Offline Harian (Kiri) & Nomor WhatsApp Bantuan (Kanan) --}}
                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">Batas penarikan offline langsung per hari per kolektor (Rp)</label>
                        <input type="number" wire:model="offlineBatasHarian" placeholder="3000000"
                            class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                        @error('offlineBatasHarian') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">Nomor WhatsApp bantuan</label>
                        <input type="text" wire:model="nomorWaBantuan" placeholder="6281234567890"
                            class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                        @error('nomorWaBantuan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    @if ($peringatanOfflineLangsung)
                        <div class="sm:col-span-2 flex items-start gap-2.5 rounded-[12px] bg-warning-soft px-4 py-3 text-xs text-warning-deep border border-warning/30">
                            <flux:icon.exclamation-triangle class="size-4 shrink-0 mt-0.5" />
                            <span>Penarikan offline langsung aktif, tetapi batas persetujuan ganda nonaktif atau jumlah admin kurang dari {{ \App\Livewire\Admin\ApprovalPenarikan::MINIMAL_ADMIN_PERSETUJUAN_GANDA }}. Penarikan besar bisa selesai tanpa pengawasan ganda.</span>
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-[10px] bg-primary hover:bg-primary-hover px-5 h-[38px] text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer">
                        Simpan Konfigurasi
                    </button>
                </div>
            </div>
        </form>
    @endif

    {{-- Tab 2: Admin & Peran --}}
    @if ($activeTab === 'admin')
        <div class="overflow-hidden rounded-[14px] border border-hairline bg-surface shadow-xs">
            <div class="p-4 sm:p-5 border-b border-hairline">
                <h2 class="text-base font-semibold text-ink">Akun Admin</h2>
                <p class="text-xs text-mute mt-0.5">Daftar pengguna dengan peran admin di sistem ini.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-hairline bg-surface-2">
                            <th class="px-4 py-2.5 font-mono text-xs uppercase tracking-wider text-mute">NAMA</th>
                            <th class="px-4 py-2.5 font-mono text-xs uppercase tracking-wider text-mute">NO. HP</th>
                            <th class="px-4 py-2.5 font-mono text-xs uppercase tracking-wider text-mute">PERAN</th>
                            <th class="px-4 py-2.5 text-right font-mono text-xs uppercase tracking-wider text-mute"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hairline">
                        @foreach ($daftarAdmin as $admin)
                            <tr class="transition hover:bg-surface-2">
                                <td class="px-4 py-3 text-xs font-medium text-ink">{{ $admin->name }}</td>
                                <td class="px-4 py-3 text-xs text-mute font-mono">{{ $admin->no_hp ?? '-' }}</td>
                                <td class="px-4 py-3 text-xs text-body">Admin</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($admin->id === auth()->id())
                                        <span class="inline-flex items-center rounded-full bg-primary-soft text-on-primary-soft px-2.5 py-0.5 text-[11px] font-semibold">Anda</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Tab 3: Integrasi WhatsApp --}}
    @if ($activeTab === 'whatsapp')
        <form wire:submit="simpanWhatsApp" class="space-y-4">
            <div class="rounded-[14px] border border-hairline bg-surface p-5 sm:p-6 shadow-xs space-y-4">
                <div>
                    <h2 class="text-base font-semibold text-ink">Konfigurasi WhatsApp</h2>
                    <div class="mt-2">
                        @if (! $waApiKeyTersimpan)
                            <div class="inline-flex items-center rounded-[10px] bg-warning-soft text-warning-deep px-3 py-1.5 text-xs font-medium">
                                Belum terhubung
                            </div>
                        @else
                            <div class="inline-flex items-center rounded-[10px] bg-primary-soft text-on-primary-soft px-3 py-1.5 text-xs font-medium">
                                WhatsApp terhubung
                            </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">Provider WhatsApp</label>
                        <select wire:model="waProvider"
                            class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="">Pilih provider...</option>
                            <option value="fonnte">Fonnte</option>
                            <option value="wablas">Wablas</option>
                            <option value="official">WhatsApp Business API (Official)</option>
                        </select>
                        @error('waProvider') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-ink mb-1.5">API Key</label>
                        <input
                            type="password"
                            wire:model="waApiKey"
                            placeholder="{{ $waApiKeyTersimpan ? '•••••••• — isi untuk mengganti' : 'Masukkan API Key dari provider Anda' }}"
                            class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                        @error('waApiKey') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-[10px] bg-primary hover:bg-primary-hover px-5 h-[38px] text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer">
                        Simpan Konfigurasi WhatsApp
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-4 rounded-[14px] border border-hairline bg-surface p-5 sm:p-6 shadow-xs space-y-4">
            <div>
                <h2 class="text-base font-semibold text-ink">Uji Coba</h2>
                <p class="text-xs text-mute mt-0.5">Kirim pesan uji coba untuk memastikan integrasi berfungsi.</p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-ink mb-1.5">Nomor WhatsApp tujuan</label>
                    <input type="text" wire:model="waTestNumber" placeholder="628xxxxxxxxxx"
                        class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                    @error('waTestNumber') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <button type="button" wire:click="kirimPesanUjiCoba"
                    class="inline-flex items-center justify-center rounded-[10px] border border-hairline bg-surface hover:bg-surface-2 px-4 h-10 text-xs font-semibold text-ink transition active:scale-95 cursor-pointer">
                    Kirim pesan uji coba
                </button>
            </div>
        </div>
    @endif

    {{-- Tab 4: Backup & Keamanan --}}
    @if ($activeTab === 'backup')
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-[14px] border border-hairline bg-surface p-5 sm:p-6 shadow-xs space-y-4">
                <div>
                    <h2 class="text-base font-semibold text-ink">Backup Database</h2>
                    <p class="text-xs text-mute mt-0.5">Buat cadangan database sekarang.</p>
                </div>

                <div class="rounded-[10px] bg-surface-2 p-3 text-xs text-mute">
                    @if ($backupTerakhir)
                        Backup terakhir: <span class="font-semibold text-ink">{{ $backupTerakhir }}</span>
                    @else
                        Belum pernah backup
                    @endif
                </div>

                <div>
                    <button type="button" wire:click="backupSekarang"
                        class="inline-flex items-center justify-center rounded-[10px] bg-primary hover:bg-primary-hover px-5 h-[38px] text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer">
                        Backup sekarang
                    </button>
                </div>

                <p class="text-[11px] text-mute">
                    File backup akan diunduh dalam format .sql. Pastikan mysqldump tersedia di server.
                </p>
            </div>

            <div class="rounded-[14px] border border-hairline bg-surface p-5 sm:p-6 shadow-xs space-y-4">
                <div>
                    <h2 class="text-base font-semibold text-ink">Retensi Log Aktivitas</h2>
                    <p class="text-xs text-mute mt-0.5">Log yang lebih lama dari periode ini akan dihapus otomatis.</p>
                </div>

                <form wire:submit="simpanRetensiLog" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 pt-2">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-ink mb-1.5">Retensi log</label>
                        <select wire:model="retensiLogBulan"
                            class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                            <option value="3">3 bulan</option>
                            <option value="6">6 bulan</option>
                            <option value="12">12 bulan</option>
                            <option value="24">24 bulan</option>
                        </select>
                    </div>
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-[10px] border border-hairline bg-surface hover:bg-surface-2 px-5 h-10 text-xs font-semibold text-ink transition active:scale-95 cursor-pointer">
                        Simpan
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Tab 5: Akun Saya --}}
    @if ($activeTab === 'akun')
        <div class="rounded-[14px] border border-hairline bg-surface shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-hairline">
                <h2 class="text-base font-semibold text-ink">Akun Saya</h2>
                <p class="text-xs text-mute mt-0.5">Kelola informasi akun pribadi Anda.</p>
            </div>

            <div class="divide-y divide-hairline">
                <button type="button" wire:click="bukaModalProfil"
                   class="flex w-full items-center justify-between p-4 sm:px-5 text-left transition hover:bg-surface-2 cursor-pointer">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-ink">Edit Profil Saya</p>
                        <span class="block text-[11px] text-mute mt-0.5">Ubah nama dan foto profil</span>
                    </div>
                    <flux:icon.chevron-right class="size-4 text-mute shrink-0" />
                </button>

                <button type="button" wire:click="bukaModalPin"
                   class="flex w-full items-center justify-between p-4 sm:px-5 text-left transition hover:bg-surface-2 cursor-pointer">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-ink">Ganti PIN Saya</p>
                        <span class="block text-[11px] text-mute mt-0.5">Perbarui PIN keamanan akun</span>
                    </div>
                    <flux:icon.chevron-right class="size-4 text-mute shrink-0" />
                </button>

                <button type="button" wire:click="bukaModalTema"
                   class="flex w-full items-center justify-between p-4 sm:px-5 text-left transition hover:bg-surface-2 cursor-pointer">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-ink">Tampilan (Terang/Gelap)</p>
                        <span class="block text-[11px] text-mute mt-0.5">Ubah tema tampilan aplikasi</span>
                    </div>
                    <flux:icon.chevron-right class="size-4 text-mute shrink-0" />
                </button>
            </div>
        </div>
    @endif

    {{-- Modal Edit Profil Saya --}}
    @if ($showModalProfil)
        <x-admin.dialog
            title="Edit Profil Saya"
            message="Perbarui nama dan foto profil akun admin Anda."
            confirm-text="Simpan"
            cancel-text="Batal"
            confirm-action="simpanProfil"
            cancel-action="tutupModalProfil"
        >
            <form wire:submit.prevent="simpanProfil" class="space-y-3.5 text-left">
                <div class="flex items-center gap-3.5 pb-2 border-b border-hairline/60">
                    @if ($profilFoto)
                        <img src="{{ $profilFoto->temporaryUrl() }}" alt="Preview Foto" class="size-14 rounded-full object-cover border border-hairline shrink-0">
                    @elseif (auth()->user()->foto_profil_path)
                        <img src="{{ asset('storage/'.auth()->user()->foto_profil_path) }}" alt="Foto Profil" class="size-14 rounded-full object-cover border border-hairline shrink-0">
                    @else
                        <div class="flex size-14 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary font-semibold text-base border border-primary/20">
                            {{ auth()->user()->initials() }}
                        </div>
                    @endif
                    <div>
                        <label class="inline-flex items-center justify-center rounded-[8px] border border-hairline bg-surface px-3 py-1.5 text-xs font-medium text-ink hover:bg-surface-2 cursor-pointer transition">
                            Ganti Foto
                            <input type="file" wire:model="profilFoto" accept="image/*" class="hidden">
                        </label>
                        <p class="text-[11px] text-mute mt-1">Format JPG, PNG max 2MB</p>
                        @error('profilFoto') <p class="text-xs text-danger mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink mb-1">Nama Lengkap</label>
                    <input type="text" wire:model="profilNama" placeholder="Nama Admin"
                        class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                    @error('profilNama') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink mb-1">No. HP (Login)</label>
                    <input type="text" value="{{ $profilNoHp }}" disabled readonly
                        class="h-10 w-full rounded-[10px] border border-hairline bg-surface-2 px-3 text-xs text-mute cursor-not-allowed" />
                    <p class="mt-1 text-[11px] text-mute">No. HP tidak dapat diubah karena digunakan untuk login.</p>
                </div>
            </form>
        </x-admin.dialog>
    @endif

    {{-- Modal Ganti PIN Saya --}}
    @if ($showModalPin)
        <x-admin.dialog
            title="Ganti PIN Saya"
            message="Pastikan PIN Anda aman dan mudah diingat (6 digit angka)."
            confirm-text="Simpan PIN"
            cancel-text="Batal"
            confirm-action="simpanPin"
            cancel-action="tutupModalPin"
        >
            <form wire:submit.prevent="simpanPin" class="space-y-3.5 text-left">
                <div>
                    <label class="block text-xs font-medium text-ink mb-1">PIN Saat Ini</label>
                    <input type="password" wire:model="current_pin" maxlength="6" inputmode="numeric" placeholder="••••••"
                        class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-mute tracking-widest focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                    @error('current_pin') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink mb-1">PIN Baru (6 angka)</label>
                    <input type="password" wire:model="pin" maxlength="6" inputmode="numeric" placeholder="••••••"
                        class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-mute tracking-widest focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                    @error('pin') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink mb-1">Konfirmasi PIN Baru</label>
                    <input type="password" wire:model="pin_confirmation" maxlength="6" inputmode="numeric" placeholder="••••••"
                        class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-mute tracking-widest focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                    @error('pin_confirmation') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
            </form>
        </x-admin.dialog>
    @endif

    {{-- Modal Tampilan (Terang/Gelap) --}}
    @if ($showModalTema)
        <x-admin.dialog
            title="Tampilan (Terang/Gelap)"
            message="Pilih preferensi tema tampilan aplikasi."
            confirm-text="Selesai"
            cancel-text="Tutup"
            confirm-action="tutupModalTema"
            cancel-action="tutupModalTema"
        >
            <div x-data class="space-y-2.5 text-left">
                <button type="button" @click="$flux.appearance = 'light'"
                    class="flex w-full items-center justify-between rounded-[10px] border p-3 transition cursor-pointer"
                    :class="$flux.appearance === 'light' ? 'border-primary bg-primary-soft/30 text-ink' : 'border-hairline bg-surface text-ink hover:bg-surface-2'">
                    <div class="flex items-center gap-3">
                        <flux:icon.sun class="size-4.5 text-warning-deep" />
                        <div class="text-left">
                            <p class="text-xs font-semibold">Mode Terang</p>
                            <p class="text-[11px] text-mute">Tampilan bersih berlatar terang</p>
                        </div>
                    </div>
                    <template x-if="$flux.appearance === 'light'">
                        <flux:icon.check class="size-4 text-primary" />
                    </template>
                </button>

                <button type="button" @click="$flux.appearance = 'dark'"
                    class="flex w-full items-center justify-between rounded-[10px] border p-3 transition cursor-pointer"
                    :class="$flux.appearance === 'dark' ? 'border-primary bg-primary-soft/30 text-ink' : 'border-hairline bg-surface text-ink hover:bg-surface-2'">
                    <div class="flex items-center gap-3">
                        <flux:icon.moon class="size-4.5 text-ink" />
                        <div class="text-left">
                            <p class="text-xs font-semibold">Mode Gelap</p>
                            <p class="text-[11px] text-mute">Tampilan nyaman di mata saat minim cahaya</p>
                        </div>
                    </div>
                    <template x-if="$flux.appearance === 'dark'">
                        <flux:icon.check class="size-4 text-primary" />
                    </template>
                </button>

                <button type="button" @click="$flux.appearance = 'system'"
                    class="flex w-full items-center justify-between rounded-[10px] border p-3 transition cursor-pointer"
                    :class="$flux.appearance === 'system' ? 'border-primary bg-primary-soft/30 text-ink' : 'border-hairline bg-surface text-ink hover:bg-surface-2'">
                    <div class="flex items-center gap-3">
                        <flux:icon.computer-desktop class="size-4.5 text-mute" />
                        <div class="text-left">
                            <p class="text-xs font-semibold">Ikuti Sistem</p>
                            <p class="text-[11px] text-mute">Menyesuaikan dengan pengaturan sistem perangkat</p>
                        </div>
                    </div>
                    <template x-if="$flux.appearance === 'system'">
                        <flux:icon.check class="size-4 text-primary" />
                    </template>
                </button>
            </div>
        </x-admin.dialog>
    @endif

    <p class="mt-8 text-center text-xs text-mute">Tabungan Digital · versi alpha</p>
</div>
