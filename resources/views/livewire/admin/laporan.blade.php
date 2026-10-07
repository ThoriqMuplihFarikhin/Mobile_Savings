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
        @foreach(['keuangan' => 'Keuangan', 'kolektor' => 'Per Kolektor', 'rekon' => 'Rekonsiliasi', 'umurkas' => 'Umur Kas', 'mutasi' => 'Mutasi', 'penarikan' => 'Penarikan', 'paket' => 'Per Paket', 'barang' => 'Kebutuhan Barang'] as $nilaiSeksi => $labelSeksi)
            <button wire:click="pilihSeksi('{{ $nilaiSeksi }}')"
                class="rounded-full px-4 py-2 text-sm font-medium transition {{ $seksi === $nilaiSeksi ? 'bg-indigo-800 text-white' : 'bg-gray-50 text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb] hover:bg-white' }}">
                {{ $labelSeksi }}
            </button>
        @endforeach
    </div>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        @if (in_array($seksi, ['keuangan', 'kolektor', 'rekon', 'mutasi', 'penarikan'], true))
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="flex gap-2">
                    @foreach(['harian' => 'Harian', 'bulanan' => 'Bulanan', 'rentang' => 'Rentang'] as $value => $label)
                        <button wire:click="$set('periode', '{{ $value }}')"
                            class="rounded-full px-4 py-2 text-sm font-medium transition {{ $periode === $value ? 'bg-indigo-800 text-white' : 'bg-gray-50 text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb] hover:bg-white' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                @if($periode === 'rentang')
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium text-gray-500">Dari</label>
                        <x-ui.tanggal wire:model.live="dariTanggal" :max="now()->toDateString()"
                            class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        <label class="text-xs font-medium text-gray-500">Sampai</label>
                        <x-ui.tanggal wire:model.live="sampaiTanggal" :min="$dariTanggal" :max="now()->toDateString()"
                            class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                    </div>
                @elseif($periode === 'harian')
                    <x-ui.tanggal wire:model.live="tanggal" :max="now()->toDateString()"
                        class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                @else
                    <x-ui.tanggal wire:model.live="bulan" mode="bulan" :max="now()->toDateString()"
                        class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                @endif
                @if ($seksi === 'mutasi')
                    <select wire:model.live="mutasiNasabahId"
                        class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                        <option value="0">Pilih nasabah</option>
                        @foreach ($mutasiNasabahList as $nasabah)
                            <option value="{{ $nasabah['id'] }}">{{ $nasabah['nama'] }}</option>
                        @endforeach
                    </select>
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
                'rekon' => 'Rekonsiliasi Kas',
                'umurkas' => 'Umur Kas',
                'mutasi' => 'Mutasi Nasabah',
                'penarikan' => 'Penarikan',
                default => 'Keuangan',
            };
            $judulPeriode = in_array($seksi, ['keuangan', 'kolektor', 'rekon', 'mutasi', 'penarikan'], true)
                ? match ($periode) {
                    'bulanan' => $bulan,
                    'rentang' => $dariTanggal.' s/d '.$sampaiTanggal,
                    default => $tanggal,
                }
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
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-gray-900">Definisi angka</p>
                    <p class="mt-0.5">Total Setoran = jumlah setoran aktif (yang dibatalkan dikecualikan) berdasarkan tanggal transaksi. Total Penarikan = jumlah penarikan approved/selesai berdasarkan waktu approval. Total Komisi = jumlah komisi dari penarikan tersebut. Saldo Bersih = Total Setoran &minus; Total Penarikan &minus; Total Komisi. Pembulatan hanya terjadi di tampilan.</p>
                </div>
            </div>
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
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-gray-900">Definisi angka</p>
                    <p class="mt-0.5">Total Setoran = setoran yang diinput kolektor pada periode. Penarikan Tunai Dibayar = jumlah penarikan tunai (nominal diterima) yang dibayarkan kolektor pada periode. Kas di Tangan = setoran belum disetor kantor &minus; penarikan tunai belum direkonsiliasi (rumus D13, posisi terkini bukan periode). Setor Kantor = total pengajuan setor ke kantor pada periode. Selisih Rekon = selisih rekon lebih/kurang pada periode.</p>
                </div>
            </div>
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
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Penarikan Tunai Dibayar</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Kas di Tangan</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Setor Kantor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @forelse($kolektorRows as $baris)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $baris['nama'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($baris['total_setoran'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ $baris['jumlah_transaksi'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm {{ $baris['selisih'] < 0 ? 'text-[#ee0000]' : 'text-gray-900' }}">Rp {{ number_format($baris['selisih'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($baris['penarikan_dibayar'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($baris['setor_kantor'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-4 text-sm text-gray-500">Belum ada data kolektor.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($seksi === 'rekon')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-gray-900">Definisi angka</p>
                    <p class="mt-0.5">Seharusnya = total seharusnya disetor kolektor. Diterima = total yang benar-benar diterima kantor. Selisih = Diterima &minus; Seharusnya (lebih = positif, kurang = negatif). Penerima = admin yang menerima/merekonsiliasi pengajuan. Seluruh status pengajuan (pending, cocok, lebih, kurang, dibatalkan) pada periode disertakan agar riwayat tetap terlacak.</p>
                </div>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Rekap Pengajuan Setor per Kolektor</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-[#ebebeb]">
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Kolektor</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Pengajuan</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Seharusnya</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Diterima</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @forelse($rekonRows as $baris)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $baris['nama'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ $baris['jumlah'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($baris['seharusnya'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($baris['diterima'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm {{ $baris['selisih'] != 0 ? 'text-[#ee0000]' : 'text-gray-900' }}">Rp {{ number_format($baris['selisih'], 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="px-3 py-4 text-sm text-gray-500">Belum ada pengajuan setor pada periode ini.</td></tr>
                            @endforelse
                            @if(count($rekonRows) > 0)
                                <tr class="bg-gray-50 font-semibold">
                                    <td class="px-3 py-2 text-sm text-gray-900">TOTAL</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ $rekonTotal['jumlah'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($rekonTotal['seharusnya'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($rekonTotal['diterima'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm {{ $rekonTotal['selisih'] != 0 ? 'text-[#ee0000]' : 'text-gray-900' }}">Rp {{ number_format($rekonTotal['selisih'], 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-6 rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Detail Pengajuan</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-[#ebebeb]">
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Tanggal</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Kolektor</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Seharusnya</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Diterima</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Selisih</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Keterangan</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Penerima</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @forelse($rekonDetail as $item)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ \Carbon\Carbon::parse($item->tanggal_setor)->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $item->kolektor->name ?? '-' }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format((float) $item->total_seharusnya, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format((float) $item->total_diterima, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm {{ (float) $item->selisih != 0 ? 'text-[#ee0000]' : 'text-gray-900' }}">Rp {{ number_format((float) $item->selisih, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ $item->keterangan_selisih ?? '-' }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $item->diterimaOleh->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-4 text-sm text-gray-500">Belum ada pengajuan setor pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($seksi === 'umurkas')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-gray-900">Definisi angka</p>
                    <p class="mt-0.5">Kas di Tangan = setoran belum disetor kantor &minus; penarikan tunai belum direkonsiliasi (rumus D13, posisi terkini). Umur = hari sejak setoran belum disetor terkoleksi; kelompok 0-1, 2-3, dan lebih dari 3 hari. Lewat Batas = kas melebihi batas kas dan/atau umur melebihi batas hari sesuai pengaturan admin. Laporan ini adalah posisi terkini, bukan periode.</p>
                </div>
            </div>
            <div class="mb-6 grid gap-4 sm:grid-cols-3">
                @foreach($umurKelompok as $kelompok => $ringkas)
                    <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                        <p class="font-mono text-xs uppercase tracking-wider text-gray-500">{{ $kelompok }}</p>
                        <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">Rp {{ number_format($ringkas['kas'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500">{{ $ringkas['jumlah'] }} kolektor</p>
                    </div>
                @endforeach
            </div>
            <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Umur Kas Per Kolektor</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-[#ebebeb]">
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Kolektor</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Kas di Tangan</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Umur Terlama</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Kelompok</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Lewat Batas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @forelse($umurKasRows as $baris)
                                @php
                                    $kunciUmur = match (true) {
                                        $baris['umur_terlama_hari'] <= 1 => '0-1 hari',
                                        $baris['umur_terlama_hari'] <= 3 => '2-3 hari',
                                        default => '>3 hari',
                                    };
                                @endphp
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $baris['nama'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ $baris['umur_terlama_hari'] }} hari</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $kunciUmur }}</td>
                                    <td class="px-3 py-2 text-sm">
                                        @if($baris['lewat_batas'])
                                            <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2 py-0.5 font-mono text-xs text-[#c50000]">Ya</span>
                                        @else
                                            <span class="text-gray-500">Tidak</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-4 text-sm text-gray-500">Belum ada data kolektor.</td></tr>
                            @endforelse
                            @if(count($umurKasRows) > 0)
                                <tr class="bg-gray-50 font-semibold">
                                    <td class="px-3 py-2 text-sm text-gray-900">TOTAL</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($umurTotal['kas'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">&mdash;</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $umurTotal['jumlah'] }} kolektor</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $umurTotal['lewat_batas'] }} lewat</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($seksi === 'mutasi')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-gray-900">Definisi angka</p>
                    <p class="mt-0.5">Saldo Awal = saldo saat ini dikurangi seluruh mutasi sejak awal periode. Saldo Berjalan = saldo setelah setiap baris. Setoran hanya yang aktif (dibatalkan dikecualikan); penarikan hanya approved/selesai. Cocokkan dengan buku tabungan fisik untuk nasabah offline (D14).</p>
                </div>
            </div>
            <div class="mb-6 grid gap-4 sm:grid-cols-4">
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Saldo Awal</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">Rp {{ number_format($mutasiRingkas['saldoAwal'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Total Setoran</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">Rp {{ number_format($mutasiRingkas['totalSetoran'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Total Penarikan</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">Rp {{ number_format($mutasiRingkas['totalPenarikan'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Saldo Akhir</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">Rp {{ number_format($mutasiRingkas['saldoAkhir'], 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Buku Tabungan</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-[#ebebeb]">
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Tanggal</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Tipe</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Produk</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Nominal</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Saldo Berjalan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @forelse($mutasiRows as $baris)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ \Carbon\Carbon::parse($baris['tanggal'])->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $baris['tipe'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ $baris['produk'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm {{ $baris['arah'] > 0 ? 'text-gray-900' : 'text-[#ee0000]' }}">{{ $baris['arah'] > 0 ? '+' : '-' }} Rp {{ number_format($baris['nominal'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($baris['saldo'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-4 text-sm text-gray-500">{{ $mutasiNasabahId > 0 ? 'Belum ada mutasi pada periode ini.' : 'Pilih nasabah untuk menampilkan buku tabungan.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($seksi === 'penarikan')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-gray-900">Definisi angka</p>
                    <p class="mt-0.5">Periode mengikuti saat pengajuan diajukan (bukan saat diproses). Rekap per status: pending, approved, selesai, ditolak, dibatalkan, kedaluwarsa. Total Diminta = nominal diminta nasabah. Waktu Proses = waktu approval (kosong bila belum diproses). Alasan terisi untuk pengajuan yang dibatalkan/ditolak.</p>
                </div>
            </div>
            <div class="mb-6 grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @foreach($penarikanRekap as $ringkas)
                    <div class="rounded-xl bg-gray-50 p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                        <p class="font-mono text-xs uppercase tracking-wider text-gray-500">{{ $ringkas['label'] }}</p>
                        <p class="mt-1 font-mono text-2xl font-semibold {{ in_array($ringkas['status'], ['ditolak', 'dibatalkan'], true) ? 'text-[#ee0000]' : 'text-gray-900' }}">{{ $ringkas['jumlah'] }}</p>
                        <p class="text-xs text-gray-500">Rp {{ number_format($ringkas['total'], 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
            <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Detail Penarikan</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-[#ebebeb]">
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Tanggal</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Nasabah</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Produk</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Diminta</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Komisi</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Diterima</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Lokasi</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Waktu Proses</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @forelse($penarikanRows as $baris)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ $baris['tanggal'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $baris['nasabah'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ $baris['produk'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($baris['diminta'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($baris['komisi'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($baris['diterima'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-sm {{ in_array($baris['status'], ['ditolak', 'dibatalkan'], true) ? 'text-[#ee0000]' : 'text-gray-900' }}">{{ $baris['status'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ $baris['lokasi'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ $baris['waktu_proses'] ?? '-' }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ $baris['alasan'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="px-3 py-4 text-sm text-gray-500">Belum ada penarikan pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($seksi === 'paket')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-gray-900">Definisi angka</p>
                    <p class="mt-0.5">Peserta Aktif = kepesertaan tanpa keputusan akhir dan belum diserahkan. Total Terkumpul = jumlah total aktual terkumpul seluruh peserta aktif. Total Tunggakan = jumlah tunggakan seluruh peserta aktif.</p>
                </div>
            </div>
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
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-gray-900">Definisi angka</p>
                    <p class="mt-0.5">Total Kebutuhan = Peserta Aktif x jumlah per orang (teks bebas pada isi paket). Harga barang tidak ditampilkan pada laporan ini (keputusan D15, hanya admin).</p>
                </div>
            </div>
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
