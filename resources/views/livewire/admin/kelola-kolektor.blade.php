<div class="card mx-auto max-w-7xl space-y-6">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Kelola Kolektor</h1>
            <p class="mt-1 text-sm text-gray-500">Manajemen data kolektor dan penugasan nasabah.</p>
        </div>
        <button wire:click="toggleForm"
            class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Tambah Kolektor
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
        <div class="card mb-6">
            <div class="border-b border-[#ebebeb] px-6 py-4">
                <h3 class="text-sm font-semibold text-gray-900">{{ $editId ? 'Edit Kolektor' : 'Tambah Kolektor Baru' }}</h3>
            </div>
            <form wire:submit="save" class="space-y-4 p-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-900">Nama Lengkap</label>
                        <input type="text" wire:model="name"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                            placeholder="Nama kolektor" />
                        @error('name') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-900">No. HP</label>
                        <input type="text" wire:model="noHp"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                            placeholder="08xxxxxxxxxx" />
                        @error('noHp') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
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
                        class="btn-primary">
                        {{ $editId ? 'Simpan Perubahan' : 'Tambah Kolektor' }}
                    </button>
                    <button type="button" wire:click="toggleForm"
                        class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="mb-4">
        <input type="text" wire:model.live="search" placeholder="Cari nama atau no. HP..."
            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-4 text-sm text-gray-900 placeholder-[#a1a1a1] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-gray-50">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Nama</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">No. HP</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Nasabah Aktif</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($kolektor as $item)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->no_hp }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->nasabah_aktif_count }}</td>
                            <td class="px-4 py-3">
                                @if($item->status_akun === 'aktif')
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 font-mono text-xs text-indigo-600">Aktif</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2.5 py-0.5 font-mono text-xs text-[#c50000]">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <button wire:click="edit({{ $item->id }})" class="rounded-full p-1.5 text-[#0070f3] transition hover:bg-indigo-100" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button wire:click="toggleAssign({{ $item->id }})" class="rounded-full p-1.5 text-[#4c2889] transition hover:bg-purple-100" title="Assign Nasabah">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    </button>
                                    <button wire:click="toggleStatus({{ $item->id }})" class="rounded-full p-1.5 transition {{ $item->status_akun === 'aktif' ? 'text-[#ab570a] hover:bg-amber-100' : 'text-[#0070f3] hover:bg-indigo-100' }}" title="{{ $item->status_akun === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        @if($item->status_akun === 'aktif')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @endif
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    <p class="text-sm text-gray-500">Tidak ada data kolektor.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#ebebeb] px-4 py-3">{{ $kolektor->links() }}</div>
    </div>

    @if($showAssign && $selectedKolektor)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Assign Nasabah ke {{ $selectedKolektor->name }}</h3>
                <p class="mt-1 text-sm text-gray-500">Pilih nasabah yang akan ditugaskan ke kolektor ini.</p>

                <div class="mt-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-900">Pilih Nasabah</label>
                    <select wire:model="assignNasabahId"
                        class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                        <option value="">Pilih Nasabah</option>
                        @foreach($availableNasabah as $nasabah)
                            <option value="{{ $nasabah->user_id }}">{{ $nasabah->nama }} ({{ $nasabah->user->no_hp ?? '-' }})</option>
                        @endforeach
                    </select>
                    @error('assignNasabahId') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                </div>

                <div class="mt-3 flex justify-end">
                    <button wire:click="assignNasabah" {{ empty($assignNasabahId) ? 'disabled' : '' }}
                        class="btn-primary">
                        Assign
                    </button>
                </div>

                <div class="mt-4 border-t border-[#ebebeb] pt-4">
                    <h4 class="text-sm font-medium text-gray-900">Nasabah yang Ditugaskan</h4>
                    <div class="mt-2 max-h-48 space-y-2 overflow-y-auto">
                        @forelse($selectedKolektor->kolektorNasabahs()->where('status', 'aktif')->get() as $assign)
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 shadow-[inset_0_0_0_1px_#ebebeb]">
                                <span class="text-sm text-gray-600">{{ $assign->nasabah->name ?? '-' }}</span>
                                <button wire:click="removeAssign({{ $assign->id }})" class="text-xs text-[#ee0000] hover:underline">Hapus</button>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada nasabah yang ditugaskan.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button wire:click="$set('showAssign', false)"
                        class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>