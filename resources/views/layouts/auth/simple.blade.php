<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#fafafa] text-[#171717] antialiased">
        <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden p-6">
            {{-- Atmospheric Mesh Backdrop — warna aksen portal (D19 butir 4) --}}
            <div class="pointer-events-none absolute -top-40 left-1/2 -z-10 h-[500px] w-[800px] -translate-x-1/2 opacity-25 blur-3xl"
                 style="background: radial-gradient(circle at 50% 30%, {{ filled($portal ?? null) ? \App\Support\PortalLogin::aksen($portal) : '#007cf0' }} 0%, #7928ca 50%, transparent 100%);"></div>

            <div class="w-full max-w-md">
                <div class="overflow-hidden rounded-2xl bg-white p-8 sm:p-10 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,0px_8px_16px_-4px_#0000000a,inset_0_0_0_1px_#ebebeb]">
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

