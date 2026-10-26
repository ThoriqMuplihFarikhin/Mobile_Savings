<div class="mx-auto max-w-2xl pb-24">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-ink">Setor ke kantor</h1>
                <p class="mt-1 text-sm text-mute">Ajukan penyerahan kas fisik ke kantor pusat.</p>
            </div>
        </div>

        {{-- Segmented tab switcher --}}
        <div class="mt-4 flex rounded-2xl bg-inset p-1">
            <a href="{{ route('kolektor.setoran.index') }}" wire:navigate
               class="flex-1 rounded-xl py-2 text-center text-sm font-medium text-mute transition hover:text-ink">
                Input Setoran
            </a>
            <span class="flex-1 rounded-xl border border-hairline bg-white py-2 text-center text-sm font-medium text-ink dark:bg-surface">
                Setor ke Kantor
            </span>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if($menungguVerifikasi)
        <div class="mb-5 rounded-xl border border-warning-soft bg-warning-soft p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex gap-3">
                    <flux:icon.clock class="mt-0.5 size-5 shrink-0 text-warning-deep" aria-hidden="true" />
                    <p class="text-sm font-medium text-warning-deep">Pengajuan setoran kas Anda sedang menunggu verifikasi admin.</p>
                </div>
                <button wire:click="batalkan({{ $pengajuanPending->id }})"
                    wire:confirm="Yakin ingin membatalkan pengajuan setoran ini? Seluruh setoran akan kembali belum disetor."
                    class="shrink-0 rounded-full bg-danger px-3 py-1.5 text-xs font-medium text-white transition hover:opacity-90">
                    Batalkan
                </button>
            </div>
        </div>
    @endif
    @if (session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-xl border border-hairline bg-primary-soft px-4 py-3 text-sm font-medium text-on-primary-soft">
            <flux:icon.check-circle class="size-5 shrink-0" aria-hidden="true" />
            <span class="flex-1">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-5 flex items-center gap-3 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm font-medium text-danger-deep dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.x-circle class="size-5 shrink-0" aria-hidden="true" />
            <span class="flex-1">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Ringkasan Kas --}}
    <div class="mb-6 rounded-2xl border border-hairline bg-white p-5 dark:bg-surface">
        <h2 class="mb-4 text-sm font-medium text-mute">
            Ringkasan kas
        </h2>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-hairline bg-inset p-4">
                <p class="text-xs text-mute">Setoran masuk</p>
                <p class="mt-1 text-xl font-semibold text-ink">
                    <x-ui.rupiah :nilai="$totalSetoranMasuk" />
                </p>
            </div>
            <div class="rounded-xl border border-hairline bg-inset p-4">
                <p class="text-xs text-mute">Penarikan tunai</p>
                <p class="mt-1 text-xl font-semibold text-ink">
                    <x-ui.rupiah :nilai="$totalPenarikanTunai" />
                </p>
            </div>
            <div class="rounded-xl border border-hairline bg-inset p-4">
                <p class="text-xs text-mute">Total seharusnya</p>
                <p class="mt-1 text-xl font-semibold text-ink">
                    <x-ui.rupiah :nilai="$totalBelumDisetor" />
                </p>
            </div>
            <div class="rounded-xl border border-hairline bg-inset p-4">
                <p class="text-xs text-mute">Jumlah transaksi</p>
                <p class="mt-1 text-xl font-semibold tabular-nums text-ink">
                    {{ $jumlahTransaksi }}
                    <span class="text-xs font-normal text-mute">transaksi</span>
                </p>
            </div>
        </div>

        @if($rincianLangsung['belum']['jumlah'] + $rincianLangsung['sesuai']['jumlah'] + $rincianLangsung['bermasalah']['jumlah'] > 0)
            <div class="mt-4 rounded-xl border border-warning-soft bg-warning-soft px-4 py-3">
                <p class="text-sm font-semibold text-warning-deep">Rincian penarikan offline langsung kolektor ini</p>
                <p class="mt-1.5 text-sm leading-6 text-warning-deep">
                    belum ditinjau {{ $rincianLangsung['belum']['jumlah'] }} (<x-ui.rupiah :nilai="$rincianLangsung['belum']['nominal']" />)
                    &middot; sesuai {{ $rincianLangsung['sesuai']['jumlah'] }} (<x-ui.rupiah :nilai="$rincianLangsung['sesuai']['nominal']" />)
                    &middot; bermasalah {{ $rincianLangsung['bermasalah']['jumlah'] }} (<x-ui.rupiah :nilai="$rincianLangsung['bermasalah']['nominal']" />)
                </p>
            </div>
        @endif

        @if($jumlahTransaksi > 0)
            <div class="mt-4">
                <label class="mb-1.5 block text-sm font-medium text-body" for="catatan-serah-kas">Catatan penyerahan kas (opsional)</label>
                <textarea wire:model="catatan" rows="2" id="catatan-serah-kas"
                    class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-3 text-base text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"
                    placeholder="Contoh: Diserahkan tunai ke kasir Mbak Rina"></textarea>
                @error('catatan') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
            </div>
            <button wire:click="submit" wire:confirm="Yakin ingin mengajukan setoran kas sebesar Rp {{ number_format($totalBelumDisetor, 0, ',', '.') }} ke kantor?"
                class="mt-4 flex h-12 w-full items-center justify-center gap-2 rounded-full bg-primary text-base font-medium text-on-primary transition hover:bg-primary-hover active:scale-[0.98]">
                Ajukan setoran ke kantor
            </button>
        @else
            <div class="mt-4 rounded-xl border border-hairline bg-primary-soft px-4 py-3 text-center">
                <p class="flex items-center justify-center gap-2 text-sm font-medium text-on-primary-soft">
                    <flux:icon.check-circle class="size-5 shrink-0" aria-hidden="true" />
                    Semua setoran kas telah diserahkan dan tidak ada kas tertahan.
                </p>
            </div>
        @endif
    </div>

    {{-- Riwayat Setor ke Kantor --}}
    <div class="overflow-hidden rounded-2xl border border-hairline bg-white dark:bg-surface">
        <div class="border-b border-hairline px-5 py-4">
            <h2 class="text-sm font-medium text-ink">Riwayat setor ke kantor</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-hairline bg-inset text-xs font-medium text-mute">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3 text-right">Seharusnya</th>
                        <th class="px-4 py-3 text-right">Diterima</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($riwayat as $item)
                        <tr class="transition-colors hover:bg-inset">
                            <td class="px-4 py-3.5 font-medium text-ink">
                                {{ $item->tanggal_setor->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-semibold text-ink">
                                <x-ui.rupiah :nilai="$item->total_seharusnya" />
                            </td>
                            <td class="px-4 py-3.5 text-right text-body">
                                @if($item->total_diterima !== null)
                                    <x-ui.rupiah :nilai="$item->total_diterima" />
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <x-ui.status-chip domain="setor_kas" status="{{ $item->status }}" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-mute">
                                Belum ada riwayat penyerahan kas ke kantor.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($riwayat->hasPages())
            <div class="border-t border-hairline px-4 py-3">{{ $riwayat->links() }}</div>
        @endif
    </div>
</div>
