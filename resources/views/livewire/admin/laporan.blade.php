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
        <h1 class="text-2xl font-semibold tracking-tight text-ink">Laporan Lengkap</h1>
        <p class="mt-1 text-sm text-mute">Rekapitulasi keuangan, kolektor, paket, dan kebutuhan barang.</p>
    </div>

    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-danger-soft px-4 py-3 text-sm text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach(['keuangan' => 'Keuangan', 'kolektor' => 'Per Kolektor', 'rekon' => 'Rekonsiliasi', 'umurkas' => 'Umur Kas', 'mutasi' => 'Mutasi', 'penarikan' => 'Penarikan', 'offline' => 'Penarikan Offline', 'tunggakan' => 'Tunggakan', 'serah' => 'Serah Terima', 'absensi' => 'Absensi', 'nasabah' => 'Nasabah', 'paket' => 'Per Paket', 'barang' => 'Kebutuhan Barang'] as $nilaiSeksi => $labelSeksi)
            <button wire:click="pilihSeksi('{{ $nilaiSeksi }}')"
                class="rounded-md px-4 py-2 text-sm font-medium transition {{ $seksi === $nilaiSeksi ? 'bg-accent text-accent-foreground' : 'bg-inset text-body shadow-[inset_0_0_0_1px_#e5e5e5] hover:bg-white' }}">
                {{ $labelSeksi }}
            </button>
        @endforeach
    </div>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        @if (in_array($seksi, ['keuangan', 'kolektor', 'rekon', 'mutasi', 'penarikan', 'offline', 'absensi'], true))
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div data-test="baris-periode" class="flex flex-wrap gap-2">
                    @foreach(['harian' => 'Harian', 'bulanan' => 'Bulanan', 'rentang' => 'Rentang'] as $value => $label)
                        <button wire:click="$set('periode', '{{ $value }}')"
                            class="rounded-md px-4 py-2 text-sm font-medium transition {{ $periode === $value ? 'bg-accent text-accent-foreground' : 'bg-inset text-body shadow-[inset_0_0_0_1px_#e5e5e5] hover:bg-white' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                @if($periode === 'rentang')
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="text-xs font-medium text-mute">Dari</label>
                        <x-ui.tanggal wire:model.live="dariTanggal" :max="now()->toDateString()"
                            class="w-full h-10 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                        <label class="text-xs font-medium text-mute">Sampai</label>
                        <x-ui.tanggal wire:model.live="sampaiTanggal" :min="$dariTanggal" :max="now()->toDateString()"
                            class="w-full h-10 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                    </div>
                @elseif($periode === 'harian')
                    <x-ui.tanggal wire:model.live="tanggal" :max="now()->toDateString()"
                        class="h-10 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                @else
                    <x-ui.tanggal wire:model.live="bulan" mode="bulan" :max="now()->toDateString()"
                        class="h-10 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10" />
                @endif
                @if ($seksi === 'mutasi')
                    <select wire:model.live="mutasiNasabahId"
                        class="h-10 rounded-md border border-hairline bg-white px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10">
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
                class="inline-flex items-center gap-2 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Export CSV
            </button>
            <button x-on:click="window.print()"
                class="inline-flex items-center gap-2 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                Cetak
            </button>
            <a href="{{ route('admin.komisi.index') }}"
                class="inline-flex items-center gap-2 rounded-md border border-hairline bg-white px-4 py-2 text-sm font-medium text-ink transition hover:bg-inset">
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
                'offline' => 'Penarikan Offline',
                'tunggakan' => 'Tunggakan & Paket Gagal',
                'serah' => 'Serah Terima Paket',
                'absensi' => 'Absensi & Izin',
                'nasabah' => 'Nasabah',
                default => 'Keuangan',
            };
            $judulPeriode = in_array($seksi, ['keuangan', 'kolektor', 'rekon', 'mutasi', 'penarikan', 'offline', 'absensi'], true)
                ? match ($periode) {
                    'bulanan' => $bulan,
                    'rentang' => $dariTanggal.' s/d '.$sampaiTanggal,
                    default => $tanggal,
                }
                : now()->toDateString();
        @endphp
        <div class="mb-4 hidden print:block">
            <h2 class="text-lg font-semibold text-ink">Laporan {{ $judulSeksi }} - {{ $judulPeriode }}</h2>
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-x-4 gap-y-1 rounded-lg bg-inset px-4 py-2 text-sm text-body shadow-[inset_0_0_0_1px_#e5e5e5]">
            <span class="font-medium">Nasabah Offline</span>
            <span class="tabular-nums font-semibold">{{ $jumlahNasabahOffline }}</span>
        </div>

        @if($seksi === 'keuangan')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-inset px-4 py-3 text-sm text-body shadow-[inset_0_0_0_1px_#e5e5e5]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-ink">Definisi angka</p>
                    <p class="mt-0.5">Total Setoran = jumlah setoran aktif (yang dibatalkan dikecualikan) berdasarkan tanggal transaksi. Total Penarikan = jumlah penarikan approved/selesai berdasarkan waktu approval. Total Komisi = jumlah komisi dari penarikan tersebut. Saldo Bersih = Total Setoran &minus; Total Penarikan &minus; Total Komisi. Pembulatan hanya terjadi di tampilan.</p>
                </div>
            </div>
            <div class="mb-6 grid gap-4 sm:grid-cols-4">
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Total Setoran</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">Rp {{ number_format($totalSetoran, 0, ',', '.') }}</p>
                    <p class="text-xs text-mute">{{ $jumlahTransaksiSetoran }} transaksi</p>
                </div>
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Total Penarikan</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">Rp {{ number_format($totalPenarikan, 0, ',', '.') }}</p>
                    <p class="text-xs text-mute">{{ $jumlahTransaksiPenarikan }} transaksi · {{ $jumlahOverrideRisiko }} override berisiko</p>
                </div>
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Total Komisi</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">Rp {{ number_format($totalKomisi, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Saldo Bersih</p>
                    @php $saldoBersih = $totalSetoran - $totalPenarikan - $totalKomisi; @endphp
                    <p class="mt-1 tabular-nums text-2xl font-semibold {{ $saldoBersih >= 0 ? 'text-ink' : 'text-danger' }}">Rp {{ number_format($saldoBersih, 0, ',', '.') }}</p>
                </div>
            </div>

            @if($periode === 'bulanan')
                <div class="rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <h3 class="mb-4 text-sm font-semibold text-ink">Rekap Harian</h3>
                    <div class="overflow-x-auto">
                        <table class="hidden md:table w-full text-left">
                            <thead>
                                <tr class="border-b border-hairline">
                                    <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tanggal</th>
                                    <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Setoran</th>
                                    <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Penarikan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-hairline">
                                @for($d = 1; $d <= $days; $d++)
                                    @php $day = str_pad($d, 2, '0', STR_PAD_LEFT); @endphp
                                    <tr class="transition hover:bg-inset">
                                        <th class="px-4 py-3 text-sm text-body">{{ $day }}</td>
                                        <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ isset($dailySetoran[$day]) ? 'Rp ' . number_format($dailySetoran[$day], 0, ',', '.') : '-' }}</td>
                                        <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ isset($dailyPenarikan[$day]) ? 'Rp ' . number_format($dailyPenarikan[$day], 0, ',', '.') : '-' }}</td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                    <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                        @for($d = 1; $d <= $days; $d++)
                            @php $day = str_pad($d, 2, '0', STR_PAD_LEFT); @endphp
                            <div class="px-4 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-ink">{{ $day }}</p>
                                    </div>
                                </div>
                                <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                    <div>
                                        <span class="block text-xs text-mute">Setoran</span>
                                        <span class="tabular-nums text-sm text-body">{{ isset($dailySetoran[$day]) ? 'Rp ' . number_format($dailySetoran[$day], 0, ',', '.') : '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-mute">Penarikan</span>
                                        <span class="tabular-nums text-sm text-body">{{ isset($dailyPenarikan[$day]) ? 'Rp ' . number_format($dailyPenarikan[$day], 0, ',', '.') : '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            @endif
        @elseif($seksi === 'kolektor')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-inset px-4 py-3 text-sm text-body shadow-[inset_0_0_0_1px_#e5e5e5]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-ink">Definisi angka</p>
                    <p class="mt-0.5">Total Setoran = setoran yang diinput kolektor pada periode. Penarikan Tunai Dibayar = jumlah penarikan tunai (nominal diterima) yang dibayarkan kolektor pada periode. Kas di Tangan = setoran belum disetor kantor &minus; penarikan tunai belum direkonsiliasi (rumus D13, posisi terkini bukan periode). Setor Kantor = total pengajuan setor ke kantor pada periode. Selisih Rekon = selisih rekon lebih/kurang pada periode.</p>
                </div>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <h3 class="mb-4 text-sm font-semibold text-ink">Rekap Per Kolektor</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-hairline">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Kolektor</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Total Setoran</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Jumlah Transaksi</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Selisih Rekon</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Penarikan Tunai Dibayar</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Kas di Tangan</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Setor Kantor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @forelse($kolektorRows as $baris)
                                <tr class="transition hover:bg-inset">
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['nama'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($baris['total_setoran'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $baris['jumlah_transaksi'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm {{ $baris['selisih'] < 0 ? 'text-danger' : 'text-ink' }}">Rp {{ number_format($baris['selisih'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($baris['penarikan_dibayar'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($baris['setor_kantor'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-4 text-sm text-mute">Belum ada data kolektor.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                    @forelse($kolektorRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink">{{ $baris['nama'] }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-xs text-mute">Total Setoran</span>
                                    <span class="tabular-nums text-sm text-body">Rp {{ number_format($baris['total_setoran'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Jumlah Transaksi</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['jumlah_transaksi'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Selisih Rekon</span>
                                    <span class="tabular-nums text-sm {{ $baris['selisih'] < 0 ? 'text-danger' : 'text-ink' }}">Rp {{ number_format($baris['selisih'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Penarikan Tunai Dibayar</span>
                                    <span class="tabular-nums text-sm text-body">Rp {{ number_format($baris['penarikan_dibayar'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Kas di Tangan</span>
                                    <span class="tabular-nums text-sm text-body">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Setor Kantor</span>
                                    <span class="tabular-nums text-sm text-body">Rp {{ number_format($baris['setor_kantor'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-mute">Belum ada data kolektor.</div>
                    @endforelse
                </div>
            </div>
        @elseif($seksi === 'rekon')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-inset px-4 py-3 text-sm text-body shadow-[inset_0_0_0_1px_#e5e5e5]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-ink">Definisi angka</p>
                    <p class="mt-0.5">Seharusnya = total seharusnya disetor kolektor. Diterima = total yang benar-benar diterima kantor. Selisih = Diterima &minus; Seharusnya (lebih = positif, kurang = negatif). Penerima = admin yang menerima/merekonsiliasi pengajuan. Seluruh status pengajuan (pending, cocok, lebih, kurang, dibatalkan) pada periode disertakan agar riwayat tetap terlacak.</p>
                </div>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <h3 class="mb-4 text-sm font-semibold text-ink">Rekap Pengajuan Setor per Kolektor</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-hairline">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Kolektor</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Pengajuan</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Seharusnya</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Diterima</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @forelse($rekonRows as $baris)
                                <tr class="transition hover:bg-inset">
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['nama'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $baris['jumlah'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($baris['seharusnya'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($baris['diterima'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm {{ $baris['selisih'] != 0 ? 'text-danger' : 'text-ink' }}">Rp {{ number_format($baris['selisih'], 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="px-3 py-4 text-sm text-mute">Belum ada pengajuan setor pada periode ini.</td></tr>
                            @endforelse
                            @if(count($rekonRows) > 0)
                                <tr class="bg-inset font-semibold">
                                    <th class="px-4 py-3 text-sm text-ink">TOTAL</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $rekonTotal['jumlah'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($rekonTotal['seharusnya'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($rekonTotal['diterima'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm {{ $rekonTotal['selisih'] != 0 ? 'text-danger' : 'text-ink' }}">Rp {{ number_format($rekonTotal['selisih'], 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                    @forelse($rekonRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink">{{ $baris['nama'] }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-xs text-mute">Pengajuan</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['jumlah'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Seharusnya</span>
                                    <span class="tabular-nums text-sm text-body">Rp {{ number_format($baris['seharusnya'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Diterima</span>
                                    <span class="tabular-nums text-sm text-body">Rp {{ number_format($baris['diterima'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Selisih</span>
                                    <span class="tabular-nums text-sm {{ $baris['selisih'] != 0 ? 'text-danger' : 'text-ink' }}">Rp {{ number_format($baris['selisih'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-mute">Belum ada pengajuan setor pada periode ini.</div>
                    @endforelse
                    @if(count($rekonRows) > 0)
                        <div class="flex justify-between px-4 py-2.5 text-sm font-semibold text-ink bg-inset">
                            <span>TOTAL</span>
                            <span class="tabular-nums">{{ $rekonTotal['jumlah'] }} · Rp {{ number_format($rekonTotal['seharusnya'], 0, ',', '.') }} · Rp {{ number_format($rekonTotal['diterima'], 0, ',', '.') }} · <span class="{{ $rekonTotal['selisih'] != 0 ? 'text-danger' : 'text-ink' }}">Rp {{ number_format($rekonTotal['selisih'], 0, ',', '.') }}</span></span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="mt-6 rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <h3 class="mb-4 text-sm font-semibold text-ink">Detail Pengajuan</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-hairline">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tanggal</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Kolektor</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Seharusnya</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Diterima</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Selisih</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Keterangan</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Penerima</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @forelse($rekonDetail as $item)
                                <tr class="transition hover:bg-inset">
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ \Carbon\Carbon::parse($item->tanggal_setor)->format('d/m/Y') }}</td>
                                    <th class="px-4 py-3 text-sm text-ink">{{ $item->kolektor->name ?? '-' }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format((float) $item->total_seharusnya, 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format((float) $item->total_diterima, 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm {{ (float) $item->selisih != 0 ? 'text-danger' : 'text-ink' }}">Rp {{ number_format((float) $item->selisih, 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 text-sm text-body">{{ $item->keterangan_selisih ?? '-' }}</td>
                                    <th class="px-4 py-3 text-sm text-ink">{{ $item->diterimaOleh->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-4 text-sm text-mute">Belum ada pengajuan setor pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                    @forelse($rekonDetail as $item)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink">{{ $item->kolektor->name ?? '-' }}</p>
                                    <p class="text-xs text-mute">{{ \Carbon\Carbon::parse($item->tanggal_setor)->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-xs text-mute">Seharusnya</span>
                                    <span class="tabular-nums text-sm text-body">Rp {{ number_format((float) $item->total_seharusnya, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Diterima</span>
                                    <span class="tabular-nums text-sm text-body">Rp {{ number_format((float) $item->total_diterima, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Selisih</span>
                                    <span class="tabular-nums text-sm {{ (float) $item->selisih != 0 ? 'text-danger' : 'text-ink' }}">Rp {{ number_format((float) $item->selisih, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Keterangan</span>
                                    <span class="tabular-nums text-sm text-body">{{ $item->keterangan_selisih ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Penerima</span>
                                    <span class="tabular-nums text-sm text-body">{{ $item->diterimaOleh->name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-mute">Belum ada pengajuan setor pada periode ini.</div>
                    @endforelse
                </div>
            </div>
        @elseif($seksi === 'umurkas')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-inset px-4 py-3 text-sm text-body shadow-[inset_0_0_0_1px_#e5e5e5]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-ink">Definisi angka</p>
                    <p class="mt-0.5">Kas di Tangan = setoran belum disetor kantor &minus; penarikan tunai belum direkonsiliasi (rumus D13, posisi terkini). Umur = hari sejak setoran belum disetor terkoleksi; kelompok 0-1, 2-3, dan lebih dari 3 hari. Lewat Batas = kas melebihi batas kas dan/atau umur melebihi batas hari sesuai pengaturan admin. Laporan ini adalah posisi terkini, bukan periode.</p>
                </div>
            </div>
            <div class="mb-6 grid gap-4 sm:grid-cols-3">
                @foreach($umurKelompok as $kelompok => $ringkas)
                    <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                        <p class="text-xs text-mute">{{ $kelompok }}</p>
                        <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">Rp {{ number_format($ringkas['kas'], 0, ',', '.') }}</p>
                        <p class="text-xs text-mute">{{ $ringkas['jumlah'] }} kolektor</p>
                    </div>
                @endforeach
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <h3 class="mb-4 text-sm font-semibold text-ink">Umur Kas Per Kolektor</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-hairline">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Kolektor</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Kas di Tangan</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Umur Terlama</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Kelompok</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Lewat Batas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @forelse($umurKasRows as $baris)
                                @php
                                    $kunciUmur = match (true) {
                                        $baris['umur_terlama_hari'] <= 1 => '0-1 hari',
                                        $baris['umur_terlama_hari'] <= 3 => '2-3 hari',
                                        default => '>3 hari',
                                    };
                                @endphp
                                <tr class="transition hover:bg-inset">
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['nama'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $baris['umur_terlama_hari'] }} hari</td>
                                    <th class="px-4 py-3 text-sm text-ink">{{ $kunciUmur }}</td>
                                    <th class="px-4 py-3 text-sm">
                                        @if($baris['lewat_batas'])
                                            <span class="inline-flex items-center rounded-full bg-danger-soft px-2 py-0.5 text-xs text-danger">Ya</span>
                                        @else
                                            <span class="text-mute">Tidak</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-4 text-sm text-mute">Belum ada data kolektor.</td></tr>
                            @endforelse
                            @if(count($umurKasRows) > 0)
                                <tr class="bg-inset font-semibold">
                                    <th class="px-4 py-3 text-sm text-ink">TOTAL</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($umurTotal['kas'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">&mdash;</td>
                                    <th class="px-4 py-3 text-sm text-ink">{{ $umurTotal['jumlah'] }} kolektor</td>
                                    <th class="px-4 py-3 text-sm text-ink">{{ $umurTotal['lewat_batas'] }} lewat</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
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
                                    <p class="text-sm font-medium text-ink">{{ $baris['nama'] }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-xs text-mute">Kas di Tangan</span>
                                    <span class="tabular-nums text-sm text-body">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Umur Terlama</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['umur_terlama_hari'] }} hari</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Kelompok</span>
                                    <span class="tabular-nums text-sm text-body">{{ $kunciUmur }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Lewat Batas</span>
                                    <span class="tabular-nums text-sm text-body">
                                        @if($baris['lewat_batas'])
                                            <span class="inline-flex items-center rounded-full bg-danger-soft px-2 py-0.5 text-xs text-danger">Ya</span>
                                        @else
                                            <span class="text-mute">Tidak</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-mute">Belum ada data kolektor.</div>
                    @endforelse
                    @if(count($umurKasRows) > 0)
                        <div class="flex justify-between px-4 py-2.5 text-sm font-semibold text-ink bg-inset">
                            <span>TOTAL</span>
                            <span class="tabular-nums">Rp {{ number_format($umurTotal['kas'], 0, ',', '.') }} · &mdash; · {{ $umurTotal['jumlah'] }} kolektor · {{ $umurTotal['lewat_batas'] }} lewat</span>
                        </div>
                    @endif
                </div>
            </div>
        @elseif($seksi === 'mutasi')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-inset px-4 py-3 text-sm text-body shadow-[inset_0_0_0_1px_#e5e5e5]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-ink">Definisi angka</p>
                    <p class="mt-0.5">Saldo Awal = saldo saat ini dikurangi seluruh mutasi sejak awal periode. Saldo Berjalan = saldo setelah setiap baris. Setoran hanya yang aktif (dibatalkan dikecualikan); penarikan hanya approved/selesai. Cocokkan dengan buku tabungan fisik untuk nasabah offline (D14).</p>
                </div>
            </div>
            <div class="mb-6 grid gap-4 sm:grid-cols-4">
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Saldo Awal</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">Rp {{ number_format($mutasiRingkas['saldoAwal'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Total Setoran</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">Rp {{ number_format($mutasiRingkas['totalSetoran'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Total Penarikan</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">Rp {{ number_format($mutasiRingkas['totalPenarikan'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Saldo Akhir</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">Rp {{ number_format($mutasiRingkas['saldoAkhir'], 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <h3 class="mb-4 text-sm font-semibold text-ink">Buku Tabungan</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-hairline">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tanggal</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tipe</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Produk</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Nominal</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Saldo Berjalan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @forelse($mutasiRows as $baris)
                                <tr class="transition hover:bg-inset">
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ \Carbon\Carbon::parse($baris['tanggal'])->format('d/m/Y') }}</td>
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['tipe'] }}</td>
                                    <th class="px-4 py-3 text-sm text-body">{{ $baris['produk'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm {{ $baris['arah'] > 0 ? 'text-ink' : 'text-danger' }}">{{ $baris['arah'] > 0 ? '+' : '-' }} Rp {{ number_format($baris['nominal'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($baris['saldo'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-4 text-sm text-mute">{{ $mutasiNasabahId > 0 ? 'Belum ada mutasi pada periode ini.' : 'Pilih nasabah untuk menampilkan buku tabungan.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                    @forelse($mutasiRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink">{{ \Carbon\Carbon::parse($baris['tanggal'])->format('d/m/Y') }}</p>
                                    <p class="text-xs text-mute">{{ $baris['tipe'] }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-xs text-mute">Produk</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['produk'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Nominal</span>
                                    <span class="tabular-nums text-sm {{ $baris['arah'] > 0 ? 'text-ink' : 'text-danger' }}">{{ $baris['arah'] > 0 ? '+' : '-' }} Rp {{ number_format($baris['nominal'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Saldo Berjalan</span>
                                    <span class="tabular-nums text-sm text-body">Rp {{ number_format($baris['saldo'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-mute">{{ $mutasiNasabahId > 0 ? 'Belum ada mutasi pada periode ini.' : 'Pilih nasabah untuk menampilkan buku tabungan.' }}</div>
                    @endforelse
                </div>
            </div>
        @elseif($seksi === 'penarikan')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-inset px-4 py-3 text-sm text-body shadow-[inset_0_0_0_1px_#e5e5e5]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-ink">Definisi angka</p>
                    <p class="mt-0.5">Periode mengikuti saat pengajuan diajukan (bukan saat diproses). Rekap per status: pending, approved, selesai, ditolak, dibatalkan, kedaluwarsa. Total Diminta = nominal diminta nasabah. Waktu Proses = waktu approval (kosong bila belum diproses). Alasan terisi untuk pengajuan yang dibatalkan/ditolak.</p>
                </div>
            </div>
            <div class="mb-6 grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @foreach($penarikanRekap as $ringkas)
                    <div class="rounded-lg bg-inset p-4 shadow-[inset_0_0_0_1px_#e5e5e5]">
                        <p class="text-xs text-mute">{{ $ringkas['label'] }}</p>
                        <p class="mt-1 tabular-nums text-2xl font-semibold {{ in_array($ringkas['status'], ['ditolak', 'dibatalkan'], true) ? 'text-danger' : 'text-ink' }}">{{ $ringkas['jumlah'] }}</p>
                        <p class="text-xs text-mute">Rp {{ number_format($ringkas['total'], 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <h3 class="mb-4 text-sm font-semibold text-ink">Detail Penarikan</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-hairline">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tanggal</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nasabah</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Produk</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Diminta</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Komisi</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Diterima</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Status</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Lokasi</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Waktu Proses</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @forelse($penarikanRows as $baris)
                                <tr class="transition hover:bg-inset">
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $baris['tanggal'] }}</td>
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['nasabah'] }}</td>
                                    <th class="px-4 py-3 text-sm text-body">{{ $baris['produk'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($baris['diminta'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($baris['komisi'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($baris['diterima'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 text-sm {{ in_array($baris['status'], ['ditolak', 'dibatalkan'], true) ? 'text-danger' : 'text-ink' }}">{{ $baris['status'] }}</td>
                                    <th class="px-4 py-3 text-sm text-body">{{ $baris['lokasi'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $baris['waktu_proses'] ?? '-' }}</td>
                                    <th class="px-4 py-3 text-sm text-body">{{ $baris['alasan'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="px-3 py-4 text-sm text-mute">Belum ada penarikan pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                    @forelse($penarikanRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink">{{ $baris['nasabah'] }}</p>
                                    <p class="text-xs text-mute">{{ $baris['tanggal'] }}</p>
                                </div>
                                <span class="shrink-0 text-sm {{ in_array($baris['status'], ['ditolak', 'dibatalkan'], true) ? 'text-danger' : 'text-ink' }}">{{ $baris['status'] }}</span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-xs text-mute">Produk</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['produk'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Diminta</span>
                                    <span class="tabular-nums text-sm text-body">Rp {{ number_format($baris['diminta'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Komisi</span>
                                    <span class="tabular-nums text-sm text-body">Rp {{ number_format($baris['komisi'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Diterima</span>
                                    <span class="tabular-nums text-sm text-body">Rp {{ number_format($baris['diterima'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Lokasi</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['lokasi'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Waktu Proses</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['waktu_proses'] ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Alasan</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['alasan'] }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-mute">Belum ada penarikan pada periode ini.</div>
                    @endforelse
                </div>
            </div>
        @elseif($seksi === 'offline')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-inset px-4 py-3 text-sm text-body shadow-[inset_0_0_0_1px_#e5e5e5]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-ink">Definisi angka</p>
                    <p class="mt-0.5">Penarikan offline langsung = penarikan tunai yang diselesaikan kolektor tanpa PIN nasabah (verifikasi offline) dan menunggu tinjauan admin. Belum Ditinjau = baris yang hasil tinjauannya masih kosong. Persen Kas = total penarikan langsung kolektor &divide; total seluruh penarikan yang mempengaruhi kas milik kolektor tersebut pada periode ini.</p>
                </div>
            </div>
            <div class="mb-6 rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <h3 class="mb-4 text-sm font-semibold text-ink">Rekap Per Kolektor</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-hairline">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Kolektor</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Jumlah</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Total Diterima</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Persen Kas</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Belum Ditinjau</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Sesuai</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Bermasalah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @forelse($offlineRows as $baris)
                                <tr class="transition hover:bg-inset">
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['kolektor'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $baris['jumlah'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($baris['total'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ number_format($baris['persen_kas'], 1, ',', '.') }}%</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $baris['belum'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $baris['sesuai'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm {{ $baris['bermasalah'] > 0 ? 'text-danger' : 'text-ink' }}">{{ $baris['bermasalah'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-4 text-sm text-mute">Belum ada penarikan offline langsung pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                    @forelse($offlineRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-medium text-ink">{{ $baris['kolektor'] }}</p>
                                <span class="shrink-0 tabular-nums text-sm text-ink">{{ number_format($baris['persen_kas'], 1, ',', '.') }}%</span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-xs text-mute">Jumlah</span>
                                    <span class="tabular-nums text-sm text-ink">{{ $baris['jumlah'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Total Diterima</span>
                                    <span class="tabular-nums text-sm text-ink">Rp {{ number_format($baris['total'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Belum Ditinjau</span>
                                    <span class="tabular-nums text-sm text-ink">{{ $baris['belum'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Sesuai</span>
                                    <span class="tabular-nums text-sm text-ink">{{ $baris['sesuai'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Bermasalah</span>
                                    <span class="tabular-nums text-sm {{ $baris['bermasalah'] > 0 ? 'text-danger' : 'text-ink' }}">{{ $baris['bermasalah'] }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-mute">Belum ada penarikan offline langsung pada periode ini.</div>
                    @endforelse
                </div>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <h3 class="mb-4 text-sm font-semibold text-ink">Detail Penarikan Offline</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-hairline">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tanggal</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nasabah</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Kolektor</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Diterima</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tinjauan</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Bukti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @forelse($offlineDetail as $baris)
                                <tr class="transition hover:bg-inset">
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $baris['tanggal'] }}</td>
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['nasabah'] }}</td>
                                    <th class="px-4 py-3 text-sm text-body">{{ $baris['kolektor'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($baris['diterima'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 text-sm {{ $baris['tinjauan'] === 'Bermasalah' ? 'text-danger' : ($baris['tinjauan'] === 'Belum ditinjau' ? 'text-warning-deep' : 'text-body') }}">{{ $baris['tinjauan'] }}</td>
                                    <th class="px-4 py-3 text-sm">
                                        @if($baris['bukti'] !== null)
                                            <a href="{{ $baris['bukti'] }}" target="_blank" rel="noopener" class="font-medium text-info hover:underline">Lihat bukti</a>
                                        @else
                                            <span class="text-mute">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-4 text-sm text-mute">Belum ada penarikan offline langsung pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                    @forelse($offlineDetail as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink">{{ $baris['nasabah'] }}</p>
                                    <p class="text-xs text-mute">{{ $baris['tanggal'] }} &middot; {{ $baris['kolektor'] }}</p>
                                </div>
                                <span class="shrink-0 text-sm {{ $baris['tinjauan'] === 'Bermasalah' ? 'text-danger' : ($baris['tinjauan'] === 'Belum ditinjau' ? 'text-warning-deep' : 'text-body') }}">{{ $baris['tinjauan'] }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-3">
                                <span class="tabular-nums text-sm text-ink">Rp {{ number_format($baris['diterima'], 0, ',', '.') }}</span>
                                @if($baris['bukti'] !== null)
                                    <a href="{{ $baris['bukti'] }}" target="_blank" rel="noopener" class="text-sm font-medium text-info hover:underline">Lihat bukti</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-mute">Belum ada penarikan offline langsung pada periode ini.</div>
                    @endforelse
                </div>
            </div>
        @elseif($seksi === 'tunggakan')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-inset px-4 py-3 text-sm text-body shadow-[inset_0_0_0_1px_#e5e5e5]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-ink">Definisi angka</p>
                    <p class="mt-0.5">Tunggakan hari = hari berjalan &minus; hari terbayar (peserta aktif yang belum diserahkan). Tunggakan (Rp) = tunggakan hari &times; harga per hari. Status Alert: peringatan bila menunggak, perlu review bila melewati batas toleransi tanpa penundaan. Keputusan akhir kosong berarti masih berjalan. Posisi terkini, bukan periode.</p>
                </div>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <h3 class="mb-4 text-sm font-semibold text-ink">Nasabah Menunggak</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-hairline">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nasabah</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Produk</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tunggakan (hari)</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tunggakan (Rp)</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Status Alert</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Keputusan Akhir</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Ditunda Hingga</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @forelse($tunggakanRows as $baris)
                                <tr class="transition hover:bg-inset">
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['nasabah'] }}</td>
                                    <th class="px-4 py-3 text-sm text-body">{{ $baris['produk'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $baris['tunggakan_hari'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($baris['tunggakan_rupiah'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 text-sm {{ $baris['status_alert'] === 'Perlu Review' ? 'text-danger' : 'text-ink' }}">{{ $baris['status_alert'] }}</td>
                                    <th class="px-4 py-3 text-sm text-body">{{ $baris['keputusan_akhir'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-body">{{ $baris['ditunda_hingga'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-4 text-sm text-mute">Tidak ada nasabah menunggak.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                    @forelse($tunggakanRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink">{{ $baris['nasabah'] }}</p>
                                    <p class="text-xs text-mute">{{ $baris['produk'] }}</p>
                                </div>
                                <span class="shrink-0 text-sm {{ $baris['status_alert'] === 'Perlu Review' ? 'text-danger' : 'text-ink' }}">{{ $baris['status_alert'] }}</span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-xs text-mute">Tunggakan (hari)</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['tunggakan_hari'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Tunggakan (Rp)</span>
                                    <span class="tabular-nums text-sm text-body">Rp {{ number_format($baris['tunggakan_rupiah'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Keputusan Akhir</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['keputusan_akhir'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Ditunda Hingga</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['ditunda_hingga'] ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-mute">Tidak ada nasabah menunggak.</div>
                    @endforelse
                </div>
            </div>
        @elseif($seksi === 'serah')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-inset px-4 py-3 text-sm text-body shadow-[inset_0_0_0_1px_#e5e5e5]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-ink">Definisi angka</p>
                    <p class="mt-0.5">Status serah terima: belum (masih berjalan) atau sudah diterima. Metode = cara paket diambil. Penerima = nama yang menandatangani serah terima. Tanggal = tanggal serah terima (kosong bila belum). Foto bukti hanya dapat dilihat oleh admin (D15). Posisi terkini, bukan periode.</p>
                </div>
            </div>
            <div class="mb-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Sudah Diterima</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">{{ collect($serahRows)->where('status_kunci', 'sudah_diterima')->count() }} paket</p>
                </div>
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Belum</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">{{ collect($serahRows)->where('status_kunci', 'belum')->count() }} paket</p>
                </div>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <h3 class="mb-4 text-sm font-semibold text-ink">Detail Serah Terima</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-hairline">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nasabah</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Produk</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Status</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Metode</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Penerima</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tanggal</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Foto Bukti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @forelse($serahRows as $baris)
                                <tr class="transition hover:bg-inset">
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['nasabah'] }}</td>
                                    <th class="px-4 py-3 text-sm text-body">{{ $baris['produk'] }}</td>
                                    <th class="px-4 py-3 text-sm {{ $baris['status_kunci'] === 'sudah_diterima' ? 'text-ink' : 'text-mute' }}">{{ $baris['status'] }}</td>
                                    <th class="px-4 py-3 text-sm text-body">{{ $baris['metode'] }}</td>
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['penerima'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $baris['tanggal'] ?? '-' }}</td>
                                    <th class="px-4 py-3 text-sm">
                                        @if ($baris['foto'] !== null)
                                            <a href="{{ $baris['foto'] }}" target="_blank" rel="noopener noreferrer" class="text-info underline-offset-2 hover:underline">Lihat</a>
                                        @else
                                            <span class="text-mute">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-4 text-sm text-mute">Belum ada kepesertaan paket.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                    @forelse($serahRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink">{{ $baris['nasabah'] }}</p>
                                    <p class="text-xs text-mute">{{ $baris['produk'] }}</p>
                                </div>
                                <span class="shrink-0 text-sm {{ $baris['status_kunci'] === 'sudah_diterima' ? 'text-ink' : 'text-mute' }}">{{ $baris['status'] }}</span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-xs text-mute">Metode</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['metode'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Penerima</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['penerima'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Tanggal</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['tanggal'] ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Foto Bukti</span>
                                    <span class="tabular-nums text-sm text-body">
                                        @if ($baris['foto'] !== null)
                                            <a href="{{ $baris['foto'] }}" target="_blank" rel="noopener noreferrer" class="text-info underline-offset-2 hover:underline">Lihat</a>
                                        @else
                                            <span class="text-mute">-</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-mute">Belum ada kepesertaan paket.</div>
                    @endforelse
                </div>
            </div>
        @elseif($seksi === 'absensi')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-inset px-4 py-3 text-sm text-body shadow-[inset_0_0_0_1px_#e5e5e5]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-ink">Definisi angka</p>
                    <p class="mt-0.5">Hadir = jumlah hari absensi kolektor tercatat (satu baris per tanggal per kolektor) pada periode berjalan. Izin Disetujui dan Izin Pending dihitung dari izin yang tanggalnya tumpang tindih dengan periode. Jam Masuk dan Jam Keluar berasal dari absensi harian kolektor.</p>
                </div>
            </div>
            <div class="mb-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Hadir</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">{{ $absensiRekap['hadir'] }}</p>
                </div>
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Izin Disetujui</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">{{ $absensiRekap['izinDisetujui'] }}</p>
                </div>
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Izin Pending</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">{{ $absensiRekap['izinPending'] }}</p>
                </div>
            </div>

            <div class="mb-6 rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <h3 class="mb-4 text-sm font-semibold text-ink">Absensi Kolektor</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-hairline">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Tanggal</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Kolektor</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Jam Masuk</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Jam Keluar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @forelse($absensiRows as $baris)
                                <tr class="transition hover:bg-inset">
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $baris['tanggal'] }}</td>
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['kolektor'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $baris['jam_masuk'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm {{ $baris['jam_keluar'] !== null ? 'text-ink' : 'text-danger' }}">{{ $baris['jam_keluar'] ?? 'Belum keluar' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-4 text-sm text-mute">Belum ada absensi pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                    @forelse($absensiRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink">{{ $baris['kolektor'] }}</p>
                                    <p class="text-xs text-mute">{{ $baris['tanggal'] }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-xs text-mute">Jam Masuk</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['jam_masuk'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Jam Keluar</span>
                                    <span class="tabular-nums text-sm {{ $baris['jam_keluar'] !== null ? 'text-ink' : 'text-danger' }}">{{ $baris['jam_keluar'] ?? 'Belum keluar' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-mute">Belum ada absensi pada periode ini.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <h3 class="mb-4 text-sm font-semibold text-ink">Izin Kolektor</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-hairline">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Kolektor</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Dari</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Sampai</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Alasan</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Status</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Diproses Oleh</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @forelse($izinRows as $baris)
                                <tr class="transition hover:bg-inset">
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['kolektor'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-body">{{ $baris['dari'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-body">{{ $baris['sampai'] }}</td>
                                    <th class="px-4 py-3 text-sm text-body">{{ $baris['alasan'] }}</td>
                                    <th class="px-4 py-3 text-sm {{ $baris['status'] === 'Ditolak' ? 'text-danger' : 'text-ink' }}">{{ $baris['status'] }}</td>
                                    <th class="px-4 py-3 text-sm text-body">{{ $baris['pemroses'] }}</td>
                                    <th class="px-4 py-3 text-sm text-mute">{{ $baris['catatan'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-4 text-sm text-mute">Tidak ada izin pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                    @forelse($izinRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink">{{ $baris['kolektor'] }}</p>
                                </div>
                                <span class="shrink-0 text-sm {{ $baris['status'] === 'Ditolak' ? 'text-danger' : 'text-ink' }}">{{ $baris['status'] }}</span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-xs text-mute">Dari</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['dari'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Sampai</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['sampai'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Alasan</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['alasan'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Diproses Oleh</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['pemroses'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Catatan</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['catatan'] ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-mute">Tidak ada izin pada periode ini.</div>
                    @endforelse
                </div>
            </div>
        @elseif($seksi === 'nasabah')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-inset px-4 py-3 text-sm text-body shadow-[inset_0_0_0_1px_#e5e5e5]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-ink">Definisi angka</p>
                    <p class="mt-0.5">Status = status pendaftaran nasabah pada posisi terkini (tanpa filter periode). Mode Akses = Digital atau Offline sesuai akun nasabah. Kolektor = penanggung jawab aktif dari nasabah tersebut; tanda &minus; berarti belum ditangani kolektor mana pun. Rekap Terakhir Dicetak = tanggal terakhir tombol Cetak Rekap ditekan untuk nasabah tersebut (Belum pernah = belum pernah dicetak); baris diurutkan dari yang paling lama agar siklus cetak nasabah offline mudah dipantau. Kolom ini murni informasi, tanpa ambang angka.</p>
                </div>
            </div>
            <div class="mb-6 grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Aktif</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">{{ $nasabahRekap['aktif'] }}</p>
                </div>
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Pending Verifikasi</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">{{ $nasabahRekap['pending'] }}</p>
                </div>
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Ditolak</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">{{ $nasabahRekap['ditolak'] }}</p>
                </div>
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Digital</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">{{ $nasabahRekap['digital'] }}</p>
                </div>
                <div class="rounded-lg bg-inset p-5 shadow-[inset_0_0_0_1px_#e5e5e5]">
                    <p class="text-xs text-mute">Offline</p>
                    <p class="mt-1 tabular-nums text-2xl font-semibold text-ink">{{ $nasabahRekap['offline'] }}</p>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <h3 class="mb-4 text-sm font-semibold text-ink">Daftar Nasabah</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-hairline">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Nama</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">No HP</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Status Pendaftaran</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Mode Akses</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Kolektor</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Rekap Terakhir Dicetak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @forelse($nasabahRows as $baris)
                                <tr class="transition hover:bg-inset">
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['nama'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-body">{{ $baris['no_hp'] }}</td>
                                    <th class="px-4 py-3 text-sm {{ in_array($baris['status_pendaftaran'], ['Ditolak'], true) ? 'text-danger' : 'text-ink' }}">{{ $baris['status_pendaftaran'] }}</td>
                                    <th class="px-4 py-3 text-sm text-body">{{ $baris['mode_akses'] }}</td>
                                    <th class="px-4 py-3 text-sm text-body">{{ $baris['kolektor'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-body">{{ $baris['rekap_terakhir_dicetak'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-4 text-sm text-mute">Belum ada nasabah terdaftar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                    @forelse($nasabahRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink">{{ $baris['nama'] }}</p>
                                    <p class="text-xs text-mute">{{ $baris['no_hp'] }}</p>
                                </div>
                                <span class="shrink-0 text-sm {{ in_array($baris['status_pendaftaran'], ['Ditolak'], true) ? 'text-danger' : 'text-ink' }}">{{ $baris['status_pendaftaran'] }}</span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-xs text-mute">Mode Akses</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['mode_akses'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Kolektor</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['kolektor'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Rekap Dicetak</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['rekap_terakhir_dicetak'] }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-mute">Belum ada nasabah terdaftar.</div>
                    @endforelse
                </div>
            </div>
        @elseif($seksi === 'paket')
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-inset px-4 py-3 text-sm text-body shadow-[inset_0_0_0_1px_#e5e5e5]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-ink">Definisi angka</p>
                    <p class="mt-0.5">Peserta Aktif = kepesertaan tanpa keputusan akhir dan belum diserahkan. Total Terkumpul = jumlah total aktual terkumpul seluruh peserta aktif. Total Tunggakan = jumlah tunggakan seluruh peserta aktif.</p>
                </div>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <h3 class="mb-4 text-sm font-semibold text-ink">Rekap Per Paket</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-hairline">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Paket</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Peserta Aktif</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Total Terkumpul</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Total Tunggakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @forelse($paketRows as $baris)
                                <tr class="transition hover:bg-inset">
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['produk'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $baris['peserta_aktif'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm text-ink">Rp {{ number_format($baris['total_terkumpul'], 0, ',', '.') }}</td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm {{ $baris['total_tunggakan'] > 0 ? 'text-danger' : 'text-ink' }}">Rp {{ number_format($baris['total_tunggakan'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-4 text-sm text-mute">Belum ada produk paket.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                    @forelse($paketRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink">{{ $baris['produk'] }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-xs text-mute">Peserta Aktif</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['peserta_aktif'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Total Terkumpul</span>
                                    <span class="tabular-nums text-sm text-body">Rp {{ number_format($baris['total_terkumpul'], 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Total Tunggakan</span>
                                    <span class="tabular-nums text-sm {{ $baris['total_tunggakan'] > 0 ? 'text-danger' : 'text-ink' }}">Rp {{ number_format($baris['total_tunggakan'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-mute">Belum ada produk paket.</div>
                    @endforelse
                </div>
            </div>
        @else
            <div class="mb-4 flex items-start gap-2 rounded-lg bg-inset px-4 py-3 text-sm text-body shadow-[inset_0_0_0_1px_#e5e5e5]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <p class="font-medium text-ink">Definisi angka</p>
                    <p class="mt-0.5">Total Kebutuhan = Peserta Aktif x jumlah per orang (teks bebas pada isi paket). Estimasi Biaya = harga per item x Peserta Aktif, hanya untuk item yang punya harga (tanda - berarti tanpa harga). Angka harga hanya tersedia di laporan admin ini (keputusan D15).</p>
                </div>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0_0_0_1px_#e5e5e5]">
                <h3 class="mb-4 text-sm font-semibold text-ink">Kebutuhan Barang Pengadaan</h3>
                <div class="overflow-x-auto">
                    <table class="hidden md:table w-full text-left">
                        <thead>
                            <tr class="border-b border-hairline">
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Paket</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Item</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Jumlah per Orang</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Peserta Aktif</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Total Kebutuhan</th>
                                <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Estimasi Biaya</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @forelse($barangRows as $baris)
                                <tr class="transition hover:bg-inset">
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['produk'] }}</td>
                                    <th class="px-4 py-3 text-sm text-ink">{{ $baris['item'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-body">{{ $baris['jumlah'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">{{ $baris['peserta'] }}</td>
                                    <th class="px-4 py-3 tabular-nums text-sm text-ink">
                                        @if ($baris['total'] !== null)
                                            {{ $baris['total'] }}
                                        @else
                                            {{ $baris['peserta'] }} peserta × {{ $baris['jumlah'] }}
                                        @endif
                                    </td>
                                    <th class="px-4 py-3 tabular-nums text-right text-sm {{ $baris['estimasi'] !== null ? 'text-ink' : 'text-mute' }}">
                                        {{ $baris['estimasi'] !== null ? 'Rp '.number_format($baris['estimasi'], 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-4 text-sm text-mute">Belum ada isi paket pada produk paket.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
                    @forelse($barangRows as $baris)
                        <div class="px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink">{{ $baris['produk'] }}</p>
                                    <p class="text-xs text-mute">{{ $baris['item'] }}</p>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                                <div>
                                    <span class="block text-xs text-mute">Jumlah per Orang</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['jumlah'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Peserta Aktif</span>
                                    <span class="tabular-nums text-sm text-body">{{ $baris['peserta'] }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Total Kebutuhan</span>
                                    <span class="tabular-nums text-sm text-body">
                                        @if ($baris['total'] !== null)
                                            {{ $baris['total'] }}
                                        @else
                                            {{ $baris['peserta'] }} peserta × {{ $baris['jumlah'] }}
                                        @endif
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-xs text-mute">Estimasi Biaya</span>
                                    <span class="tabular-nums text-sm {{ $baris['estimasi'] !== null ? 'text-ink' : 'text-mute' }}">
                                        {{ $baris['estimasi'] !== null ? 'Rp '.number_format($baris['estimasi'], 0, ',', '.') : '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-mute">Belum ada isi paket pada produk paket.</div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>
