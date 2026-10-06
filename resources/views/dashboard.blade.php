@if(auth()->user()->isAdmin())
<x-layouts::admin :title="__('Dashboard')">
    @include('livewire.admin.dashboard')
</x-layouts::admin>

@else
<x-layouts::mobile :title="__('Dashboard')">
    <div class="flex flex-col gap-5 pb-8">
        @if(auth()->user()->isKolektor())
            {{-- ==================== KOLEKTOR DASHBOARD ==================== --}}
            
            {{-- 1. Header & Absen Status --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-zinc-900 text-white font-bold text-sm shadow-sm dark:bg-white dark:text-zinc-900 ring-1 ring-zinc-200 dark:ring-zinc-700">
                        {{ $user->initials() }}
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Petugas Kolektor</span>
                        <h1 class="text-base font-bold tracking-tight text-zinc-900 dark:text-white leading-tight">
                            {{ $user->name }}
                        </h1>
                    </div>
                </div>

                @if($sudahAbsenHariIni)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 dark:bg-emerald-500/20 px-3 py-1 text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 border border-emerald-500/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Sudah Absen
                    </span>
                @else
                    <a href="{{ route('kolektor.absen.index') }}" wire:navigate
                       class="inline-flex items-center gap-1.5 rounded-full bg-rose-500/10 dark:bg-rose-500/20 px-3 py-1 text-[11px] font-semibold text-rose-700 dark:text-rose-300 border border-rose-500/20 transition hover:bg-rose-500/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500 animate-ping"></span>
                        Belum Absen
                    </a>
                @endif
            </div>

            {{-- 2. Hero Card: Kas di Tangan --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-900 via-zinc-800 to-black p-5 text-white shadow-xl border border-zinc-800">
                <div class="absolute -right-10 -bottom-10 h-40 w-40 rounded-full bg-blue-500/10 blur-3xl pointer-events-none"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-semibold uppercase tracking-widest text-zinc-400">Kas di Tangan</span>
                        <span class="rounded-full bg-white/10 backdrop-blur-md px-2.5 py-0.5 text-[10px] font-medium text-zinc-300 border border-white/10">
                            Hari Ini
                        </span>
                    </div>
                    <p class="font-mono text-3xl font-bold tracking-tight text-white mb-4">
                        Rp {{ number_format($stats['setoran_belum_disetor'], 0, ',', '.') }}
                    </p>

                    <div class="grid grid-cols-2 gap-2.5">
                        <a href="{{ route('kolektor.setoran.index') }}" wire:navigate
                           class="flex items-center justify-center gap-2 rounded-xl bg-white py-2.5 text-xs font-semibold text-zinc-900 hover:bg-zinc-100 active:scale-95 transition shadow-xs">
                            <flux:icon.plus-circle class="size-4 text-zinc-900" />
                            Setor Nasabah
                        </a>
                        <a href="{{ route('kolektor.setor-kantor.index') }}" wire:navigate
                           class="flex items-center justify-center gap-2 rounded-xl bg-white/10 hover:bg-white/15 backdrop-blur-md py-2.5 text-xs font-semibold text-white active:scale-95 transition border border-white/15">
                            <flux:icon.building-office-2 class="size-4 text-white" />
                            Setor ke Kantor
                        </a>
                    </div>
                </div>
            </div>

            {{-- 3. Metric Cards Grid --}}
            <div class="grid grid-cols-3 gap-2.5">
                <div class="rounded-2xl bg-white p-3.5 shadow-xs border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Kunjungan</span>
                    <p class="mt-1 font-mono text-base font-bold text-emerald-600 dark:text-emerald-400">
                        {{ $stats['kunjungan_selesai'] }}<span class="text-xs font-sans font-normal text-zinc-400 dark:text-zinc-500">/{{ $stats['kunjungan_total'] }}</span>
                    </p>
                    <div class="mt-2 h-1.5 w-full rounded-full bg-zinc-100 dark:bg-zinc-700 overflow-hidden">
                        @php
                            $pct = $stats['kunjungan_total'] > 0 ? round(($stats['kunjungan_selesai'] / $stats['kunjungan_total']) * 100) : 0;
                        @endphp
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $pct }}%"></div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-3.5 shadow-xs border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Binaan</span>
                    <p class="mt-1 font-mono text-base font-bold text-blue-600 dark:text-blue-400">
                        {{ $totalNasabahBinaan ?? 0 }} <span class="text-[10px] font-sans font-normal text-zinc-400 dark:text-zinc-500">Nasabah</span>
                    </p>
                    <p class="mt-1 text-[10px] text-zinc-400 dark:text-zinc-500 truncate">Status aktif</p>
                </div>

                <div class="rounded-2xl bg-white p-3.5 shadow-xs border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Tunggakan</span>
                    <p class="mt-1 font-mono text-base font-bold text-amber-600 dark:text-amber-400">
                        {{ $nasabahTunggakParah }} <span class="text-[10px] font-sans font-normal text-zinc-400 dark:text-zinc-500">Nasabah</span>
                    </p>
                    <p class="mt-1 text-[10px] text-amber-600 dark:text-amber-400 font-medium truncate">Perlu penagihan</p>
                </div>
            </div>

            {{-- 4. Menu Shortcuts --}}
            <div class="rounded-2xl bg-white p-4 shadow-xs border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
                <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-3">Menu Utama</span>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <a href="{{ route('kolektor.setoran.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="h-11 w-11 rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 flex items-center justify-center group-hover:scale-105 transition shadow-2xs border border-blue-100 dark:border-blue-900/50">
                            <flux:icon.arrow-down-tray class="size-5" />
                        </div>
                        <span class="mt-1.5 text-[11px] font-medium text-zinc-700 dark:text-zinc-300">Setor</span>
                    </a>
                    <a href="{{ route('kolektor.setor-kantor.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="h-11 w-11 rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 flex items-center justify-center group-hover:scale-105 transition shadow-2xs border border-amber-100 dark:border-amber-900/50">
                            <flux:icon.building-office class="size-5" />
                        </div>
                        <span class="mt-1.5 text-[11px] font-medium text-zinc-700 dark:text-zinc-300">Kantor</span>
                    </a>
                    <a href="{{ route('kolektor.verifikasi-penarikan.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="relative h-11 w-11 rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center group-hover:scale-105 transition shadow-2xs border border-emerald-100 dark:border-emerald-900/50">
                            <flux:icon.check-badge class="size-5" />
                            @if($penarikanMenungguDiantar > 0)
                                <span class="absolute -top-1.5 -right-1.5 min-w-[18px] rounded-full bg-rose-500 px-1 text-center text-[9px] font-bold leading-[18px] text-white shadow">{{ $penarikanMenungguDiantar }}</span>
                            @endif
                        </div>
                        <span class="mt-1.5 text-[11px] font-medium text-zinc-700 dark:text-zinc-300">Verifikasi</span>
                        @if($penarikanMenungguDiantar > 0)
                            <span class="text-[9px] font-semibold text-rose-600 dark:text-rose-400 leading-tight">menunggu diantar</span>
                        @endif
                    </a>
                    <a href="{{ route('kolektor.jadwal.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="h-11 w-11 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 flex items-center justify-center group-hover:scale-105 transition shadow-2xs border border-indigo-100 dark:border-indigo-900/50">
                            <flux:icon.calendar-days class="size-5" />
                        </div>
                        <span class="mt-1.5 text-[11px] font-medium text-zinc-700 dark:text-zinc-300">Jadwal</span>
                    </a>
                    <a href="{{ route('kolektor.nasabah.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="h-11 w-11 rounded-2xl bg-purple-50 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400 flex items-center justify-center group-hover:scale-105 transition shadow-2xs border border-purple-100 dark:border-purple-900/50">
                            <flux:icon.users class="size-5" />
                        </div>
                        <span class="mt-1.5 text-[11px] font-medium text-zinc-700 dark:text-zinc-300">Nasabah</span>
                    </a>
                    <a href="{{ route('serah-terima.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="h-11 w-11 rounded-2xl bg-teal-50 text-teal-600 dark:bg-teal-950/60 dark:text-teal-400 flex items-center justify-center group-hover:scale-105 transition shadow-2xs border border-teal-100 dark:border-teal-900/50">
                            <flux:icon.hand-raised class="size-5" />
                        </div>
                        <span class="mt-1.5 text-[11px] font-medium text-zinc-700 dark:text-zinc-300">Serah Terima</span>
                    </a>
                </div>
            </div>

            {{-- 5. Rute Kunjungan --}}
            @if($jadwalHariIni->count() > 0)
                <div class="rounded-2xl bg-white p-4 shadow-xs border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
                    <div class="mb-3.5 flex items-center justify-between pb-2.5 border-b border-zinc-100 dark:border-zinc-700/60">
                        <div class="flex items-center gap-2">
                            <flux:icon.map-pin class="size-4 text-blue-600 dark:text-blue-400" />
                            <h3 class="text-xs font-bold text-zinc-900 dark:text-white uppercase tracking-wider">Rute Kunjungan Hari Ini</h3>
                        </div>
                        <a href="{{ route('kolektor.jadwal.index') }}" wire:navigate class="text-[11px] font-semibold text-blue-600 hover:underline dark:text-blue-400">
                            Lihat Semua
                        </a>
                    </div>

                    <div class="relative pl-6 space-y-3.5">
                        <div class="absolute bottom-2 left-[9px] top-2 w-0.5 bg-zinc-200 dark:bg-zinc-700"></div>
                        @php
                            $firstUndone = $jadwalHariIni->firstWhere('status_kunjungan', '!=', 'dikunjungi');
                        @endphp
                        @foreach($jadwalHariIni as $jadwal)
                            <div class="relative flex items-center justify-between gap-2">
                                @if($jadwal->status_kunjungan === 'dikunjungi')
                                    <div class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[8px] font-bold">✓</div>
                                    <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500 line-through truncate">{{ $jadwal->nasabah_name }}</p>
                                    <span class="rounded-md bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:text-emerald-300 shrink-0">Selesai</span>
                                @elseif($firstUndone && $jadwal->id === $firstUndone->id)
                                    <div class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full bg-blue-600 ring-4 ring-blue-100 dark:ring-blue-900/50"></div>
                                    <p class="text-xs font-bold text-zinc-900 dark:text-white truncate">{{ $jadwal->nasabah_name }}</p>
                                    <a href="{{ route('kolektor.setoran.index') }}" wire:navigate class="rounded-lg bg-blue-600 px-2.5 py-1 text-[10px] font-bold text-white hover:bg-blue-700 transition shrink-0">Setor</a>
                                @else
                                    <div class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full border-2 border-zinc-300 bg-white dark:border-zinc-600 dark:bg-zinc-800"></div>
                                    <p class="text-xs text-zinc-600 dark:text-zinc-400 truncate">{{ $jadwal->nasabah_name }}</p>
                                    <span class="text-[10px] text-zinc-400 shrink-0">Antrean</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        @else
            {{-- ==================== NASABAH DASHBOARD ==================== --}}
            
            {{-- 1. Header & Bell Notification --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if($user->foto_profil_path)
                        <img src="{{ asset('storage/'.$user->foto_profil_path) }}" alt="Foto Profil"
                             class="h-11 w-11 rounded-2xl object-cover shadow-xs border border-zinc-200 dark:border-zinc-700" />
                    @else
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white font-bold text-sm shadow-xs">
                            {{ $user->initials() }}
                        </div>
                    @endif
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Nasabah Tabungan</span>
                        <h1 class="text-base font-bold tracking-tight text-zinc-900 dark:text-white leading-tight">
                            {{ $user->name }}
                        </h1>
                    </div>
                </div>

                <a href="{{ route('nasabah.notifikasi.index') }}" wire:navigate
                   class="relative flex h-10 w-10 items-center justify-center rounded-2xl bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 shadow-xs border border-zinc-200/80 dark:border-zinc-700 transition hover:bg-zinc-50 dark:hover:bg-zinc-700">
                    <flux:icon.bell class="size-5" />
                    @if($unreadNotifikasi)
                        <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-500 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-rose-500"></span>
                        </span>
                    @endif
                </a>
            </div>

            {{-- 2. Hero Balance Card (Gradient) --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-blue-600 to-indigo-900 p-5 text-white shadow-xl border border-blue-500/20">
                <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-semibold text-blue-100 uppercase tracking-widest">Total Saldo Tabungan</span>
                        <span class="rounded-full bg-white/15 backdrop-blur-md px-2.5 py-0.5 text-[10px] font-medium text-white border border-white/15">
                            Aktif
                        </span>
                    </div>
                    <p class="font-mono text-3xl font-bold tracking-tight text-white mb-3">
                        Rp {{ number_format($totalSaldo, 0, ',', '.') }}
                    </p>

                    @if($saldoPerProduk->count() > 0)
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @foreach($saldoPerProduk as $sp)
                                <span class="rounded-lg bg-white/15 backdrop-blur-md px-2.5 py-1 text-[11px] font-medium text-white border border-white/15">
                                    {{ $sp->produk?->nama ?? '-' }} &middot; <strong>Rp {{ number_format($sp->saldo, 0, ',', '.') }}</strong>
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- 3. Streak & Countdown Paket Grid --}}
            <div class="grid grid-cols-2 gap-3">
                {{-- Streak Nabung --}}
                <div class="rounded-2xl bg-white p-4 shadow-xs border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 shrink-0 border border-amber-100 dark:border-amber-900/50">
                            <flux:icon.fire class="size-5" />
                        </div>
                        <div>
                            <p class="text-lg font-bold text-zinc-900 dark:text-white leading-none font-mono">{{ $streak }} Hari</p>
                            <p class="text-[10px] font-medium uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mt-1">Streak Nabung</p>
                        </div>
                    </div>
                </div>

                {{-- Countdown Paket Lebaran --}}
                @if($kepesertaanAktif?->produk)
                    @php
                        $tanggalCair = \Carbon\Carbon::parse($kepesertaanAktif->produk->tanggal_boleh_cair)->startOfDay();
                        $hariLagi = (int) now()->startOfDay()->diffInDays($tanggalCair, false);
                    @endphp
                    <a href="{{ route('nasabah.progres-paket.index') }}" wire:navigate
                       class="rounded-2xl bg-amber-50 dark:bg-amber-950/40 p-4 border border-amber-200 dark:border-amber-800/60 block hover:border-amber-300 transition">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 block truncate">
                            {{ $kepesertaanAktif->produk->nama }}
                        </span>
                        <p class="text-sm font-bold text-amber-900 dark:text-amber-200 mt-0.5">
                            @if($hariLagi > 0)
                                {{ $hariLagi }} Hari Lagi
                            @else
                                Sudah Bisa Dicairkan
                            @endif
                        </p>
                    </a>
                @else
                    <a href="{{ route('nasabah.progres-paket.index') }}" wire:navigate
                       class="rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 p-3.5 border border-indigo-200 dark:border-indigo-800/60 flex items-center justify-between hover:bg-indigo-100/50 transition">
                        <div>
                            <p class="text-xs font-bold text-indigo-900 dark:text-indigo-200">Paket Lebaran</p>
                            <p class="text-[10px] text-indigo-700 dark:text-indigo-400 mt-0.5">Gabung Paket Sekarang</p>
                        </div>
                        <flux:icon.arrow-right class="size-4 text-indigo-600 dark:text-indigo-400 shrink-0" />
                    </a>
                @endif
            </div>

            {{-- 4. Quick Actions Grid --}}
            <div class="rounded-2xl bg-white p-4 shadow-xs border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
                <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-3">Aksi Cepat</span>
                <div class="grid grid-cols-4 gap-2 text-center">
                    <a href="{{ route('nasabah.penarikan.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="h-11 w-11 rounded-2xl bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 flex items-center justify-center group-hover:scale-105 transition shadow-2xs border border-rose-100 dark:border-rose-900/50">
                            <flux:icon.arrow-up-tray class="size-5" />
                        </div>
                        <span class="mt-1.5 text-[11px] font-medium text-zinc-700 dark:text-zinc-300">Tarik</span>
                    </a>
                    <a href="{{ route('nasabah.riwayat-tabungan.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="h-11 w-11 rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 flex items-center justify-center group-hover:scale-105 transition shadow-2xs border border-blue-100 dark:border-blue-900/50">
                            <flux:icon.clock class="size-5" />
                        </div>
                        <span class="mt-1.5 text-[11px] font-medium text-zinc-700 dark:text-zinc-300">Riwayat</span>
                    </a>
                    <a href="{{ route('nasabah.progres-paket.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="h-11 w-11 rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 flex items-center justify-center group-hover:scale-105 transition shadow-2xs border border-emerald-100 dark:border-emerald-900/50">
                            <flux:icon.shopping-bag class="size-5" />
                        </div>
                        <span class="mt-1.5 text-[11px] font-medium text-zinc-700 dark:text-zinc-300">Paket</span>
                    </a>
                    <a href="{{ route('nasabah.komplain.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="h-11 w-11 rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 flex items-center justify-center group-hover:scale-105 transition shadow-2xs border border-amber-100 dark:border-amber-900/50">
                            <flux:icon.chat-bubble-left-right class="size-5" />
                        </div>
                        <span class="mt-1.5 text-[11px] font-medium text-zinc-700 dark:text-zinc-300">Komplain</span>
                    </a>
                </div>
            </div>

            {{-- 4b. Pengajuan Penarikan Aktif (D18) --}}
            <livewire:nasabah.pengajuan-aktif />

            {{-- 5. Riwayat Terbaru --}}
            @if($riwayatGabungan->count() > 0)
                <div class="rounded-2xl bg-white p-4 shadow-xs border border-zinc-200/80 dark:bg-zinc-800/90 dark:border-zinc-700/80">
                    <div class="mb-3 flex items-center justify-between pb-2.5 border-b border-zinc-100 dark:border-zinc-700/60">
                        <h3 class="text-xs font-bold text-zinc-900 dark:text-white uppercase tracking-wider">Transaksi Terbaru</h3>
                        <a href="{{ route('nasabah.riwayat-tabungan.index') }}" wire:navigate class="text-[11px] font-semibold text-blue-600 hover:underline dark:text-blue-400">
                            Lihat Semua
                        </a>
                    </div>
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-700/50">
                        @foreach($riwayatGabungan as $trx)
                            <div class="py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl shrink-0 {{ $trx['type'] === 'setoran' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-100 dark:border-rose-900/40' }}">
                                        @if($trx['type'] === 'setoran')
                                            <flux:icon.arrow-down-left class="size-4" />
                                        @else
                                            <flux:icon.arrow-up-right class="size-4" />
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-zinc-900 dark:text-white truncate">{{ $trx['nama'] }}</p>
                                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                            {{ $trx['tanggal']->isToday() ? 'Hari ini, '.$trx['tanggal']->format('H:i') : $trx['tanggal']->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                                <span class="font-mono text-xs font-bold shrink-0 {{ $trx['type'] === 'setoran' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $trx['type'] === 'setoran' ? '+' : '-' }}Rp {{ number_format($trx['nominal'], 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-layouts::mobile>
@endif
