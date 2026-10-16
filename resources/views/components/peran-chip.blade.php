@php
    $portalChip = \App\Support\PortalLogin::dariPeran(auth()->user()?->role);
@endphp

@if ($portalChip)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white']) }} data-test="chip-peran" style="background: {{ \App\Support\PortalLogin::aksen($portalChip) }}">{{ \App\Support\PortalLogin::labelPortal($portalChip) }}</span>
@endif
