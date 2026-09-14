<div class="mx-auto max-w-3xl space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Pusat Bantuan & Komplain</h1>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Ajukan keluhan atau pertanyaan terkait layanan tabungan Anda.</p>
        </div>
        <button wire:click="toggleForm"
            class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 px-4 py-2.5 text-xs font-bold text-white shadow-lg shadow-indigo-500/20 transition shrink-0">
            <flux:icon.plus class="size-4 text-white" />
            <span>Buat Tiket</span>
        </button>
    </div>

    {{-- Flash Message --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 p-4 text-xs font-semibold text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600 dark:text-emerald-400" />
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Form Tiket Komplain --}}
    @if($showForm)
        <div class="rounded-3xl bg-white dark:bg-zinc-800 p-6 shadow-xs border border-zinc-200/80 dark:border-zinc-700/80 transition-all">
            <div class="mb-4 flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-700/60">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-white">Form Pengajuan Tiket</h3>
                <button type="button" wire:click="toggleForm" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-xs font-semibold">Tutup</button>
            </div>

            <form wire:submit="submit" class="space-y-4">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Kategori Kendala</label>
                    <select wire:model="kategori"
                        class="w-full rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 px-4 py-3 text-xs font-bold text-zinc-900 dark:text-white focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="saldo">Permasalahan Saldo</option>
                        <option value="barang_paket">Pencairan Paket / Barang</option>
                        <option value="penarikan">Penarikan Tabungan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Transaksi Terkait <span class="text-zinc-400 font-normal lowercase">(opsional)</span></label>
                    <select wire:model="transaksiTerkaitId"
                        class="w-full rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 px-4 py-3 text-xs font-bold text-zinc-900 dark:text-white focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">Tidak ada transaksi terkait</option>
                        @foreach($riwayatTransaksi as $trx)
                            <option value="{{ $trx->id }}">
                                {{ $trx->tanggal_transaksi->translatedFormat('d M Y') }} — {{ $trx->produk->nama ?? '-' }} — Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Deskripsi Detail</label>
                    <textarea wire:model="deskripsi" rows="4"
                        class="w-full rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 p-4 text-xs font-semibold text-zinc-900 dark:text-white focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        placeholder="Jelaskan kendala Anda secara rinci..."></textarea>
                    @error('deskripsi') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" wire:click="toggleForm"
                        class="w-1/3 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-3 text-center text-xs font-bold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="w-2/3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 py-3 text-center text-xs font-bold text-white shadow-lg shadow-indigo-500/20 transition">
                        Kirim Tiket
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Lista Tiket Cards --}}
    <div class="space-y-3">
        @forelse($komplains as $item)
            @php
                $kategoriLabel = match($item->kategori) {
                    'saldo'       => 'Saldo',
                    'barang_paket'=> 'Barang Paket',
                    'penarikan'   => 'Penarikan',
                    default       => 'Lainnya'
                };
                $statusBadge = match($item->status) {
                    'baru'     => ['label' => 'Baru', 'class' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800/60'],
                    'diproses' => ['label' => 'Diproses', 'class' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800/60'],
                    default    => ['label' => 'Selesai', 'class' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/60'],
                };
            @endphp
            <div class="rounded-3xl bg-white dark:bg-zinc-800/90 p-5 shadow-xs border border-zinc-200/80 dark:border-zinc-700/80 transition">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-lg bg-zinc-100 dark:bg-zinc-700/60 px-2.5 py-0.5 text-[10px] font-bold text-zinc-700 dark:text-zinc-300">
                                {{ $kategoriLabel }}
                            </span>
                            <span class="text-[11px] font-medium text-zinc-400 dark:text-zinc-500">
                                {{ $item->tanggal_dibuat->translatedFormat('d M Y, H:i') }}
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 pt-1 leading-relaxed">
                            {{ $item->deskripsi }}
                        </p>
                    </div>
                    <span class="shrink-0 rounded-full px-3 py-1 text-[10px] font-bold border {{ $statusBadge['class'] }}">
                        {{ $statusBadge['label'] }}
                    </span>
                </div>

                @if($item->catatan_penyelesaian)
                    <div class="mt-4 rounded-2xl bg-zinc-50 dark:bg-zinc-900/60 p-3.5 border border-zinc-200/60 dark:border-zinc-700/40">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                            <flux:icon.check-circle class="size-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                            <span>Tanggapan Admin:</span>
                        </div>
                        <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-300 pl-5 leading-relaxed">
                            {{ $item->catatan_penyelesaian }}
                        </p>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-3xl bg-white dark:bg-zinc-800 p-10 text-center border border-zinc-200/80 dark:border-zinc-700/80 shadow-xs">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-700/60 text-zinc-400">
                    <flux:icon.chat-bubble-left-right class="size-7 text-zinc-400" />
                </div>
                <h3 class="mt-4 text-sm font-bold text-zinc-900 dark:text-white">Belum Ada Tiket Komplain</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Jika Anda memiliki kendala, jangan ragu untuk mengajukan tiket di sini.</p>
            </div>
        @endforelse
    </div>

    @if($komplains->hasPages())
        <div class="mt-4">{{ $komplains->links() }}</div>
    @endif
</div>

