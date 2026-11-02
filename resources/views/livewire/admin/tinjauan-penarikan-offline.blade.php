<div class="space-y-5">
    {{-- Header Halaman --}}
    <x-admin.page-header
        breadcrumb="Keuangan"
        title="Tinjauan Penarikan Offline"
        subtitle="Tinjau pasca-fakta penarikan langsung oleh kolektor."
    />

    {{-- Banner Notifikasi Flash --}}
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-primary/20 bg-primary-soft px-4 py-3 text-xs font-medium text-on-primary-soft" data-test="flash-sukses">
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-danger/20 bg-danger-soft px-4 py-3 text-xs font-medium text-danger" data-test="flash-error">
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Strip KPI (D25) --}}
    <x-admin.kpi-grid cols="3">
        <x-admin.kpi-card
            label="Belum ditinjau"
            :value="$hitungKpi['belum'] ?? 0"
            sub="Perlu tindakan"
            :variant="($hitungKpi['belum'] ?? 0) > 0 ? 'warn' : 'default'"
        />
        <x-admin.kpi-card
            label="Sesuai"
            :value="$hitungKpi['sesuai'] ?? 0"
        />
        <x-admin.kpi-card
            label="Bermasalah"
            :value="$hitungKpi['bermasalah'] ?? 0"
            :variant="($hitungKpi['bermasalah'] ?? 0) > 0 ? 'error' : 'default'"
        />
    </x-admin.kpi-grid>

    {{-- Card Container Tabel & Filter --}}
    <div class="rounded-[14px] border border-hairline bg-surface shadow-xs overflow-hidden">
        {{-- Toolbar Filter Segmented Tab --}}
        <div class="p-3 border-b border-hairline flex flex-wrap items-center gap-2" data-test="baris-filter">
            <div class="inline-flex items-center gap-1 rounded-[10px] border border-hairline bg-surface-2 p-[3px]" role="group" aria-label="Status Tinjauan">
                @php
                    $statusTabs = [
                        'semua' => 'Semua',
                        'belum' => 'Belum ditinjau',
                        'sesuai' => 'Sesuai',
                        'bermasalah' => 'Bermasalah',
                    ];
                @endphp
                @foreach ($statusTabs as $value => $label)
                    <button
                        type="button"
                        wire:click="$set('statusFilter', '{{ $value }}')"
                        class="min-h-11 inline-flex items-center justify-center rounded-[7px] px-3 py-1 text-xs transition cursor-pointer {{ $statusFilter === $value ? 'bg-surface text-ink font-semibold shadow-xs' : 'text-mute hover:text-ink font-medium' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Tabel Desktop --}}
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-surface-2 text-xs uppercase tracking-[0.04em] text-mute font-medium border-b border-hairline">
                        <th class="px-4 py-2.5">Waktu</th>
                        <th class="px-4 py-2.5">Kolektor</th>
                        <th class="px-4 py-2.5">Nasabah / Produk</th>
                        <th class="px-4 py-2.5 text-right">Nominal</th>
                        <th class="px-4 py-2.5">Lokasi</th>
                        <th class="px-4 py-2.5">Catatan & Bukti</th>
                        <th class="px-4 py-2.5 text-center">Tinjauan</th>
                        <th class="px-4 py-2.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse ($penarikans as $item)
                        <tr class="hover:bg-surface-2 transition text-ink" data-test="baris-tinjauan-{{ $item->id }}">
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-mute tabular-nums">
                                @if($item->waktu_pencairan)
                                    @if($item->waktu_pencairan->isToday())
                                        Hari ini {{ $item->waktu_pencairan->format('H.i') }}
                                    @elseif($item->waktu_pencairan->isYesterday())
                                        Kemarin {{ $item->waktu_pencairan->format('H.i') }}
                                    @else
                                        {{ $item->waktu_pencairan->translatedFormat('d M H.i') }}
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-ink">
                                {{ $item->dibayarOleh->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</div>
                                @if($item->produk)
                                    <div class="text-xs text-mute mt-0.5">{{ $item->produk->nama }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-xs font-medium text-ink">
                                Rp {{ number_format((float) $item->nominal_diminta, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-body">
                                <div>{{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}</div>
                                <div class="text-[11px] {{ $item->mempengaruhi_kas ? 'text-danger font-medium' : 'text-mute' }}">
                                    {{ $item->mempengaruhi_kas ? 'mengurangi kas' : 'tanpa dampak kas' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-body">
                                <div class="max-w-[200px] truncate">{{ $item->catatan_kolektor ?? '—' }}</div>
                                @if ($item->bukti_path)
                                    <a
                                        href="{{ route('penarikan.bukti', $item) }}"
                                        @click.prevent="$dispatch('buka-foto-bukti', { url: '{{ route('penarikan.bukti', $item) }}', judul: 'Bukti Penarikan Offline', keterangan: '{{ $item->nasabah?->name ?? 'Nasabah' }}' })"
                                        class="text-xs font-medium text-primary hover:underline cursor-pointer inline-block mt-0.5"
                                    >
                                        Lihat bukti
                                    </a>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                @if ($item->hasil_tinjauan === null)
                                    <x-admin.status-chip status="Belum ditinjau" />
                                @elseif ($item->hasil_tinjauan === 'sesuai')
                                    <x-admin.status-chip status="Sesuai" />
                                @else
                                    <x-admin.status-chip status="Bermasalah" />
                                @endif
                                @if ($item->ditinjau_oleh)
                                    <div class="mt-0.5 text-[11px] text-mute">oleh {{ $item->ditinjauOleh->name ?? '-' }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        wire:click="bukaDetail({{ $item->id }})"
                                        class="min-h-11 h-8 inline-flex items-center justify-center rounded-[8px] border border-hairline bg-surface px-2.5 text-xs font-medium text-ink transition hover:bg-surface-2 active:scale-95 cursor-pointer shadow-xs"
                                    >
                                        Detail
                                    </button>
                                    @if ($item->hasil_tinjauan === null)
                                        <button
                                            type="button"
                                            wire:click="sesuai({{ $item->id }})"
                                            wire:confirm="Tandai penarikan ini sesuai?"
                                            class="min-h-11 h-8 inline-flex items-center justify-center rounded-[8px] bg-primary px-3 text-xs font-semibold text-white transition hover:bg-primary-hover active:scale-95 cursor-pointer shadow-xs"
                                        >
                                            Sesuai
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="pilihTinjauan({{ $item->id }})"
                                            class="min-h-11 h-8 inline-flex items-center justify-center rounded-[8px] bg-danger-soft px-3 text-xs font-semibold text-danger transition hover:bg-danger hover:text-white active:scale-95 cursor-pointer"
                                        >
                                            Bermasalah
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-sm text-mute" data-test="tinjauan-kosong">
                                Tidak ada penarikan offline pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Kartu Mobile (di bawah md) --}}
        <div class="space-y-3 p-4 md:hidden">
            @forelse ($penarikans as $item)
                <div class="rounded-[14px] border border-hairline bg-surface p-4 shadow-xs space-y-3"
                    data-test="kartu-tinjauan-{{ $item->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-sm text-ink">{{ $item->nasabah->name ?? '-' }}</p>
                            <p class="text-xs text-mute mt-0.5">
                                {{ $item->dibayarOleh->name ?? '-' }} · 
                                @if($item->waktu_pencairan)
                                    {{ $item->waktu_pencairan->format('d/m/Y H:i') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <span class="shrink-0 tabular-nums font-semibold text-ink">Rp {{ number_format((float) $item->nominal_diminta, 0, ',', '.') }}</span>
                    </div>

                    <div class="text-xs text-ink bg-surface-2 p-2.5 rounded-[8px] border border-hairline space-y-0.5">
                        <div class="text-mute">{{ $item->produk->nama ?? '-' }} · {{ $item->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}</div>
                        <div class="font-medium">{{ $item->catatan_kolektor ?? '—' }}</div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs pt-1">
                        <div>
                            @if ($item->bukti_path)
                                <a
                                    href="{{ route('penarikan.bukti', $item) }}"
                                    @click.prevent="$dispatch('buka-foto-bukti', { url: '{{ route('penarikan.bukti', $item) }}', judul: 'Bukti Penarikan Offline', keterangan: '{{ $item->nasabah?->name ?? 'Nasabah' }}' })"
                                    class="font-medium text-primary hover:underline cursor-pointer"
                                >
                                    Lihat bukti
                                </a>
                            @endif
                        </div>
                        <div>
                            @if ($item->hasil_tinjauan === null)
                                <x-admin.status-chip status="Belum ditinjau" />
                            @elseif ($item->hasil_tinjauan === 'sesuai')
                                <x-admin.status-chip status="Sesuai" />
                            @else
                                <x-admin.status-chip status="Bermasalah" />
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-hairline">
                        <button
                            type="button"
                            wire:click="bukaDetail({{ $item->id }})"
                            class="min-h-11 flex-1 h-9 inline-flex items-center justify-center rounded-[8px] border border-hairline bg-surface px-3 text-xs font-medium text-ink transition hover:bg-surface-2 active:scale-95 cursor-pointer shadow-xs"
                        >
                            Detail
                        </button>
                        @if ($item->hasil_tinjauan === null)
                            <button
                                type="button"
                                wire:click="sesuai({{ $item->id }})"
                                wire:confirm="Tandai penarikan ini sesuai?"
                                class="min-h-11 flex-1 h-9 inline-flex items-center justify-center rounded-[8px] bg-primary px-3 text-xs font-semibold text-white transition hover:bg-primary-hover active:scale-95 cursor-pointer shadow-xs"
                            >
                                Sesuai
                            </button>
                            <button
                                type="button"
                                wire:click="pilihTinjauan({{ $item->id }})"
                                class="min-h-11 flex-1 h-9 inline-flex items-center justify-center rounded-[8px] bg-danger-soft px-3 text-xs font-semibold text-danger transition hover:bg-danger hover:text-white active:scale-95 cursor-pointer"
                            >
                                Bermasalah
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-[14px] border border-hairline bg-surface p-6 text-center text-sm text-mute" data-test="kartu-tinjauan-kosong">
                    Tidak ada penarikan offline pada filter ini.
                </div>
            @endforelse
        </div>

        @if ($penarikans->count() > 0)
            <div class="flex items-center justify-between border-t border-hairline px-4 py-2.5 text-xs text-mute bg-surface">
                <span>Menampilkan {{ $penarikans->count() }} dari {{ $penarikans->total() }} data</span>
                @if ($penarikans->hasPages())
                    <div>{{ $penarikans->links() }}</div>
                @else
                    <span>Halaman 1 dari 1</span>
                @endif
            </div>
        @endif
    </div>

    {{-- Modal Dialog Tandai Bermasalah --}}
    @if ($terpilihId !== null)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-label="Tandai bermasalah">
            <div class="fixed inset-0 bg-black/45 transition-opacity cursor-pointer" wire:click="batalTinjauan" aria-hidden="true"></div>

            <div class="relative w-full max-w-[460px] rounded-[16px] border border-hairline bg-surface p-5 shadow-2xl transition-colors z-10 my-auto" data-test="form-bermasalah">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-ink leading-tight">Tandai Bermasalah</h3>
                        <p class="mt-1 text-xs text-mute leading-relaxed">Wajib mengisi alasan minimal 10 karakter.</p>
                    </div>
                    <button
                        type="button"
                        wire:click="batalTinjauan"
                        class="flex size-7 shrink-0 items-center justify-center rounded-[8px] text-mute transition hover:bg-surface-2 hover:text-ink cursor-pointer"
                        aria-label="Tutup dialog"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-3.5 text-left">
                    <div>
                        <label for="catatan-tinjauan" class="mb-1 block text-xs font-medium text-ink">Alasan</label>
                        <textarea
                            id="catatan-tinjauan"
                            wire:model="catatanTinjauan"
                            rows="3"
                            maxlength="255"
                            placeholder="mis. nominal tidak cocok dengan bukti tanda tangan"
                            class="w-full rounded-[10px] border border-hairline bg-surface p-2.5 text-xs text-ink placeholder-mute focus:border-danger focus:outline-none focus:ring-1 focus:ring-danger"
                        ></textarea>
                        @error('catatanTinjauan')
                            <p class="mt-1 text-xs text-danger font-medium" data-test="error-tinjauan">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-start gap-2 text-xs text-ink cursor-pointer">
                        <input type="checkbox" wire:model="bekukanKolektor" class="mt-0.5 size-4 rounded border-hairline text-danger focus:ring-danger">
                        <span>Bekukan penarikan offline langsung kolektor ini (disarankan).</span>
                    </label>
                </div>

                <div class="mt-5 flex items-center justify-end gap-2 pt-2 border-t border-hairline/60">
                    <button
                        type="button"
                        wire:click="batalTinjauan"
                        class="min-h-11 h-[38px] rounded-[10px] border border-hairline bg-surface px-4 text-xs font-medium text-ink transition hover:bg-surface-2 cursor-pointer"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="bermasalah({{ $terpilihId }})"
                        data-test="submit-bermasalah"
                        class="min-h-11 h-[38px] rounded-[10px] bg-danger px-4 text-xs font-semibold text-white transition hover:bg-danger-hover cursor-pointer"
                    >
                        Simpan Bermasalah
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Drawer Detail Penarikan Offline --}}
    @if($showDetail && $detailPenarikan)
        {{-- Backdrop Scrim --}}
        <div class="fixed inset-0 bg-black/40 z-40 transition-opacity" wire:click="tutupDetail"></div>

        {{-- Drawer Slide-over Panel --}}
        <aside class="fixed inset-y-0 right-0 z-50 w-full max-w-[500px] bg-surface border-l border-hairline shadow-2xl flex flex-col transition-transform">
            {{-- Header Drawer --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-hairline">
                <div>
                    <h3 class="text-base font-semibold text-ink">Detail Penarikan Offline</h3>
                    <p class="text-xs text-mute mt-0.5">ID Transaksi: #{{ $detailPenarikan->id }}</p>
                </div>
                <button
                    type="button"
                    wire:click="tutupDetail"
                    class="flex size-8 items-center justify-center rounded-[8px] border border-hairline text-mute hover:text-ink hover:bg-surface-2 transition cursor-pointer"
                    aria-label="Tutup"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Isi Drawer --}}
            <div class="p-5 overflow-y-auto flex-1 space-y-5 text-sm">
                {{-- 1. Status Tinjauan --}}
                <div class="flex items-center justify-between p-3.5 rounded-[12px] bg-surface-2 border border-hairline">
                    <div>
                        <span class="text-xs text-mute block">Status Tinjauan</span>
                        <b class="text-sm font-semibold text-ink">
                            @if($detailPenarikan->hasil_tinjauan === 'sesuai')
                                Sesuai
                            @elseif($detailPenarikan->hasil_tinjauan === 'bermasalah')
                                Bermasalah
                            @else
                                Belum ditinjau
                            @endif
                        </b>
                    </div>
                    @if ($detailPenarikan->hasil_tinjauan === null)
                        <x-admin.status-chip status="Belum ditinjau" />
                    @elseif ($detailPenarikan->hasil_tinjauan === 'sesuai')
                        <x-admin.status-chip status="Sesuai" />
                    @else
                        <x-admin.status-chip status="Bermasalah" />
                    @endif
                </div>

                {{-- 2. Data Nasabah --}}
                <div class="space-y-1">
                    <h4 class="text-xs uppercase tracking-[0.05em] text-mute font-medium mb-1">Data Nasabah</h4>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Nama</span>
                        <b class="font-medium text-ink">{{ $detailPenarikan->nasabah->name ?? '-' }}</b>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">No. HP</span>
                        <b class="font-medium text-ink tabular-nums">{{ $detailPenarikan->nasabah->no_hp ?? '— (offline)' }}</b>
                    </div>
                    @if($detailPenarikan->nasabah?->nasabahProfil?->alamat)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">Alamat</span>
                            <span class="font-medium text-ink text-right text-xs max-w-[240px]">{{ $detailPenarikan->nasabah->nasabahProfil->alamat }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between gap-3 py-2 border-b-0 text-sm">
                        <span class="text-mute">Mode Akses</span>
                        <x-admin.status-chip domain="mode_akses" :status="$detailPenarikan->nasabah?->mode_akses ?? 'offline'" :teks="ucfirst($detailPenarikan->nasabah?->mode_akses ?? 'Offline')" />
                    </div>
                </div>

                {{-- 3. Transaksi & Keuangan --}}
                <div class="space-y-1">
                    <h4 class="text-xs uppercase tracking-[0.05em] text-mute font-medium mb-1">Rincian Penarikan</h4>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Produk Tabungan</span>
                        <b class="font-medium text-ink">{{ $detailPenarikan->produk->nama ?? '-' }}</b>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Nominal Dibayarkan</span>
                        <b class="font-semibold text-primary text-base tabular-nums">Rp {{ number_format((float) $detailPenarikan->nominal_diminta, 0, ',', '.') }}</b>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b-0 text-sm">
                        <span class="text-mute">Dampak Kas</span>
                        <span class="text-xs font-semibold {{ $detailPenarikan->mempengaruhi_kas ? 'text-danger' : 'text-mute' }}">
                            {{ $detailPenarikan->mempengaruhi_kas ? 'Mengurangi Kas Kolektor' : 'Tanpa Dampak Kas' }}
                        </span>
                    </div>
                </div>

                {{-- 4. Operasional Lapangan --}}
                <div class="space-y-1">
                    <h4 class="text-xs uppercase tracking-[0.05em] text-mute font-medium mb-1">Operasional Lapangan</h4>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Kolektor Pembayar</span>
                        <b class="font-medium text-ink">{{ $detailPenarikan->dibayarOleh->name ?? '-' }}</b>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Lokasi Penyerahan</span>
                        <span class="font-medium text-ink">{{ $detailPenarikan->lokasi_pengambilan === 'kantor' ? 'Kantor' : 'Rumah Kolektor' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                        <span class="text-mute">Waktu Pencairan</span>
                        <span class="text-ink font-medium tabular-nums">{{ optional($detailPenarikan->waktu_pencairan)->format('d/m/Y H:i') ?? '-' }}</span>
                    </div>
                    @if($detailPenarikan->ditinjauOleh)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">Ditinjau Oleh</span>
                            <span class="text-ink font-medium">{{ $detailPenarikan->ditinjauOleh->name }}</span>
                        </div>
                    @endif
                    @if($detailPenarikan->ditinjau_pada)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">Waktu Tinjauan</span>
                            <span class="text-ink font-medium tabular-nums">{{ $detailPenarikan->ditinjau_pada->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                    @if($detailPenarikan->catatan_tinjauan)
                        <div class="flex flex-col gap-1 py-2 border-b border-dashed border-hairline text-sm">
                            <span class="text-mute">Catatan Tinjauan Admin:</span>
                            <p class="text-xs text-danger bg-danger-soft p-2.5 rounded-[8px]">{{ $detailPenarikan->catatan_tinjauan }}</p>
                        </div>
                    @endif
                    @if($detailPenarikan->catatan_kolektor)
                        <div class="flex flex-col gap-1 py-2 border-b-0 text-sm">
                            <span class="text-mute">Catatan Kolektor:</span>
                            <p class="text-xs text-ink bg-surface-2 p-2.5 rounded-[8px]">{{ $detailPenarikan->catatan_kolektor }}</p>
                        </div>
                    @endif
                </div>

                {{-- 5. Lampiran Bukti --}}
                @if($detailPenarikan->bukti_path)
                    <div class="space-y-2 pt-2 border-t border-hairline">
                        <h4 class="text-xs uppercase tracking-[0.05em] text-mute font-medium">Foto Bukti Penarikan</h4>
                        <a
                            href="{{ route('penarikan.bukti', $detailPenarikan) }}"
                            @click.prevent="$dispatch('buka-foto-bukti', { url: '{{ route('penarikan.bukti', $detailPenarikan) }}', judul: 'Bukti Penarikan Offline', keterangan: '{{ $detailPenarikan->nasabah?->name ?? 'Nasabah' }}' })"
                            class="inline-flex items-center gap-1.5 rounded-[10px] border border-hairline bg-surface px-3 py-2 text-xs font-medium text-ink transition hover:bg-surface-2 cursor-pointer shadow-xs"
                        >
                            <svg class="size-4 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            Buka Foto Bukti Penarikan
                        </a>
                    </div>
                @endif
            </div>

            {{-- Footer Drawer Aksi --}}
            <div class="p-4 border-t border-hairline bg-surface flex items-center justify-end gap-2">
                @if ($detailPenarikan->hasil_tinjauan === null)
                    <button
                        type="button"
                        wire:click="sesuai({{ $detailPenarikan->id }})"
                        wire:confirm="Tandai penarikan ini sesuai?"
                        class="min-h-11 h-9 inline-flex items-center justify-center rounded-[8px] bg-primary px-4 text-xs font-semibold text-white transition hover:bg-primary-hover active:scale-95 cursor-pointer shadow-xs"
                    >
                        Sesuai
                    </button>
                    <button
                        type="button"
                        wire:click="pilihTinjauan({{ $detailPenarikan->id }})"
                        class="min-h-11 h-9 inline-flex items-center justify-center rounded-[8px] bg-danger-soft px-4 text-xs font-semibold text-danger transition hover:bg-danger hover:text-white active:scale-95 cursor-pointer"
                    >
                        Bermasalah
                    </button>
                @endif
                <button
                    type="button"
                    wire:click="tutupDetail"
                    class="min-h-11 h-9 inline-flex items-center justify-center rounded-[8px] border border-hairline bg-surface px-4 text-xs font-medium text-ink transition hover:bg-surface-2 active:scale-95 cursor-pointer shadow-xs"
                >
                    Tutup
                </button>
            </div>
        </aside>
    @endif
</div>
