@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-surface-alt dark:bg-slate-950 text-text dark:text-white font-sans transition-colors">
        <div class="flex h-screen overflow-hidden">
            <x-admin.sidebar />

            <div class="flex min-w-0 flex-1 flex-col">
                <x-admin.topbar :title="$title" />

                <main class="flex-1 overflow-y-auto px-4 md:px-7 py-6 max-w-[1240px] mx-auto w-full">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
