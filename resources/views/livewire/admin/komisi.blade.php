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
            <h2 class="text-lg font-semibold text-gray-900">Laporan Komisi</h2>
            <p class="text-sm text-gray-600">Periode {{ $dariTanggal ? \Illuminate\Support\Carbon::parse($dariTanggal)->format('d/m/Y') : 'awal' }} - {{ $sampaiTanggal ? \Illuminate\Support\Carbon::parse($sampaiTanggal)->format('d/m/Y') : 'akhir' }}
                &middot; Dasar tanggal: {{ $dasarTanggal === 'waktu_pencairan' ? 'Pencairan' : 'Approval' }}
                &middot; Status: {{ $status !== '' ? $status : 'approved & selesai' }}</p>
        </div>

        <div class="mb-8">
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Komisi</h1>
            <p class="mt-1 text-sm text-gray-500">Rincian komisi dari penarikan yang sudah disetujui. <span class="text-gray-400">(Basis penarikan approved/selesai; ubah dasar tanggal & status pada filter)</span></p>
        </div>

        {{-- Filter --}}
        <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium text-gray-500">Dari</label>
                    <x-ui.tanggal wire:model.live="dariTanggal"
                        class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium text-gray-500">Sampai</label>
                    <x-ui.tanggal wire:model.live="sampaiTanggal" :min="$dariTanggal"
                        class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                </div>
                <select wire:model.live="produkId"
                    class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                    <option value="">Semua Produk</option>
                    @foreach($produkList as $produk)
                        <option value="{{ $produk->id }}">{{ $produk->nama }} ({{ $produk->persen_komisi }}%)</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="exportCsv"
                    class="inline-flex items-center gap-2 rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Unduh CSV
                </button>
                <button x-on:click="window.print()"
                    class="inline-flex items-center gap-2 rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Cetak
                </button>
                <button wire:click="resetFilter"
                    class="inline-flex items-center gap-2 rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    Reset Filter
                </button>
            </div>
        </div>

        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
            <select wire:model.live="status"
                class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                <option value="">Semua Status (approved & selesai)</option>
                <option value="approved">Approved</option>
                <option value="selesai">Selesai</option>
            </select>
            <select wire:model.live="lokasiPengambilan"
                class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                <option value="">Semua Lokasi</option>
                <option value="kantor">Kantor</option>
                <option value="rumah_kolektor">Rumah Kolektor</option>
            </select>
            <select wire:model.live="kolektorId"
                class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                <option value="">Semua Kolektor Pembayar</option>
                @foreach($kolektorList as $kolektor)
                    <option value="{{ $kolektor->id }}">{{ $kolektor->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="dasarTanggal" title="Dasar tanggal penentuan periode"
                class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                <option value="waktu_approval">Dasar: Tanggal Approval</option>
                <option value="waktu_pencairan">Dasar: Tanggal Pencairan</option>
            </select>
        </div>

        {{-- Summary Cards --}}
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Total Komisi</p>
                <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">Rp {{ number_format($totalKomisi, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-500">{{ $totalTransaksi }} transaksi</p>
            </div>
            <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Total Nominal Penarikan</p>
                <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">Rp {{ number_format($totalNominalPenarikan, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Rata-rata Komisi/Transaksi</p>
                <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">Rp {{ $totalTransaksi > 0 ? number_format($totalKomisi / $totalTransaksi, 0, ',', '.') : '0' }}</p>
            </div>
        </div>

        {{-- Komisi per Produk --}}
        <div class="mb-6 rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Komisi per Produk</h3>
            @if($komisiPerProduk->isNotEmpty())
                <div class="space-y-4">
                    @foreach($komisiPerProduk as $item)
                        @php
                            $persentase = $totalKomisi > 0 ? ($item->total_komisi / $totalKomisi) * 100 : 0;
                        @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900">{{ $item->nama }}</span>
                                <span class="font-mono text-sm font-semibold text-gray-900">Rp {{ number_format($item->total_komisi, 0, ',', '.') }}</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-indigo-600 transition-all" style="width: {{ $persentase }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ $item->jumlah_transaksi }} transaksi &middot; {{ number_format($persentase, 1) }}% dari total</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">Belum ada komisi pada periode ini.</p>
            @endif
        </div>

        {{-- Komisi per Bulan --}}
        <div class="mb-6 rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
            <h3 class="mb-4 text-sm font-semibold text-gray-900">Komisi per Bulan</h3>
            @if($rekapBulan->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-[#ebebeb] bg-gray-50">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Bulan</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500 text-right">Transaksi</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500 text-right">Komisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @foreach($rekapBulan as $item)
                                @php
                                    $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                    [$tahun, $bulan] = explode('-', $item->bulan);
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ $namaBulan[(int) $bulan - 1] }} {{ $tahun }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right font-mono text-sm text-gray-600">{{ $item->jumlah_transaksi }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right font-mono text-sm font-semibold text-gray-900">Rp {{ number_format($item->total_komisi, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-500">Belum ada komisi pada periode ini.</p>
            @endif
        </div>

        {{-- Riwayat Transaksi --}}
        <div class="rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
            <div class="border-b border-[#ebebeb] px-5 py-4">
                <h3 class="text-sm font-semibold text-gray-900">Riwayat Transaksi</h3>
            </div>
            @if($riwayat->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-[#ebebeb] bg-gray-50">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Tanggal</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Nasabah</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Produk</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500 text-right">Nominal</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500 text-right">% Komisi</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500 text-right">Komisi</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Disetujui Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @foreach($riwayat as $item)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ $item->waktu_approval?->format('d M Y, H:i') ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->nasabah->name ?? '-' }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ $item->produk->nama ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right font-mono text-sm text-gray-900">Rp {{ number_format($item->nominal_diminta, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right font-mono text-sm text-gray-600">{{ rtrim(rtrim(number_format($item->persen_komisi_terpakai, 2), '0'), '.') }}%</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right font-mono text-sm font-semibold text-gray-900">Rp {{ number_format($item->nominal_komisi, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ $item->disetujuiOleh->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-[#ebebeb] px-4 py-3">{{ $riwayat->links() }}</div>
            @else
                <div class="px-5 py-12 text-center">
                    <p class="text-sm text-gray-500">Tidak ada data komisi pada periode ini.</p>
                </div>
            @endif
        </div>
    </div>
</div>
