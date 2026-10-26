<div>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Manajemen Nasabah</h1>
            <p class="mt-1 text-sm text-mute">Kelola data nasabah tabungan digital.</p>
        </div>
        <button wire:click="toggleForm"
            class="min-h-11 inline-flex shrink-0 items-center gap-2 rounded-md bg-accent px-4 py-2 text-sm font-medium text-accent-foreground transition hover:opacity-90">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Tambah Nasabah
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-info-soft px-4 py-3 text-sm text-info">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-danger-soft px-4 py-3 text-sm text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    @if($showForm)
        <div class="mb-6 rounded-lg bg-inset shadow-[inset_0_0_0_1px_#e5e5e5]">
            <div class="border-b border-hairline px-6 py-4">
                <h3 class="text-sm font-semibold text-ink">{{ $editId ? 'Edit Nasabah' : 'Tambah Nasabah Baru' }}</h3>
            </div>
            <form wire:submit="save" class="space-y-4 p-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Nama Lengkap</label>
                        <input type="text" wire:model="nama"
                            class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                            placeholder="Nama nasabah" />
                        @error('nama') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">No. HP</label>
                        <input type="text" wire:model="no_hp"
                            class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                            placeholder="08xxxxxxxxxx" />
                        @error('no_hp') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-ink">Alamat</label>
                        <textarea wire:model="alamat" rows="2"
                            class="w-full rounded-md border border-hairline bg-white px-3 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                            placeholder="Alamat lengkap"></textarea>
                        @error('alamat') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">PIN (6 digit) {{ $editId ? '- Kosongkan jika tidak diubah' : '' }}</label>
                        <input type="password" wire:model="pin" maxlength="6"
                            class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                            placeholder="123456" />
                        @error('pin') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" wire:loading.attr="disabled"
                        class="min-h-11 rounded-md bg-accent px-4 py-2 text-sm font-medium text-accent-foreground transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
                        {{ $editId ? 'Simpan Perubahan' : 'Tambah Nasabah' }}
                    </button>
                    <button type="button" wire:click="toggleForm"
                        class="min-h-11 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <input type="text" wire:model.live="search" placeholder="Cari nama atau no. HP..."
            class="h-10 w-full rounded-md border border-hairline bg-white px-4 text-sm text-ink placeholder-[#737373] focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
        <select wire:model.live="modeFilter"
            class="h-10 w-full shrink-0 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10 sm:w-48">
            <option value="">Semua Mode</option>
            <option value="digital">Digital</option>
            <option value="offline">Offline</option>
        </select>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-[inset_0_0_0_1px_#e5e5e5]">
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-inset">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nama</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">No. HP</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Alamat</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($nasabah as $item)
                        <tr class="transition hover:bg-inset">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-ink">{{ $item->nama }}</div>
                                <div class="text-xs text-mute">oleh {{ $item->didaftarkan_oleh->name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-body">{{ $item->user->no_hp ?? '— (offline)' }}</td>
                            <td class="max-w-[200px] truncate px-4 py-3 text-sm text-body">{{ $item->alamat }}</td>
                            <td class="px-4 py-3">
                                @if($item->status_pendaftaran === 'aktif')
                                    <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Aktif</span>
                                @elseif($item->status_pendaftaran === 'pending_verifikasi')
                                    <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-0.5 text-xs text-warning-deep">Pending</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs text-danger">Ditolak</span>
                                @endif
                                @if($item->user?->isOffline())
                                    <span class="ml-1 inline-flex items-center rounded-full bg-hairline px-2.5 py-0.5 text-xs text-body">Mode Offline</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('admin.nasabah.detail', $item->user_id) }}" class="rounded-full p-1.5 text-mute transition hover:bg-inset hover:text-ink" title="Lihat Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </a>
                                    <button wire:click="edit({{ $item->id }})" class="min-h-11 rounded-full p-1.5 text-info transition hover:bg-info-soft">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button wire:click="toggleStatus({{ $item->id }})" class="min-h-11 rounded-full p-1.5 transition {{ $item->status_pendaftaran === 'aktif' ? 'text-warning-deep hover:bg-warning-soft' : 'text-info hover:bg-info-soft' }}">
                                        @if($item->status_pendaftaran === 'aktif')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @endif
                                    </button>
                                    @if($item->status_pendaftaran === 'aktif' && ($item->user->status_akun === 'terkunci' || ($item->user->login_terkunci_hingga && $item->user->login_terkunci_hingga->isFuture())))
                                        <button wire:click="bukaKunci({{ $item->id }})" class="min-h-11 rounded-full p-1.5 text-primary transition hover:bg-primary-soft" title="Buka Kunci">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                        </button>
                                    @endif
                                    <button wire:click="confirmKonversi({{ $item->id }})" class="min-h-11 rounded-full p-1.5 text-subtle transition hover:bg-inset" title="Ubah Mode Akses">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                                    </button>
                                    <button wire:click="confirmResetPin({{ $item->user_id }})" class="min-h-11 rounded-full p-1.5 text-warning-deep transition hover:bg-warning-soft" title="Reset PIN">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159-.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $item->id }})" class="min-h-11 rounded-full p-1.5 text-danger transition hover:bg-danger-soft">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <p class="text-sm text-mute">Tidak ada data nasabah.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($nasabah as $item)
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink">{{ $item->nama }}</p>
                            <p class="text-xs text-mute">oleh {{ $item->didaftarkan_oleh->name ?? '-' }}</p>
                        </div>
                        @if($item->status_pendaftaran === 'aktif')
                            <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Aktif</span>
                        @elseif($item->status_pendaftaran === 'pending_verifikasi')
                            <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-0.5 text-xs text-warning-deep">Pending</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs text-danger">Ditolak</span>
                        @endif
                        @if($item->user?->isOffline())
                            <span class="ml-1 inline-flex items-center rounded-full bg-hairline px-2.5 py-0.5 text-xs text-body">Mode Offline</span>
                        @endif
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                        <div>
                            <span class="block text-xs text-mute">No. HP</span>
                            <span class="tabular-nums text-sm text-body">{{ $item->user->no_hp ?? '— (offline)' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Alamat</span>
                            <span class="tabular-nums text-sm text-body">{{ $item->alamat }}</span>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('admin.nasabah.detail', $item->user_id) }}" class="rounded-full p-1.5 text-mute transition hover:bg-inset hover:text-ink" title="Lihat Detail">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </a>
                            <button wire:click="edit({{ $item->id }})" class="min-h-11 rounded-full p-1.5 text-info transition hover:bg-info-soft">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>
                            <button wire:click="toggleStatus({{ $item->id }})" class="min-h-11 rounded-full p-1.5 transition {{ $item->status_pendaftaran === 'aktif' ? 'text-warning-deep hover:bg-warning-soft' : 'text-info hover:bg-info-soft' }}">
                                @if($item->status_pendaftaran === 'aktif')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                @endif
                            </button>
                            @if($item->status_pendaftaran === 'aktif' && ($item->user->status_akun === 'terkunci' || ($item->user->login_terkunci_hingga && $item->user->login_terkunci_hingga->isFuture())))
                                <button wire:click="bukaKunci({{ $item->id }})" class="min-h-11 rounded-full p-1.5 text-primary transition hover:bg-primary-soft" title="Buka Kunci">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                </button>
                            @endif
                            <button wire:click="confirmKonversi({{ $item->id }})" class="min-h-11 rounded-full p-1.5 text-subtle transition hover:bg-inset" title="Ubah Mode Akses">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                            </button>
                            <button wire:click="confirmResetPin({{ $item->user_id }})" class="min-h-11 rounded-full p-1.5 text-warning-deep transition hover:bg-warning-soft" title="Reset PIN">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159-.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                            </button>
                            <button wire:click="confirmDelete({{ $item->id }})" class="min-h-11 rounded-full p-1.5 text-danger transition hover:bg-danger-soft">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <p class="text-sm text-mute">Tidak ada data nasabah.</p>
                </div>
            @endforelse
        </div>
        <div class="border-t border-hairline px-4 py-3">{{ $nasabah->links() }}</div>
    </div>

    @if($tampilKonfirmasiHapus)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-[0_8px_24px_rgb(0_0_0/0.12)]">
                <h3 class="text-lg font-semibold text-ink">Hapus Nasabah?</h3>
                <p class="mt-2 text-sm text-mute">Data nasabah akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('tampilKonfirmasiHapus', false)" class="min-h-11 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">Batal</button>
                    <button wire:click="delete" class="min-h-11 rounded-md bg-danger px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">Hapus</button>
                </div>
            </div>
        </div>
    @endif

    @if($tampilKonfirmasiResetPin)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-[0_8px_24px_rgb(0_0_0/0.12)]">
                <h3 class="text-lg font-semibold text-ink">Reset PIN?</h3>
                <p class="mt-2 text-sm text-mute">PIN pengguna akan diganti menjadi PIN acak baru dan seluruh sesi aktifnya dihentikan. Pengguna wajib mengganti PIN setelah login berikutnya.</p>
                @error('reset_pin') <p class="mt-2 text-sm text-danger">{{ $message }}</p> @enderror
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('tampilKonfirmasiResetPin', false)" class="min-h-11 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">Batal</button>
                    <button wire:click="resetPin" class="min-h-11 rounded-md bg-danger px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">Reset PIN</button>
                </div>
            </div>
        </div>
    @endif

    @if($tampilKonversiMode)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-[0_8px_24px_rgb(0_0_0/0.12)]">
                <h3 class="text-lg font-semibold text-ink">Ubah Mode Akses?</h3>
                @php $profilKonversi = $nasabah->getCollection()->firstWhere('id', $konversiProfilId); @endphp
                <p class="mt-2 text-sm text-mute">
                    {{ $profilKonversi?->nama }} — saat ini mode
                    <span class="font-semibold">{{ $profilKonversi?->user?->isOffline() ? 'offline (tanpa aplikasi)' : 'digital (memakai aplikasi)' }}</span>.
                </p>
                @if($profilKonversi?->user?->isOffline())
                    <div class="mt-4">
                        <label class="mb-1.5 block text-sm font-medium text-ink">No. HP Baru (wajib, unik)</label>
                        <input type="text" wire:model="konversiNoHp" placeholder="08xxxxxxxxxx"
                            class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                        @error('konversiNoHp') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                        <p class="mt-2 text-xs text-mute">Nasabah kembali memakai aplikasi: PIN awal dikirim dan nasabah wajib mengganti PIN saat login.</p>
                    </div>
                @else
                    <p class="mt-4 text-sm text-mute">Seluruh sesi login nasabah akan dihapus dan notifikasi dimatikan — nasabah dicatat kembali lewat buku fisik.</p>
                @endif
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('tampilKonversiMode', false)" class="min-h-11 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">Batal</button>
                    <button wire:click="konversiMode" wire:loading.attr="disabled" class="min-h-11 rounded-md bg-accent px-4 py-2 text-sm font-medium text-accent-foreground transition hover:opacity-90 disabled:opacity-50">
                        {{ $profilKonversi?->user?->isOffline() ? 'Aktifkan Digital' : 'Aktifkan Offline' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>