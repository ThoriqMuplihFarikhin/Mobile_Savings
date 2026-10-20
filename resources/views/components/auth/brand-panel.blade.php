@props([
    'portal' => 'nasabah',
])

@php
    $aksen = \App\Support\PortalLogin::aksen($portal);
    $labelPortal = \App\Support\PortalLogin::labelPortal($portal);
    $isKolektor = $portal === \App\Support\PortalLogin::KOLEKTOR;

    $headline = $isKolektor ? 'Pencatatan setoran cepat & akurat' : 'Nabung harian lebih mudah & aman';
    $deskripsi = $isKolektor
        ? 'Kelola data nasabah dan setoran keliling dengan rapi, aman, dan terkontrol.'
        : 'Pantau saldo dan setoran harian Anda langsung dari genggaman kapan saja.';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex flex-col justify-between overflow-hidden p-6 sm:p-8 min-[900px]:p-12 text-ink']) }}>
    <!-- Header Brand & Portal Chip -->
    <div class="flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="group inline-flex items-center gap-3 transition hover:opacity-90">
            <span class="inline-flex h-11 w-11 sm:h-12 sm:w-12 items-center justify-center rounded-2xl text-white shadow-none transition" style="background: {{ $aksen }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </span>
            <div>
                <span class="block text-lg font-bold tracking-tight text-ink sm:text-xl">Tabungan Digital</span>
                <span class="block text-xs font-medium text-mute">Kolektor Keliling v1.0</span>
            </div>
        </a>

        <!-- Portal Chip Badge -->
        <span data-test="label-portal" class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white tracking-wide" style="background: {{ $aksen }}">Portal {{ $labelPortal }}</span>
    </div>

    <!-- Konten Tengah: Ilustrasi Toples + Headline -->
    <div class="my-6 flex flex-col items-center text-center sm:my-8 min-[900px]:my-10">
        <!-- Ilustrasi Toples Celengan -->
        <x-auth.ilustrasi-toples :portal="$portal" class="mb-4 sm:mb-6" />

        <h2 class="text-xl font-bold tracking-tight text-ink sm:text-2xl min-[900px]:text-3xl max-w-sm">
            {{ $headline }}
        </h2>
        <p class="mt-2 text-xs sm:text-sm text-mute max-w-xs sm:max-w-sm leading-relaxed">
            {{ $deskripsi }}
        </p>

        <!-- Kartu Mockup "Setoran dicatat" (Hairline border, tanpa bayangan tebal) -->
        <div class="auth-toast-float mt-6 w-full max-w-xs rounded-xl border border-hairline bg-surface p-3 text-left">
            <div class="flex items-center gap-3">
                <div class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $isKolektor ? 'bg-info-soft text-info' : 'bg-primary-soft text-primary' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-xs font-semibold text-ink">Setoran Dicatat</span>
                        <span class="text-[10px] text-mute">Baru saja</span>
                    </div>
                    <p class="truncate text-xs font-medium text-body">
                        {{ $isKolektor ? 'Setoran nasabah tersimpan aman' : 'Saldo tabungan bertambah' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian Bawah: Pesan Ramah / Hak Cipta Ringkas -->
    <div class="hidden min-[900px]:block text-xs text-mute">
        <span>© {{ date('Y') }} Tabungan Digital • Aman, transparan & tercatat rapi</span>
    </div>
</div>
