@php
    $totalAntrean = \App\Support\AntreanPersetujuan::total();

    $aktifBeranda = request()->routeIs('dashboard');
    $aktifPersetujuan = request()->routeIs('admin.persetujuan.*');
    $aktifKeuangan = request()->routeIs('admin.rekonsiliasi.*')
        || request()->routeIs('admin.kas-kolektor.*')
        || request()->routeIs('admin.komisi.*')
        || request()->routeIs('admin.produk.*')
        || request()->routeIs('admin.penarikan.*')
        || request()->routeIs('admin.tinjauan-penarikan-offline');
    $aktifNasabah = request()->routeIs('admin.nasabah.*')
        || request()->routeIs('admin.registrasi.*')
        || request()->routeIs('admin.verifikasi.*')
        || request()->routeIs('admin.bermasalah.*');

    $gayaTab = fn (bool $aktif): string => $aktif
        ? 'bg-primary-soft text-on-primary-soft font-semibold shadow-xs'
        : 'text-mute hover:text-ink';

    $iconBeranda = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-[18px]"><path stroke-linecap="round" stroke-linejoin="round" d="M3 11l9-8 9 8v9a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1z"/></svg>';
    $iconPersetujuan = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-[18px]"><path stroke-linecap="round" stroke-linejoin="round" d="M22 12h-6l-2 3h-4l-2-3H2M5 5h14l3 7v6a2 2 0 01-2 2H4a2 2 0 01-2-2v-6z"/></svg>';
    $iconKeuangan = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-[18px]"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/></svg>';
    $iconNasabah = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-[18px]"><circle cx="9" cy="8" r="3.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 20a7 7 0 0114 0M17 4.5a3.5 3.5 0 010 7M22 20a6 6 0 00-4-5.6"/></svg>';
    $iconMenu = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-[18px]"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>';

    $grupMenu = [
        'Beranda' => [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Hub Persetujuan', 'url' => route('admin.persetujuan.index')],
        ],
        'Nasabah' => [
            ['label' => 'Manajemen Nasabah', 'url' => route('admin.nasabah.index')],
            ['label' => 'Registrasi Nasabah', 'url' => route('admin.registrasi.index')],
            ['label' => 'Verifikasi Nasabah', 'url' => route('admin.verifikasi.index')],
            ['label' => 'Nasabah Bermasalah', 'url' => route('admin.bermasalah.index')],
        ],
        'Keuangan' => [
            ['label' => 'Approval Penarikan', 'url' => route('admin.penarikan.index')],
            ['label' => 'Tinjauan Penarikan Offline', 'url' => route('admin.tinjauan-penarikan-offline')],
            ['label' => 'Rekonsiliasi Kas', 'url' => route('admin.rekonsiliasi.index')],
            ['label' => 'Kas di Tangan Kolektor', 'url' => route('admin.kas-kolektor.index')],
            ['label' => 'Komisi', 'url' => route('admin.komisi.index')],
            ['label' => 'Produk Tabungan', 'url' => route('admin.produk.index')],
        ],
        'Operasional' => [
            ['label' => 'Kelola Kolektor', 'url' => route('admin.kolektor.index')],
            ['label' => 'Handover Kolektor', 'url' => route('admin.handover.index')],
            ['label' => 'Catat Setoran', 'url' => route('admin.setoran.create')],
            ['label' => 'Monitoring Setoran', 'url' => route('admin.monitoring-setoran.index')],
            ['label' => 'Monitoring Absensi', 'url' => route('admin.monitoring-absensi.index')],
            ['label' => 'Serah Terima Paket', 'url' => route('serah-terima.index')],
            ['label' => 'Komplain', 'url' => route('admin.komplain.index')],
        ],
        'Sistem' => [
            ['label' => 'Laporan', 'url' => route('admin.laporan.index')],
            ['label' => 'Log Aktivitas', 'url' => route('admin.log.index')],
            ['label' => 'Pengaturan', 'url' => route('admin.pengaturan.index')],
        ],
    ];
@endphp

