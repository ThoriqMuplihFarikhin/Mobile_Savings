<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Operasional"
        title="Kelola Kolektor"
        subtitle="Manajemen data kolektor dan penugasan nasabah."
    >
        <x-slot:actions>
            <button wire:click="toggleForm"
                class="inline-flex h-[38px] items-center gap-2 rounded-[10px] bg-primary px-4 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-[0.98]">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                <span>Tambah Kolektor</span>
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-primary/20 bg-primary-soft p-3 text-xs font-medium text-on-primary-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-danger/20 bg-danger-soft p-3 text-xs font-medium text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($showForm)
        <div class="overflow-hidden rounded-[14px] border border-hairline bg-white shadow-xs dark:bg-surface">
            <div class="border-b border-hairline bg-inset/40 px-6 py-4">
                <h3 class="text-sm font-semibold text-ink">{{ $editId ? 'Edit Kolektor' : 'Tambah Kolektor Baru' }}</h3>
                <p class="text-xs text-mute">Masukkan data akun petugas kolektor penanggung jawab.</p>
            </div>
            <form wire:submit="save" class="space-y-4 p-6">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink">Nama Lengkap</label>
                        <input type="text" wire:model="name"
                            class="h-[38px] w-full rounded-[10px] border border-hairline bg-white px-3 text-xs text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"
                            placeholder="Nama kolektor" />
                        @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink">No. HP</label>
                        <input type="text" wire:model="noHp"
                            class="h-[38px] w-full rounded-[10px] border border-hairline bg-white px-3 text-xs text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"
                            placeholder="08xxxxxxxxxx" />
                        @error('noHp') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink">PIN (6 digit) {{ $editId ? '- Kosongkan bila tidak diubah' : '' }}</label>
                        <input type="password" wire:model="pin" maxlength="6"
                            class="h-[38px] w-full rounded-[10px] border border-hairline bg-white px-3 text-xs text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"
                            placeholder="123456" />
                        @error('pin') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-hairline pt-4">
                    <button type="button" wire:click="toggleForm"
                        class="h-[38px] rounded-[10px] border border-hairline bg-white px-4 text-xs font-semibold text-ink shadow-xs transition hover:bg-inset dark:bg-surface">
                        Batal
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex h-[38px] items-center gap-2 rounded-[10px] bg-primary px-5 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover disabled:opacity-50">
                        {{ $editId ? 'Simpan Perubahan' : 'Tambah Kolektor' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Toolbar Pencarian --}}
    <div class="overflow-hidden rounded-[14px] border border-hairline bg-white shadow-xs dark:bg-surface">
        <div class="border-b border-hairline p-3 sm:px-4">
            <div class="relative max-w-md">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 size-4 text-mute" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4" />
                </svg>
                <input type="text" wire:model.live="search" placeholder="Cari nama atau no. HP..."
                    class="h-[38px] w-full rounded-[10px] border border-hairline bg-surface pl-9 pr-4 text-xs text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-inset/50">
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Nama</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">No. HP</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-mute">Nasabah Aktif</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Penarikan Langsung</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-mute">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($kolektor as $item)
                        <tr class="transition hover:bg-inset/30">
                            <td class="px-4 py-3 text-xs font-medium text-ink">{{ $item->name }}</td>
                            <td class="px-4 py-3 text-xs text-body">{{ $item->no_hp }}</td>
                            <td class="px-4 py-3 text-center tabular-nums text-xs text-body">{{ $item->nasabah_aktif_count }}</td>
                            <td class="px-4 py-3">
                                <x-admin.status-chip
                                    :label="$item->status_akun === 'aktif' ? 'Aktif' : 'Nonaktif'"
                                    :variant="$item->status_akun === 'aktif' ? 'success' : 'danger'"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <x-admin.status-chip
                                    :label="$item->izin_penarikan_langsung ? 'Diizinkan' : 'Diblokir'"
                                    :variant="$item->izin_penarikan_langsung ? 'info' : 'neutral'"
                                />
                                <button wire:click="toggleIzinPenarikanLangsung({{ $item->id }})"
                                    wire:confirm="{{ $item->izin_penarikan_langsung ? 'Nonaktifkan izin penarikan offline langsung kolektor ini?' : 'Aktifkan izin penarikan offline langsung kolektor ini?' }}"
                                    class="mt-1 block text-[11px] font-medium text-primary hover:underline">
                                    {{ $item->izin_penarikan_langsung ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button wire:click="edit({{ $item->id }})" class="flex h-8 w-8 items-center justify-center rounded-[8px] text-body transition hover:bg-inset hover:text-ink" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button wire:click="toggleAssign({{ $item->id }})" class="flex h-8 w-8 items-center justify-center rounded-[8px] text-body transition hover:bg-inset hover:text-ink" title="Assign Nasabah">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    </button>
                                    <button wire:click="toggleStatus({{ $item->id }})" @if($item->status_akun === 'aktif') wire:confirm="Yakin menonaktifkan kolektor ini? Akun dikunci dan kas yang belum disetor tetap dipantau di dashboard kas." @endif class="flex h-8 w-8 items-center justify-center rounded-[8px] transition {{ $item->status_akun === 'aktif' ? 'text-warning-deep hover:bg-warning-soft' : 'text-primary hover:bg-primary-soft' }}" title="{{ $item->status_akun === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        @if($item->status_akun === 'aktif')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @endif
                                    </button>
                                    @if($item->status_akun === 'terkunci' || ($item->login_terkunci_hingga && $item->login_terkunci_hingga->isFuture()))
                                        <button wire:click="bukaKunci({{ $item->id }})" class="flex h-8 w-8 items-center justify-center rounded-[8px] text-primary transition hover:bg-primary-soft" title="Buka Kunci">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                        </button>
                                    @endif
                                    <button wire:click="confirmResetPin({{ $item->id }})" class="flex h-8 w-8 items-center justify-center rounded-[8px] text-warning-deep transition hover:bg-warning-soft" title="Reset PIN">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159-.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    <p class="text-xs text-mute">Tidak ada data kolektor.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($kolektor as $item)
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-ink">{{ $item->name }}</p>
                            <p class="text-[11px] text-mute">{{ $item->no_hp }}</p>
                        </div>
                        <x-admin.status-chip
                            :label="$item->status_akun === 'aktif' ? 'Aktif' : 'Nonaktif'"
                            :variant="$item->status_akun === 'aktif' ? 'success' : 'danger'"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-x-3 gap-y-1.5 rounded-[10px] bg-inset/40 p-2.5 text-[11px]">
                        <div>
                            <span class="block text-mute">Nasabah Binaan</span>
                            <span class="tabular-nums font-semibold text-ink">{{ $item->nasabah_aktif_count }} nasabah</span>
                        </div>
                        <div>
                            <span class="block text-mute">Tarik Langsung</span>
                            <span class="font-medium text-body">{{ $item->izin_penarikan_langsung ? 'Diizinkan' : 'Diblokir' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <button wire:click="toggleIzinPenarikanLangsung({{ $item->id }})"
                            wire:confirm="{{ $item->izin_penarikan_langsung ? 'Nonaktifkan izin penarikan offline langsung kolektor ini?' : 'Aktifkan izin penarikan offline langsung kolektor ini?' }}"
                            class="text-[11px] font-medium text-primary hover:underline">
                            {{ $item->izin_penarikan_langsung ? 'Nonaktifkan Izin' : 'Aktifkan Izin' }}
                        </button>
                        <div class="flex items-center gap-1">
                            <button wire:click="edit({{ $item->id }})" class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-hairline bg-white text-body shadow-xs transition hover:bg-inset hover:text-ink dark:bg-surface" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>
                            <button wire:click="toggleAssign({{ $item->id }})" class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-hairline bg-white text-body shadow-xs transition hover:bg-inset hover:text-ink dark:bg-surface" title="Assign Nasabah">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </button>
                            <button wire:click="toggleStatus({{ $item->id }})" @if($item->status_akun === 'aktif') wire:confirm="Yakin menonaktifkan kolektor ini? Akun dikunci dan kas yang belum disetor tetap dipantau di dashboard kas." @endif class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-hairline bg-white shadow-xs transition {{ $item->status_akun === 'aktif' ? 'text-warning-deep hover:bg-warning-soft' : 'text-primary hover:bg-primary-soft' }} dark:bg-surface" title="{{ $item->status_akun === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                @if($item->status_akun === 'aktif')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                @endif
                            </button>
                            @if($item->status_akun === 'terkunci' || ($item->login_terkunci_hingga && $item->login_terkunci_hingga->isFuture()))
                                <button wire:click="bukaKunci({{ $item->id }})" class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-hairline bg-white text-primary shadow-xs transition hover:bg-primary-soft dark:bg-surface" title="Buka Kunci">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                </button>
                            @endif
                            <button wire:click="confirmResetPin({{ $item->id }})" class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-hairline bg-white text-warning-deep shadow-xs transition hover:bg-warning-soft dark:bg-surface" title="Reset PIN">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159-.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <p class="text-xs text-mute">Tidak ada data kolektor.</p>
                </div>
            @endforelse
        </div>
        @if($kolektor->hasPages())
            <div class="border-t border-hairline px-4 py-3">{{ $kolektor->links() }}</div>
        @endif
    </div>

    @if($showAssign && $selectedKolektor)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modal-assign-nasabah-title">
            <div wire:click="$set('showAssign', false)" class="fixed inset-0 bg-black/50 transition-opacity" aria-hidden="true"></div>
            <div class="relative w-full max-w-lg rounded-[16px] border border-hairline bg-surface p-6 shadow-2xl dark:bg-surface">
                <div class="flex items-start justify-between border-b border-hairline pb-3">
                    <div>
                        <h3 id="modal-assign-nasabah-title" class="text-sm font-semibold text-ink">Assign Nasabah ke {{ $selectedKolektor->name }}</h3>
                        <p class="mt-0.5 text-xs text-mute">Pilih nasabah yang akan ditugaskan ke kolektor ini.</p>
                    </div>
                    <button type="button" wire:click="$set('showAssign', false)"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-mute transition hover:bg-inset hover:text-ink"
                        aria-label="Tutup modal">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4">
                    <label class="mb-1.5 block text-xs font-medium text-ink">Pilih Nasabah</label>
                    <select wire:model.live="assignNasabahId"
                        class="h-[38px] w-full rounded-[10px] border border-hairline bg-white px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                        <option value="">Pilih Nasabah</option>
                        @foreach($availableNasabah as $nasabah)
                            <option value="{{ $nasabah->user_id }}">{{ $nasabah->nama }} ({{ $nasabah->user->no_hp ?? '— (offline)' }})</option>
                        @endforeach
                    </select>
                    @error('assignNasabahId') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="mt-3 flex justify-end">
                    <button wire:click="assignNasabah" {{ empty($assignNasabahId) ? 'disabled' : '' }}
                        class="h-[38px] rounded-[10px] bg-primary px-4 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover disabled:opacity-50">
                        Assign
                    </button>
                </div>

                <div class="mt-4 border-t border-hairline pt-4">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-mute">Nasabah yang Ditugaskan</h4>
                    <div class="mt-2 max-h-48 space-y-2 overflow-y-auto">
                        @forelse($selectedKolektor->kolektorNasabahs()->where('status', 'aktif')->get() as $assign)
                            <div class="flex items-center justify-between rounded-[10px] border border-hairline bg-inset/40 px-3.5 py-2">
                                <span class="text-xs text-body">{{ $assign->nasabah->name ?? '-' }}</span>
                                <button wire:click="removeAssign({{ $assign->id }})" class="text-xs font-medium text-danger hover:underline">Hapus</button>
                            </div>
                        @empty
                            <p class="py-3 text-center text-xs text-mute">Belum ada nasabah yang ditugaskan.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button wire:click="$set('showAssign', false)"
                        class="h-[38px] rounded-[10px] border border-hairline bg-white px-4 text-xs font-semibold text-ink shadow-xs transition hover:bg-inset dark:bg-surface">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($tampilKonfirmasiResetPin)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modal-reset-pin-kolektor-title">
            <div wire:click="$set('tampilKonfirmasiResetPin', false)" class="fixed inset-0 bg-black/50 transition-opacity" aria-hidden="true"></div>
            <div class="relative w-full max-w-md rounded-[16px] border border-hairline bg-surface p-6 shadow-2xl dark:bg-surface">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-danger-soft text-danger">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div>
                            <h3 id="modal-reset-pin-kolektor-title" class="text-sm font-semibold text-ink">Reset PIN?</h3>
                            <p class="mt-0.5 text-xs text-mute">Tindakan ini memerlukan konfirmasi.</p>
                        </div>
                    </div>
                    <button type="button" wire:click="$set('tampilKonfirmasiResetPin', false)"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-mute transition hover:bg-inset hover:text-ink"
                        aria-label="Tutup modal">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-subtle">PIN pengguna akan diganti menjadi PIN acak baru dan seluruh sesi aktifnya dihentikan. Pengguna wajib mengganti PIN setelah login berikutnya.</p>
                @error('reset_pin') <p class="mt-2 text-xs text-danger">{{ $message }}</p> @enderror
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('tampilKonfirmasiResetPin', false)" class="h-[38px] rounded-[10px] border border-hairline bg-white px-4 text-xs font-semibold text-ink transition hover:bg-inset dark:bg-surface">Batal</button>
                    <button wire:click="resetPin" class="h-[38px] rounded-[10px] bg-danger px-4 text-xs font-semibold text-white transition hover:opacity-90">Reset PIN</button>
                </div>
            </div>
        </div>
    @endif
</div>