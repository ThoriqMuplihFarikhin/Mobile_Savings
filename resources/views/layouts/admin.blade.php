@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if ($portalHtml = \App\Support\PortalLogin::dariPeran(auth()->user()?->role)) data-portal="{{ $portalHtml }}" @endif>
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-canvas text-ink font-sans transition-colors antialiased">
        <div class="flex h-screen overflow-hidden">
            <x-admin.sidebar />

            <div class="flex min-w-0 flex-1 flex-col">
                <x-admin.topbar :title="$title" />

                <main class="flex-1 overflow-y-auto px-4 md:px-7 pt-5 md:pt-6 pb-28 lg:pb-8 max-w-[1240px] mx-auto w-full">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @if(auth()->user()?->isAdmin())
            @include('layouts.app.admin-bottom-nav')
        @endif

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        <x-ui.modal-struk />
        <x-ui.modal-foto-bukti />

        @fluxScripts
    </body>
</html>
