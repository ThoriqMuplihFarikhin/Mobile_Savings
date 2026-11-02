<div
    x-data="{
        buka: false,
        memuat: false,
        galat: false,
        url: '',
        judul: 'Foto Bukti',
        keterangan: '',
        bukaFoto(detail) {
            this.url = detail.url || '';
            this.judul = detail.judul || 'Foto Bukti';
            this.keterangan = detail.keterangan || '';
            this.memuat = true;
            this.galat = false;
            this.buka = true;
        },
        tutup() {
            this.buka = false;
            this.url = '';
            this.memuat = false;
            this.galat = false;
        },
        bukaUkuranAsli() {
            if (this.url) {
                window.open(this.url, '_blank');
            }
        }
    }"
    x-on:buka-foto-bukti.window="bukaFoto($event.detail)"
    x-on:keydown.escape.window="tutup()"
    x-show="buka"
    style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-3 sm:p-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-foto-bukti-title"
    x-transition:enter="ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    data-test="modal-foto-bukti-popup"
>
    {{-- Backdrop click area to dismiss --}}
    <div class="fixed inset-0" @click="tutup()" aria-hidden="true"></div>

    {{-- Modal Dialog Panel --}}
    <div
        class="relative w-full max-w-lg rounded-[24px] border border-hairline bg-surface p-4 shadow-md dark:bg-surface sm:p-5 max-h-[92vh] flex flex-col"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
        @click.stop
    >
        {{-- Header --}}
        <div class="flex items-center justify-between pb-3 border-b border-hairline gap-3 shrink-0">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-primary-soft text-primary dark:bg-zinc-800 dark:text-zinc-200">
                        <flux:icon.photo class="size-4" />
                    </span>
                    <h3 id="modal-foto-bukti-title" class="text-sm font-semibold text-ink dark:text-zinc-100 truncate" x-text="judul"></h3>
                </div>
                <template x-if="keterangan">
                    <p class="mt-0.5 text-xs text-mute truncate pl-9" x-text="keterangan"></p>
                </template>
            </div>
            <button
                type="button"
                @click="tutup()"
                class="flex size-8 shrink-0 items-center justify-center rounded-full bg-surface-inset text-mute transition hover:bg-hairline hover:text-ink active:scale-95 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-400"
                aria-label="Tutup foto"
                title="Tutup"
            >
                <flux:icon.x-mark class="size-4" />
            </button>
        </div>

        {{-- Image Display Area --}}
        <div class="relative my-3 flex-1 overflow-hidden rounded-2xl bg-zinc-950/5 dark:bg-zinc-950/50 flex items-center justify-center min-h-[220px] max-h-[66vh] p-2 border border-hairline/60">
            {{-- Loading Spinner --}}
            <div x-show="memuat" class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-surface/80 dark:bg-surface/80 z-10">
                <div class="inline-flex size-9 items-center justify-center rounded-full bg-primary-soft text-primary animate-spin">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium text-mute">Memuat foto bukti...</span>
            </div>

            {{-- Error State --}}
            <div x-show="galat" style="display: none;" class="py-8 text-center space-y-2">
                <div class="mx-auto flex size-10 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-950/40 dark:text-red-400">
                    <flux:icon.exclamation-triangle class="size-5" />
                </div>
                <p class="text-xs font-semibold text-danger">Gagal memuat foto bukti.</p>
                <button
                    type="button"
                    @click="bukaUkuranAsli()"
                    class="inline-flex h-8 items-center justify-center rounded-full border border-hairline px-3 text-xs font-medium text-body hover:bg-surface-inset"
                >
                    Coba buka langsung
                </button>
            </div>

            {{-- Actual Image --}}
            <img
                :src="url"
                :alt="judul"
                x-show="!galat"
                x-on:load="memuat = false"
                x-on:error="memuat = false; galat = true"
                class="max-h-[62vh] w-auto max-w-full rounded-xl object-contain shadow-xs transition-opacity duration-200"
                :class="memuat ? 'opacity-0' : 'opacity-100'"
            />
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-end gap-2 pt-1 shrink-0">
            <button
                type="button"
                @click="bukaUkuranAsli()"
                class="flex h-10 items-center justify-center gap-1.5 rounded-full border border-hairline-strong bg-surface px-4 text-xs font-semibold text-ink transition hover:bg-surface-inset active:scale-95 dark:bg-zinc-800 dark:text-zinc-200"
                title="Buka gambar di tab baru untuk ukuran penuh"
            >
                <flux:icon.arrow-top-right-on-square class="size-3.5" />
                <span>Ukuran Asli</span>
            </button>
            <button
                type="button"
                @click="tutup()"
                class="flex h-10 items-center justify-center rounded-full bg-primary px-5 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-95"
            >
                Tutup
            </button>
        </div>
    </div>
</div>
