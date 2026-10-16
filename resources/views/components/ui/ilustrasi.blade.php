@props([
    'nama' => 'tunas',
    'ukuran' => 96,
])

@php
    /**
     * DESIGN.md §1 "Dekorasi" dan §7 "Kondisi kosong": ilustrasi SVG datar tanpa
     * gradien, maksimum satu per layar, selalu aria-hidden karena tidak pernah
     * membawa informasi. `tunas` untuk kondisi kosong, `kado` untuk header paket,
     * `dekor-hero` untuk pojok kanan atas hero-card (lingkaran, koin "Rp", tunas).
     */
    if (! in_array($nama, ['tunas', 'kado', 'dekor-hero'], true)) {
        throw new \InvalidArgumentException("Ilustrasi tidak dikenal: {$nama}");
    }

    $ukuran = (int) $ukuran;
@endphp

@if ($nama === 'tunas')
    <svg viewBox="0 0 64 64" width="{{ $ukuran }}" height="{{ $ukuran }}" fill="none" aria-hidden="true" focusable="false" {{ $attributes->merge(['class' => 'block']) }}>
        <path d="M32 54V26" class="stroke-on-primary-soft" stroke-width="4" stroke-linecap="round" />
        <path d="M32 40C20 40 14 32 16 22c10-1 17 6 16 18z" class="fill-primary" />
        <path d="M32 34c1-11 8-18 19-16 1 11-8 18-19 16z" class="fill-on-primary-soft" />
        <path d="M18 54h28" class="stroke-on-primary-soft" stroke-width="4" stroke-linecap="round" />
    </svg>
@elseif ($nama === 'kado')
    <svg viewBox="0 0 64 64" width="{{ $ukuran }}" height="{{ $ukuran }}" fill="none" aria-hidden="true" focusable="false" {{ $attributes->merge(['class' => 'block']) }}>
        <rect x="12" y="26" width="40" height="30" rx="4" class="fill-tile-teal-bg" />
        <rect x="6" y="16" width="52" height="12" rx="4" class="fill-tile-teal-fg" />
        <rect x="28" y="26" width="8" height="30" class="fill-tile-teal-fg" />
        <path d="M32 16c-8 0-14-4-12-9 2-4 9-2 12 9z" class="fill-tile-teal-fg" />
        <path d="M32 16c8 0 14-4 12-9-2-4-9-2-12 9z" class="fill-tile-teal-fg" />
    </svg>
@else
    <svg viewBox="0 0 120 120" width="{{ $ukuran }}" height="{{ $ukuran }}" fill="none" aria-hidden="true" focusable="false" {{ $attributes->merge(['class' => 'block']) }}>
        <circle cx="78" cy="40" r="42" class="fill-decor-green" />
        <circle cx="40" cy="76" r="26" class="fill-primary" />
        <circle cx="50" cy="40" r="18" class="fill-decor-gold" />
        <text x="50" y="41" text-anchor="middle" dominant-baseline="central" font-size="14" font-weight="600" class="fill-warning-deep">Rp</text>
        <path d="M96 108V76" class="stroke-on-hero-mute" stroke-width="5" stroke-linecap="round" />
        <path d="M96 90c-9 0-15-6-14-15 9-1 15 5 14 15z" class="fill-on-hero-mute" />
        <path d="M96 84c1-10 8-16 17-14 1 10-7 16-17 14z" class="fill-on-hero-mute" />
    </svg>
@endif
