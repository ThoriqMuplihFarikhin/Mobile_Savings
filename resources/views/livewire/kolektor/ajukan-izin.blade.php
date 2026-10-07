<div class="space-y-6">
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 border border-emerald-100 dark:bg-emerald-950/40 dark:border-emerald-900/60 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-2xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 border border-rose-100 dark:bg-rose-950/40 dark:border-rose-900/60 dark:text-rose-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-3xl bg-white p-5 shadow-xs border border-zinc-100 dark:bg-zinc-800 dark:border-zinc-700/60">
        <h2 class="text-base font-bold text-zinc-900 dark:text-white">Pengajuan Baru</h2>
        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Ajukan izin atau cuti, menunggu persetujuan admin.</p>

        <form wire:submit="ajukan" class="mt-4 space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Tanggal Mulai</label>
                    <x-ui.tanggal wire:model="tanggalMulai" :min="now()->toDateString()"
                           class="w-full rounded-2xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white" />
                    @error('tanggalMulai') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Tanggal Selesai</label>
                    <x-ui.tanggal wire:model="tanggalSelesai" :min="$tanggalMulai ?: now()->toDateString()"
                           class="w-full rounded-2xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white" />
                    @error('tanggalSelesai') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Alasan</label>
                <textarea wire:model="alasan" rows="3" placeholder="Contoh: acara keluarga di luar kota"
                          class="w-full rounded-2xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-900 dark:text-white"></textarea>
                @error('alasan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="ajukan"
                    class="w-full rounded-full bg-indigo-800 px-4 py-3 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-50">
                Kirim Pengajuan
            </button>
        </form>
    </div>

    <div class="rounded-3xl bg-white p-5 shadow-xs border border-zinc-100 dark:bg-zinc-800 dark:border-zinc-700/60">
        <h2 class="text-base font-bold text-zinc-900 dark:text-white">Riwayat Pengajuan</h2>

        <div class="mt-3 space-y-3">
            @forelse($daftarIzin as $izin)
                <div class="rounded-2xl border border-zinc-100 bg-zinc-50/60 p-4 dark:border-zinc-700/60 dark:bg-zinc-900/40">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->translatedFormat('d M Y') }}
                                - {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->translatedFormat('d M Y') }}
                            </p>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $izin->alasan }}</p>
                            @if ($izin->catatan_admin)
                                <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">Catatan admin: {{ $izin->catatan_admin }}</p>
                            @endif
                        </div>
                        @if ($izin->status === 'pending')
                            <span class="shrink-0 rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-950/60 dark:text-amber-400">Menunggu</span>
                        @elseif ($izin->status === 'disetujui')
                            <span class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">Disetujui</span>
                        @else
                            <span class="shrink-0 rounded-full bg-rose-100 px-2.5 py-0.5 text-[11px] font-semibold text-rose-700 dark:bg-rose-950/60 dark:text-rose-400">Ditolak</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Belum ada pengajuan izin.</p>
            @endforelse
        </div>
    </div>
</div>
