@props([
    'portal' => 'nasabah',
])

@php
    $aksen = \App\Support\PortalLogin::aksen($portal);
    $labelPortal = \App\Support\PortalLogin::labelPortal($portal);
    $isKolektor = $portal === \App\Support\PortalLogin::KOLEKTOR;

    $headline = $isKolektor
        ? ['Rute hari ini sudah', 'siap, tinggal', 'berangkat']
        : ['Menabung sedikit', 'demi sedikit, hasilnya', 'terasa'];

    $deskripsi = $isKolektor
        ? 'Lihat jadwal kunjungan, catat setoran, dan serahkan kas ke kantor tanpa kertas.'
        : 'Kolektor datang ke rumah, setoran langsung tercatat, saldo bisa Anda lihat kapan saja.';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex flex-col justify-between overflow-hidden p-6 sm:p-10 lg:p-12 text-white h-full select-none']) }}>
    <!-- Header Brand Pill Badge (Putih seperti di acuan desain) -->
    <div class="flex items-center justify-between">
        <a href="{{ route('home') }}" class="group inline-flex items-center gap-2.5 rounded-2xl bg-white px-4 py-2.5 shadow-sm transition hover:opacity-95">
            <span class="inline-flex h-6 w-6 items-center justify-center text-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="5" width="20" height="14" rx="3" />
                    <line x1="2" y1="10" x2="22" y2="10" />
                </svg>
            </span>
            <span class="text-sm sm:text-base font-bold tracking-tight text-gray-900">Tabungan Digital</span>
        </a>

        <!-- Portal Label untuk Aksesibilitas & Tes -->
        <span data-test="label-portal" class="sr-only">Portal {{ $labelPortal }}</span>
    </div>

    <!-- Teks Utama & Headline -->
    <div class="mt-8 sm:mt-10 lg:mt-12 max-w-md">
        <h1 class="text-2xl sm:text-3xl lg:text-[38px] font-bold tracking-tight text-white leading-[1.2]">
            {{ $headline[0] }}<br>
            {{ $headline[1] }}<br>
            {{ $headline[2] }}
        </h1>
        <p class="mt-3 sm:mt-4 text-xs sm:text-sm lg:text-base text-white/80 max-w-sm leading-relaxed">
            {{ $deskripsi }}
        </p>
    </div>

    <!-- Ilustrasi Toples Celengan Sesuai Desain Acuan -->
    <div class="my-auto pt-6 sm:pt-8 flex items-center justify-center">
        <x-auth.ilustrasi-toples :portal="$portal" class="w-full max-w-[320px] sm:max-w-[360px]" />
    </div>
</div>
