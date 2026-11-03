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

    {{-- Drawer Slide-over: Tambah & Edit Produk (Sesuai drw(..., pfH(), 1) di prototipe HTML) --}}
    @if($showForm)
        <div class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true" aria-label="{{ $editId ? 'Edit Produk Tabungan' : 'Tambah Produk Tabungan Baru' }}">
            <div class="fixed inset-0 bg-black/40 transition-opacity cursor-pointer" wire:click="toggleForm"></div>

            <div class="relative z-10 flex h-full w-full max-w-2xl flex-col border-l border-hairline/60 bg-surface shadow-2xl transition-all">
                {{-- Header Drawer --}}
                <div class="flex items-center justify-between border-b border-hairline/40 px-6 py-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-semibold text-ink">{{ $editId ? 'Edit Produk Tabungan' : 'Tambah Produk Tabungan Baru' }}</h3>
                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $tipe === 'paket' ? 'border border-primary/20 bg-primary-soft text-on-primary-soft' : 'border border-hairline/60 bg-inset text-body' }}">
                                {{ $tipe === 'paket' ? 'Paket' : 'Bebas' }}
                            </span>
                        </div>
                        <p class="text-xs text-mute mt-0.5">Atur skema tabungan, komitmen harian, dan barang sembako.</p>
                    </div>
                    <button type="button" wire:click="toggleForm"
                        class="flex size-8 items-center justify-center rounded-full text-mute transition hover:bg-inset hover:text-ink focus:outline-none"
                        aria-label="Tutup drawer">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body Drawer --}}
                <form wire:submit="save" class="flex flex-col flex-1 overflow-hidden">
                    <div class="flex-1 overflow-y-auto p-6 space-y-6">
                        {{-- Seksi 1: Identitas Produk --}}
                        <div class="rounded-xl border border-hairline/40 bg-inset/30 p-5">
                            <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-mute">1. Identitas Produk</h4>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-ink">Tipe Produk</label>
                                    <select wire:model.live="tipe"
                                        class="h-[38px] w-full rounded-xl border border-hairline/60 bg-surface px-3 text-xs font-medium text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">
                                        <option value="bebas">Tabungan Bebas (Fleksibel, Komisi %)</option>
                                        <option value="paket">Tabungan Paket (Harian Tetap, Hasil Sembako/Uang)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-ink">Nama Produk</label>
                                    <input type="text" wire:model="nama"
                                        class="h-[38px] w-full rounded-xl border border-hairline/60 bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                        placeholder="Contoh: Paket Lebaran A (Umi Uma)" />
                                    @error('nama') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                </div>

                                @if($tipe === 'bebas')
                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-ink">Komisi Penarikan (%)</label>
                                        <input type="number" wire:model="persen_komisi" step="0.01"
                                            class="h-[38px] w-full rounded-xl border border-hairline/60 bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                            placeholder="5.00" />
                                        @error('persen_komisi') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                    </div>
                                @else
                                    <div class="sm:col-span-2 rounded-xl border border-primary/20 bg-primary-soft p-3.5 text-xs text-on-primary-soft">
                                        <span class="font-semibold">Aturan Tabungan Paket:</span> Nasabah menabung cicilan harian tetap. Penarikan dicairkan saat jatuh tempo dalam bentuk barang sembako (bebas potongan komisi / 0%).
                                    </div>
                                    @if($editId && $jumlahPesertaEdit > 0)
                                        <div class="sm:col-span-2 rounded-xl border border-warning/20 bg-warning-soft p-3.5 text-xs text-warning-deep">
                                            <span class="font-semibold">Perhatian:</span> Produk ini sudah diikuti {{ $jumlahPesertaEdit }} nasabah aktif. Mengubah harga per hari atau periode akan menghitung ulang jadwal dan tunggakan seluruh peserta.
                                        </div>
                                    @endif
                                @endif

                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-ink">Minimal Setor (Rp)</label>
                                    <input type="number" wire:model="minimal_setor"
                                        class="h-[38px] w-full rounded-xl border border-hairline/60 bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                        placeholder="10000" />
                                    @error('minimal_setor') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Seksi Khusus Paket --}}
                        @if($tipe === 'paket')
                            {{-- Seksi 2: Skema Cicilan & Kalender --}}
                            <div class="rounded-xl border border-hairline/40 bg-inset/30 p-5">
                                <div class="mb-3 flex items-center justify-between">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-mute">2. Skema Cicilan &amp; Kalender Periode</h4>
                                    @if($ringkasHarga['totalHari'])
                                        <span class="rounded-full border border-primary/20 bg-primary-soft px-2.5 py-0.5 text-xs font-semibold text-on-primary-soft">
                                            Total Durasi: {{ $ringkasHarga['totalHari'] }} Hari
                                        </span>
                                    @endif
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-ink">Setoran Per Hari (Rp)</label>
                                        <input type="number" wire:model.live="harga_per_hari"
                                            class="h-[38px] w-full rounded-xl border border-hairline/60 bg-surface px-3 text-xs font-semibold tabular-nums text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                            placeholder="Contoh: 5500" />
                                        @error('harga_per_hari') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-ink">Toleransi Tunggakan (hari)</label>
                                        <input type="number" wire:model="batas_toleransi"
                                            class="h-[38px] w-full rounded-xl border border-hairline/60 bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                            placeholder="Contoh: 3 atau 7" />
                                        @error('batas_toleransi') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-ink">Periode Mulai</label>
                                        <x-ui.tanggal wire:model.live="periode_mulai"
                                            class="h-[38px] w-full rounded-xl border border-hairline/60 bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20" />
                                        @error('periode_mulai') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-ink">Periode Selesai</label>
                                        <x-ui.tanggal wire:model.live="periode_selesai" :min="$periode_mulai"
                                            class="h-[38px] w-full rounded-xl border border-hairline/60 bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20" />
                                        @error('periode_selesai') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-ink">Tanggal Boleh Cair (Jatuh Tempo)</label>
                                        <x-ui.tanggal wire:model="tanggal_boleh_cair"
                                            class="h-[38px] w-full rounded-xl border border-hairline/60 bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20" />
                                        <p class="mt-1 text-[11px] text-mute">Biasanya saat Lebaran / setelah periode selesai.</p>
                                        @error('tanggal_boleh_cair') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-ink">Batas Daftar Hingga (opsional)</label>
                                        <x-ui.tanggal wire:model="batas_daftar_hingga" :max="now()->addYears(1)->toDateString()"
                                            placeholder="Tanpa batas"
                                            class="h-[38px] w-full rounded-xl border border-hairline/60 bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20" />
                                        <p class="mt-1 text-[11px] text-mute">Tenggat terakhir nasabah baru boleh ikut.</p>
                                        @error('batas_daftar_hingga') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="sm:col-span-2 flex flex-col gap-2.5 pt-2 border-t border-hairline/40">
                                        <label class="flex items-center gap-2 text-xs font-medium text-ink cursor-pointer">
                                            <input type="checkbox" wire:model="boleh_cair_saat_target" class="h-4 w-4 rounded border-hairline/60 text-primary focus:ring-primary" />
                                            <span>Boleh cair lebih awal bila target tabungan sudah tercapai 100%</span>
                                        </label>
                                        <label class="flex items-center gap-2 text-xs font-medium text-ink cursor-pointer">
                                            <input type="checkbox" wire:model="tampilkan_harga_ke_nasabah" class="h-4 w-4 rounded border-hairline/60 text-primary focus:ring-primary" />
                                            <span>Tampilkan estimasi harga barang ke nasabah (opsional, bawaan: dirahasiakan)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Seksi 3: Hadiah Uang Tunai & Rincian Sembako --}}
                            <div class="rounded-xl border border-hairline/40 bg-inset/30 p-5 space-y-4">
                                <div>
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-mute">3. Hadiah Uang Tunai &amp; Barang Sembako</h4>
                                    <p class="text-xs text-mute mt-0.5">Sesuai dengan brosur fisik pengadaan paket lebaran.</p>
                                </div>

                                {{-- Uang Tunai Cashback --}}
                                <div class="rounded-xl border border-hairline/40 bg-surface p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div>
                                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                Uang Tunai / Cashback Saku Lebaran (Opsional)
                                            </span>
                                            <p class="text-xs text-mute mt-0.5">Misal pada Paket A: nasabah selain dapat sembako juga mendapatkan uang tunai Rp 500.000 saat pencairan.</p>
                                        </div>
                                        <div class="w-full sm:w-56">
                                            <div class="relative">
                                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-mute">Rp</span>
                                                <input type="number" wire:model.live="uang_tunai"
                                                    class="h-[38px] w-full rounded-xl border border-hairline/60 bg-surface pl-9 pr-3 text-xs font-semibold tabular-nums text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                                    placeholder="Contoh: 500000" />
                                            </div>
                                            @error('uang_tunai') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Repeater Isi Barang --}}
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-semibold text-ink">Daftar Barang Sembako</label>
                                        <button type="button" wire:click="tambahItemPaket"
                                            class="inline-flex items-center gap-1.5 rounded-xl border border-hairline/60 bg-surface px-3 py-1.5 text-xs font-semibold text-ink shadow-xs transition hover:bg-surface-2 active:scale-95">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                            Tambah Barang
                                        </button>
                                    </div>

                                    <div class="space-y-2">
                                        <div class="hidden sm:grid sm:grid-cols-12 gap-2 px-1 text-[11px] font-semibold text-mute uppercase">
                                            <div class="col-span-5">Nama Barang</div>
                                            <div class="col-span-3">Takaran / Jumlah</div>
                                            <div class="col-span-3">Estimasi Modal Beli (Rp)</div>
                                            <div class="col-span-1 text-center">Aksi</div>
                                        </div>

                                        @foreach($isiPaketItems as $index => $item)
                                            <div class="flex flex-col sm:grid sm:grid-cols-12 gap-2 rounded-xl border border-hairline/40 bg-surface p-2.5 sm:items-center">
                                                <div class="sm:col-span-5">
                                                    <input type="text" wire:model="isiPaketItems.{{ $index }}.nama"
                                                        class="h-[34px] w-full rounded-lg border border-hairline/60 bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                                        placeholder="Nama barang (misal: Beras, Daging Sapi)" />
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <input type="text" wire:model="isiPaketItems.{{ $index }}.jumlah"
                                                        class="h-[34px] w-full rounded-lg border border-hairline/60 bg-surface px-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                                        placeholder="Ukuran (misal: 5 kg, 2 Ltr, 1 btl)" />
                                                </div>
                                                <div class="sm:col-span-3">
                                                    <input type="number" min="0" wire:model.live="isiPaketItems.{{ $index }}.harga"
                                                        class="h-[34px] w-full rounded-lg border border-hairline/60 bg-surface px-3 text-xs tabular-nums text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                                                        placeholder="Harga modal (opsional)" />
                                                    @error('isiPaketItems.'.$index.'.harga') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                                </div>
                                                <div class="sm:col-span-1 flex items-center justify-end sm:justify-center gap-1">
                                                    <button type="button" wire:click="naikkanItemPaket({{ $index }})"
                                                        aria-label="Naikkan urutan"
                                                        class="flex h-8 w-7 items-center justify-center rounded-lg border border-hairline/60 bg-surface text-mute transition hover:bg-surface-2 disabled:opacity-30"
                                                        {{ $index === 0 ? 'disabled' : '' }}>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                                                    </button>
                                                    <button type="button" wire:click="turunkanItemPaket({{ $index }})"
                                                        aria-label="Turunkan urutan"
                                                        class="flex h-8 w-7 items-center justify-center rounded-lg border border-hairline/60 bg-surface text-mute transition hover:bg-surface-2 disabled:opacity-30"
                                                        {{ $index === count($isiPaketItems) - 1 ? 'disabled' : '' }}>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                                    </button>
                                                    <button type="button" wire:click="hapusItemPaket({{ $index }})"
                                                        title="Hapus baris"
                                                        class="flex h-8 w-7 items-center justify-center rounded-lg border border-hairline/60 bg-surface text-danger transition hover:bg-danger-soft">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Seksi 4: Ringkasan Analisis Finansial & Margin Laba Pengelola --}}
                            <div class="rounded-xl border border-hairline/40 bg-inset/30 p-5">
                                <div class="mb-3 flex items-center justify-between">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-mute">4. Analisis Finansial &amp; Margin Laba Pengelola</h4>
                                    @if($ringkasHarga['melebihi'])
                                        <span class="rounded-full border border-danger/30 bg-danger-soft px-3 py-1 text-xs font-semibold text-danger">
                                            Peringatan: Modal Pengadaan Melebihi Target!
                                        </span>
                                    @elseif($ringkasHarga['target'] !== null && ($ringkasHarga['selisih'] ?? 0) >= 0)
                                        <span class="rounded-full border border-primary/20 bg-primary-soft px-3 py-1 text-xs font-semibold text-on-primary-soft">
                                            Margin Sehat &amp; Layak Dijalankan
                                        </span>
                                    @endif
                                </div>

                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    <div class="rounded-xl bg-surface p-3.5 border border-hairline/60">
                                        <span class="text-xs text-mute font-medium">Durasi Komitmen</span>
                                        <p class="mt-1 text-base font-semibold tabular-nums text-ink">
                                            {{ $ringkasHarga['totalHari'] ? $ringkasHarga['totalHari'].' Hari' : '-' }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-surface p-3.5 border border-hairline/60">
                                        <span class="text-xs text-mute font-medium">Target Tabungan Nasabah</span>
                                        <p class="mt-1 text-base font-semibold tabular-nums text-ink">
                                            {{ $ringkasHarga['target'] !== null ? 'Rp '.number_format($ringkasHarga['target'], 0, ',', '.') : '-' }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-surface p-3.5 border border-hairline/60">
                                        <span class="text-xs text-mute font-medium">Total Modal Sembako + Uang</span>
                                        <p class="mt-1 text-base font-semibold tabular-nums text-ink">
                                            Rp {{ number_format($ringkasHarga['totalHarga'], 0, ',', '.') }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl p-3.5 border {{ ($ringkasHarga['selisih'] ?? 0) < 0 ? 'bg-danger-soft border-danger/30 text-danger' : 'bg-primary-soft border-primary/20 text-on-primary-soft' }}">
                                        <span class="text-xs font-medium opacity-80">Estimasi Margin / Laba Bersih</span>
                                        <p class="mt-1 text-base font-semibold tabular-nums">
                                            {{ $ringkasHarga['selisih'] !== null ? 'Rp '.number_format($ringkasHarga['selisih'], 0, ',', '.') : '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Footer Drawer --}}
                    <div class="border-t border-hairline/40 p-4 px-6 bg-surface flex items-center justify-end gap-3 shrink-0">
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
