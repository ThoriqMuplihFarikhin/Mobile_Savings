<div class="card mx-auto max-w-7xl space-y-6">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Kelola Kolektor</h1>
            <p class="mt-1 text-sm text-mute">Manajemen data kolektor dan penugasan nasabah.</p>
        </div>
        <button wire:click="toggleForm"
            class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Tambah Kolektor
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
        <div class="card mb-6">
            <div class="border-b border-hairline px-6 py-4">
                <h3 class="text-sm font-semibold text-ink">{{ $editId ? 'Edit Kolektor' : 'Tambah Kolektor Baru' }}</h3>
            </div>
            <form wire:submit="save" class="space-y-4 p-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Nama Lengkap</label>
                        <input type="text" wire:model="name"
                            class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                            placeholder="Nama kolektor" />
                        @error('name') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">No. HP</label>
                        <input type="text" wire:model="noHp"
                            class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                            placeholder="08xxxxxxxxxx" />
                        @error('noHp') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
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
                        class="btn-primary">
                        {{ $editId ? 'Simpan Perubahan' : 'Tambah Kolektor' }}
                    </button>
                    <button type="button" wire:click="toggleForm"
                        class="rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="mb-4">
        <input type="text" wire:model.live="search" placeholder="Cari nama atau no. HP..."
            class="h-10 w-full rounded-md border border-hairline bg-white px-4 text-sm text-ink placeholder-[#737373] focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-[inset_0_0_0_1px_#e5e5e5]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-inset">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nama</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">No. HP</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nasabah Aktif</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Penarikan Langsung</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($kolektor as $item)
                        <tr class="transition hover:bg-inset">
                            <td class="px-4 py-3 text-sm font-medium text-ink">{{ $item->name }}</td>
                            <td class="px-4 py-3 text-sm text-body">{{ $item->no_hp }}</td>
                            <td class="px-4 py-3 text-sm text-body">{{ $item->nasabah_aktif_count }}</td>
                            <td class="px-4 py-3">
                                @if($item->status_akun === 'aktif')
                                    <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Aktif</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs text-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($item->izin_penarikan_langsung)
                                    <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Diizinkan</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs text-danger">Diblokir</span>
                                @endif
                                <button wire:click="toggleIzinPenarikanLangsung({{ $item->id }})"
                                    wire:confirm="{{ $item->izin_penarikan_langsung ? 'Nonaktifkan izin penarikan offline langsung kolektor ini?' : 'Aktifkan izin penarikan offline langsung kolektor ini?' }}"
                                    class="mt-1 block text-xs text-info hover:underline">
                                    {{ $item->izin_penarikan_langsung ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <button wire:click="edit({{ $item->id }})" class="rounded-full p-1.5 text-info transition hover:bg-info-soft" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button wire:click="toggleAssign({{ $item->id }})" class="rounded-full p-1.5 text-body transition hover:bg-inset" title="Assign Nasabah">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    </button>
                                    <button wire:click="toggleStatus({{ $item->id }})" @if($item->status_akun === 'aktif') wire:confirm="Yakin menonaktifkan kolektor ini? Akun dikunci dan kas yang belum disetor tetap dipantau di dashboard kas." @endif class="rounded-full p-1.5 transition {{ $item->status_akun === 'aktif' ? 'text-warning-deep hover:bg-warning-soft' : 'text-info hover:bg-info-soft' }}" title="{{ $item->status_akun === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        @if($item->status_akun === 'aktif')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @endif
                                    </button>
                                    @if($item->status_akun === 'terkunci' || ($item->login_terkunci_hingga && $item->login_terkunci_hingga->isFuture()))
                                        <button wire:click="bukaKunci({{ $item->id }})" class="rounded-full p-1.5 text-primary transition hover:bg-primary-soft" title="Buka Kunci">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                        </button>
                                    @endif
                                    <button wire:click="confirmResetPin({{ $item->id }})" class="rounded-full p-1.5 text-warning-deep transition hover:bg-warning-soft" title="Reset PIN">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159-.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    <p class="text-sm text-mute">Tidak ada data kolektor.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-hairline px-4 py-3">{{ $kolektor->links() }}</div>
    </div>

    @if($showAssign && $selectedKolektor)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-lg rounded-lg bg-white p-6 shadow-[0_8px_24px_rgb(0_0_0/0.12)]">
                <h3 class="text-lg font-semibold text-ink">Assign Nasabah ke {{ $selectedKolektor->name }}</h3>
                <p class="mt-1 text-sm text-mute">Pilih nasabah yang akan ditugaskan ke kolektor ini.</p>

                <div class="mt-4">
                    <label class="mb-1.5 block text-sm font-medium text-ink">Pilih Nasabah</label>
                    <select wire:model.live="assignNasabahId"
                        class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
                        <option value="">Pilih Nasabah</option>
                        @foreach($availableNasabah as $nasabah)
                            <option value="{{ $nasabah->user_id }}">{{ $nasabah->nama }} ({{ $nasabah->user->no_hp ?? '— (offline)' }})</option>
                        @endforeach
                    </select>
                    @error('assignNasabahId') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                <div class="mt-3 flex justify-end">
                    <button wire:click="assignNasabah" {{ empty($assignNasabahId) ? 'disabled' : '' }}
                        class="btn-primary">
                        Assign
                    </button>
                </div>

                <div class="mt-4 border-t border-hairline pt-4">
                    <h4 class="text-sm font-medium text-ink">Nasabah yang Ditugaskan</h4>
                    <div class="mt-2 max-h-48 space-y-2 overflow-y-auto">
                        @forelse($selectedKolektor->kolektorNasabahs()->where('status', 'aktif')->get() as $assign)
                            <div class="flex items-center justify-between rounded-lg bg-inset px-3 py-2 shadow-[inset_0_0_0_1px_#e5e5e5]">
                                <span class="text-sm text-body">{{ $assign->nasabah->name ?? '-' }}</span>
                                <button wire:click="removeAssign({{ $assign->id }})" class="text-xs text-danger hover:underline">Hapus</button>
                            </div>
                        @empty
                            <p class="text-sm text-mute">Belum ada nasabah yang ditugaskan.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button wire:click="$set('showAssign', false)"
                        class="rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
                        Tutup
                    </button>
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
                    <button wire:click="$set('tampilKonfirmasiResetPin', false)" class="rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">Batal</button>
                    <button wire:click="resetPin" class="rounded-md bg-danger px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">Reset PIN</button>
                </div>
            </div>
        </div>
    @endif
</div>