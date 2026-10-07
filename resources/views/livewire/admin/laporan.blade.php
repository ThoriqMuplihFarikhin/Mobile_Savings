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
        @foreach(['keuangan' => 'Keuangan', 'kolektor' => 'Per Kolektor', 'rekon' => 'Rekonsiliasi', 'umurkas' => 'Umur Kas', 'mutasi' => 'Mutasi', 'penarikan' => 'Penarikan', 'tunggakan' => 'Tunggakan', 'serah' => 'Serah Terima', 'absensi' => 'Absensi', 'nasabah' => 'Nasabah', 'paket' => 'Per Paket', 'barang' => 'Kebutuhan Barang'] as $nilaiSeksi => $labelSeksi)
            <button wire:click="pilihSeksi('{{ $nilaiSeksi }}')"
                class="rounded-full px-4 py-2 text-sm font-medium transition {{ $seksi === $nilaiSeksi ? 'bg-indigo-800 text-white' : 'bg-gray-50 text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb] hover:bg-white' }}">
                {{ $labelSeksi }}
            </button>
        @endforeach
    </div>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        @if (in_array($seksi, ['keuangan', 'kolektor', 'rekon', 'mutasi', 'penarikan', 'absensi'], true))
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div data-test="baris-periode" class="flex flex-wrap gap-2">
                    @foreach(['harian' => 'Harian', 'bulanan' => 'Bulanan', 'rentang' => 'Rentang'] as $value => $label)
                        <button wire:click="$set('periode', '{{ $value }}')"
                            class="rounded-full px-4 py-2 text-sm font-medium transition {{ $periode === $value ? 'bg-indigo-800 text-white' : 'bg-gray-50 text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb] hover:bg-white' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                @if($periode === 'rentang')
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="text-xs font-medium text-gray-500">Dari</label>
                        <x-ui.tanggal wire:model.live="dariTanggal" :max="now()->toDateString()"
                            class="w-full h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        <label class="text-xs font-medium text-gray-500">Sampai</label>
                        <x-ui.tanggal wire:model.live="sampaiTanggal" :min="$dariTanggal" :max="now()->toDateString()"
                            class="w-full h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
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
        <div data-test="baris-aksi" class="flex flex-wrap gap-2">
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
            <a href="{{ route('admin.komisi.index') }}"
                class="inline-flex items-center gap-2 rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                Laporan Komisi
            </a>
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
                'tunggakan' => 'Tunggakan & Paket Gagal',
                'serah' => 'Serah Terima Paket',
                'absensi' => 'Absensi & Izin',
                'nasabah' => 'Nasabah',
                default => 'Keuangan',
            };
            $judulPeriode = in_array($seksi, ['keuangan', 'kolektor', 'rekon', 'mutasi', 'penarikan', 'absensi'], true)
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
                        <table class="hidden md:table w-full text-left">
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
                    <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
                        @for($d = 1; $d <= $days; $d++)
                            @php $day = str_pad($d, 2, '0', STR_PAD_LEFT); @endphp
                            <div class="px-4 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900">{{ $day }}</p>
                                    </div>
                                </div>
                                <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                    <div>
                                        <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Setoran</span>
                                        <span class="font-mono text-sm text-gray-600">{{ isset($dailySetoran[$day]) ? 'Rp ' . number_format($dailySetoran[$day], 0, ',', '.') : '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Penarikan</span>
                                        <span class="font-mono text-sm text-gray-600">{{ isset($dailyPenarikan[$day]) ? 'Rp ' . number_format($dailyPenarikan[$day], 0, ',', '.') : '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endfor
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
                    <table class="hidden md:table w-full text-left">
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
                <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
                    @forelse($kolektorRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $baris['nama'] }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Total Setoran</span>
                                    <span class="font-mono text-sm text-gray-600">Rp {{ number_format($baris['total_setoran'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Jumlah Transaksi</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['jumlah_transaksi'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Selisih Rekon</span>
                                    <span class="font-mono text-sm {{ $baris['selisih'] < 0 ? 'text-[#ee0000]' : 'text-gray-900' }}">Rp {{ number_format($baris['selisih'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Penarikan Tunai Dibayar</span>
                                    <span class="font-mono text-sm text-gray-600">Rp {{ number_format($baris['penarikan_dibayar'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Kas di Tangan</span>
                                    <span class="font-mono text-sm text-gray-600">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Setor Kantor</span>
                                    <span class="font-mono text-sm text-gray-600">Rp {{ number_format($baris['setor_kantor'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-gray-500">Belum ada data kolektor.</div>
                    @endforelse
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
                    <table class="hidden md:table w-full text-left">
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
                <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
                    @forelse($rekonRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $baris['nama'] }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Pengajuan</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['jumlah'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Seharusnya</span>
                                    <span class="font-mono text-sm text-gray-600">Rp {{ number_format($baris['seharusnya'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Diterima</span>
                                    <span class="font-mono text-sm text-gray-600">Rp {{ number_format($baris['diterima'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Selisih</span>
                                    <span class="font-mono text-sm {{ $baris['selisih'] != 0 ? 'text-[#ee0000]' : 'text-gray-900' }}">Rp {{ number_format($baris['selisih'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-gray-500">Belum ada pengajuan setor pada periode ini.</div>
                    @endforelse
                    @if(count($rekonRows) > 0)
                        <div class="flex justify-between px-4 py-2.5 text-sm font-semibold text-gray-900 bg-gray-50">
                            <span>TOTAL</span>
                            <span class="font-mono">{{ $rekonTotal['jumlah'] }} · Rp {{ number_format($rekonTotal['seharusnya'], 0, ',', '.') }} · Rp {{ number_format($rekonTotal['diterima'], 0, ',', '.') }} · <span class="{{ $rekonTotal['selisih'] != 0 ? 'text-[#ee0000]' : 'text-gray-900' }}">Rp {{ number_format($rekonTotal['selisih'], 0, ',', '.') }}</span></span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="mt-6 rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Detail Pengajuan</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
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
                <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
                    @forelse($rekonDetail as $item)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $item->kolektor->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($item->tanggal_setor)->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Seharusnya</span>
                                    <span class="font-mono text-sm text-gray-600">Rp {{ number_format((float) $item->total_seharusnya, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Diterima</span>
                                    <span class="font-mono text-sm text-gray-600">Rp {{ number_format((float) $item->total_diterima, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Selisih</span>
                                    <span class="font-mono text-sm {{ (float) $item->selisih != 0 ? 'text-[#ee0000]' : 'text-gray-900' }}">Rp {{ number_format((float) $item->selisih, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Keterangan</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $item->keterangan_selisih ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Penerima</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $item->diterimaOleh->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-gray-500">Belum ada pengajuan setor pada periode ini.</div>
                    @endforelse
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
                    <table class="hidden md:table w-full text-left">
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
                <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
                    @forelse($umurKasRows as $baris)
                        @php
                            $kunciUmur = match (true) {
                                $baris['umur_terlama_hari'] <= 1 => '0-1 hari',
                                $baris['umur_terlama_hari'] <= 3 => '2-3 hari',
                                default => '>3 hari',
                            };
                        @endphp
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $baris['nama'] }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Kas di Tangan</span>
                                    <span class="font-mono text-sm text-gray-600">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Umur Terlama</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['umur_terlama_hari'] }} hari</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Kelompok</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $kunciUmur }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Lewat Batas</span>
                                    <span class="font-mono text-sm text-gray-600">
                                        @if($baris['lewat_batas'])
                                            <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2 py-0.5 font-mono text-xs text-[#c50000]">Ya</span>
                                        @else
                                            <span class="text-gray-500">Tidak</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-gray-500">Belum ada data kolektor.</div>
                    @endforelse
                    @if(count($umurKasRows) > 0)
                        <div class="flex justify-between px-4 py-2.5 text-sm font-semibold text-gray-900 bg-gray-50">
                            <span>TOTAL</span>
                            <span class="font-mono">Rp {{ number_format($umurTotal['kas'], 0, ',', '.') }} · &mdash; · {{ $umurTotal['jumlah'] }} kolektor · {{ $umurTotal['lewat_batas'] }} lewat</span>
                        </div>
                    @endif
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
                    <table class="hidden md:table w-full text-left">
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
                <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
                    @forelse($mutasiRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($baris['tanggal'])->format('d/m/Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $baris['tipe'] }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Produk</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['produk'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Nominal</span>
                                    <span class="font-mono text-sm {{ $baris['arah'] > 0 ? 'text-gray-900' : 'text-[#ee0000]' }}">{{ $baris['arah'] > 0 ? '+' : '-' }} Rp {{ number_format($baris['nominal'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Saldo Berjalan</span>
                                    <span class="font-mono text-sm text-gray-600">Rp {{ number_format($baris['saldo'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-gray-500">{{ $mutasiNasabahId > 0 ? 'Belum ada mutasi pada periode ini.' : 'Pilih nasabah untuk menampilkan buku tabungan.' }}</div>
                    @endforelse
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
                    <table class="hidden md:table w-full text-left">
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
                <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
                    @forelse($penarikanRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $baris['nasabah'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $baris['tanggal'] }}</p>
                                </div>
                                <span class="shrink-0 text-sm {{ in_array($baris['status'], ['ditolak', 'dibatalkan'], true) ? 'text-[#ee0000]' : 'text-gray-900' }}">{{ $baris['status'] }}</span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Produk</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['produk'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Diminta</span>
                                    <span class="font-mono text-sm text-gray-600">Rp {{ number_format($baris['diminta'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Komisi</span>
                                    <span class="font-mono text-sm text-gray-600">Rp {{ number_format($baris['komisi'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Diterima</span>
                                    <span class="font-mono text-sm text-gray-600">Rp {{ number_format($baris['diterima'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Lokasi</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['lokasi'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Waktu Proses</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['waktu_proses'] ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Alasan</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['alasan'] }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-gray-500">Belum ada penarikan pada periode ini.</div>
                    @endforelse
                </div>
            </div>
        @elseif($seksi === 'tunggakan')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-gray-900">Definisi angka</p>
                    <p class="mt-0.5">Tunggakan hari = hari berjalan &minus; hari terbayar (peserta aktif yang belum diserahkan). Tunggakan (Rp) = tunggakan hari &times; harga per hari. Status Alert: peringatan bila menunggak, perlu review bila melewati batas toleransi tanpa penundaan. Keputusan akhir kosong berarti masih berjalan. Posisi terkini, bukan periode.</p>
                </div>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Nasabah Menunggak</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-[#ebebeb]">
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Nasabah</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Produk</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Tunggakan (hari)</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Tunggakan (Rp)</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Status Alert</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Keputusan Akhir</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Ditunda Hingga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @forelse($tunggakanRows as $baris)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $baris['nasabah'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ $baris['produk'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ $baris['tunggakan_hari'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">Rp {{ number_format($baris['tunggakan_rupiah'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-sm {{ $baris['status_alert'] === 'Perlu Review' ? 'text-[#ee0000]' : 'text-gray-900' }}">{{ $baris['status_alert'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ $baris['keputusan_akhir'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-600">{{ $baris['ditunda_hingga'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-4 text-sm text-gray-500">Tidak ada nasabah menunggak.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
                    @forelse($tunggakanRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $baris['nasabah'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $baris['produk'] }}</p>
                                </div>
                                <span class="shrink-0 text-sm {{ $baris['status_alert'] === 'Perlu Review' ? 'text-[#ee0000]' : 'text-gray-900' }}">{{ $baris['status_alert'] }}</span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Tunggakan (hari)</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['tunggakan_hari'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Tunggakan (Rp)</span>
                                    <span class="font-mono text-sm text-gray-600">Rp {{ number_format($baris['tunggakan_rupiah'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Keputusan Akhir</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['keputusan_akhir'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Ditunda Hingga</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['ditunda_hingga'] ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-gray-500">Tidak ada nasabah menunggak.</div>
                    @endforelse
                </div>
            </div>
        @elseif($seksi === 'serah')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-gray-900">Definisi angka</p>
                    <p class="mt-0.5">Status serah terima: belum (masih berjalan) atau sudah diterima. Metode = cara paket diambil. Penerima = nama yang menandatangani serah terima. Tanggal = tanggal serah terima (kosong bila belum). Foto bukti hanya dapat dilihat oleh admin (D15). Posisi terkini, bukan periode.</p>
                </div>
            </div>
            <div class="mb-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Sudah Diterima</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">{{ collect($serahRows)->where('status_kunci', 'sudah_diterima')->count() }} paket</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Belum</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">{{ collect($serahRows)->where('status_kunci', 'belum')->count() }} paket</p>
                </div>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Detail Serah Terima</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-[#ebebeb]">
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Nasabah</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Produk</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Metode</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Penerima</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Tanggal</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Foto Bukti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @forelse($serahRows as $baris)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $baris['nasabah'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ $baris['produk'] }}</td>
                                    <td class="px-3 py-2 text-sm {{ $baris['status_kunci'] === 'sudah_diterima' ? 'text-gray-900' : 'text-gray-500' }}">{{ $baris['status'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ $baris['metode'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $baris['penerima'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ $baris['tanggal'] ?? '-' }}</td>
                                    <td class="px-3 py-2 text-sm">
                                        @if ($baris['foto'] !== null)
                                            <a href="{{ $baris['foto'] }}" target="_blank" rel="noopener noreferrer" class="text-indigo-800 underline-offset-2 hover:underline">Lihat</a>
                                        @else
                                            <span class="text-gray-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-4 text-sm text-gray-500">Belum ada kepesertaan paket.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
                    @forelse($serahRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $baris['nasabah'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $baris['produk'] }}</p>
                                </div>
                                <span class="shrink-0 text-sm {{ $baris['status_kunci'] === 'sudah_diterima' ? 'text-gray-900' : 'text-gray-500' }}">{{ $baris['status'] }}</span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Metode</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['metode'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Penerima</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['penerima'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Tanggal</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['tanggal'] ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Foto Bukti</span>
                                    <span class="font-mono text-sm text-gray-600">
                                        @if ($baris['foto'] !== null)
                                            <a href="{{ $baris['foto'] }}" target="_blank" rel="noopener noreferrer" class="text-indigo-800 underline-offset-2 hover:underline">Lihat</a>
                                        @else
                                            <span class="text-gray-500">-</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-gray-500">Belum ada kepesertaan paket.</div>
                    @endforelse
                </div>
            </div>
        @elseif($seksi === 'absensi')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-gray-900">Definisi angka</p>
                    <p class="mt-0.5">Hadir = jumlah hari absensi kolektor tercatat (satu baris per tanggal per kolektor) pada periode berjalan. Izin Disetujui dan Izin Pending dihitung dari izin yang tanggalnya tumpang tindih dengan periode. Jam Masuk dan Jam Keluar berasal dari absensi harian kolektor.</p>
                </div>
            </div>
            <div class="mb-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Hadir</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">{{ $absensiRekap['hadir'] }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Izin Disetujui</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">{{ $absensiRekap['izinDisetujui'] }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Izin Pending</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">{{ $absensiRekap['izinPending'] }}</p>
                </div>
            </div>

            <div class="mb-6 rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Absensi Kolektor</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-[#ebebeb]">
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Tanggal</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Kolektor</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Jam Masuk</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Jam Keluar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @forelse($absensiRows as $baris)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ $baris['tanggal'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $baris['kolektor'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ $baris['jam_masuk'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm {{ $baris['jam_keluar'] !== null ? 'text-gray-900' : 'text-[#ee0000]' }}">{{ $baris['jam_keluar'] ?? 'Belum keluar' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-4 text-sm text-gray-500">Belum ada absensi pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
                    @forelse($absensiRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $baris['kolektor'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $baris['tanggal'] }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Jam Masuk</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['jam_masuk'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Jam Keluar</span>
                                    <span class="font-mono text-sm {{ $baris['jam_keluar'] !== null ? 'text-gray-900' : 'text-[#ee0000]' }}">{{ $baris['jam_keluar'] ?? 'Belum keluar' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-gray-500">Belum ada absensi pada periode ini.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Izin Kolektor</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-[#ebebeb]">
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Kolektor</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Dari</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Sampai</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Alasan</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Diproses Oleh</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @forelse($izinRows as $baris)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $baris['kolektor'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-600">{{ $baris['dari'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-600">{{ $baris['sampai'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ $baris['alasan'] }}</td>
                                    <td class="px-3 py-2 text-sm {{ $baris['status'] === 'Ditolak' ? 'text-[#ee0000]' : 'text-gray-900' }}">{{ $baris['status'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ $baris['pemroses'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $baris['catatan'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-4 text-sm text-gray-500">Tidak ada izin pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
                    @forelse($izinRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $baris['kolektor'] }}</p>
                                </div>
                                <span class="shrink-0 text-sm {{ $baris['status'] === 'Ditolak' ? 'text-[#ee0000]' : 'text-gray-900' }}">{{ $baris['status'] }}</span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Dari</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['dari'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Sampai</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['sampai'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Alasan</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['alasan'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Diproses Oleh</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['pemroses'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Catatan</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['catatan'] ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-gray-500">Tidak ada izin pada periode ini.</div>
                    @endforelse
                </div>
            </div>
        @elseif($seksi === 'nasabah')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-gray-900">Definisi angka</p>
                    <p class="mt-0.5">Status = status pendaftaran nasabah pada posisi terkini (tanpa filter periode). Mode Akses = Digital atau Offline sesuai akun nasabah. Kolektor = penanggung jawab aktif dari nasabah tersebut; tanda &minus; berarti belum ditangani kolektor mana pun.</p>
                </div>
            </div>
            <div class="mb-6 grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Aktif</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">{{ $nasabahRekap['aktif'] }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Pending Verifikasi</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">{{ $nasabahRekap['pending'] }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Ditolak</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">{{ $nasabahRekap['ditolak'] }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Digital</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">{{ $nasabahRekap['digital'] }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-5 shadow-[inset_0_0_0_1px_#ebebeb]">
                    <p class="font-mono text-xs uppercase tracking-wider text-gray-500">Offline</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-gray-900">{{ $nasabahRekap['offline'] }}</p>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Daftar Nasabah</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-[#ebebeb]">
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Nama</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">No HP</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Status Pendaftaran</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Mode Akses</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Kolektor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#ebebeb]">
                            @forelse($nasabahRows as $baris)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm text-gray-900">{{ $baris['nama'] }}</td>
                                    <td class="px-3 py-2 font-mono text-sm text-gray-600">{{ $baris['no_hp'] }}</td>
                                    <td class="px-3 py-2 text-sm {{ in_array($baris['status_pendaftaran'], ['Ditolak'], true) ? 'text-[#ee0000]' : 'text-gray-900' }}">{{ $baris['status_pendaftaran'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ $baris['mode_akses'] }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ $baris['kolektor'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-4 text-sm text-gray-500">Belum ada nasabah terdaftar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
                    @forelse($nasabahRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $baris['nama'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $baris['no_hp'] }}</p>
                                </div>
                                <span class="shrink-0 text-sm {{ in_array($baris['status_pendaftaran'], ['Ditolak'], true) ? 'text-[#ee0000]' : 'text-gray-900' }}">{{ $baris['status_pendaftaran'] }}</span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Mode Akses</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['mode_akses'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Kolektor</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['kolektor'] }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-gray-500">Belum ada nasabah terdaftar.</div>
                    @endforelse
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
                    <table class="hidden md:table w-full text-left">
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
                <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
                    @forelse($paketRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $baris['produk'] }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Peserta Aktif</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['peserta_aktif'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Total Terkumpul</span>
                                    <span class="font-mono text-sm text-gray-600">Rp {{ number_format($baris['total_terkumpul'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Total Tunggakan</span>
                                    <span class="font-mono text-sm {{ $baris['total_tunggakan'] > 0 ? 'text-[#ee0000]' : 'text-gray-900' }}">Rp {{ number_format($baris['total_tunggakan'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-gray-500">Belum ada produk paket.</div>
                    @endforelse
                </div>
            </div>
        @else
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-gray-900">Definisi angka</p>
                    <p class="mt-0.5">Total Kebutuhan = Peserta Aktif x jumlah per orang (teks bebas pada isi paket). Estimasi Biaya = harga per item x Peserta Aktif, hanya untuk item yang punya harga (tanda - berarti tanpa harga). Angka harga hanya tersedia di laporan admin ini (keputusan D15).</p>
                </div>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Kebutuhan Barang Pengadaan</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-[#ebebeb]">
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Paket</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Item</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Jumlah per Orang</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Peserta Aktif</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Total Kebutuhan</th>
                                <th class="px-3 py-2 font-mono text-xs uppercase tracking-wider text-gray-500">Estimasi Biaya</th>
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
                                    <td class="px-3 py-2 font-mono text-sm {{ $baris['estimasi'] !== null ? 'text-gray-900' : 'text-gray-500' }}">
                                        {{ $baris['estimasi'] !== null ? 'Rp '.number_format($baris['estimasi'], 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-4 text-sm text-gray-500">Belum ada isi paket pada produk paket.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
                    @forelse($barangRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $baris['produk'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $baris['item'] }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Jumlah per Orang</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['jumlah'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Peserta Aktif</span>
                                    <span class="font-mono text-sm text-gray-600">{{ $baris['peserta'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Total Kebutuhan</span>
                                    <span class="font-mono text-sm text-gray-600">
                                        @if ($baris['total'] !== null)
                                            {{ $baris['total'] }}
                                        @else
                                            {{ $baris['peserta'] }} peserta × {{ $baris['jumlah'] }}
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Estimasi Biaya</span>
                                    <span class="font-mono text-sm {{ $baris['estimasi'] !== null ? 'text-gray-900' : 'text-gray-500' }}">
                                        {{ $baris['estimasi'] !== null ? 'Rp '.number_format($baris['estimasi'], 0, ',', '.') : '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-gray-500">Belum ada isi paket pada produk paket.</div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>
