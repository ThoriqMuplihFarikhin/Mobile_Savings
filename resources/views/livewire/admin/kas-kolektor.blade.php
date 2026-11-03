<div class="space-y-5" data-portal="admin">
    {{-- Header Halaman --}}
    <x-admin.page-header
        breadcrumb="Keuangan"
        title="Kas di Tangan Kolektor"
        subtitle="Pantau uang tunai yang belum disetorkan kolektor ke kantor."
    />

    {{-- KPI Cards sesuai prototipe HTML PG.kas --}}
    <x-admin.kpi-grid cols="3">
        <x-admin.kpi-card
            label="Total kas"
            :value="'Rp ' . number_format($totalKas, 0, ',', '.')"
            sub="Seluruh kolektor aktif"
        />
        <x-admin.kpi-card
            label="Kolektor melewati batas"
            :value="$jumlahLewatBatas"
            :variant="$jumlahLewatBatas > 0 ? 'error' : 'default'"
            sub="Perlu tindakan"
        />
        <x-admin.kpi-card
            label="Batas kas / hari"
            :value="'Rp ' . number_format((int) \App\Models\AdminSetting::get('batas_kas_kolektor', '0'), 0, ',', '.') . ' / ' . (int) \App\Models\AdminSetting::get('batas_hari_kas', '0') . ' hari'"
            sub="Sesuai pengaturan"
        />
    </x-admin.kpi-grid>

    {{-- Card Container Tabel & Footer --}}
    <div class="rounded-[14px] border border-hairline bg-surface shadow-xs overflow-hidden">
        {{-- Tabel Desktop --}}
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-surface-2 text-xs uppercase tracking-[0.04em] text-mute font-medium border-b border-hairline">
                        <th class="px-4 py-2.5">Kolektor</th>
                        <th class="px-4 py-2.5 text-right">Setoran Masuk</th>
                        <th class="px-4 py-2.5 text-right">Penarikan Tunai</th>
                        <th class="px-4 py-2.5 text-right">Kas di Tangan</th>
                        <th class="px-4 py-2.5 text-center">Transaksi</th>
                        <th class="px-4 py-2.5 text-center">Umur Tertua</th>
                        <th class="px-4 py-2.5 text-center">Pengajuan Pending</th>
                        <th class="px-4 py-2.5 text-right">Akumulasi Selisih</th>
                        <th class="px-4 py-2.5 text-center">Status</th>
                        <th class="px-4 py-2.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($daftarKas as $baris)
                        <tr wire:key="kas-row-{{ $baris['kolektor_id'] }}" class="hover:bg-surface-2 transition text-ink">
                            {{-- Kolektor --}}
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-ink">
                                <div class="flex items-center gap-1.5">
                                    <span>{{ $baris['nama'] }}</span>
                                    @if($baris['terkunci'])
                                        <span class="inline-flex items-center rounded-full bg-danger/10 px-2 py-0.5 text-[11px] font-semibold text-danger">Terkunci</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Setoran Masuk --}}
                            <td class="whitespace-nowrap px-4 py-3 text-right text-xs font-medium text-ink tabular-nums">
                                Rp {{ number_format($baris['total_belum_disetor'], 0, ',', '.') }}
                            </td>

                            {{-- Penarikan Tunai --}}
                            <td class="whitespace-nowrap px-4 py-3 text-right text-xs font-medium text-ink tabular-nums">
                                Rp {{ number_format($baris['penarikan_tunai'], 0, ',', '.') }}
                            </td>

                            {{-- Kas di Tangan --}}
                            <td class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold text-ink tabular-nums">
                                Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}
                            </td>

                            {{-- Transaksi --}}
                            <td class="whitespace-nowrap px-4 py-3 text-center text-xs text-body tabular-nums">
                                {{ $baris['jumlah_transaksi'] }}
                            </td>

                            {{-- Umur Tertua --}}
                            <td class="whitespace-nowrap px-4 py-3 text-center text-xs text-body tabular-nums">
                                {{ $baris['umur_terlama_hari'] > 0 ? $baris['umur_terlama_hari'] . ' hari' : '-' }}
                            </td>

                            {{-- Pengajuan Pending --}}
                            <td class="whitespace-nowrap px-4 py-3 text-center text-xs text-body tabular-nums">
                                {{ $baris['pengajuan_pending'] }}
                            </td>

                            {{-- Akumulasi Selisih --}}
                            <td class="whitespace-nowrap px-4 py-3 text-right text-xs font-medium tabular-nums">
                                @if($baris['selisih_kumulatif'] != 0)
                                    @if($baris['selisih_kumulatif'] > 0)
                                        <span class="text-warning-deep">+Rp {{ number_format($baris['selisih_kumulatif'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-danger">Rp -{{ number_format(abs($baris['selisih_kumulatif']), 0, ',', '.') }}</span>
                                    @endif
                                @else
                                    <span class="text-mute">Rp 0</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                @if($baris['terkunci'])
                                    <x-admin.status-chip status="Terkunci" />
                                @elseif($baris['lewat_batas'])
                                    <x-admin.status-chip status="Lewat Batas" />
                                @else
                                    <x-admin.status-chip status="Aman" />
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <button
                                    type="button"
                                    wire:click="bukaDetail({{ $baris['kolektor_id'] }})"
                                    class="min-h-11 h-8 inline-flex items-center justify-center rounded-[8px] border border-hairline bg-surface px-3 text-xs font-medium text-ink transition hover:bg-surface-2 active:scale-95 cursor-pointer shadow-xs"
                                >
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="p-0">
                                <x-admin.empty-state
                                    title="Belum ada kolektor aktif"
                                    description="Data kas di tangan akan muncul saat kolektor mulai menerima setoran."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Fallback Kartu Mobile --}}
        <div data-test="kartu-kas" class="divide-y divide-hairline md:hidden">
            @forelse($daftarKas as $baris)
                <div @class(['p-4 space-y-3', 'bg-warning-soft/20' => $baris['lewat_batas']])>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-medium text-ink flex items-center gap-1.5">
                                <span>{{ $baris['nama'] }}</span>
                                @if($baris['terkunci'])
                                    <span class="inline-flex items-center rounded-full bg-danger/10 px-2 py-0.5 text-[11px] font-semibold text-danger">Terkunci</span>
                                @endif
                            </div>
                            <div class="text-xs text-mute mt-0.5">
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
                    <div class="grid grid-cols-2 gap-2 text-xs border-t border-b border-hairline py-2.5">
                        <div>
                            <span class="text-mute">Setoran Masuk:</span>
                            <div class="font-medium tabular-nums text-ink">Rp {{ number_format($baris['total_belum_disetor'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-mute">Penarikan Tunai:</span>
                            <div class="font-medium tabular-nums text-ink">Rp {{ number_format($baris['penarikan_tunai'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-mute">Kas di Tangan:</span>
                            <div class="font-semibold tabular-nums text-sm text-ink">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <span class="text-mute">Akumulasi Selisih:</span>
                            <div class="font-medium tabular-nums">
                                @if($baris['selisih_kumulatif'] != 0)
                                    @if($baris['selisih_kumulatif'] > 0)
                                        <span class="text-warning-deep">+Rp {{ number_format($baris['selisih_kumulatif'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-danger">Rp -{{ number_format(abs($baris['selisih_kumulatif']), 0, ',', '.') }}</span>
                                    @endif
                                @else
                                    <span class="text-mute">Rp 0</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button
                            type="button"
                            wire:click="bukaDetail({{ $baris['kolektor_id'] }})"
                            class="min-h-11 h-8 inline-flex items-center justify-center rounded-[8px] border border-hairline bg-surface px-3 text-xs font-medium text-ink transition hover:bg-surface-2 active:scale-95 cursor-pointer shadow-xs"
                        >
                            Detail
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-6">
                    <x-admin.empty-state
                        title="Belum ada kolektor aktif"
                        description="Data kas di tangan akan muncul saat kolektor mulai menerima setoran."
                    />
                </div>
            @endforelse
        </div>

        {{-- Footer Tabel sesuai Prototipe HTML --}}
        <div class="flex items-center justify-between border-t border-hairline px-4 py-3 text-xs text-mute">
            <span>Menampilkan {{ count($daftarKas) }} dari {{ count($daftarKas) }} data</span>
            <span>Halaman 1 dari 1</span>
        </div>
    </div>

    {{-- Drawer Slide-over: Detail Kas Kolektor --}}
    @if($detailKolektorId && $detailKas)
        <div class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true" aria-label="Detail Kas Kolektor">
            <div class="fixed inset-0 bg-black/45 transition-opacity cursor-pointer" wire:click="tutupDetail"></div>

            <div class="relative z-10 flex h-full w-full max-w-[480px] flex-col border-l border-hairline bg-surface shadow-2xl transition-all">
                {{-- Header Drawer --}}
                <div class="flex items-center justify-between border-b border-hairline px-5 py-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-semibold text-ink">Detail Kas Kolektor</h3>
                            @if($detailKas['baris'] && $detailKas['baris']['terkunci'])
                                <x-admin.status-chip status="Terkunci" />
                            @elseif($detailKas['baris'] && $detailKas['baris']['lewat_batas'])
                                <x-admin.status-chip status="Lewat Batas" />
                            @else
                                <x-admin.status-chip status="Aman" />
                            @endif
                        </div>
                        <p class="text-xs text-mute mt-0.5">
                            {{ $detailKas['kolektor']->name ?? 'Kolektor' }} &middot; {{ $detailKas['kolektor']->no_hp ?? '-' }}
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="tutupDetail"
                        class="flex size-8 shrink-0 items-center justify-center rounded-[8px] text-mute hover:bg-surface-2 hover:text-ink cursor-pointer"
                        aria-label="Tutup panel detail"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Content Drawer --}}
                <div class="flex-1 overflow-y-auto p-5 space-y-4">
                    @if($detailKas['baris'])
                        {{-- Ringkasan Finansial --}}
                        <div class="rounded-[10px] border border-hairline bg-surface-2 p-3.5 space-y-2">
                            <div class="flex justify-between text-xs">
                                <span class="text-mute">Kas di Tangan (Net)</span>
                                <b class="font-semibold text-sm tabular-nums text-ink">
                                    Rp {{ number_format($detailKas['baris']['kas_di_tangan'], 0, ',', '.') }}
                                </b>
                            </div>
                            <div class="flex justify-between text-xs border-t border-hairline pt-2">
                                <span class="text-mute">Total Setoran Masuk</span>
                                <span class="font-medium tabular-nums text-ink">
                                    Rp {{ number_format($detailKas['baris']['total_belum_disetor'], 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex justify-between text-xs border-t border-hairline pt-2">
                                <span class="text-mute">Total Penarikan Tunai</span>
                                <span class="font-medium tabular-nums text-ink">
                                    Rp {{ number_format($detailKas['baris']['penarikan_tunai'], 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex justify-between text-xs border-t border-hairline pt-2">
                                <span class="text-mute">Transaksi &amp; Umur</span>
                                <span class="text-ink">
                                    {{ $detailKas['baris']['jumlah_transaksi'] }} transaksi &middot; {{ $detailKas['baris']['umur_terlama_hari'] > 0 ? $detailKas['baris']['umur_terlama_hari'] . ' hari' : '-' }}
                                </span>
                            </div>
                            <div class="flex justify-between text-xs border-t border-hairline pt-2">
                                <span class="text-mute">Akumulasi Selisih Rekon</span>
                                <span class="font-medium tabular-nums">
                                    @if($detailKas['baris']['selisih_kumulatif'] != 0)
                                        @if($detailKas['baris']['selisih_kumulatif'] > 0)
                                            <span class="text-warning-deep">+Rp {{ number_format($detailKas['baris']['selisih_kumulatif'], 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-danger">Rp -{{ number_format(abs($detailKas['baris']['selisih_kumulatif']), 0, ',', '.') }}</span>
                                        @endif
                                    @else
                                        <span class="text-mute">Rp 0</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endif

                    {{-- Daftar Setoran Masuk Belum Disetor --}}
                    @if($detailKas['setoran_list']->count() > 0)
                        <div class="space-y-2">
                            <h4 class="text-xs uppercase tracking-[0.05em] text-mute font-medium">Setoran Masuk Belum Disetor ({{ $detailKas['setoran_list']->count() }})</h4>
                            <div class="rounded-[10px] border border-hairline divide-y divide-hairline overflow-hidden">
                                @foreach($detailKas['setoran_list'] as $trx)
                                    <div class="p-3 text-xs flex items-center justify-between hover:bg-surface-2 transition">
                                        <div>
                                            <p class="font-medium text-ink">{{ $trx->nasabah->name ?? 'Nasabah' }}</p>
                                            <p class="text-[11px] text-mute">{{ $trx->produk->nama ?? 'Tabungan' }} &middot; {{ $trx->tanggal_transaksi ? \Illuminate\Support\Carbon::parse($trx->tanggal_transaksi)->translatedFormat('d M Y') : '-' }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-semibold tabular-nums text-ink">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</p>
                                            <span class="text-[10px] uppercase text-mute">{{ $trx->sumber_input }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Tautan Aksi Cepat --}}
                    <div class="pt-2">
                        <a href="/admin/rekonsiliasi" wire:navigate
                            class="min-h-11 flex w-full items-center justify-center gap-2 rounded-[8px] bg-accent px-4 py-2.5 text-xs font-semibold text-accent-foreground shadow-xs transition hover:opacity-90">
                            Buka Rekonsiliasi Kas Kolektor Ini
                        </a>
                    </div>
                </div>

                {{-- Footer Drawer --}}
                <div class="border-t border-hairline p-4 flex justify-end">
                    <button
                        type="button"
                        wire:click="tutupDetail"
                        class="min-h-11 h-9 w-full rounded-[8px] border border-hairline bg-surface text-xs font-medium text-ink hover:bg-surface-2 transition cursor-pointer shadow-xs"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
