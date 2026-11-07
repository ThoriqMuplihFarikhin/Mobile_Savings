{{-- Overlay (mobile drawer backdrop) --}}
<div x-data x-show="$store.ui.sidebarOpen" x-transition.opacity
     @click="$store.ui.closeSidebar()"
     class="fixed inset-0 bg-black/45 z-30 lg:hidden"></div>

<aside x-data
       :class="$store.ui.sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed lg:sticky top-0 left-0 h-screen w-[268px] shrink-0 z-40 flex flex-col
              bg-surface text-ink border-r border-hairline transition-transform duration-250 ease-in-out">

    {{-- Brand --}}
    <div class="flex items-center gap-2.5 px-4 py-3.5 border-b border-hairline">
        <div class="flex size-[34px] items-center justify-center rounded-[10px] bg-primary text-white font-semibold text-sm shrink-0 shadow-xs">
            KD
        </div>
        <div class="min-w-0 flex-1">
            <span class="block text-sm font-semibold tracking-tight text-ink leading-tight truncate">
                KawalDuit
            </span>
        </div>
        <x-peran-chip class="shrink-0 text-[10px]" />
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-2 space-y-1 [scrollbar-width:thin] [scrollbar-color:var(--color-hairline)_transparent] [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-hairline [&::-webkit-scrollbar-thumb]:rounded-full hover:[&::-webkit-scrollbar-thumb]:bg-mute/40">
        @if(auth()->user()->isAdmin())
            @php
                $antreanNav = \App\Support\AntreanPersetujuan::ringkas();
                $totalAntreanNav = array_sum($antreanNav);
            @endphp

            {{-- 1. Beranda --}}
            <x-admin.nav-section label="Beranda" />
            <x-admin.nav-item href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 11l9-8 9 8v9a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1z" />
                </svg>
                <span>Dashboard</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.persetujuan.index') }}" :active="request()->routeIs('admin.persetujuan.*')" :badge="$totalAntreanNav">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 12h-6l-2 3h-4l-2-3H2M5 5h14l3 7v6a2 2 0 01-2 2H4a2 2 0 01-2-2v-6z" />
                </svg>
                <span>Hub Persetujuan</span>
            </x-admin.nav-item>

            {{-- 2. Nasabah --}}
            <x-admin.nav-section label="Nasabah" />
            <x-admin.nav-item href="{{ route('admin.nasabah.index') }}" :active="request()->routeIs('admin.nasabah.*')">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="8" r="3.5" /><path stroke-linecap="round" stroke-linejoin="round" d="M2 20a7 7 0 0114 0M17 4.5a3.5 3.5 0 010 7M22 20a6 6 0 00-4-5.6" />
                </svg>
                <span>Manajemen Nasabah</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.registrasi.index') }}" :active="request()->routeIs('admin.registrasi.*')">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="8" r="4" /><path stroke-linecap="round" stroke-linejoin="round" d="M2 21a7 7 0 0114 0M19 8v6M16 11h6" />
                </svg>
                <span>Registrasi Nasabah</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.verifikasi.index') }}" :active="request()->routeIs('admin.verifikasi.*')" :badge="$antreanNav['verifikasi']">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 6L9 17l-5-5" />
                </svg>
                <span>Verifikasi Nasabah</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.bermasalah.index') }}" :active="request()->routeIs('admin.bermasalah.*')" :badge="$antreanNav['bermasalah']">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l10 18H2zM12 10v5M12 18h.01" />
                </svg>
                <span>Nasabah Bermasalah</span>
            </x-admin.nav-item>

            {{-- 3. Keuangan --}}
            <x-admin.nav-section label="Keuangan" />
            <x-admin.nav-item href="{{ route('admin.penarikan.index') }}" :active="request()->routeIs('admin.penarikan.*')" :badge="$antreanNav['penarikan']">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="6" width="20" height="12" rx="2" /><circle cx="12" cy="12" r="3" />
                </svg>
                <span>Approval Penarikan</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.tinjauan-penarikan-offline') }}" :active="request()->routeIs('admin.tinjauan-penarikan-offline*')" :badge="$antreanNav['tinjauan']">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                </svg>
                <span>Tinjauan Penarikan Offline</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.rekonsiliasi.index') }}" :active="request()->routeIs('admin.rekonsiliasi.*')" :badge="$antreanNav['setoran_kantor']">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M5 7h14M5 7l-3 7a4 4 0 006 0zM19 7l-3 7a4 4 0 006 0z" />
                </svg>
                <span>Rekonsiliasi Kas</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.kas-kolektor.index') }}" :active="request()->routeIs('admin.kas-kolektor.*')">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h13v4M3 7v11a2 2 0 002 2h15V9H5a2 2 0 01-2-2zM16 14h2" />
                </svg>
                <span>Kas di Tangan Kolektor</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.komisi.index') }}" :active="request()->routeIs('admin.komisi.*')">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M14.5 9.5a3 3 0 00-5 1c0 3 5 1.5 5 4.5a3 3 0 01-5 1M12 6v2M12 16v2" />
                </svg>
                <span>Komisi</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.produk.index') }}" :active="request()->routeIs('admin.produk.*')">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8l-9-5-9 5v8l9 5 9-5zM3 8l9 5 9-5M12 13v8" />
                </svg>
                <span>Produk Tabungan</span>
            </x-admin.nav-item>

            {{-- 4. Operasional --}}
            <x-admin.nav-section label="Operasional" />
            <x-admin.nav-item href="{{ route('admin.kolektor.index') }}" :active="request()->routeIs('admin.kolektor.*')">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="8" r="3.5" /><path stroke-linecap="round" stroke-linejoin="round" d="M2 20a7 7 0 0114 0M17 4.5a3.5 3.5 0 010 7M22 20a6 6 0 00-4-5.6" />
                </svg>
                <span>Kelola Kolektor</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.handover.index') }}" :active="request()->routeIs('admin.handover.*')">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 4l-4 4 4 4M3 8h14M17 20l4-4-4-4M21 16H7" />
                </svg>
                <span>Handover Kolektor</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.setoran.create') }}" :active="request()->routeIs('admin.setoran.*')">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                </svg>
                <span>Catat Setoran</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.monitoring-setoran.index') }}" :active="request()->routeIs('admin.monitoring-setoran.*')">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z" /><circle cx="12" cy="12" r="3" />
                </svg>
                <span>Monitoring Setoran</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.monitoring-absensi.index') }}" :active="request()->routeIs('admin.monitoring-absensi.*')" :badge="$antreanNav['izin']">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="5" width="18" height="16" rx="2" /><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M8 3v4M16 3v4" />
                </svg>
                <span>Monitoring Absensi</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('serah-terima.index') }}" :active="request()->routeIs('serah-terima.*')">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="8" width="18" height="4" rx="1" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13M5 12v8a1 1 0 001 1h12a1 1 0 001-1v-8M8 8a2.5 2.5 0 114-2 2.5 2.5 0 114 2" />
                </svg>
                <span>Serah Terima Paket</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.komplain.index') }}" :active="request()->routeIs('admin.komplain.*')" :badge="$antreanNav['komplain']">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a8 8 0 01-11.5 7.2L3 21l1.8-6A8 8 0 1121 12z" />
                </svg>
                <span>Komplain</span>
            </x-admin.nav-item>

            {{-- 5. Sistem --}}
            <x-admin.nav-section label="Sistem" />
            <x-admin.nav-item href="{{ route('admin.laporan.index') }}" :active="request()->routeIs('admin.laporan.*')">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 3H6a1 1 0 00-1 1v16a1 1 0 001 1h12a1 1 0 001-1V8z" /><path stroke-linecap="round" stroke-linejoin="round" d="M14 3v5h5M9 13h6M9 17h6" />
                </svg>
                <span>Laporan</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.log.index') }}" :active="request()->routeIs('admin.log.*')">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
                </svg>
                <span>Log Aktivitas</span>
            </x-admin.nav-item>
            <x-admin.nav-item href="{{ route('admin.pengaturan.index') }}" :active="request()->routeIs('admin.pengaturan.*') || request()->routeIs('admin.settings.*')">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3" /><path stroke-linecap="round" stroke-linejoin="round" d="M19 12a7 7 0 00-.1-1.2l2-1.5-2-3.5-2.3 1a7 7 0 00-2-1.2L14.3 3h-4l-.4 2.6a7 7 0 00-2 1.2l-2.3-1-2 3.5 2 1.5A7 7 0 005 12c0 .4 0 .8.1 1.2l-2 1.5 2 3.5 2.3-1a7 7 0 002 1.2l.4 2.6h4l.4-2.6a7 7 0 002-1.2l2.3 1 2-3.5-2-1.5c.1-.4.1-.8.1-1.2z" />
                </svg>
                <span>Pengaturan</span>
            </x-admin.nav-item>
        @endif
    </nav>

    {{-- Logout di Bawah Sidebar --}}
    <div class="p-3 border-t border-hairline shrink-0">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-[10px] text-[13.5px] min-h-[38px] text-mute hover:bg-surface-2 hover:text-ink transition-colors cursor-pointer">
                <svg class="size-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" />
                </svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>
