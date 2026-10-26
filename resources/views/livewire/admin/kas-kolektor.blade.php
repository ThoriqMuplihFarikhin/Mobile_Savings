<div>
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-ink">Kas di Tangan Kolektor</h1>
            <p class="mt-1 text-sm text-mute">Pantau uang tunai yang belum disetorkan kolektor ke kantor.</p>
        </div>
        <a href="/admin/rekonsiliasi" wire:navigate
            class="rounded-md border border-hairline bg-white px-4 py-2 text-xs font-medium text-ink transition hover:bg-inset">
            Buka Rekonsiliasi
        </a>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-border bg-surface p-4">
            <div class="mb-1 text-xs text-mute dark:text-subtle">Total Kas</div>
            <div class="text-2xl font-semibold text-ink">Rp {{ number_format($totalKas, 0, ',', '.') }}</div>
        </div>
        <div class="rounded-lg border border-border bg-surface p-4">
            <div class="mb-1 text-xs text-mute dark:text-subtle">Kolektor Melewati Batas</div>
            <div class="text-2xl font-semibold text-ink">{{ $jumlahLewatBatas }}</div>
        </div>
        <div class="rounded-lg border border-border bg-surface p-4">
            <div class="mb-1 text-xs text-mute dark:text-subtle">Batas Kas / Batas Hari</div>
            <div class="text-2xl font-semibold text-ink">
                Rp {{ number_format((int) \App\Models\AdminSetting::get('batas_kas_kolektor', '0'), 0, ',', '.') }}
                / {{ (int) \App\Models\AdminSetting::get('batas_hari_kas', '0') }} hari
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-[inset_0_0_0_1px_#e5e5e5]">
        <div class="border-b border-hairline px-6 py-4">
            <h3 class="text-sm font-semibold text-ink">Kas Per Kolektor</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-inset">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Kolektor</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Setoran Masuk</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Penarikan Tunai</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Kas di Tangan</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Transaksi</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Umur Tertua</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Pengajuan Pending</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Akumulasi Selisih</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($daftarKas as $baris)
                        <tr @class(['transition hover:bg-inset', 'bg-warning-soft' => $baris['lewat_batas']])>
                            <td class="px-4 py-3 text-sm font-medium text-ink">
                                {{ $baris['nama'] }}
                                @if($baris['terkunci'])
                                    <span class="ml-1.5 inline-flex items-center rounded-full bg-danger-soft px-2 py-0.5 text-xs text-danger">Terkunci</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 tabular-nums text-sm text-body">Rp {{ number_format($baris['total_belum_disetor'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 tabular-nums text-sm text-body">Rp {{ number_format($baris['penarikan_tunai'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 tabular-nums text-sm font-semibold text-ink">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 tabular-nums text-sm text-body">{{ $baris['jumlah_transaksi'] }}</td>
                            <td class="px-4 py-3 tabular-nums text-sm text-body">{{ $baris['umur_terlama_hari'] }} hari</td>
                            <td class="px-4 py-3 tabular-nums text-sm text-body">{{ $baris['pengajuan_pending'] }}</td>
                            <td class="px-4 py-3 tabular-nums text-sm {{ $baris['selisih_kumulatif'] != 0 ? 'text-danger' : 'text-body' }}">
                                {{ $baris['selisih_kumulatif'] >= 0 ? '+' : '' }}Rp {{ number_format($baris['selisih_kumulatif'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($baris['lewat_batas'])
                                    <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-0.5 text-xs text-warning-deep">Lewat Batas</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Aman</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-hairline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <p class="text-sm text-mute">Belum ada kolektor aktif.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div data-test="kartu-kas" class="divide-y divide-hairline md:hidden">
            @forelse($daftarKas as $baris)
                <div class="px-4 py-3" @class(['bg-warning-soft' => $baris['lewat_batas']])>
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <span class="text-sm font-medium text-ink">{{ $baris['nama'] }}</span>
                            @if($baris['terkunci'])
                                <span class="ml-1.5 inline-flex items-center rounded-full bg-danger-soft px-2 py-0.5 text-xs text-danger">Terkunci</span>
                            @endif
                        </div>
                        @if($baris['lewat_batas'])
                            <span class="inline-flex items-center rounded-full bg-warning-soft px-2.5 py-0.5 text-xs text-warning-deep">Lewat Batas</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-info-soft px-2.5 py-0.5 text-xs text-info">Aman</span>
                        @endif
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                        <div>
                            <span class="block text-xs text-mute">Setoran Masuk</span>
                            <span class="tabular-nums text-sm text-body">Rp {{ number_format($baris['total_belum_disetor'], 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Penarikan Tunai</span>
                            <span class="tabular-nums text-sm text-body">Rp {{ number_format($baris['penarikan_tunai'], 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Kas di Tangan</span>
                            <span class="tabular-nums text-sm font-semibold text-ink">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Transaksi</span>
                            <span class="tabular-nums text-sm text-body">{{ $baris['jumlah_transaksi'] }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Umur Tertua</span>
                            <span class="tabular-nums text-sm text-body">{{ $baris['umur_terlama_hari'] }} hari</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Pengajuan Pending</span>
                            <span class="tabular-nums text-sm text-body">{{ $baris['pengajuan_pending'] }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-mute">Akumulasi Selisih</span>
                            <span class="tabular-nums text-sm {{ $baris['selisih_kumulatif'] != 0 ? 'text-danger' : 'text-body' }}">
                                {{ $baris['selisih_kumulatif'] >= 0 ? '+' : '' }}Rp {{ number_format($baris['selisih_kumulatif'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center gap-2 px-4 py-10 text-center">
                    <p class="text-sm text-mute">Belum ada kolektor aktif.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
