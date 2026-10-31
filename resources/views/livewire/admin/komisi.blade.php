<style>
    @page { margin: 14mm; }
    @media print {
        body * { visibility: hidden !important; }
        .laporan-cetak, .laporan-cetak * { visibility: visible !important; }
        .laporan-cetak { position: absolute; left: 0; top: 0; width: 100%; }
    }
</style>
<div>
    <div class="laporan-cetak space-y-6">
        <div class="mb-4 hidden print:block">
            <h2 class="text-lg font-semibold text-ink">Laporan Komisi</h2>
            <p class="text-sm text-body">Periode {{ $dariTanggal ? \Illuminate\Support\Carbon::parse($dariTanggal)->format('d/m/Y') : 'awal' }} - {{ $sampaiTanggal ? \Illuminate\Support\Carbon::parse($sampaiTanggal)->format('d/m/Y') : 'akhir' }}
                &middot; Dasar tanggal: {{ $dasarTanggal === 'waktu_pencairan' ? 'Pencairan' : 'Approval' }}
                &middot; Status: {{ $status !== '' ? $status : 'approved & selesai' }}</p>
        </div>

        <div class="print:hidden">
            <x-admin.page-header
                breadcrumb="Keuangan"
                title="Laporan Komisi"
                subtitle="Rincian komisi dari penarikan yang sudah disetujui. (Basis penarikan approved/selesai; ubah dasar tanggal & status pada filter)"
            />
        </div>

        {{-- Filter & Aksi Toolbar --}}
        <div class="print:hidden space-y-3">
            <x-admin.toolbar>
                <x-slot:filters>
                    <div data-test="baris-tanggal-filter" class="flex flex-wrap items-center gap-2">
                        <label class="text-xs font-medium text-mute">Dari</label>
                        <x-ui.tanggal wire:model.live="dariTanggal"
                            class="h-[38px] rounded-[10px] border border-hairline bg-white px-3 text-sm text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="text-xs font-medium text-mute">Sampai</label>
                        <x-ui.tanggal wire:model.live="sampaiTanggal" :min="$dariTanggal"
                            class="h-[38px] rounded-[10px] border border-hairline bg-white px-3 text-sm text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
                    </div>
                    <select wire:model.live="produkId"
                        class="h-[38px] rounded-[10px] border border-hairline bg-white px-3 text-sm text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                        <option value="">Semua Produk</option>
                        @foreach($produkList as $produk)
                            <option value="{{ $produk->id }}">{{ $produk->nama }} ({{ $produk->persen_komisi }}%)</option>
                        @endforeach
                    </select>
                </x-slot:filters>

                <x-slot:actions>
                    <div data-test="baris-aksi-filter" class="flex flex-wrap items-center gap-2">
                        <button wire:click="exportCsv"
                            class="inline-flex h-[38px] items-center gap-2 rounded-[10px] border border-hairline bg-white px-4 text-xs font-semibold text-ink shadow-xs transition hover:bg-inset dark:bg-surface">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Unduh CSV
                        </button>
                        <button x-on:click="window.print()"
                            class="inline-flex h-[38px] items-center gap-2 rounded-[10px] border border-hairline bg-white px-4 text-xs font-semibold text-ink shadow-xs transition hover:bg-inset dark:bg-surface">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            Cetak
                        </button>
                        <button wire:click="resetFilter"
                            class="inline-flex h-[38px] items-center gap-2 rounded-[10px] border border-hairline bg-white px-4 text-xs font-semibold text-ink shadow-xs transition hover:bg-inset dark:bg-surface">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Reset Filter
                        </button>
                    </div>
                </x-slot:actions>
            </x-admin.toolbar>

            <div class="flex flex-wrap items-center gap-2.5">
                <select wire:model.live="status"
                    class="h-[38px] rounded-[10px] border border-hairline bg-white px-3 text-xs font-medium text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                    <option value="">Semua Status (approved & selesai)</option>
                    <option value="approved">Approved</option>
                    <option value="selesai">Selesai</option>
                </select>
                <select wire:model.live="lokasiPengambilan"
                    class="h-[38px] rounded-[10px] border border-hairline bg-white px-3 text-xs font-medium text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                    <option value="">Semua Lokasi</option>
                    <option value="kantor">Kantor</option>
                    <option value="rumah_kolektor">Rumah Kolektor</option>
                </select>
                <select wire:model.live="kolektorId"
                    class="h-[38px] rounded-[10px] border border-hairline bg-white px-3 text-xs font-medium text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                    <option value="">Semua Kolektor Pembayar</option>
                    @foreach($kolektorList as $kolektor)
                        <option value="{{ $kolektor->id }}">{{ $kolektor->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="dasarTanggal" title="Dasar tanggal penentuan periode"
                    class="h-[38px] rounded-[10px] border border-hairline bg-white px-3 text-xs font-medium text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                    <option value="waktu_approval">Dasar: Tanggal Approval</option>
                    <option value="waktu_pencairan">Dasar: Tanggal Pencairan</option>
                </select>
            </div>
        </div>

        {{-- Summary Cards --}}
        <x-admin.kpi-grid columns="3">
            <x-admin.kpi-card
                label="Total Komisi"
                value="Rp {{ number_format($totalKomisi, 0, ',', '.') }}"
                sub="{{ $totalTransaksi }} transaksi"
            />
            <x-admin.kpi-card
                label="Total Nominal Penarikan"
                value="Rp {{ number_format($totalNominalPenarikan, 0, ',', '.') }}"
                sub="Periode terpilih"
            />
            <x-admin.kpi-card
                label="Rata-rata Komisi/Transaksi"
                value="Rp {{ $totalTransaksi > 0 ? number_format($totalKomisi / $totalTransaksi, 0, ',', '.') : '0' }}"
                sub="Efektivitas bagi hasil"
            />
        </x-admin.kpi-grid>

        {{-- Komisi per Produk --}}
        <div class="rounded-[14px] border border-hairline bg-white p-5 shadow-xs dark:bg-surface">
            <h3 class="mb-4 text-sm font-semibold text-ink">Komisi per Produk</h3>
            @if($komisiPerProduk->isNotEmpty())
                <div class="space-y-4">
                    @foreach($komisiPerProduk as $item)
                        @php
                            $persentase = $totalKomisi > 0 ? ($item->total_komisi / $totalKomisi) * 100 : 0;
                        @endphp
                        <div>
                            <div class="mb-1.5 flex items-center justify-between text-xs">
                                <span class="font-medium text-ink">{{ $item->nama }}</span>
                                <span class="tabular-nums font-semibold text-ink">Rp {{ number_format($item->total_komisi, 0, ',', '.') }}</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-inset">
                                <div class="h-full rounded-full bg-primary transition-all duration-300" style="width: {{ $persentase }}%"></div>
                            </div>
                            <p class="mt-1 text-[11px] text-mute">{{ $item->jumlah_transaksi }} transaksi &middot; {{ number_format($persentase, 1) }}% dari total</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="py-4 text-center text-xs text-mute">Belum ada komisi pada periode ini.</p>
            @endif
        </div>

        {{-- Komisi per Bulan --}}
        <div class="rounded-[14px] border border-hairline bg-white p-5 shadow-xs dark:bg-surface">
            <h3 class="mb-4 text-sm font-semibold text-ink">Komisi per Bulan</h3>
            <div class="overflow-x-auto">
                <table class="hidden md:table w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-hairline bg-inset/50">
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Bulan</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-mute">Transaksi</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-mute">Komisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hairline">
                        @forelse($rekapBulan as $item)
                            @php
                                $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                [$tahun, $bulan] = explode('-', $item->bulan);
                            @endphp
                            <tr class="transition hover:bg-inset/30">
                                <td class="whitespace-nowrap px-4 py-3 text-xs font-medium text-ink">{{ $namaBulan[(int) $bulan - 1] }} {{ $tahun }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-xs text-body">{{ $item->jumlah_transaksi }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-xs font-semibold text-ink">Rp {{ number_format($item->total_komisi, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-xs text-mute">Belum ada komisi pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div data-test="kartu-komisi-bulan" class="divide-y divide-hairline md:hidden">
                @forelse($rekapBulan as $item)
                    @php
                        $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        [$tahun, $bulan] = explode('-', $item->bulan);
                    @endphp
                    <div class="flex items-center justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-ink">{{ $namaBulan[(int) $bulan - 1] }} {{ $tahun }}</p>
                            <p class="text-[11px] text-mute">{{ $item->jumlah_transaksi }} transaksi</p>
                        </div>
                        <span class="tabular-nums text-xs font-semibold text-ink">Rp {{ number_format($item->total_komisi, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-mute">Belum ada komisi pada periode ini.</div>
                @endforelse
            </div>
        </div>

        {{-- Riwayat Transaksi --}}
        <div class="rounded-[14px] border border-hairline bg-white shadow-xs dark:bg-surface">
            <div class="border-b border-hairline px-5 py-4">
                <h3 class="text-sm font-semibold text-ink">Riwayat Transaksi</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="hidden md:table w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-hairline bg-inset/50">
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Tanggal</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Nasabah</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Produk</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-mute">Nominal</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-mute">% Komisi</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-mute">Komisi</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Disetujui Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hairline">
                        @forelse($riwayat as $item)
                            <tr class="transition hover:bg-inset/30">
                                <td class="whitespace-nowrap px-4 py-3 text-xs text-body">{{ $item->waktu_approval?->format('d M Y, H:i') ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <div class="text-xs font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-xs text-body">{{ $item->produk->nama ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-xs text-ink">Rp {{ number_format($item->nominal_diminta, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-xs text-body">{{ rtrim(rtrim(number_format($item->persen_komisi_terpakai, 2), '0'), '.') }}%</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-xs font-semibold text-ink">Rp {{ number_format($item->nominal_komisi, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-xs text-body">{{ $item->disetujuiOleh->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-xs text-mute">Tidak ada data komisi pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div data-test="kartu-riwayat-komisi" class="divide-y divide-hairline md:hidden">
                @forelse($riwayat as $item)
                    <div class="px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</p>
                                <p class="text-[11px] text-mute">{{ $item->waktu_approval?->format('d M Y, H:i') ?? '-' }} &middot; {{ $item->produk->nama ?? '-' }}</p>
                            </div>
                            <span class="tabular-nums text-xs font-semibold text-ink">Rp {{ number_format($item->nominal_komisi, 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-mute">
                            <span class="tabular-nums">Nominal Rp {{ number_format($item->nominal_diminta, 0, ',', '.') }}</span>
                            <span class="tabular-nums">{{ rtrim(rtrim(number_format($item->persen_komisi_terpakai, 2), '0'), '.') }}%</span>
                            <span>Disetujui {{ $item->disetujuiOleh->name ?? '-' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-xs text-mute">Tidak ada data komisi pada periode ini.</div>
                @endforelse
            </div>
            @if($riwayat->hasPages())
                <div class="border-t border-hairline px-4 py-3">{{ $riwayat->links() }}</div>
            @endif
        </div>
    </div>
</div>
