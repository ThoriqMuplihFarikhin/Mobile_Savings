<div class="space-y-4" data-portal="admin">
    <style>
        @page { margin: 14mm; }
        @media print {
            body * { visibility: hidden !important; }
            .laporan-cetak, .laporan-cetak * { visibility: visible !important; }
            .laporan-cetak { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>

    <div class="laporan-cetak space-y-4">
        {{-- Print Header --}}
        <div class="mb-4 hidden print:block">
            <h2 class="text-lg font-semibold text-ink">Laporan Komisi</h2>
            <p class="text-sm text-body">Periode {{ $dariTanggal ? \Illuminate\Support\Carbon::parse($dariTanggal)->format('d/m/Y') : 'awal' }} - {{ $sampaiTanggal ? \Illuminate\Support\Carbon::parse($sampaiTanggal)->format('d/m/Y') : 'akhir' }}
                &middot; Dasar tanggal: {{ $dasarTanggal === 'waktu_pencairan' ? 'Pencairan' : 'Approval' }}
                &middot; Status: {{ $status !== '' ? $status : 'approved & selesai' }}</p>
        </div>

        {{-- Header Halaman Sesuai Prototipe & Screenshot --}}
        <div class="print:hidden">
            <x-admin.page-header
                breadcrumb="Keuangan"
                title="Laporan Komisi"
                subtitle="Rincian komisi dari penarikan yang sudah disetujui."
            >
                <x-slot:actions>
                    <button type="button" wire:click="exportCsv"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-hairline/70 bg-surface px-4 py-2 text-xs font-semibold text-ink shadow-xs transition hover:bg-surface-2 focus:outline-none">
                        Ekspor CSV
                    </button>
                </x-slot:actions>
            </x-admin.page-header>
        </div>

        {{-- Filter Toolbar Segmented Sesuai Prototipe & Screenshot --}}
        <div class="print:hidden rounded-2xl border border-hairline/60 bg-surface p-3 shadow-xs space-y-2.5">
            <div class="flex flex-wrap items-center gap-2.5">
                {{-- Segmented 1: Produk --}}
                <div class="inline-flex flex-wrap items-center rounded-xl bg-inset/50 p-1 dark:bg-surface-2 border border-hairline/40">
                    <button type="button"
                        wire:click="$set('produkId', '')"
                        class="rounded-lg px-3 py-1.5 text-xs transition {{ $produkId === '' ? 'bg-surface font-semibold text-ink shadow-xs' : 'font-medium text-mute hover:text-ink' }}">
                        Semua Produk
                    </button>
                    @foreach($produkList as $produk)
                        <button type="button"
                            wire:click="$set('produkId', '{{ $produk->id }}')"
                            class="rounded-lg px-3 py-1.5 text-xs transition {{ (string) $produkId === (string) $produk->id ? 'bg-surface font-semibold text-ink shadow-xs' : 'font-medium text-mute hover:text-ink' }}">
                            {{ $produk->nama }}
                        </button>
                    @endforeach
                </div>

                {{-- Segmented 2: Status --}}
                <div class="inline-flex items-center rounded-xl bg-inset/50 p-1 dark:bg-surface-2 border border-hairline/40">
                    <button type="button"
                        wire:click="$set('status', '')"
                        class="rounded-lg px-3 py-1.5 text-xs transition {{ $status === '' ? 'bg-surface font-semibold text-ink shadow-xs' : 'font-medium text-mute hover:text-ink' }}">
                        Semua Status
                    </button>
                    <button type="button"
                        wire:click="$set('status', 'approved')"
                        class="rounded-lg px-3 py-1.5 text-xs transition {{ $status === 'approved' ? 'bg-surface font-semibold text-ink shadow-xs' : 'font-medium text-mute hover:text-ink' }}">
                        Approved
                    </button>
                    <button type="button"
                        wire:click="$set('status', 'selesai')"
                        class="rounded-lg px-3 py-1.5 text-xs transition {{ $status === 'selesai' ? 'bg-surface font-semibold text-ink shadow-xs' : 'font-medium text-mute hover:text-ink' }}">
                        Selesai
                    </button>
                </div>

                {{-- Segmented 3: Lokasi --}}
                <div class="inline-flex items-center rounded-xl bg-inset/50 p-1 dark:bg-surface-2 border border-hairline/40">
                    <button type="button"
                        wire:click="$set('lokasiPengambilan', '')"
                        class="rounded-lg px-3 py-1.5 text-xs transition {{ $lokasiPengambilan === '' ? 'bg-surface font-semibold text-ink shadow-xs' : 'font-medium text-mute hover:text-ink' }}">
                        Semua Lokasi
                    </button>
                    <button type="button"
                        wire:click="$set('lokasiPengambilan', 'kantor')"
                        class="rounded-lg px-3 py-1.5 text-xs transition {{ $lokasiPengambilan === 'kantor' ? 'bg-surface font-semibold text-ink shadow-xs' : 'font-medium text-mute hover:text-ink' }}">
                        Kantor
                    </button>
                    <button type="button"
                        wire:click="$set('lokasiPengambilan', 'rumah_kolektor')"
                        class="rounded-lg px-3 py-1.5 text-xs transition {{ $lokasiPengambilan === 'rumah_kolektor' ? 'bg-surface font-semibold text-ink shadow-xs' : 'font-medium text-mute hover:text-ink' }}">
                        Rumah Kolektor
                    </button>
                </div>

                {{-- Link Reset Filter --}}
                <button type="button"
                    wire:click="resetFilter"
                    class="text-xs font-semibold text-primary transition hover:underline focus:outline-none ml-1">
                    Reset filter
                </button>
            </div>

            {{-- Baris 2: Periode Tanggal & Opsi Tambahan (Rapi, Terbuka & Siap Digunakan) --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-hairline/40 pt-2.5 text-xs">
                <div data-test="baris-tanggal-filter" class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-1.5 text-mute font-medium mr-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-mute shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Periode:</span>
                    </div>

                    <div class="w-36">
                        <x-ui.tanggal wire:model.live="dariTanggal"
                            placeholder="Dari tanggal"
                            class="h-[34px] w-full rounded-xl border border-hairline/60 bg-surface px-2.5 pr-8 text-xs font-medium text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20 shadow-xs" />
                    </div>
                    <span class="text-xs font-medium text-mute">&ndash;</span>
                    <div class="w-36">
                        <x-ui.tanggal wire:model.live="sampaiTanggal" :min="$dariTanggal"
                            placeholder="Sampai tanggal"
                            class="h-[34px] w-full rounded-xl border border-hairline/60 bg-surface px-2.5 pr-8 text-xs font-medium text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20 shadow-xs" />
                    </div>

                    <select wire:model.live="kolektorId"
                        class="h-[34px] rounded-xl border border-hairline/60 bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20 shadow-xs">
                        <option value="">Semua Kolektor</option>
                        @foreach($kolektorList as $kolektor)
                            <option value="{{ $kolektor->id }}">{{ $kolektor->name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="dasarTanggal" title="Dasar tanggal penentuan periode"
                        class="h-[34px] rounded-xl border border-hairline/60 bg-surface px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20 shadow-xs">
                        <option value="waktu_approval">Dasar: Tanggal Approval</option>
                        <option value="waktu_pencairan">Dasar: Tanggal Pencairan</option>
                    </select>
                </div>

                <div data-test="baris-aksi-filter" class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="exportCsv"
                        class="inline-flex h-[34px] items-center gap-1.5 rounded-xl border border-hairline/60 bg-surface px-3 text-xs font-semibold text-ink shadow-xs transition hover:bg-surface-2 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Unduh CSV</span>
                    </button>
                    <button type="button" x-on:click="window.print()"
                        class="inline-flex h-[34px] items-center gap-1.5 rounded-xl border border-hairline/60 bg-surface px-3 text-xs font-semibold text-ink shadow-xs transition hover:bg-surface-2 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-mute" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Cetak</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- 3 KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <x-admin.kpi-card
                label="Total Komisi"
                :value="'Rp ' . number_format($totalKomisi, 0, ',', '.')"
            />
            <x-admin.kpi-card
                label="Total Nominal Penarikan"
                :value="'Rp ' . number_format($totalNominalPenarikan, 0, ',', '.')"
            />
            <x-admin.kpi-card
                label="Rata-rata Komisi/Transaksi"
                :value="'Rp ' . ($totalTransaksi > 0 ? number_format($totalKomisi / $totalTransaksi, 0, ',', '.') : '0')"
            />
        </div>

        {{-- Grid 2 Kolom: Komisi per Produk & Komisi per Bulan --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Komisi per Produk --}}
            <div class="rounded-2xl border border-hairline/60 bg-surface shadow-xs overflow-hidden">
                <div class="px-5 pt-4 pb-2">
                    <h2 class="text-sm font-semibold text-ink">Komisi per Produk</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-hairline/40">
                                <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-mute">Produk</th>
                                <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-mute">Komisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline/30">
                            @forelse($komisiPerProduk as $item)
                                <tr class="transition hover:bg-inset/30">
                                    <td class="px-5 py-3.5 font-medium text-ink">{{ $item->nama }}</td>
                                    <td class="px-5 py-3.5 text-right font-semibold tabular-nums text-ink">Rp {{ number_format($item->total_komisi, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="p-8 text-center text-xs text-mute">Belum ada komisi pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Komisi per Bulan --}}
            <div class="rounded-2xl border border-hairline/60 bg-surface shadow-xs overflow-hidden">
                <div class="px-5 pt-4 pb-2">
                    <h2 class="text-sm font-semibold text-ink">Komisi per Bulan</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-hairline/40">
                                <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-mute">Bulan</th>
                                <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-mute">Transaksi</th>
                                <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-mute">Komisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline/30">
                            @forelse($rekapBulan as $item)
                                @php
                                    $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    [$tahun, $bulan] = explode('-', $item->bulan);
                                    $labelBulan = $namaBulan[(int) $bulan - 1] . ' ' . $tahun;
                                @endphp
                                <tr class="transition hover:bg-inset/30">
                                    <td class="px-5 py-3.5 font-medium text-ink">{{ $labelBulan }}</td>
                                    <td class="px-5 py-3.5 text-center tabular-nums text-body">{{ $item->jumlah_transaksi }}</td>
                                    <td class="px-5 py-3.5 text-right font-semibold tabular-nums text-ink">Rp {{ number_format($item->total_komisi, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-8 text-center text-xs text-mute">Belum ada komisi pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-komisi-bulan" class="divide-y divide-hairline/30 md:hidden">
                    @forelse($rekapBulan as $item)
                        @php
                            $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            [$tahun, $bulan] = explode('-', $item->bulan);
                            $labelBulan = $namaBulan[(int) $bulan - 1] . ' ' . $tahun;
                        @endphp
                        <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-ink">{{ $labelBulan }}</p>
                                <p class="text-[11px] text-mute">{{ $item->jumlah_transaksi }} transaksi</p>
                            </div>
                            <span class="tabular-nums text-xs font-semibold text-ink">Rp {{ number_format($item->total_komisi, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-mute">Belum ada komisi pada periode ini.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Riwayat Transaksi --}}
        <div class="rounded-2xl border border-hairline/60 bg-surface shadow-xs overflow-hidden">
            <div class="px-5 pt-4 pb-2">
                <h2 class="text-sm font-semibold text-ink">Riwayat Transaksi</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="hidden md:table w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-hairline/40">
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-mute">Tanggal</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-mute">Nasabah</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-mute">Produk</th>
                            <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-mute">Nominal</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-mute">Disetujui Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hairline/30">
                        @forelse($riwayat as $item)
                            <tr class="transition hover:bg-inset/30">
                                <td class="px-5 py-3.5 text-mute whitespace-nowrap">
                                    {{ $item->waktu_approval ? \Carbon\Carbon::parse($item->waktu_approval)->translatedFormat('d M') : '-' }}
                                </td>
                                <td class="px-5 py-3.5 font-semibold text-ink whitespace-nowrap">
                                    {{ $item->nasabah->name ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-mute whitespace-nowrap">
                                    {{ $item->produk->nama ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold tabular-nums text-ink whitespace-nowrap">
                                    Rp {{ number_format($item->nominal_komisi, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-mute whitespace-nowrap">
                                    {{ $item->disetujuiOleh->name ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-xs text-mute">Tidak ada data komisi pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div data-test="kartu-riwayat-komisi" class="divide-y divide-hairline/30 md:hidden">
                @forelse($riwayat as $item)
                    <div class="px-5 py-3.5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-ink">{{ $item->nasabah->name ?? '-' }}</p>
                                <p class="text-[11px] text-mute">
                                    {{ $item->waktu_approval ? \Carbon\Carbon::parse($item->waktu_approval)->translatedFormat('d M') : '-' }} &middot; {{ $item->produk->nama ?? '-' }}
                                </p>
                            </div>
                            <span class="tabular-nums text-xs font-semibold text-ink">Rp {{ number_format($item->nominal_komisi, 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[11px] text-mute">
                            <span>Disetujui {{ $item->disetujuiOleh->name ?? '-' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-mute">Tidak ada data komisi pada periode ini.</div>
                @endforelse
            </div>
            @if($riwayat->hasPages())
                <div class="border-t border-hairline/40 px-5 py-3">{{ $riwayat->links() }}</div>
            @endif
        </div>
    </div>
</div>
