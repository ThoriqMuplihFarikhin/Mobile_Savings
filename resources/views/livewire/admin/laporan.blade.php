<div>
    <style>
        @page { margin: 14mm; }
        @media print {
            body * { visibility: hidden !important; }
            .laporan-cetak, .laporan-cetak * { visibility: visible !important; }
            .laporan-cetak { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>

    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Laporan Lengkap</h1>
        <p class="mt-1 text-sm text-gray-500">Rekapitulasi keuangan, kolektor, paket, dan kebutuhan barang.</p>
    </div>

    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-[#f7d4d6] px-4 py-3 text-sm text-[#c50000]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach(['keuangan' => 'Keuangan', 'kolektor' => 'Per Kolektor', 'paket' => 'Per Paket', 'barang' => 'Kebutuhan Barang'] as $nilaiSeksi => $labelSeksi)
            <button wire:click="pilihSeksi('{{ $nilaiSeksi }}')"
                class="rounded-full px-4 py-2 text-sm font-medium transition {{ $seksi === $nilaiSeksi ? 'bg-indigo-800 text-white' : 'bg-gray-50 text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb] hover:bg-white' }}">
                {{ $labelSeksi }}
            </button>
        @endforeach
    </div>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        @if (in_array($seksi, ['keuangan', 'kolektor'], true))
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="flex gap-2">
                    @foreach(['harian' => 'Harian', 'bulanan' => 'Bulanan'] as $value => $label)
                        <button wire:click="$set('periode', '{{ $value }}')"
                            class="rounded-full px-4 py-2 text-sm font-medium transition {{ $periode === $value ? 'bg-indigo-800 text-white' : 'bg-gray-50 text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb] hover:bg-white' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                @if($periode === 'harian')
                    <input type="date" wire:model.live="tanggal"
                        class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                @else
                    <input type="month" wire:model.live="bulan"
                        class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                @endif
            </div>
        @else
            <div></div>
        @endif
        <div class="flex gap-2">
            <button wire:click="exportCsv"
                class="inline-flex items-center gap-2 rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Export CSV
            </button>
            <button x-on:click="window.print()"
                class="inline-flex items-center gap-2 rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                Cetak
            </button>
        </div>
    </div>

    <div class="laporan-cetak">
        @php
            $judulSeksi = match ($seksi) {
                'kolektor' => 'Per Kolektor',
                'paket' => 'Per Paket',
                'barang' => 'Kebutuhan Barang',
                default => 'Keuangan',
            };
            $judulPeriode = in_array($seksi, ['keuangan', 'kolektor'], true)
                ? ($periode === 'bulanan' ? $bulan : $tanggal)
                : now()->toDateString();
        @endphp
        <div class="mb-4 hidden print:block">
            <h2 class="text-lg font-semibold text-gray-900">Laporan {{ $judulSeksi }} - {{ $judulPeriode }}</h2>
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-x-4 gap-y-1 rounded-xl bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-[inset_0_0_0_1px_#ebebeb]">
            <span class="font-medium">Nasabah Offline</span>
            <span class="font-mono font-semibold">{{ $jumlahNasabahOffline }}</span>
        </div>

        @if($seksi === 'keuangan')
            <div class="mb-6 grid gap-4 sm:grid-cols-4">
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Total Setoran</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">Rp {{ number_format($totalSetoran, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500">{{ $jumlahTransaksiSetoran }} transaksi</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Total Penarikan</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">Rp {{ number_format($totalPenarikan, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500">{{ $jumlahTransaksiPenarikan }} transaksi · {{ $jumlahOverrideRisiko }} override berisiko</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Total Komisi</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">Rp {{ number_format($totalKomisi, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Saldo Bersih</p>
                    @php $saldoBersih = $totalSetoran - $totalPenarikan - $totalKomisi; @endphp
                    <p class="mt-1 font-mono text-2xl font-semibold {{ $saldoBersih >= 0 ? 'text-gray-900' : 'text-[#ee0000]' }}">Rp {{ number_format($saldoBersih, 0, ',', '.') }}</p>
                </div>
            </div>

            @if($periode === 'bulanan')
                <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                    <h3 class="mb-4 text-sm font-semibold text-gray-900">Rekap Harian</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-[#ebebeb]">
                                    <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Tanggal</th>
                                    <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Setoran</th>
                                    <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Penarikan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#ebebeb]">
                                @for($d = 1; $d <= $days; $d++)
                                    @php $day = str_pad($d, 2, '0', STR_PAD_LEFT); @endphp
                                    <tr class="transition hover:bg-gray-50">
                                        <td class="px-3 py-2 text-sm text-gray-600">{{ $day }}</td>
                                        <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ isset($dailySetoran[$day]) ? 'Rp ' . number_format($dailySetoran[$day], 0, ',', '.') : '-' }}</td>
                                        <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ isset($dailyPenarikan[$day]) ? 'Rp ' . number_format($dailyPenarikan[$day], 0, ',', '.') : '-' }}</td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @elseif($seksi === 'kolektor')
            <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Rekap Per Kolektor</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-[#ebebeb]">
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Kolektor</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Total Setoran</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Jumlah Transaksi</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Selisih Rekon</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @forelse($kolektorRows as $baris)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $baris['nama'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($baris['total_setoran'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ $baris['jumlah_transaksi'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm {{ $baris['selisih'] < 0 ? 'text-[#ee0000]' : 'text-gray-900' }}">Rp {{ number_format($baris['selisih'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-4 text-sm text-gray-500">Belum ada data kolektor.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($seksi === 'paket')
            <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Rekap Per Paket</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-[#ebebeb]">
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Paket</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Peserta Aktif</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Total Terkumpul</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Total Tunggakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @forelse($paketRows as $baris)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $baris['produk'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ $baris['peserta_aktif'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($baris['total_terkumpul'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm {{ $baris['total_tunggakan'] > 0 ? 'text-[#ee0000]' : 'text-gray-900' }}">Rp {{ number_format($baris['total_tunggakan'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-4 text-sm text-gray-500">Belum ada produk paket.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Kebutuhan Barang Pengadaan</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-[#ebebeb]">
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Paket</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Item</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Jumlah per Orang</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Peserta Aktif</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Total Kebutuhan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @forelse($barangRows as $baris)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $baris['produk'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $baris['item'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-600">{{ $baris['jumlah'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ $baris['peserta'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">
                                        @if ($baris['total'] !== null)
                                            {{ $baris['total'] }}
                                        @else
                                            {{ $baris['peserta'] }} peserta × {{ $baris['jumlah'] }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-4 text-sm text-gray-500">Belum ada isi paket pada produk paket.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
