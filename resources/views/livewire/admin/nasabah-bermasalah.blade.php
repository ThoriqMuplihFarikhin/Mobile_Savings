<div>
    <x-admin.page-header
        breadcrumb="Nasabah"
        title="Nasabah Bermasalah"
        subtitle="Daftar nasabah dengan kepesertaan paket yang perlu review."
    />

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-[12px] border border-primary/30 bg-primary-soft px-4 py-3 text-xs font-semibold text-on-primary-soft">
            <flux:icon.check-circle class="size-4 shrink-0" />
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-[12px] border border-danger/30 bg-danger-soft px-4 py-3 text-xs font-semibold text-danger">
            <flux:icon.exclamation-circle class="size-4 shrink-0" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Kartu Utama Tabel --}}
    <div class="rounded-[14px] border border-hairline bg-surface shadow-xs overflow-hidden">
        {{-- Toolbar Pencarian (.tl .sr) --}}
        <div class="p-3.5 sm:p-4 border-b border-hairline">
            <div class="relative w-full sm:max-w-xs">
                <flux:icon.magnifying-glass class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-mute pointer-events-none" />
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama nasabah atau produk..."
                    class="h-9 w-full rounded-[10px] border border-hairline bg-surface pl-9 pr-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                />
            </div>
        </div>

        {{-- Tabel Desktop (.tw) --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-hairline bg-surface-2 text-mute text-[11px] font-semibold uppercase tracking-wider">
                        <th class="px-4 py-3">NASABAH</th>
                        <th class="px-4 py-3">PRODUK</th>
                        <th class="px-4 py-3">TUNGGAKAN (HARI)</th>
                        <th class="px-4 py-3">SEHARUSNYA</th>
                        <th class="px-4 py-3">AKTUAL</th>
                        <th class="px-4 py-3">STATUS</th>
                        <th class="px-4 py-3 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($kepesertaan as $item)
                        <tr class="transition hover:bg-surface-2/50">
                            <td class="px-4 py-3.5 font-medium text-ink">
                                {{ $item->nasabah->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-body">
                                {{ $item->produk->nama ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 tabular-nums text-body">
                                {{ $item->tunggakan }}
                            </td>
                            <td class="px-4 py-3.5 tabular-nums text-body">
                                Rp {{ number_format($item->total_seharusnya_terkumpul, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 tabular-nums text-body">
                                Rp {{ number_format($item->total_aktual_terkumpul, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5">
                                <x-admin.status-chip status="Perlu Review" />
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <button
                                    type="button"
                                    wire:click="selectKepesertaan({{ $item->id }})"
                                    class="inline-flex h-[32px] items-center justify-center rounded-[8px] bg-primary hover:bg-primary-hover px-4 text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer"
                                >
                                    Tinjau
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-xs text-mute">
                                <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-full bg-surface-2 text-mute">
                                    <flux:icon.exclamation-triangle class="size-6" />
                                </div>
                                <p class="font-semibold text-ink">Tidak ada nasabah bermasalah.</p>
                                <p class="mt-1 text-[11px] text-mute">Semua kepesertaan paket nasabah berjalan lancar tanpa tunggakan review.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer (.ft) --}}
        <div class="flex items-center justify-between border-t border-hairline px-4 py-3 text-xs text-mute">
            <span>Menampilkan {{ $kepesertaan->count() }} dari {{ $kepesertaan->total() }} data</span>
            <span>Halaman {{ $kepesertaan->currentPage() }} dari {{ max(1, $kepesertaan->lastPage()) }}</span>
        </div>
    </div>

    {{-- Drawer Detail Kepesertaan Paket (Matching drw in HTML prototype) --}}
    @if($showDetail && $selectedKepesertaan)
        <div class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true" aria-labelledby="drawer-kepesertaan-title">
            <div class="fixed inset-0 bg-black/45 transition-opacity cursor-pointer" wire:click="$set('showDetail', false)"></div>

            <div class="relative z-10 flex h-full w-full max-w-[560px] flex-col border-l border-hairline bg-surface shadow-2xl transition-all">
                {{-- Header Drawer (.dh) --}}
                <div class="flex items-center justify-between border-b border-hairline px-5 py-4 shrink-0 bg-surface">
                    <h3 id="drawer-kepesertaan-title" class="text-base font-semibold text-ink">Detail Kepesertaan Paket</h3>
                    <button type="button" wire:click="$set('showDetail', false)"
                        class="flex size-8 items-center justify-center rounded-[10px] border border-hairline bg-surface-2 text-mute transition hover:bg-hairline hover:text-ink active:scale-95 cursor-pointer"
                        aria-label="Tutup panel">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </div>

                {{-- Content Drawer (.db) --}}
                <div class="flex-1 overflow-y-auto p-5 space-y-6 bg-surface">
                    {{-- Section 1: Kepesertaan (.sc) --}}
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-mute mb-3">Kepesertaan</h4>
                        <div class="space-y-2 rounded-[12px] border border-hairline bg-surface-2/60 p-4 text-xs">
                            <div class="flex items-center justify-between border-b border-hairline/60 pb-2">
                                <span class="text-mute">Nasabah</span>
                                <span class="font-semibold text-ink">{{ $selectedKepesertaan->nasabah->name ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-hairline/60 pb-2">
                                <span class="text-mute">Produk</span>
                                <span class="font-medium text-ink">{{ $selectedKepesertaan->produk->nama ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-hairline/60 pb-2">
                                <span class="text-mute">Tanggal mulai</span>
                                <span class="font-medium text-ink">{{ $selectedKepesertaan->tanggal_mulai_ikut ? $selectedKepesertaan->tanggal_mulai_ikut->translatedFormat('d M Y') : '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-hairline/60 pb-2">
                                <span class="text-mute">Seharusnya terkumpul</span>
                                <span class="font-semibold tabular-nums text-ink">Rp {{ number_format($selectedKepesertaan->total_seharusnya_terkumpul, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-hairline/60 pb-2">
                                <span class="text-mute">Aktual terkumpul</span>
                                <span class="font-semibold tabular-nums text-ink">Rp {{ number_format($selectedKepesertaan->total_aktual_terkumpul, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-1">
                                <span class="text-mute">Tunggakan</span>
                                <span class="font-semibold tabular-nums text-danger">{{ $selectedKepesertaan->tunggakan }} hari</span>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Keputusan akhir (.sc) --}}
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-mute mb-3">Keputusan Akhir</h4>
                        <form wire:submit="updateKeputusan" class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-ink mb-1.5">Keputusan</label>
                                <select wire:model.live="keputusan_akhir"
                                    class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                    <option value="">Pilih Keputusan</option>
                                    <option value="lanjut">Lanjut (Tunda hingga maks. 90 hari)</option>
                                    <option value="gagal_dikembalikan">Gagal - Dikembalikan</option>
                                    <option value="gagal_dialihkan">Gagal - Dialihkan</option>
                                </select>
                                @error('keputusan_akhir') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>

                            @if($keputusan_akhir === 'lanjut')
                                <div>
                                    <label class="block text-xs font-medium text-ink mb-1.5">Tunda Hingga (maks. 90 hari)</label>
                                    <x-ui.tanggal wire:model="ditunda_hingga" :min="now()->toDateString()"
                                        class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                                    @error('ditunda_hingga') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                </div>
                            @endif

                            @if($keputusan_akhir === 'gagal_dialihkan')
                                <div>
                                    <label class="block text-xs font-medium text-ink mb-1.5">Produk Tujuan</label>
                                    <select wire:model="produkTujuanId"
                                        class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                        <option value="">Pilih Produk Tujuan</option>
                                        @foreach($produkTujuan as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->tipe }})</option>
                                        @endforeach
                                    </select>
                                    @error('produkTujuanId') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                </div>
                            @endif

                            @if($keputusan_akhir === 'gagal_dikembalikan')
                                <div>
                                    <label class="block text-xs font-medium text-ink mb-1.5">Metode Pengambilan</label>
                                    <select wire:model="metode_pengambilan"
                                        class="h-10 w-full rounded-[10px] border border-hairline bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                        <option value="">Pilih Metode Pengambilan</option>
                                        <option value="ambil_sendiri">Ambil di Kantor</option>
                                        <option value="diantar_kolektor">Diantar Kolektor</option>
                                    </select>
                                    @error('metode_pengambilan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                                </div>
                            @endif

                            <div>
                                <label class="block text-xs font-medium text-ink mb-1.5">Catatan Admin</label>
                                <textarea wire:model="catatan_admin" rows="3"
                                    class="w-full rounded-[10px] border border-hairline bg-surface p-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    placeholder="Catatan admin..."></textarea>
                                @error('catatan_admin') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex justify-end gap-2.5 pt-3 border-t border-hairline">
                                <button type="button" wire:click="$set('showDetail', false)"
                                    class="h-[38px] rounded-[10px] border border-hairline bg-surface px-4 text-xs font-medium text-ink transition hover:bg-surface-2 cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="h-[38px] rounded-[10px] bg-primary hover:bg-primary-hover px-5 text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer">
                                    Simpan Keputusan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
