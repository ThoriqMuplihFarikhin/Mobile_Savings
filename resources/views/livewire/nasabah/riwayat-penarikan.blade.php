<div class="space-y-6">
    {{-- Judul halaman --}}
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Riwayat penarikan</h1>
        <p class="mt-1 text-sm text-mute">Daftar seluruh pengajuan penarikan saldo Anda.</p>
    </div>

    {{-- Pesan sistem --}}
    @if (session('success'))
        <div class="flex items-start gap-2 rounded-xl bg-primary-soft p-3 text-xs text-on-primary-soft">
            <flux:icon.check-circle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-start gap-2 rounded-xl bg-danger-soft p-3 text-xs text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text">
            <flux:icon.exclamation-triangle class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Ringkasan statistik cepat --}}
    @php
        $totalDitarik = $penarikan->where('status', 'selesai')->sum('nominal_diterima');
        $totalDiajukan = $penarikan->whereIn('status', ['pending', 'approved'])->sum('nominal_diminta');
    @endphp
    @if($penarikan->isNotEmpty())
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-2xl border border-hairline-warm bg-white p-3.5 dark:bg-surface">
                <span class="text-xs text-mute">Selesai ditarik</span>
                <p class="mt-1 text-base font-semibold text-primary">
                    <x-ui.rupiah :nilai="$totalDitarik" />
                </p>
            </div>
            <div class="rounded-2xl border border-hairline-warm bg-white p-3.5 dark:bg-surface">
                <span class="text-xs text-mute">Sedang diajukan</span>
                <p class="mt-1 text-base font-semibold text-info">
                    <x-ui.rupiah :nilai="$totalDiajukan" />
                </p>
            </div>
        </div>
    @endif

    {{-- Daftar penarikan --}}
    <div class="space-y-3">
        @forelse($penarikan as $item)
            @php
                /**
                 * DESIGN.md §7 "Penanda tahap (stepper)": tahap dihitung dari
                 * status penarikan tanpa query baru. `dibatalkan` dan
                 * `kedaluwarsa` belum punya desain tahap — tampil chip netral
                 * saja; `ditolak` memakai dua tahap dengan titik merah tanpa
                 * centang.
                 */
                $tahapSaatIni = match ($item->status) {
                    'pending' => 0,
                    'approved' => 1,
                    'selesai' => 2,
                    default => null,
                };
            @endphp
            <article class="rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface" data-test="kartu-riwayat-penarikan">
                <div class="flex items-start gap-3">
                    <x-ui.tile kategori="biru" ukuran="40" aria-hidden="true">
                        <flux:icon.arrow-up-tray class="size-4" />
                    </x-ui.tile>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-sm font-semibold text-ink truncate">{{ $item->produk->nama ?? '-' }}</h2>
                        <p class="mt-0.5 text-xs text-mute">
                            {{ $item->created_at->translatedFormat('d M Y, H:i') }}
                            · {{ $item->lokasi_pengambilan === 'kantor' ? 'Ambil di kantor' : 'Via rumah kolektor' }}
                        </p>
                    </div>
                    <x-ui.status-chip class="shrink-0" domain="penarikan" status="{{ $item->status }}" />
                </div>

                @if ($tahapSaatIni !== null)
                    <x-ui.stepper class="mt-4" :tahap="['Diajukan', 'Disetujui', 'Selesai']" :saat-ini="$tahapSaatIni" />
                @elseif ($item->status === 'ditolak')
                    <x-ui.stepper class="mt-4" :tahap="['Diajukan', 'Ditolak']" :saat-ini="1" :ditolak="true" />
                @endif

                {{-- Rincian nominal (DESIGN.md §8) --}}
                <div class="mt-4 space-y-2 rounded-xl bg-surface-inset p-3.5 border border-hairline">
                    <div class="flex items-center justify-between gap-3 border-b border-hairline pb-2">
                        <span class="text-xs text-body">Nominal diminta</span>
                        <span class="text-sm font-semibold tabular-nums text-ink">
                            <x-ui.rupiah :nilai="$item->nominal_diminta" />
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-b border-hairline pb-2">
                        <span class="text-xs text-body">Biaya admin</span>
                        <span class="text-sm font-semibold tabular-nums">
                            <x-ui.rupiah :nilai="$item->nominal_komisi" tanda="-" />
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-3 pt-0.5">
                        <span class="text-xs font-semibold text-ink">Total diterima</span>
                        <span class="text-sm font-semibold tabular-nums text-ink">
                            <x-ui.rupiah :nilai="$item->nominal_diterima" />
                        </span>
                    </div>
                </div>

                @if (in_array($item->status, ['pending', 'approved'], true))
                    <div class="mt-4 border-t border-hairline-warm pt-3">
                        <label for="alasan-batal-{{ $item->id }}" class="block text-xs font-semibold text-body">Alasan pembatalan (opsional)</label>
                        <input type="text" id="alasan-batal-{{ $item->id }}" wire:model.defer="alasanBatal" class="mt-1 w-full rounded-xl border border-hairline-strong bg-white px-3 py-2 text-xs text-ink placeholder:text-mute focus:border-danger focus:outline-none focus:ring-2 focus:ring-danger/20 dark:bg-surface" placeholder="Contoh: dana tidak jadi dipakai" />
                        <button type="button" wire:click="batalkan({{ $item->id }})" wire:confirm="Batalkan pengajuan penarikan ini?" class="mt-3 inline-flex h-10 items-center justify-center rounded-full border border-danger/40 bg-white px-4 text-xs font-medium text-danger transition hover:bg-danger-soft active:scale-95 dark:bg-surface">
                            Batalkan
                        </button>
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-[20px] border border-hairline-warm bg-white p-8 text-center shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                <span class="mx-auto flex size-32 items-center justify-center rounded-full bg-primary-soft" aria-hidden="true">
                    <x-ui.ilustrasi nama="tunas" :ukuran="96" />
                </span>
                <p class="mt-4 text-base font-semibold text-ink">Belum ada riwayat penarikan.</p>
                <p class="mt-1 text-xs text-mute">Setiap pengajuan penarikan yang Anda buat akan tercatat di sini.</p>
            </div>
        @endforelse
    </div>

    @if ($penarikan->hasPages())
        <div>{{ $penarikan->links() }}</div>
    @endif
</div>
