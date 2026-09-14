<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Riwayat Penarikan</h1>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Daftar seluruh pengajuan penarikan saldo Anda.</p>
        </div>
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 shadow-2xs border border-indigo-100 dark:border-indigo-900/50">
            <flux:icon.arrow-up-tray class="size-5" />
        </div>
    </div>

    {{-- Withdrawal Cards --}}
    <div class="space-y-3">
        @forelse($penarikan as $item)
            @php
                $statusBadge = match($item->status) {
                    'pending'  => ['label' => 'Menunggu', 'class' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800/60'],
                    'approved' => ['label' => 'Disetujui', 'class' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800/60'],
                    'selesai'  => ['label' => 'Selesai', 'class' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/60'],
                    default    => ['label' => 'Ditolak', 'class' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800/60'],
                };
            @endphp
            <div class="rounded-3xl bg-white dark:bg-zinc-800/90 p-5 shadow-xs border border-zinc-200/80 dark:border-zinc-700/80">
                {{-- Header row --}}
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-[11px] font-semibold text-zinc-400 dark:text-zinc-500">
                            {{ $item->created_at->translatedFormat('d M Y, H:i') }}
                        </span>
                        <h3 class="mt-0.5 text-sm font-bold text-zinc-900 dark:text-white">
                            {{ $item->produk->nama ?? '-' }}
                        </h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                            {{ $item->lokasi_pengambilan === 'kantor' ? 'Ambil di Kantor' : 'Via Rumah Kolektor' }}
                        </p>
                    </div>
                    <span class="shrink-0 inline-flex items-center rounded-full px-3 py-1 text-[11px] font-semibold border {{ $statusBadge['class'] }}">
                        {{ $statusBadge['label'] }}
                    </span>
                </div>

                {{-- Amount breakdown --}}
                <div class="mt-4 pt-3.5 border-t border-zinc-100 dark:border-zinc-700/60 grid grid-cols-3 gap-2">
                    <div class="text-center">
                        <p class="text-[10px] uppercase font-bold text-zinc-400 dark:text-zinc-500">Diminta</p>
                        <p class="font-mono text-xs font-bold text-zinc-900 dark:text-white mt-0.5">
                            Rp {{ number_format($item->nominal_diminta, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="text-center border-x border-zinc-100 dark:border-zinc-700/60">
                        <p class="text-[10px] uppercase font-bold text-zinc-400 dark:text-zinc-500">Biaya Admin</p>
                        <p class="font-mono text-xs font-bold text-rose-500 mt-0.5">
                            −Rp {{ number_format($item->nominal_komisi, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] uppercase font-bold text-zinc-400 dark:text-zinc-500">Diterima</p>
                        <p class="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">
                            Rp {{ number_format($item->nominal_diterima, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-3xl bg-white dark:bg-zinc-800 p-10 text-center border border-zinc-200/80 dark:border-zinc-700/80 shadow-xs">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-700/60 text-zinc-400">
                    <flux:icon.arrow-up-tray class="size-7 text-zinc-400" />
                </div>
                <h3 class="mt-4 text-sm font-bold text-zinc-900 dark:text-white">Belum Ada Riwayat Penarikan</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Pengajuan penarikan saldo Anda akan tampil di sini.</p>
            </div>
        @endforelse
    </div>

    @if($penarikan->hasPages())
        <div class="mt-4">{{ $penarikan->links() }}</div>
    @endif
</div>

