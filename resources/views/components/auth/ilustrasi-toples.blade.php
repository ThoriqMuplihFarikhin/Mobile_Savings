@props([
    'portal' => 'nasabah',
])

@php
    $aksen = \App\Support\PortalLogin::aksen($portal);
    $isKolektor = $portal === \App\Support\PortalLogin::KOLEKTOR;
@endphp

<div {{ $attributes->merge(['class' => 'relative flex items-center justify-center select-none w-full max-w-[340px] mx-auto']) }} aria-hidden="true">
    <svg viewBox="0 0 340 320" class="w-full h-auto drop-shadow-none" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Ambient Decorative Circles in Background -->
        <circle cx="65" cy="180" r="42" fill="rgba(255,255,255,0.06)" />
        <circle cx="280" cy="195" r="28" fill="rgba(255,255,255,0.05)" />

        <!-- Sparkle Kiri -->
        <g class="auth-sparkle" style="transform-origin: 75px 185px;">
            <path d="M 75 160 L 79 180 L 99 184 L 79 188 L 75 208 L 71 188 L 51 184 L 71 180 Z" fill="#fde68a" />
        </g>

        <!-- Sparkle Kanan -->
        <g class="auth-sparkle" style="animation-delay: 1.2s; transform-origin: 275px 145px;">
            <path d="M 275 130 L 278 142 L 290 145 L 278 148 L 275 160 L 272 148 L 260 145 L 272 142 Z" fill="#fef08a" />
        </g>

        <!-- Koin 1 (Kanan Atas) -->
        <g class="auth-coin-1">
            <circle cx="242" cy="85" r="22" fill="#f59e0b" stroke="#d97706" stroke-width="2" />
            <circle cx="242" cy="85" r="18" fill="#fbbf24" />
            <text x="242" y="90" text-anchor="middle" font-size="13" font-weight="700" fill="#78350f" font-family="'Instrument Sans', sans-serif">Rp</text>
        </g>

        <!-- Koin 2 (Tengah Kiri) -->
        <g class="auth-coin-2">
            <circle cx="152" cy="165" r="20" fill="#f59e0b" stroke="#d97706" stroke-width="2" />
            <circle cx="152" cy="165" r="16" fill="#fbbf24" />
            <text x="152" y="170" text-anchor="middle" font-size="12" font-weight="700" fill="#78350f" font-family="'Instrument Sans', sans-serif">Rp</text>
        </g>

        <!-- Toples Kaca (Celengan Tabungan) -->
        <!-- Badan Kaca Belakang -->
        <path d="M 98 145 L 98 268 C 98 288 112 300 130 300 L 210 300 C 228 300 242 288 242 268 L 242 145 Z"
              fill="rgba(255,255,255,0.08)"
              stroke="#ffffff"
              stroke-width="4.5"
              stroke-opacity="0.9" />

        <!-- Isi Tabungan Kuning / Emas (Naik Perlahan via auth-jar-fill) -->
        <g class="auth-jar-fill origin-bottom">
            <path d="M 100 215 C 120 215 150 215 170 215 C 190 215 220 215 240 215 L 240 268 C 240 286 226 298 210 298 L 130 298 C 114 298 100 286 100 268 Z"
                  fill="#f59e0b" />
            <text x="170" y="266" text-anchor="middle" font-size="28" font-weight="700" fill="#451a03" font-family="'Instrument Sans', sans-serif">Rp</text>
        </g>

        <!-- Garis Lekuk & Pantulan Kaca Depan -->
        <path d="M 108 152 L 108 260 C 108 274 116 284 126 288"
              stroke="#ffffff"
              stroke-width="3"
              stroke-linecap="round"
              stroke-opacity="0.35" />

        <!-- Tutup / Rim Putih Tebal Toples Atas -->
        <rect x="90" y="128" width="160" height="18" rx="9" fill="#ffffff" />

        <!-- Kartu Toast Mengambang "Setoran dicatat + Rp 20.000" -->
        <g class="auth-toast-float">
            <!-- Background Pill Toast -->
            <rect x="180" y="130" width="130" height="52" rx="16" fill="#ffffff" />
            <text x="195" y="152" font-size="11" font-weight="500" fill="#64748b" font-family="'Instrument Sans', sans-serif">Setoran dicatat</text>
            <text x="195" y="171" font-size="14" font-weight="700" fill="#16a34a" font-family="'Instrument Sans', sans-serif">+ Rp 20.000</text>
        </g>
    </svg>
</div>
