<x-layouts::admin title="Hub Persetujuan">
    @php
        $kartu = [
            ['kode' => 'penarikan', 'label' => 'Penarikan Menunggu', 'ket' => 'Menunggu persetujuan 1/2 (D11)', 'url' => route('admin.penarikan.index'), 'ikon' => 'cash'],
            ['kode' => 'verifikasi', 'label' => 'Verifikasi Nasabah Baru', 'ket' => 'Pendaftaran menunggu verifikasi', 'url' => route('admin.verifikasi.index'), 'ikon' => 'check'],
            ['kode' => 'setoran_kantor', 'label' => 'Setoran Kantor', 'ket' => 'Setoran kolektor ke kantor pending', 'url' => route('admin.rekonsiliasi.index'), 'ikon' => 'scale'],
            ['kode' => 'komplain', 'label' => 'Komplain Baru', 'ket' => 'Komplain belum diproses', 'url' => route('admin.komplain.index'), 'ikon' => 'chat'],
            ['kode' => 'izin', 'label' => 'Izin Kolektor', 'ket' => 'Pengajuan izin menunggu', 'url' => route('admin.monitoring-absensi.index'), 'ikon' => 'cal'],
            ['kode' => 'bermasalah', 'label' => 'Nasabah Bermasalah', 'ket' => 'Kepesertaan perlu review', 'url' => route('admin.bermasalah.index'), 'ikon' => 'alert'],
            ['kode' => 'tinjauan', 'label' => 'Penarikan Offline Belum Ditinjau', 'ket' => 'Penarikan langsung menunggu tinjauan pasca-fakta', 'url' => route('admin.tinjauan-penarikan-offline'), 'ikon' => 'shield'],
        ];
        $totalMenunggu = array_sum($antrean);
    @endphp

    <div class="space-y-4">
        {{-- Header Halaman --}}
        <x-admin.page-header title="Hub Persetujuan" subtitle="Semua antrean yang menunggu tindakan Anda di satu tempat." />

        {{-- KPI Total Menunggu --}}
        <div class="max-w-xs">
            <x-admin.kpi-card 
                label="Total menunggu" 
                :value="$totalMenunggu" 
                :sub="$totalMenunggu > 0 ? 'Mulai dari yang paling atas' : 'Semua beres'" 
                :variant="$totalMenunggu > 0 ? 'warn' : 'default'" />
        </div>

        {{-- Grid Kartu Antrean --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($kartu as $k)
                @php
                    $hitung = $antrean[$k['kode']] ?? 0;
                    $adaPending = $hitung > 0;
                @endphp
                <a href="{{ $k['url'] }}" wire:navigate data-test="kartu-antrean-{{ $k['kode'] }}"
                   class="group flex items-start gap-3 rounded-[14px] border border-hairline bg-surface p-4 shadow-xs transition hover:-translate-y-0.5 hover:border-primary/40">
                    <div class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-[12px] font-semibold {{ $adaPending ? 'bg-warning-soft text-warning-deep' : 'bg-primary-soft text-on-primary-soft' }}">
                        @if($k['ikon'] === 'cash')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="6" width="20" height="12" rx="2" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        @elseif($k['ikon'] === 'check')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        @elseif($k['ikon'] === 'scale')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M5 7h14M5 7l-3 7a4 4 0 006 0zM19 7l-3 7a4 4 0 006 0z" />
                            </svg>
                        @elseif($k['ikon'] === 'chat')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-.812-.977 4.975 4.975 0 001.218-2.614C4.166 15.938 3 14.07 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                            </svg>
                        @elseif($k['ikon'] === 'cal')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        @elseif($k['ikon'] === 'alert')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        @elseif($k['ikon'] === 'shield')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <b class="block truncate text-sm font-semibold text-ink group-hover:text-primary transition-colors">
                            {{ $k['label'] }}
                        </b>
                        <small class="block truncate text-xs text-mute mt-0.5">
                            {{ $k['ket'] }}
                        </small>
                    </div>
                    <span class="ml-auto text-xl font-semibold tabular-nums {{ $adaPending ? 'text-warning-deep' : 'text-ink' }}" data-test="hitung-{{ $k['kode'] }}">
                        {{ $hitung }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</x-layouts::admin>
