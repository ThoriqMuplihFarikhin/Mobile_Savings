<div class="mx-auto max-w-4xl">
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Monitoring Absensi</h1>
        <p class="mt-1 text-sm text-[#888888]">Lihat kehadiran kolektor harian.</p>
    </div>

    {{-- Date Picker --}}
    <div class="mb-6">
        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Tanggal</label>
        <input type="date" wire:model.live="tanggal"
               class="h-10 w-full max-w-xs rounded-lg border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
            <p class="text-sm text-[#888888]">Total Kolektor</p>
            <p class="mt-1 text-2xl font-bold text-[#171717]">{{ $kolektors->count() }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
            <p class="text-sm text-[#888888]">Sudah Absen</p>
            <p class="mt-1 text-2xl font-bold text-[#0a7a3d]">{{ $absensi->count() }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
            <p class="text-sm text-[#888888]">Belum Absen</p>
            <p class="mt-1 text-2xl font-bold text-[#c50000]">{{ $kolektors->count() - $absensi->count() }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-[inset_0_0_0_1px_#ebebeb]">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-[#ebebeb] bg-[#fafafa]">
                    <th class="px-4 py-3 font-medium text-[#171717]">Nama Kolektor</th>
                    <th class="px-4 py-3 font-medium text-[#171717]">No. HP</th>
                    <th class="px-4 py-3 font-medium text-[#171717]">Status</th>
                    <th class="px-4 py-3 font-medium text-[#171717]">Bukti</th>
                    <th class="px-4 py-3 font-medium text-[#171717]">Lokasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kolektors as $kolektor)
                    @php $absen = $absensi->get($kolektor->id); @endphp
                    <tr class="border-b border-[#ebebeb] last:border-b-0 {{ $selectedKolektorId === $kolektor->id ? 'bg-[#f0f7ff]' : '' }}">
                        <td class="px-4 py-3 text-[#171717]">{{ $kolektor->name }}</td>
                        <td class="px-4 py-3 text-[#888888]">{{ $kolektor->no_hp }}</td>
                        <td class="px-4 py-3">
                            @if($absen)
                                <span class="inline-flex items-center rounded-full bg-[#dcf5e3] px-2.5 py-0.5 text-xs font-medium text-[#0a7a3d]">
                                    Masuk {{ \Carbon\Carbon::parse($absen->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i') }}
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
                                        class="text-xs text-[#0761d1] hover:underline">
                                    {{ $selectedKolektorId === $kolektor->id ? 'Tutup' : 'Lihat Bukti' }}
                                </button>
                            @else
                                <span class="text-xs text-[#a1a1a1]">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($absen && $absen->latitude && $absen->longitude)
                                <a href="https://www.google.com/maps?q={{ $absen->latitude }},{{ $absen->longitude }}" target="_blank" rel="noopener noreferrer"
                                   class="text-xs text-[#0761d1] hover:underline">
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
                                        <p class="mb-2 text-xs font-semibold text-[#171717]">Foto Selfie</p>
                                        @if($selectedAbsen->foto_selfie_path)
                                            <img src="{{ Storage::url($selectedAbsen->foto_selfie_path) }}"
                                                 alt="Selfie {{ $kolektor->name }}"
                                                 class="h-40 w-40 rounded-xl object-cover shadow-[inset_0_0_0_1px_#ebebeb]" />
                                        @else
                                            <div class="flex h-40 w-40 items-center justify-center rounded-xl bg-[#e5e5e5] text-xs text-[#888888]">
                                                Tidak ada selfie
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Tanda Tangan --}}
                                    <div>
                                        <p class="mb-2 text-xs font-semibold text-[#171717]">Tanda Tangan</p>
                                        @if($selectedAbsen->tanda_tangan_base64)
                                            <img src="{{ $selectedAbsen->tanda_tangan_base64 }}"
                                                 alt="Tanda Tangan {{ $kolektor->name }}"
                                                 class="h-40 w-40 rounded-xl bg-white object-contain p-2 shadow-[inset_0_0_0_1px_#ebebeb]" />
                                        @else
                                            <div class="flex h-40 w-40 items-center justify-center rounded-xl bg-[#e5e5e5] text-xs text-[#888888]">
                                                Tidak ada tanda tangan
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Info Lain --}}
                                    <div>
                                        <p class="mb-2 text-xs font-semibold text-[#171717]">Detail Absensi</p>
                                        <div class="space-y-2 rounded-xl bg-white p-3 shadow-[inset_0_0_0_1px_#ebebeb]">
                                            <div>
                                                <p class="text-xs text-[#888888]">Waktu Masuk</p>
                                                <p class="text-sm font-medium text-[#171717]">{{ \Carbon\Carbon::parse($selectedAbsen->waktu_masuk)->setTimezone('Asia/Jakarta')->format('H:i:s') }} WIB</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-[#888888]">Tanggal</p>
                                                <p class="text-sm font-medium text-[#171717]">{{ $selectedAbsen->tanggal->translatedFormat('d M Y') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-[#888888]">Koordinat</p>
                                                <p class="text-sm font-medium text-[#171717]">{{ round($selectedAbsen->latitude, 6) }}, {{ round($selectedAbsen->longitude, 6) }}</p>
                                            </div>
                                            <a href="https://www.google.com/maps?q={{ $selectedAbsen->latitude }},{{ $selectedAbsen->longitude }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1 text-xs text-[#0761d1] hover:underline">
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
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-[#888888]">Tidak ada data kolektor.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
