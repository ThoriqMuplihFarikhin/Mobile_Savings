<style>
    @page { margin: 14mm; }
    @media print {
        body * { visibility: hidden !important; }
        .laporan-cetak, .laporan-cetak * { visibility: visible !important; }
        .laporan-cetak { position: absolute; left: 0; top: 0; width: 100%; }
    }
</style>
<div>
    <div class="laporan-cetak">
        <div class="mb-4 hidden print:block">
            <h2 class="text-lg font-semibold text-ink">Laporan Komisi</h2>
            <p class="text-sm text-body">Periode {{ $dariTanggal ? \Illuminate\Support\Carbon::parse($dariTanggal)->format('d/m/Y') : 'awal' }} - {{ $sampaiTanggal ? \Illuminate\Support\Carbon::parse($sampaiTanggal)->format('d/m/Y') : 'akhir' }}
                &middot; Dasar tanggal: {{ $dasarTanggal === 'waktu_pencairan' ? 'Pencairan' : 'Approval' }}
                &middot; Status: {{ $status !== '' ? $status : 'approved & selesai' }}</p>
        </div>

        <div class="mb-8">
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Komisi</h1>
            <p class="mt-1 text-sm text-mute">Rincian komisi dari penarikan yang sudah disetujui. <span class="text-subtle">(Basis penarikan approved/selesai; ubah dasar tanggal & status pada filter)</span></p>
        </div>

        {{-- Filter --}}
        <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div data-test="baris-tanggal-filter" class="flex flex-wrap items-center gap-2">
                    <label class="text-xs font-medium text-mute">Dari</label>
                    <x-ui.tanggal wire:model.live="dariTanggal"
                        class="w-full h-10 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <label class="text-xs font-medium text-mute">Sampai</label>
                    <x-ui.tanggal wire:model.live="sampaiTanggal" :min="$dariTanggal"
                        class="w-full h-10 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                </div>
                <select wire:model.live="produkId"
                    class="h-10 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
                    <option value="">Semua Produk</option>
                    @foreach($produkList as $produk)
                        <option value="{{ $produk->id }}">{{ $produk->nama }} ({{ $produk->persen_komisi }}%)</option>
                    @endforeach
                </select>
            </div>
            <div data-test="baris-aksi-filter" class="flex flex-wrap items-center gap-2">
                <button wire:click="exportCsv"
                    class="inline-flex items-center gap-2 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Unduh CSV
                </button>
                <button x-on:click="window.print()"
                    class="inline-flex items-center gap-2 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Cetak
                </button>
                <button wire:click="resetFilter"
                    class="inline-flex items-center gap-2 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    Reset Filter
                </button>
            </div>
        </div>

        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
            <select wire:model.live="status"
                class="h-10 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
                <option value="">Semua Status (approved & selesai)</option>
                <option value="approved">Approved</option>
                <option value="selesai">Selesai</option>
            </select>
            <select wire:model.live="lokasiPengambilan"
                class="h-10 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
                <option value="">Semua Lokasi</option>
                <option value="kantor">Kantor</option>
                <option value="rumah_kolektor">Rumah Kolektor</option>
            </select>
            <select wire:model.live="kolektorId"
                class="h-10 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
                <option value="">Semua Kolektor Pembayar</option>
                @foreach($kolektorList as $kolektor)
                    <option value="{{ $kolektor->id }}">{{ $kolektor->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="dasarTanggal" title="Dasar tanggal penentuan periode"
                class="h-10 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
                <option value="waktu_approval">Dasar: Tanggal Approval</option>
                <option value="waktu_pencairan">Dasar: Tanggal Pencairan</option>
            </select>
        </div>

        {{-- Summary Cards --}}
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <p class="text-xs text-mute">Total Komisi</p>
                <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">Rp {{ number_format($totalKomisi, 0, ',', '.') }}</p>
                <p class="text-xs text-mute">{{ $totalTransaksi }} transaksi</p>
            </div>
            <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <p class="text-xs text-mute">Total Nominal Penarikan</p>
                <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">Rp {{ number_format($totalNominalPenarikan, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <p class="text-xs text-mute">Rata-rata Komisi/Transaksi</p>
                <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">Rp {{ $totalTransaksi > 0 ? number_format($totalKomisi / $totalTransaksi, 0, ',', '.') : '0' }}</p>
            </div>
        </div>

        {{-- Komisi per Produk --}}
        <div class="mb-6 rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
            <h3 class="mb-4 text-sm font-semibold text-ink">Komisi per Produk</h3>
            @if($komisiPerProduk->isNotEmpty())
                <div class="space-y-4">
                    @foreach($komisiPerProduk as $item)
                        @php
                            $persentase = $totalKomisi > 0 ? ($item->total_komisi / $totalKomisi) * 100 : 0;
                        @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between">
                                <span class="text-sm font-medium text-ink">{{ $item->nama }}</span>
                                <span class="tabular-nums text-sm font-semibold text-ink">Rp {{ number_format($item->total_komisi, 0, ',', '.') }}</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-inset">
                                <div class="h-full rounded-full bg-primary transition-all" style="width: {{ $persentase }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-mute">{{ $item->jumlah_transaksi }} transaksi &middot; {{ number_format($persentase, 1) }}% dari total</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-mute">Belum ada komisi pada periode ini.</p>
            @endif
        </div>

        {{-- Komisi per Bulan --}}
        <div class="mb-6 rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
            <h3 class="mb-4 text-sm font-semibold text-ink">Komisi per Bulan</h3>
            <div class="overflow-x-auto">
                <table class="hidden md:table w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-hairline bg-inset">
                            <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Bulan</th>
                            <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Transaksi</th>
                            <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Komisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hairline">
                        @forelse($rekapBulan as $item)
                            @php
                                $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                [$tahun, $bulan] = explode('-', $item->bulan);
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-ink">{{ $namaBulan[(int) $bulan - 1] }} {{ $tahun }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-sm text-body">{{ $item->jumlah_transaksi }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-sm font-semibold text-ink">Rp {{ number_format($item->total_komisi, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-sm text-mute">Belum ada komisi pada periode ini.</td>
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
                            <p class="text-sm text-ink">{{ $namaBulan[(int) $bulan - 1] }} {{ $tahun }}</p>
                            <p class="text-xs text-mute">{{ $item->jumlah_transaksi }} transaksi</p>
                        </div>
                        <span class="tabular-nums text-sm font-semibold text-ink">Rp {{ number_format($item->total_komisi, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-mute">Belum ada komisi pada periode ini.</div>
                @endforelse
            </div>
        </div>

        {{-- Riwayat Transaksi --}}
        <div class="rounded-lg bg-white shadow-[inset_0_0_0_1px_#e5e5e5]">
            <div class="border-b border-hairline px-5 py-4">
                <h3 class="text-sm font-semibold text-ink">Riwayat Transaksi</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="hidden md:table w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-hairline bg-inset">
                            <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tanggal</th>
                            <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nasabah</th>
                            <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Produk</th>
                            <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Nominal</th>
                            <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">% Komisi</th>
                            <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Komisi</th>
                            <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Disetujui Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hairline">
                        @forelse($riwayat as $item)
                            <tr class="transition hover:bg-inset">
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-body">{{ $item->waktu_approval?->format('d M Y, H:i') ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <div class="text-sm font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-body">{{ $item->produk->nama ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-sm text-ink">Rp {{ number_format($item->nominal_diminta, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-sm text-body">{{ rtrim(rtrim(number_format($item->persen_komisi_terpakai, 2), '0'), '.') }}%</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-sm font-semibold text-ink">Rp {{ number_format($item->nominal_komisi, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-body">{{ $item->disetujuiOleh->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm text-mute">Tidak ada data komisi pada periode ini.</td>
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
                                <p class="text-sm font-medium text-ink">{{ $item->nasabah->name ?? '-' }}</p>
                                <p class="text-xs text-mute">{{ $item->waktu_approval?->format('d M Y, H:i') ?? '-' }} &middot; {{ $item->produk->nama ?? '-' }}</p>
                            </div>
                            <span class="tabular-nums text-sm font-semibold text-ink">Rp {{ number_format($item->nominal_komisi, 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-mute">
                            <span class="tabular-nums">Nominal Rp {{ number_format($item->nominal_diminta, 0, ',', '.') }}</span>
                            <span class="tabular-nums">{{ rtrim(rtrim(number_format($item->persen_komisi_terpakai, 2), '0'), '.') }}%</span>
                            <span>Disetujui {{ $item->disetujuiOleh->name ?? '-' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-sm text-mute">Tidak ada data komisi pada periode ini.</div>
                @endforelse
            </div>
            @if($riwayat->hasPages())
                <div class="border-t border-hairline px-4 py-3">{{ $riwayat->links() }}</div>
            @endif
        </div>
    </div>
</div>
