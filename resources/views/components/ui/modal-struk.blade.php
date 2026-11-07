<div
    x-data="{
        buka: false,
        memuat: false,
        galat: null,
        struk: null,
        toastTampil: false,
        toastPesan: '',
        bukaStruk(url) {
            this.buka = true;
            this.memuat = true;
            this.galat = null;
            this.struk = null;
            fetch(url, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Gagal memuat rincian struk (HTTP ' + res.status + ')');
                }
                return res.json();
            })
            .then(data => {
                this.struk = data;
                this.memuat = false;
            })
            .catch(err => {
                this.galat = err.message || 'Terjadi kesalahan saat memuat struk.';
                this.memuat = false;
            });
        },
        tutup() {
            this.buka = false;
            this.struk = null;
            this.galat = null;
        },
        cetak() {
            if (this.struk && this.struk.url) {
                window.open(this.struk.url, '_blank');
            } else {
                window.print();
            }
        },
        bagikan() {
            if (!this.struk) return;
            const teks = 'Struk Setoran ' + this.struk.nomor + '\n' +
                'Nasabah: ' + this.struk.nasabah + '\n' +
                'Nominal: ' + this.struk.nominal_formatted + '\n' +
                'Tanggal: ' + this.struk.tanggal;

            if (navigator.share) {
                navigator.share({
                    title: 'Struk Setoran KawalDuit',
                    text: teks,
                    url: this.struk.url || window.location.href
                }).catch(() => {});
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(teks).then(() => {
                    this.tampilkanToast('Struk disalin ke clipboard');
                }).catch(() => {
                    this.tampilkanToast('Struk disalin ke clipboard');
                });
            } else {
                this.tampilkanToast('Struk disalin ke clipboard');
            }
        },
        tampilkanToast(pesan) {
            this.toastPesan = pesan;
            this.toastTampil = true;
            setTimeout(() => {
                this.toastTampil = false;
            }, 2500);
        }
    }"
    x-on:buka-struk.window="bukaStruk($event.detail.url)"
    x-on:keydown.escape.window="tutup()"
    x-show="buka"
    style="display: none;"
    class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-0 sm:items-center sm:p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-struk-title"
    x-transition:enter="ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    data-test="modal-struk-popup"
