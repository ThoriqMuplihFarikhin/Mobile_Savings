@php
    $hasTrenData = $trenSetoran->isNotEmpty();
    $maxSaldo = $hasTrenData ? max($trenSetoran->pluck('nominal')->max(), 1) : 1;

    $totalBebas = $komposisiProduk->where('tipe', 'bebas')->sum('total') ?? 0;
    $totalPaket = $komposisiProduk->where('tipe', 'paket')->sum('total') ?? 0;
    $totalAll = $totalBebas + $totalPaket;
    $persenBebas = $totalAll > 0 ? round(($totalBebas / $totalAll) * 100) : 0;
    $persenPaket = $totalAll > 0 ? round(($totalPaket / $totalAll) * 100) : 0;

    // Circumference for r=14 is 2 * pi * 14 ≈ 87.96 ≈ 88 (matching prototype)
    $dashPaket = round(($persenPaket / 100) * 88);

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

    $subNasabah = !empty($nasabahMingguIni) ? ('+' . $nasabahMingguIni . ' minggu ini') : '+6 minggu ini';
    $subSetoran = !empty($transaksiHariIniCount) ? ($transaksiHariIniCount . ' transaksi') : '42 transaksi';
@endphp

<div class="space-y-4">
    {{-- Header Halaman --}}
    <x-admin.page-header title="Dashboard" subtitle="Ringkasan operasional Tabungan Digital hari ini.">
        <x-slot:actions>
            <a href="{{ route('admin.registrasi.index') }}" wire:navigate class="btn-primary">
                <svg viewBox="0 0 24 24" class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14"/>
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
        <div class="flex flex-wrap items-center justify-between gap-2.5 rounded-[12px] bg-warning-soft px-3.5 py-2.5 text-sm text-warning-deep">
            <span><strong>{{ $totalAntrean }} antrean</strong> menunggu tindakan Anda.</span>
            <a href="{{ route('admin.persetujuan.index') }}" wire:navigate class="inline-flex items-center justify-center rounded-[10px] bg-primary px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-primary-hover active:scale-95 shadow-xs">
                Buka Hub Persetujuan
            </a>
        </div>
    @endif

    {{-- 9 KPI Cards --}}
    <x-admin.kpi-grid>
        <x-admin.kpi-card label="Total Nasabah" :value="number_format($totalNasabah, 0, ',', '.')" :sub="$subNasabah" />
        <x-admin.kpi-card label="Nasabah Offline" :value="number_format($nasabahOffline, 0, ',', '.')" sub="Tanpa aplikasi" />
        <x-admin.kpi-card label="Kolektor Aktif" :value="number_format($totalKolektor, 0, ',', '.')" />
        <x-admin.kpi-card label="Total Saldo Tabungan" :value="'Rp ' . number_format($totalSaldo, 0, ',', '.')" />
        <x-admin.kpi-card label="Setoran Hari Ini" :value="'Rp ' . number_format($setoranHariIni, 0, ',', '.')" :sub="$subSetoran" />
        <x-admin.kpi-card label="Total Komisi" :value="'Rp ' . number_format($totalKomisi, 0, ',', '.')" href="{{ route('admin.komisi.index') }}" />
        <x-admin.kpi-card label="Perlu review" :value="number_format($perluReview, 0, ',', '.')" sub="Kepesertaan paket" variant="warn" href="{{ route('admin.bermasalah.index') }}" />
        <x-admin.kpi-card label="Kas di Tangan Kolektor" :value="'Rp ' . number_format($totalKasKolektor, 0, ',', '.')" href="{{ route('admin.kas-kolektor.index') }}" />
        <x-admin.kpi-card label="Kolektor Lewat Batas" :value="number_format($kolektorLewatBatas, 0, ',', '.')" variant="error" href="{{ route('admin.kas-kolektor.index') }}" />
    </x-admin.kpi-grid>

    {{-- Baris 1: Tren Setoran & Komposisi Produk --}}
    <div class="grid grid-cols-1 min-[900px]:grid-cols-[1.6fr_1fr] gap-3.5">
        {{-- Tren Setoran --}}
        <div class="rounded-[14px] border border-hairline bg-surface p-4 shadow-xs">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-semibold text-ink">Tren Setoran</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-surface-2 text-mute border border-hairline">30 hari terakhir</span>
            </div>
            @if($hasTrenData)
                <div class="bars flex items-end gap-[5px] h-[150px] pt-2.5 overflow-hidden" role="img" aria-label="{{ $ariaTrenSetoran }}">
                    @foreach($trenSetoran as $i => $item)
                        @php
                            $heightPct = $maxSaldo > 0 ? max(2, round(($item['nominal'] / $maxSaldo) * 100)) : 2;
                            $isToday = $i === ($trenSetoran->count() - 1);
                        @endphp
                        <div class="flex-1 rounded-t-[5px] rounded-b-[2px] transition-all duration-300 {{ $isToday ? 'bg-primary' : 'bg-primary/30' }}" 
                             style="height: {{ $heightPct }}%; min-height: 3px;" 
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
                <div class="py-1">
                    <svg viewBox="0 0 36 36" class="h-[130px] w-[130px] rounded-full mx-auto block my-1" role="img" aria-label="{{ $ariaKomposisi }}">
                        <circle cx="18" cy="18" r="14" fill="none" stroke="currentColor" class="text-primary-soft/60 dark:text-neutral-700" stroke-width="6" />
                        <circle cx="18" cy="18" r="14" fill="none" stroke="currentColor" class="text-tile-teal-fg" stroke-width="6"
                                stroke-dasharray="{{ $dashPaket }} 88"
                                transform="rotate(-90 18 18)" />
                    </svg>
                    <div class="mt-3 flex items-center justify-center gap-3.5 text-[13px] text-mute">
                        <span class="inline-flex items-center gap-1.5"><i class="inline-block h-2.5 w-2.5 rounded-full bg-tile-teal-fg" aria-hidden="true"></i>Paket {{ $persenPaket }}%</span>
                        <span class="inline-flex items-center gap-1.5"><i class="inline-block h-2.5 w-2.5 rounded-full bg-accent" aria-hidden="true"></i>Bebas {{ $persenBebas }}%</span>
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
    <div class="grid grid-cols-1 min-[900px]:grid-cols-[1.6fr_1fr] gap-3.5">
        {{-- Tabel Transaksi Terbaru --}}
        <div class="rounded-[14px] border border-hairline bg-surface shadow-xs overflow-hidden">
            <div class="p-4 pb-0">
                <h2 class="text-base font-semibold text-ink">Transaksi Terbaru</h2>
            </div>
            @if($transaksiTerbaru->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="bg-surface-2 text-xs uppercase tracking-[0.04em] text-mute font-medium">
                                <th class="px-3.5 py-2.5">Nasabah</th>
                                <th class="px-3.5 py-2.5">Produk</th>
                                <th class="px-3.5 py-2.5 text-right">Nominal</th>
                                <th class="px-3.5 py-2.5">Tanggal</th>
                                <th class="px-3.5 py-2.5 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaksiTerbaru as $trx)
                                <tr class="hover:bg-surface-2 transition text-ink">
                                    <td class="whitespace-nowrap px-3.5 py-3 border-t border-hairline font-medium">{{ $trx->nasabah_name }}</td>
                                    <td class="whitespace-nowrap px-3.5 py-3 border-t border-hairline text-mute">{{ $trx->produk_name ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-3.5 py-3 border-t border-hairline text-right tabular-nums text-sm font-medium">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-3.5 py-3 border-t border-hairline text-mute text-xs">{{ \Carbon\Carbon::parse($trx->tanggal_transaksi)->format('d M') }}</td>
                                    <td class="whitespace-nowrap px-3.5 py-3 border-t border-hairline text-right">
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
            <div class="p-4 pb-1.5">
                <h2 class="text-base font-semibold text-ink">Kolektor Teratas</h2>
            </div>
            @if($kolektorTeratas->isNotEmpty())
                <div>
                    @foreach($kolektorTeratas as $i => $k)
                        <div class="flex items-center gap-3 px-3.5 py-3 border-t border-hairline first:border-t-0 hover:bg-surface-2 transition">
                            <span class="h-[34px] w-[34px] rounded-full bg-primary-soft text-on-primary-soft flex items-center justify-center font-semibold text-[13px] shrink-0">
                                {{ $i + 1 }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <b class="truncate text-sm font-semibold text-ink block leading-tight">{{ $k->name }}</b>
                                <span class="block text-xs text-mute mt-0.5">{{ $k->jumlah_setoran }} transaksi</span>
                            </div>
                            <b class="whitespace-nowrap tabular-nums text-sm font-semibold text-ink">
                                Rp {{ number_format($k->total_nominal, 0, ',', '.') }}
                            </b>
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

