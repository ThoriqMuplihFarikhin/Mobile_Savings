@props([
    'title' => null,
    'portal' => 'nasabah',
])

@php
    $aksenPortal = \App\Support\PortalLogin::aksen($portal);
    $labelPortal = \App\Support\PortalLogin::labelPortal($portal);
    $isKolektor = $portal === \App\Support\PortalLogin::KOLEKTOR;
    $warnaBgKiriHex = $isKolektor ? '#1a3e9c' : '#14472f';
    $kelasBgKiri = $isKolektor ? 'auth-bg-kolektor' : 'auth-bg-nasabah';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <script>
            try {
                if (localStorage.getItem('td-text-size') === 'big') {
                    document.documentElement.classList.add('big');
                }
            } catch (e) {}
        </script>
    </head>
    <body class="min-h-screen bg-canvas-warm antialiased selection:bg-primary-soft selection:text-on-primary-soft">
        <script>
            window.toggleUkuranTeks = function () {
                try {
                    const el = document.documentElement;
                    const aktif = el.classList.toggle('big');
                    localStorage.setItem('td-text-size', aktif ? 'big' : '');
                } catch (e) {}
            };
        </script>

        <!-- Container Utama: 2 Kolom pada Layar >= 900px, 1 Kolom pada Ponsel -->
        <div class="min-h-screen min-[900px]:grid min-[900px]:grid-cols-2 min-[900px]:items-stretch">
            <!-- Kolom Kiri / Header Ponsel: Panel Brand & Ilustrasi dengan Warna Identitas Portal -->
            <aside class="relative flex flex-col justify-between {{ $kelasBgKiri }} border-b border-white/10 min-[900px]:border-b-0 min-[900px]:border-r min-[900px]:border-white/10">
                <x-auth.brand-panel :portal="$portal" class="h-full" />
            </aside>

            <!-- Kolom Kanan: Panel Form Login Terang (D42: login selalu terang) -->
            <main class="relative flex flex-col items-center justify-center bg-surface p-6 sm:p-10 min-[900px]:p-12">
                <div class="w-full max-w-[420px] py-4">
                    {{ $slot }}
                </div>
            </main>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
