<!DOCTYPE html>
<html lang="id" x-bind:class="$flux.isDark && 'dark'">
<head>
    @include('partials.head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4/dist/signature_pad.umd.min.js"></script>
</head>
<body class="bg-[#e5e5e5] min-h-screen dark:bg-zinc-900">
    <div class="mx-auto max-w-[480px] min-h-screen bg-white shadow-[0_0_40px_rgba(0,0,0,0.08)] relative flex flex-col dark:bg-zinc-800 dark:shadow-[0_0_40px_rgba(0,0,0,0.3)]">
        <main class="flex-1 overflow-y-auto pb-24 px-4 pt-4">
            {{ $slot }}
        </main>

        @if(auth()->user()->isNasabah() || auth()->user()->isKolektor())
            @php
                $iconHome = '<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>';
                $iconHomeFilled = '<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" /><path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15.75v-4.5a.75.75 0 00-.75-.75h-6a.75.75 0 00-.75.75v4.5H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a.75.75 0 00.091-.086L12 5.432z" /></svg>';
                $iconClock = '<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                $iconClockFilled = '<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V9z" clip-rule="evenodd" /></svg>';
                $iconPackage = '<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>';
                $iconPackageFilled = '<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.378 1.602a.75.75 0 00-.756 0 31.2 31.2 0 00-7.193 3.27.75.75 0 00-.22 1.483 29.736 29.736 0 012.015 4.274.75.75 0 001.416.368 29.97 29.97 0 002.615-4.065.75.75 0 00.22-1.483 31.2 31.2 0 00-7.193-3.27z" /><path d="M9.773 6.97a.75.75 0 01.017 1.05 22.404 22.404 0 002.02 3.47.75.75 0 01-1.19.9 23.904 23.904 0 01-2.232-3.82.75.75 0 01.038-.6z" /><path d="M14.24 6.97a.75.75 0 01.016 1.05 22.404 22.404 0 002.02 3.47.75.75 0 01-1.19.9 23.904 23.904 0 01-2.232-3.82.75.75 0 01.038-.6z" /><path d="M18.622 6.97a.75.75 0 01.017 1.05 22.404 22.404 0 002.02 3.47.75.75 0 01-1.19.9 23.904 23.904 0 01-2.232-3.82.75.75 0 01.038-.6z" /><path d="M5.94 8.472a.75.75 0 01.017 1.05 22.404 22.404 0 002.02 3.47.75.75 0 01-1.19.9 23.904 23.904 0 01-2.232-3.82.75.75 0 01.038-.6z" /><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" /></svg>';
                $iconUser = '<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>';
                $iconUserFilled = '<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" /></svg>';
                $iconCirclePlus = '<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                $iconCirclePlusFilled = '<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 9a.75.75 0 00-1.5 0v2.25H9a.75.75 0 000 1.5h2.25V15a.75.75 0 001.5 0v-2.25H15a.75.75 0 000-1.5h-2.25V9z" clip-rule="evenodd" /></svg>';
                $iconUsers = '<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>';
                $iconUsersFilled = '<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8.25 6.75a3.75 3.75 0 117.5 0 3.75 3.75 0 01-7.5 0zM15.75 9.75a3 3 0 116 0 3 3 0 01-6 0zM2.25 9.75a3 3 0 116 0 3 3 0 01-6 0zM6.31 15.117A6.745 6.745 0 0112 12a6.745 6.745 0 016.709 7.498.75.75 0 01-.372.568A12.696 12.696 0 0112 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 01-.372-.568 6.787 6.787 0 011.019-4.38z" /><path d="M5.082 14.254a8.287 8.287 0 00-1.308 5.135 9.687 9.687 0 01-1.764-.44l-.115-.04a.563.563 0 01-.373-.487l-.01-.121a3.75 3.75 0 013.57-4.047zM20.226 19.389a8.287 8.287 0 00-1.308-5.135 3.75 3.75 0 013.57 4.047l-.01.121a.563.563 0 01-.373.486l-.115.04c-.567.2-1.156.349-1.764.441z" /></svg>';
                $iconAbsen = '<svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>';

                $items = auth()->user()->isKolektor() ? [
                    ['url' => route('dashboard'), 'icon' => $iconHome, 'iconFilled' => $iconHomeFilled, 'label' => 'Beranda', 'active' => 'dashboard'],
                    ['url' => route('kolektor.setoran.index'), 'icon' => $iconCirclePlus, 'iconFilled' => $iconCirclePlusFilled, 'label' => 'Setor', 'active' => 'kolektor.setoran.*'],
                    ['url' => route('kolektor.absen.index'), 'icon' => $iconAbsen, 'label' => 'Absen', 'active' => 'kolektor.absen.*', 'floating' => true],
                    ['url' => route('kolektor.nasabah.index'), 'icon' => $iconUsers, 'iconFilled' => $iconUsersFilled, 'label' => 'Nasabah', 'active' => 'kolektor.nasabah.*'],
                    ['url' => route('kolektor.pengaturan.index'), 'icon' => $iconUser, 'iconFilled' => $iconUserFilled, 'label' => 'Profil', 'active' => 'kolektor.pengaturan.*'],
                ] : [
                    ['url' => route('dashboard'), 'icon' => $iconHome, 'iconFilled' => $iconHomeFilled, 'label' => 'Beranda', 'active' => 'dashboard'],
                    ['url' => route('nasabah.riwayat-tabungan.index'), 'icon' => $iconClock, 'iconFilled' => $iconClockFilled, 'label' => 'Riwayat', 'active' => 'nasabah.riwayat-tabungan.*'],
                    ['url' => route('nasabah.progres-paket.index'), 'icon' => $iconPackage, 'iconFilled' => $iconPackageFilled, 'label' => 'Paket', 'active' => 'nasabah.progres-paket.*'],
                    ['url' => route('nasabah.pengaturan.index'), 'icon' => $iconUser, 'iconFilled' => $iconUserFilled, 'label' => 'Profil', 'active' => 'nasabah.pengaturan.*'],
                ];
            @endphp
            @include('layouts.app.bottom-nav')
        @endif
    </div>

    @fluxScripts
</body>
</html>
