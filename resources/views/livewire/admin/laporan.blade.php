<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Laporan Keuangan</h1>
        <p class="mt-1 text-sm text-gray-500">Rekapitulasi setoran dan penarikan.</p>
    </div>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex gap-2">
                @foreach(['harian' => 'Harian', 'bulanan' => 'Bulanan'] as $value => $label)
                    <button wire:click="$set('periode', '{{ $value }}')"
                        class="rounded-full px-4 py-2 text-sm font-medium transition {{ $periode === $value ? 'bg-indigo-800 text-white' : 'bg-gray-50 text-gray-600 shadow-[inset_0_0_0_1px_#ebebeb] hover:bg-white' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            @if($periode === 'harian')
                <input type="date" wire:model.live="tanggal"
                    class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
            @else
                <input type="month" wire:model.live="bulan"
                    class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
            @endif
        </div>
        <button wire:click="exportCsv"
            class="inline-flex items-center gap-2 rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Export CSV
        </button>
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
            <p class="text-xs text-gray-500">{{ $jumlahTransaksiPenarikan }} transaksi</p>
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
                                <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ $dailySetoran->has($day) ? 'Rp ' . number_format($dailySetoran[$day]->sum('nominal'), 0, ',', '.') : '-' }}</td>
                                <td class="px-3 py-2 font-mono text-sm text-gray-900">{{ $dailyPenarikan->has($day) ? 'Rp ' . number_format($dailyPenarikan[$day]->sum('nominal_diminta'), 0, ',', '.') : '-' }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>