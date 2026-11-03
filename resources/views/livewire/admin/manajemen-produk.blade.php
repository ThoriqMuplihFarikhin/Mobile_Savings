<div class="space-y-5" data-portal="admin">
    {{-- Header Halaman --}}
    <x-admin.page-header
        breadcrumb="Keuangan"
        title="Kelola Produk Tabungan"
        subtitle="Buat dan kelola jenis produk tabungan."
    >
        <x-slot:actions>
            <button wire:click="toggleForm"
                class="min-h-11 inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-[0.98]">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                <span>Tambah Produk</span>
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-primary/20 bg-primary-soft p-3.5 text-xs font-medium text-on-primary-soft shadow-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-2xl border border-danger/20 bg-danger-soft p-3.5 text-xs font-medium text-danger shadow-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Tabel Produk Tabungan (Sesuai list('produk') di prototipe HTML) --}}
    <div class="overflow-hidden rounded-2xl border border-hairline/60 bg-surface shadow-xs">
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-hairline/40">
                        <th class="px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-mute">Nama</th>
                        <th class="px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-mute">Tipe</th>
                        <th class="px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-mute">Komisi</th>
                        <th class="px-5 py-3.5 text-right text-[11px] font-semibold uppercase tracking-wider text-mute">Harga/Hari</th>
                        <th class="px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-mute">Status</th>
                        <th class="px-5 py-3.5 text-right text-[11px] font-semibold uppercase tracking-wider text-mute">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline/30">
                    @forelse($produk as $item)
                        <tr class="transition hover:bg-inset/30">
                            <td class="px-5 py-3.5 text-xs font-semibold text-ink">{{ $item->nama }}</td>
                            <td class="px-5 py-3.5">
                                <x-admin.status-chip
                                    :label="ucfirst($item->tipe)"
                                    :variant="$item->tipe === 'paket' ? 'neutral' : 'info'"
                                />
                            </td>
                            <td class="px-5 py-3.5 text-xs text-mute">
                                {{ $item->isPaket() ? 'Tanpa komisi' : $item->persen_komisi.'%' }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold tabular-nums text-xs text-ink">{{ $item->harga_per_hari ? 'Rp ' . number_format($item->harga_per_hari, 0, ',', '.') : '-' }}</td>
                            <td class="px-5 py-3.5">
                                <x-admin.status-chip
                                    :label="$item->status === 'aktif' ? 'Aktif' : 'Nonaktif'"
                                    :variant="$item->status === 'aktif' ? 'success' : 'neutral'"
                                />
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <button wire:click="edit({{ $item->id }})"
                                        class="rounded-xl border border-hairline/60 bg-surface px-3 py-1.5 text-xs font-semibold text-ink shadow-xs transition hover:bg-surface-2 focus:outline-none">
                                        Edit
                                    </button>
                                    <button wire:click="toggleStatus({{ $item->id }})"
                                        class="rounded-xl border border-hairline/60 bg-surface px-3 py-1.5 text-xs font-semibold text-ink shadow-xs transition hover:bg-surface-2 focus:outline-none">
                                        {{ $item->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                    <button wire:click="confirmDelete({{ $item->id }})" title="Hapus produk"
                                        class="flex size-7 items-center justify-center rounded-lg text-mute transition hover:bg-danger-soft hover:text-danger focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-xs text-mute">
                                Belum ada produk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($produk as $item)
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-ink">{{ $item->nama }}</p>
                            <p class="text-[11px] text-mute">{{ ucfirst($item->tipe) }}</p>
                        </div>
                        <x-admin.status-chip
                            :label="$item->status === 'aktif' ? 'Aktif' : 'Nonaktif'"
                            :variant="$item->status === 'aktif' ? 'success' : 'neutral'"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-x-3 gap-y-1.5 rounded-xl bg-inset/40 p-2.5 text-[11px]">
                        <div>
                            <span class="block text-mute">Komisi</span>
                            <span class="tabular-nums font-medium text-body">{{ $item->isPaket() ? 'Tanpa komisi' : $item->persen_komisi.'%' }}</span>
                        </div>
                        <div>
                            <span class="block text-mute">Harga/Hari</span>
                            <span class="tabular-nums font-semibold text-ink">{{ $item->harga_per_hari ? 'Rp ' . number_format($item->harga_per_hari, 0, ',', '.') : '-' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button wire:click="edit({{ $item->id }})"
                            class="min-h-11 inline-flex items-center gap-1.5 rounded-xl border border-hairline/60 bg-surface px-3 py-1.5 text-xs font-semibold text-ink shadow-xs transition hover:bg-surface-2">
                            Edit
                        </button>
                        <button wire:click="toggleStatus({{ $item->id }})"
                            class="min-h-11 inline-flex items-center gap-1.5 rounded-xl border border-hairline/60 bg-surface px-3 py-1.5 text-xs font-semibold text-ink shadow-xs transition hover:bg-surface-2">
                            {{ $item->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                        <button wire:click="confirmDelete({{ $item->id }})" title="Hapus"
                            class="min-h-11 flex size-10 items-center justify-center rounded-xl border border-hairline/60 bg-surface text-danger shadow-xs transition hover:bg-danger-soft">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-xs text-mute">
                    Belum ada produk.
                </div>
            @endforelse
        </div>

        {{-- Footer Tabel --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-hairline/40 px-5 py-3.5 text-xs text-mute">
            <span>Menampilkan {{ $produk->count() }} dari {{ $produk->total() }} data</span>
            @if($produk->hasPages())
                <div>{{ $produk->links() }}</div>
            @else
                <span>Halaman {{ $produk->currentPage() }} dari {{ max(1, $produk->lastPage()) }}</span>
            @endif
        </div>
    </div>

    {{-- Drawer Slide-over: Tambah & Edit Produk (Sesuai pfH() di prototipe HTML) --}}
    @if($showForm)
        <div class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true" aria-label="{{ $editId ? 'Edit Produk Tabungan' : 'Tambah Produk Tabungan Baru' }}">
            <div class="fixed inset-0 bg-black/40 transition-opacity cursor-pointer" wire:click="toggleForm"></div>

            <div class="relative z-10 flex h-full w-full max-w-2xl flex-col border-l border-hairline/60 bg-surface shadow-2xl transition-all">
                {{-- Header Drawer (.dh) --}}
                <div class="flex items-center justify-between border-b border-hairline/40 px-6 py-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-semibold text-ink">{{ $editId ? 'Edit Produk Tabungan' : 'Tambah Produk Tabungan Baru' }}</h3>
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $tipe === 'paket' ? 'border border-primary/20 bg-primary-soft text-on-primary-soft' : 'border border-hairline/60 bg-inset text-body' }}">
                                {{ $tipe === 'paket' ? 'Tabungan Paket' : 'Tabungan Bebas' }}
                            </span>
                        </div>
                        <p class="text-xs text-mute mt-0.5">Atur identitas, skema cicilan, sembako, dan analisis margin finansial.</p>
                    </div>
                    <button type="button" wire:click="toggleForm"
                        class="flex size-8 items-center justify-center rounded-full text-mute transition hover:bg-inset hover:text-ink focus:outline-none"
                        aria-label="Tutup drawer">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body Drawer (.db) --}}
                <form wire:submit="save" class="flex flex-col flex-1 overflow-hidden">
                    <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">
                        
                        {{-- 1. IDENTITAS PRODUK (sec('1. Identitas produk')) --}}
                        <div class="space-y-3">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-mute">1. Identitas produk</h4>

                            {{-- Tipe produk --}}
                            <div>
                                <label class="mb-1 block text-xs font-medium text-ink">Tipe produk</label>
                                <select wire:model.live="tipe"
                                    class="h-10 w-full rounded-[10px] border border-hairline/60 bg-surface px-3 text-xs font-medium text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">
                                    <option value="paket">Tabungan Paket (harian tetap, hasil sembako/uang)</option>
                                    <option value="bebas">Tabungan Bebas (fleksibel, komisi %)</option>
                                </select>
                            </div>

                            {{-- Nama produk --}}
                            <div>
                                <label class="mb-1 block text-xs font-medium text-ink">Nama produk</label>
                                <input type="text" wire:model="nama"
                                    class="h-10 w-full rounded-[10px] border border-hairline/60 bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                    placeholder="Contoh: Paket Lebaran A" />
                                @error('nama') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>

                            {{-- Komisi penarikan (%) - khusus Tabungan Bebas --}}
                            @if($tipe === 'bebas')
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-ink">Komisi penarikan (%) - khusus Tabungan Bebas</label>
                                    <input type="number" wire:model="persen_komisi" step="0.01"
                                        class="h-10 w-full rounded-[10px] border border-hairline/60 bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                        placeholder="5.00" />
                                    @error('persen_komisi') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                </div>
                            @endif

                            {{-- Minimal Setor (Rp) --}}
                            <div>
                                <label class="mb-1 block text-xs font-medium text-ink">Minimal Setor (Rp)</label>
                                <input type="number" wire:model="minimal_setor"
                                    class="h-10 w-full rounded-[10px] border border-hairline/60 bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                    placeholder="10000" />
                                @error('minimal_setor') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>

                            @if($tipe === 'paket' && $editId && $jumlahPesertaEdit > 0)
                                <div class="rounded-xl border border-warning/20 bg-warning-soft p-3 text-xs text-warning-deep">
                                    <span class="font-semibold">Perhatian:</span> Produk ini sudah diikuti {{ $jumlahPesertaEdit }} nasabah aktif. Mengubah harga per hari atau periode akan menghitung ulang jadwal peserta.
                                </div>
                            @endif
                        </div>

                        {{-- 2. SKEMA CICILAN & KALENDER PERIODE (sec('2. Skema cicilan & kalender periode')) --}}
                        @if($tipe === 'paket')
                            <div class="space-y-3 border-t border-hairline/40 pt-5">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-mute">2. Skema cicilan &amp; kalender periode</h4>
                                    @if($ringkasHarga['totalHari'])
                                        <span class="rounded-full border border-primary/20 bg-primary-soft px-2.5 py-0.5 text-[11px] font-semibold text-on-primary-soft">
                                            Durasi: {{ $ringkasHarga['totalHari'] }} Hari
                                        </span>
                                    @endif
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    {{-- Setoran per hari (Rp) --}}
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-ink">Setoran per hari (Rp)</label>
                                        <input type="number" wire:model.live="harga_per_hari"
                                            class="h-10 w-full rounded-[10px] border border-hairline/60 bg-surface px-3 text-xs font-semibold tabular-nums text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                            placeholder="5500" />
                                        @error('harga_per_hari') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Toleransi tunggakan (hari) --}}
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-ink">Toleransi tunggakan (hari)</label>
                                        <input type="number" wire:model="batas_toleransi"
                                            class="h-10 w-full rounded-[10px] border border-hairline/60 bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                            placeholder="3" />
                                        @error('batas_toleransi') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Periode mulai --}}
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-ink">Periode mulai</label>
                                        <x-ui.tanggal wire:model.live="periode_mulai"
                                            class="h-10 w-full rounded-[10px] border border-hairline/60 bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20" />
                                        @error('periode_mulai') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Periode selesai --}}
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-ink">Periode selesai</label>
                                        <x-ui.tanggal wire:model.live="periode_selesai" :min="$periode_mulai"
                                            class="h-10 w-full rounded-[10px] border border-hairline/60 bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20" />
                                        @error('periode_selesai') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Tanggal boleh cair (jatuh tempo) --}}
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-ink">Tanggal boleh cair (jatuh tempo)</label>
                                        <x-ui.tanggal wire:model="tanggal_boleh_cair"
                                            class="h-10 w-full rounded-[10px] border border-hairline/60 bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20" />
                                        @error('tanggal_boleh_cair') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Batas daftar hingga (opsional) --}}
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-ink">Batas daftar hingga (opsional)</label>
                                        <x-ui.tanggal wire:model="batas_daftar_hingga" :max="now()->addYears(1)->toDateString()"
                                            placeholder="Tanpa batas"
                                            class="h-10 w-full rounded-[10px] border border-hairline/60 bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20" />
                                        @error('batas_daftar_hingga') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                {{-- Checkbox Opsi Tambahan --}}
                                <div class="space-y-2 pt-2">
                                    <label class="flex items-center gap-2.5 text-xs text-ink cursor-pointer">
                                        <input type="checkbox" wire:model="boleh_cair_saat_target" class="size-4 rounded border-hairline/60 text-primary focus:ring-primary" />
                                        <span>Boleh cair lebih awal bila target tabungan sudah tercapai 100%</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 text-xs text-ink cursor-pointer">
                                        <input type="checkbox" wire:model="tampilkan_harga_ke_nasabah" class="size-4 rounded border-hairline/60 text-primary focus:ring-primary" />
                                        <span>Tampilkan estimasi harga barang ke nasabah (bawaan: dirahasiakan)</span>
                                    </label>
                                </div>
                            </div>

                            {{-- 3. HADIAH UANG TUNAI & BARANG SEMBAKO (sec('3. Hadiah uang tunai & barang sembako')) --}}
                            <div class="space-y-3 border-t border-hairline/40 pt-5">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-mute">3. Hadiah uang tunai &amp; barang sembako</h4>

                                {{-- Uang tunai saat pencairan (Rp) --}}
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-ink">Uang tunai saat pencairan (Rp)</label>
                                    <input type="number" wire:model.live="uang_tunai"
                                        class="h-10 w-full rounded-[10px] border border-hairline/60 bg-surface px-3 text-xs font-semibold tabular-nums text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                        placeholder="500000" />
                                    @error('uang_tunai') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                </div>

                                {{-- Repeater Daftar Barang Sembako Sesuai Prototipe HTML --}}
                                <div class="space-y-2 pt-1">
                                    <label class="block text-xs font-medium text-ink">Daftar barang sembako</label>
                                    @foreach($isiPaketItems as $index => $item)
                                        <div class="grid grid-cols-12 gap-2 items-center">
                                            <div class="col-span-5">
                                                <input type="text" wire:model="isiPaketItems.{{ $index }}.nama"
                                                    class="h-9 w-full rounded-[8px] border border-hairline/60 bg-surface px-2.5 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                                    placeholder="Nama barang" aria-label="Nama barang" />
                                            </div>
                                            <div class="col-span-3">
                                                <input type="text" wire:model="isiPaketItems.{{ $index }}.jumlah"
                                                    class="h-9 w-full rounded-[8px] border border-hairline/60 bg-surface px-2.5 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                                    placeholder="5 kg" aria-label="Takaran" />
                                            </div>
                                            <div class="col-span-3">
                                                <input type="number" min="0" wire:model.live="isiPaketItems.{{ $index }}.harga"
                                                    class="h-9 w-full rounded-[8px] border border-hairline/60 bg-surface px-2.5 text-xs tabular-nums text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                                    placeholder="Harga modal" aria-label="Harga modal" />
                                                @error('isiPaketItems.'.$index.'.harga') <p class="mt-1 text-[11px] text-danger">{{ $message }}</p> @enderror
                                            </div>
                                            <div class="col-span-1 flex items-center justify-end gap-1">
                                                <button type="button" wire:click="naikkanItemPaket({{ $index }})"
                                                    aria-label="Naikkan urutan"
                                                    class="flex size-7 items-center justify-center rounded-[6px] border border-hairline/60 bg-surface text-mute transition hover:bg-surface-2 disabled:opacity-25"
                                                    {{ $index === 0 ? 'disabled' : '' }}>
                                                    &uarr;
                                                </button>
                                                <button type="button" wire:click="turunkanItemPaket({{ $index }})"
                                                    aria-label="Turunkan urutan"
                                                    class="flex size-7 items-center justify-center rounded-[6px] border border-hairline/60 bg-surface text-mute transition hover:bg-surface-2 disabled:opacity-25"
                                                    {{ $index === count($isiPaketItems) - 1 ? 'disabled' : '' }}>
                                                    &darr;
                                                </button>
                                                <button type="button" wire:click="hapusItemPaket({{ $index }})"
                                                    title="Hapus barang"
                                                    class="flex size-7 items-center justify-center rounded-[6px] border border-hairline/60 bg-surface text-danger transition hover:bg-danger-soft">
                                                    &times;
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="pt-1">
                                        <button type="button" wire:click="tambahItemPaket"
                                            class="inline-flex items-center gap-1.5 rounded-[10px] border border-hairline/60 bg-surface px-3 py-1.5 text-xs font-semibold text-ink shadow-xs transition hover:bg-surface-2 active:scale-95">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                            Tambah barang
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- 4. ANALISIS FINANSIAL & MARGIN (sec('4. Analisis finansial & margin')) Sesuai .kv di HTML --}}
                            <div class="space-y-3 border-t border-hairline/40 pt-5">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-mute">4. Analisis finansial &amp; margin</h4>

                                <div class="rounded-xl border border-hairline/60 bg-surface p-4 space-y-2">
                                    <div class="flex items-center justify-between py-1.5 text-xs border-b border-dashed border-hairline/60">
                                        <span class="text-mute">Durasi komitmen</span>
                                        <b class="font-medium text-ink tabular-nums">{{ $ringkasHarga['totalHari'] ? $ringkasHarga['totalHari'].' hari' : '-' }}</b>
                                    </div>
                                    <div class="flex items-center justify-between py-1.5 text-xs border-b border-dashed border-hairline/60">
                                        <span class="text-mute">Target tabungan nasabah</span>
                                        <b class="font-medium text-ink tabular-nums">{{ $ringkasHarga['target'] !== null ? 'Rp ' . number_format($ringkasHarga['target'], 0, ',', '.') : '-' }}</b>
                                    </div>
                                    <div class="flex items-center justify-between py-1.5 text-xs border-b border-dashed border-hairline/60">
                                        <span class="text-mute">Total modal sembako + uang</span>
                                        <b class="font-medium text-ink tabular-nums">Rp {{ number_format($ringkasHarga['totalHarga'], 0, ',', '.') }}</b>
                                    </div>
                                    <div class="flex items-center justify-between pt-1.5 text-xs">
                                        <span class="text-mute">Estimasi margin / laba bersih</span>
                                        <b class="font-semibold tabular-nums {{ ($ringkasHarga['selisih'] ?? 0) >= 0 ? 'text-primary' : 'text-danger' }}">
                                            {{ $ringkasHarga['selisih'] !== null ? 'Rp ' . number_format($ringkasHarga['selisih'], 0, ',', '.') : '-' }}
                                        </b>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Footer Drawer (.df) --}}
                    <div class="flex items-center justify-end gap-2.5 border-t border-hairline/40 px-6 py-4 bg-surface shrink-0">
                        <button type="button" wire:click="toggleForm"
                            class="rounded-xl border border-hairline/60 bg-surface px-5 py-2.5 text-xs font-semibold text-ink shadow-xs transition hover:bg-surface-2 focus:outline-none">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover disabled:opacity-50 focus:outline-none">
                            <span wire:loading.remove wire:target="save">{{ $editId ? 'Simpan Perubahan Produk' : 'Simpan & Terbitkan Produk' }}</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Konfirmasi Hapus Produk --}}
    @if($tampilKonfirmasiHapus)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modal-hapus-produk-title">
            <div wire:click="$set('tampilKonfirmasiHapus', false)" class="fixed inset-0 bg-black/40 transition-opacity" aria-hidden="true"></div>
            <div class="relative w-full max-w-md rounded-2xl border border-hairline/60 bg-surface p-6 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-danger-soft text-danger">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div>
                            <h3 id="modal-hapus-produk-title" class="text-sm font-semibold text-ink">Hapus Produk?</h3>
                            <p class="mt-0.5 text-xs text-mute">Produk akan dihapus permanen.</p>
                        </div>
                    </div>
                    <button type="button" wire:click="$set('tampilKonfirmasiHapus', false)"
                        class="flex size-8 items-center justify-center rounded-full text-mute transition hover:bg-inset hover:text-ink"
                        aria-label="Tutup modal">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-mute">Jika produk ini sudah pernah digunakan nasabah, penghapusan akan dicegah secara otomatis &mdash; Anda dapat menonaktifkannya alih-alih menghapusnya.</p>
                <div class="mt-6 flex justify-end gap-2.5">
                    <button wire:click="$set('tampilKonfirmasiHapus', false)" class="rounded-xl border border-hairline/60 bg-surface px-4 py-2 text-xs font-semibold text-ink transition hover:bg-surface-2">Batal</button>
                    <button wire:click="delete" class="rounded-xl bg-danger px-4 py-2 text-xs font-semibold text-white transition hover:opacity-90">Hapus</button>
                </div>
            </div>
        </div>
    @endif
</div>
