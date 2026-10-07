<div>
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Kas di Tangan Kolektor</h1>
            <p class="mt-1 text-sm text-gray-500">Pantau uang tunai yang belum disetorkan kolektor ke kantor.</p>
        </div>
        <a href="/admin/rekonsiliasi" wire:navigate
            class="rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-xs font-medium text-gray-900 transition hover:bg-gray-50">
            Buka Rekonsiliasi
        </a>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-border bg-surface p-4">
            <div class="mb-1 text-xs text-text-muted dark:text-slate-400">Total Kas</div>
            <div class="font-serif text-[22px] font-semibold text-text dark:text-white">Rp {{ number_format($totalKas, 0, ',', '.') }}</div>
        </div>
        <div class="rounded-lg border border-border bg-surface p-4">
            <div class="mb-1 text-xs text-text-muted dark:text-slate-400">Kolektor Melewati Batas</div>
            <div class="font-serif text-[22px] font-semibold text-text dark:text-white">{{ $jumlahLewatBatas }}</div>
        </div>
        <div class="rounded-lg border border-border bg-surface p-4">
            <div class="mb-1 text-xs text-text-muted dark:text-slate-400">Batas Kas / Batas Hari</div>
            <div class="font-serif text-[22px] font-semibold text-text dark:text-white">
                Rp {{ number_format((int) \App\Models\AdminSetting::get('batas_kas_kolektor', '0'), 0, ',', '.') }}
                / {{ (int) \App\Models\AdminSetting::get('batas_hari_kas', '0') }} hari
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        <div class="border-b border-[#ebebeb] px-6 py-4">
            <h3 class="text-sm font-semibold text-gray-900">Kas Per Kolektor</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-gray-50">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Kolektor</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Setoran Masuk</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Penarikan Tunai</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Kas di Tangan</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Transaksi</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Umur Tertua</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Pengajuan Pending</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Akumulasi Selisih</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($daftarKas as $baris)
                        <tr @class(['transition hover:bg-gray-50', 'bg-amber-50' => $baris['lewat_batas']])>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $baris['nama'] }}
                                @if($baris['terkunci'])
                                    <span class="ml-1.5 inline-flex items-center rounded-full bg-[#f7d4d6] px-2 py-0.5 font-mono text-xs text-[#c50000]">Terkunci</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-600">Rp {{ number_format($baris['total_belum_disetor'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-600">Rp {{ number_format($baris['penarikan_tunai'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 font-mono text-sm font-semibold text-gray-900">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ $baris['jumlah_transaksi'] }}</td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ $baris['umur_terlama_hari'] }} hari</td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ $baris['pengajuan_pending'] }}</td>
                            <td class="px-4 py-3 font-mono text-sm {{ $baris['selisih_kumulatif'] != 0 ? 'text-[#ee0000]' : 'text-gray-600' }}">
                                {{ $baris['selisih_kumulatif'] >= 0 ? '+' : '' }}Rp {{ number_format($baris['selisih_kumulatif'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($baris['lewat_batas'])
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 font-mono text-xs text-[#ab570a]">Lewat Batas</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 font-mono text-xs text-indigo-600">Aman</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <p class="text-sm text-gray-500">Belum ada kolektor aktif.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div data-test="kartu-kas" class="divide-y divide-[#ebebeb] md:hidden">
            @forelse($daftarKas as $baris)
                <div class="px-4 py-3" @class(['bg-amber-50' => $baris['lewat_batas']])>
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <span class="text-sm font-medium text-gray-900">{{ $baris['nama'] }}</span>
                            @if($baris['terkunci'])
                                <span class="ml-1.5 inline-flex items-center rounded-full bg-[#f7d4d6] px-2 py-0.5 font-mono text-xs text-[#c50000]">Terkunci</span>
                            @endif
                        </div>
                        @if($baris['lewat_batas'])
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 font-mono text-xs text-[#ab570a]">Lewat Batas</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 font-mono text-xs text-indigo-600">Aman</span>
                        @endif
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                        <div>
                            <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Setoran Masuk</span>
                            <span class="font-mono text-sm text-gray-600">Rp {{ number_format($baris['total_belum_disetor'], 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Penarikan Tunai</span>
                            <span class="font-mono text-sm text-gray-600">Rp {{ number_format($baris['penarikan_tunai'], 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Kas di Tangan</span>
                            <span class="font-mono text-sm font-semibold text-gray-900">Rp {{ number_format($baris['kas_di_tangan'], 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Transaksi</span>
                            <span class="font-mono text-sm text-gray-600">{{ $baris['jumlah_transaksi'] }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Umur Tertua</span>
                            <span class="font-mono text-sm text-gray-600">{{ $baris['umur_terlama_hari'] }} hari</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Pengajuan Pending</span>
                            <span class="font-mono text-sm text-gray-600">{{ $baris['pengajuan_pending'] }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Akumulasi Selisih</span>
                            <span class="font-mono text-sm {{ $baris['selisih_kumulatif'] != 0 ? 'text-[#ee0000]' : 'text-gray-600' }}">
                                {{ $baris['selisih_kumulatif'] >= 0 ? '+' : '' }}Rp {{ number_format($baris['selisih_kumulatif'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center gap-2 px-4 py-10 text-center">
                    <p class="text-sm text-gray-500">Belum ada kolektor aktif.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
