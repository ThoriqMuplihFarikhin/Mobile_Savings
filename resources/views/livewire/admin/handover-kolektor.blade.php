<div class="mx-auto max-w-7xl space-y-6">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Handover Kolektor</h1>
        <p class="mt-1 text-sm text-gray-500">Pindahkan nasabah dari kolektor lama ke kolektor pengganti.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-indigo-100 px-4 py-3 text-sm text-indigo-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-[#f7d4d6] px-4 py-3 text-sm text-[#c50000]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 rounded-xl bg-gray-50 shadow-[inset_0_0_0_1px_#ebebeb]">
        <div class="border-b border-[#ebebeb] px-6 py-4">
            <h3 class="text-sm font-semibold text-gray-900">Proses Handover</h3>
        </div>
        <div class="p-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-900">Kolektor Lama (Akan Dinonaktifkan)</label>
                    <select wire:model.live="kolektorLamaId"
                        class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                        <option value="">Pilih Kolektor Lama</option>
                        @foreach($kolektorList as $k)
                            <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->no_hp }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-900">Kolektor Pengganti</label>
                    <select wire:model.live="kolektorBaruId" {{ empty($kolektorLamaId) || $hasUnsettledCash ? 'disabled' : '' }}
                        class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10 disabled:cursor-not-allowed disabled:opacity-50">
                        <option value="">Pilih Kolektor Pengganti</option>
                        @foreach($kolektorList as $k)
                            @if($k->id != $kolektorLamaId)
                                <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->no_hp }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

            @if($selectedKolektorLama)
                <div class="mt-6 space-y-4">
                    <div class="rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                        <h4 class="mb-3 text-sm font-semibold text-gray-900">Checklist Handover</h4>

                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                @if($hasUnsettledCash)
                                    <div class="mt-0.5 h-5 w-5 shrink-0 rounded-full bg-[#f7d4d6] flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#c50000]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </div>
                                @else
                                    <div class="mt-0.5 h-5 w-5 shrink-0 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Status Kas</p>
                                    @if($hasUnsettledCash)
                                        <p class="mt-0.5 text-sm text-[#c50000]">Masih ada kas belum disetor ke kantor: <span class="font-mono font-bold">Rp {{ number_format($unsettledCash, 0, ',', '.') }}</span></p>
                                        <p class="mt-0.5 text-xs text-[#c50000]">Handover <strong>DBLOKIR</strong>. Kolektor harus menyetor kas terlebih dahulu.</p>
                                    @else
                                        <p class="mt-0.5 text-sm text-indigo-600">Semua kas sudah disetor ke kantor.</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                @if($nasabahList->count() > 0)
                                    <div class="mt-0.5 h-5 w-5 shrink-0 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                @else
                                    <div class="mt-0.5 h-5 w-5 shrink-0 rounded-full border-gray-200 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Nasabah Aktif</p>
                                    <p class="mt-0.5 text-sm text-gray-600">{{ $nasabahList->count() }} nasabah akan dipindahkan.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($nasabahList->count() > 0)
                        <div class="rounded-lg bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                            <h4 class="mb-3 text-sm font-semibold text-gray-900">Daftar Nasabah yang Akan Dipindahkan</h4>
                            <div class="max-h-48 space-y-2 overflow-y-auto">
                                @foreach($nasabahList as $item)
                                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                                        <span class="text-sm text-gray-600">{{ $item->nasabah->name ?? '-' }}</span>
                                        <span class="text-xs text-gray-500">Sejak {{ $item->tanggal_mulai_ditangani->translatedFormat('d M Y') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($showConfirmation)
                        <div class="rounded-lg bg-indigo-100 p-4">
                            <p class="text-sm text-indigo-600">
                                Semua {{ $nasabahList->count() }} nasabah akan dipindahkan ke <strong>{{ $kolektorList->where('id', $kolektorBaruId)->first()->name ?? '-' }}</strong>.
                                Kolektor lama akan dinonaktifkan. Lanjutkan?
                            </p>
                            <button wire:click="processHandover" wire:loading.attr="disabled" wire:confirm="Yakin ingin melakukan handover? Tindakan ini tidak dapat dibatalkan."
                                class="mt-3 min-h-11 rounded-full bg-indigo-800 px-4 py-2 text-sm font-medium text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
                                Proses Handover
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <button type="button" wire:click="$refresh"
            class="min-h-11 inline-flex items-center gap-1.5 rounded-full border border-[#ebebeb] bg-white px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-gray-50">
            Muat Ulang
        </button>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
        <div class="border-b border-[#ebebeb] px-6 py-4">
            <h3 class="text-sm font-semibold text-gray-900">Riwayat Handover</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-[#ebebeb] bg-gray-50">
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Tanggal</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Kolektor Lama</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Kolektor Baru</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Nasabah Dipindah</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Status Kas</th>
                        <th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-gray-500">Diproses Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#ebebeb]">
                    @forelse($riwayat as $item)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->tanggal_handover->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->kolektorLama->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->kolektorBaru->name ?? '-' }}</td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ $item->jumlah_nasabah_dipindah }}</td>
                            <td class="px-4 py-3">
                                @if($item->status_kas_saat_handover === 'lunas')
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 font-mono text-xs text-indigo-600">Lunas</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2.5 py-0.5 font-mono text-xs text-[#c50000]">Tunggakan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->diprosesOleh->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                    <p class="text-sm text-gray-500">Belum ada riwayat handover.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden">
            @forelse($riwayat as $item)
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $item->kolektorLama->name ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $item->kolektorBaru->name ?? '-' }}</p>
                        </div>
                        @if($item->status_kas_saat_handover === 'lunas')
                            <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 font-mono text-xs text-indigo-600">Lunas</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2.5 py-0.5 font-mono text-xs text-[#c50000]">Tunggakan</span>
                        @endif
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-1.5">
                        <div>
                            <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Tanggal</span>
                            <span class="font-mono text-sm text-gray-600">{{ $item->tanggal_handover->translatedFormat('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Nasabah Dipindah</span>
                            <span class="font-mono text-sm text-gray-600">{{ $item->jumlah_nasabah_dipindah }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-mono uppercase tracking-wider text-gray-500">Diproses Oleh</span>
                            <span class="font-mono text-sm text-gray-600">{{ $item->diprosesOleh->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <p class="text-sm text-gray-500">Belum ada riwayat handover.</p>
                </div>
            @endforelse
        </div>
        <div class="border-t border-[#ebebeb] px-4 py-3">{{ $riwayat->links() }}</div>
    </div>
</div>
