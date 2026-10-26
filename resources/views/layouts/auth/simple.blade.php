<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-scheme="light">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-canvas text-ink antialiased">
        {{-- DESIGN.md §5: tanpa gradien, blur, atau bayangan tebal.
             Kartu auth memakai hairline saja. Halaman auth selalu terang. --}}
        <div class="flex min-h-screen flex-col items-center justify-center p-6">
            <div class="w-full max-w-md">
                {{-- §6: portal admin memakai keluarga bentuk 6-8px; portal mobile 12-16px. --}}
                <div class="border border-hairline bg-surface p-8 sm:p-10 {{ ($portal ?? null) === \App\Support\PortalLogin::ADMIN ? 'rounded-lg' : 'rounded-2xl' }}">
                    {{ $slot }}
                </div>
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
