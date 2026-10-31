@php
    $hasTrenData = $trenSetoran->isNotEmpty();
    $maxSaldo = $hasTrenData ? max($trenSetoran->pluck('nominal')->max(), 1) : 1;
    $chartWidth = 560;
    $chartHeight = 160;
    $padding = 10;
    $graphWidth = $chartWidth - ($padding * 2);
    $graphHeight = $chartHeight - ($padding * 2);

    $points = $hasTrenData ? $trenSetoran->map(function ($item, $i) use ($maxSaldo, $graphWidth, $graphHeight, $padding, $trenSetoran) {
        $x = $padding + ($i / max($trenSetoran->count() - 1, 1)) * $graphWidth;
        $y = $padding + $graphHeight - ($item['nominal'] / $maxSaldo) * $graphHeight;
        return ['x' => $x, 'y' => $y];
    }) : collect();

    $pathD = $points->map(function ($p, $i) {
        return ($i === 0 ? 'M' : 'L') . round($p['x'], 1) . ',' . round($p['y'], 1);
    })->implode(' ');

    $areaD = $pathD . ' L' . round($points->last()['x'] ?? 0, 1) . ',' . $graphHeight . ' L' . round($points->first()['x'] ?? 0, 1) . ',' . $graphHeight . ' Z';

    $totalBebas = $komposisiProduk->where('tipe', 'bebas')->sum('total') ?? 0;
    $totalPaket = $komposisiProduk->where('tipe', 'paket')->sum('total') ?? 0;
    $totalAll = $totalBebas + $totalPaket;
    $persenBebas = $totalAll > 0 ? round(($totalBebas / $totalAll) * 100) : 0;
    $persenPaket = $totalAll > 0 ? round(($totalPaket / $totalAll) * 100) : 0;

    $donutRadius = 40;
    $donutCircumference = 2 * 3.14159 * $donutRadius;

    // DESIGN.md §10 "padanan teks": ringkasan awal/akhir/tertinggi/terendah
    // untuk pembaca layar (E-a). Segmen Paket memakai teal §612–614 (E-b).
    $ariaTrenSetoran = '';
    if ($hasTrenData) {
        $nominalTren = $trenSetoran->pluck('nominal');
        $idxTertinggi = (int) $nominalTren->search($nominalTren->max());
        $idxTerendah = (int) $nominalTren->search($nominalTren->min());
        $ariaTrenSetoran = 'Grafik tren setoran 30 hari terakhir. '
            .'Awal '.number_format((float) $nominalTren->first(), 0, ',', '.').' rupiah pada '.$trenSetoran->first()['tanggal'].', '
            .'akhir '.number_format((float) $nominalTren->last(), 0, ',', '.').' rupiah pada '.$trenSetoran->last()['tanggal'].'. '
            .'Tertinggi '.number_format((float) $nominalTren->max(), 0, ',', '.').' rupiah pada '.$trenSetoran[$idxTertinggi]['tanggal'].', '
            .'terendah '.number_format((float) $nominalTren->min(), 0, ',', '.').' rupiah pada '.$trenSetoran[$idxTerendah]['tanggal'].'.';
    }

    $ariaKomposisi = $totalAll > 0
        ? 'Komposisi saldo seluruh nasabah. Bebas '.number_format((float) $totalBebas, 0, ',', '.').' rupiah ('.$persenBebas.' persen), '
            .'Paket '.number_format((float) $totalPaket, 0, ',', '.').' rupiah ('.$persenPaket.' persen). '
            .'Total '.number_format((float) $totalAll, 0, ',', '.').' rupiah.'
        : '';
@endphp

