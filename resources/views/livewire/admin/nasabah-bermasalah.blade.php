<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Nasabah"
        title="Nasabah Bermasalah"
        subtitle="Daftar nasabah dengan kepesertaan paket yang perlu review."
    />

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-primary/20 bg-primary-soft px-4 py-3 text-sm text-on-primary-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-danger/20 bg-danger-soft px-4 py-3 text-sm text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Toolbar --}}
    <x-admin.toolbar>
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--color-admin-mute)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="7" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4-4" />
            </svg>
            <input type="text" wire:model.live="search" placeholder="Cari nama nasabah atau produk..."
                class="h-[38px] w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] pl-10 pr-4 text-sm text-[var(--color-admin-text)] placeholder-[var(--color-admin-mute)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]" />
        </div>
    </x-admin.toolbar>

    {{-- Tabel Desktop --}}
    <div class="hidden md:block overflow-hidden rounded-[14px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-[var(--color-admin-border)] bg-[var(--color-surface-2)]">
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Nasabah</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Produk</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Tunggakan (hari)</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Seharusnya</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Aktual</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-admin-border)]">
                    @forelse($kepesertaan as $item)
                        <tr class="transition hover:bg-[var(--color-surface-2)]">
                            <td class="px-4 py-3">
                                <div class="font-medium text-[var(--color-admin-text)]">{{ $item->nasabah->name ?? '-' }}</div>
                                <div class="text-xs text-[var(--color-admin-mute)]">{{ $item->nasabah->no_hp ?? '— (offline)' }}</div>
                            </td>
                            <td class="px-4 py-3 text-[var(--color-admin-text)]">{{ $item->produk->nama ?? '-' }}</td>
                            <td class="px-4 py-3 tabular-nums font-medium text-red-600 dark:text-red-400">{{ $item->tunggakan }} hari</td>
                            <td class="px-4 py-3 text-right tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($item->total_seharusnya_terkumpul, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($item->total_aktual_terkumpul, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <x-admin.status-chip status="Perlu Review" />
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" wire:click="selectKepesertaan({{ $item->id }})"
                                    class="inline-flex h-8 items-center rounded-[8px] bg-[var(--color-accent)] px-3 text-xs font-medium text-white transition hover:opacity-90">
                                    Review
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-0">
                                <x-admin.empty-state
                                    title="Tidak ada nasabah bermasalah"
                                    description="Semua kepesertaan paket nasabah berjalan lancar tanpa tunggakan review."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($kepesertaan->hasPages())
            <div class="border-t border-[var(--color-admin-border)] px-4 py-3">
                {{ $kepesertaan->links() }}
            </div>
        @endif
    </div>

    {{-- Fallback Kartu Mobile --}}
    <div data-test="kartu-tabel" class="space-y-3 md:hidden">
        @forelse($kepesertaan as $item)
            <div class="rounded-[14px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] p-4 shadow-xs space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-medium text-[var(--color-admin-text)]">{{ $item->nasabah->name ?? '-' }}</div>
                        <div class="text-xs text-[var(--color-admin-mute)]">{{ $item->nasabah->no_hp ?? '— (offline)' }}</div>
                    </div>
                    <x-admin.status-chip status="Perlu Review" />
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs border-t border-b border-[var(--color-admin-border)] py-2">
                    <div>
                        <span class="text-[var(--color-admin-mute)]">Produk:</span>
                        <div class="font-medium text-[var(--color-admin-text)]">{{ $item->produk->nama ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="text-[var(--color-admin-mute)]">Tunggakan:</span>
                        <div class="font-medium text-red-600 dark:text-red-400 tabular-nums">{{ $item->tunggakan }} hari</div>
                    </div>
                    <div>
                        <span class="text-[var(--color-admin-mute)]">Seharusnya:</span>
                        <div class="font-medium tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($item->total_seharusnya_terkumpul, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <span class="text-[var(--color-admin-mute)]">Aktual:</span>
                        <div class="font-medium tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($item->total_aktual_terkumpul, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="flex justify-end pt-1">
                    <button type="button" wire:click="selectKepesertaan({{ $item->id }})"
                        class="inline-flex h-8 items-center rounded-[8px] bg-[var(--color-accent)] px-3 text-xs font-medium text-white transition hover:opacity-90">
                        Review
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-[14px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] p-6">
                <x-admin.empty-state
                    title="Tidak ada nasabah bermasalah"
                    description="Semua kepesertaan paket nasabah berjalan lancar tanpa tunggakan review."
                />
            </div>
        @endforelse
        @if($kepesertaan->hasPages())
            <div class="pt-2">
                {{ $kepesertaan->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Review Kepesertaan --}}
    @if($showDetail && $selectedKepesertaan)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modal-detail-kepesertaan-title">
            <div wire:click="$set('showDetail', false)" class="fixed inset-0 bg-black/60 transition-opacity" aria-hidden="true"></div>
            <div class="relative w-full max-w-lg rounded-[16px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-[var(--color-admin-border)]">
                    <h3 id="modal-detail-kepesertaan-title" class="text-base font-semibold text-[var(--color-admin-text)]">Detail Kepesertaan Paket</h3>
                    <button type="button" wire:click="$set('showDetail', false)"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-[var(--color-admin-mute)] transition hover:bg-[var(--color-surface-2)] hover:text-[var(--color-admin-text)]"
                        aria-label="Tutup modal">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-2.5 rounded-[12px] border border-[var(--color-admin-border)] bg-[var(--color-surface-2)] p-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-[var(--color-admin-mute)]">Nasabah</span>
                        <span class="font-medium text-[var(--color-admin-text)]">{{ $selectedKepesertaan->nasabah->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[var(--color-admin-mute)]">Produk</span>
                        <span class="text-[var(--color-admin-text)]">{{ $selectedKepesertaan->produk->nama ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[var(--color-admin-mute)]">Tanggal Mulai</span>
                        <span class="text-[var(--color-admin-text)]">{{ $selectedKepesertaan->tanggal_mulai_ikut->translatedFormat('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[var(--color-admin-mute)]">Seharusnya Terkumpul</span>
                        <span class="tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($selectedKepesertaan->total_seharusnya_terkumpul, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[var(--color-admin-mute)]">Aktual Terkumpul</span>
                        <span class="tabular-nums text-[var(--color-admin-text)]">Rp {{ number_format($selectedKepesertaan->total_aktual_terkumpul, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-[var(--color-admin-border)] pt-2 font-medium">
                        <span class="text-[var(--color-admin-mute)]">Tunggakan</span>
                        <span class="tabular-nums text-red-600 dark:text-red-400">{{ $selectedKepesertaan->tunggakan }} hari</span>
                    </div>
                </div>

                <form wire:submit="updateKeputusan" class="mt-5 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Keputusan Akhir</label>
                        <select wire:model="keputusan_akhir"
                            class="h-[38px] w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-sm text-[var(--color-admin-text)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]">
                            <option value="">Pilih Keputusan</option>
                            <option value="lanjut">Lanjut</option>
                            <option value="gagal_dikembalikan">Gagal - Dikembalikan</option>
                            <option value="gagal_dialihkan">Gagal - Dialihkan</option>
                        </select>
                        @error('keputusan_akhir') <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    @if($keputusan_akhir === 'lanjut')
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Tunda Hingga (maks. 90 hari)</label>
                            <x-ui.tanggal wire:model="ditunda_hingga" :min="now()->toDateString()"
                                class="h-[38px] w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-sm text-[var(--color-admin-text)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]" />
                            @error('ditunda_hingga') <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    @if($keputusan_akhir === 'gagal_dialihkan')
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Produk Tujuan</label>
                            <select wire:model="produkTujuanId"
                                class="h-[38px] w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-sm text-[var(--color-admin-text)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]">
                                <option value="">Pilih Produk</option>
                                @foreach($produkTujuan as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->tipe }})</option>
                                @endforeach
                            </select>
                            @error('produkTujuanId') <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Catatan Admin</label>
                        <textarea wire:model="catatan_admin" rows="3"
                            class="w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 py-2 text-sm text-[var(--color-admin-text)] placeholder-[var(--color-admin-mute)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]"
                            placeholder="Catatan..."></textarea>
                    </div>
                    @if($keputusan_akhir === 'gagal_dikembalikan')
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-[var(--color-admin-mute)]">Metode Pengambilan</label>
                            <select wire:model="metode_pengambilan"
                                class="h-[38px] w-full rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-3 text-sm text-[var(--color-admin-text)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-1 focus:ring-[var(--color-accent)]">
                                <option value="">Pilih Metode</option>
                                <option value="ambil_sendiri">Ambil Sendiri</option>
                                <option value="diantar_kolektor">Diantar Kolektor</option>
                            </select>
                            @error('metode_pengambilan') <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div class="flex justify-end gap-3 pt-3 border-t border-[var(--color-admin-border)]">
                        <button type="button" wire:click="$set('showDetail', false)"
                            class="h-[38px] rounded-[10px] border border-[var(--color-admin-border)] bg-[var(--color-surface)] px-4 text-sm font-medium text-[var(--color-admin-text)] transition hover:bg-[var(--color-surface-2)]">
                            Batal
                        </button>
                        <button type="submit"
                            class="h-[38px] rounded-[10px] bg-[var(--color-accent)] px-4 text-sm font-medium text-white transition hover:opacity-90">
                            Simpan Keputusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>