<div class="mx-auto max-w-4xl space-y-6 pb-6">
    {{-- Header Section --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Daftarkan Nasabah</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Daftarkan nasabah baru di lapangan untuk diverifikasi admin.</p>
        </div>
        <button wire:click="toggleForm"
            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-2xl bg-emerald-600 dark:bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 dark:hover:bg-emerald-600 shadow-sm shadow-emerald-500/20 active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            {{ $showForm ? 'Tutup Form' : 'Daftar Nasabah Baru' }}
        </button>
    </div>

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 shadow-sm">
            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            </div>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 rounded-2xl bg-rose-50 dark:bg-rose-950/40 px-4 py-3 text-sm text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60 shadow-sm">
            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-rose-500/20 text-rose-600 dark:text-rose-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </div>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Form Section (Collapsible) --}}
    @if($showForm)
        <div class="overflow-hidden rounded-3xl bg-white dark:bg-zinc-800 shadow-md border border-zinc-100 dark:border-zinc-700/60">
            <div class="border-b border-zinc-100 dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/50 px-6 py-4 flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-zinc-900 dark:text-white">Form Pendaftaran Nasabah</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Isi data calon nasabah secara lengkap</p>
                </div>
            </div>

            <form wire:submit="submit" class="space-y-5 p-6">
                <label class="flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    <input type="checkbox" wire:model.live="modeOffline"
                        class="h-4 w-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500" />
                    Nasabah tidak memakai aplikasi / tidak punya HP (mode offline)
                </label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Nama Lengkap</label>
                        <input type="text" wire:model="nama" placeholder="Contoh: Budi Santoso"
                            class="h-11 w-full rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 px-4 text-sm text-zinc-900 dark:text-white focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" />
                        @error('nama') <p class="mt-1 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    @if(! $modeOffline)
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">No. HP (WhatsApp)</label>
                        <input type="text" wire:model="noHp" placeholder="08xxxxxxxxxx"
                            class="h-11 w-full rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 px-4 text-sm font-mono text-zinc-900 dark:text-white focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" />
                        @error('noHp') <p class="mt-1 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    @endif
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Alamat Lengkap</label>
                        <textarea wire:model="alamat" rows="2" placeholder="Jl. Raya No. 123, RT 01/RW 02..."
                            class="w-full rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 px-4 py-3 text-sm text-zinc-900 dark:text-white focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"></textarea>
                        @error('alamat') <p class="mt-1 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Tanggal Lahir</label>
                        <x-ui.tanggal wire:model="tanggalLahir" :max="now()->toDateString()"
                            class="h-11 w-full rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 px-4 text-sm text-zinc-900 dark:text-white focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" />
                        @error('tanggalLahir') <p class="mt-1 text-xs font-medium text-rose-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Jenis Kelamin</label>
                        <select wire:model="jenisKelamin"
                            class="h-11 w-full rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 px-4 text-sm text-zinc-900 dark:text-white focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                            <option value="laki-laki">Laki-laki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Pekerjaan</label>
                        <input type="text" wire:model="pekerjaan" placeholder="Contoh: Pedagang / Wiraswasta"
                            class="h-11 w-full rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 px-4 text-sm text-zinc-900 dark:text-white focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" />
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 dark:bg-emerald-500 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 dark:hover:bg-emerald-600 disabled:opacity-50">
                        <span wire:loading.remove>Daftarkan Now</span>
                        <span wire:loading>Memproses...</span>
                    </button>
                    <button type="button" wire:click="toggleForm"
                        class="rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-5 py-2.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 transition hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Registered Nasabah Table Card --}}
    <div class="overflow-hidden rounded-3xl bg-white dark:bg-zinc-800 shadow-sm border border-zinc-100 dark:border-zinc-700/60">
        <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-700/60 flex items-center justify-between">
            <h3 class="text-sm font-bold text-zinc-900 dark:text-white">Daftar Nasabah Yang Didaftarkan</h3>
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total: {{ $nasabahList->total() }} nasabah</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-zinc-100 dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/80">
                        <th class="px-5 py-3.5 text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Nasabah</th>
                        <th class="px-5 py-3.5 text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">No. HP</th>
                        <th class="px-5 py-3.5 text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Status</th>
                        <th class="px-5 py-3.5 text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700/60">
                    @forelse($nasabahList as $item)
                        <tr class="transition hover:bg-zinc-50/60 dark:hover:bg-zinc-700/30">
                            <td class="px-5 py-4">
                                <div class="text-sm font-bold text-zinc-900 dark:text-white">{{ $item->nama }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-1 mt-0.5">{{ $item->alamat }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm font-mono text-zinc-700 dark:text-zinc-300">
                                {{ $item->user->no_hp ?? '— (offline)' }}
                            </td>
                            <td class="px-5 py-4">
                                @if($item->status_pendaftaran === 'aktif')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @elseif($item->status_pendaftaran === 'pending_verifikasi')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 dark:bg-amber-950/40 px-3 py-1 text-xs font-semibold text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Menunggu Verifikasi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 dark:bg-rose-950/40 px-3 py-1 text-xs font-semibold text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                        Ditolak
                                    </span>
                                @endif
                                @if($item->user?->isOffline())
                                    <span class="ml-1 inline-flex items-center rounded-full bg-zinc-200 dark:bg-zinc-700 px-3 py-1 text-xs font-semibold text-zinc-700 dark:text-zinc-200">Mode Offline</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $item->created_at->translatedFormat('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-14 text-center">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-700/50 text-zinc-400 dark:text-zinc-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a5.97 5.97 0 00-.942 3.197m0 0A9.093 9.093 0 012.25 18.24a3 3 0 013.742-2.72m12.457 2.2a9.093 9.093 0 00-3.742-2.72m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Belum ada nasabah yang Anda daftarkan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($nasabahList->hasPages())
            <div class="border-t border-zinc-100 dark:border-zinc-700/60 px-5 py-3">
                {{ $nasabahList->links() }}
            </div>
        @endif
    </div>
</div>

