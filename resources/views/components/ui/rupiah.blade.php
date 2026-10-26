@props([
    'nilai' => 0,
    'tanda' => null,
    'cetak' => false,
])

@php
    if (! in_array($tanda, ['+', '-', '−', null], true)) {
        throw new \InvalidArgumentException("Tanda rupiah tidak dikenal: {$tanda}");
    }

    // Layar: spasi tak-terputus. View cetak: spasi biasa (tes memakai assertSee 'Rp 1.000').
    $pemisah = $cetak ? ' ' : '&nbsp;';

    $tandaTeks = match ($tanda) {
        '+' => '+',
        '-', '−' => '−',
        default => '',
    };

    $kelasTanda = match ($tanda) {
        '+' => ' text-primary',
        '-', '−' => ' text-ink',
        default => '',
    };

    $angka = number_format((float) $nilai, 0, ',', '.');
@endphp

{{-- DESIGN.md §3: format Rp 1.250.000, tabular-nums, setoran hijau, penarikan ink. --}}
<span data-test="rupiah" {{ $attributes->merge(['class' => 'tabular-nums whitespace-nowrap'.$kelasTanda]) }}>{!! $tandaTeks !!}Rp{!! $pemisah !!}{{ $angka }}</span>
