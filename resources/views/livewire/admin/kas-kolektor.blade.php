<div class="space-y-6" data-portal="admin">
    {{-- Header Halaman --}}
    <x-admin.page-header
        breadcrumb="Keuangan"
        title="Kas di Tangan Kolektor"
        subtitle="Pantau uang tunai yang belum disetorkan kolektor ke kantor."
    />

    {{-- KPI Cards (Clean & Borderless) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Card 1: Total kas --}}
        <div class="rounded-2xl bg-white dark:bg-zinc-900 p-6 shadow-sm shadow-zinc-950/5 transition-all">
            <span class="block text-xs font-medium text-zinc-400 dark:text-zinc-500">Total kas</span>
            <b class="mt-1 block text-2xl font-bold tracking-tight tabular-nums text-zinc-900 dark:text-white">
                Rp {{ number_format($totalKas, 0, ',', '.') }}
            </b>
            <span class="mt-1 block text-xs text-zinc-400 dark:text-zinc-500">Seluruh kolektor aktif</span>
        </div>

        {{-- Card 2: Kolektor melewati batas --}}
        <div class="rounded-2xl bg-white dark:bg-zinc-900 p-6 shadow-sm shadow-zinc-950/5 transition-all">
            <span class="block text-xs font-medium text-zinc-400 dark:text-zinc-500">Kolektor melewati batas</span>
            <b class="mt-1 block text-2xl font-bold tracking-tight tabular-nums {{ $jumlahLewatBatas > 0 ? 'text-danger' : 'text-zinc-900 dark:text-white' }}">
                {{ $jumlahLewatBatas }}
            </b>
            <span class="mt-1 block text-xs text-zinc-400 dark:text-zinc-500">
                {{ $jumlahLewatBatas > 0 ? 'Perlu tindakan' : 'Kondisi aman' }}
            </span>
        </div>

        {{-- Card 3: Batas kas / hari --}}
        <div class="rounded-2xl bg-white dark:bg-zinc-900 p-6 shadow-sm shadow-zinc-950/5 transition-all">
            <span class="block text-xs font-medium text-zinc-400 dark:text-zinc-500">Batas kas / hari</span>
            <b class="mt-1 block text-2xl font-bold tracking-tight tabular-nums text-zinc-900 dark:text-white">
                Rp {{ number_format((int) \App\Models\AdminSetting::get('batas_kas_kolektor', '0'), 0, ',', '.') }} / {{ (int) \App\Models\AdminSetting::get('batas_hari_kas', '0') }} hari
            </b>
            <span class="mt-1 block text-xs text-zinc-400 dark:text-zinc-500">Sesuai pengaturan</span>
        </div>
    </div>

    {{-- Card Tabel Utama (Clean & Borderless) --}}
    <div class="rounded-2xl bg-white dark:bg-zinc-900 p-6 shadow-sm shadow-zinc-950/5 space-y-4">
        {{-- Tabel Desktop --}}
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left text-sm">
                <thead>
                    <tr class="bg-zinc-50/80 dark:bg-zinc-800/40 text-zinc-400 dark:text-zinc-400">
                        <th class="px-4 py-3.5 text-[11px] font-semibold uppercase tracking-wider rounded-l-xl">Kolektor</th>
                        <th class="px-4 py-3.5 text-right text-[11px] font-semibold uppercase tracking-wider">Setoran Masuk</th>
                        <th class="px-4 py-3.5 text-right text-[11px] font-semibold uppercase tracking-wider">Penarikan Tunai</th>
                        <th class="px-4 py-3.5 text-right text-[11px] font-semibold uppercase tracking-wider">Kas di Tangan</th>
                        <th class="px-4 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wider">Transaksi</th>
                        <th class="px-4 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wider">Umur Tertua</th>
                        <th class="px-4 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wider">Pengajuan Pending</th>
                        <th class="px-4 py-3.5 text-right text-[11px] font-semibold uppercase tracking-wider">Akumulasi Selisih</th>
                        <th class="px-4 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wider rounded-r-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100/80 dark:divide-zinc-800/60">
                    @forelse($daftarKas as $baris)
                        <tr wire:key="kas-row-{{ $baris['kolektor_id'] }}"
                            class="transition-colors duration-150 hover:bg-zinc-50/70 dark:hover:bg-zinc-800/50 group">
                            {{-- Kolektor --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-bold text-xs">
                                        {{ Str::substr($baris['nama'], 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-zinc-900 dark:text-white flex items-center gap-1.5">
                                            <span>{{ $baris['nama'] }}</span>
                                            @if($baris['terkunci'])
                                                <span class="inline-flex items-center rounded-full bg-danger/10 px-2 py-0.5 text-[10px] font-bold text-danger">Terkunci</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Setoran Masuk --}}
                            <td class="px-4 py-4 text-right tabular-nums text-zinc-700 dark:text-zinc-300 font-medium">
                                Rp {{ number_format($baris['total_belum_disetor'], 0, ',', '.') }}
                            </td>

                            {{-- Penarikan Tunai --}}
                            <td class="px-4 py-4 text-right tabular-nums text-zinc-700 dark:text-zinc-300 font-medium">
                                Rp {{ number_format($baris['penarikan_tunai'], 0, ',', '.') }}
                            </td>

                            {{-- Kas di Tangan --}}
                            <td class="px-4 py-4 text-right tabular-nums font-bold text-zinc-900 dark:text-white">
                                Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}
                            </td>

                            {{-- Transaksi --}}
                            <td class="px-4 py-4 text-center tabular-nums text-zinc-600 dark:text-zinc-400">
                                {{ $baris['jumlah_transaksi'] }}
                            </td>

                            {{-- Umur Tertua --}}
                            <td class="px-4 py-4 text-center tabular-nums text-zinc-600 dark:text-zinc-400">
                                {{ $baris['umur_terlama_hari'] > 0 ? $baris['umur_terlama_hari'] . ' hari' : '-' }}
                            </td>

                            {{-- Pengajuan Pending --}}
                            <td class="px-4 py-4 text-center tabular-nums text-zinc-600 dark:text-zinc-400">
                                {{ $baris['pengajuan_pending'] }}
                            </td>

                            {{-- Akumulasi Selisih --}}
                            <td class="px-4 py-4 text-right tabular-nums">
                                @if($baris['selisih_kumulatif'] != 0)
                                    @if($baris['selisih_kumulatif'] > 0)
                                        <span class="font-semibold text-warning-deep">+Rp {{ number_format($baris['selisih_kumulatif'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="font-semibold text-danger">Rp -{{ number_format(abs($baris['selisih_kumulatif']), 0, ',', '.') }}</span>
                                    @endif
                                @else
                                    <span class="text-zinc-400 font-medium">Rp 0</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                @if($baris['terkunci'])
                                    <x-admin.status-chip status="Terkunci" />
                                @elseif($baris['lewat_batas'])
                                    <x-admin.status-chip status="Lewat Batas" />
                                @else
                                    <x-admin.status-chip status="Aman" />
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <button type="button" wire:click="bukaDetail({{ $baris['kolektor_id'] }})"
                                    class="inline-flex h-8 items-center justify-center rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 px-3 text-xs font-semibold text-zinc-800 dark:text-zinc-200 transition cursor-pointer active:scale-[0.97]">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center">
                                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-zinc-50 dark:bg-zinc-800 text-zinc-400 mb-2.5">
                                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <b class="block text-sm font-semibold text-zinc-800 dark:text-zinc-200">Belum ada kolektor aktif</b>
                                <span class="mt-0.5 block text-xs text-zinc-400 dark:text-zinc-500">Data kas di tangan akan muncul saat kolektor mulai menerima setoran.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Fallback Kartu Mobile --}}
        <div data-test="kartu-kas" class="divide-y divide-hairline md:hidden">
            @forelse($daftarKas as $baris)
                <div @class(['p-4 space-y-3', 'bg-warning-soft/30' => $baris['lewat_batas']])>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-bold text-zinc-900 dark:text-white flex items-center gap-1.5">
                                <span>{{ $baris['nama'] }}</span>
                                @if($baris['terkunci'])
                                    <span class="inline-flex items-center rounded-full bg-danger/10 px-2 py-0.5 text-[10px] font-bold text-danger">Terkunci</span>
                                @endif
                            </div>
                            <div class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">
                                {{ $baris['jumlah_transaksi'] }} transaksi &middot; Umur {{ $baris['umur_terlama_hari'] > 0 ? $baris['umur_terlama_hari'] . ' hari' : '-' }}
                            </div>
                        </div>
                        @if($baris['terkunci'])
                            <x-admin.status-chip status="Terkunci" />
                        @elseif($baris['lewat_batas'])
                            <x-admin.status-chip status="Lewat Batas" />
                        @else
                            <x-admin.status-chip status="Aman" />
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-2.5 text-xs pt-2">
                        <div>
                            <span class="text-zinc-400 block text-[11px]">Setoran Masuk:</span>
                            <div class="font-medium tabular-nums text-zinc-700 dark:text-zinc-300 mt-0.5">Rp {{ number_format($baris['total_belum_disetor'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-zinc-400 block text-[11px]">Penarikan Tunai:</span>
                            <div class="font-medium tabular-nums text-zinc-700 dark:text-zinc-300 mt-0.5">Rp {{ number_format($baris['penarikan_tunai'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-zinc-400 block text-[11px]">Kas di Tangan:</span>
                            <div class="font-bold tabular-nums text-sm text-zinc-900 dark:text-white mt-0.5">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-zinc-400 block text-[11px]">Akumulasi Selisih:</span>
                            <div class="font-bold tabular-nums mt-0.5">
                                @if($baris['selisih_kumulatif'] != 0)
                                    @if($baris['selisih_kumulatif'] > 0)
                                        <span class="text-warning-deep">+Rp {{ number_format($baris['selisih_kumulatif'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-danger">Rp -{{ number_format(abs($baris['selisih_kumulatif']), 0, ',', '.') }}</span>
                                    @endif
                                @else
                                    <span class="text-zinc-400">Rp 0</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="pt-2 flex justify-end">
                        <button type="button" wire:click="bukaDetail({{ $baris['kolektor_id'] }})"
                            class="inline-flex h-8 items-center justify-center rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 px-3 text-xs font-semibold text-zinc-800 dark:text-zinc-200 transition cursor-pointer">
                            Lihat Detail
                        </button>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center">
                    <b class="block text-sm font-semibold text-zinc-800 dark:text-zinc-200">Belum ada kolektor aktif</b>
                    <span class="mt-0.5 block text-xs text-zinc-400 dark:text-zinc-500">Data kas di tangan akan muncul saat kolektor mulai menerima setoran.</span>
                </div>
            @endforelse
        </div>

        {{-- Footer Tabel --}}
        <div class="flex items-center justify-between pt-4 border-t border-zinc-100/80 dark:border-zinc-800/60 text-xs text-zinc-400 dark:text-zinc-500">
            <span>Menampilkan {{ count($daftarKas) }} dari {{ count($daftarKas) }} data</span>
            <span>Halaman 1 dari 1</span>
        </div>
    </div>

    {{-- Drawer Slide-over: Detail Kas Kolektor (Clean & Borderless) --}}
    @if($detailKolektorId && $detailKas)
        <div class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true" aria-label="Detail Kas Kolektor">
            <div class="fixed inset-0 bg-black/45 transition-opacity cursor-pointer" wire:click="tutupDetail"></div>

            <div class="relative z-10 flex h-full w-full max-w-[480px] flex-col bg-white dark:bg-zinc-900 shadow-2xl shadow-zinc-950/20 transition-all">
                {{-- Header Drawer --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-zinc-100 dark:border-zinc-800/80">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h3 class="text-base font-bold text-zinc-900 dark:text-white">Detail Kas Kolektor</h3>
                            @if($detailKas['baris'] && $detailKas['baris']['terkunci'])
                                <x-admin.status-chip status="Terkunci" />
                            @elseif($detailKas['baris'] && $detailKas['baris']['lewat_batas'])
                                <x-admin.status-chip status="Lewat Batas" />
                            @else
                                <x-admin.status-chip status="Aman" />
                            @endif
                        </div>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">
                            {{ $detailKas['kolektor']->name ?? 'Kolektor' }} &middot; {{ $detailKas['kolektor']->no_hp ?? '-' }}
                        </p>
                    </div>
                    <button type="button" wire:click="tutupDetail"
                        class="flex size-8 shrink-0 items-center justify-center rounded-xl text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-700 dark:hover:text-zinc-200 cursor-pointer"
                        aria-label="Tutup panel detail">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Content Drawer --}}
                <div class="flex-1 overflow-y-auto p-6 space-y-5">
                    @if($detailKas['baris'])
                        {{-- Ringkasan Kas --}}
                        <div class="rounded-2xl bg-zinc-50/80 dark:bg-zinc-800/60 p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-zinc-400">Kas di Tangan (Net)</span>
                                <span class="text-base font-bold tabular-nums text-zinc-900 dark:text-white">
                                    Rp {{ number_format($detailKas['baris']['kas_di_tangan'], 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between border-t border-zinc-200/50 dark:border-zinc-700/50 pt-2.5">
                                <span class="text-xs text-zinc-400">Total Setoran Masuk</span>
                                <span class="text-xs font-semibold tabular-nums text-zinc-800 dark:text-zinc-200">
                                    Rp {{ number_format($detailKas['baris']['total_belum_disetor'], 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between border-t border-zinc-200/50 dark:border-zinc-700/50 pt-2.5">
                                <span class="text-xs text-zinc-400">Total Penarikan Tunai</span>
                                <span class="text-xs font-semibold tabular-nums text-zinc-800 dark:text-zinc-200">
                                    Rp {{ number_format($detailKas['baris']['penarikan_tunai'], 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between border-t border-zinc-200/50 dark:border-zinc-700/50 pt-2.5">
                                <span class="text-xs text-zinc-400">Transaksi &amp; Umur Tertua</span>
                                <span class="text-xs text-zinc-800 dark:text-zinc-200">
                                    {{ $detailKas['baris']['jumlah_transaksi'] }} transaksi &middot; {{ $detailKas['baris']['umur_terlama_hari'] > 0 ? $detailKas['baris']['umur_terlama_hari'] . ' hari' : '-' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between border-t border-zinc-200/50 dark:border-zinc-700/50 pt-2.5">
                                <span class="text-xs text-zinc-400">Akumulasi Selisih Rekon</span>
                                <span class="text-xs font-bold tabular-nums">
                                    @if($detailKas['baris']['selisih_kumulatif'] != 0)
                                        @if($detailKas['baris']['selisih_kumulatif'] > 0)
                                            <span class="text-warning-deep">+Rp {{ number_format($detailKas['baris']['selisih_kumulatif'], 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-danger">Rp -{{ number_format(abs($detailKas['baris']['selisih_kumulatif']), 0, ',', '.') }}</span>
                                        @endif
                                    @else
                                        <span class="text-zinc-400">Rp 0</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endif

                    {{-- Daftar Setoran Masuk Belum Disetor --}}
                    @if($detailKas['setoran_list']->count() > 0)
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400">Setoran Belum Disetor ({{ $detailKas['setoran_list']->count() }})</h4>
                            </div>
                            <div class="rounded-2xl bg-zinc-50/80 dark:bg-zinc-800/60 divide-y divide-zinc-200/50 dark:divide-zinc-700/50 overflow-hidden">
                                @foreach($detailKas['setoran_list'] as $trx)
                                    <div class="p-3.5 text-xs flex items-center justify-between hover:bg-zinc-100/60 dark:hover:bg-zinc-800/80 transition">
                                        <div>
                                            <p class="font-bold text-zinc-900 dark:text-white">{{ $trx->nasabah->name ?? 'Nasabah' }}</p>
                                            <p class="text-[11px] text-zinc-400 mt-0.5">{{ $trx->produk->nama ?? 'Tabungan' }} &middot; {{ $trx->tanggal_transaksi ? \Illuminate\Support\Carbon::parse($trx->tanggal_transaksi)->translatedFormat('d M Y') : '-' }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold tabular-nums text-zinc-900 dark:text-white">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</p>
                                            <span class="text-[10px] uppercase font-semibold text-zinc-400">{{ $trx->sumber_input }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Quick Action --}}
                    <div class="pt-2">
                        <a href="/admin/rekonsiliasi" wire:navigate
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-accent px-4 py-3 text-xs font-semibold text-accent-foreground shadow-sm transition hover:opacity-90">
                            Buka Rekonsiliasi Kas Kolektor Ini
                        </a>
                    </div>
                </div>

                {{-- Footer Drawer --}}
                <div class="border-t border-zinc-100 dark:border-zinc-800/80 p-5 flex justify-end">
                    <button type="button" wire:click="tutupDetail"
                        class="h-10 w-full rounded-xl bg-zinc-100 dark:bg-zinc-800 text-xs font-semibold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
