@php
    $totalAntrean = \App\Support\AntreanPersetujuan::total();

    $aktifBeranda = request()->routeIs('dashboard');
    $aktifPersetujuan = request()->routeIs('admin.persetujuan.*');
    $aktifKeuangan = request()->routeIs('admin.rekonsiliasi.*')
        || request()->routeIs('admin.kas-kolektor.*')
        || request()->routeIs('admin.komisi.*')
        || request()->routeIs('admin.laporan.*');
    $aktifNasabah = request()->routeIs('admin.nasabah.*')
        || request()->routeIs('admin.registrasi.*')
        || request()->routeIs('admin.verifikasi.*');

    $gayaTab = fn (bool $aktif): string => $aktif
        ? 'bg-white text-[#171717] dark:bg-zinc-100 dark:text-zinc-900 shadow-md'
        : 'text-white/60 hover:text-white';

    $iconBeranda = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-[17px] h-[17px]"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3m10-11v11a1 1 0 01-1 1h-3m-4 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>';
    $iconPersetujuan = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-[17px] h-[17px]"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    $iconKeuangan = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-[17px] h-[17px]"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0l1.172-.879c1.171-.879 1.171-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    $iconNasabah = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-[17px] h-[17px]"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/></svg>';
    $iconMenu = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-[17px] h-[17px]"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>';

    $halamanMenu = [
        ['label' => 'Hub Persetujuan', 'url' => route('admin.persetujuan.index')],
        ['label' => 'Nasabah', 'url' => route('admin.nasabah.index')],
        ['label' => 'Registrasi Nasabah', 'url' => route('admin.registrasi.index')],
        ['label' => 'Verifikasi Nasabah', 'url' => route('admin.verifikasi.index')],
        ['label' => 'Produk', 'url' => route('admin.produk.index')],
        ['label' => 'Rekonsiliasi', 'url' => route('admin.rekonsiliasi.index')],
        ['label' => 'Kas Kolektor', 'url' => route('admin.kas-kolektor.index')],
        ['label' => 'Penarikan', 'url' => route('admin.penarikan.index')],
        ['label' => 'Komisi', 'url' => route('admin.komisi.index')],
        ['label' => 'Kelola Kolektor', 'url' => route('admin.kolektor.index')],
        ['label' => 'Catat Setoran', 'url' => route('admin.setoran.create')],
        ['label' => 'Serah Terima Paket', 'url' => route('serah-terima.index')],
        ['label' => 'Monitoring Setoran', 'url' => route('admin.monitoring-setoran.index')],
        ['label' => 'Monitoring Absensi', 'url' => route('admin.monitoring-absensi.index')],
        ['label' => 'Komplain', 'url' => route('admin.komplain.index')],
        ['label' => 'Nasabah Bermasalah', 'url' => route('admin.bermasalah.index')],
        ['label' => 'Laporan', 'url' => route('admin.laporan.index')],
        ['label' => 'Log Aktivitas', 'url' => route('admin.log.index')],
        ['label' => 'Pengaturan', 'url' => route('admin.pengaturan.index')],
    ];
@endphp

<div x-data="{ bukaMenu: false }" data-test="bottom-nav-admin"
     class="fixed inset-x-0 bottom-0 z-40 lg:hidden">

    {{-- Latar belakang sheet --}}
    <div class="absolute inset-0 bg-navy-950/45" x-show="bukaMenu" style="display:none"
         @click="bukaMenu = false"></div>

    {{-- Sheet menu (di atas tab) --}}
    <div x-show="bukaMenu" style="display:none" data-test="sheet-menu"
         class="absolute inset-x-0 bottom-full mb-2 px-3">
        <div class="max-h-[70vh] overflow-y-auto rounded-3xl bg-white dark:bg-zinc-800 p-4 shadow-2xl border border-zinc-200 dark:border-zinc-700">
            <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-slate-500">Semua Halaman</p>
            <div class="grid grid-cols-2 gap-1.5">
                @foreach ($halamanMenu as $m)
                    <a href="{{ $m['url'] }}" wire:navigate @click="bukaMenu = false"
                       class="rounded-xl px-3 py-2.5 text-xs font-medium text-zinc-700 dark:text-slate-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                        {{ $m['label'] }}
                    </a>
                @endforeach
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3 border-t border-zinc-200 dark:border-zinc-700 pt-3">
                @csrf
                <button type="submit"
                        class="w-full rounded-xl px-3 py-2.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                    Logout
                </button>
            </form>
        </div>
    </div>

    {{-- Bar bawah dengan safe-area --}}
    <div class="px-3" style="padding-bottom: calc(env(safe-area-inset-bottom) + 0.75rem)">
        <div class="flex items-center gap-1 bg-[#171717]/95 dark:bg-zinc-950/95 backdrop-blur-xl rounded-full p-1.5 shadow-[0_8px_32px_rgba(0,0,0,0.3)] border border-white/10 dark:border-zinc-800">
            <a href="{{ route('dashboard') }}" wire:navigate data-test="tab-beranda"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 py-2 rounded-full transition active:scale-95 {{ $gayaTab($aktifBeranda) }}">
                <span class="shrink-0">{!! $iconBeranda !!}</span>
                <span class="text-[9px] font-semibold whitespace-nowrap">Beranda</span>
            </a>

            <a href="{{ route('admin.persetujuan.index') }}" wire:navigate data-test="tab-persetujuan"
               class="relative flex-1 flex flex-col items-center justify-center gap-0.5 py-2 rounded-full transition active:scale-95 {{ $gayaTab($aktifPersetujuan) }}">
                <span class="shrink-0">{!! $iconPersetujuan !!}</span>
                <span class="text-[9px] font-semibold whitespace-nowrap">Persetujuan</span>
                @if ($totalAntrean > 0)
                    <span class="absolute -top-0.5 right-2 min-w-[16px] rounded-full bg-rose-500 px-1 text-center text-[9px] font-bold leading-4 text-white shadow" data-test="bottom-nav-badge">{{ $totalAntrean }}</span>
                @endif
            </a>

            <a href="{{ route('admin.rekonsiliasi.index') }}" wire:navigate data-test="tab-keuangan"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 py-2 rounded-full transition active:scale-95 {{ $gayaTab($aktifKeuangan) }}">
                <span class="shrink-0">{!! $iconKeuangan !!}</span>
                <span class="text-[9px] font-semibold whitespace-nowrap">Keuangan</span>
            </a>

            <a href="{{ route('admin.nasabah.index') }}" wire:navigate data-test="tab-nasabah"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 py-2 rounded-full transition active:scale-95 {{ $gayaTab($aktifNasabah) }}">
                <span class="shrink-0">{!! $iconNasabah !!}</span>
                <span class="text-[9px] font-semibold whitespace-nowrap">Nasabah</span>
            </a>

            <button type="button" @click="bukaMenu = !bukaMenu" data-test="tab-menu"
                    class="flex-1 flex flex-col items-center justify-center gap-0.5 py-2 rounded-full transition active:scale-95 {{ $gayaTab(false) }}">
                <span class="shrink-0">{!! $iconMenu !!}</span>
                <span class="text-[9px] font-semibold whitespace-nowrap">Menu</span>
            </button>
        </div>
    </div>
</div>
