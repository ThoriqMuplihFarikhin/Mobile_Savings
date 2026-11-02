<div>
    {{-- Header Halaman --}}
    <x-admin.page-header
        title="Manajemen Nasabah"
        subtitle="Kelola data nasabah tabungan digital."
    >
        <x-slot:actions>
            <button
                type="button"
                wire:click="toggleForm"
                class="min-h-11 h-[38px] inline-flex items-center justify-center gap-1.5 rounded-[10px] bg-primary px-4 text-xs font-semibold text-white transition hover:bg-primary-hover active:scale-95 cursor-pointer shadow-xs"
            >
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Nasabah
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Banner Notifikasi Flash --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-[10px] bg-primary-soft px-4 py-3 text-xs font-medium text-on-primary-soft border border-primary/20">
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-[10px] bg-danger-soft px-4 py-3 text-xs font-medium text-danger border border-danger/20">
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Modal Dialog Tambah / Edit Nasabah (Popup) --}}
    @if($showForm)
        <x-admin.dialog
            title="{{ $editId ? 'Edit Nasabah' : 'Tambah Nasabah Baru' }}"
            message="Lengkapi data profil nasabah di bawah ini."
            confirm-text="{{ $editId ? 'Simpan Perubahan' : 'Tambah Nasabah' }}"
            cancel-text="Batal"
            confirm-action="save"
            cancel-action="toggleForm"
        >
            <form wire:submit.prevent="save" class="space-y-3.5 text-left">
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Nama Lengkap</label>
                    <input
                        type="text"
                        wire:model="nama"
                        class="h-[38px] w-full rounded-[10px] border border-hairline bg-surface px-3 text-sm text-ink placeholder-mute focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent"
                        placeholder="Nama nasabah"
                    />
                    @error('nama') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">No. HP</label>
                    <input
                        type="text"
                        wire:model="no_hp"
                        class="h-[38px] w-full rounded-[10px] border border-hairline bg-surface px-3 text-sm text-ink placeholder-mute focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent"
                        placeholder="08xxxxxxxxxx"
                    />
                    @error('no_hp') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Alamat</label>
                    <textarea
                        wire:model="alamat"
                        rows="2"
                        class="w-full rounded-[10px] border border-hairline bg-surface px-3 py-2 text-sm text-ink placeholder-mute focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent"
                        placeholder="Alamat lengkap"
                    ></textarea>
                    @error('alamat') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">PIN (6 digit) {{ $editId ? '— Kosongkan jika tidak diubah' : '' }}</label>
                    <input
                        type="password"
                        wire:model="pin"
                        maxlength="6"
                        class="h-[38px] w-full rounded-[10px] border border-hairline bg-surface px-3 text-sm text-ink placeholder-mute focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent"
                        placeholder="123456"
                    />
                    @error('pin') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
            </form>
        </x-admin.dialog>
    @endif

    {{-- Kartu Tabel Utama --}}
    <x-admin.table :count="$nasabah->count()" :total="$nasabah->total()" :pagination="$nasabah->links()">
        {{-- Toolbar Filter & Pencarian --}}
        <x-slot:toolbar>
            <x-admin.toolbar>
                {{-- Pencarian --}}
                <div class="relative flex-1 min-w-[200px] max-w-[340px]">
                    <svg class="pointer-events-none absolute left-3 top-2.5 size-4 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4" />
                    </svg>
                    <input
                        type="text"
                        wire:model.live="search"
                        placeholder="Cari nama atau no. HP..."
                        class="h-[36px] w-full rounded-[10px] border border-hairline bg-surface-2 pl-9 pr-3 text-xs text-ink placeholder-mute focus:border-ink focus:outline-none focus:bg-surface focus:ring-1 focus:ring-accent"
                    />
                </div>

                {{-- Filter Mode --}}
                <div class="flex items-center">
                    <x-admin.segment
                        wire:model.live="modeFilter"
                        :selected="$modeFilter"
                        :options="[
                            '' => 'Semua Mode',
                            'digital' => 'Digital',
                            'offline' => 'Offline',
                        ]"
                    />
                </div>

                {{-- Filter Status --}}
                <div class="flex items-center">
                    <x-admin.segment
                        wire:model.live="statusFilter"
                        :selected="$statusFilter"
                        :options="[
                            '' => 'Semua Status',
                            'aktif' => 'Aktif',
                            'pending_verifikasi' => 'Pending',
                            'terkunci' => 'Terkunci',
                            'ditolak' => 'Ditolak',
                        ]"
                    />
                </div>
            </x-admin.toolbar>
        </x-slot:toolbar>

        {{-- Head Tabel Desktop --}}
        <x-slot:head>
            <th class="px-3.5 py-2.5 font-medium text-xs uppercase tracking-[0.04em] text-mute">Nama</th>
            <th class="px-3.5 py-2.5 font-medium text-xs uppercase tracking-[0.04em] text-mute">No. HP</th>
            <th class="px-3.5 py-2.5 font-medium text-xs uppercase tracking-[0.04em] text-mute">Mode</th>
            <th class="px-3.5 py-2.5 font-medium text-xs uppercase tracking-[0.04em] text-mute">Status</th>
            <th class="px-3.5 py-2.5 font-medium text-xs uppercase tracking-[0.04em] text-mute">Kolektor</th>
            <th class="px-3.5 py-2.5 font-medium text-xs uppercase tracking-[0.04em] text-mute text-right">Aksi</th>
        </x-slot:head>

        {{-- Body Tabel Desktop --}}
        <x-slot:body>
            @forelse($nasabah as $item)
                @php
                    $isOffline = $item->user?->isOffline() || $item->user?->mode_akses === 'offline';
                    $isLocked = $item->user?->isLocked() || ($item->user?->login_terkunci_hingga && $item->user->login_terkunci_hingga->isFuture());

                    if ($isLocked) {
                        $statusKey = 'terkunci';
                        $statusDomain = 'akun';
                        $statusTeks = 'Terkunci';
                    } elseif ($item->status_pendaftaran === 'pending_verifikasi') {
                        $statusKey = 'pending_verifikasi';
                        $statusDomain = 'pendaftaran';
                        $statusTeks = 'Pending';
                    } elseif ($item->status_pendaftaran === 'ditolak') {
                        $statusKey = 'ditolak';
                        $statusDomain = 'pendaftaran';
                        $statusTeks = 'Ditolak';
                    } else {
                        $statusKey = 'aktif';
                        $statusDomain = 'pendaftaran';
                        $statusTeks = 'Aktif';
                    }

                    $namaKolektor = $item->penugasanKolektor?->kolektor?->name
                        ?? ($item->didaftarkanOleh?->isKolektor() ? $item->didaftarkanOleh->name : '-');
                @endphp
                <tr class="transition-colors hover:bg-surface-2 border-t border-hairline">
                    <td class="px-3.5 py-3">
                        <div class="text-sm font-medium text-ink">{{ $item->nama }}</div>
                        @if($item->alamat)
                            <span class="block text-xs text-mute truncate max-w-[220px]">{{ $item->alamat }}</span>
                        @endif
                    </td>
                    <td class="px-3.5 py-3 text-xs text-body tabular-nums">
                        {{ $item->user?->no_hp ?? '-' }}
                    </td>
                    <td class="px-3.5 py-3">
                        <x-admin.status-chip domain="mode_akses" :status="$isOffline ? 'offline' : 'digital'" :teks="$isOffline ? 'Offline' : 'Digital'" />
                        @if($isOffline)
                            <span class="sr-only">Mode Offline</span>
                        @endif
                    </td>
                    <td class="px-3.5 py-3">
                        <x-admin.status-chip :domain="$statusDomain" :status="$statusKey" :teks="$statusTeks" />
                    </td>
                    <td class="px-3.5 py-3 text-xs text-body">
                        {{ $namaKolektor }}
                    </td>
                    <td class="px-3.5 py-3 text-right">
                        <x-admin.row-actions>
                            <button
                                type="button"
                                wire:click="bukaDetail({{ $item->id }})"
                                class="min-h-11 h-8 inline-flex items-center justify-center px-3 rounded-[10px] border border-hairline bg-surface text-xs font-medium text-ink transition hover:bg-surface-2 cursor-pointer shadow-xs"
                                title="Lihat Detail"
                            >
                                Detail
                            </button>
                            <button
                                type="button"
                                wire:click="edit({{ $item->id }})"
                                class="min-h-11 h-8 inline-flex items-center justify-center px-3 rounded-[10px] border border-hairline bg-surface text-xs font-medium text-ink transition hover:bg-surface-2 cursor-pointer shadow-xs"
                            >
                                Edit
                            </button>

                            <x-slot:more>
                                <button
                                    type="button"
                                    wire:click="toggleStatus({{ $item->id }})"
                                    class="w-full text-left px-3 py-2 text-xs rounded-[8px] text-ink hover:bg-surface-2 transition cursor-pointer flex items-center justify-between"
                                >
                                    <span>{{ $item->status_pendaftaran === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                                </button>

                                @if($item->status_pendaftaran === 'aktif' && ($item->user?->status_akun === 'terkunci' || ($item->user?->login_terkunci_hingga && $item->user->login_terkunci_hingga->isFuture())))
                                    <button
                                        type="button"
                                        wire:click="bukaKunci({{ $item->id }})"
                                        class="w-full text-left px-3 py-2 text-xs rounded-[8px] text-primary hover:bg-primary-soft transition cursor-pointer"
                                    >
                                        Buka Kunci Akun
                                    </button>
                                @endif

                                <button
                                    type="button"
                                    wire:click="confirmKonversi({{ $item->id }})"
                                    class="w-full text-left px-3 py-2 text-xs rounded-[8px] text-ink hover:bg-surface-2 transition cursor-pointer"
                                >
                                    Ubah Mode Akses
                                </button>

                                <button
                                    type="button"
                                    wire:click="confirmResetPin({{ $item->user_id }})"
                                    class="w-full text-left px-3 py-2 text-xs rounded-[8px] text-warning-deep hover:bg-warning-soft transition cursor-pointer"
                                >
                                    Reset PIN
                                </button>

                                <button
                                    type="button"
                                    wire:click="confirmDelete({{ $item->id }})"
                                    class="w-full text-left px-3 py-2 text-xs rounded-[8px] text-danger hover:bg-danger-soft transition cursor-pointer"
                                >
                                    Hapus
                                </button>
                            </x-slot:more>
                        </x-admin.row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-0">
                        <x-admin.empty-state
                            title="Tidak ada data nasabah."
                            description="Belum ada data nasabah yang sesuai dengan filter atau kata kunci pencarian Anda."
                        />
                    </td>
                </tr>
            @endforelse
        </x-slot:body>

        {{-- Kartu Mobile (< md) Sesuai Kontrak Tes ManajemenTabelKeKartuP74cTest --}}
        <x-slot:cards>
            @forelse($nasabah as $item)
                @php
                    $isOffline = $item->user?->isOffline() || $item->user?->mode_akses === 'offline';
                    $isLocked = $item->user?->isLocked() || ($item->user?->login_terkunci_hingga && $item->user->login_terkunci_hingga->isFuture());

                    if ($isLocked) {
                        $statusKey = 'terkunci';
                        $statusDomain = 'akun';
                        $statusTeks = 'Terkunci';
                    } elseif ($item->status_pendaftaran === 'pending_verifikasi') {
                        $statusKey = 'pending_verifikasi';
                        $statusDomain = 'pendaftaran';
                        $statusTeks = 'Pending';
                    } elseif ($item->status_pendaftaran === 'ditolak') {
                        $statusKey = 'ditolak';
                        $statusDomain = 'pendaftaran';
                        $statusTeks = 'Ditolak';
                    } else {
                        $statusKey = 'aktif';
                        $statusDomain = 'pendaftaran';
                        $statusTeks = 'Aktif';
                    }

                    $namaKolektor = $item->penugasanKolektor?->kolektor?->name
                        ?? ($item->didaftarkanOleh?->isKolektor() ? $item->didaftarkanOleh->name : '-');
                @endphp
                <div class="p-3.5 space-y-2">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink">{{ $item->nama }}</p>
                            <p class="text-xs text-mute">{{ $item->user?->no_hp ?? '-' }}</p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <x-admin.status-chip domain="mode_akses" :status="$isOffline ? 'offline' : 'digital'" :teks="$isOffline ? 'Offline' : 'Digital'" />
                            @if($isOffline)
                                <span class="sr-only">Mode Offline</span>
                            @endif
                            <x-admin.status-chip :domain="$statusDomain" :status="$statusKey" :teks="$statusTeks" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 text-xs">
                        <span class="text-mute">Kolektor: <strong class="text-ink font-medium">{{ $namaKolektor }}</strong></span>
                        <span class="text-mute truncate max-w-[180px]">{{ $item->alamat }}</span>
                    </div>

                    <div class="pt-2 border-t border-hairline/60 flex items-center justify-end">
                        <x-admin.row-actions>
                            <button
                                type="button"
                                wire:click="bukaDetail({{ $item->id }})"
                                class="min-h-11 h-8 inline-flex items-center justify-center px-3 rounded-[10px] border border-hairline bg-surface text-xs font-medium text-ink transition hover:bg-surface-2 cursor-pointer shadow-xs"
                                title="Lihat Detail"
                            >
                                Detail
                            </button>
                            <button
                                type="button"
                                wire:click="edit({{ $item->id }})"
                                class="min-h-11 h-8 inline-flex items-center justify-center px-3 rounded-[10px] border border-hairline bg-surface text-xs font-medium text-ink transition hover:bg-surface-2 cursor-pointer shadow-xs"
                            >
                                Edit
                            </button>

                            <x-slot:more>
                                <button
                                    type="button"
                                    wire:click="toggleStatus({{ $item->id }})"
                                    class="w-full text-left px-3 py-2 text-xs rounded-[8px] text-ink hover:bg-surface-2 transition cursor-pointer flex items-center justify-between"
                                >
                                    <span>{{ $item->status_pendaftaran === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                                </button>

                                @if($item->status_pendaftaran === 'aktif' && ($item->user?->status_akun === 'terkunci' || ($item->user?->login_terkunci_hingga && $item->user->login_terkunci_hingga->isFuture())))
                                    <button
                                        type="button"
                                        wire:click="bukaKunci({{ $item->id }})"
                                        class="w-full text-left px-3 py-2 text-xs rounded-[8px] text-primary hover:bg-primary-soft transition cursor-pointer"
                                    >
                                        Buka Kunci Akun
                                    </button>
                                @endif

                                <button
                                    type="button"
                                    wire:click="confirmKonversi({{ $item->id }})"
                                    class="w-full text-left px-3 py-2 text-xs rounded-[8px] text-ink hover:bg-surface-2 transition cursor-pointer"
                                >
                                    Ubah Mode Akses
                                </button>

                                <button
                                    type="button"
                                    wire:click="confirmResetPin({{ $item->user_id }})"
                                    class="w-full text-left px-3 py-2 text-xs rounded-[8px] text-warning-deep hover:bg-warning-soft transition cursor-pointer"
                                >
                                    Reset PIN
                                </button>

                                <button
                                    type="button"
                                    wire:click="confirmDelete({{ $item->id }})"
                                    class="w-full text-left px-3 py-2 text-xs rounded-[8px] text-danger hover:bg-danger-soft transition cursor-pointer"
                                >
                                    Hapus
                                </button>
                            </x-slot:more>
                        </x-admin.row-actions>
                    </div>
                </div>
            @empty
                <div class="p-0">
                    <x-admin.empty-state
                        title="Tidak ada data nasabah."
                        description="Belum ada data nasabah yang sesuai dengan filter atau kata kunci pencarian Anda."
                    />
                </div>
            @endforelse
        </x-slot:cards>
    </x-admin.table>

    {{-- Dialog Modal Konfirmasi Hapus --}}
    @if($tampilKonfirmasiHapus)
        <x-admin.dialog
            title="Hapus Nasabah?"
            message="Tindakan ini tidak dapat dibatalkan. Pastikan nasabah tidak memiliki saldo atau kewajiban aktif."
            tone="danger"
            confirm-text="Hapus"
            cancel-text="Batal"
            confirm-action="delete"
            cancel-action="$set('tampilKonfirmasiHapus', false)"
        >
            <p class="text-xs text-subtle leading-relaxed">
                Data profil nasabah, akun user, dan seluruh riwayat keterikatan akan dihapus secara permanen dari sistem.
            </p>
        </x-admin.dialog>
    @endif

    {{-- Dialog Modal Reset PIN --}}
    @if($tampilKonfirmasiResetPin)
        <x-admin.dialog
            title="Reset PIN?"
            message="PIN pengguna akan diganti menjadi PIN acak baru dan seluruh sesi aktifnya dihentikan. Pengguna wajib mengganti PIN setelah login berikutnya."
            tone="danger"
            confirm-text="Reset PIN"
            cancel-text="Batal"
            confirm-action="resetPin"
            cancel-action="$set('tampilKonfirmasiResetPin', false)"
        >
            @error('reset_pin')
                <p class="text-xs font-medium text-danger">{{ $message }}</p>
            @enderror
        </x-admin.dialog>
    @endif

    {{-- Dialog Modal Ubah Mode Akses --}}
    @if($tampilKonversiMode)
        @php $profilKonversi = $nasabah->getCollection()->firstWhere('id', $konversiProfilId); @endphp
        <x-admin.dialog
            title="Ubah Mode Akses?"
            :message="($profilKonversi?->nama ?? '') . ' — saat ini mode ' . ($profilKonversi?->user?->isOffline() ? 'offline (tanpa aplikasi)' : 'digital (memakai aplikasi)')"
            :confirm-text="$profilKonversi?->user?->isOffline() ? 'Aktifkan Digital' : 'Aktifkan Offline'"
            cancel-text="Batal"
            confirm-action="konversiMode"
            cancel-action="$set('tampilKonversiMode', false)"
        >
            @if($profilKonversi?->user?->isOffline())
                <div class="space-y-1.5">
                    <label class="block text-xs font-medium text-ink">No. HP Baru (wajib, unik)</label>
                    <input
                        type="text"
                        wire:model="konversiNoHp"
                        placeholder="08xxxxxxxxxx"
                        class="h-[38px] w-full rounded-[10px] border border-hairline bg-surface px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent"
                    />
                    @error('konversiNoHp') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                    <p class="text-[11px] text-mute leading-relaxed">Nasabah kembali memakai aplikasi: PIN awal dikirim dan nasabah wajib mengganti PIN saat login.</p>
                </div>
            @else
                <p class="text-xs text-mute leading-relaxed">Seluruh sesi login nasabah akan dihapus dan notifikasi dimatikan &mdash; nasabah dicatat kembali lewat buku fisik.</p>
            @endif
        </x-admin.dialog>
    @endif

    {{-- Drawer Slide-over Detail Nasabah Sesuai Desain UI/UX Prototipe --}}
    @if($detailData)
        @php
            $dp = $detailData['profil'];
            $isOfflineDetail = $dp->user?->isOffline() || $dp->user?->mode_akses === 'offline';
            $isLockedDetail = $dp->user?->isLocked() || ($dp->user?->login_terkunci_hingga && $dp->user->login_terkunci_hingga->isFuture());

            if ($isLockedDetail) {
                $statusKeyDetail = 'terkunci';
                $statusDomainDetail = 'akun';
                $statusTeksDetail = 'Terkunci';
            } elseif ($dp->status_pendaftaran === 'pending_verifikasi') {
                $statusKeyDetail = 'pending_verifikasi';
                $statusDomainDetail = 'pendaftaran';
                $statusTeksDetail = 'Pending';
            } elseif ($dp->status_pendaftaran === 'ditolak') {
                $statusKeyDetail = 'ditolak';
                $statusDomainDetail = 'pendaftaran';
                $statusTeksDetail = 'Ditolak';
            } else {
                $statusKeyDetail = 'aktif';
                $statusDomainDetail = 'pendaftaran';
                $statusTeksDetail = 'Aktif';
            }
        @endphp

        {{-- Backdrop Scrim --}}
        <div class="fixed inset-0 bg-black/40 z-40 transition-opacity" wire:click="tutupDetail"></div>

        {{-- Drawer Slide-over Panel --}}
        <aside class="fixed inset-y-0 right-0 z-50 w-full max-w-[560px] bg-surface border-l border-hairline shadow-2xl flex flex-col transition-transform">
            {{-- Header Drawer --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-hairline">
                <h3 class="text-base font-semibold text-ink">Detail Nasabah</h3>
                <button
                    type="button"
                    wire:click="tutupDetail"
                    class="flex size-8 items-center justify-center rounded-[8px] border border-hairline text-mute hover:text-ink hover:bg-surface-2 transition cursor-pointer"
                    aria-label="Tutup"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Isi Drawer --}}
            <div class="p-5 overflow-y-auto flex-1 space-y-5 text-sm">
                {{-- 1. Profil --}}
                <div class="space-y-1">
                    <h4 class="text-xs uppercase tracking-[0.05em] text-mute font-medium mb-1">Profil</h4>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">No. HP</span>
                        <b class="font-medium text-ink tabular-nums">{{ $dp->user?->no_hp ?? '-' }}</b>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Alamat</span>
                        <b class="font-medium text-ink text-right">{{ $dp->alamat ?? '-' }}</b>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Kolektor</span>
                        <b class="font-medium text-ink">{{ $detailData['kolektor'] }}</b>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Mode</span>
                        <x-admin.status-chip domain="mode_akses" :status="$isOfflineDetail ? 'offline' : 'digital'" :teks="$isOfflineDetail ? 'Offline' : 'Digital'" />
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b-0 text-sm">
                        <span class="text-mute">Status</span>
                        <x-admin.status-chip :domain="$statusDomainDetail" :status="$statusKeyDetail" :teks="$statusTeksDetail" />
                    </div>
                </div>

                {{-- 2. Saldo per Produk --}}
                <div class="space-y-1">
                    <h4 class="text-xs uppercase tracking-[0.05em] text-mute font-medium mb-1">Saldo per Produk</h4>
                    @forelse($detailData['saldoProduk'] as $sp)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">{{ $sp->produk->nama ?? 'Produk' }}</span>
                            <b class="font-medium text-ink tabular-nums">Rp {{ number_format($sp->saldo, 0, ',', '.') }}</b>
                        </div>
                    @empty
                        <div class="py-2 text-xs text-mute border-b border-dashed border-hairline">Belum ada saldo produk.</div>
                    @endforelse
                    <div class="flex items-center justify-between gap-3 py-2 border-b-0 text-sm">
                        <span class="text-mute font-medium">Total</span>
                        <b class="font-semibold text-ink tabular-nums">Rp {{ number_format($detailData['totalSaldo'], 0, ',', '.') }}</b>
                    </div>
                </div>

                {{-- 3. Kepesertaan Paket --}}
                <div class="space-y-1">
                    <h4 class="text-xs uppercase tracking-[0.05em] text-mute font-medium mb-1">Kepesertaan Paket</h4>
                    @forelse($detailData['kepesertaan'] as $kp)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">{{ $kp->produk->nama ?? 'Paket' }}</span>
                            <x-admin.status-chip domain="produk" status="aktif" teks="Aktif" />
                        </div>
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">Komitmen</span>
                            <b class="font-medium text-ink text-right text-xs">
                                Disetujui via kolektor, {{ $kp->tanggal_mulai_ikut ? $kp->tanggal_mulai_ikut->format('d M Y') : '12 Jan 2026' }}
                            </b>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-2 border-b-0 text-sm">
                            <span class="text-mute">Kolektor</span>
                            <b class="font-medium text-ink">{{ $detailData['kolektor'] }}</b>
                        </div>
                    @empty
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">Paket Tabungan</span>
                            <span class="text-xs text-mute">Belum ada paket aktif</span>
                        </div>
                    @endforelse
                </div>

                {{-- 4. Perkembangan Saldo --}}
                <div class="space-y-2">
                    <h4 class="text-xs uppercase tracking-[0.05em] text-mute font-medium">Perkembangan Saldo</h4>
                    <div class="inline-flex gap-1 rounded-[10px] border border-hairline bg-surface-2 p-[3px]">
                        @foreach(['30 Hari', '90 Hari', 'Tahun Ini'] as $perOpt)
                            <button
                                type="button"
                                wire:click="setPeriodeDetail('{{ $perOpt }}')"
                                @class([
                                    'h-7 rounded-[7px] px-3 text-xs font-medium transition cursor-pointer select-none',
                                    'bg-surface font-semibold text-ink shadow-xs' => $periodeDetail === $perOpt,
                                    'text-mute hover:text-ink' => $periodeDetail !== $perOpt,
                                ])
                            >
                                {{ $perOpt }}
                            </button>
                        @endforeach
                    </div>

                    <div class="bars flex items-end gap-[5px] h-[110px] pt-2 overflow-hidden rounded-[10px] bg-surface-2/60 p-2 border border-hairline/60" role="img" aria-label="Grafik perkembangan saldo {{ $periodeDetail }}">
                        @foreach($detailData['bars'] as $i => $bar)
                            @php
                                $isLast = $i === count($detailData['bars']) - 1;
                            @endphp
                            <div
                                class="flex-1 rounded-t-[5px] rounded-b-[2px] transition-all duration-300 {{ $isLast ? 'bg-primary' : 'bg-primary/30' }}"
                                style="height: {{ $bar['pct'] }}%; min-height: 4px;"
                                title="Rp {{ number_format($bar['val'], 0, ',', '.') }}"
                            ></div>
                        @endforeach
                    </div>
                </div>

                {{-- 5. Riwayat Transaksi --}}
                <div class="space-y-2">
                    <h4 class="text-xs uppercase tracking-[0.05em] text-mute font-medium">Riwayat Transaksi</h4>
                    @if($detailData['riwayat']->isNotEmpty())
                        <div class="rounded-[10px] border border-hairline overflow-hidden">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="bg-surface-2 text-mute border-b border-hairline font-medium">
                                        <th class="px-3 py-2">Tanggal</th>
                                        <th class="px-3 py-2">Tipe</th>
                                        <th class="px-3 py-2 text-right">Nominal</th>
                                        <th class="px-3 py-2 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-hairline">
                                    @foreach($detailData['riwayat'] as $trx)
                                        <tr class="hover:bg-surface-2 transition">
                                            <td class="px-3 py-2 text-mute">{{ $trx['tanggal'] }}</td>
                                            <td class="px-3 py-2 font-medium text-ink">{{ $trx['tipe'] }}</td>
                                            <td class="px-3 py-2 text-right tabular-nums font-medium text-ink">Rp {{ number_format($trx['nominal'], 0, ',', '.') }}</td>
                                            <td class="px-3 py-2 text-right">
                                                <x-admin.status-chip :status="$trx['status']" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-xs text-mute py-2">Belum ada riwayat transaksi.</p>
                    @endif
                </div>

                {{-- Tombol Aksi Bawah --}}
                <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-hairline">
                    <button
                        type="button"
                        wire:click="bukaDaftarPaket({{ $dp->user_id }})"
                        class="min-h-11 h-[38px] inline-flex items-center justify-center rounded-[10px] bg-primary px-4 text-xs font-semibold text-white transition hover:bg-primary-hover active:scale-95 cursor-pointer shadow-xs"
                    >
                        Daftarkan ke Paket
                    </button>
                    <button
                        type="button"
                        wire:click="edit({{ $dp->id }})"
                        class="min-h-11 h-[38px] inline-flex items-center justify-center rounded-[10px] border border-hairline bg-surface px-4 text-xs font-medium text-ink transition hover:bg-surface-2 cursor-pointer shadow-xs"
                    >
                        Edit Profil
                    </button>
                    <button
                        type="button"
                        wire:click="confirmResetPin({{ $dp->user_id }})"
                        class="min-h-11 h-[38px] inline-flex items-center justify-center rounded-[10px] border border-hairline bg-surface px-4 text-xs font-medium text-ink transition hover:bg-surface-2 cursor-pointer shadow-xs"
                    >
                        Reset PIN
                    </button>
                </div>
            </div>
        </aside>
    @endif

    {{-- Modal Dialog Pendaftaran Paket Tabungan (Popup) --}}
    @if($tampilDaftarPaket)
        <x-admin.dialog
            title="Daftarkan ke Paket Tabungan"
            message="Pilih paket tabungan aktif dan catat kesepakatan komitmen setoran nasabah."
            confirm-text="Daftarkan Sekarang"
            cancel-text="Batal"
            confirm-action="daftarkanKePaket"
            cancel-action="tutupDaftarPaket"
        >
            <div class="space-y-3.5 text-left">
                @if($pesanErrorDaftar)
                    <div class="rounded-[10px] bg-danger-soft p-3 text-xs font-medium text-danger border border-danger/20">
                        {{ $pesanErrorDaftar }}
                    </div>
                @endif

                {{-- Pilihan Paket --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Paket Tabungan</label>
                    <select
                        wire:model="produkDaftarId"
                        class="h-[38px] w-full rounded-[10px] border border-hairline bg-surface px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent"
                    >
                        <option value="0">-- Pilih Paket Tabungan --</option>
                        @foreach($paketTerbuka as $pkt)
                            <option value="{{ $pkt->id }}">
                                {{ $pkt->nama }} (Rp {{ number_format($pkt->harga_per_hari ?? 0, 0, ',', '.') }}/hari)
                            </option>
                        @endforeach
                    </select>
                    @error('produkDaftarId') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    @if($paketTerbuka->isEmpty())
                        <p class="mt-1 text-xs text-mute">Tidak ada paket baru yang dapat didaftarkan untuk nasabah ini.</p>
                    @endif
                </div>

                {{-- Catatan Komitmen --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-ink">Catatan Komitmen Setoran</label>
                    <textarea
                        wire:model="catatanDaftar"
                        rows="3"
                        class="w-full rounded-[10px] border border-hairline bg-surface px-3 py-2 text-sm text-ink placeholder-mute focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent"
                        placeholder="Contoh: Disepakati nasabah setor harian setiap sore di toko/lokasi kerja."
                    ></textarea>
                    <p class="mt-0.5 text-[11px] text-mute">Catatan ini akan disimpan sebagai bukti komitmen kepesertaan nasabah.</p>
                    @error('catatanDaftar') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                {{-- Checkbox Persetujuan --}}
                <div class="flex items-start gap-2.5 pt-1">
                    <input
                        type="checkbox"
                        id="setujuDaftar"
                        wire:model="setujuDaftar"
                        class="size-4 mt-0.5 rounded border-hairline text-accent focus:ring-accent cursor-pointer"
                    />
                    <label for="setujuDaftar" class="text-xs text-ink cursor-pointer leading-relaxed">
                        Nasabah telah menyetujui komitmen dan ketentuan pencairan paket tabungan ini.
                    </label>
                </div>
                @error('setujuDaftar') <p class="mt-0.5 text-xs text-danger">{{ $message }}</p> @enderror
            </div>
        </x-admin.dialog>
    @endif
</div>