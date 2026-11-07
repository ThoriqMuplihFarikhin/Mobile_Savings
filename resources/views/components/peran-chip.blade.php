@php
    $portalChip = \App\Support\PortalLogin::dariPeran(auth()->user()?->role);
@endphp

@if ($portalChip)
    <span {{ $attributes->merge(['class' => 'sr-only !hidden']) }} style="display: none !important;" data-test="chip-peran">{{ \App\Support\PortalLogin::labelPortal($portalChip) }}</span>
@endif
