<div class="space-y-5">
    {{-- Header Halaman (.ph) --}}
    <x-admin.page-header
        breadcrumb="Nasabah"
        title="Nasabah Bermasalah"
        subtitle="Daftar nasabah dengan kepesertaan paket yang perlu review."
    />

    {{-- Banner Flash Notifikasi --}}
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] bg-primary-soft px-4 py-3 text-xs font-medium text-on-primary-soft border border-primary/20">
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-[10px] bg-danger-soft px-4 py-3 text-xs font-medium text-danger border border-danger/20">
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Kartu Utama Tabel (.card) --}}
    <div class="rounded-[14px] border border-hairline bg-surface shadow-xs overflow-hidden">
        {{-- Toolbar Pencarian (.tl .sr) --}}
        <div class="p-3 sm:px-4 sm:py-3 border-b border-hairline flex flex-wrap gap-2 items-center">
            <div class="relative w-full sm:max-w-[340px]">
                <svg class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-mute pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4" />
                </svg>
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari nama nasabah atau produk..."
                    class="h-9 w-full rounded-[10px] border border-hairline bg-surface-2 pl-9 pr-3 text-xs text-ink placeholder:text-mute focus:border-primary focus:bg-surface focus:outline-none focus:ring-1 focus:ring-primary transition-colors"
                />
            </div>
        </div>

        {{-- Tabel Desktop (.tw) --}}
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-hairline bg-surface-2 text-mute text-xs font-medium uppercase tracking-[0.04em]">
                        <th class="px-4 py-2.5 whitespace-nowrap">NASABAH</th>
                        <th class="px-4 py-2.5 whitespace-nowrap">PRODUK</th>
                        <th class="px-4 py-2.5 whitespace-nowrap">TUNGGAKAN (HARI)</th>
                        <th class="px-4 py-2.5 whitespace-nowrap text-right">SEHARUSNYA</th>
                        <th class="px-4 py-2.5 whitespace-nowrap text-right">AKTUAL</th>
                        <th class="px-4 py-2.5 whitespace-nowrap">STATUS</th>
                        <th class="px-4 py-2.5 whitespace-nowrap text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($kepesertaan as $item)
                        <tr class="transition hover:bg-surface-2">
                            <td class="px-4 py-3 font-medium text-ink whitespace-nowrap">
                                {{ $item->nasabah->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-body whitespace-nowrap">
                                {{ $item->produk->nama ?? '-' }}
                            </td>
                            <td class="px-4 py-3 tabular-nums text-body whitespace-nowrap">
                                {{ (int) $item->tunggakan }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-ink whitespace-nowrap font-medium">
                                Rp {{ number_format($item->total_seharusnya_terkumpul, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-ink whitespace-nowrap font-medium">
                                Rp {{ number_format($item->total_aktual_terkumpul, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <x-admin.status-chip status="Perlu Review" />
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button
                                    type="button"
                                    wire:click="selectKepesertaan({{ $item->id }})"
                                    class="inline-flex h-[32px] items-center justify-center rounded-[8px] bg-primary hover:bg-primary-hover px-3.5 text-xs font-medium text-white shadow-xs transition active:scale-95 cursor-pointer"
                                >
                                    Tinjau
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-xs text-mute">
                                <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-xl bg-surface-2 text-mute">
                                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M22 12h-6l-2 3h-4l-2-3H2M5 5h14l3 7v6a2 2 0 01-2 2H4a2 2 0 01-2-2v-6z" />
                                    </svg>
                                </div>
                                <p class="font-medium text-ink">Tidak ada nasabah bermasalah.</p>
                                <p class="mt-0.5 text-xs text-mute">Semua kepesertaan paket nasabah berjalan lancar tanpa tunggakan review.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View (md:hidden) --}}
        <div class="divide-y divide-hairline md:hidden">
            @forelse($kepesertaan as $item)
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-semibold text-ink text-sm">{{ $item->nasabah->name ?? '-' }}</div>
                            <div class="text-xs text-mute mt-0.5">{{ $item->produk->nama ?? '-' }}</div>
                        </div>
                        <x-admin.status-chip status="Perlu Review" />
                    </div>

                    <div class="grid grid-cols-2 gap-2 rounded-[10px] bg-surface-2/60 p-2.5 text-xs">
                        <div>
                            <span class="text-mute block text-[11px]">Tunggakan</span>
                            <span class="font-semibold text-danger tabular-nums">{{ (int) $item->tunggakan }} hari</span>
                        </div>
                        <div>
                            <span class="text-mute block text-[11px]">Seharusnya</span>
                            <span class="font-medium text-ink tabular-nums">Rp {{ number_format($item->total_seharusnya_terkumpul, 0, ',', '.') }}</span>
                        </div>
                        <div class="col-span-2 pt-1 border-t border-hairline/60 flex justify-between">
                            <span class="text-mute text-[11px]">Aktual Terkumpul</span>
                            <span class="font-semibold text-ink tabular-nums">Rp {{ number_format($item->total_aktual_terkumpul, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex justify-end pt-1">
                        <button
                            type="button"
                            wire:click="selectKepesertaan({{ $item->id }})"
                            class="w-full inline-flex h-9 items-center justify-center rounded-[8px] bg-primary hover:bg-primary-hover px-4 text-xs font-semibold text-white shadow-xs transition active:scale-95 cursor-pointer"
                        >
                            Tinjau Kepesertaan
                        </button>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-xs text-mute px-4">
                    <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-xl bg-surface-2 text-mute">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M22 12h-6l-2 3h-4l-2-3H2M5 5h14l3 7v6a2 2 0 01-2 2H4a2 2 0 01-2-2v-6z" />
                        </svg>
                    </div>
                    <p class="font-medium text-ink">Tidak ada nasabah bermasalah.</p>
                    <p class="mt-0.5 text-xs text-mute">Semua kepesertaan paket nasabah berjalan lancar tanpa tunggakan review.</p>
                </div>
            @endforelse
        </div>

        {{-- Footer (.ft) --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-hairline px-4 py-3 text-xs text-mute">
            <span>Menampilkan {{ $kepesertaan->count() }} dari {{ $kepesertaan->total() }} data</span>
            @if($kepesertaan->hasPages())
                {{ $kepesertaan->links() }}
            @else
                <span>Halaman {{ $kepesertaan->currentPage() }} dari {{ max(1, $kepesertaan->lastPage()) }}</span>
            @endif
        </div>
    </div>

    {{-- Drawer Detail Kepesertaan Paket (Matching drw in HTML prototype) --}}
    @if($showDetail && $selectedKepesertaan)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-on:keydown.escape.window="$wire.set('showDetail', false)"
            class="fixed inset-0 z-50 overflow-hidden flex justify-end"
            role="dialog"
            aria-modal="true"
            aria-labelledby="drawer-kepesertaan-title"
        >
            {{-- Scrim / Backdrop --}}
            <div
                x-show="show"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                wire:click="$set('showDetail', false)"
                class="fixed inset-0 bg-black/45 transition-opacity cursor-pointer"
                aria-hidden="true"
            ></div>

            {{-- Drawer Panel (.dw) --}}
            <div
                x-show="show"
                x-transition:enter="transform transition ease-out duration-250"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="relative z-10 flex h-full w-full max-w-[560px] flex-col border-l border-hairline bg-surface shadow-2xl transition-all"
            >
                {{-- Header Drawer (.dh) --}}
                <div class="flex items-center justify-between border-b border-hairline px-5 py-4 shrink-0 bg-surface">
                    <h3 id="drawer-kepesertaan-title" class="text-base font-semibold text-ink">
                        Detail Kepesertaan Paket
                    </h3>
                    <button
                        type="button"
                        wire:click="$set('showDetail', false)"
                        class="flex size-8 items-center justify-center rounded-[10px] border border-hairline bg-surface-2 text-mute transition hover:bg-hairline hover:text-ink active:scale-95 cursor-pointer"
                        aria-label="Tutup panel"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Content Drawer (.db) --}}
                <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-6 bg-surface">
                    {{-- Section 1: Kepesertaan (.sc) --}}
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-[0.05em] text-mute mb-3">Kepesertaan</h4>
                        <div class="divide-y divide-dashed divide-hairline">
                            <div class="flex items-center justify-between gap-3 py-2.5 text-xs sm:text-sm">
                                <span class="text-mute">Nasabah</span>
                                <b class="font-medium text-ink text-right">{{ $selectedKepesertaan->nasabah->name ?? '-' }}</b>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2.5 text-xs sm:text-sm">
                                <span class="text-mute">Produk</span>
                                <b class="font-medium text-ink text-right">{{ $selectedKepesertaan->produk->nama ?? '-' }}</b>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2.5 text-xs sm:text-sm">
                                <span class="text-mute">Tanggal mulai</span>
                                <b class="font-medium text-ink text-right">{{ $selectedKepesertaan->tanggal_mulai_ikut ? $selectedKepesertaan->tanggal_mulai_ikut->translatedFormat('d M Y') : '-' }}</b>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2.5 text-xs sm:text-sm">
                                <span class="text-mute">Seharusnya terkumpul</span>
                                <b class="font-medium tabular-nums text-ink text-right">Rp {{ number_format($selectedKepesertaan->total_seharusnya_terkumpul, 0, ',', '.') }}</b>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2.5 text-xs sm:text-sm">
                                <span class="text-mute">Aktual terkumpul</span>
                                <b class="font-medium tabular-nums text-ink text-right">Rp {{ number_format($selectedKepesertaan->total_aktual_terkumpul, 0, ',', '.') }}</b>
                            </div>
                            <div class="flex items-center justify-between gap-3 py-2.5 text-xs sm:text-sm">
                                <span class="text-mute">Tunggakan</span>
                                <b class="font-semibold tabular-nums text-danger text-right">{{ (int) $selectedKepesertaan->tunggakan }} hari</b>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Keputusan Akhir (.sc) --}}
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-[0.05em] text-mute mb-3">Keputusan Akhir</h4>
                        <form wire:submit="updateKeputusan" class="space-y-4">
                            <div>
                                <label for="f-keputusan" class="block text-xs font-medium text-ink mb-1.5">Keputusan</label>
                                <select
                                    id="f-keputusan"
                                    wire:model.live="keputusan_akhir"
                                    class="h-10 w-full rounded-[10px] border border-hairline bg-surface-2 px-3 text-xs sm:text-sm text-ink focus:border-primary focus:bg-surface focus:outline-none focus:ring-1 focus:ring-primary transition-colors cursor-pointer"
                                >
                                    <option value="">Pilih Keputusan</option>
                                    <option value="lanjut">Lanjut (Tunda hingga maks. 90 hari)</option>
                                    <option value="gagal_dikembalikan">Gagal - Dikembalikan</option>
                                    <option value="gagal_dialihkan">Gagal - Dialihkan</option>
                                </select>
                                @error('keputusan_akhir')
                                    <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            @if($keputusan_akhir === 'lanjut')
                                <div>
                                    <label for="f-ditunda" class="block text-xs font-medium text-ink mb-1.5">Tunda Hingga (maks. 90 hari)</label>
                                    <x-ui.tanggal
                                        id="f-ditunda"
                                        wire:model="ditunda_hingga"
                                        :min="now()->toDateString()"
                                        :max="now()->addDays(90)->toDateString()"
                                        class="h-10 w-full rounded-[10px] border border-hairline bg-surface-2 px-3 text-xs sm:text-sm text-ink focus:border-primary focus:bg-surface focus:outline-none focus:ring-1 focus:ring-primary transition-colors"
                                    />
                                    @error('ditunda_hingga')
                                        <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            @if($keputusan_akhir === 'gagal_dialihkan')
                                <div>
                                    <label for="f-produk-tujuan" class="block text-xs font-medium text-ink mb-1.5">Produk Tujuan</label>
                                    <select
                                        id="f-produk-tujuan"
                                        wire:model="produkTujuanId"
                                        class="h-10 w-full rounded-[10px] border border-hairline bg-surface-2 px-3 text-xs sm:text-sm text-ink focus:border-primary focus:bg-surface focus:outline-none focus:ring-1 focus:ring-primary transition-colors cursor-pointer"
                                    >
                                        <option value="">Pilih Produk Tujuan</option>
                                        @foreach($produkTujuan as $p)
                                            <option value="{{ $p->id }}">{{ $p->nama }} ({{ ucfirst($p->tipe) }})</option>
                                        @endforeach
                                    </select>
                                    @error('produkTujuanId')
                                        <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            @if($keputusan_akhir === 'gagal_dikembalikan')
                                <div>
                                    <label for="f-metode" class="block text-xs font-medium text-ink mb-1.5">Metode Pengambilan</label>
                                    <select
                                        id="f-metode"
                                        wire:model="metode_pengambilan"
                                        class="h-10 w-full rounded-[10px] border border-hairline bg-surface-2 px-3 text-xs sm:text-sm text-ink focus:border-primary focus:bg-surface focus:outline-none focus:ring-1 focus:ring-primary transition-colors cursor-pointer"
                                    >
                                        <option value="">Pilih Metode Pengambilan</option>
                                        <option value="ambil_sendiri">Ambil di Kantor</option>
                                        <option value="diantar_kolektor">Diantar Kolektor</option>
                                    </select>
                                    @error('metode_pengambilan')
                                        <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            <div>
                                <label for="f-catatan" class="block text-xs font-medium text-ink mb-1.5">Catatan Admin</label>
                                <textarea
                                    id="f-catatan"
                                    wire:model="catatan_admin"
                                    rows="3"
                                    class="w-full rounded-[10px] border border-hairline bg-surface-2 p-3 text-xs sm:text-sm text-ink placeholder:text-mute focus:border-primary focus:bg-surface focus:outline-none focus:ring-1 focus:ring-primary transition-colors resize-none"
                                    placeholder="Catatan..."
                                ></textarea>
                                @error('catatan_admin')
                                    <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-hairline">
                                <button
                                    type="button"
                                    wire:click="$set('showDetail', false)"
                                    class="h-[38px] rounded-[10px] border border-hairline bg-surface px-4 text-xs font-medium text-ink transition hover:bg-surface-2 active:scale-95 cursor-pointer shadow-xs"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    class="h-[38px] rounded-[10px] bg-primary hover:bg-primary-hover px-5 text-xs font-medium text-white shadow-xs transition active:scale-95 cursor-pointer"
                                >
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
