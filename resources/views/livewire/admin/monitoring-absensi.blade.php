<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Operasional"
        title="Monitoring Absensi"
        subtitle="Lihat kehadiran kolektor harian dan tanggapi pengajuan izin."
    />

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

    {{-- Filter Tanggal --}}
    <div class="flex flex-wrap items-center gap-2">
        <label class="text-xs font-medium text-mute">Pilih Tanggal:</label>
        <x-ui.tanggal wire:model.live="tanggal"
            class="h-[38px] w-full max-w-xs rounded-[10px] border border-hairline bg-white px-3 text-xs text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
    </div>

    {{-- Stats Grid --}}
    <x-admin.kpi-grid columns="4">
        <x-admin.kpi-card
            label="Total Kolektor"
            value="{{ $kolektors->count() }}"
            sub="Petugas aktif"
        />
        <x-admin.kpi-card
            label="Sudah Absen"
            value="{{ $absensi->count() }}"
            sub="Hadir di lapangan"
        />
        <x-admin.kpi-card
            label="Izin"
            value="{{ $jumlahIzin }}"
            sub="Disetujui hari ini"
            variant="warn"
        />
        <x-admin.kpi-card
            label="Belum Absen"
            value="{{ $jumlahBelumAbsen }}"
            sub="Perlu konfirmasi"
            variant="error"
        />
    </x-admin.kpi-grid>

    {{-- Pengajuan Izin Pending --}}
    @if ($izinPending->isNotEmpty())
        <div class="overflow-hidden rounded-[14px] border border-warning/20 bg-white shadow-xs dark:bg-surface">
            <div class="border-b border-warning/20 bg-warning-soft px-5 py-3 flex items-center justify-between">
                <p class="text-xs font-semibold text-warning-deep uppercase tracking-wider">Pengajuan Izin Menunggu Persetujuan</p>
                <span class="rounded-full border border-warning/20 bg-warning-soft px-2 py-0.5 text-xs font-semibold text-warning-deep">
                    {{ $izinPending->count() }} antrean
                </span>
            </div>
            <div class="divide-y divide-hairline">
                @foreach ($izinPending as $izin)
                    <div class="p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
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
                                <button wire:click="prosesIzin({{ $izin->id }}, 'disetujui')"
                                    class="inline-flex h-[32px] items-center rounded-[8px] bg-primary px-3 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover">
                                    Setujui
                                </button>
                                <button wire:click="prosesIzin({{ $izin->id }}, 'ditolak')"
                                    class="inline-flex h-[32px] items-center rounded-[8px] border border-hairline bg-white px-3 text-xs font-semibold text-danger shadow-xs transition hover:bg-danger-soft dark:bg-surface">
                                    Tolak
                                </button>
                            </div>
                        </div>
                        <input type="text" wire:model="catatanIzin[{{ $izin->id }}]" placeholder="Catatan admin (opsional)"
                            class="mt-2.5 h-[34px] w-full rounded-[8px] border border-hairline bg-surface px-3 text-xs text-ink placeholder:text-subtle focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tabel Absensi --}}
    <div class="overflow-hidden rounded-[14px] border border-hairline bg-white shadow-xs dark:bg-surface">
        <div class="border-b border-hairline px-6 py-4">
            <h3 class="text-sm font-semibold text-ink">Daftar Kehadiran Kolektor</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-hairline bg-inset/50">
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Nama Kolektor</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">No. HP</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Bukti</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-mute">Lokasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse($kolektors as $kolektor)
                        @php $absen = $absensi->get($kolektor->id); @endphp
                        <tr class="transition hover:bg-inset/30 {{ $selectedKolektorId === $kolektor->id ? 'bg-primary-soft/30' : '' }}">
                            <td class="px-4 py-3 text-xs font-medium text-ink">{{ $kolektor->name }}</td>
                            <td class="px-4 py-3 text-xs text-mute">{{ $kolektor->no_hp }}</td>
                            <td class="px-4 py-3">
                                @if($absen)
                                    <x-admin.status-chip
                                        label="Masuk {{ \Carbon\Carbon::parse($absen->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i') }}"
                                        variant="success"
                                    />
                                @elseif($izinHariIni->contains($kolektor->id))
                                    <x-admin.status-chip
                                        label="Sedang Izin"
                                        variant="warn"
                                    />
                                @else
                                    <x-admin.status-chip
                                        label="Belum absen"
                                        variant="danger"
                                    />
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($absen)
                                    <button wire:click="selectKolektor({{ $kolektor->id }})"
                                        class="text-xs font-medium text-primary hover:underline">
                                        {{ $selectedKolektorId === $kolektor->id ? 'Tutup' : 'Lihat Bukti' }}
                                    </button>
                                @else
                                    <span class="text-xs text-subtle">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($absen && $absen->latitude && $absen->longitude)
                                    <a href="https://www.google.com/maps?q={{ $absen->latitude }},{{ $absen->longitude }}" target="_blank" rel="noopener noreferrer"
                                        class="text-xs text-primary hover:underline">
                                        {{ round($absen->latitude, 5) }}, {{ round($absen->longitude, 5) }}
                                    </a>
                                @else
                                    <span class="text-xs text-subtle">-</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Detail Row --}}
                        @if($selectedKolektorId === $kolektor->id && $selectedAbsen)
                            <tr class="border-b border-hairline">
                                <td colspan="5" class="px-5 py-4 bg-inset/40">
                                    <div class="grid gap-4 sm:grid-cols-3">
                                        {{-- Foto Selfie --}}
                                        <div>
                                            <p class="mb-2 text-xs font-semibold text-ink">Foto Selfie</p>
                                            @if($selectedAbsen->foto_selfie_path)
                                                <img src="{{ route('absensi.foto', [$selectedAbsen, 'selfie']) }}"
                                                    alt="Selfie {{ $kolektor->name }}"
                                                    class="h-40 w-40 rounded-[10px] border border-hairline object-cover shadow-xs" />
                                            @else
                                                <div class="flex h-40 w-40 items-center justify-center rounded-[10px] border border-hairline bg-inset text-xs text-mute">
                                                    Tidak ada selfie
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Tanda Tangan --}}
                                        <div>
                                            <p class="mb-2 text-xs font-semibold text-ink">Tanda Tangan</p>
                                            @if($selectedAbsen->tanda_tangan_path)
                                                <img src="{{ route('absensi.foto', [$selectedAbsen, 'tanda-tangan']) }}"
                                                    alt="Tanda Tangan {{ $kolektor->name }}"
                                                    class="h-40 w-40 rounded-[10px] border border-hairline bg-white object-contain p-2 shadow-xs dark:bg-surface" />
                                            @else
                                                <div class="flex h-40 w-40 items-center justify-center rounded-[10px] border border-hairline bg-inset text-xs text-mute">
                                                    Tidak ada tanda tangan
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Info Lain --}}
                                        <div>
                                            <p class="mb-2 text-xs font-semibold text-ink">Detail Absensi</p>
                                            <div class="space-y-2 rounded-[10px] border border-hairline bg-white p-3.5 shadow-xs dark:bg-surface text-xs">
                                                <div>
                                                    <p class="text-[11px] text-mute">Waktu Masuk</p>
                                                    <p class="font-medium text-ink">{{ \Carbon\Carbon::parse($selectedAbsen->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i:s') }} WIB</p>
                                                </div>
                                                <div>
                                                    <p class="text-[11px] text-mute">Tanggal</p>
                                                    <p class="font-medium text-ink">{{ $selectedAbsen->tanggal->translatedFormat('d M Y') }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-[11px] text-mute">Koordinat</p>
                                                    <p class="font-medium text-ink">{{ round($selectedAbsen->latitude, 6) }}, {{ round($selectedAbsen->longitude, 6) }}</p>
                                                </div>
                                                <a href="https://www.google.com/maps?q={{ $selectedAbsen->latitude }},{{ $selectedAbsen->longitude }}" target="_blank" rel="noopener noreferrer"
                                                    class="inline-flex items-center gap-1 font-medium text-primary hover:underline">
                                                    <span>Buka di Google Maps</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-xs text-mute">Tidak ada data kolektor.</td>
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
                            <p class="text-[11px] text-mute">{{ $kolektor->no_hp }}</p>
                        </div>
                        @if($absen)
                            <x-admin.status-chip
                                label="Masuk {{ \Carbon\Carbon::parse($absen->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i') }}"
                                variant="success"
                            />
                        @elseif($izinHariIni->contains($kolektor->id))
                            <x-admin.status-chip
                                label="Sedang Izin"
                                variant="warn"
                            />
                        @else
                            <x-admin.status-chip
                                label="Belum absen"
                                variant="danger"
                            />
                        @endif
                    </div>
                    <div class="flex items-center justify-between pt-1 text-xs">
                        @if($absen)
                            <button wire:click="selectKolektor({{ $kolektor->id }})"
                                class="font-medium text-primary hover:underline">
                                {{ $selectedKolektorId === $kolektor->id ? 'Tutup Bukti' : 'Lihat Bukti Foto' }}
                            </button>
                        @else
                            <span class="text-mute">-</span>
                        @endif

                        @if($absen && $absen->latitude && $absen->longitude)
                            <a href="https://www.google.com/maps?q={{ $absen->latitude }},{{ $absen->longitude }}" target="_blank" rel="noopener noreferrer"
                                class="font-medium text-primary hover:underline">
                                Maps
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-xs text-mute">Tidak ada data kolektor.</div>
            @endforelse
        </div>
    </div>
</div>
