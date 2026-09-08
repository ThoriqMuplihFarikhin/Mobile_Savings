@if(auth()->user()->isAdmin())
<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4">
        {{-- ADMIN DASHBOARD --}}
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Selamat Datang, {{ $user->name }}</h1>
            <p class="mt-1 text-sm text-[#888888]">Panel Admin - Sistem Tabungan Digital</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl bg-[#fafafa] p-6 shadow-[inset_0_0_0_1px_#ebebeb]">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#d3e5ff] text-[#0070f3]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-mono text-xs uppercase tracking-wider text-[#888888]">Total Nasabah</p>
                        <p class="font-mono text-2xl font-semibold text-[#171717]">{{ number_format($stats['total_nasabah']) }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-[#fafafa] p-6 shadow-[inset_0_0_0_1px_#ebebeb]">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#ffefcf] text-[#ab570a]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-mono text-xs uppercase tracking-wider text-[#888888]">Total Kolektor</p>
                        <p class="font-mono text-2xl font-semibold text-[#171717]">{{ number_format($stats['total_kolektor']) }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-[#fafafa] p-6 shadow-[inset_0_0_0_1px_#ebebeb]">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#d3e5ff] text-[#0070f3]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-mono text-xs uppercase tracking-wider text-[#888888]">Total Saldo</p>
                        <p class="font-mono text-2xl font-semibold text-[#171717]">Rp {{ number_format($stats['total_saldo_semua'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-[#ffefcf] p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-[#ab570a] shadow-[inset_0_0_0_1px_#ebebeb]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-mono text-xs uppercase tracking-wider text-[#ab570a]">Nasabah Pending</p>
                        <p class="font-mono text-xl font-semibold text-[#ab570a]">{{ $stats['nasabah_pending'] }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-[#d8ccf1] p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-[#4c2889] shadow-[inset_0_0_0_1px_#ebebeb]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-mono text-xs uppercase tracking-wider text-[#4c2889]">Penarikan Pending</p>
                        <p class="font-mono text-xl font-semibold text-[#4c2889]">{{ $stats['penarikan_pending'] }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-[#d3e5ff] p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-[#0761d1] shadow-[inset_0_0_0_1px_#ebebeb]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-mono text-xs uppercase tracking-wider text-[#0761d1]">Setoran Hari Ini</p>
                        <p class="font-mono text-xl font-semibold text-[#0761d1]">Rp {{ number_format($stats['total_setoran_hari'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-[#d3e5ff] p-4 shadow-[inset_0_0_0_1px_#ebebeb]">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-[#0070f3] shadow-[inset_0_0_0_1px_#ebebeb]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-mono text-xs uppercase tracking-wider text-[#0070f3]">Kolektor Aktif</p>
                        <p class="font-mono text-xl font-semibold text-[#0070f3]">{{ $stats['total_kolektor'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>

@else
<x-layouts::mobile :title="__('Dashboard')">
    <div class="flex flex-col gap-6">
        @if(auth()->user()->isKolektor())
            {{-- KOLEKTOR DASHBOARD --}}
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Kolektor, {{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-[#888888]">Panel Kolektor</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-[#dcf5e3] px-3 py-1 text-xs font-medium text-[#0a7a3d]">Aktif</span>
            </div>

            {{-- Kartu kas di tangan --}}
            <div class="rounded-2xl bg-[#171717] p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-white/60">Kas di Tangan</p>
                        <p class="mt-1 font-mono text-3xl font-semibold">Rp {{ number_format($stats['setoran_belum_disetor'], 0, ',', '.') }}</p>
                    </div>
                    <a href="{{ route('kolektor.setor-kantor.index') }}" wire:navigate
                       class="inline-flex items-center gap-1.5 rounded-full bg-white/20 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-white/30">
                        <flux:icon.banknote class="size-4" />
                        Setor ke kantor
                    </a>
                </div>
            </div>

            {{-- Dua kartu statistik --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-xl bg-[#dcf5e3] p-4">
                    <p class="text-xs font-medium text-[#0a7a3d]">Kunjungan Hari Ini</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-[#0a7a3d]">{{ $stats['jadwal_hari'] }}</p>
                </div>
                <div class="rounded-xl bg-[#f7d4d6] p-4">
                    <p class="text-xs font-medium text-[#c50000]">Nasabah Tunggak</p>
                    <p class="mt-1 font-mono text-2xl font-semibold text-[#c50000]">{{ $nasabahTunggak }}</p>
                </div>
            </div>

            {{-- List jadwal kunjungan hari ini --}}
            @if($jadwalHariIni->count() > 0)
                <div class="rounded-xl bg-white p-5 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-[#171717]">Jadwal Kunjungan</h3>
                        <a href="{{ route('kolektor.jadwal.index') }}" wire:navigate class="text-xs font-medium text-[#0761d1] hover:underline">Lihat semua</a>
                    </div>
                    <div class="space-y-3">
                        @foreach($jadwalHariIni as $jadwal)
                            <div class="flex items-center justify-between rounded-xl bg-[#fafafa] p-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#171717] text-xs font-semibold text-white">
                                        {{ \Illuminate\Support\Str::initials($jadwal->nasabah_name, 2) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-[#171717]">{{ $jadwal->nasabah_name }}</p>
                                        <p class="text-xs text-[#888888]">{{ $jadwal->produk_name ?? '-' }}</p>
                                    </div>
                                </div>
                                @if($jadwal->status_alert && in_array($jadwal->status_alert, ['peringatan', 'perlu_review']))
                                    <span class="inline-flex items-center rounded-full bg-[#f7d4d6] px-2 py-0.5 text-[10px] font-medium text-[#c50000]">
                                        Tunggakan {{ $jadwal->tunggakan }} hari
                                    </span>
                                @elseif($jadwal->status_kunjungan === 'dikunjungi')
                                    <span class="inline-flex items-center rounded-full bg-[#dcf5e3] px-2 py-0.5 text-[10px] font-medium text-[#0a7a3d]">Selesai</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-[#ffefcf] px-2 py-0.5 text-[10px] font-medium text-[#ab570a]">Belum</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        @else
            {{-- NASABAH DASHBOARD --}}
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Halo, {{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-[#888888]">Tabungan Digital Anda</p>
                </div>
                <a href="{{ route('nasabah.notifikasi.index') }}" wire:navigate class="relative flex h-10 w-10 items-center justify-center rounded-full bg-[#fafafa] text-[#171717] shadow-[inset_0_0_0_1px_#ebebeb]">
                    <flux:icon.bell class="size-5" />
                    @if($unreadNotifikasi > 0)
                        <span class="absolute -top-0.5 -right-0.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-[#ee0000] px-1 text-[9px] font-bold text-white">{{ $unreadNotifikasi > 99 ? '99+' : $unreadNotifikasi }}</span>
                    @endif
                </a>
            </div>

            {{-- Kartu saldo utama --}}
            <div class="rounded-2xl bg-[#171717] p-6 text-white shadow-lg">
                <p class="text-xs uppercase tracking-wider text-white/60">Total Saldo</p>
                <p class="mt-1 font-mono text-3xl font-semibold">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</p>
                @if($saldo->count() > 0)
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($saldo as $s)
                            <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2.5 py-0.5 text-[11px] font-medium text-white">
                                {{ $s->produk->nama }} &middot; Rp {{ number_format($s->saldo, 0, ',', '.') }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Kartu progres paket --}}
            @if($kepesertaanPaket)
                @php
                    $totalHari = $kepesertaanPaket->produk->periode_selesai
                        ? $kepesertaanPaket->produk->periode_mulai->diffInDays($kepesertaanPaket->produk->periode_selesai)
                        : 30;
                    $hariBerjalan = $kepesertaanPaket->tanggal_mulai_ikut->diffInDays(now());
                    $persen = min(100, round(($hariBerjalan / $totalHari) * 100));
                @endphp
                <div class="rounded-2xl bg-[#ffefcf] p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-[#ab570a]">Progres {{ $kepesertaanPaket->produk->nama }}</p>
                        <span class="text-xs text-[#ab570a]">{{ $hariBerjalan }}/{{ $totalHari }} hari</span>
                    </div>
                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-[#e8a54a]/30">
                        <div class="h-full rounded-full bg-[#ab570a]" style="width: {{ $persen }}%"></div>
                    </div>
                    @if($kepesertaanPaket->tunggakan > 0)
                        <p class="mt-2 text-xs font-medium text-[#c50000]">Tunggakan {{ $kepesertaanPaket->tunggakan }} hari &middot; Rp {{ number_format($kepesertaanPaket->tunggakan * $kepesertaanPaket->produk->harga_per_hari, 0, ',', '.') }}</p>
                    @endif
                </div>
            @endif

            {{-- Dua tombol aksi cepat --}}
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('nasabah.penarikan.index') }}" wire:navigate
                   class="flex items-center justify-center gap-2 rounded-xl bg-[#171717] px-4 py-3 text-sm font-medium text-white transition hover:opacity-90">
                    <flux:icon.arrow-down-to-line class="size-4" />
                    Ajukan Tarik
                </a>
                <a href="{{ route('nasabah.komplain.index') }}" wire:navigate
                   class="flex items-center justify-center gap-2 rounded-xl bg-[#f7d4d6] px-4 py-3 text-sm font-medium text-[#c50000] transition hover:bg-[#f0c0c2]">
                    <flux:icon.megaphone class="size-4" />
                    Komplain
                </a>
            </div>

            {{-- Riwayat transaksi terbaru --}}
            @if($riwayatGabungan->count() > 0)
                <div class="rounded-xl bg-white p-5 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-[#171717]">Riwayat Transaksi</h3>
                        <a href="{{ route('nasabah.riwayat-tabungan.index') }}" wire:navigate class="text-xs font-medium text-[#0761d1] hover:underline">Lihat semua</a>
                    </div>
                    <div class="space-y-3">
                        @foreach($riwayatGabungan as $trx)
                            <div class="flex items-center justify-between rounded-lg bg-[#fafafa] p-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $trx['type'] === 'setoran' ? 'bg-[#dcf5e3] text-[#0a7a3d]' : 'bg-[#f7d4d6] text-[#c50000]' }}">
                                        @if($trx['type'] === 'setoran')
                                            <flux:icon.arrow-up class="size-4" />
                                        @else
                                            <flux:icon.arrow-down class="size-4" />
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-[#171717]">{{ $trx['nama'] }}</p>
                                        <p class="text-xs text-[#888888]">{{ $trx['tanggal']->isToday() ? 'Hari ini, '.$trx['tanggal']->format('H.i') : $trx['tanggal']->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <p class="font-mono text-sm font-semibold {{ $trx['type'] === 'setoran' ? 'text-[#0a7a3d]' : 'text-[#c50000]' }}">
                                    {{ $trx['type'] === 'setoran' ? '+' : '-' }} Rp {{ number_format($trx['nominal'], 0, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-layouts::mobile>
@endif
