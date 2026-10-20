@props([
    'portal' => 'nasabah',
])

@php
    $aksen = \App\Support\PortalLogin::aksen($portal);
    $isKolektor = $portal === \App\Support\PortalLogin::KOLEKTOR;
    $warnaToplesLid = '#92400e'; // Warm cork/wood
    $warnaToplesLidHighlight = '#b45309';
    $warnaKoin = '#fbbf24'; // Gold
    $warnaKoinTepi = '#d97706';
@endphp

<div {{ $attributes->merge(['class' => 'relative flex items-center justify-center select-none']) }} aria-hidden="true">
    <svg viewBox="0 0 260 260" class="w-full max-w-[220px] sm:max-w-[260px] h-auto drop-shadow-none" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Bayangan dasar datar (hairline) -->
        <ellipse cx="130" cy="242" rx="72" ry="8" fill="#e5e5e5" />

        <!-- Toples Kaca (Celengan) -->
        <!-- Badan Toples Belakang (Kaca) -->
        <path d="M78 86 C78 74 88 68 100 68 L160 68 C172 68 182 74 182 86 L188 214 C188 230 176 238 160 238 L100 238 C84 238 72 230 72 214 Z"
              fill="#f0fdf4" stroke="#e5e5e5" stroke-width="2.5" />

        <!-- Isi Celengan / Tumpukan Tabungan (Naik Perlahan) -->
        <g class="auth-jar-fill origin-bottom">
            <!-- Lapisan isi dalam toples -->
            <path d="M74 175 C88 170 110 178 130 174 C150 170 172 178 186 175 L187 214 C187 228 176 237 160 237 L100 237 C84 237 73 228 73 214 Z"
                  fill="{{ $isKolektor ? '#dbeafe' : '#dcfce7' }}" />

            <!-- Koin-koin di dalam toples -->
            <ellipse cx="106" cy="216" rx="18" ry="7" fill="{{ $warnaKoin }}" stroke="{{ $warnaKoinTepi }}" stroke-width="1.5" />
            <ellipse cx="152" cy="218" rx="17" ry="7" fill="{{ $warnaKoin }}" stroke="{{ $warnaKoinTepi }}" stroke-width="1.5" />
            <ellipse cx="130" cy="208" rx="20" ry="8" fill="{{ $warnaKoin }}" stroke="{{ $warnaKoinTepi }}" stroke-width="1.5" />
            <ellipse cx="112" cy="198" rx="18" ry="7" fill="{{ $warnaKoin }}" stroke="{{ $warnaKoinTepi }}" stroke-width="1.5" />
            <ellipse cx="146" cy="196" rx="18" ry="7" fill="{{ $warnaKoin }}" stroke="{{ $warnaKoinTepi }}" stroke-width="1.5" />
            <ellipse cx="128" cy="186" rx="19" ry="7.5" fill="{{ $warnaKoin }}" stroke="{{ $warnaKoinTepi }}" stroke-width="1.5" />
        </g>

        <!-- Garis Pantulan Kaca Depan -->
        <path d="M86 92 L82 212 C82 222 88 228 98 230" stroke="#ffffff" stroke-width="3" stroke-linecap="round" opacity="0.8" />
        <path d="M96 92 L94 140" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" opacity="0.6" />

        <!-- Garis Lekuk Badan Depan Toples (Hairline Kuat) -->
        <path d="M78 86 C78 74 88 68 100 68 L160 68 C172 68 182 74 182 86 L188 214 C188 230 176 238 160 238 L100 238 C84 238 72 230 72 214 Z"
              stroke="#858585" stroke-width="2" fill="none" />

        <!-- Leher & Tutup Toples Kaca (Gabus/Kayu Alami) -->
        <rect x="94" y="60" width="72" height="10" rx="3" fill="{{ $warnaToplesLidHighlight }}" stroke="#858585" stroke-width="1.5" />
        <rect x="88" y="48" width="84" height="14" rx="4" fill="{{ $warnaToplesLid }}" stroke="#858585" stroke-width="1.5" />
        <line x1="96" y1="52" x2="164" y2="52" stroke="{{ $warnaToplesLidHighlight }}" stroke-width="1.5" stroke-linecap="round" />

        <!-- Celah Masuk Koin di Tutup -->
        <rect x="115" y="44" width="30" height="5" rx="2" fill="#404040" />

        <!-- Koin Jatuh 1 (Kiri Atas) -->
        <g class="auth-coin-1">
            <ellipse cx="124" cy="24" rx="14" ry="12" fill="{{ $warnaKoin }}" stroke="{{ $warnaKoinTepi }}" stroke-width="1.5" />
            <text x="124" y="27" text-anchor="middle" font-size="9" font-weight="700" fill="#92400e" font-family="'Instrument Sans', sans-serif">Rp</text>
        </g>

        <!-- Koin Jatuh 2 (Kanan Atas Bergantian) -->
        <g class="auth-coin-2">
            <ellipse cx="140" cy="10" rx="13" ry="11" fill="{{ $warnaKoin }}" stroke="{{ $warnaKoinTepi }}" stroke-width="1.5" />
            <text x="140" y="13" text-anchor="middle" font-size="8.5" font-weight="700" fill="#92400e" font-family="'Instrument Sans', sans-serif">Rp</text>
        </g>

        <!-- Kilau / Sparkle Kecil -->
        <g class="auth-sparkle" style="transform-origin: 174px 108px;">
            <path d="M174 102 L176 107 L181 108 L176 110 L174 115 L172 110 L167 108 L172 107 Z" fill="{{ $warnaKoin }}" />
        </g>
        <g class="auth-sparkle" style="animation-delay: 1.2s; transform-origin: 88px 160px;">
            <path d="M88 156 L89.5 159.5 L93 160 L89.5 161.5 L88 165 L86.5 161.5 L83 160 L86.5 159.5 Z" fill="{{ $isKolektor ? '#60a5fa' : '#4ade80' }}" />
        </g>
    </svg>
</div>
