<div class="flex flex-col gap-4 pb-6">
    @if ($tampilSukses)
        {{-- Layar sukses (DESIGN.md §7 "Layar sukses"): konfeti statis, ringkasan, catatan Notifikasi --}}
        <div data-test="layar-sukses" class="flex flex-col items-center pb-2 pt-6 text-center">
            <div class="relative flex h-[128px] w-[128px] items-center justify-center">
                {{-- Konfeti datar: titik & batang emas, hijau, teal (statis) --}}
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
            <p class="mt-1 text-xs text-mute">Pengajuan penarikan Anda menunggu persetujuan admin.</p>

            <dl class="mt-5 w-full space-y-2 rounded-2xl border border-hairline bg-white p-4 text-left shadow-xs dark:bg-surface">
                <div class="flex items-baseline justify-between gap-3">
                    <dt class="text-xs text-mute">Produk</dt>
                    <dd class="text-xs font-semibold text-ink">{{ $suksesProduk ?: '-' }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-3">
                    <dt class="text-xs text-mute">Nominal diminta</dt>
                    <dd class="text-xs font-semibold text-ink"><x-ui.rupiah :nilai="$suksesNominalDiminta" /></dd>
                </div>
                <div class="flex items-baseline justify-between gap-3">
                    <dt class="text-xs text-mute">Biaya admin</dt>
                    <dd class="text-xs font-semibold"><x-ui.rupiah :nilai="$suksesKomisi" tanda="-" /></dd>
                </div>
                <div class="flex items-baseline justify-between gap-3 border-t border-hairline pt-2">
                    <dt class="text-xs font-semibold text-ink">Total diterima</dt>
                    <dd class="text-sm font-semibold text-primary"><x-ui.rupiah :nilai="$suksesDiterima" /></dd>
                </div>
                <div class="flex items-baseline justify-between gap-3">
                    <dt class="text-xs text-mute">Lokasi pengambilan</dt>
                    <dd class="text-xs font-semibold text-ink">{{ $suksesLokasi === 'kantor' ? 'Kantor utama' : 'Rumah kolektor' }}</dd>
                </div>
            </dl>

            <div class="mt-4 flex w-full items-start gap-2 rounded-2xl bg-primary-soft p-3 text-left text-xs text-on-primary-soft">
                <flux:icon.check-circle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                <span>Kabar persetujuan admin akan muncul di menu <a href="{{ route('nasabah.notifikasi.index') }}" wire:navigate class="font-medium underline underline-offset-2">Notifikasi</a>.</span>
            </div>

            <div class="mt-6 flex w-full flex-col gap-2">
                <a href="{{ route('nasabah.notifikasi.index') }}" wire:navigate
                    class="flex h-12 w-full items-center justify-center rounded-full bg-primary text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-[0.98]">
                    Lihat notifikasi
                </a>
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="flex h-12 w-full items-center justify-center rounded-full border border-hairline-strong bg-white text-xs font-medium text-ink transition hover:bg-surface-inset active:scale-[0.98] dark:bg-surface">
                    Kembali ke beranda
                </a>
            </div>
        </div>
    @else
        {{-- Header navigasi kembali, judul & subjudul --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" wire:navigate
               aria-label="Kembali ke Beranda"
               class="flex size-11 shrink-0 items-center justify-center rounded-full border border-hairline bg-surface text-ink transition hover:bg-surface-inset active:scale-95">
                <flux:icon.arrow-left class="size-5" />
            </a>
            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-semibold tracking-tight text-ink leading-tight">Ajukan penarikan</h1>
                <p class="text-xs text-mute truncate">Tarik saldo tabungan Anda dengan mudah dan fleksibel.</p>
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

        {{-- Kartu pengajuan aktif: batal langsung dari halaman ini --}}
        <livewire:nasabah.pengajuan-aktif />

        {{-- Formulir penarikan --}}
        <form wire:submit="submit" class="flex flex-col gap-3.5">
            {{-- 1. Kartu Saldo Tersedia --}}
            @php
                $saldoTerpilih = $produkId !== '' ? optional($saldoList->firstWhere('produk_id', $produkId))->saldo : null;
                $totalSaldo = $saldoList->sum('saldo');
            @endphp
            <div data-test="hero-card" class="rounded-2xl border border-hairline bg-white p-4 shadow-xs dark:bg-surface">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-mute">Saldo tersedia</span>
                    <span class="text-base font-semibold text-primary tabular-nums">
                        @if($selectedProduk && $saldoTerpilih !== null)
                            <x-ui.rupiah :nilai="$saldoTerpilih" />
                        @else
                            <x-ui.rupiah :nilai="$totalSaldo" />
                        @endif
                    </span>
                </div>
            </div>

            {{-- 2. Pemilihan Produk Tabungan --}}
            <div>
                <label class="mb-1.5 block text-xs font-medium text-body" for="produk-tabungan">
                    Produk tabungan
                </label>
                <select id="produk-tabungan" wire:model.live="produkId"
                    class="w-full rounded-2xl border border-hairline-strong bg-white px-3.5 py-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                    <option value="">-- Pilih produk tabungan --</option>
                    @foreach($produkList as $produk)
                        @php
                            $saldoItem = optional($saldoList->firstWhere('produk_id', $produk->id))->saldo ?? 0;
                        @endphp
                        <option value="{{ $produk->id }}">
                            {{ $produk->nama }}{{ $produk->status !== 'aktif' ? ' (nonaktif)' : '' }} · Rp {{ number_format($saldoItem, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
                @error('produkId')
                    <p class="mt-1 flex items-center gap-1 text-xs text-danger dark:text-dark-danger-text">
                        <flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            {{-- 3. Input & Display Nominal Penarikan --}}
            <div class="rounded-2xl border border-hairline bg-white p-5 text-center shadow-xs dark:bg-surface">
                <label for="nominal-penarikan" class="block text-xs text-mute mb-1">
                    Nominal penarikan
                </label>

                <div class="relative inline-flex items-center justify-center max-w-full">
                    <span class="text-2xl font-semibold text-primary mr-1">Rp</span>
                    <input type="number" id="nominal-penarikan" wire:model.live="nominal" min="0" step="0.01" inputmode="numeric"
                        class="w-60 text-center text-3xl font-semibold tracking-tight text-primary bg-transparent border-b-2 border-hairline-strong focus:border-primary focus:outline-none transition p-1"
                        placeholder="0" />
                </div>

                @error('nominal')
                    <p class="mt-1.5 flex items-center justify-center gap-1 text-xs text-danger dark:text-dark-danger-text">
                        <flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />
                        <span>{{ $message }}</span>
                    </p>
                @enderror

                <p class="mt-1.5 text-xs text-mute">
                    Min. <x-ui.rupiah :nilai="(int) $nominalMinimal" /> atau tarik habis
                </p>

                {{-- 4. Pilihan nominal cepat --}}
                <div class="mt-3.5 flex flex-wrap justify-center gap-2" data-test="chip-amount">
                    @foreach ([50000, 100000, 200000] as $cepat)
                        <button type="button" wire:click="$set('nominal', '{{ $cepat }}')" @disabled((int) $nominalMinimal > $cepat)
                            class="h-10 rounded-full border px-4 text-xs font-semibold transition active:scale-95 {{ (float) ($nominal ?: 0) === (float) $cepat ? 'border-primary bg-primary text-on-primary' : 'border-hairline-strong bg-surface text-ink hover:bg-surface-inset' }} disabled:opacity-40">
                            Rp {{ number_format($cepat / 1000, 0, ',', '.') }} rb
                        </button>
                    @endforeach
                    <button type="button" wire:click="$set('nominal', '{{ $saldoTerpilih }}')" @disabled($saldoTerpilih === null || (float) $saldoTerpilih <= 0)
                        class="h-10 rounded-full border px-4 text-xs font-semibold transition active:scale-95 {{ $saldoTerpilih !== null && (float) ($nominal ?: 0) === (float) $saldoTerpilih ? 'border-primary bg-primary text-on-primary' : 'border-hairline-strong bg-surface text-ink hover:bg-surface-inset' }} disabled:opacity-40">
                        Tarik habis
                    </button>
                </div>
            </div>

            {{-- 5. Rincian Transaksi --}}
            @if($selectedProduk && $nominal > 0)
                <div class="rounded-2xl border border-hairline bg-white p-4 shadow-xs dark:bg-surface">
                    <h2 class="text-sm font-semibold text-ink mb-2">Rincian transaksi</h2>
                    <div class="divide-y divide-hairline text-xs">
                        <div class="flex items-center justify-between py-2">
                            <span class="text-mute">Estimasi pencairan</span>
                            <span class="font-semibold text-ink">
                                {{ $lokasi_pengambilan === 'kantor' ? '1 hari kerja setelah disetujui' : 'Saat kunjungan kolektor' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-mute">Nominal ditarik</span>
                            <span class="font-semibold tabular-nums text-ink">
                                <x-ui.rupiah :nilai="$nominal" />
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-mute">Biaya admin</span>
                            <span class="font-semibold tabular-nums">
                                <x-ui.rupiah :nilai="$nominalKomisi" tanda="-" />
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-2.5">
                            <span class="font-semibold text-ink">Total diterima</span>
                            <span class="text-base font-semibold tabular-nums text-primary">
                                <x-ui.rupiah :nilai="$nominalDiterima" />
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 6. Lokasi Pengambilan Cash --}}
            <div>
                <label class="mb-2 block text-xs font-medium text-body">Lokasi pengambilan cash</label>
                <div class="space-y-2">
                    <label class="flex w-full cursor-pointer items-center gap-3.5 rounded-2xl border p-3.5 text-left transition active:scale-[0.99] {{ $lokasi_pengambilan === 'kantor' ? 'border-primary bg-primary-soft text-on-primary-soft shadow-xs' : 'border-hairline-strong bg-white hover:bg-surface-inset dark:bg-surface text-ink' }}">
                        <input type="radio" wire:model.live="lokasi_pengambilan" value="kantor" class="sr-only">
                        <span class="flex size-5 shrink-0 items-center justify-center rounded-full border-2 transition {{ $lokasi_pengambilan === 'kantor' ? 'border-primary bg-primary' : 'border-hairline-strong bg-white' }}">
                            @if($lokasi_pengambilan === 'kantor')
                                <span class="size-1.5 rounded-full bg-white"></span>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <span class="block text-xs font-semibold">Kantor utama</span>
                            <span class="block text-xs text-mute">Ambil langsung</span>
                        </div>
                    </label>

                    <label class="flex w-full cursor-pointer items-center gap-3.5 rounded-2xl border p-3.5 text-left transition active:scale-[0.99] {{ $lokasi_pengambilan === 'rumah_kolektor' ? 'border-primary bg-primary-soft text-on-primary-soft shadow-xs' : 'border-hairline-strong bg-white hover:bg-surface-inset dark:bg-surface text-ink' }}">
                        <input type="radio" wire:model.live="lokasi_pengambilan" value="rumah_kolektor" class="sr-only">
                        <span class="flex size-5 shrink-0 items-center justify-center rounded-full border-2 transition {{ $lokasi_pengambilan === 'rumah_kolektor' ? 'border-primary bg-primary' : 'border-hairline-strong bg-white' }}">
                            @if($lokasi_pengambilan === 'rumah_kolektor')
                                <span class="size-1.5 rounded-full bg-white"></span>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <span class="block text-xs font-semibold">Rumah kolektor</span>
                            <span class="block text-xs text-mute">Via petugas</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- 7. Tombol Aksi Bawah & Alasan Nonaktif --}}
            @php
                $alasanNonaktif = match (true) {
                    empty($produkId) => 'Pilih produk tabungan terlebih dahulu.',
                    empty($nominal) || (float) $nominal <= 0 => 'Isi nominal penarikan.',
                    $saldoTerpilih !== null && (float) $nominal > (float) $saldoTerpilih => 'Saldo produk belum mencukupi.',
                    $selectedProduk && (int) $nominalMinimal > 0 && (float) $nominal < (int) $nominalMinimal && (float) $nominal !== (float) $saldoTerpilih => 'Nominal di bawah minimal penarikan.',
                    default => '',
                };
            @endphp
            <div class="pt-2">
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" wire:navigate
                        class="flex h-12 w-1/3 items-center justify-center rounded-full border border-hairline-strong bg-white text-xs font-medium text-ink transition hover:bg-surface-inset active:scale-95 dark:bg-surface">
                        Batal
                    </a>
                    <button type="submit" wire:loading.attr="disabled"
                        class="flex h-12 w-2/3 items-center justify-center rounded-full bg-primary text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-[0.98] disabled:opacity-50">
                        <span wire:loading.remove wire:target="submit">Lanjut konfirmasi</span>
                        <span wire:loading wire:target="submit">Memproses...</span>
                    </button>
                </div>
                @if($alasanNonaktif)
                    <p class="mt-2 text-center text-xs text-mute min-h-[1.2em]">{{ $alasanNonaktif }}</p>
                @endif
            </div>
        </form>

        {{-- Sheet konfirmasi & PIN (DESIGN.md §7 "Sheet", §8: rincian dulu, baru PIN) --}}
        @if ($tampilKonfirmasi)
            <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-0 sm:items-center sm:p-4" role="dialog" aria-modal="true" aria-labelledby="judul-konfirmasi-penarikan"
                x-data="{
                    pin: @entangle('pin'),
                    showPin: false,
                    isLocked: false,
                    isSubmitting: false,
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
                <div class="fixed inset-0" wire:click="tutupKonfirmasi" aria-hidden="true"></div>

                <div class="relative w-full max-w-md rounded-t-[28px] border-t border-hairline bg-surface p-5 sm:p-6 shadow-md dark:bg-surface sm:rounded-[24px] sm:border max-h-[92vh] overflow-y-auto" @click.stop>
                    {{-- Mobile drag handle pill --}}
                    <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-hairline-strong/60 sm:hidden" aria-hidden="true"></div>

                    <div class="flex items-start justify-between gap-3 pb-3 border-b border-hairline/80">
                        <div>
                            <h2 id="judul-konfirmasi-penarikan" class="text-base font-semibold tracking-tight text-ink dark:text-zinc-100">Konfirmasi penarikan</h2>
                            <p class="mt-0.5 text-xs text-mute dark:text-zinc-400">Periksa rincian lalu masukkan PIN Anda.</p>
                        </div>
                        <button wire:click="tutupKonfirmasi" class="flex size-8 shrink-0 items-center justify-center rounded-full bg-surface-inset text-mute transition hover:bg-hairline hover:text-ink active:scale-95 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-400" aria-label="Tutup">
                            <flux:icon.x-mark class="size-4" />
                        </button>
                    </div>

                    <dl class="mt-4 space-y-2 rounded-2xl bg-surface-inset p-3.5 border border-hairline">
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
                            <dd class="text-sm font-semibold text-primary"><x-ui.rupiah :nilai="$nominalDiterima" /></dd>
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
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    @if ($pesanError)
                        <div class="mt-4 flex items-start gap-2 rounded-2xl bg-danger-soft p-3 text-xs text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text">
                            <flux:icon.exclamation-triangle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                            <span>{{ $pesanError }}</span>
                        </div>
                    @endif

                    <div class="mt-6 flex flex-col-reverse gap-2.5 sm:flex-row sm:justify-end">
                        <button wire:click="tutupKonfirmasi" type="button"
                            class="h-11 min-h-[44px] rounded-full border border-hairline-strong bg-surface px-5 text-xs font-semibold text-ink transition hover:bg-surface-inset active:scale-95 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-700">
                            Batal
                        </button>
                        <button wire:click="konfirmasi" wire:loading.attr="disabled" type="button"
                            class="h-11 min-h-[44px] rounded-full bg-primary px-5 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-[0.98] disabled:opacity-50">
                            <span wire:loading.remove wire:target="konfirmasi">Kirim pengajuan</span>
                            <span wire:loading wire:target="konfirmasi">Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

