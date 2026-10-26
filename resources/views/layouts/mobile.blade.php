<!DOCTYPE html>
<html lang="id" @if ($portalHtml = \App\Support\PortalLogin::dariPeran(auth()->user()?->role)) data-portal="{{ $portalHtml }}" @endif x-bind:class="$flux.isDark && 'dark'">
<head>
    @include('partials.head')
</head>
<body class="bg-canvas min-h-screen dark:bg-zinc-950">
    {{-- Kanvas hangat (DESIGN.md §2/§4): kolom 480px memakai canvas-warm supaya kartu
         putih di dalamnya punya latar. Kolom juga diberi perlakuan kartu §2/§5 level 1:
         border hairline-warm, radius 20px, bayangan 0 1px 2px rgba(20,83,45,.06). --}}
    <div class="mx-auto max-w-[480px] min-h-screen overflow-hidden border border-hairline-warm rounded-[20px] bg-canvas-warm shadow-[0_1px_2px_rgba(20,83,45,.06)] relative flex flex-col">
        {{-- Pita identitas portal 4px (DESIGN.md §1): di layar ini hanya kolektor;
             pita admin ditambahkan pada layout admin. --}}
        @if (auth()->user()?->isKolektor())
            <div class="h-1 shrink-0 bg-portal-kolektor" data-test="pita-portal" aria-hidden="true"></div>
        @endif

        {{-- Chip peran portal (D19 butir 4) --}}
        <div class="flex justify-end px-4 pt-3">
            <x-peran-chip />
        </div>
        <main class="flex-1 overflow-y-auto pb-[calc(96px_+_env(safe-area-inset-bottom))] px-4 pt-4">
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

                $iconHome = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-6"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>';
                $iconReceipt = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-6"><path d="M9 14l2 2 4-4m5.5 4.5V6a2 2 0 00-2-2H7a2 2 0 00-2 2v14l3-3 2.5 2.5L13 17l2.5 2.5L18 17l3.5 3.5z"/></svg>';
                $iconPackage = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-6"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>';
                $iconUser = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-6"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"/></svg>';
                $iconCirclePlus = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-6"><circle cx="12" cy="12" r="9"/><path d="M12 8v8m-4-4h8"/></svg>';
                $iconUsers = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-6"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>';

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
            <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] z-40">
                @include('layouts.app.bottom-nav', ['isKolektor' => $isKolektor, 'sudahAbsenHariIni' => $sudahAbsenHariIni])
            </div>
        @endif
    </div>

    @fluxScripts
</body>
</html>
