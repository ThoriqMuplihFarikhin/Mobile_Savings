<div class="space-y-6">
    {{-- Judul halaman --}}
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Ikuti paket</h1>
        <p class="mt-1 text-sm text-mute">Pilih paket tabungan yang ingin Anda ikuti dan setujui komitmennya.</p>
    </div>

    {{-- Pesan sistem --}}
    @if (session('success'))
        <div class="flex items-start gap-2 rounded-xl bg-primary-soft p-3 text-sm text-on-primary-soft">
            <flux:icon.check-circle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-start gap-2 rounded-xl bg-danger-soft p-3 text-sm text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.exclamation-triangle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="space-y-3">
        @forelse ($paketTersedia as $paket)
            <article class="overflow-hidden rounded-[20px] border border-hairline-warm bg-white shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                <div class="flex items-center justify-between gap-3 border-b border-hairline-warm p-4">
                    <div class="min-w-0">
                        <h2 class="text-base font-medium text-ink">{{ $paket['nama'] }}</h2>
                        <p class="mt-0.5 text-base font-semibold text-ink">
                            <x-ui.rupiah :nilai="$paket['harga_per_hari']" />
                            <span class="text-sm font-normal text-mute">/ hari</span>
                        </p>
                    </div>
                    <x-ui.tile kategori="hijau" ukuran="44" aria-hidden="true">
                        <flux:icon.package class="size-5" />
                    </x-ui.tile>
                </div>

                <div class="space-y-4 p-4">
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2">
                        <div>
                            <dt class="text-sm text-mute">Periode</dt>
                            <dd class="mt-0.5 text-sm font-medium text-ink">
                                {{ $paket['periode_mulai'] ?? '-' }} &ndash; {{ $paket['periode_selesai'] ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-mute">Tanggal cair</dt>
                            <dd class="mt-0.5 text-sm font-medium text-ink">{{ $paket['tanggal_boleh_cair'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-mute">Aturan tunggakan</dt>
                            <dd class="mt-0.5 text-sm font-medium text-ink">
                                @if ($paket['toleransi_hari'] !== null)
                                    Toleransi {{ $paket['toleransi_hari'] }} hari
                                @else
                                    Toleransi mengikuti ketentuan umum
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-mute">Batas pendaftaran</dt>
                            <dd class="mt-0.5 text-sm font-medium text-ink">{{ $paket['batas_daftar_hingga'] ?? 'Terbuka' }}</dd>
                        </div>
                    </dl>

                    @if (count($paket['isi']) > 0)
                        <div>
                            <p class="mb-2 text-sm font-medium text-body">Isi paket</p>
                            <ul class="space-y-1">
                                @foreach ($paket['isi'] as $item)
                                    <li class="flex items-center justify-between gap-3 rounded-[14px] bg-inset px-3 py-2 text-sm">
                                        <span class="min-w-0 truncate text-ink">{{ $item['nama'] ?? '-' }}</span>
                                        <span class="shrink-0 text-body">{{ $item['jumlah'] ?? '-' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <button wire:click="bukaKomitmen({{ $paket['id'] }})"
                        class="flex h-12 w-full items-center justify-center rounded-full bg-primary text-base font-medium text-on-primary transition hover:bg-primary-hover active:scale-[0.98]">
                        Ikuti paket
                    </button>
                </div>
            </article>
        @empty
            <div class="rounded-[20px] border border-hairline-warm bg-white p-8 text-center shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                <span class="mx-auto flex size-32 items-center justify-center rounded-full bg-primary-soft" aria-hidden="true">
                    <x-ui.ilustrasi nama="tunas" :ukuran="96" />
                </span>
                <h2 class="mt-4 text-base font-semibold text-ink">Tidak ada paket yang bisa diikuti</h2>
                <p class="mt-1 text-sm text-mute">Semua paket sudah Anda ikuti atau pendaftarannya ditutup.</p>
            </div>
        @endforelse
    </div>

    {{-- Sheet konfirmasi komitmen --}}
    @if ($tampilKomitmen)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="judul-komitmen">
            <div class="w-full max-w-md rounded-t-3xl bg-white p-6 shadow-[0_8px_24px_rgb(0_0_0/0.12)] dark:bg-surface sm:rounded-3xl">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 id="judul-komitmen" class="text-xl font-semibold tracking-tight text-ink">Komitmen ikut paket</h2>
                        <p class="mt-0.5 text-sm text-mute">Baca dan setujui sebelum melanjutkan.</p>
                    </div>
                    <button wire:click="tutupKomitmen" class="-m-1 rounded-full p-1 text-mute transition hover:bg-inset" aria-label="Tutup">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                <div class="mt-4 max-h-64 overflow-y-auto rounded-xl bg-inset p-4 text-sm leading-relaxed text-body">
                    {{ $teksKomitmen }}
                </div>

                @error('setuju')
                    <p class="mt-2 flex items-center gap-1 text-sm text-danger dark:text-dark-danger-text"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p>
                @enderror

                <label class="mt-4 flex items-start gap-3 text-sm text-ink">
                    <input type="checkbox" wire:model="setuju" class="mt-0.5 size-4 rounded border-hairline-strong text-primary focus:ring-primary" />
                    <span>Saya telah membaca dan menyetujui komitmen di atas.</span>
                </label>

                <div class="mt-4">
                    <label for="pin-komitmen" class="mb-1 block text-sm font-medium text-body">Konfirmasi PIN</label>
                    <input id="pin-komitmen" type="password" inputmode="numeric" maxlength="6" wire:model="pin"
                        class="h-14 w-full rounded-xl border border-hairline-strong bg-white px-4 text-2xl font-semibold tracking-[0.4em] text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-zinc-950"
                        placeholder="******" />
                    @error('pin')
                        <p class="mt-1 flex items-center gap-1 text-sm text-danger dark:text-dark-danger-text"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p>
                    @enderror
                </div>

                @if ($pesanError)
                    <div class="mt-4 flex items-start gap-2 rounded-xl bg-danger-soft p-3 text-sm text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text">
                        <flux:icon.exclamation-triangle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                        <span>{{ $pesanError }}</span>
                    </div>
                @endif

                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button wire:click="tutupKomitmen"
                        class="h-12 rounded-full border border-hairline-strong bg-white px-5 text-base font-medium text-ink transition hover:bg-inset dark:bg-surface">
                        Batal
                    </button>
                    <button wire:click="ikutiPaket" wire:loading.attr="disabled"
                        class="h-12 rounded-full bg-primary px-5 text-base font-medium text-on-primary transition hover:bg-primary-hover disabled:opacity-50">
                        <span wire:loading.remove wire:target="ikutiPaket">Saya setuju &amp; ikuti paket</span>
                        <span wire:loading wire:target="ikutiPaket">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
