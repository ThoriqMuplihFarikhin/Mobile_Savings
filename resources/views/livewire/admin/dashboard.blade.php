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
@endphp

<div class="mx-auto max-w-7xl space-y-6">
    <div class="flex items-center justify-between mb-4">
        <livewire:admin.search />
        <a href="{{ route('admin.registrasi.index') }}" class="btn-primary">Tambah Nasabah</a>
    </div>
    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-3.5">
        <x-kpi-card label="Total Nasabah" :value="number_format($totalNasabah, 0, ',', '.')">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
        </x-kpi-card>
        <x-kpi-card label="Nasabah Offline" :value="number_format($nasabahOffline, 0, ',', '.')">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
        </x-kpi-card>
        <x-kpi-card label="Kolektor Aktif" :value="number_format($totalKolektor, 0, ',', '.')">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
        </x-kpi-card>
        <x-kpi-card label="Total Saldo Tabungan" :value="'Rp ' . number_format($totalSaldo, 0, ',', '.')">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
        </x-kpi-card>
        <x-kpi-card label="Setoran Hari Ini" :value="'Rp ' . number_format($setoranHariIni, 0, ',', '.')">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg>
        </x-kpi-card>
        <a href="{{ route('admin.komisi.index') }}" wire:navigate class="block transition hover:opacity-80">
            <x-kpi-card label="Total Komisi" :value="'Rp ' . number_format($totalKomisi, 0, ',', '.')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v-2m0-8a9 9 0 100 18 9 9 0 000-18z" /></svg>
            </x-kpi-card>
        </a>
        <a href="{{ route('admin.bermasalah.index') }}" wire:navigate class="block transition hover:opacity-80">
            <x-kpi-card label="Perlu review" :value="number_format($perluReview, 0, ',', '.')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
            </x-kpi-card>
        </a>
        <a href="{{ route('admin.kas-kolektor.index') }}" wire:navigate class="block transition hover:opacity-80">
            <x-kpi-card label="Kas di Tangan Kolektor" :value="'Rp ' . number_format($totalKasKolektor, 0, ',', '.')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7V4a1 1 0 00-1-1H5a2 2 0 000 4h15a1 1 0 011 1v4h-3a2 2 0 000 4h3a1 1 0 001-1v-2a1 1 0 00-1-1M3 5v14a2 2 0 002 2h15a1 1 0 001-1v-4" /></svg>
            </x-kpi-card>
        </a>
        <a href="{{ route('admin.kas-kolektor.index') }}" wire:navigate class="block transition hover:opacity-80">
            <x-kpi-card label="Kolektor Lewat Batas" :value="number_format($kolektorLewatBatas, 0, ',', '.')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </x-kpi-card>
        </a>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Tren Setoran --}}
        <div class="lg:col-span-2 card">
            <div class="mb-4 flex items-center justify-between">
                <div class="mx-auto max-w-7xl space-y-6">
                    <h3 class="font-serif text-base font-semibold text-text dark:text-white">Tren Setoran</h3>
                    <p class="text-xs text-text-muted dark:text-slate-400">30 hari terakhir</p>
                </div>
            </div>
            @if($hasTrenData)
                <div class="overflow-hidden">
                    <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" class="h-40 w-full">
                        <defs>
                            <linearGradient id="gradientArea" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" style="stop-color:#0A1626;stop-opacity:0.15" />
                                <stop offset="100%" style="stop-color:#0A1626;stop-opacity:0.01" />
                            </linearGradient>
                        </defs>
                        @foreach([0.25, 0.5, 0.75, 1] as $ratio)
                            <line x1="{{ $padding }}" y1="{{ $padding + $graphHeight * (1 - $ratio) }}" x2="{{ $chartWidth - $padding }}" y2="{{ $padding + $graphHeight * (1 - $ratio) }}" stroke="#e5e5e5" stroke-width="0.5" />
                        @endforeach
                        <path d="{{ $areaD }}" fill="url(#gradientArea)" />
                        <path d="{{ $pathD }}" fill="none" stroke="#0A1626" stroke-width="2" stroke-linejoin="round" />
                        @if($points->isNotEmpty())
                            <circle cx="{{ $points->last()['x'] }}" cy="{{ $points->last()['y'] }}" r="3" fill="#0A1626" />
                        @endif
                    </svg>
                </div>
            @else
                <x-admin.empty-state
                    icon='<svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>'
                    title="Belum ada data setoran"
                    description="Grafik akan muncul setelah ada transaksi setoran pertama." />
            @endif
        </div>

        {{-- Komposisi Produk --}}
        <div class="card">
            <h3 class="mb-4 font-serif text-base font-semibold text-text dark:text-white">Komposisi Produk</h3>
            @if($totalAll > 0)
                <div class="flex flex-col items-center">
                    <svg viewBox="0 0 100 100" class="h-32 w-32 -rotate-90">
                        <circle cx="50" cy="50" r="{{ $donutRadius }}" fill="none" stroke="#e5e5e5" stroke-width="12" />
                        <circle cx="50" cy="50" r="{{ $donutRadius }}" fill="none" stroke="#0A1626" stroke-width="12"
                                stroke-dasharray="{{ ($persenBebas / 100) * $donutCircumference }} {{ $donutCircumference }}"
                                stroke-linecap="round" />
                        <circle cx="50" cy="50" r="{{ $donutRadius }}" fill="none" stroke="#B8862E" stroke-width="12"
                                stroke-dasharray="{{ ($persenPaket / 100) * $donutCircumference }} {{ $donutCircumference }}"
                                stroke-dashoffset="-{{ ($persenBebas / 100) * $donutCircumference }}"
                                stroke-linecap="round" />
                    </svg>
                    <div class="mt-4 flex flex-col gap-2">
                        <div class="flex items-center gap-2 text-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-navy-950"></span>
                            <span class="text-text-muted dark:text-slate-400">Bebas</span>
                            <span class="ml-auto font-semibold text-text dark:text-white">{{ $persenBebas }}%</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-gold"></span>
                            <span class="text-text-muted dark:text-slate-400">Paket</span>
                            <span class="ml-auto font-semibold text-text dark:text-white">{{ $persenPaket }}%</span>
                        </div>
                    </div>
                </div>
            @else
                <x-admin.empty-state
                    icon='<svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>'
                    title="Belum ada produk aktif"
                    description="Komposisi akan muncul setelah nasabah memiliki tabungan." />
            @endif
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Tabel Transaksi Terbaru --}}
        <div class="lg:col-span-2 card">
            <div class="border-b border-border px-5 py-4">
                <h3 class="font-serif text-base font-semibold text-text dark:text-white">Transaksi Terbaru</h3>
            </div>
            @if($transaksiTerbaru->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-border text-xs text-text-muted dark:text-slate-400">
                                <th class="px-5 py-3 font-medium">Nasabah</th>
                                <th class="px-5 py-3 font-medium">Produk</th>
                                <th class="px-5 py-3 font-medium text-right">Nominal</th>
                                <th class="px-5 py-3 font-medium">Tanggal</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($transaksiTerbaru as $trx)
                                <tr class="text-text dark:text-white">
                                    <td class="whitespace-nowrap px-5 py-3 font-medium">{{ $trx->nasabah_name }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-text-muted dark:text-slate-400">{{ $trx->produk_name ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right font-mono text-sm">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-text-muted dark:text-slate-400">{{ \Carbon\Carbon::parse($trx->tanggal_transaksi)->format('d M Y') }}</td>
                                    <td class="whitespace-nowrap px-5 py-3">
                                        <span class="inline-flex items-center rounded-full bg-success-soft px-2 py-0.5 text-xs font-medium text-success">Berhasil</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-admin.empty-state
                    icon='<svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>'
                    title="Belum ada transaksi"
                    description="Data transaksi akan muncul setelah ada setoran dari nasabah." />
            @endif
        </div>

        {{-- Kolektor Teratas --}}
        <div class="card">
            <div class="border-b border-border px-5 py-4">
                <h3 class="font-serif text-base font-semibold text-text dark:text-white">Kolektor Teratas</h3>
                <p class="text-xs text-text-muted dark:text-slate-400">30 hari terakhir</p>
            </div>
            @if($kolektorTeratas->isNotEmpty())
                <div class="divide-y divide-border">
                    @foreach($kolektorTeratas as $i => $k)
                        <div class="flex items-center gap-3 px-5 py-3.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $i === 0 ? 'bg-gold-soft text-gold font-semibold' : 'bg-zinc-100 dark:bg-slate-800 text-zinc-500 dark:text-slate-400' }} text-xs">
                                {{ $i + 1 }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-text dark:text-white">{{ $k->name }}</p>
                                <p class="text-xs text-text-muted dark:text-slate-400">{{ $k->jumlah_setoran }} setoran</p>
                            </div>
                            <span class="whitespace-nowrap font-mono text-sm font-semibold text-text dark:text-white">
                                Rp {{ number_format($k->total_nominal, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <x-admin.empty-state
                    icon='<svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>'
                    title="Belum ada data kolektor"
                    description="Peringkat kolektor akan muncul setelah ada aktivitas setoran." />
            @endif
        </div>
    </div>
</div>
