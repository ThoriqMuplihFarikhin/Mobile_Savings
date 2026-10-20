@props([
    'title' => null,
    'portal' => 'nasabah',
])

@php
    $aksenPortal = \App\Support\PortalLogin::aksen($portal);
    $labelPortal = \App\Support\PortalLogin::labelPortal($portal);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-scheme="light">
    <head>
        @include('partials.head')
        <script>
            try {
                if (localStorage.getItem('td-text-large') === '1') {
                    document.documentElement.classList.add('text-enlarged');
                }
            } catch (e) {}
        </script>
    </head>
    <body class="min-h-screen bg-canvas-warm text-ink antialiased">
        <!-- Bar Aksesibilitas Atas (Tombol A+) -->
        <header class="absolute top-4 right-4 z-50 flex items-center gap-2">
            <button type="button"
                    onclick="window.toggleUkuranTeks()"
                    id="btn-toggle-teks"
                    aria-label="Perbesar ukuran teks"
                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-full border border-hairline-strong bg-surface px-3 text-xs font-semibold text-body transition hover:bg-surface-inset hover:text-ink focus:outline-none focus:ring-2 focus:ring-primary/20">
                <span class="text-sm font-bold">A+</span>
                <span class="hidden sm:inline text-[11px] text-mute">Teks</span>
            </button>
        </header>

        <script>
            window.toggleUkuranTeks = function () {
                try {
                    const el = document.documentElement;
                    const aktif = el.classList.toggle('text-enlarged');
                    localStorage.setItem('td-text-large', aktif ? '1' : '0');
                } catch (e) {}
            };
        </script>

        <!-- Container Utama: 2 Kolom pada Layar >= 900px, 1 Kolom pada Ponsel -->
        <div class="min-h-screen min-[900px]:grid min-[900px]:grid-cols-2 min-[900px]:items-stretch">
            <!-- Kolom Kiri / Header Ponsel: Panel Brand & Ilustrasi -->
            <aside class="relative flex flex-col justify-center border-b border-hairline bg-canvas-warm sm:rounded-b-[2.5rem] min-[900px]:border-b-0 min-[900px]:border-r min-[900px]:rounded-none">
                <x-auth.brand-panel :portal="$portal" class="h-full" />
            </aside>

            <!-- Kolom Kanan: Kartu Form Login -->
            <main class="relative flex flex-col items-center justify-center p-4 sm:p-8 min-[900px]:p-12">
                <div class="w-full max-w-md rounded-2xl border border-hairline bg-surface p-6 sm:p-8 shadow-none">
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