<div x-data="{ bukaMenu: false }" data-test="bottom-nav-admin"
     class="fixed inset-x-0 bottom-0 z-40 lg:hidden">

    {{-- Backdrop Sheet Menu --}}
    <div class="fixed inset-0 bg-black/45 transition-opacity cursor-pointer"
         x-show="bukaMenu"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display:none"
         @click="bukaMenu = false"></div>

    {{-- Sheet menu (di atas tab bottom-nav) --}}
    <div x-show="bukaMenu"
         x-transition:enter="transform transition ease-out duration-250"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         style="display:none"
         data-test="sheet-menu"
         class="absolute inset-x-0 bottom-full mb-2 px-3">
        <div class="max-h-[75vh] overflow-y-auto rounded-[20px] bg-surface p-4 shadow-2xl border border-hairline space-y-4">
            <div class="flex items-center justify-between border-b border-hairline pb-2.5">
                <span class="text-xs font-semibold text-ink">Semua Menu Admin</span>
                <button type="button" @click="bukaMenu = false"
                        class="size-7 rounded-[8px] flex items-center justify-center text-mute hover:bg-surface-2 hover:text-ink cursor-pointer"
                        aria-label="Tutup menu">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            @foreach ($grupMenu as $grup => $items)
                <div class="space-y-1.5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.06em] text-mute">{{ $grup }}</p>
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach ($items as $m)
                            <a href="{{ $m['url'] }}" wire:navigate @click="bukaMenu = false"
                               class="rounded-[10px] px-2.5 py-2 text-xs font-medium text-body hover:bg-surface-2 transition">
                                {{ $m['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <form method="POST" action="{{ route('logout') }}" class="pt-3 border-t border-hairline">
                @csrf
                <button type="submit"
                        class="w-full rounded-[10px] px-3 py-2.5 text-xs font-semibold text-danger hover:bg-danger-soft transition text-center cursor-pointer">
                    Keluar Akun
                </button>
            </form>
        </div>
    </div>

    {{-- Bar bawah dengan safe-area --}}
    <div class="px-3" style="padding-bottom: calc(env(safe-area-inset-bottom) + 0.75rem)">
        <div class="flex items-center gap-1 bg-surface border border-hairline rounded-[16px] p-1.5 shadow-lg transition-colors">
            <a href="{{ route('dashboard') }}" wire:navigate data-test="tab-beranda"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 py-1.5 rounded-[10px] transition active:scale-95 {{ $gayaTab($aktifBeranda) }}">
                <span class="shrink-0">{!! $iconBeranda !!}</span>
                <span class="whitespace-nowrap text-[11px] font-semibold">Beranda</span>
            </a>

            <a href="{{ route('admin.persetujuan.index') }}" wire:navigate data-test="tab-persetujuan"
               class="relative flex-1 flex flex-col items-center justify-center gap-0.5 py-1.5 rounded-[10px] transition active:scale-95 {{ $gayaTab($aktifPersetujuan) }}">
                <span class="shrink-0">{!! $iconPersetujuan !!}</span>
                <span class="whitespace-nowrap text-[11px] font-semibold">Persetujuan</span>
                @if ($totalAntrean > 0)
                    <span class="absolute -top-1 right-2 min-w-[18px] h-[18px] rounded-full bg-warning-soft px-1 text-center text-[10px] font-bold leading-[18px] text-warning-deep border border-warning/20" data-test="bottom-nav-badge">{{ $totalAntrean }}</span>
                @endif
            </a>

            <a href="{{ route('admin.rekonsiliasi.index') }}" wire:navigate data-test="tab-keuangan"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 py-1.5 rounded-[10px] transition active:scale-95 {{ $gayaTab($aktifKeuangan) }}">
                <span class="shrink-0">{!! $iconKeuangan !!}</span>
                <span class="whitespace-nowrap text-[11px] font-semibold">Keuangan</span>
            </a>

            <a href="{{ route('admin.nasabah.index') }}" wire:navigate data-test="tab-nasabah"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 py-1.5 rounded-[10px] transition active:scale-95 {{ $gayaTab($aktifNasabah) }}">
                <span class="shrink-0">{!! $iconNasabah !!}</span>
                <span class="whitespace-nowrap text-[11px] font-semibold">Nasabah</span>
            </a>

            <button type="button" @click="bukaMenu = !bukaMenu" data-test="tab-menu"
                    class="flex-1 flex flex-col items-center justify-center gap-0.5 py-1.5 rounded-[10px] transition active:scale-95 {{ $gayaTab(false) }} cursor-pointer">
                <span class="shrink-0">{!! $iconMenu !!}</span>
                <span class="whitespace-nowrap text-[11px] font-semibold">Menu</span>
            </button>
        </div>
    </div>
</div>
