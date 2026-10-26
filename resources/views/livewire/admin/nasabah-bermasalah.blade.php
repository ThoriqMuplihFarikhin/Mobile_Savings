<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Nasabah Bermasalah</h1>
        <p class="mt-1 text-sm text-mute">Daftar nasabah dengan kepesertaan paket yang perlu review.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-info-soft px-4 py-3 text-sm text-info">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-danger-soft px-4 py-3 text-sm text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4">
        <input type="text" wire:model.live="search" placeholder="Cari nama nasabah atau produk..."
            class="h-10 w-full rounded-md border border-hairline bg-white px-4 text-sm text-ink placeholder-[#737373] focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-[inset_0_0_0_1px_#e5e5e5]">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-inset">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nasabah</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Produk</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tunggakan</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Seharusnya</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Aktual</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Status</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($kepesertaan as $item)
                        <tr class="transition hover:bg-inset">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</div>
                                <div class="text-xs text-mute">{{ $item->nasabah->no_hp ?? '— (offline)' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-body">{{ $item->produk->nama ?? '-' }}</td>
                            <td class="px-4 py-3 tabular-nums text-sm font-medium text-danger">{{ $item->tunggakan }} hari</td>
                            <td class="px-4 py-3 tabular-nums text-sm text-body">Rp {{ number_format($item->total_seharusnya_terkumpul, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 tabular-nums text-sm text-body">Rp {{ number_format($item->total_aktual_terkumpul, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-danger-soft px-2.5 py-0.5 text-xs text-danger">Perlu Review</span>
                            </td>
                            <td class="px-4 py-3">
                                <button wire:click="selectKepesertaan({{ $item->id }})"
                                    class="inline-flex items-center gap-1 rounded-full bg-accent px-3 py-1.5 text-xs font-medium text-accent-foreground transition hover:opacity-90">
                                    Review
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <p class="text-sm text-mute">Tidak ada nasabah bermasalah.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-hairline px-4 py-3">{{ $kepesertaan->links() }}</div>
    </div>

    @if($showDetail && $selectedKepesertaan)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="mx-4 w-full max-w-lg rounded-lg bg-white p-6 shadow-[0_8px_24px_rgb(0_0_0/0.12)]">
                <h3 class="text-lg font-semibold text-ink">Detail Kepesertaan Paket</h3>

                <div class="mt-4 space-y-3 rounded-lg bg-inset p-4 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <div class="flex justify-between text-sm">
                        <span class="text-mute">Nasabah</span>
                        <span class="font-medium text-ink">{{ $selectedKepesertaan->nasabah->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-mute">Produk</span>
                        <span class="text-body">{{ $selectedKepesertaan->produk->nama ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-mute">Tanggal Mulai</span>
                        <span class="text-body">{{ $selectedKepesertaan->tanggal_mulai_ikut->translatedFormat('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-mute">Seharusnya Terkumpul</span>
                        <span class="tabular-nums text-body">Rp {{ number_format($selectedKepesertaan->total_seharusnya_terkumpul, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-mute">Aktual Terkumpul</span>
                        <span class="tabular-nums text-body">Rp {{ number_format($selectedKepesertaan->total_aktual_terkumpul, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-hairline pt-3 text-sm font-medium">
                        <span class="text-mute">Tunggakan</span>
                        <span class="tabular-nums text-danger">{{ $selectedKepesertaan->tunggakan }} hari</span>
                    </div>
                </div>

                <form wire:submit="updateKeputusan" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Keputusan Akhir</label>
                        <select wire:model="keputusan_akhir"
                            class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
                            <option value="">Pilih Keputusan</option>
                            <option value="lanjut">Lanjut</option>
                            <option value="gagal_dikembalikan">Gagal - Dikembalikan</option>
                            <option value="gagal_dialihkan">Gagal - Dialihkan</option>
                        </select>
                        @error('keputusan_akhir') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    @if($keputusan_akhir === 'lanjut')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Tunda Hingga (maks. 90 hari)</label>
                            <x-ui.tanggal wire:model="ditunda_hingga" :min="now()->toDateString()"
                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                            @error('ditunda_hingga') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    @if($keputusan_akhir === 'gagal_dialihkan')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Produk Tujuan</label>
                            <select wire:model="produkTujuanId"
                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
                                <option value="">Pilih Produk</option>
                                @foreach($produkTujuan as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->tipe }})</option>
                                @endforeach
                            </select>
                            @error('produkTujuanId') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-ink">Catatan Admin</label>
                        <textarea wire:model="catatan_admin" rows="3"
                            class="w-full rounded-md border border-hairline bg-white px-3 py-2.5 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                            placeholder="Catatan..."></textarea>
                    </div>
                    @if($keputusan_akhir === 'gagal_dikembalikan')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink">Metode Pengambilan</label>
                            <select wire:model="metode_pengambilan"
                                class="h-10 w-full rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
                                <option value="">Pilih Metode</option>
                                <option value="ambil_sendiri">Ambil Sendiri</option>
                                <option value="diantar_kolektor">Diantar Kolektor</option>
                            </select>
                            @error('metode_pengambilan') <p class="mt-1.5 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showDetail', false)"
                            class="rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-md bg-accent px-4 py-2 text-sm font-medium text-accent-foreground transition hover:opacity-90">
                            Simpan Keputusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>