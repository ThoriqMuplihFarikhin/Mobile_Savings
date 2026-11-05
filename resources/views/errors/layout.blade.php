<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <title>@yield('title', 'Galat') - {{ config('app.name', 'KawalDuit') }}</title>

    <script>
        if (document.documentElement.getAttribute('data-scheme') !== 'light' &&
            (localStorage.getItem('td-theme') === 'dark' ||
             (!('td-theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches))) {
            document.documentElement.classList.add('dark');
        }
    </script>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface-inset text-ink antialiased flex flex-col justify-between dark:bg-zinc-950 dark:text-zinc-100">
    <div class="flex min-h-[90vh] flex-col items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-md text-center">
            {{-- Brand Header --}}
            <div class="mb-6 flex items-center justify-center gap-2">
                <div class="flex size-10 items-center justify-center rounded-xl bg-primary text-base font-bold text-white shadow-xs">
                    KD
                </div>
                <span class="text-base font-bold text-ink dark:text-white">{{ config('app.name', 'KawalDuit') }}</span>
            </div>

            {{-- Error Card --}}
            <div class="rounded-2xl border border-hairline/80 bg-surface p-6 sm:p-8 shadow-xs dark:bg-surface">
                {{-- Icon Badge --}}
                <div class="mx-auto mb-4 flex size-20 items-center justify-center rounded-full @yield('icon-bg', 'bg-inset text-mute')">
                    @yield('icon')
                </div>

                {{-- Status Code Chip --}}
                @hasSection('code')
                    <div class="mb-2">
                        <span class="inline-flex items-center rounded-full bg-surface-inset px-2.5 py-0.5 text-xs font-semibold text-mute dark:bg-zinc-800 dark:text-zinc-300">
                            Kode @yield('code')
                        </span>
                    </div>
                @endif

                {{-- Heading --}}
                <h1 class="text-lg font-bold text-ink dark:text-white">
                    @yield('heading')
                </h1>

                {{-- Message --}}
                <p class="mx-auto mt-2 max-w-xs text-xs leading-relaxed text-mute dark:text-zinc-400">
                    @yield('message')
                </p>

                {{-- Action Buttons --}}
                <div class="mt-6 space-y-2.5">
                    @yield('actions')
                </div>
            </div>

            {{-- Footer Info --}}
            <p class="mt-6 text-center text-xs text-mute dark:text-zinc-500">
                {{ config('app.name', 'KawalDuit') }} &middot; Layanan Tabungan Harian
            </p>
        </div>
    </div>
</body>
</html>
