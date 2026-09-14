<div>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Kelola Produk Tabungan</h1>
            <p class="mt-1 text-sm text-gray-500">Buat dan kelola jenis produk tabungan.</p>
        </div>
        <button wire:click="toggleForm"
            class="inline-flex shrink-0 items-center gap-2 rounded-full bg-indigo-800 px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Tambah Produk
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-indigo-100 px-4 py-3 text-sm text-indigo-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($showForm)
        <div class="mb-6 rounded-xl bg-gray-50 shadow-[inset_0_0_0_1px_#ebebeb]">
            <div class="border-b border-[#ebebeb] px-6 py-4">
                <h3 class="text-sm font-semibold text-gray-900">{{ $editId ? 'Edit Produk' : 'Tambah Produk Baru' }}</h3>
            </div>
            <form wire:submit="save" class="space-y-4 p-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-900">Nama Produk</label>
                        <input type="text" wire:model="nama"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                            placeholder="contoh: Paket A" />
                        @error('nama') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-900">Tipe</label>
                        <select wire:model="tipe"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                            <option value="bebas">Bebas</option>
                            <option value="paket">Paket</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-900">Komisi (%)</label>
                        <input type="number" wire:model="persen_komisi" step="0.01"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                            placeholder="5" />
                        @error('persen_komisi') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-900">Minimal Setor</label>
                        <input type="number" wire:model="minimal_setor"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                            placeholder="10000" />
                    </div>

                    @if($tipe === 'paket')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Harga Per Hari</label>
                            <input type="number" wire:model="harga_per_hari"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                placeholder="10000" />
                            @error('harga_per_hari') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Batas Toleransi (hari)</label>
                            <input type="number" wire:model="batas_toleransi"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                placeholder="7" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Periode Mulai</label>
                            <input type="date" wire:model="periode_mulai"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Periode Selesai</label>
                            <input type="date" wire:model="periode_selesai"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Tanggal Boleh Cair</label>
                            <input type="date" wire:model="tanggal_boleh_cair"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Isi Paket (JSON)</label>
                            <textarea wire:model="isi_paket" rows="2"
                                class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2.5 font-mono text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                placeholder='[{"nama":"Beras","jumlah":"5kg"}]'></textarea>
                        </div>
                    @endif
                </div>

                <div class="flex gap-3">
                    <button type="submit" wire:loading.attr="disabled"
                        class="rounded-full bg-indigo-800 px-4 py-2 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
                        {{ $editId ? 'Simpan Perubahan' : 'Tambah Produk' }}
                    </button>
                    <button type="button" wire:click="toggleForm"
                        class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-gray-50">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Nama</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Tipe</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Komisi</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Harga/Hari</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($produk as $item)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->nama }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 font-mono text-xs {{ $item->tipe === 'paket' ? 'bg-purple-100 text-[#4c2889]' : 'bg-indigo-100 text-indigo-600' }}">
                                    {{ ucfirst($item->tipe) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->persen_komisi }}%</td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ $item->harga_per_hari ? 'Rp ' . number_format($item->harga_per_hari, 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3">
                                @if($item->status === 'aktif')
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 font-mono text-xs text-indigo-600">Aktif</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-50 px-2.5 py-0.5 font-mono text-xs text-gray-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <button wire:click="edit({{ $item->id }})" class="rounded-full p-1.5 text-[#0070f3] transition hover:bg-indigo-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button wire:click="toggleStatus({{ $item->id }})" class="rounded-full p-1.5 text-[#ab570a] transition hover:bg-amber-100">
                                        @if($item->status === 'aktif')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @endif
                                    </button>
                                    <button wire:click="confirmDelete({{ $item->id }})" class="rounded-full p-1.5 text-[#ee0000] transition hover:bg-[#f7d4d6]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                    <p class="text-sm text-gray-500">Tidak ada data produk.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[#ebebeb] px-4 py-3">{{ $produk->links() }}</div>
    </div>

    @if($confirmDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Hapus Produk?</h3>
                <p class="mt-2 text-sm text-gray-500">Produk akan dihapus permanen.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('confirmDelete', false)" class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">Batal</button>
                    <button wire:click="delete" class="rounded-full bg-[#ee0000] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">Hapus</button>
                </div>
            </div>
        </div>
    @endif
</div>