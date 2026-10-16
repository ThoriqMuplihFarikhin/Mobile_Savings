<div>
@if($pengajuan->isNotEmpty())
    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-xl bg-primary-soft p-3 text-sm font-medium text-on-primary-soft">
            <flux:icon.check-circle class="size-5 shrink-0 text-on-primary-soft" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 rounded-xl bg-danger-soft p-3 text-sm font-medium text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.exclamation-triangle class="size-5 shrink-0 text-danger-deep dark:text-dark-danger-text" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
        <div class="mb-3 flex items-center justify-between border-b border-hairline-warm pb-2.5">
            <h3 class="text-sm font-semibold text-ink">Pengajuan Aktif</h3>
            <a href="{{ route('nasabah.riwayat.index') }}" wire:navigate class="text-xs font-medium text-primary hover:underline">
                Lihat Riwayat
            </a>
        </div>

        <div class="space-y-3">
            @foreach($pengajuan as $item)
                <div class="rounded-[14px] border border-hairline-warm p-3.5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-semibold text-ink">{{ $item->produk->nama ?? '-' }}</h4>
                            <p class="mt-0.5 text-xs text-mute">
                                {{ $item->lokasi_pengambilan === 'kantor' ? 'Ambil di Kantor' : 'Via Rumah Kolektor' }}
                                &middot; diajukan {{ $item->created_at->translatedFormat('d M Y') }}
                            </p>
                        </div>
                        <x-ui.status-chip class="shrink-0" domain="penarikan" status="{{ $item->status }}" />
                    </div>

                    <div class="mt-3 flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold">
                            <x-ui.rupiah :nilai="$item->nominal_diminta" />
                        </p>
                        <button type="button"
                                wire:click="batalkan({{ $item->id }})"
                                wire:confirm="Yakin membatalkan pengajuan penarikan ini? Saldo yang sudah dipotong akan dikembalikan."
                                class="rounded-full bg-danger px-3 py-1.5 text-xs font-medium text-white transition hover:opacity-90">
                            Batalkan Pengajuan
                        </button>
                    </div>

                    <div class="mt-2.5">
                        <input type="text" wire:model="alasanBatal" maxlength="255"
                               placeholder="Alasan membatalkan (opsional)"
                               class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-2 text-xs text-ink transition placeholder:text-subtle focus:border-danger focus:outline-none focus:ring-2 focus:ring-danger/20 dark:bg-surface" />
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
</div>
