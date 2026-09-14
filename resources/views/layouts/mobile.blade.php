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
                $isKolektor = auth()->user()->isKolektor();

                $sudahAbsenHariIni = false;
                if ($isKolektor) {
                    $sudahAbsenHariIni = \App\Models\AbsensiKolektor::where('kolektor_id', auth()->id())
                        ->where('tanggal', today())
                        ->whereNotNull('waktu_masuk')
                        ->exists();
                }

                $iconHome = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-[18px] h-[18px]"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>';
                $iconReceipt = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-[18px] h-[18px]"><path d="M9 14l2 2 4-4m5.5 4.5V6a2 2 0 00-2-2H7a2 2 0 00-2 2v14l3-3 2.5 2.5L13 17l2.5 2.5L18 17l3.5 3.5z"/></svg>';
                $iconPackage = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-[18px] h-[18px]"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';
                $iconUser = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-[18px] h-[18px]"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"/></svg>';
                $iconCirclePlus = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-[18px] h-[18px]"><circle cx="12" cy="12" r="9"/><path d="M12 8v8m-4-4h8"/></svg>';
                $iconUsers = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-[18px] h-[18px]"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>';

                $items = $isKolektor ? [
                    ['url' => route('dashboard'), 'icon' => $iconHome, 'label' => 'Beranda', 'active' => 'dashboard'],
                    ['url' => route('kolektor.setoran.index'), 'icon' => $iconCirclePlus, 'label' => 'Setor', 'active' => 'kolektor.setoran.*'],
                    ['url' => route('kolektor.nasabah.index'), 'icon' => $iconUsers, 'label' => 'Nasabah', 'active' => 'kolektor.nasabah.*'],
                    ['url' => route('kolektor.pengaturan.index'), 'icon' => $iconUser, 'label' => 'Profil', 'active' => 'kolektor.pengaturan.*'],
                ] : [
                    ['url' => route('dashboard'), 'icon' => $iconHome, 'label' => 'Beranda', 'active' => 'dashboard'],
                    ['url' => route('nasabah.riwayat-tabungan.index'), 'icon' => $iconReceipt, 'label' => 'Riwayat', 'active' => 'nasabah.riwayat-tabungan.*'],
                    ['url' => route('nasabah.progres-paket.index'), 'icon' => $iconPackage, 'label' => 'Paket', 'active' => 'nasabah.progres-paket.*'],
                    ['url' => route('nasabah.pengaturan.index'), 'icon' => $iconUser, 'label' => 'Profil', 'active' => 'nasabah.pengaturan.*'],
                ];
            @endphp
            <div class="fixed bottom-3 left-1/2 -translate-x-1/2 w-full max-w-[480px] px-4 z-40">
                @include('layouts.app.bottom-nav', ['isKolektor' => $isKolektor, 'sudahAbsenHariIni' => $sudahAbsenHariIni])
            </div>
        @endif
    </div>

    @fluxScripts
</body>
</html>
