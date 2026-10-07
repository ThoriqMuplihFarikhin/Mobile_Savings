@php
    $peran = auth()->user()?->role;
    $portalChip = is_string($peran) && in_array($peran, \App\Support\PortalLogin::semua(), true) ? $peran : null;
@endphp

@if ($portalChip)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white']) }} data-test="chip-peran" style="background: {{ \App\Support\PortalLogin::aksen($portalChip) }}">{{ \App\Support\PortalLogin::labelPortal($portalChip) }}</span>
@endif
