<div>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Manajemen Nasabah</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola data nasabah tabungan digital.</p>
        </div>
        <button wire:click="toggleForm"
            class="inline-flex shrink-0 items-center gap-2 rounded-full bg-indigo-800 px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Tambah Nasabah
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-indigo-100 px-4 py-3 text-sm text-indigo-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-[#f7d4d6] px-4 py-3 text-sm text-[#c50000]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    @if($showForm)
        <div class="mb-6 rounded-xl bg-gray-50 shadow-[inset_0_0_0_1px_#ebebeb]">
            <div class="border-b border-[#ebebeb] px-6 py-4">
                <h3 class="text-sm font-semibold text-gray-900">{{ $editId ? 'Edit Nasabah' : 'Tambah Nasabah Baru' }}</h3>
            </div>
            <form wire:submit="save" class="space-y-4 p-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-900">Nama Lengkap</label>
                        <input type="text" wire:model="nama"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                            placeholder="Nama nasabah" />
                        @error('nama') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-900">No. HP</label>
                        <input type="text" wire:model="no_hp"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                            placeholder="08xxxxxxxxxx" />
                        @error('no_hp') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-900">Alamat</label>
                        <textarea wire:model="alamat" rows="2"
                            class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                            placeholder="Alamat lengkap"></textarea>
                        @error('alamat') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-900">PIN (6 digit) {{ $editId ? '- Kosongkan jika tidak diubah' : '' }}</label>
                        <input type="password" wire:model="pin" maxlength="6"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                            placeholder="123456" />
                        @error('pin') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" wire:loading.attr="disabled"
                        class="rounded-full bg-indigo-800 px-4 py-2 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
                        {{ $editId ? 'Simpan Perubahan' : 'Tambah Nasabah' }}
                    </button>
                    <button type="button" wire:click="toggleForm"
                        class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <input type="text" wire:model.live="search" placeholder="Cari nama atau no. HP..."
            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-4 text-sm text-gray-900 placeholder-[#a1a1a1] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
        <select wire:model.live="modeFilter"
            class="h-10 w-full shrink-0 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10 sm:w-48">
            <option value="">Semua Mode</option>
            <option value="digital">Digital</option>
            <option value="offline">Offline</option>
        </select>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-gray-50">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Nama</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">No. HP</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Alamat</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($nasabah as $item)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $item->nama }}</div>
                                <div class="text-xs text-gray-500">oleh {{ $item->didaftarkan_oleh->name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->user->no_hp ?? '— (offline)' }}</td>
                            <td class="max-w-[200px] truncate px-4 py-3 text-sm text-gray-600">{{ $item->alamat }}</td>
                            <td class="px-4 py-3">
                                @if($item->status_pendaftaran === 'aktif')
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 font-mono text-xs text-indigo-600">Aktif</span>
                                @elseif($item->status_pendaftaran === 'pending_verifikasi')
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 font-mono text-xs text-[#ab570a]">Pending</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2.5 py-0.5 font-mono text-xs text-[#c50000]">Ditolak</span>
                                @endif
                                @if($item->user?->isOffline())
                                    <span class="ml-1 inline-flex items-center rounded-full bg-zinc-200 px-2.5 py-0.5 font-mono text-xs text-zinc-700">Mode Offline</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('admin.nasabah.detail', $item->user_id) }}" class="rounded-full p-1.5 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900" title="Lihat Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </a>
                                    <button wire:click="edit({{ $item->id }})" class="rounded-full p-1.5 text-[#0070f3] transition hover:bg-indigo-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button wire:click="toggleStatus({{ $item->id }})" class="rounded-full p-1.5 transition {{ $item->status_pendaftaran === 'aktif' ? 'text-[#ab570a] hover:bg-amber-100' : 'text-[#0070f3] hover:bg-indigo-100' }}">
                                        @if($item->status_pendaftaran === 'aktif')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @endif
                                    </button>
                                    @if($item->status_pendaftaran === 'aktif' && ($item->user->status_akun === 'terkunci' || ($item->user->login_terkunci_hingga && $item->user->login_terkunci_hingga->isFuture())))
                                        <button wire:click="bukaKunci({{ $item->id }})" class="rounded-full p-1.5 text-emerald-600 transition hover:bg-emerald-100" title="Buka Kunci">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                        </button>
                                    @endif
                                    <button wire:click="confirmKonversi({{ $item->id }})" class="rounded-full p-1.5 text-zinc-500 transition hover:bg-zinc-100" title="Ubah Mode Akses">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                                    </button>
                                    <button wire:click="confirmResetPin({{ $item->user_id }})" class="rounded-full p-1.5 text-[#ab570a] transition hover:bg-amber-100" title="Reset PIN">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159-.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $item->id }})" class="rounded-full p-1.5 text-[#ee0000] transition hover:bg-[#f7d4d6]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <p class="text-sm text-gray-500">Tidak ada data nasabah.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#ebebeb] px-4 py-3">{{ $nasabah->links() }}</div>
    </div>

    @if($tampilKonfirmasiHapus)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Hapus Nasabah?</h3>
                <p class="mt-2 text-sm text-gray-500">Data nasabah akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('tampilKonfirmasiHapus', false)" class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">Batal</button>
                    <button wire:click="delete" class="rounded-full bg-[#ee0000] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">Hapus</button>
                </div>
            </div>
        </div>
    @endif

    @if($tampilKonfirmasiResetPin)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Reset PIN?</h3>
                <p class="mt-2 text-sm text-gray-500">PIN pengguna akan diganti menjadi PIN acak baru dan seluruh sesi aktifnya dihentikan. Pengguna wajib mengganti PIN setelah login berikutnya.</p>
                @error('reset_pin') <p class="mt-2 text-sm text-[#ee0000]">{{ $message }}</p> @enderror
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('tampilKonfirmasiResetPin', false)" class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">Batal</button>
                    <button wire:click="resetPin" class="rounded-full bg-[#ee0000] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">Reset PIN</button>
                </div>
            </div>
        </div>
    @endif

    @if($tampilKonversiMode)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Ubah Mode Akses?</h3>
                @php $profilKonversi = $nasabah->getCollection()->firstWhere('id', $konversiProfilId); @endphp
                <p class="mt-2 text-sm text-gray-500">
                    {{ $profilKonversi?->nama }} — saat ini mode
                    <span class="font-semibold">{{ $profilKonversi?->user?->isOffline() ? 'offline (tanpa aplikasi)' : 'digital (memakai aplikasi)' }}</span>.
                </p>
                @if($profilKonversi?->user?->isOffline())
                    <div class="mt-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-900">No. HP Baru (wajib, unik)</label>
                        <input type="text" wire:model="konversiNoHp" placeholder="08xxxxxxxxxx"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        @error('konversiNoHp') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                        <p class="mt-2 text-xs text-gray-500">Nasabah kembali memakai aplikasi: PIN awal dikirim dan nasabah wajib mengganti PIN saat login.</p>
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-500">Seluruh sesi login nasabah akan dihapus dan notifikasi dimatikan — nasabah dicatat kembali lewat buku fisik.</p>
                @endif
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('tampilKonversiMode', false)" class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">Batal</button>
                    <button wire:click="konversiMode" wire:loading.attr="disabled" class="rounded-full bg-indigo-800 px-4 py-2 text-sm font-medium text-white transition hover:opacity-90 disabled:opacity-50">
                        {{ $profilKonversi?->user?->isOffline() ? 'Aktifkan Digital' : 'Aktifkan Offline' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>