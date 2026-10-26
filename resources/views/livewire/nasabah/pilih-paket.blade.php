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
            <article class="overflow-hidden rounded-[20px] border border-hairline-warm bg-white shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface" data-test="kartu-paket">
                <div class="flex items-center justify-between gap-3 border-b border-hairline-warm p-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5 mb-1">
                            <span class="rounded-full bg-tile-teal-bg px-2.5 py-0.5 text-xs font-medium text-tile-teal-fg">
                                Paket
                            </span>
                            <span class="rounded-full bg-primary-soft px-2.5 py-0.5 text-xs font-medium text-primary">
                                Bebas Biaya Admin
                            </span>
                        </div>
                        <h2 class="text-base font-semibold text-ink truncate">{{ $paket['nama'] }}</h2>
                        <p class="mt-0.5 text-base font-semibold text-ink">
                            <x-ui.rupiah :nilai="$paket['harga_per_hari']" />
                            <span class="text-xs font-normal text-mute">/ hari</span>
                        </p>
                    </div>
                    <x-ui.tile kategori="teal" ukuran="44" aria-hidden="true">
                        <flux:icon.shopping-bag class="size-5" />
                    </x-ui.tile>
                </div>

                <div class="space-y-4 p-4">
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2.5">
                        @if($paket['target'] !== null)
                            <div class="col-span-2 rounded-xl bg-surface-inset p-3 border border-hairline">
                                <dt class="text-xs text-mute">Target akhir terkumpul</dt>
                                <dd class="mt-0.5 text-base font-semibold text-ink">
                                    <x-ui.rupiah :nilai="$paket['target']" />
                                </dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-xs text-mute">Periode</dt>
                            <dd class="mt-0.5 text-xs font-medium text-ink">
                                {{ $paket['periode_mulai'] ?? '-' }} &ndash; {{ $paket['periode_selesai'] ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-mute">Tanggal cair</dt>
                            <dd class="mt-0.5 text-xs font-medium text-ink">{{ $paket['tanggal_boleh_cair'] ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-mute">Aturan tunggakan</dt>
                            <dd class="mt-0.5 text-xs font-medium text-ink">
                                @if ($paket['toleransi_hari'] !== null)
                                    Toleransi {{ $paket['toleransi_hari'] }} hari
                                @else
                                    Toleransi umum
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-mute">Batas pendaftaran</dt>
                            <dd class="mt-0.5 text-xs font-medium text-ink">{{ $paket['batas_daftar_hingga'] ?? 'Terbuka' }}</dd>
                        </div>
                    </dl>

                    @if (count($paket['isi']) > 0)
                        <div>
                            <p class="mb-2 text-xs font-medium text-body">Isi paket</p>
                            <ul class="space-y-1">
                                @foreach ($paket['isi'] as $item)
                                    <li class="flex items-center justify-between gap-3 rounded-xl bg-surface-inset px-3 py-2 text-xs border border-hairline">
                                        <span class="min-w-0 truncate text-ink font-medium">{{ $item['nama'] ?? '-' }}</span>
                                        <span class="shrink-0 text-mute">{{ $item['jumlah'] ?? '-' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <button wire:click="bukaKomitmen({{ $paket['id'] }})"
                        class="flex h-12 w-full items-center justify-center rounded-full bg-primary text-sm font-medium text-on-primary transition hover:bg-primary-hover active:scale-[0.98]"
                        data-test="tombol-pilih-paket">
                        Pilih Paket Ini
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
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="judul-komitmen"
            x-data="{
                pin: @entangle('pin'),
                showPin: false,
                isLocked: false,
                isSubmitting: false,
                appendDigit(digit) {
                    if (String(this.pin).length < 6) {
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
            <div class="w-full max-w-md rounded-t-3xl bg-white p-6 shadow-[0_8px_24px_rgb(0_0_0/0.12)] dark:bg-surface sm:rounded-3xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 id="judul-komitmen" class="text-xl font-semibold tracking-tight text-ink">Komitmen ikut paket</h2>
                        <p class="mt-0.5 text-xs text-mute">Baca dan setujui sebelum melanjutkan.</p>
                    </div>
                    <button wire:click="tutupKomitmen" class="-m-1 rounded-full p-1 text-mute transition hover:bg-surface-inset" aria-label="Tutup">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                <div class="mt-4 max-h-48 overflow-y-auto rounded-xl bg-surface-inset p-4 text-xs leading-relaxed text-body border border-hairline">
                    {{ $teksKomitmen }}
                </div>

                @error('setuju')
                    <p class="mt-2 flex items-center gap-1 text-xs text-danger dark:text-dark-danger-text">
                        <flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />
                        {{ $message }}
                    </p>
                @enderror

                <label class="mt-4 flex items-start gap-3 text-xs text-ink cursor-pointer">
                    <input type="checkbox" wire:model="setuju" class="mt-0.5 size-4 rounded border-hairline-strong text-primary focus:ring-primary" />
                    <span class="font-medium">Saya telah membaca dan menyetujui komitmen di atas.</span>
                </label>

                <div class="mt-4">
                    <div class="text-center mb-2">
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
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                @if ($pesanError)
                    <div class="mt-4 flex items-start gap-2 rounded-xl bg-danger-soft p-3 text-xs text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text">
                        <flux:icon.exclamation-triangle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                        <span>{{ $pesanError }}</span>
                    </div>
                @endif

                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button wire:click="tutupKomitmen" type="button"
                        class="h-12 rounded-full border border-hairline-strong bg-white px-5 text-sm font-medium text-ink transition hover:bg-surface-inset dark:bg-surface">
                        Batal
                    </button>
                    <button wire:click="ikutiPaket" wire:loading.attr="disabled" type="button"
                        class="h-12 rounded-full bg-primary px-5 text-sm font-medium text-on-primary transition hover:bg-primary-hover disabled:opacity-50">
                        <span wire:loading.remove wire:target="ikutiPaket">Saya setuju &amp; ikuti paket</span>
                        <span wire:loading wire:target="ikutiPaket">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
