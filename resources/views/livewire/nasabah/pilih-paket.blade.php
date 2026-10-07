<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Ikuti Paket</h1>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Pilih paket tabungan yang ingin Anda ikuti dan setujui komitmennya.</p>
        </div>
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 shadow-2xs border border-indigo-100 dark:border-indigo-900/50">
            <flux:icon.circle-plus class="size-5" />
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 p-4 text-xs font-semibold text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
            <flux:icon.check-circle class="size-5 shrink-0 text-emerald-600 dark:text-emerald-400" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 rounded-2xl bg-rose-50 dark:bg-rose-950/40 p-4 text-xs font-semibold text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40">
            <flux:icon.exclamation-triangle class="size-5 shrink-0 text-rose-600 dark:text-rose-400" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="space-y-4">
        @forelse ($paketTersedia as $paket)
            <div class="rounded-3xl bg-white dark:bg-zinc-800 overflow-hidden shadow-xs border border-zinc-200/80 dark:border-zinc-700/80">
                <div class="relative overflow-hidden bg-gradient-to-br from-indigo-900 via-indigo-800 to-zinc-900 p-5 text-white dark:from-zinc-950 dark:to-zinc-900">
                    <div class="absolute -right-8 -top-8 h-36 w-36 rounded-full bg-indigo-500/20 blur-2xl pointer-events-none"></div>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-bold text-white leading-tight">{{ $paket['nama'] }}</h3>
                            <p class="mt-0.5 text-[11px] text-indigo-200/80 font-mono">Rp {{ number_format($paket['harga_per_hari'], 0, ',', '.') }} / hari</p>
                        </div>
                        <flux:icon.package class="size-6 shrink-0 text-indigo-300/70" />
                    </div>
                </div>

                <div class="space-y-4 p-5">
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">Periode</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">
                                {{ $paket['periode_mulai'] ?? '-' }} &ndash; {{ $paket['periode_selesai'] ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">Tanggal cair</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $paket['tanggal_boleh_cair'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">Aturan tunggakan</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">
                                @if ($paket['toleransi_hari'] !== null)
                                    Toleransi {{ $paket['toleransi_hari'] }} hari
                                @else
                                    Toleransi mengikuti ketentuan umum
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">Batas pendaftaran</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">{{ $paket['batas_daftar_hingga'] ?? 'Terbuka' }}</dd>
                        </div>
                    </dl>

                    @if (count($paket['isi']) > 0)
                        <div>
                            <p class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Isi Paket</p>
                            <ul class="space-y-1">
                                @foreach ($paket['isi'] as $item)
                                    <li class="flex items-center justify-between rounded-xl bg-zinc-50 dark:bg-zinc-900/60 px-3 py-2 text-xs">
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $item['nama'] ?? '-' }}</span>
                                        <span class="font-mono text-zinc-600 dark:text-zinc-300">{{ $item['jumlah'] ?? '-' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <button wire:click="bukaKomitmen({{ $paket['id'] }})"
                        class="w-full rounded-2xl bg-indigo-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-800 active:scale-[0.99] dark:bg-indigo-600 dark:hover:bg-indigo-500">
                        Ikuti Paket
                    </button>
                </div>
            </div>
        @empty
            <div class="rounded-3xl border border-dashed border-zinc-300 dark:border-zinc-700 p-8 text-center">
                <flux:icon.package class="mx-auto size-8 text-zinc-400" />
                <p class="mt-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Tidak ada paket yang bisa diikuti saat ini.</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Semua paket sudah Anda ikuti atau pendaftarannya ditutup.</p>
            </div>
        @endforelse
    </div>

    {{-- Modal Komitmen --}}
    @if ($tampilKomitmen)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-4 sm:items-center">
            <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-xl dark:bg-zinc-800">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Komitmen Ikut Paket</h3>
                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Baca dan setujui sebelum melanjutkan.</p>
                    </div>
                    <button wire:click="tutupKomitmen" class="rounded-full p-1 text-zinc-400 transition hover:bg-zinc-100 dark:hover:bg-zinc-700" aria-label="Tutup">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                <div class="mt-4 max-h-64 overflow-y-auto rounded-2xl bg-zinc-50 p-4 text-xs leading-relaxed text-zinc-700 dark:bg-zinc-900/60 dark:text-zinc-300">
                    {{ $teksKomitmen }}
                </div>

                @error('setuju')
                    <p class="mt-2 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror

                <label class="mt-4 flex items-start gap-3 text-sm text-zinc-800 dark:text-zinc-200">
                    <input type="checkbox" wire:model="setuju" class="mt-0.5 size-4 rounded border-zinc-300 text-indigo-900 focus:ring-indigo-900" />
                    <span>Saya telah membaca dan menyetujui komitmen di atas.</span>
                </label>

                <div class="mt-4">
                    <label for="pin-komitmen" class="mb-1 block text-xs font-semibold text-zinc-600 dark:text-zinc-400">Konfirmasi PIN</label>
                    <input id="pin-komitmen" type="password" inputmode="numeric" maxlength="6" wire:model="pin"
                        class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 font-mono text-sm tracking-[0.4em] text-zinc-900 focus:border-indigo-900 focus:ring-indigo-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                        placeholder="******" />
                    @error('pin')
                        <p class="mt-1 text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                @if ($pesanError)
                    <div class="mt-4 flex items-start gap-2 rounded-2xl bg-rose-50 dark:bg-rose-950/40 p-3 text-xs font-medium text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40">
                        <flux:icon.exclamation-triangle class="mt-0.5 size-4 shrink-0" />
                        <span>{{ $pesanError }}</span>
                    </div>
                @endif

                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button wire:click="tutupKomitmen"
                        class="rounded-full border border-zinc-200 px-5 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-900">
                        Batal
                    </button>
                    <button wire:click="ikutiPaket" wire:loading.attr="disabled"
                        class="rounded-full bg-indigo-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800 disabled:opacity-60 dark:bg-indigo-600 dark:hover:bg-indigo-500">
                        <span wire:loading.remove wire:target="ikutiPaket">Saya Setuju &amp; Ikuti Paket</span>
                        <span wire:loading wire:target="ikutiPaket">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
