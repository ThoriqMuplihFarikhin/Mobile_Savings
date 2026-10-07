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

                    @if($tipe === 'paket')
                        <div class="sm:col-span-2">
                            <div class="rounded-lg bg-purple-50 px-4 py-3 text-xs text-purple-700">
                                Paket tidak pakai komisi persentase — komisi selalu 0% (keputusan D6), nilainya sudah tetap sesuai harga/hari &amp; isi barang di bawah.
                            </div>
                        </div>
                        @if($editId && $jumlahPesertaEdit > 0)
                            <div class="sm:col-span-2">
                                <div class="rounded-lg bg-amber-50 px-4 py-3 text-xs text-amber-700">
                                    Produk ini sudah diikuti {{ $jumlahPesertaEdit }} peserta. Mengubah harga/hari atau periode akan menghitung ulang tunggakan seluruh peserta.
                                </div>
                            </div>
                        @endif
                    @else
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Komisi (%)</label>
                            <input type="number" wire:model="persen_komisi" step="0.01"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                placeholder="5" />
                            @error('persen_komisi') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                        </div>
                    @endif

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
                                placeholder="5500" />
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
                            <x-ui.tanggal wire:model="periode_mulai"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Periode Selesai</label>
                            <x-ui.tanggal wire:model="periode_selesai" :min="$periode_mulai"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Tanggal Boleh Cair</label>
                            <x-ui.tanggal wire:model="tanggal_boleh_cair"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Uang Tunai (opsional)</label>
                            <input type="number" wire:model="uang_tunai"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                placeholder="500000" />
                            @error('uang_tunai') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Batas Daftar Hingga (opsional)</label>
                            <x-ui.tanggal wire:model="batas_daftar_hingga" :max="now()->addYears(1)->toDateString()"
                                placeholder="Tanpa batas"
                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                            @error('batas_daftar_hingga') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex flex-col gap-3 sm:col-span-2 sm:flex-row sm:items-end">
                            <label class="flex items-center gap-2 text-sm font-medium text-gray-900">
                                <input type="checkbox" wire:model="tampilkan_harga_ke_nasabah" class="h-4 w-4 rounded border-[#ebebeb] text-indigo-800 focus:ring-[#171717]" />
                                Tampilkan harga ke nasabah
                            </label>
                            <label class="flex items-center gap-2 text-sm font-medium text-gray-900">
                                <input type="checkbox" wire:model="boleh_cair_saat_target" class="h-4 w-4 rounded border-[#ebebeb] text-indigo-800 focus:ring-[#171717]" />
                                Boleh cair lebih awal saat target tercapai
                            </label>
                        </div>

                        {{-- Isi Paket Repeater --}}
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-900">Isi Barang Paket</label>
                            <div class="space-y-2">
                                @foreach($isiPaketItems as $index => $item)
                                    <div class="flex items-end gap-2">
                                        <div class="flex-1">
                                            <input type="text" wire:model="isiPaketItems.{{ $index }}.nama"
                                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                                placeholder="contoh: Beras" />
                                        </div>
                                        <div class="flex-1">
                                            <input type="text" wire:model="isiPaketItems.{{ $index }}.jumlah"
                                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                                placeholder="contoh: 5 kg" />
                                        </div>
                                        <div class="w-32">
                                            <input type="number" min="0" wire:model="isiPaketItems.{{ $index }}.harga"
                                                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"
                                                placeholder="Harga (opsional)" />
                                            @error('isiPaketItems.'.$index.'.harga') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                                        </div>
                                        <button type="button" wire:click="naikkanItemPaket({{ $index }})"
                                            aria-label="Naikkan urutan"
                                            class="flex h-10 w-8 shrink-0 items-center justify-center rounded-md border border-[#ebebeb] text-gray-500 transition hover:bg-gray-50 disabled:opacity-40"
                                            {{ $index === 0 ? 'disabled' : '' }}>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                                        </button>
                                        <button type="button" wire:click="turunkanItemPaket({{ $index }})"
                                            aria-label="Turunkan urutan"
                                            class="flex h-10 w-8 shrink-0 items-center justify-center rounded-md border border-[#ebebeb] text-gray-500 transition hover:bg-gray-50 disabled:opacity-40"
                                            {{ $index === count($isiPaketItems) - 1 ? 'disabled' : '' }}>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                        </button>
                                        <button type="button" wire:click="hapusItemPaket({{ $index }})"
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-[#ebebeb] text-[#ee0000] transition hover:bg-[#f7d4d6]">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" wire:click="tambahItemPaket"
                                class="mt-2 inline-flex items-center gap-1.5 rounded-full border border-[#ebebeb] bg-white px-3 py-1.5 text-xs font-medium text-gray-900 transition hover:bg-gray-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                Tambah Barang
                            </button>
                            <p class="mt-1.5 text-xs text-gray-500">Isi sesuai yang tertulis di brosur, misalnya Beras 5 kg, Ayam 2 kg, Gula 1 kg. Kolom harga hanya untuk admin (D15) - kosongkan bila tidak ada.</p>

                            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 rounded-xl bg-gray-50 px-4 py-3 text-sm shadow-[inset_0_0_0_1px_#ebebeb]">
                                <span class="font-medium text-gray-900">Total Harga Barang</span>
                                <span class="font-mono font-semibold text-gray-900">Rp {{ number_format($ringkasHarga['totalHarga'], 0, ',', '.') }}</span>
                                <span class="text-gray-500">·</span>
                                <span class="font-medium text-gray-900">Target Akhir</span>
                                <span class="font-mono font-semibold text-gray-900">{{ $ringkasHarga['target'] !== null ? 'Rp '.number_format($ringkasHarga['target'], 0, ',', '.') : '-' }}</span>
                                <span class="text-gray-500">·</span>
                                <span class="font-medium text-gray-900">Selisih (Target &minus; Harga)</span>
                                <span class="font-mono font-semibold {{ ($ringkasHarga['selisih'] ?? 0) < 0 ? 'text-[#ee0000]' : 'text-gray-900' }}">{{ $ringkasHarga['selisih'] !== null ? 'Rp '.number_format($ringkasHarga['selisih'], 0, ',', '.') : '-' }}</span>
                                @if($ringkasHarga['melebihi'])
                                    <span class="rounded-full bg-[#f7d4d6] px-2.5 py-0.5 text-xs font-bold text-[#c50000]">Total harga melebihi target - tinjau kembali!</span>
                                @endif
                            </div>
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
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $item->isPaket() ? 'Tanpa komisi' : $item->persen_komisi.'%' }}
                            </td>
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

    @if($tampilKonfirmasiHapus)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Hapus Produk?</h3>
                <p class="mt-2 text-sm text-gray-500">Produk akan dihapus permanen.</p>
                <p class="mt-1 text-xs text-gray-400">Jika produk ini sudah pernah digunakan nasabah, penghapusan akan gagal - gunakan tombol Nonaktifkan sebagai gantinya.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('tampilKonfirmasiHapus', false)" class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">Batal</button>
                    <button wire:click="delete" class="rounded-full bg-[#ee0000] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">Hapus</button>
                </div>
            </div>
        </div>
    @endif
</div>
