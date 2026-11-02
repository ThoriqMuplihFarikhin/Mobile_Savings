@if(auth()->user()->isAdmin())
<x-layouts::admin :title="__('Dashboard')">
    @include('livewire.admin.dashboard')
</x-layouts::admin>

@else
<x-layouts::mobile :title="__('Dashboard')">
    <div class="flex flex-col gap-5 pb-8">
        @if(auth()->user()->isKolektor())
            {{-- ==================== KOLEKTOR DASHBOARD ==================== --}}
            @php
                $jamSekarang = (int) now()->hour;
                $sapaanWaktu = match (true) {
                    $jamSekarang >= 3 && $jamSekarang < 11 => 'Selamat pagi',
                    $jamSekarang >= 11 && $jamSekarang < 15 => 'Selamat siang',
                    $jamSekarang >= 15 && $jamSekarang < 19 => 'Selamat sore',
                    default => 'Selamat malam',
                };
            @endphp

            {{-- 1. Hero Header dengan Gelombang Dekoratif --}}
            <div class="relative -mx-4 -mt-4 overflow-hidden bg-primary-deep px-4 pb-16 pt-4 text-on-hero">
                {{-- Lingkaran dekoratif dan koin mengambang sesuai prototipe --}}
                <span class="pointer-events-none absolute -right-6 -top-4 size-28 rounded-full bg-white/5 animate-pulse" aria-hidden="true"></span>
                <span class="pointer-events-none absolute right-8 top-14 flex size-7 items-center justify-center rounded-full bg-decor-gold text-xs font-semibold text-ink shadow-xs animate-hero-float select-none" aria-hidden="true">Rp</span>
                <span class="pointer-events-none absolute right-20 top-10 flex size-5 items-center justify-center rounded-full bg-decor-gold text-xs font-semibold text-ink shadow-xs opacity-90 animate-hero-float-delayed select-none" aria-hidden="true">Rp</span>

                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex size-11 items-center justify-center rounded-full bg-info-soft text-sm font-semibold text-info">
                            {{ $user->initials() }}
                        </div>
                        <div>
                            <div class="text-xs text-on-hero-mute">{{ $sapaanWaktu }}</div>
                            <h1 class="text-base font-semibold leading-tight text-white">
                                {{ $user->name }}
                            </h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('kolektor.notifikasi.index') }}" wire:navigate
                           class="relative flex size-9 items-center justify-center rounded-full bg-white/15 text-white transition hover:bg-white/25 active:scale-95"
                           aria-label="Notifikasi">
                            <flux:icon.bell class="size-4" />
                            @if(!empty($unreadNotifikasiCount) && $unreadNotifikasiCount > 0)
                                <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-decor-gold px-1 text-xs font-semibold leading-none text-ink shadow-xs">
                                    {{ $unreadNotifikasiCount > 9 ? '9+' : $unreadNotifikasiCount }}
                                </span>
                            @endif
                        </a>

                        <button type="button" @click="$store.textSize.toggle()"
                                :aria-pressed="$store.textSize?.isBig"
                                class="relative flex size-9 items-center justify-center rounded-full bg-white/15 text-xs font-semibold text-white transition hover:bg-white/25 active:scale-95"
                                aria-label="Perbesar tulisan">
                            <span x-text="$store.textSize?.isBig ? 'A−' : 'A+'">A+</span>
                        </button>
                    </div>
                </div>

                {{-- Gelombang SVG transisi kanvas --}}
                <svg class="absolute -bottom-px left-0 h-7 w-full text-canvas-warm dark:text-dark-canvas" viewBox="0 0 400 28" preserveAspectRatio="none" fill="currentColor" aria-hidden="true">
                    <path d="M0 28V16C70 0 130 0 200 12s130 8 200-6v22z"/>
                </svg>
            </div>

            {{-- 2. Card Kas di Tangan (Overlap Hero) --}}
            <div class="relative -mt-10 rounded-[20px] border border-hairline bg-surface p-4 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-mute">Kas di Tangan hari ini</span>
                    @if(!empty($menungguAdminSetor) && $menungguAdminSetor)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-warning-soft px-2.5 py-0.5 text-xs font-medium text-warning-deep">
                            <span class="size-1.5 rounded-full bg-warning animate-pulse"></span>
                            Menunggu admin
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-soft px-2.5 py-0.5 text-xs font-medium text-on-primary-soft">
                            <span class="size-1.5 rounded-full bg-primary animate-pulse"></span>
                            Tersambung
                        </span>
                    @endif
                </div>

                <div class="my-2 text-[28px] font-semibold leading-tight tracking-tight text-ink tabular-nums">
                    <x-ui.rupiah :nilai="$stats['setoran_belum_disetor']" />
                </div>

                {{-- Cincin progres kunjungan --}}
                <div class="my-3 flex items-center gap-3.5">
                    <div class="relative size-16 shrink-0">
                        <svg viewBox="0 0 64 64" class="size-16 -rotate-90">
                            <circle class="stroke-hairline" cx="32" cy="32" r="26" fill="none" stroke-width="7" stroke-linecap="round"/>
                            <circle class="stroke-primary transition-all duration-700" cx="32" cy="32" r="26" fill="none" stroke-width="7" stroke-linecap="round"
                                stroke-dasharray="163.36" stroke-dashoffset="{{ 163.36 * (1 - ($persenKunjungan / 100)) }}"/>
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-xs font-semibold tabular-nums text-ink">
                            {{ $persenKunjungan }}%
                        </span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-ink">
                            {{ $stats['kunjungan_selesai'] }} dari {{ $stats['kunjungan_total'] }} kunjungan
                        </p>
                        <p class="mt-0.5 truncate text-xs text-mute">
                            @if($stats['kunjungan_total'] === 0)
                                Belum ada jadwal kunjungan hari ini
                            @elseif($stats['kunjungan_selesai'] >= $stats['kunjungan_total'])
                                Semua selesai. Saatnya setor ke kantor.
                            @else
                                Tinggal {{ $stats['kunjungan_total'] - $stats['kunjungan_selesai'] }} lagi, pasti selesai hari ini.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('kolektor.setor-kantor.index') }}" wire:navigate
                       aria-label="Setor kantor"
                       class="flex w-full items-center justify-center gap-2 rounded-full bg-primary py-3.5 text-sm font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-[0.98]">
                        <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 7a2 2 0 0 1 2-2h13v4M3 7v11a2 2 0 0 0 2 2h15V9H5a2 2 0 0 1-2-2zM16 14h2"/>
                        </svg>
                        Setor ke kantor
                    </a>
                </div>
            </div>

            {{-- 3. Ubin Menu Cepat (Quick Tiles 4x2) --}}
            <div class="grid grid-cols-4 gap-2">
                {{-- Tile 1: Jadwal (primary-soft) --}}
                <a href="{{ route('kolektor.jadwal.index') }}" wire:navigate class="group flex flex-col items-center gap-1.5 p-1">
                    <div class="flex size-14 items-center justify-center rounded-2xl bg-primary-soft text-on-primary-soft shadow-xs transition group-hover:scale-105 group-active:scale-95">
                        <flux:icon.calendar-days class="size-6" />
                    </div>
                    <span class="text-xs font-medium text-body">Jadwal</span>
                </a>

                {{-- Tile 2: Nasabah (info-soft) --}}
                <a href="{{ route('kolektor.nasabah.index') }}" wire:navigate class="group flex flex-col items-center gap-1.5 p-1">
                    <div class="flex size-14 items-center justify-center rounded-2xl bg-info-soft text-info shadow-xs transition group-hover:scale-105 group-active:scale-95">
                        <flux:icon.users class="size-6" />
                    </div>
                    <span class="text-xs font-medium text-body">Nasabah</span>
                </a>

                {{-- Tile 3: Absen (teal) --}}
                <a href="{{ route('kolektor.absen.index') }}" wire:navigate class="group flex flex-col items-center gap-1.5 p-1">
                    <div class="flex size-14 items-center justify-center rounded-2xl bg-tile-teal-bg text-tile-teal-fg shadow-xs transition group-hover:scale-105 group-active:scale-95">
                        <flux:icon.finger-print class="size-6" />
                    </div>
                    <span class="text-xs font-medium text-body">Absen</span>
                </a>

                {{-- Tile 4: Izin (warning-soft) --}}
                <a href="{{ route('kolektor.izin.index') }}" wire:navigate class="group flex flex-col items-center gap-1.5 p-1">
                    <div class="flex size-14 items-center justify-center rounded-2xl bg-warning-soft text-warning-deep shadow-xs transition group-hover:scale-105 group-active:scale-95">
                        <flux:icon.document-text class="size-6" />
                    </div>
                    <span class="text-xs font-medium text-body">Izin</span>
                </a>

                {{-- Tile 5: Daftar (primary-soft) --}}
                <a href="{{ route('kolektor.daftar-nasabah.index') }}" wire:navigate class="group flex flex-col items-center gap-1.5 p-1">
                    <div class="flex size-14 items-center justify-center rounded-2xl bg-primary-soft text-on-primary-soft shadow-xs transition group-hover:scale-105 group-active:scale-95">
                        <flux:icon.user-plus class="size-6" />
                    </div>
                    <span class="text-xs font-medium text-body">Daftar</span>
                </a>

                {{-- Tile 6: Tarik Tunai (info-soft) --}}
                <a href="{{ route('kolektor.penarikan-offline.index') }}" wire:navigate class="group flex flex-col items-center gap-1.5 p-1">
                    <div class="flex size-14 items-center justify-center rounded-2xl bg-info-soft text-info shadow-xs transition group-hover:scale-105 group-active:scale-95">
                        <flux:icon.arrow-down-tray class="size-6" />
                    </div>
                    <span class="text-xs font-medium text-body">Tarik tunai</span>
                </a>

                {{-- Tile 7: Verifikasi (teal) --}}
                <a href="{{ route('kolektor.verifikasi-penarikan.index') }}" wire:navigate class="group relative flex flex-col items-center gap-1.5 p-1">
                    <div class="relative flex size-14 items-center justify-center rounded-2xl bg-tile-teal-bg text-tile-teal-fg shadow-xs transition group-hover:scale-105 group-active:scale-95">
                        <flux:icon.shield-check class="size-6" />
                        @if($penarikanMenungguDiantar > 0)
                            <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-info px-1 text-xs font-semibold leading-none text-white">
                                {{ $penarikanMenungguDiantar }}
                            </span>
                        @endif
                    </div>
                    <span class="text-xs font-medium text-body">Verifikasi</span>
                </a>

                {{-- Tile 8: Riwayat (warning-soft) --}}
                <a href="{{ route('kolektor.riwayat-absensi.index') }}" wire:navigate class="group flex flex-col items-center gap-1.5 p-1">
                    <div class="flex size-14 items-center justify-center rounded-2xl bg-warning-soft text-warning-deep shadow-xs transition group-hover:scale-105 group-active:scale-95">
                        <flux:icon.clock class="size-6" />
                    </div>
                    <span class="text-xs font-medium text-body">Riwayat</span>
                </a>
            </div>

            {{-- 4. Card Kunjungan Berikutnya --}}
            <div class="rounded-[20px] border border-hairline bg-surface p-4 shadow-xs">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-ink">Kunjungan berikutnya</h2>
                    <span class="rounded-full bg-info-soft px-2.5 py-0.5 text-xs font-medium text-info">
                        {{ $kunjunganBerikutnya ? '0,4 km' : 'Selesai' }}
                    </span>
                </div>

                @if($kunjunganBerikutnya)
                    <div class="flex items-center gap-3 rounded-xl bg-canvas-warm p-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-xs font-semibold text-on-primary-soft">
                            {{ substr($kunjunganBerikutnya->nasabah_name, 0, 2) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-ink">{{ $kunjunganBerikutnya->nasabah_name }}</p>
                            <p class="mt-0.5 truncate text-xs text-mute">
                                {{ $kunjunganBerikutnya->nasabah_alamat ?? 'Nasabah binaan' }}
                                @if(!empty($kunjunganBerikutnya->produk_name))
                                    · {{ $kunjunganBerikutnya->produk_name }}
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('kolektor.setoran.index', ['nasabah_id' => $kunjunganBerikutnya->nasabah_id]) }}" wire:navigate
                           class="shrink-0 rounded-full bg-primary px-3.5 py-1.5 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-95">
                            Catat setoran
                        </a>
                    </div>
                @else
                    <div class="flex items-center gap-3 rounded-xl bg-canvas-warm p-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary text-on-primary">
                            <flux:icon.check class="size-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-ink">Semua kunjungan selesai</p>
                            <p class="mt-0.5 text-xs text-mute">Kerja bagus hari ini, seluruh jadwal telah selesai.</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 5. Card Serah Terima Paket (Jika Ada Paket Siap) --}}
            @if(!empty($paketSiapDiserahkan) && $paketSiapDiserahkan > 0)
                <div class="rounded-[20px] border border-hairline bg-surface p-4 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary-soft text-on-primary-soft">
                            <flux:icon.gift class="size-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-ink">Serah terima paket</p>
                            <p class="text-xs text-mute">{{ $paketSiapDiserahkan }} paket siap diserahkan</p>
                        </div>
                        <a href="{{ route('serah-terima.index') }}" wire:navigate
                           class="shrink-0 rounded-full bg-primary-soft px-3.5 py-1.5 text-xs font-medium text-on-primary-soft transition hover:bg-primary hover:text-on-primary">
                            Buka
                        </a>
                    </div>
                </div>
            @endif

            {{-- 6. Card Ringkasan Hari Ini --}}
            <div class="rounded-[20px] border border-hairline bg-surface p-4 shadow-xs">
                <h2 class="mb-2 text-sm font-semibold text-ink">Ringkasan hari ini</h2>
                <div class="flex items-center justify-between py-1.5 text-xs">
                    <span class="text-mute">Setoran masuk</span>
                    <span class="font-semibold text-ink tabular-nums">
                        <x-ui.rupiah :nilai="$setoranMasukHariIni" />
                    </span>
                </div>
                <div class="flex items-center justify-between border-b border-hairline pb-2.5 pt-1.5 text-xs">
                    <span class="text-mute">Nasabah dikunjungi</span>
                    <span class="font-semibold text-ink tabular-nums">
                        {{ $stats['kunjungan_selesai'] }} dari {{ $stats['kunjungan_total'] }}
                    </span>
                </div>
                <a href="{{ route('kolektor.transaksi.index') }}" wire:navigate
                   class="mt-2.5 flex items-center justify-between pt-1 text-xs font-semibold text-primary hover:underline">
                    <span>Lihat semua transaksi</span>
                    <flux:icon.chevron-right class="size-4" />
                </a>
            </div>

            {{-- 7. Card Kinerja 6 Hari (Grafik Batang) --}}
            <div class="rounded-[20px] border border-hairline bg-surface p-4 shadow-xs">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-ink">Kinerja 6 hari</h2>
                    <span class="rounded-full bg-primary-soft px-2.5 py-0.5 text-xs font-medium tabular-nums text-on-primary-soft">
                        Rata-rata <x-ui.rupiah :nilai="$rataRataKinerja" />
                    </span>
                </div>

                @php
                    $maxNominal = max(collect($kinerja6Hari)->max('nominal') ?? 0, $targetHarian) * 1.15;
                    $maxNominal = max($maxNominal, 1);
                    $targetBottomPct = min(85, round(($targetHarian / $maxNominal) * 100));
                @endphp
                <div class="relative flex h-28 items-end gap-2.5 px-1 pb-6 pt-2">
                    {{-- Garis putus-putus target harian --}}
                    <div class="absolute left-0 right-0 border-t-2 border-dashed border-warning"
                         style="bottom: calc(24px + {{ $targetBottomPct }}% * 0.7);"
                         title="Target harian Rp 1.500.000"></div>

                    @foreach($kinerja6Hari as $hari)
                        @php
                            $barHeightPct = max(6, round(($hari['nominal'] / $maxNominal) * 100));
                        @endphp
                        <div class="group relative flex h-full flex-1 flex-col items-center justify-end">
                            <div class="w-full rounded-t-lg transition-all duration-500 {{ $hari['is_hari_ini'] ? 'bg-primary' : 'bg-primary-soft' }}"
                                 style="height: {{ $barHeightPct }}%;"
                                 title="{{ $hari['label'] }}: Rp {{ number_format($hari['nominal'], 0, ',', '.') }}"></div>
                            <span class="absolute -bottom-5 text-xs {{ $hari['is_hari_ini'] ? 'font-semibold text-primary' : 'text-mute' }}">
                                {{ $hari['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 text-xs text-mute">
                    @if($setoranMasukHariIni >= $targetHarian)
                        <span class="font-medium text-primary">Target harian Rp 1.500.000 tercapai. Kerja bagus!</span>
                    @else
                        <span>Kurang <x-ui.rupiah :nilai="$targetHarian - $setoranMasukHariIni" /> dari target harian Rp 1.500.000 (garis putus-putus).</span>
                    @endif
                </div>
            </div>

            {{-- 8. Timeline Rute Kunjungan --}}
            @if($jadwalHariIni->count() > 0)
                <div class="rounded-[20px] border border-hairline bg-surface p-4 shadow-xs">
                    <div class="mb-3.5 flex items-center justify-between border-b border-hairline pb-2.5">
                        <div class="flex items-center gap-2">
                            <flux:icon.map-pin class="size-4 text-primary" />
                            <h2 class="text-sm font-semibold text-ink">Rute kunjungan hari ini</h2>
                        </div>
                        <span class="rounded-full bg-primary-soft px-2.5 py-0.5 text-xs font-medium text-on-primary-soft">
                            {{ $jadwalHariIni->count() }} titik
                        </span>
                    </div>

                    <div class="relative space-y-3.5 pl-6">
                        <div class="absolute bottom-2 left-[9px] top-2 w-0.5 bg-hairline-warm dark:bg-hairline"></div>
                        @php
                            $firstUndone = $jadwalHariIni->firstWhere('status_kunjungan', '!=', 'dikunjungi');
                        @endphp
                        @foreach($jadwalHariIni as $jadwal)
                            <div class="relative flex items-center justify-between gap-2">
                                @if($jadwal->status_kunjungan === 'dikunjungi')
                                    <div class="absolute -left-6 top-1 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-primary text-on-primary"><flux:icon.check class="size-2.5" /></div>
                                    <p class="truncate text-xs font-medium text-subtle line-through">{{ $jadwal->nasabah_name }}</p>
                                    <span class="shrink-0 rounded-full bg-primary-soft px-2 py-0.5 text-xs font-medium text-on-primary-soft">Selesai</span>
                                @elseif($firstUndone && $jadwal->id === $firstUndone->id)
                                    <div class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full bg-primary ring-4 ring-primary-soft"></div>
                                    <p class="truncate text-xs font-semibold text-ink dark:text-zinc-100">{{ $jadwal->nasabah_name }}</p>
                                    <a href="{{ route('kolektor.setoran.index', ['nasabah_id' => $jadwal->nasabah_id]) }}" wire:navigate class="shrink-0 rounded-full bg-primary px-2.5 py-1 text-xs font-medium text-on-primary transition hover:bg-primary-hover">Setor</a>
                                @else
                                    <div class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full border-2 border-hairline-strong bg-white dark:bg-surface"></div>
                                    <p class="truncate text-xs text-mute">{{ $jadwal->nasabah_name }}</p>
                                    <span class="shrink-0 text-xs text-subtle">Antrean</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 border-t border-hairline-warm/60 pt-3 dark:border-hairline/60">
                        <a href="{{ route('kolektor.jadwal.index') }}" wire:navigate
                           class="flex w-full items-center justify-center gap-1.5 rounded-full bg-primary-soft py-2.5 text-xs font-semibold text-on-primary-soft transition hover:bg-primary hover:text-on-primary">
                            <span>Buka rute lengkap</span>
                            <flux:icon.arrow-right class="size-3.5" />
                        </a>
                    </div>
                </div>
            @endif

        @else
            {{-- ==================== NASABAH DASHBOARD ==================== --}}
                      @php
                /**
                 * DESIGN.md §9 (sapaan nama depan dan waktu) dan §14 (sapaan waktu
                 * baru, dihitung di view). Jam memakai timezone aplikasi; nama dan
                 * kehadiran setoran hari ini semuanya berasal dari data yang sudah
                 * dimuat controller, tanpa query baru.
                 */
                $jamSekarang = (int) now()->hour;
                $sapaanWaktu = match (true) {
                    $jamSekarang >= 3 && $jamSekarang < 11 => 'Selamat pagi',
                    $jamSekarang >= 11 && $jamSekarang < 15 => 'Selamat siang',
                    $jamSekarang >= 15 && $jamSekarang < 19 => 'Selamat sore',
                    default => 'Selamat malam',
                };
                $namaDepan = \Illuminate\Support\Str::before(trim($user->name), ' ');
                $setoranHariIniTercatat = $riwayatGabungan->contains(
                    fn (array $t): bool => $t['type'] === 'setoran' && $t['tanggal']->isToday()
                );
            @endphp

            {{-- 1. Hero Header dengan Gelombang Dekoratif (Selaras Kolektor & Prototipe) --}}
            <div class="relative -mx-4 -mt-4 overflow-hidden bg-primary-deep px-4 pb-16 pt-4 text-on-hero">
                {{-- Lingkaran dekoratif halus di latar belakang --}}
                <span class="pointer-events-none absolute -right-6 -top-4 size-28 rounded-full bg-white/5 animate-pulse" aria-hidden="true"></span>

                <div class="relative flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @if($user->foto_profil_path)
                            <img src="{{ asset('storage/'.$user->foto_profil_path) }}" alt="Foto Profil"
                                 class="size-11 rounded-full border border-white/20 object-cover shadow-xs" />
                        @else
                            <div class="flex size-11 items-center justify-center rounded-full bg-primary-soft text-sm font-semibold text-primary">
                                {{ $user->initials() }}
                            </div>
                        @endif
                        <div>
                            <div class="text-xs text-on-hero-mute">{{ $sapaanWaktu }}</div>
                            <h1 class="text-base font-semibold leading-tight text-white">
                                Halo, {{ $namaDepan }}
                            </h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="$store.textSize.toggle()"
                                :aria-pressed="$store.textSize?.isBig"
                                class="relative flex size-9 items-center justify-center rounded-full bg-white/15 text-xs font-semibold text-white transition hover:bg-white/25 active:scale-95"
                                aria-label="Perbesar tulisan">
                            <span x-text="$store.textSize?.isBig ? 'A−' : 'A+'">A+</span>
                        </button>

                        <a href="{{ route('nasabah.notifikasi.index') }}" wire:navigate
                           class="relative flex size-9 items-center justify-center rounded-full bg-white/15 text-white transition hover:bg-white/25 active:scale-95"
                           aria-label="{{ $unreadNotifikasi ? 'Notifikasi, belum dibaca' : 'Notifikasi' }}">
                            <flux:icon.bell class="size-4" />
                            @if($unreadNotifikasi)
                                <span class="absolute -right-1 -top-1 flex h-3.5 min-w-3.5 items-center justify-center rounded-full bg-decor-gold px-1 text-xs font-semibold leading-none text-ink shadow-xs">
                                    !
                                </span>
                            @endif
                        </a>
                    </div>
                </div>

                {{-- Gelombang SVG transisi kanvas --}}
                <svg class="absolute -bottom-px left-0 h-7 w-full text-canvas-warm dark:text-dark-canvas" viewBox="0 0 400 28" preserveAspectRatio="none" fill="currentColor" aria-hidden="true">
                    <path d="M0 28V16C70 0 130 0 200 12s130 8 200-6v22z"/>
                </svg>
            </div>

            {{-- 2. Kartu saldo utama (Overlap Hero -mt-10, Selaras Kolektor Kas di Tangan & Prototipe) --}}
            <x-ui.kartu-saldo-hero :total-saldo="$totalSaldo" variant="surface" class="relative -mt-10">
                @if($saldoPerProduk->count() > 0)
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach($saldoPerProduk as $index => $sp)
                            @php
                                $badgeStyle = match ($index % 3) {
                                    0 => 'bg-primary-soft text-primary',
                                    1 => 'bg-info-soft text-info',
                                    default => 'bg-tile-teal-bg text-tile-teal-fg',
                                };
                            @endphp
                            <a href="{{ route('nasabah.saldo.index') }}" wire:navigate
                               class="inline-flex items-center gap-1 rounded-full {{ $badgeStyle }} px-3 py-1 text-xs font-medium transition hover:opacity-80 active:scale-95">
                                <span>{{ $sp->produk?->nama ?? '-' }}</span>
                                <span class="opacity-60">&middot;</span>
                                <span class="font-semibold"><x-ui.rupiah :nilai="$sp->saldo" /></span>
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Tombol Aksi Langsung --}}
                <div class="mt-4 flex items-center gap-2.5">
                    @if($totalSaldo > 0)
                        <a href="{{ route('nasabah.penarikan.index') }}" wire:navigate
                           class="flex h-11 flex-1 items-center justify-center gap-1.5 rounded-full bg-primary text-xs font-semibold text-white shadow-xs transition hover:bg-primary-hover active:scale-95">
                            <flux:icon.arrow-down-tray class="size-4 shrink-0 text-white" />
                            <span>Tarik saldo</span>
                        </a>
                    @else
                        <button type="button" disabled
                           title="Saldo Anda masih Rp 0. Belum ada saldo yang dapat ditarik."
                           class="flex h-11 flex-1 cursor-not-allowed items-center justify-center gap-1.5 rounded-full bg-surface-inset text-xs font-semibold text-mute shadow-xs">
                            <flux:icon.arrow-down-tray class="size-4 shrink-0 text-mute" />
                            <span>Tarik saldo</span>
                        </button>
                    @endif
                    <a href="{{ route('nasabah.saldo.index') }}" wire:navigate
                       class="flex h-11 flex-1 items-center justify-center gap-1.5 rounded-full bg-primary-soft text-xs font-semibold text-on-primary-soft transition hover:bg-primary-soft/80 active:scale-95">
                        <flux:icon.wallet class="size-4 shrink-0 text-primary" />
                        <span>Rincian</span>
                    </a>
                </div>

                @if($setoranHariIniTercatat)
                    <div class="mt-3.5 flex items-center gap-2 rounded-xl bg-primary-soft px-3 py-2 text-xs font-medium text-on-primary-soft">
                        <flux:icon.check-circle class="size-4 shrink-0 text-primary" />
                        <span>Setoran hari ini sudah tercatat. Terima kasih!</span>
                    </div>
                @endif
            </x-ui.kartu-saldo-hero>

            {{-- 3. Kartu streak nabung (DESIGN.md §7 "Streak": tujuh lingkaran 30px Senin–Minggu) --}}
            <div class="rounded-[20px] border border-hairline bg-surface p-4 shadow-xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <x-ui.tile kategori="hijau" ukuran="44" aria-hidden="true">
                            <flux:icon.fire class="size-5" />
                        </x-ui.tile>
                        <div>
                            <p class="text-base font-semibold text-ink tabular-nums">{{ $streak }} hari berturut-turut</p>
                            <p class="text-xs text-mute">Pertahankan kebiasaan menabungmu</p>
                        </div>
                    </div>
                    @if($streak >= 7)
                        <span class="rounded-full bg-decor-gold/20 px-2.5 py-0.5 text-xs font-semibold text-ink">
                            Hebat! 🔥
                        </span>
                    @endif
                </div>

                <ul class="mt-3.5 flex items-center justify-between" aria-label="Setoran minggu ini">
                    @foreach ($streakMingguIni as $titik)
                        <li class="flex w-10 flex-col items-center gap-1.5">
                            <span @class([
                                'flex h-[30px] w-[30px] items-center justify-center rounded-full text-xs transition',
                                'bg-primary-soft text-primary font-semibold' => $titik['ada_setoran'],
                                'border-2 border-hairline-strong bg-surface text-mute' => ! $titik['ada_setoran'],
                            ])>
                                @if ($titik['ada_setoran'])
                                    <flux:icon.check class="size-4" aria-hidden="true" />
                                @endif
                            </span>
                            <span class="text-xs font-medium text-mute">{{ $titik['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- 4. Kartu paket: cincin progres atau ajakan gabung paket --}}
            @if($kepesertaanAktif?->produk)
                @php
                    $pencairanPaket = $kepesertaanAktif->statusPencairan(simpan: false);
                    $tanggalCair = $kepesertaanAktif->produk->tanggal_boleh_cair;
                    $hariLagi = $tanggalCair
                        ? (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($tanggalCair)->startOfDay(), false)
                        : 0;

                    // Target paket: durasi periode (mulai s/d selesai) x harga per hari
                    $targetPaket = $kepesertaanAktif->produk?->targetAkhir() ?? $kepesertaanAktif->targetKepesertaan();
                    $terkumpulPaket = (float) $kepesertaanAktif->total_aktual_terkumpul;
                    $sisaPaket = $targetPaket !== null ? max(0, $targetPaket - $terkumpulPaket) : null;
                    $persenPaket = $targetPaket !== null && $targetPaket > 0
                        ? (int) min(100, round($terkumpulPaket / $targetPaket * 100))
                        : null;
                    $sisaPersenPaket = $persenPaket !== null ? max(0, 100 - $persenPaket) : null;

                    $teksPencairan = $pencairanPaket['boleh']
                        ? 'Sudah Bisa Dicairkan'
                        : (($pencairanPaket['kode'] === 'belum_waktunya' && $tanggalCair !== null)
                            ? $hariLagi.' Hari Lagi'
                            : (string) $pencairanPaket['alasan']);
                    $kunciPencairan = match (true) {
                        $pencairanPaket['boleh'] => 'boleh',
                        $teksPencairan === \App\Models\KepesertaanPaket::ALASAN_TANGGAL_KOSONG => 'tanggal_kosong',
                        default => (string) $pencairanPaket['kode'],
                    };
                @endphp
                <a href="{{ route('nasabah.progres-paket.index') }}" wire:navigate
                   class="group flex items-center gap-4 rounded-[20px] border border-hairline bg-surface p-4 shadow-xs transition hover:border-hairline-strong active:scale-[0.99]">
                    @if($persenPaket !== null)
                        <x-ui.progress-ring :persen="$persenPaket" :ukuran="88" :lebar="9"
                            :label="'Capaian paket '.$persenPaket.' persen'" />
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                            <p class="truncate text-sm font-medium text-mute">{{ $kepesertaanAktif->produk->nama }}</p>
                            <flux:icon.chevron-right class="size-4 shrink-0 text-mute transition group-hover:translate-x-0.5 group-hover:text-primary" />
                        </div>
                        @if($sisaPersenPaket !== null)
                            <p class="mt-0.5 text-base font-semibold text-ink">
                                Tinggal {{ $sisaPersenPaket }}% lagi
                            </p>
                        @endif
                        <div class="mt-2 flex items-center gap-2">
                            <x-ui.status-chip domain="pencairan" :status="$kunciPencairan" :teks="$teksPencairan" />
                            @if($sisaPaket !== null)
                                <span class="text-xs text-mute">
                                    Sisa <x-ui.rupiah :nilai="$sisaPaket" />
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @else
                <a href="{{ route('nasabah.progres-paket.index') }}" wire:navigate
                   class="group flex items-center justify-between rounded-[20px] border border-hairline bg-surface p-4 shadow-xs transition hover:border-hairline-strong active:scale-[0.99]">
                    <div class="flex items-center gap-3.5">
                        <x-ui.tile kategori="teal" ukuran="44">
                            <flux:icon.gift class="size-5" />
                        </x-ui.tile>
                        <div>
                            <p class="text-base font-semibold text-ink">Paket Lebaran</p>
                            <p class="mt-0.5 text-xs font-medium text-primary">Gabung Paket Sekarang &middot; Wujudkan impian hari raya</p>
                        </div>
                    </div>
                    <flux:icon.arrow-right class="size-4 shrink-0 text-primary transition group-hover:translate-x-0.5" />
                </a>
            @endif

            {{-- 5. Aksi cepat: Grid 4 ubin kategori (§7 & prototipe) --}}
            <div class="rounded-[20px] border border-hairline bg-surface p-4 shadow-xs">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-base font-semibold text-ink">Mau apa hari ini?</span>
                    <span class="text-xs font-medium text-mute">Menu Cepat</span>
                </div>
                <div class="grid grid-cols-4 gap-2">
                    <x-ui.ubin-aksi :href="route('nasabah.penarikan.index')" label="Tarik" kategori="hijau">
                        <flux:icon.arrow-down-tray class="size-6" />
                    </x-ui.ubin-aksi>
                    <x-ui.ubin-aksi :href="route('nasabah.riwayat-tabungan.index')" label="Riwayat" kategori="biru">
                        <flux:icon.clock class="size-6" />
                    </x-ui.ubin-aksi>
                    <x-ui.ubin-aksi :href="route('nasabah.progres-paket.index')" label="Paket" kategori="teal">
                        <flux:icon.shopping-bag class="size-6" />
                    </x-ui.ubin-aksi>
                    <x-ui.ubin-aksi :href="route('nasabah.komplain.index')" label="Bantuan" kategori="kuning">
                        <flux:icon.chat-bubble-left-right class="size-6" />
                    </x-ui.ubin-aksi>
                </div>
            </div>

            {{-- 6. Pengajuan Penarikan Aktif (D18) --}}
            <livewire:nasabah.pengajuan-aktif />

            {{-- 7. Petugas Kolektor Anda (Hanya jika nasabah memiliki penugasan aktif) --}}
            @php
                $kolektorPj = \App\Models\KolektorNasabah::where('nasabah_id', $user->id)
                    ->where('status', 'aktif')
                    ->with('kolektor')
                    ->first()?->kolektor;
            @endphp
            @if($kolektorPj)
                <div class="rounded-[20px] border border-hairline bg-surface p-4 shadow-xs">
                    <div class="mb-2.5 flex items-center justify-between">
                        <h3 class="text-xs font-medium text-mute">Petugas Kolektor Anda</h3>
                        <span class="rounded-full bg-primary-soft px-2 py-0.5 text-xs font-medium text-on-primary-soft">
                            Penanggung Jawab
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-info-soft text-sm font-semibold text-info" aria-hidden="true">
                                {{ $kolektorPj->initials() }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-ink">{{ $kolektorPj->name }}</p>
                                <p class="truncate text-xs text-mute">{{ $kolektorPj->no_hp ?? 'Petugas lapangan' }}</p>
                            </div>
                        </div>
                        @if($kolektorPj->no_hp)
                            @php
                                $waKolektor = preg_replace('/[^0-9]/', '', (string)$kolektorPj->no_hp);
                                if (str_starts_with($waKolektor, '0')) {
                                    $waKolektor = '62' . substr($waKolektor, 1);
                                }
                            @endphp
                            <a href="https://wa.me/{{ $waKolektor }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-primary-soft px-3 py-1.5 text-xs font-semibold text-on-primary-soft transition hover:bg-primary hover:text-on-primary active:scale-95">
                                <flux:icon.chat-bubble-left-ellipsis class="size-3.5" />
                                <span>Chat</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- 8. Aktivitas terakhir --}}
            <div class="rounded-[20px] border border-hairline bg-surface p-4 shadow-xs">
                <div class="mb-1 flex items-center justify-between border-b border-hairline pb-3">
                    <h3 class="text-base font-semibold text-ink">Aktivitas terakhir</h3>
                    @if($riwayatGabungan->count() > 0)
                        <a href="{{ route('nasabah.riwayat-tabungan.index') }}" wire:navigate class="text-xs font-semibold text-primary hover:underline">
                            Lihat Semua
                        </a>
                    @endif
                </div>

                @if($riwayatGabungan->count() > 0)
                    <div class="divide-y divide-hairline">
                        @foreach($riwayatGabungan->take(3) as $trx)
                            <x-ui.baris-riwayat
                                class="px-1"
                                :judul="$trx['nama']"
                                :keterangan="$trx['tanggal']->isToday() ? 'Hari ini, '.$trx['tanggal']->format('H:i') : ($trx['tanggal']->isYesterday() ? 'Kemarin, '.$trx['tanggal']->format('H:i') : $trx['tanggal']->translatedFormat('d M Y, H:i'))"
                                :nominal="$trx['nominal']"
                                :tanda="$trx['type'] === 'setoran' ? '+' : '-'"
                                :kategori="$trx['type'] === 'setoran' ? 'hijau' : 'netral'"
                                :aksi-href="($trx['type'] === 'setoran' && !empty($trx['id'])) ? route('struk.setoran', $trx['id']) : null"
                                :aksi-label="($trx['type'] === 'setoran' && !empty($trx['id'])) ? 'Lihat struk' : null">
                                @if($trx['type'] === 'setoran')
                                    <flux:icon.arrow-down-left class="size-4" />
                                @else
                                    <flux:icon.arrow-up-right class="size-4" />
                                @endif
                            </x-ui.baris-riwayat>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <div class="mx-auto mb-2.5 flex size-12 items-center justify-center rounded-full bg-surface-inset text-mute">
                            <flux:icon.clock class="size-6" />
                        </div>
                        <p class="text-sm font-semibold text-ink">Belum ada aktivitas</p>
                        <p class="mt-0.5 text-xs text-mute">Transaksi setoran dan penarikan Anda akan tercatat di sini.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-layouts::mobile>
@endif
