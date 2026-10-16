@props([
    'kategori' => 'hijau',
    'ukuran' => 44,
])

@php
    /**
     * DESIGN.md §2 "Ubin kategori": latar dan warna ikon mengikuti tabel pemetaan,
     * radius 14px (§6 token `tile`), ukuran 40/44/56px. Ubin bukan status — status
     * tetap lewat <x-ui.status-chip> — dan selalu berpasangan dengan label teks
     * yang ditulis di view pemakai, bukan di dalam komponen.
     */
    $kelasKategori = match ($kategori) {
        'hijau' => 'bg-primary-soft text-on-primary-soft',
        'biru' => 'bg-info-soft text-info',
        'teal' => 'bg-tile-teal-bg text-tile-teal-fg',
        'kuning' => 'bg-warning-soft text-warning-deep',
        'netral' => 'bg-inset text-body',
        'mawar' => 'bg-danger-soft text-danger-deep dark:bg-dark-danger-soft dark:text-dark-danger-text',
        default => throw new \InvalidArgumentException("Kategori ubin tidak dikenal: {$kategori}"),
    };

    $kelasUkuran = match ((int) $ukuran) {
        40 => 'size-10',
        44 => 'size-11',
        56 => 'size-14',
        default => throw new \InvalidArgumentException("Ukuran ubin tidak dikenal: {$ukuran}"),
    };
@endphp

<div data-test="tile" {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-[14px] '.$kelasKategori.' '.$kelasUkuran]) }}>
    {{ $slot }}
</div>
