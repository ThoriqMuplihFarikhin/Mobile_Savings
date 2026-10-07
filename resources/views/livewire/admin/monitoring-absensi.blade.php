<div class="mx-auto max-w-4xl">
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Monitoring Absensi</h1>
        <p class="mt-1 text-sm text-gray-500">Lihat kehadiran kolektor harian.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Date Picker --}}
    <div class="mb-6">
        <label class="mb-1.5 block text-sm font-medium text-gray-900">Tanggal</label>
        <x-ui.tanggal wire:model.live="tanggal"
               class="h-10 w-full max-w-xs rounded-lg border border-[#ebebeb] bg-white px-3 text-sm text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-4">
        <div class="rounded-xl bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
            <p class="text-sm text-gray-500">Total Kolektor</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $kolektors->count() }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
            <p class="text-sm text-gray-500">Sudah Absen</p>
            <p class="mt-1 text-2xl font-bold text-[#0a7a3d]">{{ $absensi->count() }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
            <p class="text-sm text-gray-500">Izin</p>
            <p class="mt-1 text-2xl font-bold text-[#b45309]">{{ $jumlahIzin }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
            <p class="text-sm text-gray-500">Belum Absen</p>
            <p class="mt-1 text-2xl font-bold text-[#c50000]">{{ $jumlahBelumAbsen }}</p>
        </div>
    </div>

    {{-- Pengajuan Izin Pending --}}
    @if ($izinPending->isNotEmpty())
        <div class="mb-6 overflow-hidden rounded-xl bg-white shadow-[inset_0_0_0_1px_#ebebeb]">
            <div class="border-b border-[#ebebeb] bg-gray-50 px-4 py-3">
                <p class="text-sm font-medium text-gray-900">Pengajuan Izin Menunggu Persetujuan</p>
            </div>
            <div class="divide-y divide-[#ebebeb]">
                @foreach ($izinPending as $izin)
                    <div class="px-4 py-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $izin->kolektor->name }}
                                    <span class="font-normal text-gray-500">
                                        &middot; {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->translatedFormat('d M Y') }}
                                        - {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->translatedFormat('d M Y') }}
                                    </span>
                                </p>
                                <p class="mt-0.5 text-sm text-gray-500">{{ $izin->alasan }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="prosesIzin({{ $izin->id }}, 'disetujui')"
                                        class="rounded-lg bg-[#0a7a3d] px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90">
                                    Setujui
                                </button>
                                <button wire:click="prosesIzin({{ $izin->id }}, 'ditolak')"
                                        class="rounded-lg bg-[#c50000] px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90">
                                    Tolak
                                </button>
                            </div>
                        </div>
                        <input type="text" wire:model="catatanIzin[{{ $izin->id }}]" placeholder="Catatan admin (opsional)"
                               class="mt-2 w-full rounded-lg border border-[#ebebeb] bg-white px-3 py-1.5 text-xs text-gray-900 focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-[inset_0_0_0_1px_#ebebeb]">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-[#ebebeb] bg-gray-50">
                    <th class="px-4 py-3 font-medium text-gray-900">Nama Kolektor</th>
                    <th class="px-4 py-3 font-medium text-gray-900">No. HP</th>
                    <th class="px-4 py-3 font-medium text-gray-900">Status</th>
                    <th class="px-4 py-3 font-medium text-gray-900">Bukti</th>
                    <th class="px-4 py-3 font-medium text-gray-900">Lokasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kolektors as $kolektor)
                    @php $absen = $absensi->get($kolektor->id); @endphp
                    <tr class="border-b border-[#ebebeb] last:border-b-0 {{ $selectedKolektorId === $kolektor->id ? 'bg-[#f0f7ff]' : '' }}">
                        <td class="px-4 py-3 text-gray-900">{{ $kolektor->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $kolektor->no_hp }}</td>
                        <td class="px-4 py-3">
                            @if($absen)
                                <span class="inline-flex items-center rounded-full bg-[#dcf5e3] px-2.5 py-0.5 text-xs font-medium text-[#0a7a3d]">
                                    Masuk {{ \Carbon\Carbon::parse($absen->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i') }}
                                </span>
                            @elseif($izinHariIni->contains($kolektor->id))
                                <span class="inline-flex items-center rounded-full bg-[#fef3c7] px-2.5 py-0.5 text-xs font-medium text-[#b45309]">
                                    Sedang Izin
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2.5 py-0.5 text-xs font-medium text-[#c50000]">
                                    Belum absen
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($absen)
                                <button wire:click="selectKolektor({{ $kolektor->id }})"
                                        class="text-xs text-indigo-600 hover:underline">
                                    {{ $selectedKolektorId === $kolektor->id ? 'Tutup' : 'Lihat Bukti' }}
                                </button>
                            @else
                                <span class="text-xs text-[#a1a1a1]">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($absen && $absen->latitude && $absen->longitude)
                                <a href="https://www.google.com/maps?q={{ $absen->latitude }},{{ $absen->longitude }}" target="_blank" rel="noopener noreferrer"
                                   class="text-xs text-indigo-600 hover:underline">
                                    {{ round($absen->latitude, 5) }}, {{ round($absen->longitude, 5) }}
                                </a>
                            @else
                                <span class="text-xs text-[#a1a1a1]">-</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Detail Row --}}
                    @if($selectedKolektorId === $kolektor->id && $selectedAbsen)
                        <tr class="border-b border-[#ebebeb]">
                            <td colspan="5" class="px-4 py-4 bg-[#f8f9fa]">
                                <div class="grid gap-4 sm:grid-cols-3">
                                    {{-- Foto Selfie --}}
                                    <div>
                                        <p class="mb-2 text-xs font-semibold text-gray-900">Foto Selfie</p>
                                        @if($selectedAbsen->foto_selfie_path)
                                            <img src="{{ route('absensi.foto', [$selectedAbsen, 'selfie']) }}"
                                                 alt="Selfie {{ $kolektor->name }}"
                                                 class="h-40 w-40 rounded-xl object-cover shadow-[inset_0_0_0_1px_#ebebeb]" />
                                        @else
                                            <div class="flex h-40 w-40 items-center justify-center rounded-xl bg-[#e5e5e5] text-xs text-gray-500">
                                                Tidak ada selfie
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Tanda Tangan --}}
                                    <div>
                                        <p class="mb-2 text-xs font-semibold text-gray-900">Tanda Tangan</p>
                                        @if($selectedAbsen->tanda_tangan_path)
                                            <img src="{{ route('absensi.foto', [$selectedAbsen, 'tanda-tangan']) }}"
                                                 alt="Tanda Tangan {{ $kolektor->name }}"
                                                 class="h-40 w-40 rounded-xl bg-white object-contain p-2 shadow-[inset_0_0_0_1px_#ebebeb]" />
                                        @else
                                            <div class="flex h-40 w-40 items-center justify-center rounded-xl bg-[#e5e5e5] text-xs text-gray-500">
                                                Tidak ada tanda tangan
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Info Lain --}}
                                    <div>
                                        <p class="mb-2 text-xs font-semibold text-gray-900">Detail Absensi</p>
                                        <div class="space-y-2 rounded-xl bg-white p-3 shadow-[inset_0_0_0_1px_#ebebeb]">
                                            <div>
                                                <p class="text-xs text-gray-500">Waktu Masuk</p>
                                                <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($selectedAbsen->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i:s') }} WIB</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Tanggal</p>
                                                <p class="text-sm font-medium text-gray-900">{{ $selectedAbsen->tanggal->translatedFormat('d M Y') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Koordinat</p>
                                                <p class="text-sm font-medium text-gray-900">{{ round($selectedAbsen->latitude, 6) }}, {{ round($selectedAbsen->longitude, 6) }}</p>
                                            </div>
                                            <a href="https://www.google.com/maps?q={{ $selectedAbsen->latitude }},{{ $selectedAbsen->longitude }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:underline">
                                                Buka di Google Maps
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data kolektor.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
