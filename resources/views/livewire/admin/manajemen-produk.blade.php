<div>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Kelola Produk Tabungan</h1>
            <p class="mt-1 text-sm text-mute">Buat dan kelola jenis produk tabungan.</p>
        </div>
        <button wire:click="toggleForm"
            class="min-h-11 inline-flex shrink-0 items-center gap-2 rounded-md bg-accent px-4 py-2 text-sm font-medium text-accent-foreground transition hover:opacity-90">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Tambah Produk
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-info-soft px-4 py-3 text-sm text-info">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($showForm)
        <div class="mb-6 rounded-lg bg-inset shadow-[inset_0_0_0_1px_#e5e5e5]">
            <div class="border-b border-hairline px-6 py-4">
                <h3 class="text-sm font-semibold text-ink">{{ $editId ? 'Edit Produk' : 'Tambah Produk Baru' }}</h3>
            </div>
            <form wire:submit="save" class="space-y-4 p-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Nama Produk</label>
                        <input type="text" wire:model="nama"
                            class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                            placeholder="contoh: Paket A" />
                        @error('nama') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Tipe</label>
                        <select wire:model="tipe"
                            class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
                            <option value="bebas">Bebas</option>
                            <option value="paket">Paket</option>
                        </select>
                    </div>

                    @if($tipe === 'paket')
                        <div class="sm:col-span-2">
                            <div class="rounded-lg bg-inset px-4 py-3 text-xs text-body">
                                Paket tidak pakai komisi persentase — komisi selalu 0% (keputusan D6), nilainya sudah tetap sesuai harga/hari &amp; isi barang di bawah.
                            </div>
                        </div>
                        @if($editId && $jumlahPesertaEdit > 0)
                            <div class="sm:col-span-2">
                                <div class="rounded-lg bg-warning-soft px-4 py-3 text-xs text-warning-deep">
                                    Produk ini sudah diikuti {{ $jumlahPesertaEdit }} peserta. Mengubah harga/hari atau periode akan menghitung ulang tunggakan seluruh peserta.
                                </div>
                            </div>
                        @endif
                    @else
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Komisi (%)</label>
                            <input type="number" wire:model="persen_komisi" step="0.01"
                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                                placeholder="5" />
                            @error('persen_komisi') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Minimal Setor</label>
                        <input type="number" wire:model="minimal_setor"
                            class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                            placeholder="10000" />
                    </div>

                    @if($tipe === 'paket')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Harga Per Hari</label>
                            <input type="number" wire:model="harga_per_hari"
                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                                placeholder="5500" />
                            @error('harga_per_hari') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Batas Toleransi (hari)</label>
                            <input type="number" wire:model="batas_toleransi"
                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                                placeholder="7" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Periode Mulai</label>
                            <x-ui.tanggal wire:model="periode_mulai"
                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Periode Selesai</label>
                            <x-ui.tanggal wire:model="periode_selesai" :min="$periode_mulai"
                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Tanggal Boleh Cair</label>
                            <x-ui.tanggal wire:model="tanggal_boleh_cair"
                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Uang Tunai (opsional)</label>
                            <input type="number" wire:model="uang_tunai"
                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                                placeholder="500000" />
                            @error('uang_tunai') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Batas Daftar Hingga (opsional)</label>
                            <x-ui.tanggal wire:model="batas_daftar_hingga" :max="now()->addYears(1)->toDateString()"
                                placeholder="Tanpa batas"
                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                            @error('batas_daftar_hingga') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex flex-col gap-3 sm:col-span-2 sm:flex-row sm:items-end">
                            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                <input type="checkbox" wire:model="tampilkan_harga_ke_nasabah" class="h-4 w-4 rounded border-hairline text-info focus:ring-ink" />
                                Tampilkan harga ke nasabah
                            </label>
                            <label class="flex items-center gap-2 text-sm font-medium text-ink">
                                <input type="checkbox" wire:model="boleh_cair_saat_target" class="h-4 w-4 rounded border-hairline text-info focus:ring-ink" />
                                Boleh cair lebih awal saat target tercapai
                            </label>
                        </div>

                        {{-- Isi Paket Repeater --}}
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-ink">Isi Barang Paket</label>
                            <div class="space-y-2">
                                @foreach($isiPaketItems as $index => $item)
                                    <div class="flex items-end gap-2">
                                        <div class="flex-1">
                                            <input type="text" wire:model="isiPaketItems.{{ $index }}.nama"
                                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                                                placeholder="contoh: Beras" />
                                        </div>
                                        <div class="flex-1">
                                            <input type="text" wire:model="isiPaketItems.{{ $index }}.jumlah"
                                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                                                placeholder="contoh: 5 kg" />
                                        </div>
                                        <div class="w-32">
                                            <input type="number" min="0" wire:model="isiPaketItems.{{ $index }}.harga"
                                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                                                placeholder="Harga (opsional)" />
                                            @error('isiPaketItems.'.$index.'.harga') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                                        </div>
                                        <button type="button" wire:click="naikkanItemPaket({{ $index }})"
                                            aria-label="Naikkan urutan"
                                            class="min-h-11 flex h-10 w-8 shrink-0 items-center justify-center rounded-md border border-hairline text-mute transition hover:bg-inset disabled:opacity-40"
                                            {{ $index === 0 ? 'disabled' : '' }}>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                                        </button>
                                        <button type="button" wire:click="turunkanItemPaket({{ $index }})"
                                            aria-label="Turunkan urutan"
                                            class="min-h-11 flex h-10 w-8 shrink-0 items-center justify-center rounded-md border border-hairline text-mute transition hover:bg-inset disabled:opacity-40"
                                            {{ $index === count($isiPaketItems) - 1 ? 'disabled' : '' }}>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                        </button>
                                        <button type="button" wire:click="hapusItemPaket({{ $index }})"
                                            class="min-h-11 flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-hairline text-danger transition hover:bg-danger-soft">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" wire:click="tambahItemPaket"
                                class="min-h-11 mt-2 inline-flex items-center gap-1.5 rounded-md border border-hairline bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:bg-inset">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                Tambah Barang
                            </button>
                            <p class="mt-1.5 text-xs text-mute">Isi sesuai yang tertulis di brosur, misalnya Beras 5 kg, Ayam 2 kg, Gula 1 kg. Kolom harga hanya untuk admin (D15) - kosongkan bila tidak ada.</p>

                            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 rounded-lg bg-inset px-4 py-3 text-sm shadow-[inset_0_0_0_1px_#e5e5e5]">
                                <span class="font-medium text-ink">Total Harga Barang</span>
                                <span class="tabular-nums font-semibold text-ink">Rp {{ number_format($ringkasHarga['totalHarga'], 0, ',', '.') }}</span>
                                <span class="text-mute">·</span>
                                <span class="font-medium text-ink">Target Akhir</span>
                                <span class="tabular-nums font-semibold text-ink">{{ $ringkasHarga['target'] !== null ? 'Rp '.number_format($ringkasHarga['target'], 0, ',', '.') : '-' }}</span>
                                <span class="text-mute">·</span>
                                <span class="font-medium text-ink">Selisih (Target &minus; Harga)</span>
                                <span class="tabular-nums font-semibold {{ ($ringkasHarga['selisih'] ?? 0) < 0 ? 'text-danger' : 'text-ink' }}">{{ $ringkasHarga['selisih'] !== null ? 'Rp '.number_format($ringkasHarga['selisih'], 0, ',', '.') : '-' }}</span>
                                @if($ringkasHarga['melebihi'])
                                    <span class="rounded-full bg-danger-soft px-2.5 py-0.5 text-xs font-semibold text-danger">Total harga melebihi target - tinjau kembali!</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex gap-3">
                    <button type="submit" wire:loading.attr="disabled"
                        class="min-h-11 rounded-md bg-accent px-4 py-2 text-sm font-medium text-accent-foreground transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
                        {{ $editId ? 'Simpan Perubahan' : 'Tambah Produk' }}
                    </button>
                    <button type="button" wire:click="toggleForm"
                        class="min-h-11 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-hidden rounded-lg bg-white shadow-[inset_0_0_0_1px_#e5e5e5]">
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-inset">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nama</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tipe</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Komisi</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Harga/Hari</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($produk as $item)
                        <tr class="transition hover:bg-inset">
                            <td class="px-4 py-3 text-sm font-medium text-ink">{{ $item->nama }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs {{ $item->tipe === 'paket' ? 'bg-inset text-body' : 'bg-info-soft text-info' }}">
                                    {{ ucfirst($item->tipe) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-body">
                                {{ $item->isPaket() ? 'Tanpa komisi' : $item->persen_komisi.'%' }}
                            </td>
                            <td class="px-4 py-3 tabular-nums text-sm text-body">{{ $item->harga_per_hari ? 'Rp ' . number_format($item->harga_per_hari, 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3">
                                @if($item->status === 'aktif')
                                    <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Aktif</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-inset px-2.5 py-0.5 text-xs text-mute">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <button wire:click="edit({{ $item->id }})" class="min-h-11 rounded-full p-1.5 text-info transition hover:bg-info-soft">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button wire:click="toggleStatus({{ $item->id }})" class="min-h-11 rounded-full p-1.5 text-warning-deep transition hover:bg-warning-soft">
                                        @if($item->status === 'aktif')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @endif
                                    </button>
                                    <button wire:click="confirmDelete({{ $item->id }})" class="min-h-11 rounded-full p-1.5 text-danger transition hover:bg-danger-soft">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                    <p class="text-sm text-mute">Tidak ada data produk.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($produk as $item)
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink">{{ $item->nama }}</p>
                            <p class="text-xs text-mute">{{ ucfirst($item->tipe) }}</p>
                        </div>
                        @if($item->status === 'aktif')
                            <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Aktif</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-inset px-2.5 py-0.5 text-xs text-mute">Nonaktif</span>
                        @endif
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                        <div>
                            <span class="block text-xs text-mute">Komisi</span>
                            <span class="tabular-nums text-sm text-body">{{ $item->isPaket() ? 'Tanpa komisi' : $item->persen_komisi.'%' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Harga/Hari</span>
                            <span class="tabular-nums text-sm font-semibold text-ink">{{ $item->harga_per_hari ? 'Rp ' . number_format($item->harga_per_hari, 0, ',', '.') : '-' }}</span>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-1">
                            <button wire:click="edit({{ $item->id }})" class="min-h-11 rounded-full p-1.5 text-info transition hover:bg-info-soft">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>
                            <button wire:click="toggleStatus({{ $item->id }})" class="min-h-11 rounded-full p-1.5 text-warning-deep transition hover:bg-warning-soft">
                                @if($item->status === 'aktif')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                @endif
                            </button>
                            <button wire:click="confirmDelete({{ $item->id }})" class="min-h-11 rounded-full p-1.5 text-danger transition hover:bg-danger-soft">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <p class="text-sm text-mute">Tidak ada data produk.</p>
                </div>
            @endforelse
        </div>
        <div class="border-t border-hairline px-4 py-3">{{ $produk->links() }}</div>
    </div>

    @if($tampilKonfirmasiHapus)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-[0_8px_24px_rgb(0_0_0/0.12)]">
                <h3 class="text-lg font-semibold text-ink">Hapus Produk?</h3>
                <p class="mt-2 text-sm text-mute">Produk akan dihapus permanen.</p>
                <p class="mt-1 text-xs text-subtle">Jika produk ini sudah pernah digunakan nasabah, penghapusan akan gagal - gunakan tombol Nonaktifkan sebagai gantinya.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('tampilKonfirmasiHapus', false)" class="min-h-11 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">Batal</button>
                    <button wire:click="delete" class="min-h-11 rounded-md bg-danger px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">Hapus</button>
                </div>
            </div>
        </div>
    @endif
</div>
