<div class="space-y-6">
    @if ($tampilSukses)
        {{-- Layar sukses (DESIGN.md §7 "Layar sukses"): konfeti statis, ringkasan, catatan Notifikasi --}}
        <div data-test="layar-sukses" class="flex flex-col items-center pb-2 pt-6 text-center">
            <div class="relative flex h-[128px] w-[128px] items-center justify-center">
                {{-- Konfeti datar: titik & batang emas, hijau, teal (statis, tanpa animasi) --}}
                <span class="absolute left-1 top-4 h-2 w-2 rounded-full bg-decor-gold" aria-hidden="true"></span>
                <span class="absolute left-7 top-0 h-3 w-1.5 rotate-[24deg] rounded-full bg-decor-green" aria-hidden="true"></span>
                <span class="absolute right-7 top-2 h-2 w-2 rounded-full bg-tile-teal-fg" aria-hidden="true"></span>
                <span class="absolute right-0 top-10 h-1.5 w-3 -rotate-[18deg] rounded-full bg-decor-gold" aria-hidden="true"></span>
                <span class="absolute bottom-2 left-3 h-2 w-2 rounded-full bg-tile-teal-fg" aria-hidden="true"></span>
                <span class="absolute bottom-0 left-10 h-3 w-1.5 -rotate-[30deg] rounded-full bg-decor-gold" aria-hidden="true"></span>
                <span class="absolute bottom-3 right-6 h-2.5 w-1.5 rotate-[35deg] rounded-full bg-decor-green" aria-hidden="true"></span>
                <span class="absolute bottom-8 right-0 h-2 w-2 rounded-full bg-decor-green" aria-hidden="true"></span>

                <div class="flex h-[104px] w-[104px] items-center justify-center rounded-full bg-primary ring-[12px] ring-primary-soft">
                    <flux:icon.check class="size-12 text-on-primary" aria-hidden="true" />
                </div>
            </div>

            <h2 class="mt-4 text-xl font-semibold tracking-tight text-ink">Pengajuan terkirim</h2>
            <p class="mt-1 text-sm text-mute">Pengajuan penarikan Anda menunggu persetujuan admin.</p>

            <dl class="mt-5 w-full space-y-2 rounded-[20px] border border-hairline-warm bg-surface p-4 text-left shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                <div class="flex items-baseline justify-between gap-3">
                    <dt class="text-sm text-mute">Produk</dt>
                    <dd class="text-sm font-semibold text-ink">{{ $suksesProduk ?: '-' }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-3">
                    <dt class="text-sm text-mute">Nominal diminta</dt>
                    <dd class="text-sm font-semibold text-ink"><x-ui.rupiah :nilai="$suksesNominalDiminta" /></dd>
                </div>
                <div class="flex items-baseline justify-between gap-3">
                    <dt class="text-sm text-mute">Biaya admin</dt>
                    <dd class="text-sm font-semibold"><x-ui.rupiah :nilai="$suksesKomisi" tanda="-" /></dd>
                </div>
                <div class="flex items-baseline justify-between gap-3 border-t border-hairline-warm pt-2">
                    <dt class="text-sm font-medium text-ink">Total diterima</dt>
                    <dd class="text-sm font-semibold text-ink"><x-ui.rupiah :nilai="$suksesDiterima" /></dd>
                </div>
                <div class="flex items-baseline justify-between gap-3">
                    <dt class="text-sm text-mute">Lokasi pengambilan</dt>
                    <dd class="text-sm font-semibold text-ink">{{ $suksesLokasi === 'kantor' ? 'Kantor utama' : 'Rumah kolektor' }}</dd>
                </div>
            </dl>

            <div class="mt-4 flex w-full items-start gap-2 rounded-xl bg-primary-soft p-3 text-left text-sm text-on-primary-soft">
                <flux:icon.check-circle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                <span>Kabar persetujuan admin akan muncul di menu <a href="{{ route('nasabah.notifikasi.index') }}" wire:navigate class="font-medium underline underline-offset-2">Notifikasi</a>.</span>
            </div>

            <div class="mt-6 flex w-full flex-col gap-2">
                <a href="{{ route('nasabah.notifikasi.index') }}" wire:navigate
                    class="flex h-12 w-full items-center justify-center rounded-full bg-primary text-sm font-medium text-on-primary transition hover:bg-primary-hover">
                    Lihat notifikasi
                </a>
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="flex h-12 w-full items-center justify-center rounded-full border border-hairline-strong bg-white text-sm font-medium text-ink transition hover:bg-surface-inset dark:bg-surface">
                    Kembali ke beranda
                </a>
            </div>
        </div>
    @else
        {{-- Judul halaman & Stepper --}}
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-ink">Ajukan penarikan</h1>
                <p class="mt-1 text-sm text-mute">Tarik saldo tabungan Anda dengan mudah dan fleksibel.</p>
            </div>
            <div class="mt-2">
                <x-ui.stepper-langkah :langkah-aktif="1" :total-langkah="3" />
            </div>
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

        {{-- Kartu pengajuan aktif (D18): batal langsung dari halaman ini --}}
        <livewire:nasabah.pengajuan-aktif />

        {{-- Saldo yang bisa ditarik --}}
        <x-ui.hero-card aria-labelledby="label-saldo-tersedia">
            <h2 id="label-saldo-tersedia" class="text-sm text-on-hero-mute">Saldo tersedia</h2>
            <div class="mt-2 divide-y divide-on-hero-mute/20">
                @forelse($saldoList as $s)
                    <div class="flex items-center justify-between gap-3 py-2 first:pt-0 last:pb-0">
                        <span class="min-w-0 truncate text-sm">{{ $s->produk->nama }}</span>
                        <span class="shrink-0 text-base font-semibold">
                            <x-ui.rupiah :nilai="$s->saldo" />
                        </span>
                    </div>
                @empty
                    <p class="py-1 text-sm">Belum ada saldo yang tersedia.</p>
                @endforelse
            </div>
        </x-ui.hero-card>

        {{-- Formulir pengajuan --}}
        <form wire:submit="submit" class="space-y-5 rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
            {{-- Pilih produk --}}
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-body" for="produk-tabungan">Produk tabungan</label>
                <select id="produk-tabungan" wire:model.live="produkId"
                    class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-sm text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                    <option value="">-- Pilih produk tabungan --</option>
                    @foreach($produkList as $produk)
                        <option value="{{ $produk->id }}">{{ $produk->nama }}{{ $produk->status !== 'aktif' ? ' (nonaktif)' : '' }}</option>
                    @endforeach
                </select>
                @error('produkId') <p class="mt-1 flex items-center gap-1 text-xs text-danger dark:text-dark-danger-text"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
            </div>

            {{-- Nominal penarikan --}}
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-body" for="nominal-penarikan">Nominal penarikan</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-2xl font-semibold text-mute">Rp</span>
                    <input type="number" id="nominal-penarikan" wire:model.live="nominal" min="0" step="0.01" inputmode="numeric"
                        class="h-[64px] w-full rounded-xl border border-hairline-strong bg-white pl-14 pr-4 text-2xl font-semibold text-primary focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"
                        placeholder="0" />
                </div>
                @error('nominal') <p class="mt-1 flex items-center gap-1 text-xs text-danger dark:text-dark-danger-text"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
                <p class="mt-1.5 text-xs text-mute">
                    Min. <x-ui.rupiah :nilai="(int) $nominalMinimal" /> atau tarik habis
                </p>

                {{-- Pilihan nominal cepat (DESIGN.md §7 "Input nominal dan pilihan cepat") --}}
                @php
                    /**
                     * Chip "Tarik habis" memakai saldo produk yang dipilih (token sudah
                     * dimuat lewat loadSaldo), tanpa query baru. Pengecualian minimal
                     * penarikan untuk tarik habis (AturanPenarikanTest) membuat chip ini
                     * hanya nonaktif bila produk belum dipilih atau saldonya nol.
                     */
                    $saldoTerpilih = $produkId !== '' ? optional($saldoList->firstWhere('produk_id', $produkId))->saldo : null;
                @endphp
                <div class="mt-2.5 flex flex-wrap gap-2" data-test="chip-amount">
                    @foreach ([50000, 100000, 200000] as $cepat)
                        <button type="button" wire:click="$set('nominal', '{{ $cepat }}')" @disabled((int) $nominalMinimal > $cepat)
                            class="h-10 rounded-full border px-4 text-xs font-medium transition {{ (float) ($nominal ?: 0) === (float) $cepat ? 'border-primary bg-primary text-on-primary' : 'border-hairline-strong bg-surface text-ink hover:bg-surface-inset' }} disabled:opacity-40">
                            Rp {{ number_format($cepat / 1000, 0, ',', '.') }} rb
                        </button>
                    @endforeach
                    <button type="button" wire:click="$set('nominal', '{{ $saldoTerpilih }}')" @disabled($saldoTerpilih === null || (float) $saldoTerpilih <= 0)
                        class="h-10 rounded-full border px-4 text-xs font-medium transition {{ $saldoTerpilih !== null && (float) ($nominal ?: 0) === (float) $saldoTerpilih ? 'border-primary bg-primary text-on-primary' : 'border-hairline-strong bg-surface text-ink hover:bg-surface-inset' }} disabled:opacity-40">
                        Tarik habis
                    </button>
                </div>
            </div>

            {{-- Rincian komisi --}}
            @if($selectedProduk && $nominal > 0)
                <div class="rounded-xl bg-surface-inset p-4 border border-hairline">
                    <div class="flex items-baseline justify-between gap-3">
                        <span class="text-xs font-semibold text-ink">Rincian transaksi</span>
                        <span class="text-xs text-mute">Estimasi pencairan</span>
                    </div>

                    <dl class="mt-3 space-y-2 border-t border-hairline pt-3">
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-xs text-mute">Nominal ditarik</dt>
                            <dd class="text-sm font-semibold text-ink">
                                <x-ui.rupiah :nilai="$nominal" />
                            </dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-xs text-mute">Biaya admin ({{ $persenKomisi }}%)</dt>
                            <dd class="text-sm font-semibold">
                                <x-ui.rupiah :nilai="$nominalKomisi" tanda="-" />
                            </dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3 border-t border-hairline pt-2">
                            <dt class="text-xs font-semibold text-ink">Total diterima</dt>
                            <dd class="text-sm font-semibold text-ink">
                                <x-ui.rupiah :nilai="$nominalDiterima" />
                            </dd>
                        </div>
                    </dl>
                </div>
            @endif

            {{-- Lokasi pengambilan --}}
            <fieldset>
                <legend class="mb-1.5 block text-xs font-semibold text-body">Lokasi pengambilan cash</legend>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border p-4 text-center transition {{ $lokasi_pengambilan === 'kantor' ? 'border-primary bg-primary-soft text-on-primary-soft font-semibold' : 'border-hairline bg-white text-body dark:bg-surface' }}">
                        <input type="radio" wire:model.live="lokasi_pengambilan" value="kantor" class="sr-only">
                        <span class="text-xs font-semibold">Kantor utama</span>
                        <span class="mt-0.5 text-xs text-mute">Ambil langsung</span>
                    </label>

                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border p-4 text-center transition {{ $lokasi_pengambilan === 'rumah_kolektor' ? 'border-primary bg-primary-soft text-on-primary-soft font-semibold' : 'border-hairline bg-white text-body dark:bg-surface' }}">
                        <input type="radio" wire:model.live="lokasi_pengambilan" value="rumah_kolektor" class="sr-only">
                        <span class="text-xs font-semibold">Rumah kolektor</span>
                        <span class="mt-0.5 text-xs text-mute">Via petugas</span>
                    </label>
                </div>
            </fieldset>

            {{-- Aksi --}}
            <div class="flex items-center gap-3 pt-2">
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="flex h-12 w-1/3 items-center justify-center rounded-full border border-hairline-strong bg-white text-sm font-medium text-ink transition hover:bg-surface-inset dark:bg-surface">
                    Batal
                </a>
                <button type="submit" wire:loading.attr="disabled"
                    class="flex h-12 w-2/3 items-center justify-center rounded-full bg-primary text-sm font-medium text-on-primary transition hover:bg-primary-hover active:scale-[0.98] disabled:opacity-50">
                    <span wire:loading.remove wire:target="submit">Lanjut konfirmasi</span>
                    <span wire:loading wire:target="submit">Memproses...</span>
                </button>
            </div>
        </form>

        {{-- Sheet konfirmasi & PIN (DESIGN.md §7 "Sheet", §8: rincian dulu, baru PIN) --}}
        @if ($tampilKonfirmasi)
            <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="judul-konfirmasi-penarikan"
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
                            <div class="flex items-center gap-2 mb-1">
                                <h2 id="judul-konfirmasi-penarikan" class="text-xl font-semibold tracking-tight text-ink">Konfirmasi penarikan</h2>
                            </div>
                            <p class="text-xs text-mute">Periksa rincian lalu masukkan PIN Anda.</p>
                        </div>
                        <button wire:click="tutupKonfirmasi" class="-m-1 rounded-full p-1 text-mute transition hover:bg-surface-inset" aria-label="Tutup">
                            <flux:icon.x-mark class="size-5" />
                        </button>
                    </div>

                    <dl class="mt-4 space-y-2 rounded-xl bg-surface-inset p-4 border border-hairline">
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-xs text-mute">Produk</dt>
                            <dd class="text-xs font-semibold text-ink">{{ optional($produkList->firstWhere('id', $produkId))->nama ?? '-' }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-xs text-mute">Nominal diminta</dt>
                            <dd class="text-xs font-semibold text-ink"><x-ui.rupiah :nilai="$nominal" /></dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-xs text-mute">Biaya admin ({{ $persenKomisi }}%)</dt>
                            <dd class="text-xs font-semibold"><x-ui.rupiah :nilai="$nominalKomisi" tanda="-" /></dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3 border-t border-hairline pt-2">
                            <dt class="text-xs font-semibold text-ink">Total diterima</dt>
                            <dd class="text-xs font-semibold text-ink"><x-ui.rupiah :nilai="$nominalDiterima" /></dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="text-xs text-mute">Lokasi pengambilan</dt>
                            <dd class="text-xs font-semibold text-ink">{{ $lokasi_pengambilan === 'kantor' ? 'Kantor utama' : 'Rumah kolektor' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4">
                        <div class="text-center mb-1">
                            <label for="pin-konfirmasi" class="text-xs font-semibold text-ink">Konfirmasi PIN</label>
                        </div>

                        {{-- Hidden input for accessibility and Livewire test binding --}}
                        <input id="pin-konfirmasi" type="password" inputmode="numeric" maxlength="6" wire:model="pin" x-model="pin"
                            class="sr-only" placeholder="******" />

                        {{-- Visual Keypad PIN --}}
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
                        <button wire:click="tutupKonfirmasi" type="button"
                            class="h-12 rounded-full border border-hairline-strong bg-white px-5 text-sm font-medium text-ink transition hover:bg-surface-inset dark:bg-surface">
                            Batal
                        </button>
                        <button wire:click="konfirmasi" wire:loading.attr="disabled" type="button"
                            class="h-12 rounded-full bg-primary px-5 text-sm font-medium text-on-primary transition hover:bg-primary-hover disabled:opacity-50">
                            <span wire:loading.remove wire:target="konfirmasi">Kirim pengajuan</span>
                            <span wire:loading wire:target="konfirmasi">Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
