<div>
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Progres Paket</h1>
        <p class="mt-1 text-sm text-[#888888]">Detail kepesertaan paket tabungan Anda.</p>
    </div>

    <div class="space-y-4">
        @forelse($kepesertaan as $item)
            @php
                $persentase = $item->total_seharusnya_terkumpul > 0
                    ? round(($item->total_aktual_terkumpul / $item->total_seharusnya_terkumpul) * 100)
                    : 0;
                $persentase = min($persentase, 100);
                $barColor = match($item->status_alert) {
                    'normal'     => 'bg-[#0070f3]',
                    'peringatan' => 'bg-[#f5a623]',
                    default      => 'bg-[#ee0000]',
                };
            @endphp
            <div class="rounded-xl bg-white p-6 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                {{-- Header --}}
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-base font-semibold text-[#171717]">{{ $item->produk->nama ?? '-' }}</h3>
                        <p class="mt-0.5 font-mono text-xs text-[#888888]">Mulai: {{ $item->tanggal_mulai_ikut->translatedFormat('d M Y') }}</p>
                    </div>
                    <div class="shrink-0">
                        @if($item->status_alert === 'normal')
                            <span class="inline-flex items-center rounded-full bg-[#d3e5ff] px-2.5 py-0.5 font-mono text-xs text-[#0761d1]">Normal</span>
                        @elseif($item->status_alert === 'peringatan')
                            <span class="inline-flex items-center rounded-full bg-[#ffefcf] px-2.5 py-0.5 font-mono text-xs text-[#ab570a]">Peringatan</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2.5 py-0.5 font-mono text-xs text-[#c50000]">Perlu Review</span>
                        @endif
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="mt-5">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#4d4d4d]">Progres terkumpul</span>
                        <span class="font-mono text-sm font-medium text-[#171717]">{{ $persentase }}%</span>
                    </div>
                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-[#f5f5f5]">
                        <div class="h-full rounded-full transition-all duration-500 {{ $barColor }}"
                            style="width: {{ $persentase }}%"></div>
                    </div>
                    <div class="mt-1.5 flex items-center justify-between font-mono text-xs text-[#888888]">
                        <span>Rp {{ number_format($item->total_aktual_terkumpul, 0, ',', '.') }}</span>
                        <span>Rp {{ number_format($item->total_seharusnya_terkumpul, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Tunggakan Alert --}}
                @if($item->tunggakan > 0)
                    <div class="mt-4 flex items-center gap-2.5 rounded-lg bg-[#f7d4d6] px-4 py-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-[#ee0000]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                        <p class="text-sm font-medium text-[#c50000]">
                            Tunggakan: Rp {{ number_format($item->tunggakan, 0, ',', '.') }}
                        </p>
                    </div>
                @endif

                {{-- Detail Grid --}}
                @if($item->produk && $item->produk->isPaket())
                    @php
                        $details = array_filter([
                            $item->produk->tanggal_boleh_cair ? ['label' => 'Tanggal Boleh Cair', 'value' => \Carbon\Carbon::parse($item->produk->tanggal_boleh_cair)->translatedFormat('d M Y')] : null,
                            $item->keputusan_akhir           ? ['label' => 'Keputusan Akhir', 'value' => ucfirst(str_replace('_', ' ', $item->keputusan_akhir))] : null,
                            $item->metode_pengambilan        ? ['label' => 'Metode Pengambilan', 'value' => $item->metode_pengambilan === 'ambil_sendiri' ? 'Ambil Sendiri' : 'Diantar Kolektor'] : null,
                            $item->status_serah_terima       ? ['label' => 'Serah Terima', 'value' => $item->status_serah_terima === 'sudah_diterima' ? 'Sudah Diterima' : 'Belum'] : null,
                        ]);
                    @endphp
                    @if(count($details) > 0)
                        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-[#ebebeb] pt-4">
                            @foreach($details as $detail)
                                <div class="rounded-lg bg-[#fafafa] px-3 py-2.5">
                                    <p class="font-mono text-xs text-[#888888]">{{ $detail['label'] }}</p>
                                    <p class="mt-1 text-sm font-medium text-[#171717]">{{ $detail['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif

                {{-- Admin Note --}}
                @if($item->catatan_admin)
                    <div class="mt-4 rounded-lg bg-[#ffefcf] px-4 py-3">
                        <p class="font-mono text-xs text-[#ab570a]">Catatan Admin</p>
                        <p class="mt-1 text-sm text-[#ab570a]">{{ $item->catatan_admin }}</p>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-xl bg-[#fafafa] p-12 shadow-[inset_0_0_0_1px_#ebebeb]">
                <div class="flex flex-col items-center gap-3 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white shadow-[inset_0_0_0_1px_#ebebeb]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-[#a1a1a1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-[#171717]">Belum ada kepesertaan paket</p>
                        <p class="mt-1 text-sm text-[#888888]">Tanya kolektor Anda tentang pilihan paket tabungan.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
