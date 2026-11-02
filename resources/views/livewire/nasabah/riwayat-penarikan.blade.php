<div class="flex flex-col gap-4 pb-6">
    {{-- Header navigasi kembali & judul --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" wire:navigate
           aria-label="Kembali ke Beranda"
           class="flex size-11 shrink-0 items-center justify-center rounded-full border border-hairline bg-surface text-ink transition hover:bg-surface-inset active:scale-95">
            <flux:icon.arrow-left class="size-5" />
        </a>
        <div class="min-w-0 flex-1">
            <h1 class="text-xl font-semibold tracking-tight text-ink leading-tight">Riwayat penarikan</h1>
            <p class="text-xs text-mute truncate">Daftar seluruh pengajuan penarikan saldo Anda.</p>
        </div>
    </div>

    {{-- Segmented Switcher Tab --}}
    <div class="flex rounded-full bg-surface-inset p-1 border border-hairline">
        <a href="{{ route('nasabah.riwayat-tabungan.index') }}" wire:navigate
           class="flex-1 rounded-full py-2 text-center text-xs font-medium text-mute hover:text-ink transition dark:hover:text-zinc-200">
            Tabungan
        </a>
        <a href="{{ route('nasabah.riwayat.index') }}" wire:navigate
           class="flex-1 rounded-full py-2 text-center text-xs font-semibold bg-white text-primary shadow-xs dark:bg-surface dark:text-primary-soft">
            Penarikan
        </a>
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

    {{-- Ringkasan statistik cepat --}}
    @php
        $totalDitarik = $totalDitarik ?? $penarikan->where('status', 'selesai')->sum('nominal_diterima');
        $totalDiajukan = $totalDiajukan ?? $penarikan->whereIn('status', ['pending', 'approved'])->sum('nominal_diminta');
        $pengajuanAktif = $penarikan->whereIn('status', ['pending', 'approved']);
        $riwayatSelesai = $penarikan->whereNotIn('status', ['pending', 'approved']);
    @endphp
    @if($penarikan->isNotEmpty())
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-2xl border border-hairline bg-white p-3.5 shadow-xs dark:bg-surface">
                <span class="text-xs text-mute">Selesai ditarik</span>
                <p class="mt-1 text-base font-semibold text-primary">
                    <x-ui.rupiah :nilai="$totalDitarik" />
                </p>
            </div>
            <div class="rounded-2xl border border-hairline bg-white p-3.5 shadow-xs dark:bg-surface">
                <span class="text-xs text-mute">Sedang diajukan</span>
                <p class="mt-1 text-base font-semibold text-info">
                    <x-ui.rupiah :nilai="$totalDiajukan" />
                </p>
            </div>
        </div>
    @endif

    {{-- Pengajuan Aktif --}}
    @if($pengajuanAktif->isNotEmpty())
        <div class="flex flex-col gap-3">
            <div class="flex items-center justify-between px-1">
                <h2 class="text-sm font-semibold text-ink">Pengajuan Aktif</h2>
                <span class="rounded-full bg-info-soft px-2.5 py-0.5 text-xs font-medium text-info">
                    {{ $pengajuanAktif->count() }} pengajuan
                </span>
            </div>

            @foreach($pengajuanAktif as $item)
                @php
                    $tahapSaatIni = match ($item->status) {
                        'pending' => 0,
                        'approved' => 1,
                        'selesai' => 2,
                        default => null,
                    };
                @endphp
                <article class="rounded-2xl border border-hairline bg-white p-4 shadow-xs dark:bg-surface" data-test="kartu-riwayat-penarikan">
                    <div class="flex items-start gap-3">
                        <x-ui.tile kategori="biru" ukuran="40" aria-hidden="true">
                            <flux:icon.arrow-up-tray class="size-4" />
                        </x-ui.tile>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-semibold text-ink truncate">{{ $item->produk->nama ?? '-' }}</h3>
                            <p class="mt-0.5 text-xs text-mute">
                                {{ $item->created_at->translatedFormat('d M Y, H:i') }}
                                · {{ $item->lokasi_pengambilan === 'kantor' ? 'Ambil di kantor' : 'Via rumah kolektor' }}
                            </p>
                        </div>
                        <x-ui.status-chip class="shrink-0" domain="penarikan" status="{{ $item->status }}" />
                    </div>

                    @if ($tahapSaatIni !== null)
                        <x-ui.stepper class="mt-4" :tahap="['Diajukan', 'Disetujui', 'Selesai']" :saat-ini="$tahapSaatIni" />
                    @endif

                    {{-- Rincian nominal (DESIGN.md §8) --}}
                    <div class="mt-4 space-y-2 rounded-2xl bg-surface-inset p-3.5 border border-hairline">
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

                    <div class="mt-4 border-t border-hairline pt-3" x-data="{ bukaBatal: false }">
                        <div x-show="!bukaBatal" class="flex items-center justify-between">
                            <span class="text-xs text-mute">Ingin membatalkan pengajuan ini?</span>
                            <button type="button" @click="bukaBatal = true"
                                class="inline-flex min-h-9 items-center justify-center rounded-full border border-danger/30 bg-danger-soft px-3.5 text-xs font-medium text-danger transition hover:bg-danger/10 active:scale-95">
                                Batalkan
                            </button>
                        </div>
                        <div x-show="bukaBatal" x-cloak class="space-y-2.5 pt-1">
                            <label for="alasan-batal-{{ $item->id }}" class="block text-xs font-medium text-body">
                                Alasan pembatalan (opsional)
                            </label>
                            <input type="text" id="alasan-batal-{{ $item->id }}"
                                wire:model.defer="alasanBatal"
                                class="w-full rounded-xl border border-hairline-strong bg-white px-3 py-2 text-xs text-ink placeholder:text-mute focus:border-danger focus:outline-none focus:ring-2 focus:ring-danger/20 dark:bg-surface"
                                placeholder="Contoh: dana tidak jadi dipakai" />
                            <div class="flex items-center gap-2">
                                <button type="button"
                                    wire:click="batalkan({{ $item->id }})"
                                    wire:confirm="Batalkan pengajuan penarikan ini?"
                                    class="inline-flex min-h-9 items-center justify-center rounded-full bg-danger px-4 text-xs font-medium text-white transition hover:bg-danger/90 active:scale-95">
                                    Ya, batalkan
                                </button>
                                <button type="button" @click="bukaBatal = false"
                                    class="inline-flex min-h-9 items-center justify-center rounded-full border border-hairline-strong bg-white px-4 text-xs font-medium text-mute transition hover:bg-surface-inset active:scale-95 dark:bg-surface">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    {{-- Riwayat Penarikan Selesai / Arsip --}}
    @if($riwayatSelesai->isNotEmpty())
        <div class="flex flex-col gap-3">
            <div class="px-1 {{ $pengajuanAktif->isNotEmpty() ? 'pt-1' : '' }}">
                <h2 class="text-sm font-semibold text-ink">Riwayat Penarikan</h2>
            </div>

            <div class="rounded-2xl border border-hairline bg-white px-4 divide-y divide-hairline shadow-xs dark:bg-surface">
                @foreach($riwayatSelesai as $item)
                    <div class="py-3.5" data-test="kartu-riwayat-penarikan" x-data="{ open: false }">
                        <div class="flex items-start gap-3">
                            <x-ui.tile :kategori="$item->status === 'selesai' ? 'hijau' : ($item->status === 'ditolak' || $item->status === 'dibatalkan' ? 'mawar' : 'netral')" ukuran="40" aria-hidden="true">
                                <flux:icon.arrow-up-tray class="size-4" />
                            </x-ui.tile>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-baseline justify-between gap-2">
                                    <h3 class="text-sm font-semibold text-ink truncate">{{ $item->produk->nama ?? 'Penarikan' }}</h3>
                                    <span class="text-sm font-semibold tabular-nums text-ink">
                                        <x-ui.rupiah :nilai="$item->nominal_diterima ?: $item->nominal_diminta" />
                                    </span>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center justify-between gap-2">
                                    <p class="text-xs text-mute truncate">
                                        {{ $item->created_at->translatedFormat('d M Y, H:i') }}
                                        · {{ $item->lokasi_pengambilan === 'kantor' ? 'Ambil di kantor' : 'Via rumah kolektor' }}
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <x-ui.status-chip class="shrink-0" domain="penarikan" status="{{ $item->status }}" />
                                        <button type="button" @click="open = !open"
                                            class="text-xs font-medium text-mute hover:text-ink transition"
                                            :aria-expanded="open.toString()">
                                            <span x-text="open ? 'Tutup' : 'Rincian'">Rincian</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Rincian penarikan selesai/arsip --}}
                        <div x-show="open" x-cloak class="mt-3 rounded-2xl bg-surface-inset p-3.5 border border-hairline text-xs space-y-2">
                            <div class="flex items-center justify-between border-b border-hairline pb-1.5">
                                <span class="text-mute">Nominal diminta</span>
                                <span class="font-semibold text-ink tabular-nums"><x-ui.rupiah :nilai="$item->nominal_diminta" /></span>
                            </div>
                            <div class="flex items-center justify-between border-b border-hairline pb-1.5">
                                <span class="text-mute">Biaya admin</span>
                                <span class="font-semibold tabular-nums"><x-ui.rupiah :nilai="$item->nominal_komisi" tanda="-" /></span>
                            </div>
                            <div class="flex items-center justify-between pt-0.5 font-semibold text-ink">
                                <span>Total diterima</span>
                                <span class="tabular-nums"><x-ui.rupiah :nilai="$item->nominal_diterima" /></span>
                            </div>
                            @if($item->alasan_batal)
                                <div class="border-t border-hairline pt-2 text-mute">
                                    <span class="font-medium text-ink">Alasan pembatalan:</span> {{ $item->alasan_batal }}
                                </div>
                            @endif
                            @if($item->alasan_ditolak)
                                <div class="border-t border-hairline pt-2 text-danger">
                                    <span class="font-medium">Alasan ditolak:</span> {{ $item->alasan_ditolak }}
                                </div>
                            @endif
                            @if($item->status === 'ditolak')
                                <div class="pt-2 border-t border-hairline">
                                    <x-ui.stepper :tahap="['Diajukan', 'Ditolak']" :saat-ini="1" :ditolak="true" />
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Keadaan Kosong --}}
    @if($penarikan->isEmpty())
        <div class="rounded-2xl border border-hairline bg-white p-8 text-center shadow-xs dark:bg-surface">
            <span class="mx-auto flex size-32 items-center justify-center rounded-full bg-primary-soft" aria-hidden="true">
                <x-ui.ilustrasi nama="tunas" :ukuran="96" />
            </span>
            <h3 class="mt-4 text-base font-semibold text-ink">Belum ada riwayat penarikan</h3>
            <p class="mt-1 text-xs text-mute">Setiap pengajuan penarikan yang Anda buat akan tercatat di sini.</p>
        </div>
    @endif

    @if ($penarikan->hasPages())
        <div class="pt-1">{{ $penarikan->links() }}</div>
    @endif
</div>

