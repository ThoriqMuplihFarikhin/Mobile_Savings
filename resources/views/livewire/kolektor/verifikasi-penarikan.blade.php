<div class="mx-auto max-w-2xl pb-8">
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Verifikasi penarikan</h1>
            <p class="mt-1 text-sm text-mute">Serahkan uang dan verifikasi dengan PIN nasabah.</p>
        </div>
        <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft" aria-hidden="true">
            <flux:icon.shield-check class="size-5" />
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-hairline bg-primary-soft px-4 py-3 text-sm font-medium text-on-primary-soft">
            <flux:icon.check-circle class="size-5 shrink-0" aria-hidden="true" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm font-medium text-danger-deep dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.x-circle class="size-5 shrink-0" aria-hidden="true" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Info Banner --}}
    <div class="mb-4 flex items-start gap-3 rounded-xl border border-info-soft bg-info-soft px-4 py-3 text-sm font-medium text-info">
        <flux:icon.information-circle class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
        <span>Serahkan HP ke nasabah agar PIN diketik sendiri. Verifikasi ini membuktikan nasabah telah menerima uang.</span>
    </div>

    {{-- Card List --}}
    <div class="space-y-3">
        @forelse($penarikan as $item)
            <div class="overflow-hidden rounded-2xl border border-hairline bg-white dark:bg-surface">
                <div class="p-4">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-base font-semibold text-ink">{{ $item->nasabah->name ?? '-' }}</p>
                            <p class="mt-0.5 truncate text-sm tabular-nums text-mute">{{ $item->nasabah->no_hp ?? '— (offline)' }}</p>
                        </div>
                        <x-ui.status-chip class="shrink-0" domain="penarikan" status="{{ $item->status }}" />
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-mute">Produk</span>
                            <span class="text-right font-medium text-ink">{{ $item->produk->nama ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-mute">Nominal diserahkan</span>
                            <span class="text-right font-semibold text-ink"><x-ui.rupiah :nilai="$item->nominal_diterima" /></span>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-mute">Lokasi</span>
                            <span class="text-right font-medium text-ink">{{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah kolektor' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-mute">Diajukan</span>
                            <span class="text-right text-body">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button wire:click="bukaModal({{ $item->id }})"
                            class="flex h-12 w-full items-center justify-center gap-2 rounded-full bg-primary text-base font-medium text-on-primary transition hover:bg-primary-hover active:scale-[0.98]">
                            Serahkan dan verifikasi PIN
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-hairline bg-white p-12 dark:bg-surface">
                <div class="flex flex-col items-center gap-2 text-center">
                    <flux:icon.shield-check class="size-10 text-mute" aria-hidden="true" />
                    <p class="text-sm text-mute">Tidak ada penarikan yang perlu diverifikasi.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($penarikan->hasPages())
        <div class="mt-4">{{ $penarikan->links() }}</div>
    @endif

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 dark:bg-black/70" wire:click.self="tutupModal"></div>

            <div class="relative w-full max-w-sm overflow-hidden rounded-2xl border border-hairline bg-white dark:bg-surface">
                <div class="bg-primary px-5 py-5 text-center text-on-primary">
                    <div class="mx-auto mb-2 flex size-12 items-center justify-center rounded-full bg-white/20" aria-hidden="true">
                        <flux:icon.finger-print class="size-6" />
                    </div>
                    <p class="text-base font-semibold">Verifikasi PIN nasabah</p>
                    <p class="mt-0.5 text-xs">Minta nasabah mengetik PIN-nya di bawah ini.</p>
                </div>

                <form wire:submit="konfirmasi" class="space-y-4 p-5">
                    <div>
                        <label class="mb-2 block text-center text-sm font-medium text-body">PIN nasabah (6 digit)</label>
                        <input type="password" inputmode="numeric" maxlength="6" wire:model="pin"
                            class="w-full rounded-xl border border-hairline-strong bg-inset py-4 text-center text-2xl font-semibold tracking-[0.4em] text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition"
                            placeholder="------" autocomplete="off" />
                        @error('pin')
                            <p class="mt-2 text-center text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" wire:click="tutupModal"
                            class="flex h-12 w-1/3 items-center justify-center rounded-full border border-hairline-strong bg-white text-base font-medium text-ink transition hover:bg-inset dark:bg-surface">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex h-12 w-2/3 items-center justify-center rounded-full bg-primary text-base font-medium text-on-primary transition hover:bg-primary-hover active:scale-[0.98] disabled:opacity-50">
                            <span wire:loading.remove wire:target="konfirmasi">Verifikasi</span>
                            <span wire:loading wire:target="konfirmasi">Memverifikasi...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
