<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Tabungan Digital</span>
                </div>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                @if(auth()->user()->isAdmin())
                    <flux:sidebar.group :heading="__('Admin')" class="grid">
                        <flux:sidebar.item icon="house" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" href="/admin/nasabah" :current="request()->routeIs('admin.nasabah.*')" wire:navigate>
                            {{ __('Nasabah') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="user-plus" href="/admin/registrasi" :current="request()->routeIs('admin.registrasi.*')" wire:navigate>
                            {{ __('Registrasi Nasabah') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="package" href="/admin/produk" :current="request()->routeIs('admin.produk.*')" wire:navigate>
                            {{ __('Produk') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="arrow-down-to-line" href="/admin/rekonsiliasi" :current="request()->routeIs('admin.rekonsiliasi.*')" wire:navigate>
                            {{ __('Rekonsiliasi') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="wallet" href="/admin/kas-kolektor" :current="request()->routeIs('admin.kas-kolektor.*')" wire:navigate>
                            {{ __('Kas Kolektor') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="arrow-up-from-line" href="/admin/penarikan" :current="request()->routeIs('admin.penarikan.*')" wire:navigate>
                            {{ __('Penarikan') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="banknote" href="/admin/komisi" :current="request()->routeIs('admin.komisi.*')" wire:navigate>
                            {{ __('Komisi') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="megaphone" href="/admin/komplain" :current="request()->routeIs('admin.komplain.*')" wire:navigate>
                            {{ __('Komplain') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="file-text" href="/admin/laporan" :current="request()->routeIs('admin.laporan.*')" wire:navigate>
                            {{ __('Laporan') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard-list" href="/admin/log" :current="request()->routeIs('admin.log.*')" wire:navigate>
                            {{ __('Log Aktivitas') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" href="/admin/kolektor" :current="request()->routeIs('admin.kolektor.*')" wire:navigate>
                            {{ __('Kelola Kolektor') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="arrow-down-to-line" href="/admin/monitoring-setoran" :current="request()->routeIs('admin.monitoring-setoran.*')" wire:navigate>
                            {{ __('Monitoring Setoran') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clipboard" href="/admin/monitoring-absensi" :current="request()->routeIs('admin.monitoring-absensi.*')" wire:navigate>
                            {{ __('Monitoring Absensi') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="megaphone" href="/admin/bermasalah" :current="request()->routeIs('admin.bermasalah.*')" wire:navigate>
                            {{ __('Nasabah Bermasalah') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="settings" href="/admin/pengaturan" :current="request()->routeIs('admin.pengaturan.*')" wire:navigate>
                            {{ __('Pengaturan') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @elseif(auth()->user()->isKolektor())
                    <flux:sidebar.group :heading="__('Kolektor')" class="grid">
                        <flux:sidebar.item icon="house" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" href="/kolektor/nasabah" :current="request()->routeIs('kolektor.nasabah.*')" wire:navigate>
                            {{ __('Nasabah Binaan') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="user-plus" href="/kolektor/daftar-nasabah" :current="request()->routeIs('kolektor.daftar-nasabah.*')" wire:navigate>
                            {{ __('Daftarkan Nasabah') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="circle-plus" href="/kolektor/setoran" :current="request()->routeIs('kolektor.setoran.*')" wire:navigate>
                            {{ __('Input Setoran') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="calendar" href="/kolektor/jadwal" :current="request()->routeIs('kolektor.jadwal.*')" wire:navigate>
                            {{ __('Jadwal Kunjungan') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="banknote" href="/kolektor/setor-kantor" :current="request()->routeIs('kolektor.setor-kantor.*')" wire:navigate>
                            {{ __('Setor ke Kantor') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="arrow-up-from-line" href="/kolektor/penarikan-offline" :current="request()->routeIs('kolektor.penarikan-offline.*')" wire:navigate>
                            {{ __('Penarikan Offline') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="shield-check" href="/kolektor/verifikasi-penarikan" :current="request()->routeIs('kolektor.verifikasi-penarikan.*')" wire:navigate>
                            {{ __('Verifikasi Penarikan') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @else
                    <flux:sidebar.group :heading="__('Nasabah')" class="grid">
                        <flux:sidebar.item icon="house" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="wallet" href="/nasabah/saldo" :current="request()->routeIs('nasabah.saldo.*')" wire:navigate>
                            {{ __('Saldo Saya') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clock" href="/nasabah/riwayat" :current="request()->routeIs('nasabah.riwayat.*')" wire:navigate>
                            {{ __('Riwayat Penarikan') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="clock" href="/nasabah/riwayat-tabungan" :current="request()->routeIs('nasabah.riwayat-tabungan.*')" wire:navigate>
                            {{ __('Riwayat Tabungan') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="package" href="/nasabah/progres-paket" :current="request()->routeIs('nasabah.progres-paket.*')" wire:navigate>
                            {{ __('Progres Paket') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="arrow-down-to-line" href="/nasabah/penarikan" :current="request()->routeIs('nasabah.penarikan.*')" wire:navigate>
                            {{ __('Penarikan') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="megaphone" href="/nasabah/komplain" :current="request()->routeIs('nasabah.komplain.*')" wire:navigate>
                            {{ __('Komplain') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="bell" href="/nasabah/notifikasi" :current="request()->routeIs('nasabah.notifikasi.*')" wire:navigate>
                            {{ __('Notifikasi') }}
                            @php
                                $unreadCount = \App\Models\LogNotifikasi::where('nasabah_id', auth()->id())
                                    ->where('is_read', false)
                                    ->count();
                            @endphp
                            @if($unreadCount > 0)
                                <span class="ml-auto inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-[#ee0000] px-1 font-mono text-[10px] font-medium text-white">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                            @endif
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="cog-6-tooth" href="/nasabah/pengaturan" :current="request()->routeIs('nasabah.pengaturan.*')" wire:navigate>
                            {{ __('Pengaturan') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-md text-sm w-full
                               text-sidebar-text hover:text-navy-900 dark:hover:text-white transition-colors cursor-pointer">
                        {{ __('Logout') }}
                    </button>
                </form>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="menu" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->no_hp }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
