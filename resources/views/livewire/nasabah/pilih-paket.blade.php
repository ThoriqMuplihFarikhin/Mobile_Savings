<div class="space-y-5">
    {{-- Header navigasi kembali & judul --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('nasabah.progres-paket.index') }}" wire:navigate
           aria-label="Kembali ke Progres Paket"
           class="flex size-11 shrink-0 items-center justify-center rounded-full border border-hairline bg-surface text-ink transition hover:bg-surface-inset active:scale-95">
            <flux:icon.arrow-left class="size-5" />
        </a>
        <div class="min-w-0 flex-1">
            <h1 class="text-xl font-semibold tracking-tight text-ink leading-tight">Ikuti paket</h1>
            <p class="text-xs text-mute truncate">Pilih paket tabungan yang ingin Anda ikuti dan setujui komitmennya.</p>
        </div>
    </div>

    {{-- Pesan sistem --}}
    @if (session('success'))
        <div class="flex items-start gap-2 rounded-2xl bg-primary-soft p-3 text-xs text-on-primary-soft">
            <flux:icon.check-circle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-start gap-2 rounded-2xl bg-danger-soft p-3 text-xs text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.exclamation-triangle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="space-y-4">
        @forelse ($paketTersedia as $paket)
            <article class="rounded-[24px] border border-hairline bg-white p-5 shadow-xs dark:bg-surface"
                     data-test="kartu-paket">
                {{-- Header kartu paket --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <x-ui.tile kategori="teal" ukuran="44" aria-hidden="true">
                            <flux:icon.gift class="size-5" />
                        </x-ui.tile>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-base font-semibold text-ink truncate leading-tight">{{ $paket['nama'] }}</h2>
                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                <span class="rounded-full bg-tile-teal-bg px-2 py-0.5 text-xs font-semibold text-tile-teal-fg">
                                    Paket
                                </span>
                                <span class="rounded-full bg-primary-soft px-2 py-0.5 text-xs font-semibold text-primary">
                                    Bebas Biaya Admin
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-semibold text-ink">
                            <x-ui.rupiah :nilai="$paket['harga_per_hari']" />
                        </p>
                        <p class="text-xs text-mute">/ hari</p>
                    </div>
                </div>

                {{-- Spesifikasi & Nilai (terbuka langsung sesuai prototipe #pc) --}}
                <div class="mt-4 pt-2 border-t border-hairline divide-y divide-hairline text-xs">
                    @if($paket['target'] !== null)
                        <div class="flex items-center justify-between py-2">
                            <span class="text-mute">Target akhir terkumpul</span>
                            <span class="font-semibold text-ink">
                                <x-ui.rupiah :nilai="$paket['target']" />
                            </span>
                        </div>
                    @endif

                    @if(!empty($paket['uang_tunai']) && $paket['uang_tunai'] > 0)
                        <div class="flex items-center justify-between py-2">
                            <span class="text-mute">Bonus Uang Tunai Diterima</span>
                            <span class="font-semibold text-primary">
                                <x-ui.rupiah :nilai="$paket['uang_tunai']" tanda="+" />
                            </span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between py-2">
                        <span class="text-mute">Periode</span>
                        <span class="font-medium text-ink">{{ $paket['periode_mulai'] ?? '-' }} &ndash; {{ $paket['periode_selesai'] ?? '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between py-2">
                        <span class="text-mute">Tanggal cair</span>
                        <span class="font-medium text-ink">{{ $paket['tanggal_boleh_cair'] ?? '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between py-2">
                        <span class="text-mute">Aturan tunggakan</span>
                        <span class="font-medium text-ink">
                            @if ($paket['toleransi_hari'] !== null)
                                Toleransi tunggakan {{ $paket['toleransi_hari'] }} hari
                            @else
                                Toleransi umum
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-2">
                        <span class="text-mute">Batas pendaftaran</span>
                        <span class="font-medium text-ink">{{ $paket['batas_daftar_hingga'] ?? 'Terbuka' }}</span>
                    </div>
                </div>

                {{-- Isi paket --}}
                @if (count($paket['isi']) > 0)
                    <div class="mt-3 rounded-2xl bg-surface-inset p-3 border border-hairline text-xs">
                        <p class="font-semibold text-ink mb-1.5">Isi paket</p>
                        <div class="divide-y divide-hairline">
                            @foreach ($paket['isi'] as $item)
                                <div class="flex items-center justify-between py-1.5">
                                    <span class="text-mute truncate">{{ $item['nama'] ?? '-' }}</span>
                                    <span class="font-semibold text-ink shrink-0">&times; {{ $item['jumlah'] ?? '-' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Tombol ikuti paket --}}
                <button wire:click="bukaKomitmen({{ $paket['id'] }})"
                    class="mt-4 flex h-12 w-full items-center justify-center rounded-full bg-primary text-sm font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-[0.98]"
                    data-test="tombol-pilih-paket">
                    Ikuti paket
                </button>
            </article>
        @empty
            <div class="rounded-[24px] border border-hairline bg-white p-8 text-center shadow-xs dark:bg-surface">
                <span class="mx-auto flex size-32 items-center justify-center rounded-full bg-primary-soft" aria-hidden="true">
                    <x-ui.ilustrasi nama="tunas" :ukuran="96" />
                </span>
                <h2 class="mt-4 text-base font-semibold text-ink">Tidak ada paket yang bisa diikuti</h2>
                <p class="mt-1 text-xs text-mute">Semua paket sudah Anda ikuti atau pendaftarannya ditutup.</p>
            </div>
        @endforelse
    </div>

    {{-- Sheet konfirmasi komitmen persis prototipe (#sh-k) --}}
    @if ($tampilKomitmen)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-0 sm:items-center sm:p-4" role="dialog" aria-modal="true" aria-labelledby="judul-komitmen"
            x-data="{
                pin: @entangle('pin'),
                showPin: false,
                isLocked: false,
                isSubmitting: false,
                hasError: {{ $errors->has('pin') || $pesanError ? 'true' : 'false' }},
                appendDigit(digit) {
                    if (String(this.pin || '').length < 6) {
                        this.pin = (this.pin ? String(this.pin) : '') + String(digit);
                    }
                },
                deleteDigit() {
                    if (this.pin && String(this.pin).length > 0) {
                        this.pin = String(this.pin).slice(0, -1);
                    }
                },
                peekPin() {
                    this.showPin = !this.showPin;
                }
            }">
            {{-- Backdrop click area to dismiss --}}
            <div class="fixed inset-0" wire:click="tutupKomitmen" aria-hidden="true"></div>

            <div class="relative w-full max-w-md rounded-t-[28px] border-t border-hairline bg-surface p-5 sm:p-6 shadow-md dark:bg-surface sm:rounded-[24px] sm:border max-h-[92vh] overflow-y-auto" @click.stop>
                {{-- Mobile drag handle pill --}}
                <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-hairline-strong/60 sm:hidden" aria-hidden="true"></div>

                <div class="flex items-center justify-between gap-3 pb-3 border-b border-hairline/80">
                    <div>
                        <h2 id="judul-komitmen" class="text-base font-semibold tracking-tight text-ink dark:text-zinc-100">Komitmen ikut paket</h2>
                        <p class="mt-0.5 text-xs text-mute dark:text-zinc-400">Baca dan setujui sebelum melanjutkan.</p>
                    </div>
                    <button wire:click="tutupKomitmen" class="flex size-8 shrink-0 items-center justify-center rounded-full bg-surface-inset text-mute transition hover:bg-hairline hover:text-ink active:scale-95 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-400" aria-label="Tutup">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </div>

                {{-- Teks komitmen --}}
                <div class="mt-4 max-h-40 overflow-y-auto rounded-2xl bg-surface-inset p-3.5 text-xs leading-relaxed text-body border border-hairline">
                    {{ $teksKomitmen }}
                </div>

                @error('setuju')
                    <p class="mt-2 flex items-center gap-1 text-xs text-danger dark:text-dark-danger-text">
                        <flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />
                        <span>{{ $message }}</span>
                    </p>
                @enderror

                {{-- Checkbox persetujuan --}}
                <label class="mt-4 flex items-start gap-3 text-xs text-ink cursor-pointer">
                    <input type="checkbox" wire:model="setuju" class="mt-0.5 size-4 rounded border-hairline-strong text-primary focus:ring-primary" />
                    <span class="font-medium">Saya telah membaca dan menyetujui komitmen di atas.</span>
                </label>

                {{-- Keypad PIN --}}
                <div class="mt-4">
                    <div class="text-center mb-1">
                        <label for="pin-komitmen" class="text-xs font-semibold text-ink">Masukkan PIN 6 Digit</label>
                    </div>

                    {{-- Hidden input for accessibility, keyboard, & Livewire binding --}}
                    <input id="pin-komitmen" type="password" inputmode="numeric" maxlength="6" wire:model="pin" x-model="pin"
                        class="sr-only" placeholder="******" />

                    {{-- Visual PIN Pad --}}
                    <x-ui.pin-pad portal="nasabah" />

                    @error('pin')
                        <p class="mt-2 text-center flex items-center justify-center gap-1 text-xs text-danger dark:text-dark-danger-text">
                            <flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                @if ($pesanError)
                    <div class="mt-4 flex items-start gap-2 rounded-xl bg-danger-soft p-3 text-xs text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text">
                        <flux:icon.exclamation-triangle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                        <span>{{ $pesanError }}</span>
                    </div>
                @endif

                <div class="mt-5 flex flex-col-reverse gap-2.5 sm:flex-row sm:justify-end">
                    <button wire:click="tutupKomitmen" type="button"
                        class="h-11 min-h-[44px] rounded-full border border-hairline-strong bg-surface px-5 text-xs font-semibold text-ink transition hover:bg-surface-inset active:scale-95 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-700">
                        Batal
                    </button>
                    <button wire:click="ikutiPaket" wire:loading.attr="disabled" type="button"
                        class="h-11 min-h-[44px] rounded-full bg-primary px-5 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-95 disabled:opacity-50">
                        <span wire:loading.remove wire:target="ikutiPaket">Saya setuju &amp; ikuti paket</span>
                        <span wire:loading wire:target="ikutiPaket">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

