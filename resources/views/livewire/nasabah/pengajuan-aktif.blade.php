<div>
@if($pengajuan->isNotEmpty())
    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 p-4 text-xs font-semibold text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600 dark:text-emerald-400" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 rounded-2xl bg-rose-50 dark:bg-rose-950/40 p-4 text-xs font-semibold text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40">
            <flux:icon.exclamation-triangle class="size-5 shrink-0 text-rose-600 dark:text-rose-400" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="rounded-2xl bg-white p-4 shadow-xs border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
        <div class="mb-3 flex items-center justify-between pb-2.5 border-b border-zinc-100 dark:border-zinc-700/60">
            <h3 class="text-xs font-bold text-zinc-900 dark:text-white uppercase tracking-wider">Pengajuan Aktif</h3>
            <a href="{{ route('nasabah.riwayat.index') }}" wire:navigate class="text-[11px] font-semibold text-blue-600 hover:underline dark:text-blue-400">
                Lihat Riwayat
            </a>
        </div>

        <div class="space-y-3">
            @foreach($pengajuan as $item)
                @php
                    $badge = $item->status === 'approved'
                        ? ['label' => 'Disetujui', 'class' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800/60']
                        : ['label' => 'Menunggu', 'class' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800/60'];
                @endphp
                <div class="rounded-2xl border border-zinc-100 dark:border-zinc-700/60 p-3.5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-bold text-zinc-900 dark:text-white">{{ $item->produk->nama ?? '-' }}</h4>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                {{ $item->lokasi_pengambilan === 'kantor' ? 'Ambil di Kantor' : 'Via Rumah Kolektor' }}
                                &middot; diajukan {{ $item->created_at->translatedFormat('d M Y') }}
                            </p>
                        </div>
                        <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-semibold border {{ $badge['class'] }}">
                            {{ $badge['label'] }}
                        </span>
                    </div>

                    <div class="mt-3 flex items-center justify-between gap-3">
                        <p class="font-mono text-sm font-bold text-zinc-900 dark:text-white">
                            Rp {{ number_format($item->nominal_diminta, 0, ',', '.') }}
                        </p>
                        <button type="button"
                                wire:click="batalkan({{ $item->id }})"
                                wire:confirm="Yakin membatalkan pengajuan penarikan ini? Saldo yang sudah dipotong akan dikembalikan."
                                class="rounded-xl border border-rose-200 dark:border-rose-800/60 bg-rose-50 dark:bg-rose-950/40 px-3 py-1.5 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-950/70 transition">
                            Batalkan Pengajuan
                        </button>
                    </div>

                    <div class="mt-2.5">
                        <input type="text" wire:model="alasanBatal" maxlength="255"
                               placeholder="Alasan membatalkan (opsional)"
                               class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 px-3 py-2 text-xs text-zinc-900 dark:text-white placeholder:text-zinc-400 focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-400/20 transition" />
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
</div>
