<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Operasional"
        title="Monitoring Absensi"
        subtitle="Lihat kehadiran kolektor harian."
    >
        <x-slot:actions>
            <div class="flex flex-col sm:items-end gap-1.5">
                <div class="relative">
                    <x-ui.tanggal wire:model.live="tanggal"
                        :tanda="$tandaKalender"
                        tandaModel="tandaKalender"
                        class="h-[38px] w-36 rounded-[10px] border border-hairline bg-white pr-9 pl-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface cursor-pointer" />
                </div>
                <div class="flex items-center gap-2.5 text-[11px] text-mute">
                    <span class="inline-flex items-center gap-1 font-medium">
                        <span class="size-2 rounded-full bg-primary inline-block"></span>
                        <span>Absen</span>
                    </span>
                    <span class="inline-flex items-center gap-1 font-medium">
                        <span class="size-2 rounded-full bg-warning-deep inline-block"></span>
                        <span>Izin</span>
                    </span>
                </div>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-primary/20 bg-primary-soft p-3 text-xs font-medium text-on-primary-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-[10px] border border-danger/20 bg-danger-soft p-3 text-xs font-medium text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Stats KPI Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="rounded-2xl border border-hairline/80 bg-white p-5 shadow-xs dark:bg-surface">
            <span class="block text-xs font-medium text-mute">Total Kolektor</span>
            <b class="mt-1 block text-2xl font-bold tracking-tight text-ink tabular-nums">{{ $kolektors->count() }}</b>
        </div>
        <div class="rounded-2xl border border-primary/20 bg-primary-soft/60 p-5 shadow-xs dark:bg-primary/10">
            <span class="block text-xs font-medium text-mute">Sudah Absen</span>
            <b class="mt-1 block text-2xl font-bold tracking-tight text-primary tabular-nums">{{ $absensi->count() }}</b>
        </div>
        <div class="rounded-2xl border border-hairline/80 bg-white p-5 shadow-xs dark:bg-surface">
            <span class="block text-xs font-medium text-mute">Izin</span>
            <b class="mt-1 block text-2xl font-bold tracking-tight text-ink tabular-nums">{{ $jumlahIzin }}</b>
        </div>
        <div class="rounded-2xl border border-hairline/80 bg-white p-5 shadow-xs dark:bg-surface">
            <span class="block text-xs font-medium text-mute">Belum Absen</span>
            <b class="mt-1 block text-2xl font-bold tracking-tight text-warning-deep tabular-nums">{{ $jumlahBelumAbsen }}</b>
        </div>
    </div>

    {{-- Pengajuan Izin Pending --}}
    @if ($izinPending->isNotEmpty())
        <div class="overflow-hidden rounded-2xl border border-warning/20 bg-white shadow-xs dark:bg-surface">
            <div class="border-b border-warning/20 bg-warning-soft px-5 py-3 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold text-warning-deep uppercase tracking-wider">Pengajuan Izin Menunggu Persetujuan</h3>
                </div>
                <span class="rounded-full border border-warning/20 bg-warning-soft px-2.5 py-0.5 text-xs font-semibold text-warning-deep">
                    {{ $izinPending->count() }} antrean
                </span>
            </div>
            <div class="divide-y divide-hairline">
                @foreach ($izinPending as $izin)
                    <div class="p-4 sm:p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-ink">
                                    {{ $izin->kolektor->name }}
                                    <span class="font-normal text-mute">
                                        &middot; {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->translatedFormat('d M Y') }}
                                        - {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->translatedFormat('d M Y') }}
                                    </span>
                                </p>
                                <p class="mt-1 text-xs text-body">{{ $izin->alasan }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="prosesIzin({{ $izin->id }}, 'disetujui')"
                                    class="min-h-11 inline-flex h-8 items-center rounded-lg bg-primary px-3 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-[0.98]">
                                    Setujui
                                </button>
                                <button type="button" wire:click="prosesIzin({{ $izin->id }}, 'ditolak')"
                                    class="min-h-11 inline-flex h-8 items-center rounded-lg border border-danger/20 bg-danger-soft px-3 text-xs font-semibold text-danger shadow-xs transition hover:bg-danger/10 active:scale-[0.98]">
                                    Tolak
                                </button>
                            </div>
                        </div>
                        <input type="text" wire:model="catatanIzin.{{ $izin->id }}" placeholder="Catatan admin (opsional)"
                            class="mt-2.5 h-[34px] w-full rounded-lg border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tabel Kehadiran Kolektor --}}
    <div class="overflow-hidden rounded-2xl border border-hairline/80 bg-white shadow-xs dark:bg-surface">
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead>
                    <tr class="border-b border-hairline bg-surface-2 text-[11px] font-semibold uppercase tracking-wider text-mute">
                        <th class="px-5 py-3">Nama Kolektor</th>
                        <th class="px-5 py-3">No. HP</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Bukti</th>
                        <th class="px-5 py-3">Lokasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($kolektors as $kolektor)
                        @php $absen = $absensi->get($kolektor->id); @endphp
                        <tr class="transition hover:bg-surface-2 {{ $selectedKolektorId === $kolektor->id ? 'bg-primary-soft/20' : '' }}">
                            <td class="whitespace-nowrap px-5 py-3.5 text-xs font-medium text-ink">{{ $kolektor->name }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-xs text-body tabular-nums">{{ $kolektor->no_hp ?? '-' }}</td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                @if($absen)
                                    <x-admin.status-chip status="Sudah absen" />
                                @elseif($izinHariIni->contains($kolektor->id))
                                    <x-admin.status-chip status="Izin" title="Sedang Izin" aria-label="Sedang Izin" />
                                @else
                                    <x-admin.status-chip status="Belum absen" />
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5">
                                @if($absen)
                                    <button type="button" wire:click="selectKolektor({{ $kolektor->id }})"
                                        class="text-xs font-medium text-primary hover:underline cursor-pointer">
                                        Lihat bukti
                                    </button>
                                @else
                                    <span class="text-xs text-mute">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3.5 text-xs text-body">
                                @if($absen && $absen->latitude && $absen->longitude)
                                    <a href="https://www.google.com/maps?q={{ $absen->latitude }},{{ $absen->longitude }}" target="_blank" rel="noopener noreferrer"
                                        class="hover:text-primary hover:underline tabular-nums">
                                        {{ round($absen->latitude, 4) }}, {{ round($absen->longitude, 4) }}
                                    </a>
                                @else
                                    <span class="text-xs text-mute">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-xs text-mute">Tidak ada data kolektor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($kolektors as $kolektor)
                @php $absen = $absensi->get($kolektor->id); @endphp
                <div class="p-4 space-y-2.5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-ink">{{ $kolektor->name }}</p>
                            <p class="text-[11px] text-mute tabular-nums">{{ $kolektor->no_hp ?? '-' }}</p>
                        </div>
                        @if($absen)
                            <x-admin.status-chip status="Sudah absen" />
                        @elseif($izinHariIni->contains($kolektor->id))
                            <x-admin.status-chip status="Izin" title="Sedang Izin" aria-label="Sedang Izin" />
                        @else
                            <x-admin.status-chip status="Belum absen" />
                        @endif
                    </div>
                    <div class="flex items-center justify-between pt-1 text-xs">
                        @if($absen)
                            <button type="button" wire:click="selectKolektor({{ $kolektor->id }})"
                                class="min-h-11 inline-flex items-center font-medium text-primary hover:underline cursor-pointer">
                                Lihat bukti
                            </button>
                        @else
                            <span class="text-mute">-</span>
                        @endif

                        @if($absen && $absen->latitude && $absen->longitude)
                            <a href="https://www.google.com/maps?q={{ $absen->latitude }},{{ $absen->longitude }}" target="_blank" rel="noopener noreferrer"
                                class="min-h-11 inline-flex items-center font-medium text-body hover:text-primary hover:underline tabular-nums">
                                {{ round($absen->latitude, 4) }}, {{ round($absen->longitude, 4) }}
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-xs text-mute">Tidak ada data kolektor.</div>
            @endforelse
        </div>
    </div>

    {{-- Drawer Detail Absensi --}}
    @if($selectedKolektorId && $selectedAbsen)
        <div class="fixed inset-0 z-50 flex justify-end" role="dialog" aria-modal="true" aria-label="Detail Absensi">
            <div class="fixed inset-0 bg-black/45 transition-opacity cursor-pointer" wire:click="selectKolektor(null)"></div>

            <div class="relative z-10 flex h-full w-full max-w-[440px] flex-col border-l border-hairline bg-surface shadow-2xl transition-all">
                {{-- Header Drawer --}}
                <div class="flex items-center justify-between border-b border-hairline px-5 py-4">
                    <div>
                        <h3 class="text-base font-semibold text-ink">Detail Absensi</h3>
                        <p class="text-xs text-mute">{{ $selectedAbsen->kolektor?->name ?? 'Kolektor' }} &middot; {{ $selectedAbsen->tanggal ? $selectedAbsen->tanggal->translatedFormat('d M Y') : '' }}</p>
                    </div>
                    <button type="button" wire:click="selectKolektor(null)"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-mute transition hover:bg-surface-2 hover:text-ink cursor-pointer"
                        aria-label="Tutup">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Content Drawer --}}
                <div class="flex-1 overflow-y-auto p-5 space-y-4">
                    {{-- Info Ringkas --}}
                    <div class="rounded-xl border border-hairline bg-surface-2 p-3.5 text-xs space-y-2">
                        <div class="flex justify-between">
                            <span class="text-mute">Kolektor</span>
                            <span class="font-medium text-ink">{{ $selectedAbsen->kolektor?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-mute">Waktu Masuk</span>
                            <span class="font-medium text-ink tabular-nums">
                                {{ $selectedAbsen->waktu_masuk ? \Carbon\Carbon::parse($selectedAbsen->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i:s') . ' WIB' : '-' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-mute">Koordinat</span>
                            @if($selectedAbsen->latitude && $selectedAbsen->longitude)
                                <a href="https://www.google.com/maps?q={{ $selectedAbsen->latitude }},{{ $selectedAbsen->longitude }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 font-medium text-primary hover:underline tabular-nums">
                                    <span>{{ round($selectedAbsen->latitude, 5) }}, {{ round($selectedAbsen->longitude, 5) }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                </a>
                            @else
                                <span class="text-mute">-</span>
                            @endif
                        </div>
                    </div>

                    {{-- Bukti Foto & Tanda Tangan --}}
                    <div class="grid grid-cols-2 gap-3">
                        {{-- Foto Selfie --}}
                        <div>
                            <p class="mb-1.5 text-xs font-semibold text-ink">Foto Selfie</p>
                            @if($selectedAbsen->foto_selfie_path)
                                <img src="{{ route('absensi.foto', [$selectedAbsen, 'selfie']) }}"
                                    alt="Selfie {{ $selectedAbsen->kolektor?->name }}"
                                    class="h-44 w-full rounded-xl border border-hairline object-cover shadow-2xs" />
                            @else
                                <div class="flex h-44 w-full items-center justify-center rounded-xl border border-dashed border-hairline bg-surface-2 text-xs text-mute text-center p-3">
                                    Tidak ada foto selfie
                                </div>
                            @endif
                        </div>

                        {{-- Tanda Tangan --}}
                        <div>
                            <p class="mb-1.5 text-xs font-semibold text-ink">Tanda Tangan</p>
                            @if($selectedAbsen->tanda_tangan_path)
                                <img src="{{ route('absensi.foto', [$selectedAbsen, 'tanda-tangan']) }}"
                                    alt="Tanda Tangan {{ $selectedAbsen->kolektor?->name }}"
                                    class="h-44 w-full rounded-xl border border-hairline bg-white object-contain p-2 shadow-2xs dark:bg-surface" />
                            @else
                                <div class="flex h-44 w-full items-center justify-center rounded-xl border border-dashed border-hairline bg-surface-2 text-xs text-mute text-center p-3">
                                    Tidak ada tanda tangan
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Footer Drawer --}}
                <div class="border-t border-hairline p-4 flex justify-end">
                    <button type="button" wire:click="selectKolektor(null)"
                        class="min-h-11 h-9 w-full rounded-lg border border-hairline bg-surface text-xs font-medium text-ink hover:bg-surface-2 transition cursor-pointer shadow-xs">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
