<div class="mx-auto max-w-2xl pb-12">
    {{-- Header --}}
    <div class="mb-4 flex items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Verifikasi penarikan</h1>
            <p class="mt-0.5 text-xs text-mute">Serahkan uang, lalu minta nasabah mengetik PIN-nya sendiri.</p>
        </div>
        <a href="{{ route('kolektor.penarikan-offline.index') }}" wire:navigate
           class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3.5 py-1.5 text-xs font-semibold text-ink transition hover:bg-inset active:scale-95 dark:bg-surface">
            <flux:icon.arrow-left class="size-3.5" aria-hidden="true" />
            <span>Kembali</span>
        </a>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-2xl border border-hairline bg-primary-soft px-4 py-3 text-xs font-semibold text-on-primary-soft shadow-xs">
            <flux:icon.check-circle class="size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-2xl border border-danger-soft bg-danger-soft px-4 py-3 text-xs font-semibold text-danger-deep shadow-xs dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.x-circle class="size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Petunjuk Alur Verifikasi --}}
    <div class="mb-4 flex items-start gap-2.5 rounded-2xl border border-info-soft bg-info-soft px-3.5 py-3 text-xs font-medium text-info">
        <flux:icon.information-circle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
        <span>Serahkan uang tunai terlebih dahulu, lalu serahkan HP ke nasabah untuk memasukkan 6 angka PIN konfirmasi.</span>
    </div>

    {{-- Daftar Penarikan Menunggu Verifikasi PIN (Prototype #vl style) --}}
    <div class="space-y-2.5">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-ink">Menunggu verifikasi PIN</h2>
            <span class="text-xs font-medium text-mute">{{ $penarikan->total() }} pengajuan</span>
        </div>

        <div class="overflow-hidden rounded-2xl border border-hairline bg-white shadow-xs dark:bg-surface divide-y divide-hairline">
            @forelse($penarikan as $item)
                <div class="flex min-h-16 w-full items-center justify-between gap-3 p-3.5 transition hover:bg-inset">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft" aria-hidden="true">
                            <flux:icon.shield-check class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-xs font-semibold text-ink sm:text-sm">
                                    {{ $item->nasabah->name ?? '-' }}
                                </p>
                                <x-ui.status-chip class="shrink-0" domain="penarikan" status="{{ $item->status }}" />
                            </div>
                            <p class="mt-0.5 truncate text-xs text-mute">
                                <x-ui.rupiah :nilai="$item->nominal_diterima" /> · {{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor utama' : 'Rumah kolektor' }}
                                · <span class="opacity-75">{{ $item->produk->nama ?? '-' }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0">
                        <button type="button" wire:click="bukaModal({{ $item->id }})"
                                class="inline-flex h-8 items-center rounded-full bg-primary px-3 text-xs font-semibold text-on-primary transition hover:bg-primary-hover active:scale-95 shadow-xs">
                            Verifikasi PIN
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-mute">
                    <flux:icon.shield-check class="mx-auto mb-2 size-8 text-mute" aria-hidden="true" />
                    <p class="font-semibold text-ink">Tidak ada penarikan yang perlu diverifikasi.</p>
                    <p class="mt-0.5 text-mute">Semua pengajuan penarikan telah selesai atau belum ada pengajuan baru.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination --}}
    @if($penarikan->hasPages())
        <div class="mt-4">{{ $penarikan->links() }}</div>
    @endif

    {{-- Modal Verifikasi PIN (Prototype #pin style) --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-0 sm:items-center sm:p-4"
             role="dialog" aria-modal="true">
            <div class="fixed inset-0" wire:click.self="tutupModal" aria-hidden="true"></div>

            <div class="relative w-full max-w-sm rounded-t-[28px] border-t border-hairline bg-surface p-5 sm:p-6 shadow-md dark:bg-surface sm:rounded-[24px] sm:border max-h-[92vh] overflow-y-auto z-10" @click.stop>
                {{-- Mobile drag handle pill --}}
                <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-hairline-strong/60 sm:hidden" aria-hidden="true"></div>

                <div class="flex items-center justify-between pb-3 border-b border-hairline/80">
                    <div>
                        <h3 class="text-base font-semibold text-ink dark:text-zinc-100">Verifikasi PIN</h3>
                        <p class="text-xs text-mute dark:text-zinc-400">Minta nasabah mengetik PIN 6 angka</p>
                    </div>
                    <button type="button" wire:click="tutupModal" aria-label="Tutup"
                            class="flex size-8 shrink-0 items-center justify-center rounded-full bg-surface-inset text-mute transition hover:bg-hairline hover:text-ink active:scale-95 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-400">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </div>

                <form wire:submit="konfirmasi" class="mt-3.5 space-y-3.5"
                      x-data="{
                          pin: @entangle('pin').live,
                          appendDigit(d) {
                              if ((this.pin || '').length < 6) {
                                  this.pin = (this.pin || '') + '' + d;
                                  if (this.pin.length === 6) {
                                      $wire.konfirmasi();
                                  }
                              }
                          },
                          deleteDigit() {
                              this.pin = (this.pin || '').slice(0, -1);
                          }
                      }">
                    @if(isset($penarikanAktif) && $penarikanAktif)
                        <div class="rounded-xl border border-hairline bg-inset p-3 text-xs space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-mute">Nasabah</span>
                                <span class="font-semibold text-ink">{{ $penarikanAktif->nasabah?->name ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-mute">Nominal diserahkan</span>
                                <span class="font-semibold text-ink"><x-ui.rupiah :nilai="$penarikanAktif->nominal_diterima" /></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-mute">Lokasi</span>
                                <span class="font-medium text-mute">{{ $penarikanAktif->lokasi_pengambilan === 'kantor' ? 'Kantor utama' : 'Rumah kolektor' }}</span>
                            </div>
                        </div>
                    @endif

                    <div>
                        <p class="text-center text-xs text-mute">Minta nasabah mengetik PIN pada keypad di bawah:</p>

                        {{-- 6 Titik PIN Sesuai Prototipe --}}
                        <div class="my-3 flex justify-center gap-3">
                            <template x-for="i in 6" :key="i">
                                <span class="size-4 rounded-full border-2 transition-all duration-150"
                                      :class="i <= (pin?.length || 0) ? 'border-primary bg-primary scale-110' : 'border-hairline-strong bg-inset'"></span>
                            </template>
                        </div>

                        {{-- Keypad Virtual 3x4 Sesuai Prototipe --}}
                        <div class="my-2.5 grid grid-cols-3 gap-2">
                            @foreach([1,2,3,4,5,6,7,8,9] as $k)
                                <button type="button" @click="appendDigit({{ $k }})"
                                        class="flex h-12 items-center justify-center rounded-xl border border-hairline bg-white text-lg font-semibold tabular-nums text-ink shadow-xs transition hover:bg-inset active:scale-95 dark:bg-surface">
                                    {{ $k }}
                                </button>
                            @endforeach
                            <span></span>
                            <button type="button" @click="appendDigit(0)"
                                    class="flex h-12 items-center justify-center rounded-xl border border-hairline bg-white text-lg font-semibold tabular-nums text-ink shadow-xs transition hover:bg-inset active:scale-95 dark:bg-surface">
                                0
                            </button>
                            <button type="button" @click="deleteDigit()" aria-label="Hapus digit"
                                    class="flex h-12 items-center justify-center rounded-xl border border-hairline bg-white text-base font-semibold text-mute shadow-xs transition hover:bg-inset active:scale-95 dark:bg-surface">
                                ⌫
                            </button>
                        </div>

                        <input type="password" inputmode="numeric" maxlength="6" wire:model="pin" x-model="pin"
                            class="sr-only" aria-label="PIN nasabah" autocomplete="off" />

                        @error('pin')
                            <p class="mt-2 text-center text-xs font-semibold text-danger-deep">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2.5 pt-1">
                        <button type="button" wire:click="tutupModal"
                            class="flex h-11 w-1/3 items-center justify-center rounded-full border border-hairline-strong bg-white text-xs font-semibold text-ink transition hover:bg-inset dark:bg-surface">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex h-11 w-2/3 items-center justify-center rounded-full bg-primary text-xs font-semibold text-on-primary transition hover:bg-primary-hover active:scale-95 disabled:opacity-50">
                            <span wire:loading.remove wire:target="konfirmasi">Konfirmasi</span>
                            <span wire:loading wire:target="konfirmasi">Memeriksa PIN...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
