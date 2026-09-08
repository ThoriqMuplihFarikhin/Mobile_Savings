<div>
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Saldo Saya</h1>
        <p class="mt-1 text-sm text-[#888888]">Detail saldo per produk tabungan Anda.</p>
    </div>

    {{-- Total Saldo Hero Card --}}
    <div class="mb-6 rounded-xl bg-[#171717] p-6 shadow-[0px_2px_2px_#0000000a,0px_8px_16px_-4px_#0000000a]">
        <p class="font-mono text-xs uppercase tracking-widest text-[#888888]">Total Saldo</p>
        <p class="mt-3 text-4xl font-semibold tracking-tight text-white">
            Rp {{ number_format($totalSaldo, 0, ',', '.') }}
        </p>
        <p class="mt-2 text-sm text-[#a1a1a1]">Akumulasi dari seluruh produk tabungan aktif.</p>
    </div>

    {{-- Per-Product Cards --}}
    <div class="grid gap-4 sm:grid-cols-2">
        @forelse($saldo as $item)
            <div class="rounded-xl bg-white p-5 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        {{-- Badge Tipe --}}
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 font-mono text-xs
                            {{ $item->produk->isPaket()
                                ? 'bg-[#d8ccf1] text-[#4c2889]'
                                : 'bg-[#d3e5ff] text-[#0761d1]' }}">
                            {{ $item->produk->tipe === 'paket' ? 'Tabungan Paket' : 'Tabungan Bebas' }}
                        </span>
                        <p class="mt-2 text-sm text-[#4d4d4d]">{{ $item->produk->nama }}</p>
                        <p class="mt-1 text-2xl font-semibold tracking-tight text-[#171717]">
                            Rp {{ number_format($item->saldo, 0, ',', '.') }}
                        </p>
                    </div>
                    {{-- Icon --}}
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg
                        {{ $item->produk->isPaket() ? 'bg-[#d8ccf1] text-[#7928ca]' : 'bg-[#d3e5ff] text-[#0070f3]' }}">
                        @if($item->produk->isPaket())
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @endif
                    </div>
                </div>
                @if($item->produk->isPaket() && $item->produk->tanggal_boleh_cair)
                    <div class="mt-4 flex items-center gap-1.5 border-t border-[#ebebeb] pt-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#888888]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span class="font-mono text-xs text-[#888888]">Cair: {{ \Carbon\Carbon::parse($item->produk->tanggal_boleh_cair)->translatedFormat('d M Y') }}</span>
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full rounded-xl bg-[#fafafa] p-12 shadow-[inset_0_0_0_1px_#ebebeb]">
                <div class="flex flex-col items-center gap-3 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white shadow-[inset_0_0_0_1px_#ebebeb]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-[#a1a1a1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-[#171717]">Belum ada tabungan aktif</p>
                        <p class="mt-1 text-sm text-[#888888]">Hubungi kolektor Anda untuk memulai menabung.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