>
    {{-- Backdrop click area to dismiss --}}
    <div class="fixed inset-0" @click="tutup()" aria-hidden="true"></div>

    {{-- Sheet / Dialog Content --}}
    <div
        class="relative w-full max-w-md rounded-t-[28px] border-t border-hairline bg-surface p-5 shadow-md dark:bg-surface sm:rounded-[24px] sm:border sm:p-6 max-h-[92vh] overflow-y-auto"
        x-transition:enter="ease-out duration-250"
        x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
        x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
        x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
        @click.stop
    >
        {{-- Mobile drag handle pill --}}
        <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-hairline-strong/60 sm:hidden" aria-hidden="true"></div>

        {{-- Top Bar: Brand & Close Button --}}
        <div class="flex items-center justify-between pb-3 border-b border-hairline/80">
            <div class="flex items-center gap-2">
                <span class="flex size-7 items-center justify-center rounded-lg bg-primary text-white shadow-xs">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4">
                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                        <line x1="2" y1="10" x2="22" y2="10"/>
                    </svg>
                </span>
                <span class="text-xs font-semibold uppercase tracking-wider text-ink dark:text-zinc-100">KawalDuit</span>
            </div>
            <button
                type="button"
                @click="tutup()"
                class="flex size-8 items-center justify-center rounded-full bg-surface-inset text-mute transition hover:bg-hairline hover:text-ink active:scale-95 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-400"
                aria-label="Tutup struk"
                title="Tutup"
            >
                <flux:icon.x-mark class="size-4" />
            </button>
        </div>

        {{-- Loading Skeleton State --}}
        <template x-if="memuat">
            <div class="py-10 text-center space-y-3">
                <div class="inline-flex size-10 items-center justify-center rounded-full bg-primary-soft text-primary animate-spin">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <p class="text-xs font-medium text-mute">Memuat struk setoran...</p>
            </div>
        </template>

        {{-- Error State --}}
        <template x-if="galat">
            <div class="py-8 text-center space-y-3">
                <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-950/40 dark:text-red-400">
                    <flux:icon.exclamation-triangle class="size-6" />
                </div>
                <p class="text-sm font-semibold text-ink dark:text-zinc-100" x-text="galat"></p>
                <button
                    type="button"
                    @click="tutup()"
                    class="inline-flex h-9 items-center justify-center rounded-full border border-hairline px-4 text-xs font-semibold text-body hover:bg-surface-inset"
                >
                    Tutup
                </button>
            </div>
        </template>

        {{-- Receipt Content --}}
        <template x-if="struk && !memuat">
            <div class="mt-4">
                {{-- Physical Ticket Slip Card --}}
                <div class="relative rounded-2xl border border-hairline-warm bg-canvas-warm/80 p-4 sm:p-5 dark:border-zinc-700/60 dark:bg-zinc-800/50 shadow-xs">
                    {{-- Success Badge & Header --}}
                    <div class="text-center">
                        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-primary-soft text-primary-deep dark:bg-emerald-950/70 dark:text-emerald-300 ring-4 ring-primary-soft/60 dark:ring-emerald-950/40">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <h2 id="modal-struk-title" class="mt-2.5 text-sm font-semibold tracking-wide text-ink dark:text-zinc-100 uppercase">Struk Setoran</h2>
                        <div class="mt-1 flex items-center justify-center">
                            <span class="inline-flex items-center gap-1 rounded-full bg-surface px-2.5 py-0.5 text-xs font-mono font-medium text-mute shadow-2xs border border-hairline/80 dark:bg-surface dark:text-zinc-300 dark:border-zinc-700/60 tabular-nums">
                                <span>No.</span>
                                <span class="font-semibold text-ink dark:text-zinc-100" x-text="struk.nomor"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Nominal Amount Hero --}}
                    <div class="my-4 text-center">
                        <div class="text-[11px] font-medium uppercase tracking-wider text-mute dark:text-zinc-400">Total Setoran</div>
                        <div class="mt-0.5 text-3xl font-semibold tracking-tight text-primary dark:text-emerald-400 tabular-nums" x-text="struk.nominal_formatted"></div>
                        <div class="mt-2 flex items-center justify-center">
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-medium"
                                :class="struk.status === 'dikoreksi' ? 'bg-amber-100 text-amber-800 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-900/50' : 'bg-primary-soft text-on-primary-soft border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-900/50'"
                            >
                                <span class="size-1.5 rounded-full" :class="struk.status === 'dikoreksi' ? 'bg-amber-600 dark:bg-amber-400' : 'bg-primary dark:bg-emerald-400'"></span>
                                <span x-text="struk.status_label"></span>
                            </span>
                        </div>
                        <template x-if="struk.status === 'dikoreksi' && struk.nominal_asli_formatted">
                            <p class="mt-1.5 text-xs text-mute dark:text-zinc-400">
                                Dikoreksi dari <span class="font-semibold line-through text-ink/70 dark:text-zinc-300" x-text="struk.nominal_asli_formatted"></span>
                            </p>
                        </template>
                    </div>

                    {{-- Ticket Perforation Notch / Divider --}}
                    <div class="relative -mx-4 sm:-mx-5 my-4 flex items-center" aria-hidden="true">
                        <div class="size-4 -ml-2 rounded-r-full bg-surface dark:bg-surface border-y border-r border-hairline/80 dark:border-zinc-700/80"></div>
                        <div class="w-full border-t border-dashed border-hairline-strong/60 dark:border-zinc-600/70"></div>
                        <div class="size-4 -mr-2 rounded-l-full bg-surface dark:bg-surface border-y border-l border-hairline/80 dark:border-zinc-700/80"></div>
                    </div>

                    {{-- Details Key-Value List --}}
                    <dl class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-mute dark:text-zinc-400">Tanggal</dt>
                            <dd class="font-semibold text-ink dark:text-zinc-100 text-right tabular-nums" x-text="struk.tanggal"></dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-mute dark:text-zinc-400">Nasabah</dt>
                            <dd class="font-semibold text-ink dark:text-zinc-100 text-right truncate max-w-[200px]" x-text="struk.nasabah"></dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-mute dark:text-zinc-400">Produk</dt>
                            <dd class="font-semibold text-ink dark:text-zinc-100 text-right truncate max-w-[200px]" x-text="struk.produk"></dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-mute dark:text-zinc-400">Kolektor</dt>
                            <dd class="font-semibold text-ink dark:text-zinc-100 text-right truncate max-w-[200px]" x-text="struk.kolektor"></dd>
                        </div>
                    </dl>

                    {{-- Closing Note --}}
                    <div class="mt-4 pt-3 border-t border-dashed border-hairline/80 dark:border-zinc-700/60 text-center">
                        <p class="text-xs font-medium text-mute dark:text-zinc-400">Terima kasih sudah menabung.</p>
                        <p class="mt-0.5 text-[11px] text-mute/70 dark:text-zinc-500">Bukti transaksi sah yang tercatat di sistem.</p>
                    </div>
                </div>

                {{-- Modal Action Buttons --}}
                <div class="mt-4 flex gap-2.5">
                    <button
                        type="button"
                        @click="cetak()"
                        class="flex h-11 min-h-[44px] flex-1 items-center justify-center gap-2 rounded-full border border-hairline-strong bg-surface text-xs font-semibold text-ink transition hover:bg-surface-inset active:scale-95 dark:bg-zinc-800 dark:text-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-700"
                    >
                        <flux:icon.printer class="size-4 text-mute dark:text-zinc-400" />
                        <span>Cetak</span>
                    </button>
                    <button
                        type="button"
                        @click="bagikan()"
                        class="flex h-11 min-h-[44px] flex-1 items-center justify-center gap-2 rounded-full bg-primary text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-95"
                    >
                        <flux:icon.share class="size-4" />
                        <span>Bagikan</span>
                    </button>
                </div>

                <button
                    type="button"
                    @click="tutup()"
                    class="mt-2.5 flex h-10 w-full items-center justify-center rounded-full text-xs font-semibold text-mute transition hover:text-ink hover:bg-surface-inset active:scale-95 dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-800"
                >
                    Tutup
                </button>
            </div>
        </template>
    </div>

    {{-- Toast feedback --}}
    <div
        x-show="toastTampil"
        style="display: none;"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-60 rounded-full bg-ink px-4 py-2 text-xs font-medium text-white shadow-lg dark:bg-zinc-100 dark:text-zinc-900 pointer-events-none"
        x-text="toastPesan"
    ></div>
</div>
