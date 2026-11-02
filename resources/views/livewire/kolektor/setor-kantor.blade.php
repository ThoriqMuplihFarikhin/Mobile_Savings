<div class="mx-auto max-w-2xl pb-24">
    {{-- Top Bar & Header --}}
    <div class="mb-5">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('kolektor.setoran.index') }}" wire:navigate
                   class="flex size-10 shrink-0 items-center justify-center rounded-full border border-hairline bg-white text-ink transition hover:bg-inset active:scale-95 dark:bg-surface"
                   aria-label="Kembali ke input setoran">
                    <flux:icon.chevron-left class="size-5" />
                </a>
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-ink">Setor ke kantor</h1>
                    <p class="mt-0.5 text-xs text-mute">Ajukan penyerahan kas fisik ke kantor pusat.</p>
                </div>
            </div>
        </div>

        {{-- Segmented Tab Switcher --}}
        <div class="mt-4 flex rounded-2xl bg-inset p-1">
            <a href="{{ route('kolektor.setoran.index') }}" wire:navigate
               class="flex flex-1 items-center justify-center gap-1.5 rounded-xl py-2.5 text-center text-sm font-medium text-mute transition hover:text-ink">
                <flux:icon.arrow-down-tray class="size-4" />
                Input Setoran
            </a>
            <span class="flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-hairline bg-white py-2.5 text-center text-sm font-semibold text-ink shadow-xs dark:bg-surface">
                <flux:icon.building-office-2 class="size-4 text-primary" />
                Setor ke Kantor
            </span>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if($menungguVerifikasi)
        <div class="mb-4 rounded-2xl border border-warning-soft bg-warning-soft p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex gap-3">
                    <div class="shrink-0 rounded-full bg-white/60 p-2 dark:bg-surface/60" aria-hidden="true">
                        <flux:icon.clock class="size-5 text-warning-deep" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-warning-deep">Pengajuan setor kas sedang diverifikasi</p>
                        <p class="mt-0.5 text-xs text-warning-deep">Admin kasir sedang merekonsiliasi fisik kas Anda di kantor.</p>
                    </div>
                </div>
                <button wire:click="batalkan({{ $pengajuanPending->id }})"
                    wire:confirm="Yakin ingin membatalkan pengajuan setoran ini? Seluruh setoran akan kembali belum disetor."
                    class="shrink-0 rounded-full border border-hairline-strong bg-white px-3 py-1.5 text-xs font-semibold text-danger transition hover:bg-inset active:scale-95 dark:bg-surface">
                    Batalkan
                </button>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-hairline bg-primary-soft px-4 py-3 text-sm font-medium text-on-primary-soft">
            <flux:icon.check-circle class="size-5 shrink-0" aria-hidden="true" />
            <span class="flex-1">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-danger-soft bg-danger-soft px-4 py-3 text-sm font-medium text-danger-deep dark:border-dark-danger-soft dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.x-circle class="size-5 shrink-0" aria-hidden="true" />
            <span class="flex-1">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Main Reconciliation Card --}}
    <div class="mb-5 overflow-hidden rounded-2xl border border-hairline bg-white dark:bg-surface">
        {{-- Card Header: Total Kas Bersih --}}
        <div class="bg-primary p-6 text-center text-white">
            <span class="text-xs font-medium text-white/80">Total seharusnya disetor</span>
            <div class="mt-1 text-3xl font-semibold tabular-nums text-white">
                <x-ui.rupiah :nilai="$totalBelumDisetor" />
            </div>
            <p class="mt-2 text-xs text-white/70">
                {{ $jumlahTransaksi }} transaksi terkumpul di lapangan
            </p>
        </div>

        {{-- Itemized Breakdown List --}}
        <div class="p-5">
            <h2 class="mb-3 text-xs font-semibold text-mute">
                RINCIAN KAS
            </h2>

            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 text-mute">
                        <flux:icon.arrow-down-tray class="size-4 text-primary" />
                        Setoran masuk
                    </span>
                    <span class="font-semibold tabular-nums text-ink">
                        <x-ui.rupiah :nilai="$totalSetoranMasuk" />
                    </span>
                </div>

                <div class="flex items-center justify-between border-t border-hairline pt-3 text-sm">
                    <span class="flex items-center gap-2 text-mute">
                        <flux:icon.arrow-up-tray class="size-4 text-warning-deep" />
                        Penarikan tunai
                    </span>
                    <span class="font-semibold tabular-nums text-ink">
                        <x-ui.rupiah :nilai="$totalPenarikanTunai" tanda="-" />
                    </span>
                </div>

                <div class="flex items-center justify-between border-t border-hairline pt-3 text-sm">
                    <span class="flex items-center gap-2 text-mute">
                        <flux:icon.calculator class="size-4 text-primary" />
                        Total seharusnya
                    </span>
                    <span class="font-semibold tabular-nums text-primary">
                        <x-ui.rupiah :nilai="$totalBelumDisetor" />
                    </span>
                </div>

                <div class="flex items-center justify-between border-t border-hairline pt-3 text-sm">
                    <span class="flex items-center gap-2 text-mute">
                        <flux:icon.document-text class="size-4 text-mute" />
                        Jumlah transaksi
                    </span>
                    <span class="font-medium tabular-nums text-body">
                        {{ $jumlahTransaksi }} transaksi
                    </span>
                </div>
            </div>

            @if($rincianLangsung['belum']['jumlah'] + $rincianLangsung['sesuai']['jumlah'] + $rincianLangsung['bermasalah']['jumlah'] > 0)
                <div class="mt-4 rounded-xl border border-warning-soft bg-warning-soft p-3.5">
                    <p class="text-xs font-semibold text-warning-deep">Rincian penarikan offline langsung kolektor ini</p>
                    <p class="mt-1 text-xs leading-5 text-warning-deep">
                        belum ditinjau {{ $rincianLangsung['belum']['jumlah'] }} (<x-ui.rupiah :nilai="$rincianLangsung['belum']['nominal']" />)
                        &middot; sesuai {{ $rincianLangsung['sesuai']['jumlah'] }} (<x-ui.rupiah :nilai="$rincianLangsung['sesuai']['nominal']" />)
                        &middot; bermasalah {{ $rincianLangsung['bermasalah']['jumlah'] }} (<x-ui.rupiah :nilai="$rincianLangsung['bermasalah']['nominal']" />)
                    </p>
                </div>
            @endif
        </div>

        {{-- Form & Action Area --}}
        @if($jumlahTransaksi > 0)
            <div class="border-t border-hairline bg-inset/40 p-5 space-y-3.5">
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-body" for="catatan-serah-kas">Catatan penyerahan kas (opsional)</label>
                    <textarea wire:model="catatan" rows="2" id="catatan-serah-kas"
                        class="w-full rounded-xl border border-hairline-strong bg-white px-3.5 py-2.5 text-sm text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"
                        placeholder="Contoh: Diserahkan tunai ke kasir Mbak Rina"></textarea>
                    @error('catatan') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
                </div>
                <button wire:click="submit" wire:confirm="Yakin ingin mengajukan setoran kas sebesar Rp {{ number_format($totalBelumDisetor, 0, ',', '.') }} ke kantor?"
                    class="flex h-12 w-full items-center justify-center gap-2 rounded-full bg-primary text-base font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-[0.98]">
                    <flux:icon.building-office-2 class="size-5" />
                    Ajukan setoran ke kantor
                </button>
            </div>
        @else
            <div class="border-t border-hairline bg-primary-soft/40 p-4 text-center">
                <p class="flex items-center justify-center gap-2 text-xs font-medium text-on-primary-soft">
                    <flux:icon.check-circle class="size-4 shrink-0 text-primary" aria-hidden="true" />
                    Semua setoran kas telah diserahkan dan tidak ada kas tertahan.
                </p>
            </div>
        @endif
    </div>

    {{-- Riwayat Setor ke Kantor --}}
    <div class="overflow-hidden rounded-2xl border border-hairline bg-white dark:bg-surface">
        <div class="flex items-center justify-between border-b border-hairline px-4.5 py-3.5">
            <h2 class="text-sm font-semibold text-ink">Riwayat setor ke kantor</h2>
            <span class="rounded-full bg-inset px-2.5 py-0.5 text-xs font-semibold text-body">
                {{ $riwayat->total() }} riwayat
            </span>
        </div>

        {{-- Mobile Cards List (Visible on Small Screens) --}}
        <div class="divide-y divide-hairline sm:hidden">
            @forelse($riwayat as $item)
                <div class="p-4 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-ink">
                            {{ $item->tanggal_setor->translatedFormat('d M Y') }}
                        </span>
                        <x-ui.status-chip domain="setor_kas" status="{{ $item->status }}" />
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-mute">Seharusnya</span>
                        <span class="font-semibold tabular-nums text-ink">
                            <x-ui.rupiah :nilai="$item->total_seharusnya" />
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-mute">Diterima kasir</span>
                        <span class="font-medium tabular-nums text-body">
                            @if($item->total_diterima !== null)
                                <x-ui.rupiah :nilai="$item->total_diterima" />
                            @else
                                &mdash;
                            @endif
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-sm text-mute">
                    Belum ada riwayat penyerahan kas ke kantor.
                </div>
            @endforelse
        </div>

        {{-- Desktop Table (Visible on sm and up) --}}
        <div class="hidden overflow-x-auto sm:block">
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
                            <td class="px-4 py-3.5 text-right font-semibold tabular-nums text-ink">
                                <x-ui.rupiah :nilai="$item->total_seharusnya" />
                            </td>
                            <td class="px-4 py-3.5 text-right tabular-nums text-body">
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