<div class="space-y-6">
    {{-- Header Halaman --}}
    <x-admin.page-header title="Dashboard" subtitle="Ringkasan operasional Tabungan Digital hari ini.">
        <x-slot:actions>
            <a href="{{ route('admin.registrasi.index') }}" wire:navigate class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Nasabah
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @php
        $totalAntrean = \App\Support\AntreanPersetujuan::total();
    @endphp

    {{-- Banner Antrean --}}
    @if($totalAntrean > 0)
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-[12px] border border-warning-deep/20 bg-warning-soft px-4 py-3 text-sm text-warning-deep shadow-xs">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-warning-deep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span><strong>{{ $totalAntrean }} antrean</strong> menunggu tindakan Anda.</span>
            </div>
            <a href="{{ route('admin.persetujuan.index') }}" wire:navigate class="inline-flex items-center justify-center rounded-[10px] bg-primary px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-primary-hover active:scale-95 shadow-xs">
                Buka Hub Persetujuan
            </a>
        </div>
    @endif

    {{-- 9 KPI Cards --}}
    <x-admin.kpi-grid>
        <x-admin.kpi-card label="Total Nasabah" :value="number_format($totalNasabah, 0, ',', '.')" />
        <x-admin.kpi-card label="Nasabah Offline" :value="number_format($nasabahOffline, 0, ',', '.')" sub="Tanpa aplikasi" />
        <x-admin.kpi-card label="Kolektor Aktif" :value="number_format($totalKolektor, 0, ',', '.')" />
        <x-admin.kpi-card label="Total Saldo Tabungan" :value="'Rp ' . number_format($totalSaldo, 0, ',', '.')" />
        <x-admin.kpi-card label="Setoran Hari Ini" :value="'Rp ' . number_format($setoranHariIni, 0, ',', '.')" />
        <a href="{{ route('admin.komisi.index') }}" wire:navigate class="block transition hover:opacity-90">
            <x-admin.kpi-card label="Total Komisi" :value="'Rp ' . number_format($totalKomisi, 0, ',', '.')" />
        </a>
        <a href="{{ route('admin.bermasalah.index') }}" wire:navigate class="block transition hover:opacity-90">
            <x-admin.kpi-card label="Perlu review" :value="number_format($perluReview, 0, ',', '.')" sub="Kepesertaan paket" variant="warn" />
        </a>
        <a href="{{ route('admin.kas-kolektor.index') }}" wire:navigate class="block transition hover:opacity-90">
            <x-admin.kpi-card label="Kas di Tangan Kolektor" :value="'Rp ' . number_format($totalKasKolektor, 0, ',', '.')" />
        </a>
        <a href="{{ route('admin.kas-kolektor.index') }}" wire:navigate class="block transition hover:opacity-90">
            <x-admin.kpi-card label="Kolektor Lewat Batas" :value="number_format($kolektorLewatBatas, 0, ',', '.')" variant="error" />
        </a>
    </x-admin.kpi-grid>

    {{-- Baris 1: Tren Setoran & Komposisi Produk --}}
    <div class="grid grid-cols-1 lg:grid-cols-[1.6fr_1fr] gap-3.5">
        {{-- Tren Setoran --}}
        <div class="rounded-[14px] border border-hairline bg-surface p-4 shadow-xs">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-semibold text-ink">Tren Setoran</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-surface-2 text-mute border border-hairline">30 hari terakhir</span>
            </div>
            @if($hasTrenData)
                <div class="bars flex items-end gap-1 sm:gap-1.5 h-[150px] pt-2.5 overflow-hidden" role="img" aria-label="{{ $ariaTrenSetoran }}">
                    @foreach($trenSetoran as $i => $item)
                        @php
                            $heightPct = $maxSaldo > 0 ? max(2, round(($item['nominal'] / $maxSaldo) * 100)) : 2;
                            $isToday = $i === ($trenSetoran->count() - 1);
                        @endphp
                        <div class="flex-1 rounded-t-[4px] transition-all duration-300 {{ $isToday ? 'bg-primary' : 'bg-primary/30' }}" 
                             style="height: {{ $heightPct }}%; min-height: 4px;" 
                             title="{{ $item['tanggal'] }}: Rp {{ number_format($item['nominal'], 0, ',', '.') }}"></div>
                    @endforeach
                </div>
            @else
                <x-admin.empty-state
                    title="Belum ada data setoran"
                    description="Grafik akan muncul setelah ada transaksi setoran pertama." />
            @endif
        </div>

        {{-- Komposisi Produk --}}
        <div class="rounded-[14px] border border-hairline bg-surface p-4 shadow-xs">
            <h2 class="mb-3 text-base font-semibold text-ink">Komposisi Produk</h2>
            @if($totalAll > 0)
                <div class="flex flex-col items-center justify-center py-2">
                    <svg viewBox="0 0 100 100" class="h-32 w-32 -rotate-90" role="img" aria-label="{{ $ariaKomposisi }}">
                        <circle cx="50" cy="50" r="{{ $donutRadius }}" fill="none" stroke="currentColor" stroke-opacity="0.15" stroke-width="12" />
                        <circle cx="50" cy="50" r="{{ $donutRadius }}" fill="none" stroke="currentColor" stroke-width="12"
                                stroke-dasharray="{{ ($persenBebas / 100) * $donutCircumference }} {{ $donutCircumference }}"
                                stroke-linecap="round" />
                        <circle cx="50" cy="50" r="{{ $donutRadius }}" fill="none" stroke="currentColor" class="text-tile-teal-fg" stroke-width="12"
                                stroke-dasharray="{{ ($persenPaket / 100) * $donutCircumference }} {{ $donutCircumference }}"
                                stroke-dashoffset="-{{ ($persenBebas / 100) * $donutCircumference }}"
                                stroke-linecap="round" />
                    </svg>
                    <div class="mt-4 flex flex-col gap-2 w-full max-w-[200px]">
                        <div class="flex items-center gap-2 text-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-accent"></span>
                            <span class="text-mute">Bebas</span>
                            <span class="ml-auto font-semibold tabular-nums text-ink">{{ $persenBebas }}%</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-tile-teal-fg"></span>
                            <span class="text-mute">Paket</span>
                            <span class="ml-auto font-semibold tabular-nums text-ink">{{ $persenPaket }}%</span>
                        </div>
                    </div>
                </div>
            @else
                <x-admin.empty-state
                    title="Belum ada produk aktif"
                    description="Komposisi akan muncul setelah nasabah memiliki tabungan." />
            @endif
        </div>
    </div>

    {{-- Baris 2: Transaksi Terbaru & Kolektor Teratas --}}
    <div class="grid grid-cols-1 lg:grid-cols-[1.6fr_1fr] gap-3.5">
        {{-- Tabel Transaksi Terbaru --}}
        <div class="rounded-[14px] border border-hairline bg-surface shadow-xs overflow-hidden">
            <div class="p-4 border-b border-hairline">
                <h2 class="text-base font-semibold text-ink">Transaksi Terbaru</h2>
            </div>
            @if($transaksiTerbaru->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-surface-2 text-xs uppercase tracking-[0.04em] text-mute font-medium">
                                <th class="px-4 py-2.5">Nasabah</th>
                                <th class="px-4 py-2.5">Produk</th>
                                <th class="px-4 py-2.5 text-right">Nominal</th>
                                <th class="px-4 py-2.5">Tanggal</th>
                                <th class="px-4 py-2.5 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hairline">
                            @foreach($transaksiTerbaru as $trx)
                                <tr class="hover:bg-surface-2 transition text-ink">
                                    <td class="whitespace-nowrap px-4 py-3 font-medium">{{ $trx->nasabah_name }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-mute">{{ $trx->produk_name ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums text-sm font-medium">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-mute text-xs">{{ \Carbon\Carbon::parse($trx->tanggal_transaksi)->format('d M Y') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <x-admin.status-chip status="Berhasil" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-admin.empty-state
                    title="Belum ada transaksi"
                    description="Data transaksi akan muncul setelah ada setoran dari nasabah." />
            @endif
        </div>

        {{-- Kolektor Teratas --}}
        <div class="rounded-[14px] border border-hairline bg-surface shadow-xs overflow-hidden">
            <div class="p-4 border-b border-hairline">
                <h2 class="text-base font-semibold text-ink">Kolektor Teratas</h2>
                <p class="text-xs text-mute mt-0.5">30 hari terakhir</p>
            </div>
            @if($kolektorTeratas->isNotEmpty())
                <div class="divide-y divide-hairline">
                    @foreach($kolektorTeratas as $i => $k)
                        <div class="flex items-center gap-3 px-4 py-3 hover:bg-surface-2 transition">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold {{ $i === 0 ? 'bg-primary-soft text-on-primary-soft' : 'bg-surface-2 text-mute border border-hairline' }}">
                                {{ $i + 1 }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-ink">{{ $k->name }}</p>
                                <p class="text-xs text-mute">{{ $k->jumlah_setoran }} transaksi</p>
                            </div>
                            <span class="whitespace-nowrap tabular-nums text-sm font-semibold text-ink">
                                Rp {{ number_format($k->total_nominal, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <x-admin.empty-state
                    title="Belum ada data kolektor"
                    description="Peringkat kolektor akan muncul setelah ada aktivitas setoran." />
            @endif
        </div>
    </div>
</div>
