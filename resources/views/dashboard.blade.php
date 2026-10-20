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
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-primary-soft text-sm font-semibold text-on-primary-soft">
                        {{ $user->initials() }}
                    </div>
                    <div>
                        <span class="text-xs text-mute">Petugas kolektor</span>
                        <h1 class="text-base font-semibold tracking-tight text-ink leading-tight">
                            {{ $user->name }}
                        </h1>
                    </div>
                </div>

                @if($sudahAbsenHariIni)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-soft px-3 py-1.5 text-xs font-medium text-on-primary-soft">
                        <span class="h-1.5 w-1.5 rounded-full bg-on-primary-soft animate-pulse"></span>
                        Sudah Absen
                    </span>
                @else
                    <a href="{{ route('kolektor.absen.index') }}" wire:navigate
                       class="inline-flex items-center gap-1.5 rounded-full border border-hairline-strong bg-white px-3.5 py-1.5 text-xs font-medium text-ink transition hover:bg-inset dark:bg-surface">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary animate-ping"></span>
                        Belum Absen
                    </a>
                @endif
            </div>

            {{-- 2. Hero Card: Kas di Tangan --}}
            <div class="rounded-2xl border border-primary bg-primary-soft p-5 text-on-primary-soft">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-sm">Kas di Tangan</span>
                        <span class="rounded-full border border-primary px-2.5 py-1 text-xs font-medium text-on-primary-soft">
                            Hari Ini
                        </span>
                    </div>
                    <p class="mb-4 text-[32px] font-semibold leading-9 tracking-tight">
                        <x-ui.rupiah :nilai="$stats['setoran_belum_disetor']" />
                    </p>

                    <div class="grid grid-cols-2 gap-2.5">
                        <a href="{{ route('kolektor.setoran.index') }}" wire:navigate
                           class="flex items-center justify-center gap-2 rounded-full bg-primary py-2.5 text-sm font-medium text-on-primary transition hover:bg-primary-hover active:scale-95">
                            <flux:icon.plus-circle class="size-4 text-on-primary" />
                            Setor Nasabah
                        </a>
                        <a href="{{ route('kolektor.setor-kantor.index') }}" wire:navigate
                           class="flex items-center justify-center gap-2 rounded-full border border-primary py-2.5 text-sm font-medium text-on-primary-soft transition hover:bg-inset active:scale-95">
                            <flux:icon.building-office-2 class="size-4 text-on-primary-soft" />
                            Setor ke Kantor
                        </a>
                    </div>
                </div>
            </div>

            {{-- 3. Metric Cards Grid --}}
            <div class="grid grid-cols-3 gap-2.5">
                <div class="rounded-2xl border border-hairline bg-white p-3.5 dark:bg-surface">
                    <span class="text-xs text-mute">Kunjungan</span>
                    <p class="mt-1 text-base font-semibold text-primary">
                        {{ $stats['kunjungan_selesai'] }}<span class="text-xs text-mute">/{{ $stats['kunjungan_total'] }}</span>
                    </p>
                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-inset">
                        @php
                            $pct = $stats['kunjungan_total'] > 0 ? round(($stats['kunjungan_selesai'] / $stats['kunjungan_total']) * 100) : 0;
                        @endphp
                        <div class="h-full rounded-full bg-primary transition-all duration-500" style="width: {{ $pct }}%"></div>
                    </div>
                </div>

                <div class="rounded-2xl border border-hairline bg-white p-3.5 dark:bg-surface">
                    <span class="text-xs text-mute">Binaan</span>
                    <p class="mt-1 text-base font-semibold text-info">
                        {{ $totalNasabahBinaan ?? 0 }} <span class="text-xs text-mute">Nasabah</span>
                    </p>
                    <p class="mt-1 truncate text-xs text-subtle">Status aktif</p>
                </div>

                <div class="rounded-2xl border border-hairline bg-white p-3.5 dark:bg-surface">
                    <span class="text-xs text-mute">Tunggakan</span>
                    <p class="mt-1 text-base font-semibold text-warning-deep">
                        {{ $nasabahTunggakParah }} <span class="text-xs text-mute">Nasabah</span>
                    </p>
                    <p class="mt-1 truncate text-xs font-medium text-warning-deep">Perlu penagihan</p>
                </div>
            </div>

            {{-- 4. Menu Shortcuts --}}
            <div class="rounded-2xl border border-hairline bg-white p-4 dark:bg-surface">
                <span class="mb-3 block text-xs text-mute">Menu Utama</span>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <a href="{{ route('kolektor.setoran.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft transition group-hover:scale-105">
                            <flux:icon.arrow-down-tray class="size-5" />
                        </div>
                        <span class="mt-1.5 text-xs font-medium text-body">Setor</span>
                    </a>
                    <a href="{{ route('kolektor.setor-kantor.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-info-soft text-info transition group-hover:scale-105">
                            <flux:icon.building-office class="size-5" />
                        </div>
                        <span class="mt-1.5 text-xs font-medium text-body">Kantor</span>
                    </a>
                    <a href="{{ route('kolektor.verifikasi-penarikan.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="relative flex h-11 w-11 items-center justify-center rounded-full bg-warning-soft text-warning-deep transition group-hover:scale-105">
                            <flux:icon.check-badge class="size-5" />
                            @if($penarikanMenungguDiantar > 0)
                                <span class="absolute -right-1.5 -top-1.5 min-w-[18px] rounded-full bg-info-soft px-1 text-center text-xs font-medium leading-[18px] text-info">{{ $penarikanMenungguDiantar }}</span>
                            @endif
                        </div>
                        <span class="mt-1.5 text-xs font-medium text-body">Verifikasi</span>
                        @if($penarikanMenungguDiantar > 0)
                            <span class="text-xs font-medium leading-tight text-warning-deep">menunggu diantar</span>
                        @endif
                    </a>
                    <a href="{{ route('kolektor.jadwal.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-info-soft text-info transition group-hover:scale-105">
                            <flux:icon.calendar-days class="size-5" />
                        </div>
                        <span class="mt-1.5 text-xs font-medium text-body">Jadwal</span>
                    </a>
                    <a href="{{ route('kolektor.nasabah.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-inset text-ink transition group-hover:scale-105">
                            <flux:icon.users class="size-5" />
                        </div>
                        <span class="mt-1.5 text-xs font-medium text-body">Nasabah</span>
                    </a>
                    <a href="{{ route('serah-terima.index') }}" wire:navigate class="flex flex-col items-center group">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-inset text-ink transition group-hover:scale-105">
                            <flux:icon.hand-raised class="size-5" />
                        </div>
                        <span class="mt-1.5 text-xs font-medium text-body">Serah Terima</span>
                    </a>
                </div>
            </div>

            {{-- 5. Rute Kunjungan --}}
            @if($jadwalHariIni->count() > 0)
                <div class="rounded-2xl border border-hairline bg-white p-4 dark:bg-surface">
                    <div class="mb-3.5 flex items-center justify-between border-b border-hairline pb-2.5">
                        <div class="flex items-center gap-2">
                            <flux:icon.map-pin class="size-4 text-info" />
                            <h3 class="text-sm font-semibold text-ink">Rute kunjungan hari ini</h3>
                        </div>
                        <a href="{{ route('kolektor.jadwal.index') }}" wire:navigate class="text-xs font-medium text-primary hover:underline">
                            Lihat Semua
                        </a>
                    </div>

                    <div class="relative pl-6 space-y-3.5">
                        <div class="absolute bottom-2 left-[9px] top-2 w-0.5 bg-hairline"></div>
                        @php
                            $firstUndone = $jadwalHariIni->firstWhere('status_kunjungan', '!=', 'dikunjungi');
                        @endphp
                        @foreach($jadwalHariIni as $jadwal)
                            <div class="relative flex items-center justify-between gap-2">
                                @if($jadwal->status_kunjungan === 'dikunjungi')
                                    <div class="absolute -left-6 top-1 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-primary text-on-primary"><flux:icon.check class="size-2.5" /></div>
                                    <p class="text-xs font-medium text-subtle line-through truncate">{{ $jadwal->nasabah_name }}</p>
                                    <span class="shrink-0 rounded-full bg-primary-soft px-2 py-0.5 text-xs font-medium text-on-primary-soft">Selesai</span>
                                @elseif($firstUndone && $jadwal->id === $firstUndone->id)
                                    <div class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full bg-primary ring-4 ring-primary-soft"></div>
                                    <p class="text-xs font-semibold text-ink truncate">{{ $jadwal->nasabah_name }}</p>
                                    <a href="{{ route('kolektor.setoran.index') }}" wire:navigate class="shrink-0 rounded-full bg-primary px-2.5 py-1 text-xs font-medium text-on-primary transition hover:bg-primary-hover">Setor</a>
                                @else
                                    <div class="absolute -left-6 top-1 h-3.5 w-3.5 rounded-full border-2 border-hairline-strong bg-white dark:bg-surface"></div>
                                    <p class="text-xs text-mute truncate">{{ $jadwal->nasabah_name }}</p>
                                    <span class="shrink-0 text-xs text-subtle">Antrean</span>
                                @endif
                            </div>
                        @endforeach
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

            {{-- 1. Header sapaan waktu & lonceng notifikasi --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @if($user->foto_profil_path)
                        <img src="{{ asset('storage/'.$user->foto_profil_path) }}" alt="Foto Profil"
                             class="h-11 w-11 rounded-full border border-hairline object-cover" />
                    @else
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-primary-soft text-sm font-semibold text-on-primary-soft">
                            {{ $user->initials() }}
                        </div>
                    @endif
                    <div>
                        <p class="text-sm text-mute">{{ $sapaanWaktu }}</p>
                        <h1 class="text-base font-semibold tracking-tight text-ink leading-tight">
                            Halo, {{ $namaDepan }}
                        </h1>
                    </div>
                </div>

                <a href="{{ route('nasabah.notifikasi.index') }}" wire:navigate
                   aria-label="{{ $unreadNotifikasi ? 'Notifikasi, belum dibaca' : 'Notifikasi' }}"
                   class="relative flex h-10 w-10 items-center justify-center rounded-full border border-hairline bg-white text-ink transition hover:bg-inset dark:bg-surface">
                    <flux:icon.bell class="size-5" />
                    @if($unreadNotifikasi)
                        <span class="absolute top-2 right-2 h-2 w-2 rounded-full bg-primary"></span>
                    @endif
                </a>
            </div>

            {{-- 2. Kartu saldo utama (hero-card §7) --}}
            <x-ui.kartu-saldo-hero :total-saldo="$totalSaldo">
                @if($saldoPerProduk->count() > 0)
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach($saldoPerProduk as $sp)
                            <span class="rounded-full bg-white/[0.16] px-2.5 py-1 text-xs font-medium">
                                {{ $sp->produk?->nama ?? '-' }} &middot; <x-ui.rupiah :nilai="$sp->saldo" />
                            </span>
                        @endforeach
                    </div>
                @endif

                @if($setoranHariIniTercatat)
                    <p class="mt-3 flex items-start gap-1.5 text-sm">
                        <flux:icon.check class="mt-0.5 size-4 shrink-0" />
                        Setoran hari ini sudah tercatat. Terima kasih!
                    </p>
                @endif
            </x-ui.kartu-saldo-hero>

            {{-- 3. Kartu streak nabung (DESIGN.md §7 "Streak": tujuh lingkaran 30px Senin–Minggu) --}}
            <div class="rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                <div class="flex items-center gap-3">
                    <x-ui.tile kategori="hijau" ukuran="44" aria-hidden="true">
                        <flux:icon.fire class="size-5" />
                    </x-ui.tile>
                    <p class="text-base font-semibold text-ink tabular-nums">{{ $streak }} hari berturut-turut</p>
                </div>

                <ul class="mt-3 flex items-center justify-between" aria-label="Setoran minggu ini">
                    @foreach ($streakMingguIni as $titik)
                        <li class="flex w-10 flex-col items-center gap-1">
                            <span @class([
                                'flex h-[30px] w-[30px] items-center justify-center rounded-full',
                                'bg-primary-soft text-primary' => $titik['ada_setoran'],
                                'border-2 border-hairline-strong bg-white dark:bg-surface' => ! $titik['ada_setoran'],
                            ])>
                                @if ($titik['ada_setoran'])
                                    <flux:icon.check class="size-4" aria-hidden="true" />
                                @endif
                            </span>
                            <span class="text-xs text-mute">{{ $titik['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- 4. Kartu paket: cincin progres, sisa target, dan status pencairan --}}
            @if($kepesertaanAktif?->produk)
                @php
                    $pencairanPaket = $kepesertaanAktif->statusPencairan(simpan: false);
                    $tanggalCair = $kepesertaanAktif->produk->tanggal_boleh_cair;
                    $hariLagi = $tanggalCair
                        ? (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($tanggalCair)->startOfDay(), false)
                        : 0;

                    // DESIGN.md §14: "Tinggal Rp X lagi" dihitung dari target
                    // produk dan total_aktual_terkumpul yang sudah dimuat.
                    $targetPaket = $kepesertaanAktif->produk->targetAkhir();
                    $terkumpulPaket = (float) $kepesertaanAktif->total_aktual_terkumpul;
                    $sisaPaket = $targetPaket !== null ? max(0, $targetPaket - $terkumpulPaket) : null;
                    $persenPaket = $targetPaket !== null && $targetPaket > 0
                        ? (int) min(100, round($terkumpulPaket / $targetPaket * 100))
                        : null;

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
                   class="flex items-center gap-4 rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] transition hover:border-hairline-strong dark:bg-surface">
                    @if($persenPaket !== null)
                        <x-ui.progress-ring :persen="$persenPaket" :ukuran="88" :lebar="10"
                            :label="'Capaian paket '.$persenPaket.' persen'" />
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm text-mute">{{ $kepesertaanAktif->produk->nama }}</p>
                        @if($sisaPaket !== null)
                            <p class="mt-1 text-base font-semibold text-ink">
                                Tinggal <x-ui.rupiah :nilai="$sisaPaket" /> lagi
                            </p>
                        @endif
                        <div class="mt-2">
                            <x-ui.status-chip domain="pencairan" :status="$kunciPencairan" :teks="$teksPencairan" />
                        </div>
                    </div>
                </a>
            @else
                <a href="{{ route('nasabah.progres-paket.index') }}" wire:navigate
                   class="flex items-center justify-between rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] transition hover:border-hairline-strong dark:bg-surface">
                    <div>
                        <p class="text-base font-semibold text-ink">Paket Lebaran</p>
                        <p class="mt-0.5 text-sm text-primary">Gabung Paket Sekarang</p>
                    </div>
                    <flux:icon.arrow-right class="size-4 shrink-0 text-primary" />
                </a>
            @endif

            {{-- 5. Aksi cepat: empat ubin kategori (§7) --}}
            <div class="rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                <span class="mb-3 block text-base font-semibold text-ink">Mau apa hari ini?</span>
                <div class="grid grid-cols-4 gap-2">
                    <x-ui.ubin-aksi :href="route('nasabah.penarikan.index')" label="Tarik" kategori="hijau">
                        <flux:icon.arrow-up-tray class="size-6" />
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

            {{-- 7. Aktivitas terakhir --}}
            @if($riwayatGabungan->count() > 0)
                <div class="rounded-[20px] border border-hairline-warm bg-white p-4 shadow-[0_1px_2px_rgba(20,83,45,.06)] dark:bg-surface">
                    <div class="mb-1 flex items-center justify-between border-b border-hairline-warm pb-3">
                        <h3 class="text-base font-semibold text-ink">Aktivitas terakhir</h3>
                        <a href="{{ route('nasabah.riwayat-tabungan.index') }}" wire:navigate class="text-xs font-medium text-primary hover:underline">
                            Lihat Semua
                        </a>
                    </div>
                    <div>
                        @foreach($riwayatGabungan->take(3) as $trx)
                            <x-ui.baris-riwayat
                                :judul="$trx['nama']"
                                :keterangan="$trx['tanggal']->isToday() ? 'Hari ini, '.$trx['tanggal']->format('H:i') : $trx['tanggal']->diffForHumans()"
                                :nominal="$trx['nominal']"
                                :tanda="$trx['type'] === 'setoran' ? '+' : '-'"
                                :kategori="$trx['type'] === 'setoran' ? 'hijau' : 'netral'">
                                @if($trx['type'] === 'setoran')
                                    <flux:icon.arrow-down-left class="size-4" />
                                @else
                                    <flux:icon.arrow-up-right class="size-4" />
                                @endif
                            </x-ui.baris-riwayat>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-layouts::mobile>
@endif
